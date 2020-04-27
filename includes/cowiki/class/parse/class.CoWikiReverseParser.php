<?php

/**
 *
 * $Id: class.CoWikiReverseParser.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     parse
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
 * coWiki - coWiki reverse parser class
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class CoWikiReverseParser extends WikiReverseParser {
    protected
        $aWebList = array();

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
            self::$Instance = new CoWikiReverseParser;
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
        $Context = RuntimeContext::getInstance();

        // If link references point to an other web, we will need
        // some web data. Get the names of all webs.
        $Node = $Context->getDocumentDAO()->getWebComposite();

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {
            $this->aWebList[$Obj->get('id')] = $Obj->get('name');
        }
    }

    /**
     * Process link
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processLink(&$sStr) {

        $sStr = preg_replace_callback(
            '=<link strref\="(.*)">(.*)</link>=Umsi',
            array(&$this, 'buildStrRefLink'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<link href\="(.*)">(.*)</link>=Umsi',
            array(&$this, 'buildHyperRefLink'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<link idref\="(.*)">(.*)</link>=Umsi',
            array(&$this, 'buildIdRefLink'),
            $sStr
        );

        return $sStr;
    }

    /**
     * Build id ref link
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
    protected function buildIdRefLink(&$aMatches) {
        $Context = RuntimeContext::getInstance();

        // Get current directory/document object
        $Node = $Context->getCurrentNode();

        // Get referenced node (faster: get required fields only)
        $RefNode = $Context->getDocumentDAO()->getNodeById(
                      $aMatches[1], 'tree_id, parent_id, name'
                   );

        // If no referenced document was found
        if (!is_object($RefNode)) {
            return '['.__('I18N_DOC_DELETED_DOCUMENT').']';
        }

        // Normalize!
        $aMatches[2] = unescape($aMatches[2]);

        $sPrefix = '';

        // Link reference in an other web?
        if ($Node->get('treeId') != $RefNode->get('treeId')) {
            if (isset($this->aWebList[$RefNode->get('treeId')])) {

                $sPrefix =  escape($this->aWebList[$RefNode->get('treeId')]);
                $sPrefix .= '|';

            } else {

                $sPrefix = 'UNKNOWN|';
            }

            // Special treatment if links point to a web
            if ($RefNode->get('isWeb')) {

                // Link directly to a web with an alias
                if ($aMatches[2] != '') {
                   return '(('.$sPrefix.')('.escape($aMatches[2]).'))';
                }

                // Link directly to a web without alias
                return '(('.$sPrefix.'))';
            }
        }

        // ---

        // Generate normal link without alias
        if ($aMatches[2] == '') {
            return
              '(('
                  .$sPrefix
                  .escape($RefNode->get('name'))
              .'))';
        }

        // Generate normal link with an alias
        return '('
                 .'('
                    .$sPrefix
                    .$this->noopDelimiters(escape($RefNode->get('name')))
                 .')'
                 .'('
                    .escape($aMatches[2])
                 . ')'
               .')';

    }

    /**
     * Build hyper ref link
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
    protected function buildHyperRefLink(&$aMatches) {
        if (trim($aMatches[2]) == '') {
            return '((' . $aMatches[1] . '))';
        }
        return '((' . $aMatches[1] . ')(' . $aMatches[2] . '))';
    }

} // of class

?>
