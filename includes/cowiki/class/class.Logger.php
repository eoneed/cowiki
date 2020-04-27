<?php

/**
 *
 * $Id: class.Logger.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Logger. The Logger provides logging capabilities of debug
 * messages to the webserver error log.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.4
 */

    // {{{ DEBUG }}} constants, use error_log(), not syslog()
    define('D_OFF',   0);
    define('D_ERR',   1);
    define('D_WARN',  2);
    define('D_INFO',  4);
    define('D_SQL',   8);
    define('D_FORK',  16);
    define('D_IO',    32);
    define('D_EXE',   64);
    define('D_REPT',  16384);

    define(
        'D_ALL',
        D_ERR | D_WARN | D_INFO | D_SQL | D_FORK | D_IO | D_EXE
    );

    // Display the parent caller name
    define('LOG_PARENT_CALLER', 1);

/**
 *  @author   Daniel T. Gorski <daniel.gorski@develnet.org>
 */
class Logger {

    static
        $sLogFile = null,
        $bLoggerOff = true,
        $nMsgCount  = 0;

    // --------------------------------------------------------------------

    protected function Logger() {}
    public static function triggerAutoload() {}

    // --------------------------------------------------------------------

    public static function info($sStr, $nType = 0) {
        Logger::doLog(D_INFO, $sStr, $nType);
    }
    public static function information($sStr, $nType = 0) {
        Logger::doLog(D_INFO, $sStr, $nType);
    }

    public static function warn($sStr, $nType = 0) {
        Logger::doLog(D_WARN, $sStr, $nType);
    }
    public static function warning($sStr, $nType = 0) {
        Logger::doLog(D_WARN, $sStr, $nType);
    }

    public static function err($sStr, $nType = 0) {
        Logger::doLog(D_ERR, $sStr, $nType);
    }
    public static function error($sStr, $nType = 0) {
        Logger::doLog(D_ERR, $sStr, $nType);
    }

    public static function fork($sStr, $nType = 0) {
        Logger::doLog(D_FORK, $sStr, $nType);
    }
    public static function sql($sStr, $nType = 0) {
        Logger::doLog(D_SQL, $sStr, $nType);
    }
    public static function io($sStr, $nType = 0) {
        Logger::doLog(D_IO, $sStr, $nType);
    }

    public static function exe($sStr, $nType = 0) {
        Logger::doLog(D_EXE, $sStr, $nType);
    }
    public static function execute($sStr, $nType = 0) {
        Logger::doLog(D_EXE, $sStr, $nType);
    }

    public static function infowarn($sStr, $bFlag, $nType = 0) {
        if ($bFlag) {
            Logger::info($sStr, $nType);
        } else {
            Logger::warn($sStr, $nType);
        }
    }

    // --------------------------------------------------------------------

    private static function doLog($nMode, $sStr, $nType) {
        static $aBuf = array(), $nBufIdx = 0;

        // Until the Registry is not completely initialized we have a
        // chicken-egg problem here: we can not log as we do not know
        // what should be logged. Hence we buffer the stuff first and
        // output it if every object that we require is available.
        if (!Registry::isInitialized()) {

            // Remember
            $aBuf[$nBufIdx]['STRN'] = Logger::prepareLogString($sStr, $nType);
            $aBuf[$nBufIdx]['MODE'] = $nMode;
            $aBuf[$nBufIdx]['PROF'] = profile();
            $nBufIdx++;

            return;
        }

        // Check debug level
        $sConfValue = Registry::getInstance()->get('SOFTWARE_DEBUG_LEVEL');
        @eval('$nLevel = '.$sConfValue.';');
        if (($nLevel & $nMode) == 0) { return; }

        self::$bLoggerOff = false;

        // ---

        // Log separator
        if (Logger::$nMsgCount == 0) {
            Logger::header();

            // If we have something buffered, output it now
            if ($nBufIdx > 0) {
                for ($i=0; $i<$nBufIdx; $i++) {

                    // Check debug level
                    if (($nLevel & $aBuf[$i]['MODE']) == 0) { continue; }

                    Logger::writeLogMessage(
                        $aBuf[$i]['STRN'],
                        $aBuf[$i]['MODE'],
                        $aBuf[$i]['PROF']
                    );
                }
            }
        }

        $sStr = Logger::prepareLogString($sStr, $nType);

        Logger::writeLogMessage($sStr, $nMode, profile());
    }

    // --------------------------------------------------------------------

