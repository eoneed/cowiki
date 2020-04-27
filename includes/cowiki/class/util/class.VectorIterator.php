<?php

/**
 *
 * $Id: class.VectorIterator.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Vector iterator class
 *
 * @package     util
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class VectorIterator extends Object
                     implements CustomIterator {

    protected
        $aArr = array(),
        $bFirst = true,
        $i = 0;

    // --------------------------------------------------------------------

    /**
     *  vector iterator
     *
     * @access  public
     * @param   array
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function VectorIterator(&$aArr) {
        $this->aArr = &$aArr;
        $this->reset();
    }

    // --------------------------------------------------------------------

    /**
     * Reset
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function reset() {
       $this->i = 0;
       $this->bFirst = true;
    }

    // --------------------------------------------------------------------

    /**
     * Has next
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function hasNext() {
        if (isset($this->aArr[$this->i])) {
            return true;
        }
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Next
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function next() {
        if ($this->hasNext()) {

            if ($this->i != 0) {
                $this->bFirst = false;
            }

            $this->i++;
            return $this->aArr[$this->i - 1];
        }
        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Is first
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isFirst() {
        if ($this->bFirst) {
            return true;
        }
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Is last
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isLast() {
        if ($this->hasNext()) {
            return false;
        }
        return true;
    }

}   // of class

?>
