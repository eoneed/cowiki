<?php

/**
 *
 * $Id: class.Utility.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * The Utility class provides general methods (like string manipulations)
 * that do not fit elsewhere. This class is a Singelton. You can not
 * instantiate this class directly (with $foo = new class), but have to
 * get its instance:
 *
 * Example:
 *   <code>
 *      // This won't work
 *      $Util = new Utility();
 *
 *      // This is the right way
 *      $Util = Utility::getInstance();
 *   </code>
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class Utility extends Object {

    protected static
        $Instance = null;

    protected
        $Context  = null,
        $Registry = null;

    // --------------------------------------------------------------------

    /**
     * Get the unique instance of the class (This class is implemented as
     * Singleton).
     *
     * @access  public
     * @return  Object  The class instance
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new Utility;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $this->Context  = RuntimeContext::getInstance();
        $this->Registry = $this->Context->getRegistry();
    }

    // --------------------------------------------------------------------

    /**
     * Colorize posting quotes - set different colors for different
     * quoting levels. Recoginzed quoting characters are '>', '|' and ':'.
     *
     * @access  public
     * @param   string  The posting (mail) text with multiple quoting
                        levels
     * @return  string  The colorized string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function &colorizeQuote($sStr) {
        $aArr = explode("\n", $sStr);

        for ($i=0, $n=sizeof($aArr); $i<$n; $i++) {

            // Convert "&gt;" entity to an unusual character. The '&gt;'
            // entity won't match the regex character class. Changing
            // it to a character that could not occur in the string will
            // do the trick.
            $aArr[$i]= str_replace('&gt;', "\x1", $aArr[$i]);

            // Find quoting identifiers
            preg_match("=^([\x1|: ]+)(.*)=S", $aArr[$i], $aMatches);

            // Compute quoting level
            $nLevel = 0;
            if (isset($aMatches[1])) {
                $nLevel = strlen(str_replace(' ', '', $aMatches[1]));
            }

            // Convert unusual character back to "&gt;"
            $aArr[$i] = str_replace("\x1", '&gt;', $aArr[$i]);

            // Colorize
            if ($this->Registry->has('COLOR_QUOTE_LEVEL'.$nLevel)) {
                $sColor = $this->Registry->get('COLOR_QUOTE_LEVEL'.$nLevel);

                $sStyle = 'style="color:'.$sColor.'"';

                $aArr[$i] = '<span '.$sStyle.'>'
                                .'<i>'.chop($aArr[$i]).'</i>'
                            .'</span>';
            }
        }

        $sStr = join("\n", $aArr);
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Colorize (highlight) code.
     *
     * @access  public
     * @param   string  Code that has to be highlighted.
     * @return  string  The highlighted code.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function colorizeCode($sStr) {

        // Dispatch code by type
        switch (true) {
            default:
                return $this->colorizePhpCode($sStr);
                break;
        }

    }

    // --------------------------------------------------------------------

    /**
     * Colorize (highlight) PHP code. This is a helper method for
     * colorizeCode()
     *
     * @access  protected
     * @param   string  Code that has to be highlighted.
     * @return  string  The highlighted code.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @see     colorizeCode
     */
    protected function colorizePhpCode($sStr) {
        $sStr = highlight_string(unescape($sStr), 1);

        // Avoid ugly breaks: remove HTML code assembled by the
        // highlight_string() function
        $nPos = strpos($sStr, '<br />') + 6; // x ist the length of needle
        $sStr = substr($sStr, $nPos);

        $nPos = strrpos($sStr, '<br />');
        $sStr = substr($sStr, 0, $nPos);

        // Append missing </font> if needed
        if (substr($sStr, 0, 5) == '<font') {
            $sStr .= '</font>';
        }

        // Rewrite <font/> to <span/> (<font/> not allowed in <pre/>)
        $sStr = str_replace('<font color="', '<span style="color: ', $sStr);
        $sStr = str_replace('</font>', '</span>', $sStr);

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Convert all URI in a text into clickable HTML counterparts. URIs
     * prefixed by the http, https, ftp, mailto and news protocol schemes
     * will become clickable.
     *
     * @access  public
     * @param   string  Text to be converted.
     * @return  string  The converted HTML text.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function clickable($sStr) {

        // Treat clickable URIs (http:, mailto: etc.)
        $sStr = preg_replace_callback(
            '=  (http://|https://|ftp://|mailto:|news:)
                (\S+)
                (\*\s|\=\s|&quot;|&lt;|&gt;|<|>|\(|\)|\s|$)
             =Usmix',
            array(&$this, 'processClickable'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Clickable callback. This is a helper callback method for clickable.
     *
     * @access  protected
     * @param   array   RegEx matches defined in preg_replace_callback().
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     clickable
     */
    protected function processClickable(&$aMatches) {
        $sHref = $aMatches[1].$aMatches[2];

        return '<a rel="nofollow" target="_blank" href="'.$sHref.'">'
                  .shortenUrl($sHref)
                .'</a>'.$aMatches[3];
    }

} // of class

?>
