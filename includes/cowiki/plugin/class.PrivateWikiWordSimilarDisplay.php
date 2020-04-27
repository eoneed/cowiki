<?php

/**
 *
 * $Id: class.PrivateWikiWordSimilarDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateWikiWordSimilarDisplay
 * #purpose:   Display a list of documents with similar wiki names
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      28. March 2003
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
 * coWiki - Display a list of documents with similar wiki names
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
class PrivateWikiWordSimilarDisplay extends AbstractPlugin {

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
     */
    public function perform() {

        $aStopWords = array(
            'about', 'also', 'and', 'another', 'any', 'are', 'back',
            'because', 'been', 'being', 'but', 'can', 'could', 'did',
            'each', 'end', 'even', 'for', 'from', 'get', 'had', 'have',
            'her', 'here', 'his', 'how', 'into', 'just', 'may', 'might',
            'much', 'must', 'not', 'off', 'only', 'other', 'our', 'out',
            'should', 'some', 'still', 'such', 'than', 'that', 'the',
            'their', 'them', 'then', 'there', 'these', 'they', 'this',
            'those', 'too', 'try', 'two', 'under', 'was', 'were', 'what',
            'when', 'where', 'which', 'while', 'who', 'why', 'will',
            'with', 'within', 'without', 'would', 'you', 'your'
        );

        // Get document DAO
        $DocDAO  = $this->Context->getDocumentDAO();

        // Get color from Registry
        $sColor = $this->Registry->get('COLOR_FOUND');

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        $this->Template->set(
            'TPL_ITEM_DOCUMENT',
            escape($Node->get('name'))
        );

        // ----------------------------------------------------------------

        $aWords = $this->createWordList($Node->get('name'));
        $sPattern = join('|', $aWords);

        $Criteria = new SearchCriteria();
        $Criteria->addOrder('is_dir');

        $Disjunction = new Disjunction();

        for ($i=0, $n=sizeof($aWords); $i<$n; $i++) {
            if (in_array($aWords[$i], $aStopWords)) {
                continue;
            }

            $sLike = '%' . $aWords[$i] . '%';
            $Disjunction->add(new LikeExpression('name', $sLike));
        }

        $Criteria->addExpression($Disjunction);

        // Get one or more (document) results
        $Result = $DocDAO->getUnguardedNodesByCriteria(
                      $Criteria,
                      $this->Context->getCurrentUser(),
                      array('summary')
                  );

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM_TITLE', __('I18N_SIMI_RESULT'));

        $aTplItem = array();
        $It = $Result->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();

            $aItem['HREF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                               );
            $aItem['TEASER'] = cutOffWord(escape($Obj->get('summary')), 300);
            $aItem['TYPE']   = $Obj->get('isContainer')
                                 ?  __('I18N_DIR_TOKEN')
                                 :  __('I18N_DOC_TOKEN');

            // Prepare name (title)
            $sName = escape($Obj->get('name'));
            $sName = preg_replace(
                            '#('.$sPattern.')#UiS',
                            '<span style="color:'.$sColor.'">\1</span>',
                            $sName
                        );
            $aItem['NAME'] = $sName;

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.wikiword.similar.tpl');
    }

    /**
     * Create word list
     *
     * @access  protected
     * @param   string
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function createWordList($sName) {

        // Cleanup, remove undesired characters
        $sName = preg_replace('#([^a-zA-Z0-9\x80-\xFF ]+)#', ' ', $sName);

        // Split name into words
        $aWords = array_unique(explode(' ', trim($sName)));

        $aNewWords = array();
        $sAbbrev = '';
        $sPattern = '';

        // ----------------------------------------------------------------

        // Gather first characters (as abbreviations)
        foreach ($aWords as $sWord) {
            if (strlen($sWord) > 0) {
                $sAbbrev .= $sWord{0};
            }
        }

        // ----------------------------------------------------------------

        for ($i=0, $n=sizeof($aWords); $i<$n; $i++) {
            if (!isset($aWords[$i]) || strlen($aWords[$i]) == 0) {
                continue;
            }

            $sWord = trim($aWords[$i]);

            // Get rid of non alphanumeric characters (keep 8-bit chars)
            $sWord = preg_replace('#[^A-Za-z\x80-\xFF]#', '', $sWord);

            // Remove trailing (plural) "ies"
            if (substr($sWord, -3) == 'ies') {
                $sWord = substr($sWord, 0, -3);
            }

            // Remove trailing "ing" for search
            if (substr($sWord, -3) == 'ing') {
                $sWord = substr($sWord, 0, -3);
            }

            // Remove trailing (plural) "s"
            $sWord = preg_replace('#s+$#', '', $sWord);

            // Remove trailing "ly"
            if (substr($sWord, -2) == 'ly') {
                $sWord = substr($sWord, 0, -2);
            }

            // Remove trailing (singular) "y"
            if (substr($sWord, -1) == 'y') {
                $sWord = substr($sWord, 0, -1);
            }

            // Remove trailing "able" for search
            if (substr($sWord, -4) == 'able') {
                $sWord = substr($sWord, 0, -4);
            }

            if (strlen($sWord) < 3) {
                continue;
            }

            $aNewWords[] = $sWord;
        }

        if (strlen($sAbbrev) > 2) {
            $aNewWords[] = $sAbbrev;
        }

        return $aNewWords;
    }

} // of plugin component

?>
