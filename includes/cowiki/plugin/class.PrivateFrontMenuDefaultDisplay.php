<?php

/**
 *
 * $Id: class.PrivateFrontMenuDefaultDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontMenuDefaultDisplay
 * #purpose:   Provide the default (main) menu
 * #param:     none
 * #caching:   yes, internal cache
 * #comment:   none
 * #version:   1.0
 * #date:      01. November 2002
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Provide the default (main) menu
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateFrontMenuDefaultDisplay extends AbstractPlugin
                                     implements CustomObserver {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    /**
     * Init
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
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    /**
     * Perform
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   Relocate the HTML to template. Maybe a TemplateProcessor
     */
    public function perform() {

        // Make this plugin observe the document DAO and user DAO.
        // Observed DAO will signal if this plugin should clean up its
        // cache. See "update()"
        $this->Context->getDocumentDAO()->addObserver($this);
        $this->Context->getUserDAO()->addObserver($this);

        // ----------------------------------------------------------------

        // If cached result exists, put it out and leave the plugin
        if ($sStr = $this->Context->getFromCache($this)) {
            echo $sStr;
            return true; // leave plugin
        }

        // ----------------------------------------------------------------

        // Get plugin parameters
        $sImg = $this->Context->getPluginParam('img')
                  ? $this->Context->getPluginParam('img')
                  : false;

        $sHspace = $this->Context->getPluginParam('imghspace')
                    ? $this->Context->getPluginParam('imghspace')
                    : '0';

        $sVspace = $this->Context->getPluginParam('imgvspace')
                    ? $this->Context->getPluginParam('imgvspace')
                    : '0';

        $sImgPath = $this->Registry->get('PATH_IMAGES');

        // Get image dimensions
        $sDocRoot = $this->Context->getEnvironment()->get('DOCUMENT_ROOT');
        $sFile = $sDocRoot . $sImgPath . $sImg;

        $sImgDim = 'width="1" height="1"';

        if ($sImg) {
            if (is_readable($sFile)) {
                $aProp = @getimagesize($sFile);

                if (isset($aProp[3])) {
                    $sImgDim = $aProp[3];
                }
            }
        }

        $sBullet = '<img src="'.$sImgPath.$sImg.'"'
                    .' hspace="'.$sHspace.'" vspace="'.$sVspace.'"'
                    .' '.$sImgDim.' alt="" border="0">';

        // Store variable, make it accessible in helper function
        $this->sBullet = $sBullet;

        // FIX: Relocate the HTML to template. Maybe a TemplateProcessor
        // "prepass" and {var} ... {/var} fetching?
        $sSeparator = '
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td bgcolor="'.$this->Registry->get('COLOR_TUBORG_SHADOW').'"
                ><img src="'.$this->Registry->get('PATH_IMAGES').'0.gif"
                width="1" height="1" alt="" border="0"></td>
            </tr>
            <tr>
              <td bgcolor="'.$this->Registry->get('COLOR_TUBORG_HIGHLIGHT').'"
                ><img src="'.$this->Registry->get('PATH_IMAGES').'0.gif"
                width="1" height="1" alt="" border="0"></td>
             </tr>
          </table>';

        // ----------------------------------------------------------------

        // Get webs as composite
        $Node = $this->Context->getDocumentDAO()->getWebComposite();

        $bSeparate = false;
        $aTplItem = array();

        // Iterate through webs
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {

            if (!$Obj->get('isInMenu') || !$Obj->isReadable()) {
                continue;
            }

            $aItem = array();

            $aItem['INDENT'] = '';
            $aItem['IMAGE']  = $sBullet;
            $aItem['NAME']   = escape($Obj->get('name'));
            $aItem['HREF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                               );

            // Odd separator
            if ($bSeparate) { $aItem['SEPARATOR'] = $sSeparator;  }
            $bSeparate = true;

            // Append item to template items
            $aTplItem[] = $aItem;

            // If item has children, append them to menu items
            $aChildMenu = $this->getMenuItems(
                            $Obj,
                            $this->Registry->get('MENU_MAX_DEPTH')
                          );

            for ($j=0, $m=sizeof($aChildMenu); $j<$m; $j++) {
                $aTplItem[] = $aChildMenu[$j];
            }

        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.front.menu.default.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr);

        // Output result
        echo $sStr;
    }

    /**
     * Get menu items
     *
     * @access  private
     * @param   object
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check the parameter type of "$nDepth"
     */
    private function getMenuItems($Node, $nDepth) {
        static $nLevel = 1;

        $aTplItem = array();

        // Maximum depth
        if ($nLevel >= $nDepth) {
            return $aTplItem;
        }

        // Get all container children
        $Node = $this->Context->getDocumentDAO()->getAllChildren($Node);

        // ---

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {
            if (!$Obj->get('isInMenu') || !$Obj->isReadable()) {
                continue;
            }

            $aItem = array();

            $aItem['INDENT'] = str_repeat('&nbsp;&nbsp;', $nLevel);
            $aItem['IMAGE']  = $this->sBullet;
            $aItem['NAME']   = escape($Obj->get('name'));
            $aItem['HREF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                               );

            $aTplItem[] = $aItem;

            // If item has children, append them to menu items
            $nLevel++;
            $aChildMenu = $this->getMenuItems($Obj, $nDepth);
            $nLevel--;

            for ($j=0, $m=sizeof($aChildMenu); $j<$m; $j++) {
                $aTplItem[] = $aChildMenu[$j];
            }
        }

        return $aTplItem;
    }

    /**
     * Implement observer "update()" method to clean the cache if necessary
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function update() {
        $this->Context->removeFromCache($this);
    }

} // of plugin component

?>
