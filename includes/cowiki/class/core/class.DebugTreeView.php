<?php

/**
 *
 * $Id: class.DebugTreeView.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     core
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
 * Output a simple composite tree for debug purposes.
 *
 * Example:
 *   <code>
 *        $Xml = new XmlObjectGraph();
 *        $Graph = $Xml->parse(file_get_contents('recent.rdf'));
 *
 *        $Debug = new DebugTreeView();
 *        $Debug->getTreeView($Graph, XML::ELM_NAME);
 *  </code>
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.2
 */
class DebugTreeView extends AbstractTreeView {

    protected
        $sOutElm = '';

    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    function __construct() {}

    // --------------------------------------------------------------------

    /**
     * Implementor method. As this is a class for debug purposes the tree
     * will be printed additionally.
     *
     * @access  public
     * @param   object  Composite tree
     * @param   integer Name of the element to be shown as node name
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function &getTreeView($Cont, $sOutElm = null) {

        if ($sOutElm == null) {
            $sOutElm = Xml::ELM_NAME;
        }

        if (!defined($sOutElm)) {
            $sOutElm = Xml::ELM_NAME;
        }

        $this->sOutElm = $sOutElm;

        // Fake a root node
        $Root = new DocumentContainer();
        $Root->addItem($Cont);

        echo '<pre>';
        $aItem = $this->getTemplateTreeItems($Root);
        echo '</pre>';

        return $aItem;        
    }

    // --------------------------------------------------------------------

    /**
     * Get tree items from top to bottom. As this is a class for debug
     * purposes the tree will be printed additionally.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    protected function &getTemplateTreeItems($Cont) {
        static $nLevel = 0;

        $nId = 0;
        $It = $Cont->getItems()->iterator();

        while ($Obj = $It->next()) {

            echo $this->buildItem($Obj, $nId, $nLevel);

            $nLevel++;
            $this->getTemplateTreeItems($Obj);
            $nLevel--;
        }

        return $aTplItem;
    }

    // --------------------------------------------------------------------

    /**
     * Create a singe tree "row" with its branch connectors.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    protected function &buildItem($Cont, &$nId, &$nLevel) {

        $sStr = $this->getVertConnectors($Cont, $nLevel);

        if ($Cont->hasSuccessorItem()) {
            $sStr .= '|-- ';
        } else {

            // No branch conector for the very frist entry
            if ($nLevel != 0) {
                $sStr .= '\'-- ';
            }
        }

        $sStr .= $Cont->get($this->sOutElm);
        $sStr .= '<br />';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Get branch connectors
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    protected function &getVertConnectors($Cont, &$nLevel) {
        static $aConn = array();

        $aConn[$nLevel] = $Cont->hasSuccessorItem();

        $sStr = '';
        for ($i=1; $i<$nLevel;  $i++) {
            $sStr .= (isset($aConn[$i]) && $aConn[$i])
                        ? '| &nbsp; '
                        : ' &nbsp; ';
        }

        return $sStr;
    }

} // of class

/*
    They say it's the last song
    They don't know us, you see
    It's only the last song
    If we let it be
*/

?>
