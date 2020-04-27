<?php

/**
 *
 * $Id: class.PrivateAdminUserListEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminUserListEditor
 * #purpose:   Edit user list in admin panel
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      01. November 2002
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  PrivateAdmin
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Edit user list in admin panel
 *
 * @package     plugin
 * @subpackage  PrivateAdmin
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateAdminUserListEditor extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Init
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
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        // Get all users
        $Users = $UserDAO->getAllUsers();

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Iterate through users
            $It = $Users->iterator();

            while ($Obj = $It->next()) {

                // Set "active"
                $Obj->set('isActive', false);
                if ($this->Request->has('active'.$Obj->get('userId'))) {
                    $Obj->set('isActive', true);
                }
            }

            // ------------------------------------------------------------

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // SAVE
                if ($UserDAO->storeUsers($Users)) {

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController();
                }
            }

            // ------------------------------------------------------------

            // Main form: CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // {{{ DEBUG }}}
                Logger::info('Redirecting to controller (CANCEL)');

                // Exit now
                $this->Response->redirectToController();
            }

        }

        // ----------------------------------------------------------------

        // Form action
        $this->Template->set(
            'TPL_FORM_ACTION',
            $this->Response->getControllerAction()
        );

        // Form control data
        $sStr =  '<input type="hidden" name="submit"'
                  .' value="'.$this->Context->getSubmitId().'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ----------------------------------------------------------------

        // Action buttons
        $aTplItem = array();

        $aItem = array();
        $aItem['NAME']   = __('I18N_USER_NEW');
        $aItem['TARGET'] = '_self';
        $aItem['HREF']   = $this->Response->getControllerHref(
                              'module=user&cmd='.CMD_NEWUSR
                           );

        $aTplItem[] = $aItem;

        $this->Template->set('TPL_ACTION', $aTplItem);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Delete" button. Leave empty.
        $this->Template->set('TPL_ITEM_BUTTON2', '');

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON3', $sStr);

        // ----------------------------------------------------------------

        $aTplItem = array();

        // Iterate through users
        $It = $Users->iterator();

        while ($Obj = $It->next()) {

            // Ignore root & guest users
            if ($Obj->get('userId') == 0 || $Obj->get('userId') == 65535) {
                continue;
            }

            // Get default user group
            $Group = $UserDAO->getGroupByGid($Obj->get('groupId'));

            // Get member group names
            $sMember = '';

            $GrpIt = $Obj->getMemberGroups()->iterator();

            // Concat group name string
            while ($GrpObj = $GrpIt->next()) {

                // Skip default group
                if ($GrpObj->get('groupId') == $Obj->get('groupId')) {
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

            // Default
            $aItem['ACTIVE'] = '';

            // Check if user storage has a user active flag
            if ($UserDAO->hasUserActiveFlag()) {
                $aItem['ACTIVE'] =  '<input';
                $aItem['ACTIVE'] .= ' name="active'.$Obj->get('userId').'"';
                $aItem['ACTIVE'] .= ' type="checkbox"';
                if ($Obj->get('isActive')) {
                    $aItem['ACTIVE'] .= ' checked="on"';
                }
                $aItem['ACTIVE'] .= '>';
            }

            $aItem['LOGIN']  = escape($Obj->get('login'));
            $aItem['NAME']   = escape($Obj->get('name'));
            $aItem['GROUP']  = is_object($Group) ? $Group->get('name') : '';
            $aItem['HREF']   = $this->Response->getControllerHref(
                'module=user&cmd='.CMD_EDITUSR.'&user='.$Obj->get('userId')
            );
            $aItem['MEMBER'] = $sMember;

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.admin.user.list.edit.tpl');
    }

} // of plugin component

/*
  If you find yourself alone riding in green fields with the sun on your
  face, do not be troubled, for you are in elysium and you are already dead.

  What we do in life echoes in eternity.
*/

?>
