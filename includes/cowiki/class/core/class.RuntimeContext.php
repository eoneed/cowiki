<?php

/**
 *
 * $Id: class.RuntimeContext.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Runtime context class
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
class RuntimeContext extends Object {

    protected static
        $Instance = null;

    private
        $Registry       = null,
        $DAOFactory     = null,
        $UserDAO        = null,
        $CurrUser       = null,
        $CurrNode       = null,
        $RootNode       = null,
        $AuthManager    = null,

        $sPluginName    = 'core',
        $aPluginParam   = array(),
        $aError         = array(),
        $aVarCont       = array(),
        $aSessCont      = array(),

        $bInErrorState  = false;

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
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new RuntimeContext;
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
     */
    protected function __construct() {
        $this->DAOFactory = DAOFactory::getInstance();

        if (isset($_SESSION['g_aSess'])) {
            $this->aSessCont = &$_SESSION['g_aSess'];
        } else {
            $_SESSION['g_aSess'] = &$this->aSessCont;
        }
    }

    // === ESSENTIAL OBJECT ACCESSORS =====================================

    /**
     * Get registry
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
    public function getRegistry() {
        if (!$this->Registry) {

            // Avoid infinite loop if instantiation of Registry fails
            $this->bInErrorState = true;

            try {
                $this->Registry = Registry::getInstance();
            } catch (GenericException $e) {
                throw $e; // rethrow
            }

            $this->bInErrorState = false;
        }

        return $this->Registry;
    }

    // --------------------------------------------------------------------

    /**
     * Get environment
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
    public function getEnvironment() {
        return Environment::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get request
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
    public function getRequest() {
        return HttpRequest::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get response
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
    public function getResponse() {
        return HttpResponse::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get template registry
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
    public function getTemplateRegistry() {
        return TemplateRegistry::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get document dao
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
    public function getDocumentDAO() {
        return $this->DAOFactory->createDocumentDAO();
    }

    // --------------------------------------------------------------------

    /**
     * Get media dao
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
    public function getMediaDAO() {
        return MediaDAO::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get referrer dao
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
    public function getReferrerDAO() {
        return ReferrerDAO::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get comment dao
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
    public function getCommentDAO() {
        return CommentDAO::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get shout box dao
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
    public function getShoutBoxDAO() {
        return $this->DAOFactory->createShoutBoxDAO();
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin loader
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
    public function getPluginLoader() {
        return PluginLoader::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get template processor
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
    public function getTemplateProcessor() {
        return TemplateProcessor::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get layouter
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
    public function getLayouter() {
        return Layouter::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get utility
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
    public function getUtility() {
        return Utility::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Get auth manager
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getAuthManager() {
        if (is_object($this->AuthManager)) {
            return $this->AuthManager;
        }

        $this->AuthManager = new AuthManager;

        $sClass = $this->Registry->get('.AUTH_HANDLER');

        $Callback = new $sClass;
        $Callback->init();

        $this->AuthManager->registerCallback($Callback);
        return $this->AuthManager;
    }

    // --------------------------------------------------------------------

    /**
     * Get user dao
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
    public function getUserDAO() {
        if (!$this->UserDAO) {
            $this->UserDAO = $this->DAOFactory->createUserDAO();
        }
        return $this->UserDAO;
    }

    // === PROPERTY ACCESSORS AND MUTATORS ==============================

    /**
     * Set current user
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$UserObj"
     */
    public function setCurrentUser($UserObj) {
        $this->CurrUser = $UserObj;
    }

    // --------------------------------------------------------------------

    /**
     * Get current user
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
    public function getCurrentUser() {
        return $this->CurrUser;
    }

    // --------------------------------------------------------------------

    /**
     * Set current node
     *
     * @access  public
     * @param   object
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function setCurrentNode($Node){
        $this->CurrNode = $Node;

        if (!is_object($Node)) {
            return false;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get current node
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
    public function getCurrentNode() {
        return $this->CurrNode;
    }

    // --------------------------------------------------------------------

    /**
     * Get root node
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function getRootNode($Node) {
        if ($Node->get('parentId') == 0) {
            return $Node;
        }

        return $this->getRootNode($Node->get('parent'));
    }

    // --------------------------------------------------------------------

    /**
     * Get leaf node
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function getLeafNode($Node) {
        $Items = $Node->getItems();

        if ($Items->isEmpty()) {
            return $Node;
        }

        return $this->getLeafNode($Items->iterator()->next());
    }

    // === PLUGINS ========================================================

    /**
     * Get plugin interface version
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
    public function getPluginInterfaceVersion() {
        return (int)$this->getRegistry()->
                get('COWIKI_PLUGIN_INTERFACE_VERSION');
    }

    // --------------------------------------------------------------------

    /**
     * Init plugin
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
    public function initPlugin($sVersion) {
        if ($this->getPluginInterfaceVersion() != (int)$sVersion) {
            $sMsg = $this->getPluginName()
                    . ' is version ' . $sVersion
                    . ' - required version is '
                    . $this->getPluginInterfaceVersion();
            $this->addError(312, $sMsg);

            return $this->resume();
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Set plugin name
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function setPluginName($sName) {
        $this->sPluginName = $sName;
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin name
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
    public function getPluginName() {
        return $this->sPluginName;
    }

    // --------------------------------------------------------------------

    /**
     * Get submit id
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
    public function getSubmitId() {
        return strtoupper(md5($this->sPluginName));
    }

    // === CACHING ========================================================

    /**
     * Get temp file name path
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   make temp file a MD5 string only, this is sufficient
     */
    public function getTempFileNamePath() {
        static $sPath = null;

        if (!$sPath) {
            // Get temporary file location, add slash if necessary
            $sPath = $this->getRegistry()->get('PATH_TEMP') ;

            if ($sPath != ''
             && file_exists($sPath)) {

                // make the temp path absolute
                if ($sCwd = getcwd()) {
                    chdir($sPath);
                    $sPath = getcwd();
                    chdir($sCwd);
                }
                else {
                    $sPath = $this->findTempFilePath();
                }
            }
            else {
                $sPath = $this->findTempFilePath();
            }

            if (substr($sPath, -1) != '/') {
                $sPath .= '/';
            }

            $sCowikiRoot = $this->getEnvironment()->get('COWIKI_ROOT');

            // FIX: make temp file a MD5 string only, this is sufficient
            $sPath .= $this->getRegistry()->get('COWIKI_NAME') . '_';
            $sPath .= strtoupper(substr(md5($sCowikiRoot), 0, 8)) . '_';
        }

        return $sPath;
    }

    // --------------------------------------------------------------------

    /**
     * findTempFilePath
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     *
     * @todo    This is a quickhack if the tmp path does not exists
     *          Make it more harmonic with the rest
     */
    protected function findTempFilePath() {

        if (function_exists('sys_get_temp_dir')) {
            return sys_get_temp_dir();
        }

        // Try to get from environment variable
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        else if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        else if (!empty($_ENV['TEMP']) ) {
            return realpath($_ENV['TEMP']);
        }
        // Detect by creating a temporary file
        else {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ($temp_file) {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            }
            else {
                return false;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get temp file name
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function getTempFileName($sClass, $sIdent = 'none') {

        $sFile = $sClass . '_';
        $sFile .= $sIdent . '_';
        $sFile .= $this->getRegistry()->get('RUNTIME_TEMPLATE_ACTIVE');
        $sFile .= '_' . $this->getCurrentUser()->get('userId');
        $sFile .= '_' . $this->Registry->get('RUNTIME_LANGUAGE_LOCALE');

        return $this->getTempFileNamePath() . strtolower($sFile);
    }

    // --------------------------------------------------------------------

    /**
     * Must return a string, even if it is empty
     *
     * @access  public
     * @param   object
     * @param   string
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Obj"
     * @todo    [D11N]  Check the parameter type of "$nExp"
     */
    public function getFromCache($Obj, $sIdent = 'none', $nExp = 86400) {

        if ($this->getRegistry()->get('RUNTIME_CACHE_ENABLE')) {
            $sFile = $this->getTempFileName(get_class($Obj), $sIdent);

            if (is_readable($sFile)) {
                if (filemtime($sFile) + $nExp > time()) {

                    // Return at least an empty string
                    return @file_get_contents($sFile) . '';
                }
            }
        }

        return '';
    }

    // --------------------------------------------------------------------

    /**
     * Put data to cache
     *
     * @access  public
     * @param   object
     * @param   object
     * @param   string
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Obj"
     * @todo    [D11N]  Check the parameter type of "$mData"
     */
    public function putToCache($Obj, $mData, $sIdent = 'none') {

        if ($this->getRegistry()->get('RUNTIME_CACHE_ENABLE')) {
            $sFile = $this->getTempFileName(get_class($Obj), $sIdent);

            try {
                $Out = new FileOutputStream($sFile);
                $Out->write($mData);
                $Out->close();

            } catch (Exception $e) {
                // Swallow exception
                return false;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Remove cached data
     *
     * @access  public
     * @param   object
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Obj"
     */
    public function removeFromCache($Obj, $sIdent = '*') {

        $sPath = $this->getTempFileNamePath();
        $sPath .= strtolower(get_class($Obj) . '_' . $sIdent . '_');

        // Remove all cached files
        foreach (glob($sPath . '*', GLOB_NOSORT) as $sFile) {
            @unlink($sFile);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Write temp file
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function writeTempFile($sFileName, $sData) {
        $sFileName = $this->getTempFileNamePath() . $sFileName;

        try {
            $Out = new FileOutputStream($sFileName);
            $Out->write($sData);
            $Out->close();

        } catch (Exception $e) {
            // Swallow exception
            $this->addError(512, $sFileName);
            $this->terminate();
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Read temp file
     *
     * @access  public
     * @param   string
     * @param   boolean
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @throws  IOException
     *
     * @todo    [D11N]  Check description
     */
    public function readTempFile($sFileName) {

        $sFileName = $this->getTempFileNamePath() . $sFileName;
        $sContent = '';

        try {
            $Stream = new FileInputStream($sFileName);
            $sContent = $Stream->readAll();
            $Stream->close();

            return $sContent;

        } catch (IOException $e) {
            throw $e;
        }
    }

    // === ERRORS, MESSAGES, TERMINATION, RESUMPTION ======================

    /**
     * Resume
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
    public function resume() {
        return $this->terminate(false);
    }

    // --------------------------------------------------------------------

    /**
     * Terminate
     *
     * @access  public
     * @param   boolean
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function terminate($bExitus = true) {
        if ($bExitus) {
            $this->addError(110);               // Terminate
            echo $this->getErrorQueueFormatted();
            exit;
        }

        $this->addError(111);                   // Resume / Keep alive
        echo $this->getErrorQueueFormatted();
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Add error
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function addError() {

        $aArgs   = func_get_args();
        $nStatus = 0;
        $sStr    = '';
        $nMsg    = '';

        // First argument is error number
        $nStatus = array_shift($aArgs);

        // Do not record error doublettes
        for ($i=0, $n=sizeof($this->aError); $i<$n; $i++) {
            if ($this->aError[$i]['status'] == $nStatus) {
                return true;
            }
        }

        // Additional error info? Further parameters?
        if (isset($aArgs[0])) {

            if (is_array($aArgs[0])) {
                for ($i=0, $n=sizeof($aArgs[0]); $i<$n; $i++) {
                    $sStr .= '<br />&nbsp;<tt>*</tt>&nbsp;';
                    $sStr .= $aArgs[0][$i];
                }
            } else if (is_string($aArgs[0])) {
                $sStr .= '<br />&nbsp;<tt>*</tt>&nbsp;';
                $sStr .= $aArgs[0];
            }
        }

        $sStatus = __($nStatus) .' ['
            .'<a target="_blank" href="status.php#status'.$nStatus.'">'
            .__('I18N_ERROR_STATUS') . ' ' . $nStatus
            .'</a>' .']' . $sStr;

        $this->aError[] = array(
            'text'   => $sStatus,
            'status' => $nStatus
        );
    }

    // --------------------------------------------------------------------

    /**
     * Get error queue formatted
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getErrorQueueFormatted() {
        if ($this->hasNoErrors()) {
            return '';
        }

        // Avoid infinite loop if an error occured while coWiki was already
        // in an error state
        if ($this->bInErrorState) {
            echo '<pre>';
            for ($i=0, $n=sizeof($this->aError); $i<$n; $i++) {
                echo $this->aError[$i]['text'];
                echo "\n";
            }
            echo '</pre>';
            exit; // stop!
        }

        $bErrorState = true;

        $sImgPath  = $this->getRegistry()->get('PATH_IMAGES');

        $aTplItem = array();

        for ($i=0, $n=sizeof($this->aError); $i<$n; $i++) {
            $aItem = array();
            $sImg = '0.gif';

            if ($this->aError[$i]['status'] >= 0)   { $sImg = 'error.gif'; }
            if ($this->aError[$i]['status'] >= 100) { $sImg = 'info.gif';  }
            if ($this->aError[$i]['status'] >= 300) { $sImg = 'warn.gif';  }
            if ($this->aError[$i]['status'] >= 500) { $sImg = 'error.gif'; }

            $aItem['IMAGE'] = $sImg;
            $aItem['TEXT']  = $this->aError[$i]['text'];

            $aTplItem[] = $aItem;
        }

        // Set template variable
        $this->getTemplateRegistry()->set('TPL_ITEM', $aTplItem);

        // Clean error container
        $this->aError = array();

        // Parse template
        $this->bInErrorState = true;
        $sStr = $this->getTemplateProcessor()->parse('error.tpl');
        $this->bInErrorState = false;

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Has no errors
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
    public function hasNoErrors() {
        return sizeof($this->aError) == 0;
    }

    // --------------------------------------------------------------------

    /**
     * Has errors
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
    public function hasErrors() {
        return sizeof($this->aError) != 0;
    }

    // === DATE & TIME ====================================================

    /**
     * Make date
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nDate"
     * @todo    [D11N]  Check return type
     */
    public function makeDate($nDate) {
        return strftime(__('I18N_DATE_FORMAT'), $nDate);
    }

    // --------------------------------------------------------------------

    /**
     * Make time
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nTime"
     * @todo    [D11N]  Check return type
     */
    public function makeTime($nTime) {
        return strftime(__('I18N_TIME_FORMAT'), $nTime);
    }

    // --------------------------------------------------------------------

    /**
     * Make date time relative
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nStamp"
     */
    public function makeDateTimeRelative($nStamp) {
        if (strftime('%d%m%Y',$nStamp) == strftime('%d%m%Y',time())) {
            return strftime(
                __('I18N_TODAY') . ', ' . __('I18N_TIME_FORMAT'),
                $nStamp
            );
        }

        if (strftime('%d%m%Y',$nStamp) == strftime('%d%m%Y',time()-86400)) {
            return strftime(
                __('I18N_YESTERDAY') . ', ' . __('I18N_TIME_FORMAT'),
                $nStamp
            );
        }

        return strftime(
            __('I18N_DATE_FORMAT').', '.__('I18N_TIME_FORMAT'),
            $nStamp
        );
    }

    // === PLUGIN PARAMETERS ==============================================

    /**
     * Input string: e.g. width="200" height="300" border="10"
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function setPluginParam($sStr) {
        $this->aPluginParam = array();

        preg_match_all(
            '#([a-z_]+)\s*=\s*("([^"]*)"|\'([^\']*)\')#Si',
            $sStr,
            $aMatches,
            PREG_SET_ORDER
        );

        for ($i=0, $n=sizeof($aMatches); $i<$n; $i++) {
            $sKey   = strtolower($aMatches[$i][1]);
            $sValue = null;

            if (isset($aMatches[$i][3]) && $aMatches[$i][3] != null) {
                $sValue = $aMatches[$i][3];
            }
            if (isset($aMatches[$i][4]) && $aMatches[$i][4] != null) {
                $sValue = $aMatches[$i][4];
            }

            // Set key and value, remove illegal chars
            $this->aPluginParam[$sKey] = str_replace('>', '', $sValue);

            // Set attributes for layouter
            $this->getLayouter()->addAttribute($sKey, $sValue);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin param
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
    public function getPluginParam($sVarName, $bEscaped = true) {
        if (isset($this->aPluginParam[$sVarName])) {
            if ($bEscaped !== false) {
                return escape($this->aPluginParam[$sVarName]);
            } else {
                return $this->aPluginParam[$sVarName];
            }
        }
        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Has plugin param
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
    public function hasPluginParam($sVarName) {
        if (isset($this->aPluginParam[$sVarName])) {
            return true;
        }
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin param boolean
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
    public function getPluginParamBoolean($sVarName) {
        $mParam = $this->getPluginParam($sVarName);
        if (!$mParam) { return $mParam; }

        $mParam = strtolower($mParam);

        return in_array($mParam, array('on', 'yes', 'true', '1'));
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin params
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
    public function getPluginParams($bEscaped = true) {
        if ($bEscaped !== false) {
            return array_map('escape', $this->aPluginParam);
        } else {
            return $this->aPluginParam;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Clean plugin params
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function cleanPluginParams() {
        $this->aPluginParam = array();
    }

    // --------------------------------------------------------------------

    /**
     * Get plugin param ident
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
    public function getPluginParamIdent() {
        return strtoupper(substr(md5(serialize($this->aPluginParam)), 0, 8));
    }

    // === COOKIE VARIABLES ===============================================

    /**
     * Set cookie var
     *
     * @access  public
     * @param   string
     * @param   string
     * @param   integer
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nExpire"
     */
    public function setCookieVar($sVarName, $sValue, $nExpire = 1000000) {
        @setcookie($sVarName, $sValue, time()+$nExpire, '/');
    }

    // --------------------------------------------------------------------

    /**
     * Unset cookie var
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function unsetCookieVar($sVarName) {
        $this->setCookieVar($sVarName, '');
    }

    // --------------------------------------------------------------------

    /**
     * Get cookie var
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   move this code to HttpRequest, where it belongs
     */
    public function getCookieVar($sVarName) {
        if (isset($_COOKIE[$sVarName])) {

            // FIX: move this code to HttpRequest, where it belongs
            if (ini_get('magic_quotes_gpc')) {
                return stripslashes($_COOKIE[$sVarName]) . '';
            } else {
                return $_COOKIE[$sVarName] . '';
            }
        }

        return '';
    }

    // === SESSION VARIABLES ==============================================

    /**
     * Set session var
     *
     * @access  public
     * @param   string
     * @param   object
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mValue"
     * @todo    [D11N]  Check the parameter type of "$mExtCont"
     */
    public function setSessionVar($sVarName, $mValue, $mExtCont = true) {
        $sContName = $this->_getContainerName($mExtCont);

        $this->aSessCont[$sContName][$sVarName] = $mValue;
    }

    // --------------------------------------------------------------------

    /**
     * Unset session var
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
     * @todo    [D11N]  Check the parameter type of "$mExtCont"
     */
    public function unsetSessionVar($sVarName, $mExtCont = true) {
        $sContName = $this->_getContainerName($mExtCont);

        if (isset($this->aSessCont[$sContName][$sVarName])) {
            unset($this->aSessCont[$sContName][$sVarName]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get session var
     *
     * @access  public
     * @param   string
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mExtCont"
     */
    public function getSessionVar($sVarName, $mExtCont = true) {
        $sContName = $this->_getContainerName($mExtCont);

        if (isset($this->aSessCont[$sContName][$sVarName])) {
            return $this->aSessCont[$sContName][$sVarName];
        }
        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Get session vars
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mLocalContainer"
     */
    public function getSessionVars($mLocalContainer = true) {
        $sContName = ($mLocalContainer === true)
                        ?   $this->getPluginName()
                        :   $mLocalContainer;

        if (isset($this->aSessCont[$sContName])) {
            return $this->aSessCont[$sContName];
        }
        return null;
    }

    // --------------------------------------------------------------------

    /**
     * _get container name
     *
     * @access  private
     * @param   object
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mExtCont"
     */
    private function _getContainerName($mExtCont = true) {
        $sContName = ($mExtCont === true)
                        ?   $this->getPluginName()
                        :   $mExtCont;
        return $sContName;
    }

/*
    I've ... seen things you people wouldn't believe, hm ... attack ships
    on fire off the shoulder of Orion ... I watched C-beams ... glitter in
    the dark near the Tannhauser Gate ... all those ... moments will be
    lost ... in time ... like tears in rain ...
*/

} // of class

?>
