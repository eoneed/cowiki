<?php

/**
 *
 * $Id: class.PersistentObservable.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Persistent observable class
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
class PersistentObservable extends Observable { 

    protected
        $aObservers = array();

    // --------------------------------------------------------------------

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
     */
    public function addObserver($Obj) {
        $this->loadObservers();

        if (!isset($this->aObservers[get_class($Obj)])) {
            $this->aObservers[get_class($Obj)] = get_class($Obj);
            $this->storeObservers();
        }
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
     */
    public function deleteObserver($Obj) {
        $this->loadObservers();
        if (isset($this->aObservers[get_class($Obj)])) {
            unset($this->aObservers[get_class($Obj)]);
        }
        $this->storeObservers();
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
        $this->storeObservers();        
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
        $PluginLoader = RuntimeContext::getInstance()->getPluginLoader();

        $this->loadObservers();

        $aKeys = array_keys($this->aObservers);

        for ($i=0, $n=sizeof($aKeys); $i<$n; $i++) {

            // {{{ DEBUG }}}
            Logger::info('Notifying observer "'.$aKeys[$i].'"');

            // Get an instance of a plugin without performing its task
            $Obj = $PluginLoader->instantiate($aKeys[$i]);

            // Did we got a plugin returned?
            if (is_object($Obj) && $Obj->isA('AbstractPlugin')) {

                // Init plugin
                $Obj->init(); 

                // Call observer "update()" method in plugin
                $Obj->update();

            } else {

                // {{{ DEBUG }}}
                Logger::err('Couldn\'t notify "'.$aKeys[$i].'"');
            }
            
        }
    }

    // --------------------------------------------------------------------

    /**
     * Plugin observers are persistent
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function loadObservers() {

        $Context = RuntimeContext::getInstance();

        try {
            $sContent = $Context->readTempFile('observerplugins');
            $aTmp = explode(',', $sContent);

            foreach ($aTmp as $k => $v) {
                $this->aObservers[$v] = $v;
            }

        } catch (IOException $e) {
            // FIX: do not swallow, rethrow
        }
    }

    // --------------------------------------------------------------------

    /**
     * Plugin observers are persistent
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function storeObservers() {
        RuntimeContext::getInstance()->writeTempFile(
            'observerplugins',
            join(',', $this->aObservers)
        );
    }

} // of class

?>
