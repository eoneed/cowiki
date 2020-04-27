<?php

/**
 *
 * $Id: class.CustomPostInstall.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomPostInstall
 * #purpose:   Displays post installation information
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      22. July 2004
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
 * Displays post installation information
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.4.0
 */
class CustomPostInstall extends AbstractPlugin {

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
     * @since   coWiki 0.4.0
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
     * @since   coWiki 0.4.0
     */
    public function perform() {

        // I18N stuff
        $sChng      = __('I18N_CHANGE');
        $sAdm       = __('I18N_ADMIN');
        $sWebStruct = __('I18N_ADMIN_MENU_LABEL_STRUCT');
        $sEdit      = __('I18N_EDIT');

        // Visual
        $sStyle =   'margin: 20px;';
        $sStyle .=  'padding: 10px;';
        $sStyle .=  'border: 1px dotted #CCC;';
        $sStyle .=  'background-color: #F8F8F8;';
        $sStyle .=  'font-size: 14px;';

        $sStr =   '<div';
        $sStr .=    ' style="' . $sStyle . '"';
        $sStr .=  '>';

        $sStr .=    '<h1>Congratulation - it is alive!</h1>';
        $sStr .=    '<p>';
        $sStr .=      'Your ' . COWIKI_NAME . ' ' . COWIKI_VERSION;

        if (COWIKI_RELEASE_NAME != '') {
            $sStr .= ' (' . COWIKI_RELEASE_NAME. ')';
        } else {
            $sStr .= ' (' . COWIKI_VERSION_DATE . ' / CVS snapshot)';
        }

        $sStr .=      ' installation seems to be up and running.';
        $sStr .=    '</p>';

        $sStr .=    '<p>';
        $sStr .=      'Well, what\'s next?';
        $sStr .=    '</p>';

        $sStr .=    '<p>';
        $sStr .=      '<ol style="line-height: 140%">';

        $sStr .=        '<li>';
        $sStr .=          'Press the ['.$sChng.'] button to switch the';
        $sStr .=          ' current guest user to "root" using the password';
        $sStr .=          ' you have encrypted and set for <tt>ROOT_PASSWD</tt>';
        $sStr .=          ' in the configuration file (<tt>core.conf</tt>).';
        $sStr .=          ' Then, an ['.$sAdm.'] button will appear.';
        $sStr .=        '</li>';

        $sStr .=        '<li>';
        $sStr .=          'Click the [Admin] button, enter the admistration';
        $sStr .=          ' area and create a few "webs" (main menu entries';
        $sStr .=          ' on the left) in the "'.$sWebStruct.'" menu.';
        $sStr .=          ' Don\'t forget to make them visible to the main';
        $sStr .=          ' menu and the page footer by checking the';
        $sStr .=          ' appropriate options.';
        $sStr .=          ' You should rename the "Congratulation" web and';
        $sStr .=          ' reuse ('.$sEdit.') this very document. Just';
        $sStr .=          ' overwrite the default content.';
        $sStr .=        '</li>';

        $sStr .=        '<li>';
        $sStr .=          'Play around a while :)';
        $sStr .=          ' Understand how it works.';
        $sStr .=          ' Generate knowledge.';
        $sStr .=        '</li>';

        $sStr .=        '<li>';
        $sStr .=          'After a while, if you think your coWiki has';
        $sStr .=          ' grown up, we would be glad to welcome you ';
        $sStr .=          ' on our reference list ("Who is using coWiki") at';
        $sStr .=          ' <a target="_blank" href="http://www.cowiki.org/">';
        $sStr .=          'www.cowiki.org</a>.';
        $sStr .=        '</li>';

        $sStr .=      '</ol>';
        $sStr .=    '</p>';

        $sStr .=    '<p>';
        $sStr .=      'Thank you for using this software. For more';
        $sStr .=      '  information and coWiki documentation please visit';
        $sStr .=      '  the home of coWiki at';
        $sStr .=      ' <a target="_blank" href="http://www.cowiki.org/">';
        $sStr .=      'www.cowiki.org</a>.';
        $sStr .=    '</p>';

        $sStr .=  '</div>';

        echo $sStr;
    }

} // of plugin component

/* 
    Buenos tardes amigo
    Hola, my good friend
    Cinco de Mayo's on Tuesday
    And I hoped we'd see each other again

    You killed my brother last winter
    You shot him three times in the back
    In the night I still hear mama weeping
    Oh mama, still dresses in black

    I looked at every fiesta
    For you I wanted to greet
    Maybe I'd sell you a chicken
    With poison interlaced with the meat

    You, you look like my brother
    Mama loved him the best
    He was head honcho with the ladies
    Mama always said he was blessed

    The village, all gathered around him
    They couldn't believe what they saw
    I said, it was you that had killed him
    And that I'd find you and upstand the law

    The people of the village believed me
    Mama, she wanted revenge
    I told her that I'd see that she was honored
    I'd find you and put you to death

    So now, now that I've found you
    On this such a joyous day
    I tell you it was me who killed him
    But the truth I'll never have to say

    Buenos tardes amigo
    Hola, my good friend
    Cinco de Mayo's on Tuesday
    And I hoped we'd see each other again
    Yes, I hoped we'd see each other again

*/

?>
