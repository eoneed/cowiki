<?php

/**
 *
 * $Id: class.Registry.php 27 2011-01-09 12:37:59Z eoneed $
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
 * @version     $Revision: 27 $
 *
 */

/**
 * coWiki - Registry class
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
class Registry extends Object {

    protected static
        $Instance = null,
        $bInitDone = false;

    // --------------------------------------------------------------------

    /**
     * Get instance
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new Registry;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @throws  ConfigNotReadableException
     * @throws  ConfigSyntaxException
     */
    protected function __construct() {
        $Context = RuntimeContext::getInstance();

        try {
            $sPath = realpath(getDirName(__FILE__).'../..') . '/core.conf';

            $Conf = new Config();
            $aConf = $Conf->getIniConfigAsArray($sPath);

        } catch (GenericException $e) {
            throw $e; // rethrow
        }

        // ----------------------------------------------------------------

        // Copy config values to Registry
        foreach ($aConf as $k => $v) {
            $this->set($k, $v);
        }

        // Check configuration file version
        $sVer = $this->get('CONFIG_VERSION');

        if (!$sVer || (int)$sVer != (int)COWIKI_CONFIG_FILE_VERSION) {
            $Context->addError(516);
            $Context->terminate();
        }

        // ----------------------------------------------------------------

        // Fetch template configuration

        $sPath = $Context->getEnvironment()->get('PHP_SELF');

        $sTpl = getRequestBasePath($sPath) . 'tpl/';

        // Concat path to active template
        $sActTpl = $sTpl . $this->get('RUNTIME_TEMPLATE_ACTIVE') . '/';

        // Concat path to default template (for fallback reasons)
        $sDefTpl = $sTpl . 'default/';

        // Basic template path
        $this->set('PATH_TEMPLATE', $sTpl);

        // Active template
        $this->set('PATH_TEMPLATE_ACTIVE', $sActTpl);

        // Fallback template path
        $this->set('PATH_TEMPLATE_DEFAULT', $sDefTpl);

        // Path to active images
        $this->set('PATH_IMAGES', $sActTpl . 'img/');

        // ----------------------------------------------------------------

        // Get template configuration file and define constants
        try {
            $this->getTplConf($sActTpl);
        } catch (GenericException $e) {
            throw $e; // rethrow
        }

        // This need to be the last line in this constructor
        Registry::$bInitDone = true;
    }

    // --------------------------------------------------------------------

    /**
     * Report if the Registry has been completely initialized.
     *
     * @access  public
     * @return  boolean  true if Registry initialized, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     */
    public static function isInitialized() {
        return Registry::$bInitDone;
    }

    // --------------------------------------------------------------------

    /**
     * Get template configuration file and define constants in Registry.
     *
     * @access  public
     * @param   string  Name of template.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @throws  ConfigNotReadableException
     * @throws  ConfigSyntaxException
     */
    public function getTplConf($sActTpl) {
        $Context = RuntimeContext::getInstance();

        try {
            $sPath = $Context->getEnvironment()->get('DOCUMENT_ROOT')
                     . $sActTpl . 'tpl.conf';

            $Conf = new Config();
            $aConf = $Conf->getIniConfigAsArray($sPath);

        } catch (GenericException $e) {
            throw $e; // rethrow
        }

        // ----------------------------------------------------------------

        // Copy config values to Registry.
        foreach ($aConf as $k => $v) {
            $this->set($k, $v);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Overwrite parent method
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function has($sKey) {
        if (isset($this->__aBaseProp[$sKey])) {
            return true;
        }

        if (defined($sKey)) {
            return true;
        }

        return false;
    }

    // --------------------------------------------------------------------

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
        if (isset($this->__aBaseProp[$sKey])) {
            unset($this->__aBaseProp[$sKey]);
        }

        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Overwrite parent method
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function get($sKey) {
        if (isset($this->__aBaseProp[$sKey])) {
            return $this->__aBaseProp[$sKey];
        }

        if (defined($sKey)) {
            $sValue = constant($sKey);
            return $sValue;
        }

        return null;
    }

} // of class

?>