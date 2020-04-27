<?php

/**
 *
 * $Id: class.PrivateFrontMenuBottomDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontMenuBottomDisplay
 * #purpose:   Show all available webs and provide corresponding
 *             links to them
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
 * coWiki - Private front menu bottom display class
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
class PrivateFrontMenuBottomDisplay extends AbstractPlugin
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

        // Get additional plugin parameters
        $sDeli = $this->Context->getPluginParam('delimiter')
                    ? $this->Context->getPluginParam('delimiter')
                    : $this->Registry->get('PAGE_FOOTER_DELI');

        // ----------------------------------------------------------------

        // Get webs as composite
        $Node = $this->Context->getDocumentDAO()->getWebComposite();

        // ----------------------------------------------------------------

        $aTplItem = array();
        $bSeparate = false;

        // Iterate through webs
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {

            if (!$Obj->get('isInFooter') || !$Obj->isReadable()) {
                continue;
            }

            $aItem = array();

            $aItem['NAME'] = escape($Obj->get('name'));
            $aItem['HREF'] = $this->Response->getControllerHref(
                                'node='.$Obj->get('id')
                             );

            // Odd separator
            if ($bSeparate) {
                $aItem['SEPARATOR'] = $sDeli;
            }
            $bSeparate = true;

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.front.menu.bottom.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr);

        // Output result
        echo $sStr;
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
