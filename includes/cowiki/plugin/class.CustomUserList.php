<?php

/**
 *
 * $Id: class.CustomUserList.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      UserList
 * #purpose:   Provide a list of all users from current coWiki data storage.
 *
 * #param:     title    Adds a title to the top of userlist box (default none).
 * #caching:   not used
 * #comment:   Handle with care, you may not list your users in public
 *             documents!
 * #version:   1.0
 * #date:      01. November 2003
 * #author:    Franziskus Domig <fd@php.net>
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Franziskus Domig <fd@php.net>
 * @copyright   (C) Franziskus Domig {@link http://seric.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * Provide a list of all users from current coWiki data storage. It supports
 * the distinction between activated and deactivated users and just lists
 * active users.
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Franziskus Domig <fd@php.net>
 * @since       coWiki 0.4.0
 */
class CustomUserList extends AbstractPlugin {

    // Put in the interface version the plugin works with.
    // This has nothing to do with the @version of this plugin!
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean   true if initialization successful,
     *                    false otherwise
     *
     * @author  Franziskus Domig <fd@php.net>
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
     *
     * @author  Franziskus Domig <fd@php.net>
     * @since   coWiki 0.4.0
     */
    public function perform() {

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // Get all users
        $Users = $UserDAO->getAllUsers();

        // ----------------------------------------------------------------

        // Get the title param if defined
        $sTitle = $this->Context->getPluginParam('title')
                    ? $this->Context->getPluginParam('title')
                    : '';
        $sStyle = $this->Context->getPluginParam('style')
                    ? $this->Context->getPluginParam('style')
                    : '';

        // ----------------------------------------------------------------

        // Set plugin parameters for template
        $this->Template->set('TPL_TABLE_STYLE', $sStyle);
        $this->Template->set('TPL_TITLE', $sTitle);

        // ----------------------------------------------------------------

        $aTplItem = array();

        // Iterate through users
        $It = $Users->iterator();

        while ($Obj = $It->next()) {

            // Ignore root & guest users
            if ($Obj->get('userId') == 0 || $Obj->get('userId') == 65535) {
                continue;
            }

            // Get users default group
            $Group = $UserDAO->getGroupByGid($Obj->get('groupId'));

            // Get member group names sting
            $sMember = '';

            $GrpIt = $Obj->getMemberGroups()->iterator();

            // Concat group name string
            while ($GrpObj = $GrpIt->next()) {

                // Skip default group
                if ($GrpObj->get('groupId') == $Obj->get('groupId')) {
                    continue;
                }

                // Show "wheel" member groups for root only
                if ($GrpObj->get('groupId') == 0 && !$CurrUser->isRoot()) {
                    continue;
                }

                $sMember .= $GrpObj->get('name').', ';
            }

            // Cut trailing comma
            $sMember = substr($sMember, 0, -2);
            if ($sMember != '') {
                $sMember = '('.$sMember.')';
            }

            // ------------------------------------------------------------

            $aItem = array();

            $aItem['LOGIN']  = escape($Obj->get('login'));
            $aItem['NAME']   = escape($Obj->get('name'));
            $aItem['EMAIL']  = obfuscateEmail($Obj->get('email'));
            $aItem['GROUP']  = is_object($Group) ? $Group->get('name') : '';
            $aItem['MEMBER'] = $sMember;

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        // Sort array
        $this->sortArray($aTplItem);

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.user.list.tpl');

    }

    // --------------------------------------------------------------------

    /**
     * Compares names. Helper method for sortArray()
     *
     * @access  private
     *
     * @author  Kai Schröder <k.schroeder@php.net>
     * @since   coWiki 0.4.0
     */
    private function compareName($aA, $aB) {
        return strcasecmp($aA['NAME'], $aB['NAME']);
    }

    // --------------------------------------------------------------------

    /**
     * Sort array
     *
     * @access  private
     *
     * @author  Kai Schröder <k.schroeder@php.net>
     * @since   coWiki 0.4.0
     */
    private function sortArray(&$aTplItem) {
        usort($aTplItem, array($this, 'compareName'));
    }

} // of plugin component

/*
    lookin' for to save my soul
    lookin' in the places where no flowers grow
    lookin' for to fill that GOD shaped hole
    mother mother sucking rock and roll
*/

?>
