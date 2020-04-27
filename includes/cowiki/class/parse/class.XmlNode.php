<?php

/**
 *
 * $Id: class.XmlNode.php 19 2011-01-04 03:52:35Z eoneed $
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
 * Representation of a XML node within a composite tree.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.1
 *
 * @see         XmlObjectGraph
 */
class XmlNode extends Object implements Composite {

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
     * @since   coWiki 0.3.1
     */
    public function __construct() {
        $this->set('isContainer', true);
        $this->Items = new Vector();
        $this->set('_depth', 0);
    }

    // --------------------------------------------------------------------

    /**
     * Add an item to the composite XML tree
     *
     * @access  public
     * @param   object  A document XmlNode object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
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
     * @since   coWiki 0.3.1
     *
     * @see     getChildNodes
     */
    public function getItems() {
        return $this->Items;
    }

    // --------------------------------------------------------------------

    /**
     * Return the Vector of child items (alias for getItems()). Its more
     * DOM like.
     *
     * @access  public
     * @return  object  The Vector with all children of this node
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     *
     * @see     getItems
     */
    public function getChildNodes() {
        return $this->getItems();
    }

    // --------------------------------------------------------------------

    /**
     * Clean the child vector
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
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
     * @since   coWiki 0.3.1
     */
    public function getItemCount() {
        return $this->Items->size();
    }

    // --------------------------------------------------------------------

    /**
     * Check whether this node (composite item) has children or not.
     *
     * @access  public
     * @return  boolean true if this node has chlidren, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
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
     * @since   coWiki 0.3.2
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
     * @since   coWiki 0.3.1
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
     * @since   coWiki 0.3.2
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
     * @since   coWiki 0.3.1
     */
    public function hasPredecessorItem() {
        return $this->bPred;
    }

    // --------------------------------------------------------------------

    /**
     * Returns the first matching element with a given tag name. The search
     * for the elements always starts at the _current_ node. You will
     * get only the first matching element beneath the current node.
     *
     * @access  public
     * @param   string  Tag name of the XML element you are looking for.
     *                  The search is casesensitve.
     * @return  object  Matching XmlNode, or null
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function getElementByTagName($sTag) {
        return $this->_getElementByTagNameRecusive($this, $sTag);
    }

    // --------------------------------------------------------------------

    /**
     * Lookup a single element recursive, traverse the tree. This method
     * is a helper for getElementsByTagName().
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     *
     * @see     getElementByTagName
     */
    protected function _getElementByTagNameRecusive($Node, $sTag) {
        $Result = null;

        $Items = $Node->getItems();
        $It = $Items->iterator();

        while ($Obj = $It->next()) {

            // Do we have the name what we are looking for?
            if ($Obj->getElementName() == $sTag) {
                $Result = $Obj;
                break;
            }

            // Recursion?
            if ($Obj->hasItems()) {
                $Result = $this->_getElementByTagNameRecusive($Obj, $sTag);
                if (is_object($Result)) {
                    break;
                }
            }
        }

        return $Result;
    }

    // --------------------------------------------------------------------

    /**
     * Returns a Vector of all the elements with a given tag name in the
     * order in which they occur in a preorder traversal of the composite
     * graph. The search for the elements starts always at the _current_
     * node. You will get all matching elements beneath the current node.
     *
     * @access  public
     * @param   string  Tag name of the XML element you are looking for.
     *                  The search is casesensitve.
     * @return  object  Vector containing all matching XmlNodes beneath
     *                  this one.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getElementsByTagName($sTag) {
        $Vector = new Vector();
        return $this->getElementsByTagNameRecusive($Vector, $this, $sTag);
    }

    // --------------------------------------------------------------------

    /**
     * Lookup elements recursive, traverse the tree. This method is a
     * helper for getElementsByTagName().
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @see     getElementsByTagName
     */
    protected function getElementsByTagNameRecusive($Vector, $Node, $sTag) {

        $It = $Node->getItems()->iterator();
        while ($Obj = $It->next()) {

            // Do we have the name what we are looking for?
            if ($Obj->get(Xml::ELM_NAME) == $sTag) {
                $Vector->add($Obj);
            }

            // Recursion?
            if ($Obj->hasItems()) {
                $this->getElementsByTagNameRecusive($Vector, $Obj, $sTag);
            }
        }

        return $Vector;
    }

    // --------------------------------------------------------------------

    /**
     * Returns an attribute value for this XML element.
     *
     * @access  public
     * @param   string  Name of attribute.
     * @return  string  The value if the requested attribute.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getAttribute($sAttrName) {
        if ($aAttr = $this->get(Xml::ELM_ATTR)) {
            if (isset($aAttr[$sAttrName])) {
                return $aAttr[$sAttrName];
            }
        }
        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Set an attribute for this XML element.
     *
     * @access  public
     * @param   string  Name of attribute.
     * @param   string  Value for attribute.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function setAttribute($sAttrName, $sAttrValue) {
        $sAttrValue = escape(unescape($sAttrValue));

        if (!($aAttr = $this->get(Xml::ELM_ATTR))) {
            $aAttr = array();
        }

        $aAttr[$sAttrName] = $sAttrValue;
        $this->set(Xml::ELM_ATTR, $aAttr);
    }

    // --------------------------------------------------------------------

    /**
     * Set element data.
     *
     * @access  public
     * @param   string  The element data.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function setData($sStr) {
        $this->set(Xml::ELM_CDATA, $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Retrieve element data.
     *
     * @access  public
     * @return  string  The element data.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getData() {
        return $this->get(Xml::ELM_CDATA);
    }

    // --------------------------------------------------------------------

    /**
     * Set element PCDATA.
     *
     * @access  public
     * @param   string  The element PCDATA.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     *
     * @todo    FIX: implement
     */
    public function setPCData($sStr) {
        echo 'Not implemented';
    }

    // --------------------------------------------------------------------

    /**
     * Retrieve element PCDATA.
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @todo    FIX: implement
     */
    public function getPCData() {
        echo 'Not implemented';
    }

    // --------------------------------------------------------------------

    /**
     * Sets the XML element name for this node.
     *
     * @access  public
     * @param   string    XML element name.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function setElementName($sStr) {
        $this->set(Xml::ELM_NAME, $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Returns the XML element name for this node.
     *
     * @access  public
     * @return  string    The XML element name for this node.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function getElementName() {
        return $this->get(Xml::ELM_NAME);
    }

} // of class

/*

    Where's the hand that guided me
    My cry within me is let me be
    Crossing the endless seas of pain
    Fighting against torrential rain
    It seems pain is the experience in life
    I'm sorely
    Tried by affliction in my life
    I have to master this impossible situation
    This driving force is my salvation

*/

?>
