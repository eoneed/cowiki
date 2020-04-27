<?php

/**
 *
 * $Id: class.PluginLoader.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     plugin
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
 * coWiki - Plugin loader class
 *
 * @package     plugin
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PluginLoader extends Object {
    protected static
        $Instance = null;

    protected
        $Context      = null,
        $aPluginMap   = array(),
        $aPluginName  = array(),
        $aObjMap      = array(),
        $aIsRunning   = array(),
        $aCurrent     = array(),
        $aCurrentId   = -1;

    // --------------------------------------------------------------------

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
     * @todo    [FIX]   put plugin names to session? This is probably faster?
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new PluginLoader;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [FIX]  put plugin names to session? This is probably faster?!
     */
    protected function __construct() {
        $this->Context = RuntimeContext::getInstance();

        // Get absolute path
        $sPluginPath = realpath(getDirName(__FILE__) . '../../plugin');

        $rDir = @opendir($sPluginPath);

        if (!is_resource($rDir)) {
            $this->Context->addError(511, $sPluginPath);
            $this->Context->terminate();
        }

        // Read all plugin names
        while ($sFile = @readdir($rDir)) {
            if ($sFile == '.' || $sFile == '..') {
                continue;
            }

            $bHasPlugin = false;

            // Gather prefixed files only
            if (strtolower(substr($sFile, 0, 13)) == 'class.private') {

                $sName = substr($sFile, 6, strrpos($sFile, '.') - 6);
                $bHasPlugin = true;

            } else if (strtolower(substr($sFile, 0, 12)) == 'class.custom') {

                $sName = substr($sFile, 6, strrpos($sFile, '.') - 6);
                $bHasPlugin = true;
            }

            if ($bHasPlugin) {
                $sTmp = strtolower(str_replace('.', '', $sName));
                $this->aPluginName[$sTmp] = $sName;
                $this->aPluginMap[$sTmp] = $sPluginPath . '/' . $sFile;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin paths
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
    public function getPluginPaths() {
        return $this->aPluginMap;
    }

    // --------------------------------------------------------------------

    /**
     * Load
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
    public function load($sName) {

        // Trim, lowercase and ignore possible dividing dots
        $sName = trim(strtolower(str_replace('.', '', $sName)));

        // Check if a plugin is already running, avoid infinite loops
        if (isset($this->aIsRunning[$sName])) {
            return '';
        }

        // Mark a plugin as "running"
        $this->aIsRunning[$sName] = true;

        // Be optimistic
        $bHasObj = true;

        // Remember plugin name in internal stack (for nested calls)
        $this->nCurrentId++;
        $this->aCurrent[$this->nCurrentId] = $sName;

        // Maybe someone wants to know, what we executing now
        $this->Context->setPluginName($sName);

        // Reset get parameter container for a plugin
        $this->Context->getResponse()->clearGetParams();

        // Start buffering
        ob_start();

        if (!isset($this->aObjMap[$sName])) {

            if ($Obj = $this->instantiate($sName)) {

                // {{{ DEBUG }}}
                Logger::exe('executing '.@$this->aPluginName[$sName]);

                if (!$Obj->init()) {
                    // FIX
                    die('init failed.');
                }

                $this->aObjMap[$sName] = $Obj;

            } else {

                // Plugin not found
                $this->restorePluginName();

                $this->Context->addError(311, $sName);
                $this->Context->resume();
                $bHasObj = false;
            }
        }

        if ($bHasObj) {
            try {
                // Init container stack for current (possibly nested) plugin
                $this->Context->getTemplateRegistry()->init();

                // Perform plugin
                $this->aObjMap[$sName]->perform();

                // Restore template variable container stack for last plugin
                $this->Context->getTemplateRegistry()->restore();

            } catch (Exception $e) {
                if ($this->Context->hasErrors()) {
                    $this->restorePluginName();
                    $this->Context->resume();
                }
                $this->restorePluginName();

                // rethrow
                throw $e;
            }
        }

        $this->restorePluginName();

        $sContent = ob_get_contents(); ob_end_clean();
        return $sContent;
    }

    // --------------------------------------------------------------------

    /**
     * Restore plugin name
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function restorePluginName() {
        // Reset
        if (isset($this->aCurrent[$this->nCurrentId])) {
            if ($this->aIsRunning[$this->aCurrent[$this->nCurrentId]]) {
               unset($this->aIsRunning[$this->aCurrent[$this->nCurrentId]]);
            }
        }

        $this->nCurrentId--;

        if ($this->nCurrentId <= 0) {
            $this->Context->setPluginName('core');
        } else {
            // Retrieve latest plugin name from internal stack
            $this->Context->setPluginName($this->aCurrent[$this->nCurrentId]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Instantiate
     *
     * @access  public
     * @param   string
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function instantiate($sName) {
        $Obj = false;

        $sName = strtolower($sName);

        if (isset($this->aPluginMap[$sName])
            && @include_once($this->aPluginMap[$sName])) {

            $Obj = new $sName;
        }

        return $Obj;
    }

} // of class

?>
