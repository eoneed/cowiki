<?php

/**
 *
 * $Id: core.function.php 30 2011-01-09 14:48:12Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    core
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 30 $
 *
 */

/**
 * For debugging purposes
 *
 * @package  core
 * @access   public
 *
 * @author   Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since    coWiki 0.3.0
 *
 * @todo     [D11N]  Complete documentation
 */

    $COWIKI_PROFILING_TIME = getMicroTime();
    $COWIKI_PROFILING_START_TIME = $COWIKI_PROFILING_TIME;
    $COWIKI_PROFILING_COUNTER = 0;

    // --------------------------------------------------------------------

    /**
     *
     *
     * @access  public
     * @return  mixed
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    function debug() {
        error_reporting(E_ALL|E_STRICT);
        ini_set('display_errors', 'on');
    }

    // --------------------------------------------------------------------

    /**
     * Profiling functions
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    function getMicroTime() {
        return microtime(true);
    }

    // --------------------------------------------------------------------

    /**
     * Profile
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function profile() {
        $GLOBALS['COWIKI_PROFILING_COUNTER']++;

        $nTime  = $GLOBALS['COWIKI_PROFILING_TIME'];
        $nCount = $GLOBALS['COWIKI_PROFILING_COUNTER'];
        $GLOBALS['COWIKI_PROFILING_TIME'] = getMicroTime();

        return number_format(getMicroTime() - $nTime, 3);
    }

    function profileTotal() {
        return number_format(
                  getMicroTime() - $GLOBALS['COWIKI_PROFILING_START_TIME'],
                  3
               );
    }

    // --------------------------------------------------------------------

    /**
     * We do not use PHP's dirname() function, it has strange side effects
     *
     * ack FIX: This function does not work properly under win, if the
     *          incoming path is a Unix/URI Style Path with "/"
     *          Do we have to return Backslashes?
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    function getDirName($sPath) {
        $sDeli = '/';
        $sPath = str_replace('\\', '/', $sPath);
        //$sDeli = substr(PHP_OS, 0, 3) == 'WIN' ? '\\' : '/';
        return substr($sPath, 0, strrpos($sPath, $sDeli)) . $sDeli;
    }

    // --------------------------------------------------------------------

    /**
     * Get request base path
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function getRequestBasePath($sPath) {
        return substr($sPath, 0, strrpos($sPath, '/')) . '/';
    }

    // --------------------------------------------------------------------

    /**
     * Newline2br
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function newline2br($sStr)  {
        return str_replace("\n", "<br />\n", $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Cut off
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nMaxLen"
     */
    function cutOff($sStr, $nMaxLen, $sSuffix = '...')    {
        if (strlen($sStr) > $nMaxLen) {
            $sStr = substr($sStr, 0 , $nMaxLen) . $sSuffix;
        }
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Cut off at start
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nMaxLen"
     */
    function cutOffAtStart($sStr, $nMaxLen, $sPrefix = '...')    {
        if (strlen($sStr) > $nMaxLen) {
            $sStr =  $sPrefix . substr($sStr, -$nMaxLen);
        }
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Cut off word
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nMaxLen"
     */
    function cutOffWord($sStr, $nMaxLen, $sSuffix = '...') {
        if (strlen($sStr) > $nMaxLen) {
            // Do not use ";" as delimiter, as it would destroy HTML
            // entities under unfortunate circumstances.
            $aDelimiter = array(' ', '.', ',', '!', '?', '-', ':', '_', '/');
            $sStr = substr($sStr, 0, $nMaxLen + 1);

            $aPos = array();

            for ($i=0, $n=sizeof($aDelimiter); $i<$n; $i++) {
                $nPos = strrpos($sStr, $aDelimiter[$i]);
                if ($nPos) {
                    $aPos[] = $nPos;
                }
            }

            if (sizeof($aPos) > 0) {
                rsort($aPos);
                $sStr = substr($sStr, 0, $aPos[0]);
            }

            $sStr .= $sSuffix;
        }
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Wiki word
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function wikiWord($sStr) {
        static $aTrans = array(
            'À' => 'A',  'Á' => 'A',  'Â' => 'A',  'Ã' => 'A',  'Ä' => 'Ae',
            'Å' => 'A',  'Æ' => 'Ae', 'Ç' => 'C',  'È' => 'E',  'É' => 'E',
            'Ê' => 'E',  'Ë' => 'E',  'Ì' => 'I',  'Í' => 'I',  'Î' => 'I',
            'Ï' => 'I',  'Ñ' => 'N',  'Ò' => 'O',  'Ó' => 'O',  'Ô' => 'O',
            'Õ' => 'O',  'Ö' => 'Oe', 'Ø' => 'O',  'Ù' => 'U',  'Ú' => 'U',
            'Û' => 'U',  'Ü' => 'Ue', 'Ý' => 'Y',
            'ß' => 'ss', 'à' => 'a',  'á' => 'a',  'â' => 'a',  'ã' => 'a',
            'ä' => 'ae', 'å' => 'a',  'æ' => 'ae', 'ç' => 'c',  'è' => 'e',
            'é' => 'e',  'ê' => 'e',  'ë' => 'e',  'ì' => 'i',  'í' => 'i',
            'î' => 'i',  'ï' => 'i',  'ñ' => 'n',  'ò' => 'o',  'ó' => 'o',
            'ô' => 'o',  'õ' => 'o',  'ö' => 'oe', 'ø' => 'o',  'ù' => 'u',
            'ú' => 'u',  'û' => 'u',  'ü' => 'ue', 'ý' => 'y',  'ÿ' => 'y',
            '±' => 'a',  'ê' => 'e',  'æ' => 'c',  'ó' => 'o',  '³' => 'l',
            'ñ' => 'n',  '¶' => 's',  '¿' => 'z',  '¼' => 'z',
            '¡' => 'A',  'Ê' => 'E',  'Æ' => 'C',  'Ó' => 'O',  '£' => 'L',
            'Ñ' => 'N',  '¦' => 'S',  '¯' => 'Z',  '¬' => 'Z'
        );
        $sStr = strtr($sStr, $aTrans);

        // Get rid of quotation mark
        $sStr = str_replace("'", '', $sStr);

        // Mark delimiters
        $sStr = preg_replace(
                    "#[\x20-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]#",
                    "\x01",
                    $sStr
                );

        // Get rid of special chars
        $sStr = preg_replace("#[^a-zA-Z0-9\x01]#", "", $sStr);

        // Recover delimiters as spaces
        $sStr = str_replace("\x01", " ", $sStr);

        // Capitalize the first character of each word
        return str_replace(" ", "", ucwords($sStr));
    }

    // --------------------------------------------------------------------

    /**
     * Obfuscate email
     *
     * @access  public
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function obfuscateEmail($sStr) {

        $sStr = preg_replace(
            '#(.+)@(.+)\.([a-zA-Z]{2,})#US',
            '\1 (at) \2 (dot) \3',
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Escape
     *
     * @access  public
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function escape($sStr) {
        $sStr = htmlspecialchars($sStr);
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Unescape
     *
     * @access  public
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function unescape($sStr) {
        $sStr = strtr($sStr,
                   array_flip(
                      get_html_translation_table(HTML_SPECIALCHARS)
                   )
                );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Escape backslashes
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function escapeBackslashes($sStr) {
        return str_replace('\\', '\\\\', $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Unescape backslashes
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function unescapeBackslashes($sStr) {
        return str_replace('\\\\', '\\', $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Expects warp=physical|hard
     *
     * @access  public
     * @param   string
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$nLen"
     * @todo    [D11N]  Check return type
     */
    function quote($sStr, $nLen = 78) {
        $aTok = explode("\n", $sStr);

        for ($i=0, $n=sizeof($aTok); $i<$n; $i++) {

            if (strlen($aTok[$i]) > $nLen) {
                // Already quoted?
                preg_match('#^(>*)#', $aTok[$i], $aMatches);
                $aTok[$i] = '>' . $aTok[$i];
                $aTok[$i] = wordwrap($aTok[$i], $nLen);
                $aTok[$i] = str_replace("\n", "\n>".$aMatches[1], $aTok[$i]);
            } else {
                $aTok[$i] = '>' . $aTok[$i];
            }
        }

        $sStr = join("\n", $aTok);

        // Remove empty quoted lines
        return preg_replace('#^>+$#m', '', $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Set catalog
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function setCatalog($sPathLocale, $sName)    {
        if ($sPathLocale[strlen($sPathLocale)-1] != '/') {
            $sPathLocale .= '';
        }

        if (@include($sPathLocale . $sName. '.cat')) {
            foreach ($__ as $k => $v) {
                $GLOBALS['__'][$k] = $v;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * __
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function __($sMsgId) {
        if (isset($GLOBALS['__'][$sMsgId])) {
            return $GLOBALS['__'][$sMsgId];
        }

        return $sMsgId;
    }

    // --------------------------------------------------------------------

    /**
     * Get salt
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function getSalt() {
        return getSaltChar().getSaltChar();
    }

    // --------------------------------------------------------------------

    /**
     * Get salt char
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function getSaltChar(){
        do ($nNum = randomSaltNum());
        while (($nNum > 57 && $nNum < 65) || ($nNum > 90 && $nNum < 97));
        return chr($nNum);
    }

    // --------------------------------------------------------------------

    /**
     * Random salt num
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    function randomSaltNum()    {
        mt_srand((double)microtime()*1000000);
        return mt_rand(46, 122);
    }

    // --------------------------------------------------------------------

    /**
     * Use with caution on self referenced structures! Watch your httpd!
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$mVar"
     */
    function printr($mVar) {
        ob_start();
        print_r($mVar);
        $sContent = ob_get_contents();
        ob_end_clean();

        echo '<pre>';
        echo htmlentities($sContent);
        echo '</pre><hr />';
    }

    // --------------------------------------------------------------------

    function trace() {
        ob_start();
        debug_print_backtrace();
        $sContent = ob_get_contents();
        ob_end_clean();

        echo '<pre>';
        echo htmlentities($sContent);
        echo '</pre><hr />';
    }

    // --------------------------------------------------------------------

    // For string debug purposes
    function dump($s, $l = 16, $b = true) {
        $sStr = '<pre>';

        // Avoid undesired side effects
        if (abs($l) < 4) { $l = 16; }

        for ($i=0; $i<strlen($s); $i = $i+$l) {
            if ($b) {
                for ($j=0; $j<$l; $j++) {
                    $sStr .= sprintf ('%02X ', ord(substr($s, $i+$j, 1)));
                }

                $sStr .= ' ';
            }

            for ($j=0; $j<$l; $j++) {
                $a = substr($s, $i+$j, 1);
                if (ord($a) < 32) {
                    $sStr .= '.';
                } else {
                    $sStr .= htmlentities($a);
                }
            }

            $sStr .= "\n";

            if ($i>=$l*$l && $i%($l*$l) == 0) { $sStr .= "\n"; }
        }

        $sStr .= '</pre>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    // Because extremely long URIs won't wrap in browser output and shred
    // the output horizontally, this function shortens the URI.
    function shortenUrl($sStr) {

        if (strlen($sStr) > 90) {
            // Leave spaces for the rendering engine to wrap the string
            return substr($sStr, 0, 40).' ... '.substr($sStr, -35);
        }

        return $sStr;
    }

?>
