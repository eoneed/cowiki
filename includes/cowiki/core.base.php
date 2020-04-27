<?php

/**
 *
 * $Id: core.base.php 30 2011-01-09 14:48:12Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    core
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 30 $
 *
 */

    set_magic_quotes_runtime(0);
    iconv_set_encoding('output_encoding',   'UTF-8');
    iconv_set_encoding('internal_encoding', 'UTF-8');

/*
    header("Expires: Sat, 05 Aug 2000 22:27:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Cache-Control: post-check=0, pre-check=0");
*/
    header('Vary: Accept-Encoding, User-Agent');

    /**
     * Version information
     */
    include_once 'version.php';

    /**
     * Core functions
     */
    include_once 'core.function.php';

    // Put PHP errors and warnings to $php_errormsg variable
    ini_set('track_errors', 1);
    $php_errormsg = 'unknown';

    register_shutdown_function('shutdownFunction');

    // {{{ DEBUG }}}
    Logger::info('Started processing.');

    // First read the uptodate english localized file.
    setCatalog('locale/', 'error');
    setCatalog('locale/', 'en.utf-8');

    // --------------------------------------------------------------------

    define('CMD_CHUSR',      'chusr');
    define('CMD_NEWUSR',     'newusr');
    define('CMD_EDITUSR',    'editusr');
    define('CMD_PREFUSR',    'prefusr');
    define('CMD_NEWGRP',     'newgrp');
    define('CMD_EDITGRP',    'editgrp');
    define('CMD_NEWWEB',     'newweb');
    define('CMD_EDITWEB',    'editweb');
    define('CMD_SHOWDIR',    'showdir');
    define('CMD_NEWDIR',     'newdir');
    define('CMD_EDITDIR',    'editdir');
    define('CMD_NEWDOC',     'newdoc');
    define('CMD_EDITDOC',    'editdoc');
    define('CMD_SIMDOC',     'simdoc');
    define('CMD_AMBIDOC',    'ambidoc');
    define('CMD_MOVEDOC',    'movedoc');
    define('CMD_PRNTDOC',    'prntdoc');
    define('CMD_XMLDOC',     'xmldoc');
    define('CMD_SRCHDOC',    'srchdoc');
    define('CMD_SHOWHIST',   'showhist');
    define('CMD_COMPHIST',   'comphist');
    define('CMD_DIFFHIST',   'diffhist');
    define('CMD_RECOVHIST',  'recovhist');
    define('CMD_NEWCOM',     'newcom');
    define('CMD_LISTCOM',    'listcom');
    define('CMD_REPLYCOM',   'replycom');
    define('CMD_DETAILUSR',  'detusr');

    // --------------------------------------------------------------------

    // Register unserialize callback handler for objects coming from session
    spl_autoload_register('__autoload');

    // Session settings and lift off
    ini_set('arg_separator.output', '&amp;');
    session_name('cowiki');
    @session_start();

    /**
     * Catch the ConfigNotReadableException
     * from invalid configs
     */
    try {
        $Context  = RuntimeContext::getInstance();
        $Request  = $Context->getRequest();
        $Registry = $Context->getRegistry();
    }
    catch(ConfigNotReadableException $e) {
        $e->printStackTrace();
        exit;
    }
    catch(Exception $e) {
        echo $e->getMessage();
        exit;
    }

    // Load localized catalog
    if ($Registry->get('RUNTIME_LANGUAGE_LOCALE')) {
        if ($Registry->get('RUNTIME_LANGUAGE_LOCALE') != 'en.utf-8') {
            setCatalog('locale/', $Registry->get('RUNTIME_LANGUAGE_LOCALE'));
        }
    }

    // --------------------------------------------------------------------

    // Set the default timezone
    date_default_timezone_set($Registry->get('RUNTIME_DEFAULT_TIMEZONE'));

    // --------------------------------------------------------------------

    // Check if we should teergrubing the client
    $aTeer = explode(',', $Registry->get('RUNTIME_TEERGRUBING_AGENT'));
    $sAgent = strtolower($Request->getAgent());

    for ($i=0, $n=sizeof($aTeer); $i<$n; $i++) {
        $sStr = strtolower(trim($aTeer[$i]));

        // Avoid user errors in teergrubing agent string
        if ($sStr == '') {
            continue;
        }

        if (substr_count($sAgent, $sStr)) {
            $nTimeOut = (int)$Registry->get('RUNTIME_TEERGRUBING_TIMEOUT');

            if ($nTimeOut < 0) {

                // {{{ DEBUG }}}
                Logger::info('Teergrubing (tarpit) rule matches. Bailing out.');
                exit; // die
            }

            // {{{ DEBUG }}}
            Logger::info(
                'Teergrubing (tarpit) rule matches.
                Sleeping for '.$nTimeOut.' seconds.'
            );
            sleep($nTimeOut);
        }
    }

    // --------------------------------------------------------------------

    // Check if we should teergrubing the IP
    $aTeer = explode(',', $Registry->get('RUNTIME_TEERGRUBING_IP'));

    for ($i=0, $n=sizeof($aTeer); $i<$n; $i++) {
        $sStr = trim($aTeer[$i]);

        // Avoid user errors in teergrubing IP string
        if ($sStr == '') {
            continue;
        }

        if (preg_match('#^'.$sStr.'$#', $Request->getRemoteAddr())) {
            $nTimeOut = (int)$Registry->get('RUNTIME_TEERGRUBING_TIMEOUT');

            if ($nTimeOut < 0) {
                // {{{ DEBUG }}}
                Logger::info('Teergrubing (tarpit) rule matches. Bailing out.');
                exit; // die
            }

            // {{{ DEBUG }}}
            Logger::info(
                'Teergrubing (tarpit) rule matches.
                Sleeping for '.$nTimeOut.' seconds.'
            );
            sleep($nTimeOut);
        }
    }

    // --------------------------------------------------------------------

    // Check if user is logged in ...
    $aTmp = $Context->getSessionVar('loginData', 'core');

    $bValid = is_array($aTmp)
              && isset($aTmp['userId'])
              && isset($aTmp['loggedIn'])
              && $aTmp['loggedIn'];

    if ($bValid) {
        $UserDAO = $Context->getUserDAO();
        $User = $UserDAO->getUserByUid($aTmp['userId']);

        if (is_object($User)) {
            $CurrUser = CurrentUser::getInstance();

            // Set properties in current user object
            $CurrUser->set('isValid', true);
            $CurrUser->set('userId',  $User->get('userId'));
            $CurrUser->set('groupId', $User->get('groupId'));
            $CurrUser->set('login',   $User->get('login'));
            $CurrUser->set('email',   $User->get('email'));
            $CurrUser->set('name',    $User->get('name'));
            $CurrUser->setMemberGroups($User->getMemberGroups());
        } else {
            // Force user reset
            $bValid = false;
        }
    }

    if (!$bValid) {
        $CurrUser = CurrentUser::getInstance();
        $CurrUser->reset();

        // Set localized name for the guest user
        $CurrUser->set('name', __('I18N_GUEST_FULLNAME'));
    }

    // Set current user object in context
    $Context->setCurrentUser($CurrUser);

    // --------------------------------------------------------------------

    // Set locales depending on loaded catalog file
    $aLocales = explode(',', __('I18N_LOCALES'));
    for ($i=0, $n=sizeof($aLocales); $i<$n; $i++) {
        if (@setlocale(LC_ALL, trim($aLocales[$i]))) {

            // {{{ DEBUG }}}
            Logger::info('Set locale to '.$aLocales[$i]);
            break;
        }
    }

    // --------------------------------------------------------------------

    // This & that ... before we start
    $Registry->set('ZEND_VERSION', zend_version());

    $Context->getReferrerDAO()->store($Request->getReferrer());

    $Registry->set('BASE_URI',    $Request->getHostUri());
    $Registry->set('REMOTE_ADDR', $Request->getRemoteAddr());
    $Registry->set('REMOTE_HOST', $Request->getRemoteHost());

    $Registry->set(
        'TIME',
        strftime(
            __('I18N_DATE_FORMAT').', '.__('I18N_TIME_FORMAT'),
            time()
        )
    );

    // --------------------------------------------------------------------

    ini_set('highlight.string',   $Registry->get('COLOR_CODE_STRING'));
    ini_set('highlight.comment',  $Registry->get('COLOR_CODE_COMMENT'));
    ini_set('highlight.keyword',  $Registry->get('COLOR_CODE_KEYWORD'));
    ini_set('highlight.bg',       $Registry->get('COLOR_CODE_BG'));
    ini_set('highlight.default',  $Registry->get('COLOR_CODE_DEFAULT'));
    ini_set('highlight.html',     $Registry->get('COLOR_CODE_HTML'));

    ob_start();     // ... at least let the fun start

    // {{{ DEBUG }}}
    Logger::info('Stopped processing.');

    // === HELPER =========================================================

    /**
     * Class loader
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     *
     * @todo    FIX: Store paths in cache to avoid multiple lookups
     */
    function __autoload($sClass) {
        static $aPaths = null;

        if (!$aPaths) {
            // Classpath
            $sDir = realpath(dirname(__FILE__)) . '/class/';
            if (!is_dir($sDir)) {
                echo 'Classpath error'; // FIX
            }
            $aPaths = getClassPaths($sDir);
        }

        if (class_exists('Logger') && class_exists('Registry')) {
            // {{{ DEBUG }}}
            Logger::io('loading '.$sClass, LOG_PARENT_CALLER);
        }

        /**
         * If class not set in $aPaths
         */
        if (isset($aPaths[$sClass])) {
            include_once $aPaths[$sClass];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Will be invoked on script shutdown
     */
    function shutdownFunction() {
        if (class_exists('Logger')) {
            Logger::shutdown();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Helper function for __autoload() (class loader)
     *
     * @access  private
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    function &getClassPaths($sDir) {
        static $aArr = array();

        /**
         * Simply speed up parsing
         * Just as interim solution
         */
        $aArr = parse_ini_file(
            dirname(__FILE__).'/core.classes.conf'
        );
        return $aArr;

        $rDir = @opendir($sDir);

        while ($sFileName = @readdir($rDir)) {
            switch ($sFileName) {
                case '.':
                case '..':
                case '.svn':
                case 'CVS':
                continue 2;
            }

            if (is_dir($sDir.$sFileName)) {
                getClassPaths($sDir.$sFileName.'/');
            } else {
                $aParts = explode('.', $sFileName);
                $aArr[$aParts[1]] = $sDir . $sFileName;
            }
        }

        closedir($rDir);
        return $aArr;
    }

?>
