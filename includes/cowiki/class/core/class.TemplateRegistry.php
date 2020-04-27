<?php

/**
 *
 * $Id: class.TemplateRegistry.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Template registry class
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
class TemplateRegistry extends Object {

    protected static
        $Instance = null;

    protected
        $nStackId = -1,
        $aStack = array();

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
            self::$Instance = new TemplateRegistry;
        }
        return self::$Instance;
    }

    /**
     * Init
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function init() {
        $this->nStackId++;
        $this->clean();
    }

    /**
     * Restore
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function restore() {
        $this->nStackId--;
    }

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
        $this->aStack[$this->nStackId][$sKey] = $mValue;
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
        if (isset($this->aStack[$this->nStackId][$sKey])) {
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
        if (isset($this->aStack[$this->nStackId][$sKey])) {
            return $this->aStack[$this->nStackId][$sKey];
        }

        if (defined($sKey)) {
            $sValue = constant($sKey);
            return $sValue;
        }

        return null;
    }
    

    /**
     * Remove
     *
     * @access  public
     * @param   string
     * @return  null
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function remove($sKey) {
        if (isset($this->aStack[$this->nStackId][$sKey])) {
            unset($this->aStack[$this->nStackId][$sKey]);
        }

        return null;
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
        $this->aStack[$this->nStackId] = array();
    }

} // of class

?>
