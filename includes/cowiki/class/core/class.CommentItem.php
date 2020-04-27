<?php

/**
 *
 * $Id: class.CommentItem.php 19 2011-01-04 03:52:35Z eoneed $
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
 * The CommentItem carries information about a single forum comment.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class CommentItem extends Object
                  implements Composite {

    protected
        $Items  = null,
        $bSucc  = false,
        $bPred  = false;

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
        $this->set('isContainer', true);
        $this->Items = new Vector();
        $this->set('_depth', 0);
    }

    // --------------------------------------------------------------------

    /**
     * Add an item to the composite comment tree
     *
     * @access  public
     * @param   object  ACommentItem object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function addItem($Item) {
        $Item->set('parent', $this);

        // Find all siblings with an equal depth
        $It = $this->Items->findAllByPropertyValue(
                  '_depth', $this->get('_depth')+1
              )->iterator();

        while($Obj = $It->next()) {
            $Obj->setHasSuccessorItem(true);
        }

        // Internal variable _depth
        $Item->set('_depth', $this->get('_depth') + 1);

        $this->Items->add($Item);
    }

    // --------------------------------------------------------------------

    /**
     * Return the Vector of child items
     *
     * @access  public
     * @return  object  The Vector with all children of this node
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getItems() {
        return $this->Items;
    }

    // --------------------------------------------------------------------

    /**
     * Clean the child vector.
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function removeItems() {
        $this->Items->clear();
    }

    // --------------------------------------------------------------------

    /**
     * Get the number of children in the child Vector.
     *
     * @access  public
     * @return  integer Number of children.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getItemCount() {
        return $this->Items->size();
    }

    // --------------------------------------------------------------------

    /**
     * Check whether this node (composite item) has children or not.
     *
     * @access  public
     * @return  boolean true if this node has children, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function hasItems() {
        return $this->Items->isEmpty() == false;
    }

    // --------------------------------------------------------------------

    /**
     * Set if this node has successor item.
     *
     * @access  protected
     * @param   boolean   true or false
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function setHasSuccessorItem($bFlag) {
        $this->bSucc = $bFlag;
    }

    // --------------------------------------------------------------------

    /**
     * Check if this node (composite item) has a successor item.
     *
     * @access  public
     * @return  boolean true if this node has a successor, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function hasSuccessorItem() {
        return $this->bSucc;
    }

    // --------------------------------------------------------------------

    /**
     * Set if this node has predecessor item
     *
     * @access  protected
     * @param   boolean   true or false
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function setHasPredecessorItem($bFlag) {
        $this->bPred = $bFlag;
    }

    // --------------------------------------------------------------------

    /**
     * Check if this node (composite item) has a predecessor item.
     *
     * @access  public
     * @return  boolean true if this node has a predecessor, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function hasPredecessorItem() {
        return $this->bPred;
    }

} // of class

?>
