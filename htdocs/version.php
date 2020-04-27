<?php

/**
 *
 * $Id: version.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    htdocs
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19 $
 *
 */

/**
 * This file is subject to change for each release
 *
 * @package  htdocs
 * @access   public
 *
 * @author   Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since    coWiki 0.3.0
 */

    // This value has to be set to '0' (zero) for a stable version release.
    error_reporting(0); // !!

    // --------------------------------------------------------------------

    /**
     * Required PHP interpreter version
     */
    define('COWIKI_REQUIRED_PHP_VERSION', '5.0.2');

    /**
     * Plugin interface version
     */
    define('COWIKI_PLUGIN_INTERFACE_VERSION', 1);

    /**
     * Configuration file version
     */
    define('COWIKI_CONFIG_FILE_VERSION', 1);

    /**
     * Naming, version & release date
     */
    define('COWIKI_NAME', 'coWiki');

    /**
     * Additional software subname string
     */
    define('COWIKI_SUBNAME', 'web collaboration tool');

    /**
     * Major software version
     */
    define('COWIKI_VERSION_MAJOR', '0');

    /**
     * Minor software version
     */
    define('COWIKI_VERSION_MINOR', '3');

    /**
     * Micro software version
     */
    define('COWIKI_VERSION_MICRO', '4');

    /**
     * Software version patch
     */
    define('COWIKI_VERSION_PATCH', '');

    /**
     * Software release version
     */
    define('COWIKI_RELEASE_NAME', 'Carrion');

    /**
     * Software build (full) date
     */
    define('COWIKI_VERSION_DATE', 'August 2007');


    // --- Do not change from here ----------------------------------------

    /**
     * Software build (unix timestamp) date
     */
    define('COWIKI_VERSION_STAMP', strtotime(COWIKI_VERSION_DATE));

    /**
     * Complete software version string
     */
    define('COWIKI_VERSION', COWIKI_VERSION_MAJOR . '.'
                             . COWIKI_VERSION_MINOR . '.'
                             . COWIKI_VERSION_MICRO
                             . COWIKI_VERSION_PATCH);

    /**
     * Complete software name string
     */
    define('COWIKI_FULL_NAME', COWIKI_NAME . ' ' .COWIKI_VERSION);

    /**
     * The URL of the home of this software
     */
    define('COWIKI_HOME', 'http://www.cowiki.org');

?>
