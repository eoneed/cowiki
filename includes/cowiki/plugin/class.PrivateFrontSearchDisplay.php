<?php

/**
 *
 * $Id: class.PrivateFrontSearchDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontSearchDisplay
 * #purpose:   Display a list of search results
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      19. August 2003
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
 * coWiki - Display a list of search results
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
class PrivateFrontSearchDisplay extends AbstractPlugin {

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
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   this solution in not very clever
     */
    public function perform() {

        // Get document DAO
        $DocDAO  = $this->Context->getDocumentDAO();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // ---

        // Get color from Registry
        $sColor = $this->Registry->get('COLOR_FOUND');

        // Prepare word list
        $sQuery = trim($this->Request->get('q'));

        // Remove plus signs in filter query
        $sQuery = str_replace('+', ' ', $sQuery);

        // Exclude illegal characters
        $sQuery = preg_replace('#[^\w-_.]#U', ' ', $sQuery);

        // Remove multiple spaces in filter query
        $sQuery = preg_replace('# +#U', ' ', $sQuery);

        // Split query and prepare it for further use
        $aQuery = explode(' ', $sQuery);

        // Remove all too short words
        $aWords = array();
        for ($i=0, $n=sizeof($aQuery); $i<$n; $i++) {
            if (strlen($aQuery[$i]) < 3) {
                continue;
            }
            $aWords[] = $aQuery[$i];
        }

        // ---

        // These fields are important for search
        $aFields = array('name', 'summary', 'keywords');

        // Get criteria result
        $Result = $DocDAO->getUnguardedNodesByCriteria(
                      $this->getCriteria($aFields, $aWords),
                      $CurrUser,
                      array('summary')
                  );

        // ----------------------------------------------------------------

        $aTplItem = array();

        $It = $Result->iterator();
        while ($Obj = $It->next()) {

            $aItem = array();

            $aItem['VIEWS'] = $Obj->get('views');
            $aItem['HREF']  = $this->Response->getControllerHref(
                                 'node='.$Obj->get('id')
                              );

            // ---

            // Calculate relevance
            $nRel = 0;
            for ($i=0, $n=sizeof($aWords); $i<$n; $i++) {

                $sWord = strtolower($aWords[$i]);

                $sName     = strtolower($Obj->get('name'));
                $sKeywords = strtolower($Obj->get('keywords'));
                $sSummary  = strtolower($Obj->get('summary'));

                // Name is most relevant
                $nTmp = substr_count($sName, $sWord);
                if ($nTmp) {  $nRel += 20 + $nTmp;  }

                // Keywords count twice
                $nTmp = substr_count($sKeywords, $sWord);
                if ($nTmp) {  $nRel += 1 + $nTmp; }

                $nRel += substr_count($sSummary , $sWord);
            }

            $aItem['RELEVANCE'] = $nRel;

            // ---

            // Prepare summary teaser
            $sPattern = join('|', $aWords);

            $sSummary = cutOffWord(escape($Obj->get('summary')), 350);
            $sSummary = preg_replace(
                            '#('.$sPattern.')#UiS',
                            '<span style="color:'.$sColor.'">\1</span>',
                            $sSummary
                        );
            $aItem['TEASER'] = $sSummary;

            // ---

            // Prepare name (title)
            $sName = escape($Obj->get('name'));
            $sName = preg_replace(
                            '#('.$sPattern.')#UiS',
                            '<span style="color:'.$sColor.'">\1</span>',
                            $sName
                        );
            $aItem['NAME'] = $sName;

            // ---

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        // ----------------------------------------------------------------

        // FIX: this solution in not very clever

        if (sizeof($aTplItem)) {
            // Sort by relevance
            foreach($aTplItem as $v) {
                $aSortBy[] = $v['RELEVANCE'];
            }
            array_multisort($aSortBy, SORT_DESC, $aTplItem);

            // Set count
            for ($i=0, $n=sizeof($aTplItem); $i<$n; $i++) {
                $aTplItem[$i]['COUNT'] = $i+1;
            }
        }

        // ----------------------------------------------------------------

        $this->Template->set(
            'TPL_SEARCH_QUERY',
            escape(trim($this->Request->get('q')))
        );

        if ($Result->size() > 0) {
            $this->Template->set(
                'TPL_SEARCH_MATCHES',
                sprintf(__('I18N_SEARCH_MATCHES'), $Result->size())
            );
        } else {
            $this->Template->set(
                'TPL_SEARCH_MATCHES',
                __('I18N_SEARCH_NO_RESULT')
            );
        }

        if (!empty($aTplItem)) {
            $this->Template->set('TPL_ITEM', $aTplItem);
        }

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.search.display.tpl');
    }

    /**
     * Get criteria
     *
     * @access  protected
     * @param   array
     * @param   array
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function getCriteria($aFields, $aWords) {

        $Criteria = new SearchCriteria;

        // Check if we have words in query
        if (sizeof($aWords)) {

            // If query filter is not empty, add expression
            $Criteria->addExpression(
                new NotEqExpression('is_dir', 'Y')
            );

            // Create new junctions
            $Junction = $this->prepareFilter($aFields, $aWords);
            $Criteria->addExpression($Junction);

            // Remember filter string
            $Criteria->setQueryFilter(join(' ', $aWords));

        } else {

            // Create a criteria that will return no results
            $Criteria->addExpression(
                new EqPropertyExpression('1', '0')
            );

        }

        return $Criteria;
    }

    /**
     * Prepare filter
     *
     * @access  protected
     * @param   array
     * @param   array
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function prepareFilter($aFields, $aQuery) {

        $aPositive = array();
        $aNegative = array();

        // Divide into positive and negative queries
        for ($i=0, $n=sizeof($aQuery); $i<$n; $i++) {
            if ($aQuery[$i] == '-') {
                continue;
            }

            if ($aQuery[$i]{0} == '-') {
                $aNegative[] = substr($aQuery[$i], 1);
            } else {
                $aPositive[] = $aQuery[$i];
            }
        }

        // ---

        // Generate a appropriate criteria expression.
        $PosJunc = new Disjunction();
        $NegJunc = new Conjunction();

        // Posititives
        for ($i=0, $n=sizeof($aFields); $i<$n; $i++) {

            $NewJunc = new Conjunction();

            // Process the search query parts (positive)
            for ($j=0, $m=sizeof($aPositive); $j<$m; $j++) {
                $NewJunc->add(
                    new LikeExpression(
                        $aFields[$i],
                        '%'.$aPositive[$j].'%'
                    )
                );
            }

            $PosJunc->add($NewJunc);
        }

        // Negatives
        for ($i=0, $n=sizeof($aFields); $i<$n; $i++) {

            // Process the search query parts (positive)
            for ($j=0, $m=sizeof($aNegative); $j<$m; $j++) {
                $NegJunc->add(
                    new NotLikeExpression(
                        $aFields[$i],
                        '%'.$aNegative[$j].'%'
                    )
                );
            }
        }

        // ---

        $Junc = new Conjunction();
        $Junc->add($PosJunc);
        $Junc->add($NegJunc);

        return $Junc;
    }

} // of plugin component

/*

    In my heart is no place for you
    And in my mind is no space for you
    The exit already melted away
    And now there's nothing left to say

*/

?>
