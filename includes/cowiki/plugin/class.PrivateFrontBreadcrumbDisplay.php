<?php

/**
 *
 * $Id: class.PrivateFrontBreadcrumbDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontBreadcrumbDisplay
 * #purpose:   Display breadcrumb path to this document node
 * #param:     delimiter   Path delimiter, eg. "&gt;" or "/" (optional,
 *                         default is set in tpl.conf files)
 * #param:     maxdepth    Max. breadcrumb path depth (optional,
 *                         default is set in tpl.conf files)
 * #caching:   not used
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
 * coWiki - Display breadcrumb path to this document node
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
class PrivateFrontBreadcrumbDisplay extends AbstractPlugin {

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
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();
        if (!is_object($Node)) {
            return true;  // leave plugin
        }

        // Check validity and user access
        if (!$Node->isReadable()) {
            return true;  // leave plugin
        }

        $Node = $this->Context->getLeafNode($Node);

        // ----------------------------------------------------------------

        // Get additional plugin parameters
        $sDeli = $this->Context->getPluginParam('delimiter')
                    ? $this->Context->getPluginParam('delimiter')
                    : $this->Registry->get('PAGE_BREADCRUMB_DELI');

        $nDepth = $this->Context->getPluginParam('maxdepth')
                    ? abs((int)$this->Context->getPluginParam('maxdepth'))
                    : $this->Registry->get('PAGE_BREADCRUMB_DEPTH');

        // ----------------------------------------------------------------

        // detect List Directory view
        if ($this->Request->get('cmd') == CMD_SHOWDIR) {
            // go to parent directory node unless $Node is one
            if ($Node->get('isContainer') === false) {
                $Node = $Node->get('parent');
            }
        }

        // ----------------------------------------------------------------

        $this->Template->set('TPL_CURRENT_ITEM', escape($Node->get('name')));

        // ----------------------------------------------------------------

        $aTplItem = array();

        $nCount = 0;
        while (true) {
            if (!$Node = $Node->get('parent')) {
                break;
            }

            $aItem = array();

            // Maximum depth reached?
            $nCount++;

            if ($nCount >= $nDepth) {
                if ($Node->get('isComment')) {
                    $aItem['HREF'] = $this->Response->getControllerHref(
                                        'node='.$Node->get('id')
                                        .'&cmd='. CMD_LISTCOM
                                    );
                } else {
                    $aItem['HREF'] = $this->Response->getControllerHref(
                                        'node='.$Node->get('id')
                                    );
                }

                $aItem['NAME'] = '..';
                $aItem['DELI'] = '&nbsp;<tt>'.$sDeli.'</tt>';

                $aTplItem[] = $aItem;
                break;
            }

            if ($Node->get('isComment')) {
                $aItem['HREF'] = $this->Response->getControllerHref(
                                    'node='.$Node->get('id')
                                    .'&cmd='. CMD_LISTCOM
                                 );
            } else {
                $aItem['HREF'] = $this->Response->getControllerHref(
                                    'node='.$Node->get('id')
                                 );
            }

            $aItem['NAME'] = escape($Node->get('name'));
            $aItem['DELI'] = '&nbsp;<tt>'.$sDeli.'</tt>';

            $aTplItem[] = $aItem;
        }

        // TPL_ITEMs has been recorded in reverse order!
        $this->Template->set('TPL_ITEM', array_reverse($aTplItem));

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.breadcrumb.tpl');
    }

} // of plugin component

?>
