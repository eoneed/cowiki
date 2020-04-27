<?php

/**
 *
 * $Id: class.CustomPager.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomPager
 * #purpose:   Provide a document pager
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      27. July 2004
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
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
 * coWiki - Provide a document pager
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class CustomPager extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

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

    // --------------------------------------------------------------------

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        if (!is_object($Node) || $Node->get('id') == 0) {
            return true;  // leave plugin
        }

        // ----------------------------------------------------------------

        // Set template defaults
        $this->Template->set('TPL_ITEM_PREV_GADGET', '');
        $this->Template->set('TPL_ITEM_PREV_HREF', '');
        $this->Template->set('TPL_ITEM_PREV', '');

        $this->Template->set('TPL_ITEM_NEXT_GADGET', '');
        $this->Template->set('TPL_ITEM_NEXT_HREF', '');
        $this->Template->set('TPL_ITEM_NEXT', '');

        // Get document DAO object
        $DocDAO = $this->Context->getDocumentDAO();

        // Check if this directory branch is readable for current user
        // anyway. Leave this plugin if not. Get all parent nodes (up to
        // the root) first and check readability then.
        $Node = $DocDAO->getNodePath($Node);

        if (!$Node->isReadable()) {
            return false;
        }

        // Get previous readable document node
        $PrevNode = $Node;
        do {
            $PrevNode = $DocDAO->getPrevSiblingItem($PrevNode);
        } while (is_object($PrevNode) && !$PrevNode->isReadable());

        // Get next readable document node
        $NextNode = $Node;
        do {
            $NextNode = $DocDAO->getNextSiblingItem($NextNode);
        } while (is_object($NextNode) && !$NextNode->isReadable());

        // ---

        $bHasItem = false;

        $sImgPath = $this->Registry->get('PATH_IMAGES');
        $sLft =  '<img src="'.$sImgPath.'left.gif"';
        $sLft .= ' alt="'.__('I18N_PREV').'" border="0" />';
        $sRgt =  '<img src="'.$sImgPath.'right.gif"';
        $sRgt .= ' alt="'.__('I18N_NEXT').'" border="0" />';

        if (is_object($PrevNode)) {
            $this->Template->set('TPL_ITEM_PREV_GADGET', $sLft);

            $this->Template->set(
                'TPL_ITEM_PREV_HREF',
                $this->Response->getControllerHref(
                    'node='.$PrevNode->get('id')
                )
            );

            $this->Template->set(
                'TPL_ITEM_PREV',
                escape($PrevNode->get('name'))
            );

            $bHasItem = true;
        }

        // ---

        if (is_object($NextNode)) {
            $this->Template->set('TPL_ITEM_NEXT_GADGET', $sRgt);

            $this->Template->set(
                'TPL_ITEM_NEXT_HREF',
                $this->Response->getControllerHref(
                    'node='.$NextNode->get('id')
                )
            );

            $this->Template->set(
                'TPL_ITEM_NEXT',
                escape($NextNode->get('name'))
            );

            $bHasItem = true;
        }

        // Conditional output
        if ($bHasItem) {
            // Parse template
            $Tpl = $this->Context->getTemplateProcessor();
            echo $Tpl->parse('plugin.pager.tpl');
        }
    }

} // of plugin component

?>
