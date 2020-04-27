<?php

/**
 *
 * $Id: class.Environment.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Environment class
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
class Environment extends Object {
    protected static
        $Instance = null;

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
            self::$Instance = new Environment;
        }
        return self::$Instance;
    }

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

        $_SERVER['COWIKI_ROOT'] = realpath('.');

        if (substr($_SERVER['PHP_SELF'], -3) != 'php') {
            // fix problems with redirected CGI
            if (!empty($_SERVER['ORIG_SCRIPT_NAME']) &&
                substr($_SERVER['ORIG_SCRIPT_NAME'], -3) == 'php') {
                $_SERVER['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
            } else {
                $Context  = RuntimeContext::getInstance();
                $Context->addError(535);
                $Context->terminate();
            }
        }
        $sPhpSelfPath = dirname($_SERVER['PHP_SELF']);

        // Do not trust in DOCUMENT_ROOT, re-set it
        if ($sPhpSelfPath != '/' && $sPhpSelfPath != '\\') {
            $_SERVER['DOCUMENT_ROOT'] = substr(
                $_SERVER['COWIKI_ROOT'],
                0,
                strpos($_SERVER['COWIKI_ROOT'], $sPhpSelfPath)
            );
        } else {
            $_SERVER['DOCUMENT_ROOT'] = $_SERVER['COWIKI_ROOT'];
        }

    }

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
        if (isset($_SERVER[$sKey])) {
            return true;
        }

        return (bool)getenv($sKey);
    }

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
        if (isset($_SERVER[$sKey])) {
            return $_SERVER[$sKey];
        }

        return getenv($sKey) ? getenv($sKey) : null;
    }

} // of class

?>
