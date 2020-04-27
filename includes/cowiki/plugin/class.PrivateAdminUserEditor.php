<?php

/**
 *
 * $Id: class.PrivateAdminUserEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminUserEditor
 * #purpose:   Edit a coWiki user
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      20. December 2002
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
 * coWiki - Edit a coWiki user
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
class PrivateAdminUserEditor extends AbstractPlugin {

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
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   Error
     */
    public function perform() {

        // Get user data access object
        $UserDAO = $this->Context->getUserDAO();
        $Groups = $UserDAO->getAllGroups();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // ----------------------------------------------------------------

        $bCreateNew = $this->Request->get('cmd') == CMD_NEWUSR;

        // ----------------------------------------------------------------

        // Is this an existing user, or are we editing a new one?
        // Create appropriate object.
        if ($this->Request->has('user') && !$bCreateNew) {

            $nUid = (int)$this->Request->get('user');

            // Check for reserved user ids, leave if necessary
            if ($nUid == 0 || $nUid == 65535) {
                // FIX: Error
                $this->Context->addError(0);
                return true;
            }

            // We are editing an existing user
            $Edit = $UserDAO->getUserByUid($nUid);

        } else {

            // Create a new user
            $Edit = new User();

            // New users are belong to guest group by default
            $Edit->set('groupId', 65535);

            // New users are active by default
            $Edit->set('isActive', true);
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Populate the edit-node with posted data
                $Edit->set('recTan',  $this->Request->get('rec_tan'));

                $Edit->set('name',    $this->Request->get('name'));
                $Edit->set('login',   $this->Request->get('login_admin'));
                $Edit->set('email',   $this->Request->get('email'));
                $Edit->set('groupId', (int)$this->Request->get('groupId'));

                // Set expiration statically by now
                $Edit->set('expires', 1920000000);

                // Set password only if it has been changed, otherwise
                // set it to "null". A "null" password must be handled by
                // the DAO as if no changes on password are needed.
                // If we have a password string, it has to be passed plain
                // (not crypted), but without any magic quotes.
                // Plausibility/encryption have to be done by the DAO.
                if ($this->Request->get('passwd_admin') == '') {
                    $Edit->set('password', null);
                } else {
                    $Edit->set(
                        'password',
                        $this->Request->get('passwd_admin')
                    );
                    $Edit->set(
                        'passwordIsEncrypted',
                        $this->Request->has('passwd_admin_crypted')
                    );
                }

                // ---

                $Edit->set('isActive', false);
                $Edit->set('isLocked', false);

                if ($this->Request->has('active')) {
                    $Edit->set('isActive', true);
                }

                // ---

                // Set member groups
                $Member = new Vector;
                $It = $Groups->iterator();

                while ($Obj = $It->next()) {
                    // Skip default group
                    if ($Obj->get('groupId') == $Edit->get('groupId')) {
                        continue;
                    }

                    if ($this->Request->has('member'.$Obj->get('groupId'))) {
                        $Member->add($Obj);
                    }
                }

                $Edit->setMemberGroups($Member);

                // --------------------------------------------------------

                // SAVE
                if ($UserDAO->storeUser($Edit)) {

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController('module=user');
                }
            }

            // ------------------------------------------------------------

            // Main form: DELETE? Get confirmation.
            if ($this->Request->has('button_delete')) {

                // Form action
                $this->Template->set(
                    'TPL_FORM_ACTION',
                    $this->Response->getControllerAction()
                );

                // Form control data
                $sStr = '<input type="hidden" name="submit"'
                        .' value="'.$this->Context->getSubmitId().'">';

                $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

                // ---

                // Form button
                $this->Template->set(
                    'TPL_ITEM_CONFIRM_HEADER',
                    __('I18N_ADMIN_USER_HEAD_DELETE')
                );

                // Form button
                $this->Template->set(
                    'TPL_ITEM_CONFIRM_TEXT',
                    sprintf(
                      __('I18N_ADMIN_USER_CONFIRM_DELETE'),
                      escape($Edit->get('login')),
                      $Edit->get('userId')
                    )
                );

                // ---

                // "Delete" button
                $sStr =  '<input type="submit" name="button_confirm_delete"';
                $sStr .= ' class="submit" value="'.__('I18N_DELETE').'">';

                $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

                // "Cancel" button
                $sStr =  '<input type="submit" name="button_confirm_cancel"';
                $sStr .= ' class="submit" value="'.__('I18N_CANCEL').'">';

                $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

                // ---

                // Parse template
                $Tpl = $this->Context->getTemplateProcessor();
                echo $Tpl->parse('plugin.confirm.twobutton.tpl');

                // As this is a confirmation form, leave this plugin now
                return true;
            }

            // ------------------------------------------------------------

            // Main form: CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // {{{ DEBUG }}}
                Logger::info('Redirecting to controller (CANCEL)');

                // Exit now
                $this->Response->redirectToController('module=user');
            }

            // ------------------------------------------------------------

            // Confirmation form: DELETE?
            if ($this->Request->has('button_confirm_delete')) {
                if ($UserDAO->removeUser($Edit)) {

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (DELETE)');

                    // Exit now
                    $this->Response->redirectToController('module=user');
                }
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
        $sStr .= '<input type="hidden" name="rec_id"'
                  .' value="'.$Edit->get('userId').'">';
        $sStr .= '<input type="hidden" name="rec_tan"'
                  .' value="'.$Edit->get('recTan').'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ---

        // User name
        $sStr = '<input type="text" name="name" size="32"';
        $sStr .=  ' maxlength="32" value="'.escape($Edit->get('name')).'">';
        $this->Template->set('TPL_ITEM_NAME', $sStr);

        // User login
        $sStr = '<input type="text" name="login_admin" size="32"';
        $sStr .=  ' maxlength="8" value="'.escape($Edit->get('login')).'">';
        $this->Template->set('TPL_ITEM_LOGIN', $sStr);

        // User password
        $sStr = '<input type="password" name="passwd_admin" size="32"';
        $sStr .=  ' value="">';
        $this->Template->set('TPL_ITEM_PASSWORD', $sStr);

        // User password is already crypted
        $sStr = '<input type="checkbox" name="passwd_admin_crypted">';
        $this->Template->set('TPL_ITEM_PASSWORD_CRYPTED', $sStr);

        // User email
        $sStr = '<input type="text" name="email" size="32"';
        $sStr .=  ' maxlength="64" value="'.escape($Edit->get('email')).'">';
        $this->Template->set('TPL_ITEM_EMAIL', $sStr);

        // ---

        // Iterate through groups
        $It = $Groups->iterator();

        $sStr = '<select name="groupId" size="1">';

        while ($Obj = $It->next()) {

            $sStr .=  '<option value="'.$Obj->get('groupId').'"';
            if ($Obj->get('groupId') == $Edit->get('groupId')) {
                $sStr .= ' selected="true"';
            }
            $sStr .=  '>';
            $sStr .=    escape($Obj->get('name'));
            $sStr .=  '</option>';
        }

        $sStr .= '</select>';

        $this->Template->set('TPL_ITEM_GROUP_DEFAULT', $sStr);

        // ---

        // Active
        $sStr = 'n/a';
        if ($UserDAO->hasUserActiveFlag()) {
            $sStr =  '<input type="checkbox" name="active" value="on"';
            if ($Edit->get('isActive')) {
                $sStr .= ' checked="true"';
            }
            $sStr .= '/>';
        }

        $this->Template->set('TPL_ITEM_ACTIVE', $sStr);

        // ---

        // Get member groups of the user we are editing
        $Member = $Edit->getMemberGroups();

        // Iterate through all groups
        $It = $Groups->iterator();

        while ($Obj = $It->next()) {

            $aItem = array();

            $sStr = '<input type="checkbox" value="on"';
            $sStr .= ' name="member'.$Obj->get('groupId').'"';
            if ($Member->contains($Obj)) {
                $sStr .= ' checked="true"';
            }
            $sStr .= '/>';

            $aItem['CHECKBOX'] = $sStr;
            $aItem['NAME'] = escape($Obj->get('name'));
            $aItem['DESC'] = escape(cutOff($Obj->get('description'), 20));

            // ---

            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM_MEMBER', $aTplItem);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Delete" button. Leave empty if we are creating a new record
        if ($Edit->get('userId') == 0) {
            $sStr = '';
        } else {
            $sStr =  '<input type="submit" name="button_delete"';
            $sStr .= ' class="submit" value="'.__('I18N_DELETE').'">';
        }

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON3', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.admin.user.edit.tpl');
    }

} // of plugin component

?>
