<?php

/**
 *
 * $Id: class.Vector.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     util
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
 * The Vector class implements a growable array of objects. Like an array,
 * it contains components that can be accessed using an integer index.
 * However, the size of a Vector can grow or shrink as needed to accommodate
 * adding and removing items after the Vector has been created.
 *
 * Example:
 *   <code>
 *       $Vector = new Vector();
 *
 *       $Vector->add(new Object());
 *       $Vector->add(new Object());
 *
 *       $It = $Vector->iterator();
 *
 *       while ($Obj = $It->next()) {
 *           $Obj->exhibit();
 *       }
 *   </code>
 *
 * @package     util
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class Vector extends Object implements Collection {

    protected
        $aArr = null;

    /**
     * Vector constructor
     *
     * The contructor initialises the internal data structure.
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {
        $this->aArr = array();
    }

    // --------------------------------------------------------------------

    /**
     * Append the specified element to the end of this Vector.
     *
     * @access  public
     * @param   Object  The element to be appended to this Vector.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     push
     */
    public function add($Obj) {
        $this->aArr[] = $Obj;
    }

    // --------------------------------------------------------------------

    /**
     * Append all of the elements in the specified Collection to the end
     * of this Vector, in the order that they are returned by the specified
     * Collection's Iterator.
     *
     * @access  public
     * @param   Collection  Collection of elements to be appended to
                            this Vector.
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function addAll($Collection) {
        if (!is_object($Collection)) {
            return;
        }

        if (!$Collection->isA('Collection')) {
            return;
        }

        $It->$Collection->iterator();

        while ($Obj = $It->next()) {
            $this->add($Obj);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Test if the specified object is a component in this Vector.
     *
     * @access  public
     * @param   Object    The object that you are looking for.
     * @return  boolean   true if found, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function contains($Obj) {
        return $this->indexOf($Obj) >= 0;
    }

    // --------------------------------------------------------------------

    /**
     * Remove an object from the Vector.
     *
     * Warning: this operation removes only the first object that matches
     * and it of course changes the internal order of the Vector items. Be
     * carefull if you are using indexOf() or elementAt() or similar
     * methods that may change element order.
     *
     * @access  public
     * @param   Object  The object that you want to remove.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     indexOf
     * @see     elementAt
     * @see     setElementAt
     */
    public function remove($Obj) {
        $i = $this->indexOf($Obj);

        if ($i != -1) {
            $this->aArr = array_splice($this->aArr, $i, 1);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Remove all of the elements from this Vector. The Vector will be
     * empty after this call returns.
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function clear() {
        $this->aArr = array();
    }

    // --------------------------------------------------------------------

    /**
     * Return the size of the Vector.
     *
     * @access  public
     * @return  integer The size of the Vector.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function size() {
        return sizeof($this->aArr);
    }

    // --------------------------------------------------------------------

    /**
     * Check if Vector is empty.
     *
     * @access  public
     * @return  boolean   true if Vector is empty, false otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function isEmpty() {
        return sizeof($this->aArr) == 0;
    }

    // --------------------------------------------------------------------

    /**
     * Find a single object by its property value.
     *
     * @access  public
     * @param   string  Property name.
     * @param   mixed   Property value.
     * @return  Object  The object that contains the property. If nothing
     *                  is found this method returns null.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function findByPropertyValue($sKey, $mValue) {
        for ($i=0, $n=sizeof($this->aArr); $i<$n; $i++) {
            if ($this->aArr[$i]->get($sKey) === $mValue) {
                return $this->aArr[$i];
            }
        }

        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Find all objects that match the given property value.
     *
     * @access  public
     * @param   string  Property name.
     * @param   mixed   Property value.
     * @return  Vector  Vector with objects that match the property value.
     *                  If no matches are found, an empty Vector is returned.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function findAllByPropertyValue($sKey, $mValue) {
        $Vector = new Vector();

        for ($i=0, $n=sizeof($this->aArr); $i<$n; $i++) {
            if ($this->aArr[$i]->get($sKey) === $mValue) {
                $Vector->add($this->aArr[$i]);
            }
        }

        return $Vector;
    }

    // --------------------------------------------------------------------

    /**
     * Return the numeric index of given object.
     *
     * @access  public
     * @param   Object    The object you are looking for.
     * @return  integer   The numeric index >= 0 if the Object has been
                          found, -1 will be returned otherwise.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     elementAt
     * @see     setElementAt
     */
    public function indexOf($Obj) {
        for ($i=0, $n=sizeof($this->aArr); $i<$n; $i++) {
            if ($this->aArr[$i] === $Obj) {
                return $i;
            }
        }

        return -1;
    }

    // --------------------------------------------------------------------

    /**
     * Retrieve the Object from a specified numeric index.
     *
     * @access  public
     * @param   integer   The numeric index.
     * @return  mixed     The Object from given numeric index, or null if
     *                    the index is out of Vectors range.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     setElementAt
     */
    public function elementAt($nIndex) {
        if (isset($this->aArr[$nIndex])) {
            return $this->aArr[$nIndex];
        }

        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Set an Object at the specified numeric index. The original Object
     * at the index position will we lost.
     *
     * @access  public
     * @param   Object    The Object to be stored in the Vector.
     * @param   integer   The target index (>=0) where to put the Object in.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     elementAt
     */
    public function setElementAt($Obj, $nIndex) {
        if ($nIndex >= 0 && $nIndex < sizeof($this->aArr)) {
            $this->aArr[$nIndex] = $Obj;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Push the passed Object onto the end of Vector.
     *
     * @access  public
     * @param   Object  The element to be appended to this Vector.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     add
     * @see     pop
     */
    public function push($Obj) {
        array_push($this->aArr, $Obj);
    }

    // --------------------------------------------------------------------

    /**
     * Pop and return the last value of the Vector, shortening the Vector
     * by one element. 
     *
     * @access  public
     * @return  Object  The last Object element of the Vector.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     push
     */
    public function pop() {
        return array_pop($this->aArr);
    }

    // --------------------------------------------------------------------

    /**
     * Prepend passed Object to the front of the Vector.
     *
     * @access  public
     * @param   Object  The element to be prepended to this Vector.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     shift
     */
    public function unshift($Obj) {
        array_unshift($this->aArr, $Obj);
    }

    // --------------------------------------------------------------------

    /**
     * Shift the first value of the Vector off and return it, shortening
     * the Vector by one element.
     *
     * @access  public
     * @return  Object  The shifted Object.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     unshift
     */
    public function shift() {
        return array_shift($this->aArr);
    }

    // --------------------------------------------------------------------

    /**
     * Cut a sequence of Object elements from the beginning of a Vector as
     * specified by the length parameter.
     *
     * @access  public
     * @param   integer   Number of elements to be cut.
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function cut($nLength) {
        $this->aArr = array_slice($this->aArr, 0, $nLength);
    }

    // --------------------------------------------------------------------

    /**
     * Retrieve the Iterator for this Vector.
     *
     * @access  public
     * @return  Iterator  The Iterator instance.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function iterator() {
        return new VectorIterator($this->aArr);
    }

} // of class

/*
    La musica ideas portara 
    y siempre continuara 
    Sonido electronico 
    Decibel sintetico 
*/

?>
