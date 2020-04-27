<?php

/**
 *
 * $Id: class.Object.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Object class
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class Object {

    protected
        $__aBaseProp = array();

    /**
     * Set
     *
     * @access  public
     * @param   string
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mValue"
     */
    public function set($sKey, $mValue) {
        $this->__aBaseProp[$sKey] = $mValue;
    }

    /**
     * Has
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function has($sKey) {
        if (isset($this->__aBaseProp[$sKey])) {
            return true;
        }
        return false;
    }

    /**
     * &get
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function get($sKey) {
        if (isset($this->__aBaseProp[$sKey])) {
            return $this->__aBaseProp[$sKey];
        }
        return null;
    }

    /**
     * Is a
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mInput"
     */
    public function isA($mInput) {

        // Check class name
        if (is_string($mInput)) {
            if (class_exists($mInput)
                && $this instanceof $mInput) {
                return true;
            }
        }

        // Check object
        if (class_exists($mInput)
            && $this instanceof $mInput) {
            return true;
        }

        return false;
    }

    /**
     * &get dynamic properties
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
    public function getDynamicProperties() {
        return $this->__aBaseProp;
    }

    /**
     * Clean
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function clean() {
        $this->__aBaseProp = array();
    }

    /**
     * Clone primitives
     *
     * @access  public
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function clonePrimitives() {
        $sName = get_class($this);
        $Obj = new $sName;

        foreach ($this->__aBaseProp as $k => $v) {
            if (is_object($v)) {
                continue;
            }
            $Obj->set($k, $v);
        }

        return $Obj;
    }

    /**
     * Exhibit yourself
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function exhibit() {
        echo '<pre>';
        print_r($this);
        echo '</pre>';
        echo '<hr />';
    }

} // of class

/*
    At the end of days, at the end of time
    When the sun burns out will any of this matter
    Who will be there to remember who we were?
    Who will be there to know that any of this had meaning for us?

    And in retrospect I'll say we've done no wrong
    Who are we to judge what is right and what has purpose for us?
    With designs upon ourselves to do no wrong,
    Running wild unaware of what might come of us

    The sun was born, so it shall die, so only shadows comfort me
    I know in darkness I will find you giving up inside like me
    Each day shall end as it begins and though you're far away from me
    I know in darkness I will find you giving up inside like me

    Without a thought I will see everything eternal,
    Forget that once we were just dust from heavens far
    As we were forged we shall return, perhaps some day
    I will remember us and wonder who we were
*/

?>
