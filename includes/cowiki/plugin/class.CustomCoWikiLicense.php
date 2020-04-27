<?php

/**
 *
 * $Id: class.CustomCoWikiLicense.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomCoWikiLicense
 * #purpose:   Displays the coWiki License of the installed version
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      03.08.2007
 * #author:    Alexander Klein, <a.klein@eoneed.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 * @copyright   (C) Alexander Klein, {@link http://ageless.de}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 *
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 * @since       coWiki 0.3.4
 */
class CustomCoWikiLicense extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean true if initialization successful, false otherwise
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform the plugin purpose. This is the main method of the plugin.
     *
     * @access  public
     * @return  void
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function perform() {

        $sChlog = realpath('../') . '/LICENSE';

        if (file_exists($sChlog) && is_readable($sChlog)) {
            echo '<pre class="code">';
            echo    escape(@file_get_contents($sChlog));
            echo '</pre>';
        }
    }

} // of plugin component

?>
