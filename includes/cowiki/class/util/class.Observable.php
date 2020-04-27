<?php

/**
 *
 * $Id: class.Observable.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Observable class
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
class Observable extends Object { 

    protected
        $aObservers = array();

        
    /**
     * Add observer
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Obj"
     * @todo    [FIX]   Different observers may be different instances of the
     */
    public function addObserver($Obj) {
        // FIX: Different observers may be different instances of the
        // same class. Distinguishing observers by their class name is
        // a fast solution for now. E.g. an oid (UUID) has to be used.
        $this->aObservers[get_class($Obj)] = $Obj;
    }
    
    // --------------------------------------------------------------------

    /**
     * Delete observer
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Obj"
     * @todo    [FIX]   Different observers may be different instances of the
     */
    public function deleteObserver($Obj) {
        // FIX: Different observers may be different instances of the
        // same class. Distinguishing observers by their class name is
        // a fast solution for now. E.g. an oid (UUID) has to be used.
        unset($this->aObservers[get_class($Obj)]);
    }

    // --------------------------------------------------------------------

    /**
     * Delete observers
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function deleteObservers() {
        $this->aObservers = array();
    }

    // --------------------------------------------------------------------

    /**
     * Notify observers
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$ArgumentObj"
     */
    public function notifyObservers() {
        $aKeys = array_keys($this->aObservers);
        
        for ($i=0, $n=sizeof($aKeys); $i<$n; $i++) {
            $this->aObservers[$aKeys[$i]]->update();
        }
    }

} // of class

/*
    I look toward the ocean see reflections on the water
    A glossy illumination of the city lights
    Far out in the waves is the shape of a whale
    Hear it singing its old song like it has always done
    
    It was just a dream which comes and goes
    As do the old seasons while the wind still blows
    
    Open the door, step outside
    Walk on the frozen ground
    Look in the sky - a grey cloud
    Winter is still around
    
    It will take a hundred years waiting for the summer
    And no one just yet knows if we'll hear the whales again
    Nuclear winter covers the world for almost twenty years
    All what we own are dreams and hope for the next generation
    
    Open the door, step outside
    Walk on the frozen ground
    Look in the sky - a grey cloud
    Winter is still around
    
    It was just a dream which comes and goes
    As do the old seasons while the wind still blows
*/

?>
