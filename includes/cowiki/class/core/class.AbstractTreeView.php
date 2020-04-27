<?php

/**
 *
 * $Id: class.AbstractTreeView.php 19 2011-01-04 03:52:35Z eoneed $
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
 * Abstract composite tree view class. The AbstractTreeView provides
 * basic methods for tree visualisation of a composite tree.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @see         Composite
 */
abstract class AbstractTreeView extends Object implements CompositeView {

    protected
        $Context  = null,
        $Registry = null,
        $Request  = null,
        $Response = null;

    protected $aTree = array(
        'MINUS'       => 'tree_minus.gif',
        'MINUS_FIRST' => 'tree_minus_first.gif',
        'MINUS_LAST'  => 'tree_minus_last.gif',
        'PLUS'        => 'tree_plus.gif',
        'PLUS_FIRST'  => 'tree_plus_first.gif',
        'PLUS_LAST'   => 'tree_plus_last.gif',
        'CROSS'       => 'tree_cross.gif',
        'LAST'        => 'tree_last.gif',
        'VERT'        => 'tree_vert.gif',
        'VOID'        => '0.gif',
        'DIR'         => 'dir.gif',
        'DIR_OPEN'    => 'dir_open.gif'
    );

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {
        $this->Context = RuntimeContext::getInstance();

        // Get registry, request and response objects
        $this->Registry = $this->Context->getRegistry();
        $this->Request  = $this->Context->getRequest();
        $this->Response = $this->Context->getResponse();

        $sPath =  $this->Registry->get('PATH_IMAGES');

        foreach ($this->aTree as $k => $v) {
            $this->aTree[$k] = '<img src="' . $sPath . $v .'"'
                              .' width="18" height="20" alt="" border="0">';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Provide a tree view of the given composite tree.
     *
     * @access  public
     * @param   object  Composite tree
     * @param   integer n/a
     * @return  array   The tree as an array, row by row and ready to be
     *                  put to the template processor.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function &getTreeView($Cont, $nCurrId = 0) {

        // Fake a root node
        $Root = new DocumentContainer();
        $Root->addItem($Cont);

        return $this->getTemplateTreeItems($Root, $nCurrId);
    }

    // --------------------------------------------------------------------

    /**
     * Iterate through the composite tree and get the tree items.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &getTemplateTreeItems($Cont, $nCurrId) {
        static
            $aTplItem = array(),
            $nLevel = 0;

        $It = $Cont->getItems()->iterator();

        while ($Obj = $It->next()) {

            $aTplItem[] = $this->buildItem($Obj, $nCurrId, $nLevel);

            $nLevel++;
            $this->getTemplateTreeItems($Obj, $nCurrId);
            $nLevel--;
        }

        return $aTplItem;
    }

    // --------------------------------------------------------------------

    /**
     * Get vertical connectors of the tree branches. This abstract method
     * needs an implmentor in a derived class.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function &buildItem($Cont, &$nCurrId, &$nLevel);

    // --------------------------------------------------------------------

    /**
     * Get vertical connectors of the tree branches. This abstract method
     * needs an implmentor in a derived class.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function &getVertConnectors($Cont, &$nLevel);

} // of class

/*
    Don't say what you mean
    You might spoil your face
    If you walk in the crowd
    You won't leave any trace
    It's always the same
    You're jumping someone else's train
*/

?>
