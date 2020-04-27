<?php

/**
 *
 * $Id: class.GenericException.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     exception
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

 /**
 * Generic exception. This is the very basic exception.
 *
 * @package     exception
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.4.0
 */
class GenericException extends Exception {

    protected
        $sName  = 'n/a',
        $sMsg   = null,
        $aClass = array();

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  Message for the exception to clarify the cause
     *                  (optional).
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function __construct($sMsg = null) {
        parent::__construct($sMsg);
        $this->setName(__CLASS__);

        // Set for local getExceptionMessage()
        $this->sMsg = $sMsg;
    }

    // --------------------------------------------------------------------

    /**
     * Sets the internal name of an exception.
     *
     * @access  protected
     * @param   string  The name of the exception.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    protected function setName($sName) {
        $this->sName = $sName;
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves the internal name of an exception.
     *
     * @access  public
     * @return  string  The name of the exception.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function getName() {
        return $this->sName;
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves the message of an exception. This method may return NULL
     * too, the build-in Exception::getMessage() can't as of time of
     * writing.
     *
     * @access  public
     * @return  string  Exception message.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function getExceptionMessage() {
        return $this->sMsg;
    }

    // --------------------------------------------------------------------

    /**
     * Prints exception box (HTML) and the stack backtrace if the exception
     * has not been caught but untreated.
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function printStackTrace() {

        // THIS IS REALLY A WEIRD PIECE OF CODE ... (the author)

        $sStyleEyecatch = 'color: #FF0000;'
                          .' font-family: Courier New, Courier, monospace;'
                          .' font-weight: bold;'
                          .' font-size: 16px;';
        $sStyleSrc      = 'color: #000000;'
                          .' font-family: Courier New, Courier, monospace;'
                          .' font-size: 16px;';
        $sStyleTxt      = 'color: #000000;'
                          .' font-family: Arial, Helvetica, sans-serif;'
                          .' font-size: 15px;';
        $sStyleBox      = 'color: #000000;'
                          .' font-family: Arial, Helvetica, sans-serif;'
                          .' font-size: 15px;'
                          .' background: #EEEEEE;'
                          .' border-style: solid;'
                          .' border-width: 1px;'
                          .' border-color: #666666;'
                          .' margin: 10px;';

        // --

        $sFile  = $this->getFile();
        $sLine  = $this->getLine();
        $nCode  = $this->getCode();
        $sMsg   = $this->getExceptionMessage();
        $aTrace = $this->getTrace();

        // ---

        echo  '<table width="1" cellspacing="3" cellpadding="0" border="0"';
        echo    ' style="' . $sStyleBox . '">';
        echo    '<tr valign="top">';
        echo      '<td rowspan="10">&nbsp;</td>';
        echo      '<td align="right" nowrap>Untreated:</td>';
        echo      '<td>';
        echo        '<span style="' . $sStyleEyecatch . '">';
        echo          $this->getName();
        echo        '</span>';
        echo      '</td>';
        echo      '<td rowspan="10">&nbsp;</td>';
        echo    '</tr>';

        echo    '<tr valign="top">';
        echo      '<td align="right" nowrap>';
        echo        'In file:';
        echo      '</td>';
        echo      '<td nowrap>' . $sFile . '</td>';
        echo    '</tr>';

        echo    '<tr valign="top">';
        echo      '<td align="right" nowrap>Reason:</b></td>';
        echo      '<td nowrap>';

        if ($sMsg === null) {
            echo 'Not given (NULL)';
        } else if (trim($sMsg) === '') {
            echo 'Given, but is empty string (may it\'s the cause?)';
        } else {
            echo $sMsg;
        }

        echo      '</td>';
        echo    '</tr>';

        // ----------------------------------------------------------------

        // If no status code is given, this exception is not caught by
        // the software directly. Print backtrace.
        if (!$nCode) {
            echo    '<tr><td colspan="2"><hr size="1" noshade /></td></tr>';

            // ---

            echo    '<tr valign="top">';
            echo      '<td align="right" nowrap>Trace:<br /></td>';
            echo      '<td>';
            echo        '<table cellpadding="0" cellspacing="0" border="0"';
            echo          ' style="' . $sStyleTxt . '">';

            echo          '<tr>';
            echo            '<td nowrap>';
            echo              '<tt>*</tt> class ';
            echo                '<span style="' . $sStyleEyecatch . '">';
            echo                  $this->extractClassName($sFile);
            echo                '</span>';
            echo              ' throws ';
            echo                '<span style="' . $sStyleEyecatch . '">';
            echo                  $this->getName();
            echo                '</span>';
            echo              ' in line ' . $sLine;
            echo            '</td>';
            echo          '</tr>';

            // Iterate trough backtrace, and generate nice HTML output
            for ($i=0, $n=sizeof($aTrace); $i<$n; $i++) {

                if(!isset($aTrace[$i]['file'])) {
                    continue;
                }

                $sFileName = basename($aTrace[$i]['file']);

                if ($sStr = $this->extractClassName($sFileName)) {
                    $sNewStr = 'method '
                                .'<span style="' . $sStyleSrc . '">'
                                .$sStr
                                .'::'
                                .$aTrace[$i]['function']
                                .'()'
                                .'</span>';

                } else {
                    $sNewStr =  'in file ' . $sFileName . ' ';

                    $sNewStr .= '<span style="' . $sStyleSrc . '">';
                    if (isset($aTrace[$i]['class'])) {
                        if (isset($this->aClass[$aTrace[$i]['class']])) {
                            $sNewStr .=   @$this->aClass[$aTrace[$i]['class']];
                        }
                        $sNewStr .=   $aTrace[$i]['type'];
                    }
                    $sNewStr .= $aTrace[$i]['function'].'()';
                    $sNewStr .= '</span>';
                }

                // ---

                echo      '<tr>';
                echo        '<td nowrap>';
                echo          '<tt>*</tt> ';
                echo            $sNewStr;
                echo            ($i < $n-1) ? ' rethrows' : ' terminates';
                echo            ' in line '. $aTrace[$i]['line'];
                echo        '</td>';
                echo      '</tr>';
            }

            echo        '</table>';
            echo      '</td>';
            echo    '</tr>';

            echo    '<tr><td colspan="2"><hr size="1" noshade /></td></tr>';

            echo    '<tr valign="top">';
            echo      '<td align="right">Engine:</td>';
            echo      '<td nowrap>';
            echo        COWIKI_FULL_NAME . ' (' . COWIKI_VERSION_DATE .')';
            echo        ' running PHP ' . PHP_VERSION;
            echo      '</td>';
            echo    '</tr>';

            echo    '<tr><td colspan="2"><hr size="1" noshade /></td></tr>';

            echo    '<tr>';
            echo      '<td colspan="2">';
            echo        'Please report the complete message above (copy';
            echo        ' &amp; paste) to the developers of this software.';
            echo        ' Provide your offical email for further enquiry.';
            echo        ' Thank you for your efforts.';
            echo      '</td>';
            echo    '</tr>';
        } // of if

        echo  '</table>';
    }

    // --------------------------------------------------------------------

    /**
     * Extracts class or interface name from its complete filesystem path.
     *
     * @access  private
     * @param   string  The filesystem path to an interface or class.
     * @return  string  The extracted interface or class name.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    private function extractClassName($sStr) {
        $sPat = '#(class|interface)\.([^.]+)\.php#Si';

        if (preg_match($sPat, $sStr, $aMatches)) {
            $sStr = $aMatches[2];

            // Remember uppercase class name
            $this->aClass[strtolower($aMatches[2])] = $aMatches[2];

            $sStyleSrc = 'color: #000000;'
                         .' font-family: Courier New, Courier, monospace;'
                         .' font-size: 16px;';

            return '<span style="' . $sStyleSrc . '">' . $sStr . '</span>';
        }

        return '';
    }

} // of class

?>