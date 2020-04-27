<?php

/**
 *
 * $Id: class.DocumentItem.php 19 2011-01-04 03:52:35Z eoneed $
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
 * The DocumentItem carries information about a coWiki document.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class DocumentItem extends CompositeGuardedItem
                   implements Composite {

    protected
        $Items = null,
        $bSucc = false,
        $bPred = false;

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
        $this->set('isContainer', false);
        $this->Items = new Vector();
    }

    // --------------------------------------------------------------------

    /**
     * Add an item to the composite document tree
     *
     * @access  public
     * @param   object  A DocumentItem object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function addItem($Item) {
        $Item->set('parent', $this);
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

    // ====================================================================

    /**
     * Sort a child item up.
     *
     * @access  public
     * @param   integer The 'id' of the child item to be moved/sorted up.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function sortItemUp($nId) {
        $Obj = $this->Items->findByPropertyValue('id', $nId);

        if (is_object($Obj)) {
            $i = $this->Items->indexOf($Obj);

            if ($i > 0) {
                $Item = $this->Items->elementAt($i-1);
                $this->Items->setElementAt($Obj, $i-1);
                $this->Items->setElementAt($Item, $i);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Sort a child item down.
     *
     * @access  public
     * @param   integer The 'id' of the child item to be moved/sorted down.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function sortItemDown($nId) {
        $Obj = $this->Items->findByPropertyValue('id', $nId);

        if (is_object($Obj)) {
            $i = $this->Items->indexOf($Obj);

            if ($i < $this->Items->size() - 1) {
                $Item = $this->Items->elementAt($i+1);
                $this->Items->setElementAt($Obj, $i+1);
                $this->Items->setElementAt($Item, $i);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Determine if this composite item is recoverable.
     *
     * @access  public
     * @return  boolean true if this item is recoverable, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function isRecoverable() {

        // Is a historical node?
        if (!$this->get('histId') > 0) {
            return false;
        }

        // Node is readable for current user?
        if (!$this->isReadable()) {
            return false;
        }

        // Parent exists?
        if (!$Parent = $this->get('parent')) {
            return false;
        }

        if ($Original = $this->get('original')) {
            // Original node is editable?
            if (!$Original->isEditable()) {
                return false;
            }
        } else {
            // Parent of historical node is writable?
            if (!$this->isParentWritable()) {
                return false;
            }
        }

        return true;
    }

} // of class

?>
