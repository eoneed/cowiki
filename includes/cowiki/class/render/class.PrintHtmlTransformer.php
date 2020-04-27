<?php

/**
 *
 * $Id: class.PrintHtmlTransformer.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     render
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
 * coWiki - Print HTML transformer class
 *
 * @package     render
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrintHtmlTransformer extends FrontHtmlTransformer {

    protected
        $Response = null,
        $nTopicCount = 0,
        $aNodeBackup = array(),
        $bChangedRef = false;

    private
        $aLinks = array();

    /**
     * Get instance
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
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new PrintHtmlTransformer;
        }
        return self::$Instance;
    }

    /**
     * Class constructor
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        parent::__construct();
    }

    /**
     * &_transform missing document
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _transformMissingDocument($aMatches) {
        $Context = RuntimeContext::getInstance();

        // Get current directory/document object
        $Node = $Context->getCurrentNode();

        // Check if 'strref' is a reference to an other web.
        // Means: "webname|documentname"
        $aWebRef = explode('|', $aMatches[1]);

        // Do we have a reference to an other web?
        if (sizeof($aWebRef) > 1) {
            $sStr =  '<span class="error">[';
            $sStr .=    __('I18N_DOC_UNRESOLVED_WEB_REFERENCE').': ';
            $sStr .=    $aMatches[1];
            $sStr .= ']</span>';
            return $sStr;
        }

        // Remove <noop>s if any
        $aMatches[1] = str_replace('<noop>', '', $aMatches[1]);
        $aMatches[1] = str_replace('</noop>', '', $aMatches[1]);

        // Do we have an alias?
        $sStr = ($aMatches[2] == '') ? $aMatches[1] : $aMatches[2];

        return $sStr . '<strong>?</strong>';
    }

    /**
     * &_transform existing document
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function _transformExistingDocument($aMatches) {
        $Context = RuntimeContext::getInstance();

        // Possible alias
        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $sStr = $aMatches[2];
        } else {
            $sStr = escape($this->aNodeBackup[$aMatches[1]]->get('name'));
        }

        // First create temporary link tags. This is needed because we
        // do not know the exact order of links or URIs.

        $sLink =  $Context->getRequest()->getHostUri();
        $sLink .= $this->Response->getControllerHref('node='.$aMatches[1]);

        return '<tmplink uri="'.$sLink.'">' . $sStr . '</tmplink>';
    }

    /**
     * &_transform uri
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function _transformUri($aMatches) {
        $sStr1 = $aMatches[1];

        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $sStr2 = $aMatches[2];
        } else {

            // Because extremely long URIs won't wrap in browser output
            // and shred the output horizontally, we'll shorten them a bit.
            // If you change it, also change the FrontHtmlTransformer.

            // FIX: This behaviour might me toggleable or configureable in
            // length via the core.conf file.

            $sStr2 = shortenUrl($aMatches[1]);
        }

        if ($this->Registry->get('RUNTIME_EMAIL_OBFUSCATE')) {
            $sStr1 = obfuscateEmail($sStr1);
            $sStr2 = obfuscateEmail($sStr2);
        }

        return '<tmplink uri="'.$sStr1.'">'.$sStr2.'</tmplink>';
    }

    /**
     * &_transform topic
     *
     * @access  protected
     * @param   array
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _transformTopic($aMatches) {
        return $aMatches[2];
    }

    /**
     * Do something with the completed string
     *
     * @access  public
     * @param   string
     * @param   object
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function finish(&$sStr, $Node) {

        $Request  = RuntimeContext::getInstance()->getRequest();
        $Response = RuntimeContext::getInstance()->getResponse();

        // Reset link counter
        $this->nLink = 0;

        // Treat all temporary links
        $sStr = preg_replace_callback(
            '=<tmplink uri\="(.*)">(.*)</tmplink>=Usi',
            array(&$this, '_transformTempLink'),
            $sStr
        );

        // Add URL of this document
        $sStr .= '<hr />';
        $sStr .= '<p class="linklist">';
        $sStr .=    '<strong>'.__('I18N_DOCUMENT_URL').'</strong>:<br />';
        $sStr .=    '&nbsp;&nbsp;&nbsp;';
        $sStr .=    $Request->getHostUri();
        $sStr .=    $Response->getControllerHref('node='.$Node->get('id'));
        $sStr .= '</p>';

        // Build link reference list
        $sLinkStr = '';

        for ($i=0, $n=sizeof($this->aLinks); $i<$n; $i++) {
            $sLinkStr .= '&nbsp;' . ($i<9 ? '&nbsp;&nbsp;' : '');
            $sLinkStr .= '['.($i+1).']&nbsp;'. $this->aLinks[$i].'<br />';
        }

        if ($sLinkStr != '') {
            $sStr .= '<p class="linklist">';
            $sStr .=    '<strong>'.__('I18N_LINKS').'</strong>:<br />';
            $sStr .=    $sLinkStr;
            $sStr .= '</p>';
        }

        return $sStr;
    }

    /**
     * &_transform temp link
     *
     * @access  private
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    private function _transformTempLink(&$aMatches) {

        // Look up if URI or link is already in array
        $mPos = array_search($aMatches[1], $this->aLinks, true);

        // URI or link not found in array
        if ($mPos === false) {
            // Add new link or URI to array
            $this->aLinks[] = $aMatches[1];
            $nIndex = sizeof($this->aLinks);

        } else {
            // URIs already in array - add 1 to position
            $nIndex = $mPos + 1;
        }

        // Return aliases
        return '<u>' . $aMatches[2] . '</u>&nbsp;[' . $nIndex . ']';
    }

} // of class

?>