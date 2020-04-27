<?php

/**
 *
 * $Id: class.PrivateWikiWordAmbiguousDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateWikiWordAmbiguousDisplay
 * #purpose:   Display a list of ambiguous wiki names
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      29. January 2003
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  Private
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Display a list of ambiguous wiki names
 *
 * @package     plugin
 * @subpackage  Private
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateWikiWordAmbiguousDisplay extends AbstractPlugin {

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
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        $sWebName = $this->Request->get('webname');
        $sDocName = $this->Request->get('docname');

        // Anything to do for this plugin?
        if (!$sWebName && !$sDocName) {
            return;
        }

        // Get document DAO
        $DocDAO  = $this->Context->getDocumentDAO();

        // ----------------------------------------------------------------

        $Criteria = new SearchCriteria;

        $Criteria->addExpression(new EqExpression('parent_id', '0'));
        $Criteria->addExpression(new EqExpression('is_dir', 'Y'));
        $Criteria->addExpression(new EqExpression('wikiname', $sWebName));

        // Get one or more (web) results
        $Result = $DocDAO->getUnguardedNodesByCriteria(
                      $Criteria,
                      $this->Context->getCurrentUser()
                  );

        // If document name is given, get all documents within the web
        // with the same wiki name
        if ($sDocName && !$Result->isEmpty()) {

            $nTreeId = $Result->elementAt(0)->get('treeId');

            $Criteria = new SearchCriteria;
            $Criteria->addOrder('is_dir');
            $Criteria->addOrder('views', 'DESC');

            $Criteria->addExpression(new EqExpression('tree_id', $nTreeId));
            $Criteria->addExpression(new EqExpression('wikiname', $sDocName));

            // Get one or more (document) results
            $Result = $DocDAO->getUnguardedNodesByCriteria(
                          $Criteria,
                          $this->Context->getCurrentUser(),
                          array('summary')
                      );
        }

        // ----------------------------------------------------------------

        // Anything to do for this plugin?
        if ($Result->isEmpty()) {
            return;
        }

        // Robots are not permitted to spider this area or to follow links
        $this->Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM_TITLE', __('I18N_AMBI_RESULT'));

        $aTplItem = array();
        $It = $Result->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();

            $aItem['HREF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                               );
            $aItem['NAME']   = escape($Obj->get('name'));
            $aItem['TEASER'] = cutOffWord(escape($Obj->get('summary')), 300);
            $aItem['TYPE']   = $Obj->get('isContainer')
                                 ?  __('I18N_DIR_TOKEN')
                                 :  __('I18N_DOC_TOKEN');

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.wikiword.ambiguous.tpl');
    }

} // of plugin component

/*
    One - You lock the target
    Two - You bait the line
    Three - You slowly spread the net
    And four - You catch the man
*/

?>
