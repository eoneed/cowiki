<?php

/**
 *
 * $Id: class.CustomCoWikiChangeLog.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomCoWikiChangeLog
 * #purpose:   Displays the coWiki ChangeLog of the installed version
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      05. November 2003
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Displays the coWiki ChangeLog
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.4
 */
class CustomCoWikiChangeLog extends AbstractPlugin {

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
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
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
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     */
    public function perform() {

        $sChlog = realpath('../') . '/ChangeLog';

        if (file_exists($sChlog) && is_readable($sChlog)) {
            echo '<pre class="code">';
            echo    escape(@file_get_contents($sChlog));
            echo '</pre>';
        }
    }

} // of plugin component

/* 
    Everybody wants to dance in a playpen
    But nobody wants to play in my garden
    I see the hippies on an angry line
    Guess they don't get my meaning

    I'm enchanted by the birds in my blossoms
    I'm enamored by young lovers on the weekend
    I like the Fourth of July
    When bombs start flashing

    And I wish I had a shiny red top
    A bugle with a big brass bell would cheer me up
    Or maybe something bigger that could really go pop!
    So I could make the gardening stop

    Come out to play
    Come out to play
    And we'll pretend it's Christmas Day
    In my atomic ... garden

    All my scientists are working on a deadline
    So my psychologist is working day and night time
    They say they know what's best for me
    But they don't know what they're doing

    And I'm glad I'm not Gorbachev
    'cause I'd wiggle all night
    Like jelly in a pot
    At leats he's got a garden with a fertile plot
    And a party that will never stop

    I hope there's nothing wrong out there
    I'm watching from my room inside my room
*/

?>