    private static function prepareLogString($sStr, $nType) {

        // Get backtrace
        $aTrace = debug_backtrace();
        $sFile = 'n/a';
        $sLine = '?';

        $nIdx = 3;
        if ($nType == LOG_PARENT_CALLER) {
            $nIdx = 4;
        }

        if (!isset($aTrace[$nIdx]) || !isset($aTrace[$nIdx]['class'])) {

            $sFile = 'n/a';
            if (isset($aTrace[$nIdx-1]['file'])) {
                $sFile = basename($aTrace[$nIdx-1]['file']);
            }

            // Check if 'file' fits class/interface naming scheme:
            // 'class.TheNameOfTheClass.php'
            if (substr($sFile, 0, 6) == 'class.') {
                $sFile = substr($sFile, 6, -4);
            }

            $sLine = 'n/a';
            if (isset($aTrace[$nIdx-1]['line'])) {
                $sLine = $aTrace[$nIdx-1]['line'];
            }
        } else {
            $sFile = $aTrace[$nIdx]['class'];
            $sLine = $aTrace[$nIdx-1]['line'];
        }

        return $sFile . ' (' . $sLine . '): ' . $sStr;
    }

    // --------------------------------------------------------------------

    private static function writeLogMessage($sStr, $nMode, $nProfile) {
        static $nRep = 0, $sLog = '';

        $aDebug[D_ERR]    = 'ERR';
        $aDebug[D_WARN]   = 'WRN';
        $aDebug[D_INFO]   = '   ';
        $aDebug[D_SQL]    = 'SQL';
        $aDebug[D_FORK]   = 'FRK';
        $aDebug[D_IO]     = 'I/O';
        $aDebug[D_EXE]    = 'EXE';
        $aDebug[D_REPT]   = '-->';

        // Remove line feeds
        $sStr = str_replace("\n", '', $sStr);

        // Remove multiple spaces
        $sStr = preg_replace('# +#S', ' ', $sStr);

        // ---

        // Check for "Last message repeated"
        if ($sStr == $sLog) {
            $nRep++;
            return;
        } else {
            $sLog = $sStr;
        }

        if ($nRep != 0) {
            $sWarn = 'HEADS UP: Last message repeated ' . $nRep . ' time(s)';

            Logger::logMessage(
                $sWarn,
                ++Logger::$nMsgCount,
                $nProfile,
                $aDebug[D_REPT]
            );

            $sLog = '';
            $nRep = 0;
        }

        // Do not report empty strings
        if ($sStr == '') { return ''; }

        // ---

        $nPos = strpos($sStr, ':');

        if ($nPos) {
            $nPos += 2;

            $sMsg1 = substr($sStr, 0, $nPos);
            $sMsg2 = substr($sStr, $nPos);

            $sStr = $sMsg1 . wordwrap(
                                $sMsg2,
                                65 - $nPos, // Calc wrap width
                                "\n      |     | ".str_repeat(' ', $nPos),
                                true
                             );
        }

        Logger::logMessage(
            $sStr,
            ++Logger::$nMsgCount,
            $nProfile,
            $aDebug[$nMode]
        );
    }

    // --------------------------------------------------------------------

    private static function logMessage($sMsg, $nCount, $nProfile, $sPrefix) {
        self::writeLogFile(sprintf('%s | %s | %s', $nProfile, $sPrefix, $sMsg));
    }

    // --------------------------------------------------------------------

    private static function header() {
        $sTime   = strftime('%a %d. %B %Y, %H:%M:%S', time());
        $sUri    = '['. $_SERVER['REQUEST_METHOD'] . ']' . chr(160)
                    . $_SERVER['REQUEST_URI'];
        $sRemote = gethostbyaddr($_SERVER['REMOTE_ADDR']) . chr(160)
                   . '(' . $_SERVER['REMOTE_ADDR'] . ')';

        self::writeLogFile("\n" . ' Secs | Lvl | Message');
        self::writeLogFile('------+-----+'.str_repeat('-', 66));
        self::writeLogFile('      |     | Request time: ' . $sTime);
        self::writeLogFile('      |     | Request URI: '  . $sUri);
        self::writeLogFile('      |     | Requested by: ' . $sRemote);
    }

    // --------------------------------------------------------------------

    public static function shutdown() {
        static $bCalled = false;

        if (!self::$bLoggerOff && !$bCalled) {
            self::writeLogFile('------+-----+'.str_repeat('-', 66));
            self::writeLogFile(profileTotal().' seconds - estimated total script'
                      .' execution time');

            $bCalled = true;
        }
    }

    // --------------------------------------------------------------------

    protected static function writeLogFile($sMsg) {

        if (self::$sLogFile == null) {
            self::$sLogFile = Registry::getInstance()->get('SOFTWARE_DEBUG_LOG_FILE');
        }

        if (self::$sLogFile != ''
         && is_writable(self::$sLogFile)) {

            /**
             * todo
             * failed, if we use FileOutputStream
             * i don't know why :(
             */
            file_put_contents(self::$sLogFile, $sMsg."\r\n", FILE_APPEND);

            //try {
            //    echo 1;
            //    $Out = new FileOutputStream(
            //        self::$sLogFile, true
            //    );
            //    echo 2;
            //    $Out->write($sMsg."\r\n");
            //    $Out->close();
            //
            //} catch (Exception $e) {
            //    echo $e->getMessage(); // rethrow
            //}
        }
        else {
            error_log($sMsg);
        }
    }

    // --------------------------------------------------------------------

} // of class

?>