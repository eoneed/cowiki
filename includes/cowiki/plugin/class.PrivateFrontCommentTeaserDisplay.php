<?php

/**
 *
 * $Id: class.PrivateFrontCommentTeaserDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontCommentTeaserDisplay
 * #purpose:   Display a teaser with the latest comments (postings)
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      04. May 2003
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
 * coWiki - Display a teaser with the latest comments (postings)
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
class PrivateFrontCommentTeaserDisplay extends AbstractPlugin {

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Comment data access
        $ComDAO = $this->Context->getCommentDAO();

        // ----------------------------------------------------------------

        // Check if node is commentable
        if (!$Node->get('isCommentable')) {
            return true;
        }

        $sQueryStr = 'node='.$Node->get('id').'&';

        $this->Template->set(
            'TPL_ITEM_WRITE_HREF',
            $this->Response->getControllerHref(
                $sQueryStr . 'comid=0&cmd='.CMD_NEWCOM
            )
        );

        // ---

        $this->Template->set(
            'TPL_ITEM_COUNT',
            $ComDAO->getCommentCount($Node)
        );

        // ---

        $this->Template->set(
            'TPL_ITEM_COMMENTS',
            $this->Response->getControllerHref(
                'node='.$Node->get('id').'&cmd='.CMD_LISTCOM
            )
        );

        // ---

        $aTplItem = array();
        $It = $ComDAO->getRecentCommentsForNode($Node)->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();
            $aItem['SUBJECT'] = escape(cutOff($Obj->get('subject'), 40));
            $aItem['HREF']    = $this->Response->getCommentHref(
                                    $Obj->get('id'),
                                    'node='.$Node->get('id')
                                );
            $aItem['NAME']    = escape($Obj->get('authorName'));
            $aItem['TIME']    = $this->Context->makeDateTimeRelative(
                                  $Obj->get('created')
                                );

            $aTplItem[] = $aItem;
        }

        if (!empty($aTplItem)) {
            $this->Template->set('TPL_ITEM', $aTplItem);
        }

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.comment.teaser.tpl');
    }

} // of plugin component

?>
