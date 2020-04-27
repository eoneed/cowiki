<?php

/**
 *
 * $Id: class.CustomEmbed.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomEmbed
 * #purpose:   Embed a simple object (e.g. image) without access check
 * #param:     src     the relative source path or URI of the object
 * #param:     width   width of the object to embed
 * #param:     height  height of the object to embed
 * #param:     alt     alternative tooltip text for the object
 * #param:     style   CSS style to apply to the embedded object
 * #param:     align   alignment attribute ("left", "right" etc.) for
 *                     the object if no CSS styles are used or available
 * #caching:   not used
 * #comment:   none
 * #version:   1.2
 * #date:      31. January 2004
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Embed a simple object (e.g. image) without access check
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class CustomEmbed extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean true if initialization successful, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform the plugin purpose. This is the main method of the plugin.
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function perform() {

        // Get plugin parameters
        $sSrc   = $this->Context->getPluginParam('src');
        $sStyle = $this->Context->getPluginParam('style')
                      ? $this->Context->getPluginParam('style')
                      : '';
        $sAlt   = $this->Context->getPluginParam('alt')
                      ? $this->Context->getPluginParam('alt')
                      : '';
        $sWidth = $this->Context->getPluginParam('width')
                      ? $this->Context->getPluginParam('width')
                      : '';
        $sHeight = $this->Context->getPluginParam('height')
                      ? $this->Context->getPluginParam('height')
                      : '';
        $sAlign = $this->Context->getPluginParam('align')
                      ? $this->Context->getPluginParam('align')
                      : '';

        // ----------------------------------------------------------------

        $aData = null;

        // Remove up paths references and avoid root access
        $sSrc = str_replace('..', '', $sSrc);
        $sSrc = preg_replace('=^/*=', '', $sSrc);

        // Check if "source" is an URI
        $UriInfo = new UriInfo($sSrc);

        // Source seems to be an URI
        if ($UriInfo->isValid()) {
            // Get data of the referenced URI source
            $HttpRequest = HttpRequest::getInstance();
            $aReturn = $HttpRequest->fetchContent($UriInfo);

            if ($aReturn['status']['code'] === 200) {
                $aData = @getimagesize($sSrc);
            }
        }

        // Assume that source is not an URI. Embed local object/image
        if ($UriInfo->isNotValid()) {

            $sFull = $this->Env->get('DOCUMENT_ROOT').'/'.getDirName($sSrc);
            $sPath = realpath($sFull) . '/' . basename($sSrc);

            // Avoid notice if source is not readable or does not exist
            if (is_readable($sPath)) {
                // Get data of the referenced source
                $aData = @getimagesize($sPath);
            }
        }

        if (!is_array($aData)) {
            //return true;  // leave silently if unrecoginzed format
        }

        // ----------------------------------------------------------------

        // Assemble "width" & "height" dimensions if given
        $sDim = '';
        if ($sWidth != '') {
            $sDim .= ' width="'.$sWidth.'"';
        }
        if ($sHeight != '') {
            $sDim .= ' height="'.$sHeight.'"';
        }

        // If no "width" and "height" given, get it from "getimagesize()"
        if ($sDim == '') {
            $sDim = ' ' . $aData[3];
        }

        $sStr = '';

        switch ($aData[2]) {

            case 1: // image/gif
            case 2: // image/jpeg
            case 3: // image/png
                $sStr =  '<img '.($sStyle != ''?'style="'.$sStyle.'"':'');
                $sStr .= ' src="'.$sSrc.'"'.$sDim.' alt="'.$sAlt.'"';
                $sStr .= ' align="'.$sAlign.'" border="0" />';
                break;

            case 4;  // application/x-shockwave-flash
            case 13; // application/x-shockwave-flash
                $sStr =  '<object '.($sStyle != ''?'style="'.$sStyle.'"':'');
                $sStr .= ' classid="clsid:D27CDB6E-AE6D-11cf-96B8-';
                $sStr .= '444553540000" codebase="http://download.macro';
                $sStr .= 'media.com/pub/shockwave/cabs/flash/swflash.cab';
                $sStr .= '#version=6,0,29,0"'.$sDim.'>';
                $sStr .= '<param name="movie" value="'.$sSrc.'">';
                $sStr .= '<param name="quality" value="high">';
                $sStr .= '<embed src="'.$sSrc.'" quality="high"';
                $sStr .= ' pluginspage="http://www.macromedia.com/go/get';
                $sStr .= 'flashplayer" type="application/x-shockwave-flash"';
                $sStr .= $sDim.'></embed></object>';
                break;

            // <ack>
            // !!! +comment line #139 ("return true;  // leave silently if unrecoginzed format")
            default:

                if (!file_exists($sSrc)) {
                    return true;  // leave silently if unrecoginzed format
                }
                $sStr .= 'Attached file: <a href="'.$sSrc.'">'.basename($sSrc).'</a> ('.number_format(filesize($sSrc)).' bytes)';
            // </ack>
        }

        // ----------------------------------------------------------------

        echo $sStr;
    }

} // of plugin component

?>
