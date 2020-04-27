<?php

/**
 *
 * $Id: class.PrivateAdminWebEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminWebEditor
 * #purpose:   Edit a web
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
 * coWiki - Edit a web
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
class PrivateAdminWebEditor extends AbstractPlugin {

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
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // ----------------------------------------------------------------

        $bCreateNew = $this->Request->get('cmd') == CMD_NEWWEB;

        // ----------------------------------------------------------------

        // Check if node is a web, we can not edit a document here
        if (!$bCreateNew && !$Node->get('isWeb')) {
            $this->Context->addError(404);  // Not found
            return true;
        }

        // ----------------------------------------------------------------

        // Is this an existing document, or are we editing a new one?
        // Create appropriate object.
        if ($Node && !$bCreateNew) {

            // We are editing an existing directory
            $Edit = $Node;

        } else {

            // Create a new (empty) DocumentContainer
            $Edit = new DocumentContainer();

            // The new tree id is equal to the tree id of the parent node
            $Edit->set('treeId', 0);

            // For a document, the parent id is equal to the id of its
            // container
            $Edit->set('parentId', 0);

            // Set user and group ids
            $Edit->set('userId', $CurrUser->get('userId'));
            $Edit->set('groupId', $CurrUser->get('groupId'));

            // Set default creation mask
            $Edit->setAccessByUmask(
                0777,
                $this->Registry->get('RUNTIME_UMASK')
            );
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Populate the edit-node with posted data
                $Edit->set('id',     $this->Request->get('rec_id'));
                $Edit->set('recTan', $this->Request->get('rec_tan'));

                $Edit->set('name',    trim($this->Request->get('name')));
                $Edit->set('userId',  (int)$this->Request->get('user_id'));
                $Edit->set('groupId', (int)$this->Request->get('group_id'));

                $Edit->set(
                    'isUserReadable',
                    $this->Request->get('user_read')
                );
                $Edit->set(
                    'isUserWritable',
                    $this->Request->get('user_write')
                );
                $Edit->set(
                    'isUserExecutable',
                    $this->Request->get('user_exec')
                );
                $Edit->set(
                    'isGroupReadable',
                    $this->Request->get('group_read')
                );
                $Edit->set(
                    'isGroupWritable',
                    $this->Request->get('group_write')
                );
                $Edit->set(
                    'isGroupExecutable',
                    $this->Request->get('group_exec')
                );
                $Edit->set(
                    'isWorldReadable',
                    $this->Request->get('world_read')
                );
                $Edit->set(
                    'isWorldWritable',
                    $this->Request->get('world_write')
                );
                $Edit->set(
                    'isWorldExecutable',
                    $this->Request->get('world_exec')
                );

                $Edit->set('isWeb', true);

                // --------------------------------------------------------

                // SAVE
                if ($DocDAO->store($Edit)) {

                    // Reset session data from previous screen. This is
                    // not really nice, but has to work for now.
                    $this->Context->unsetSessionVar(
                        'node',
                        'privateadminweblisteditor'
                    );

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController('module=struct');
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
                    __('I18N_DIR_HEAD_DELETE')
                );

                // Form button
                $this->Template->set(
                    'TPL_ITEM_CONFIRM_TEXT',
                    sprintf(
                      __('I18N_DIR_CONFIRM_DELETE'),
                      escape($Edit->get('name'))
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
                $this->Response->redirectToController('module=struct');
            }

            // ------------------------------------------------------------

            // Confirmation form: DELETE?
            if ($this->Request->has('button_confirm_delete')) {
                if ($DocDAO->remove($Edit)) {

                    // Reset session data from previous screen. This is
                    // not really nice, but has to work for now.
                    $this->Context->unsetSessionVar(
                        'node',
                        'privateadminweblisteditor'
                    );

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (DELETE)');

                    // Exit now
                    $this->Response->redirectToController('module=struct');
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
                  .' value="'.$Edit->get('id').'">';
        $sStr .= '<input type="hidden" name="tree_id"'
                  .' value="'.$Edit->get('treeId').'">';
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

        // Directory name
        $sStr = '<input type="text" name="name" size="';
        $sStr .=  $this->Registry->get('EDIT_DIR_NAME_INPUT_WIDTH').'"';
        $sStr .=  ' maxlength="128" value="'.escape($Edit->get('name')).'">';

        $this->Template->set('TPL_ITEM_NAME', $sStr);

        // ----------------------------------------------------------------

        // User options
        $sStr = '';
        $It = $this->Context->getUserDAO()->getAllUsers(false)->iterator();

        while ($Obj = $It->next()) {
            $sStr .= '<option value="'.$Obj->get('userId').'"';

            if ($Edit->get('userId') === $Obj->get('userId')) {
                $sStr .= ' selected="true"';
            }

            $sStr .= '>';
            $sStr .=    escape($Obj->get('login'));
            $sStr .= '</option>';
        }

        $sStr = '<select name="user_id">' . $sStr . '</select>';

        $this->Template->set('TPL_ITEM_USER_OPTIONS', $sStr);

        // ----------------------------------------------------------------

        // Group options
        $sStr = '';
        $It = $this->Context->getUserDAO()->getAllGroups()->iterator();

        while ($Obj = $It->next()) {
            $sStr .= '<option value="'.$Obj->get('groupId').'"';

            if ($Edit->get('groupId') === $Obj->get('groupId')) {
                $sStr .= ' selected="true"';
            }

            $sStr .= '>';
            $sStr .=    escape($Obj->get('name'));
            $sStr .= '</option>';
        }

        $sStr = '<select name="group_id">' . $sStr . '</select>';

        $this->Template->set('TPL_ITEM_GROUP_OPTIONS', $sStr);

        // ----------------------------------------------------------------

        // User access bits
        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="user_read"';
        $sStr .=    $Edit->get('isUserReadable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_USER_READ', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="user_write"';
        $sStr .=    $Edit->get('isUserWritable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_USER_WRITE', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="user_exec"';
        $sStr .=    $Edit->get('isUserExecutable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_USER_EXEC', $sStr);

        // ---

        // Group access bits
        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="group_read"';
        $sStr .=    $Edit->get('isGroupReadable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_GROUP_READ', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="group_write"';
        $sStr .=    $Edit->get('isGroupWritable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_GROUP_WRITE', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="group_exec"';
        $sStr .=    $Edit->get('isGroupExecutable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_GROUP_EXEC', $sStr);

        // ---

        // World access bits
        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="world_read"';
        $sStr .=    $Edit->get('isWorldReadable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_WORLD_READ', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="world_write"';
        $sStr .=    $Edit->get('isWorldWritable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_WORLD_WRITE', $sStr);

        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="world_exec"';
        $sStr .=    $Edit->get('isWorldExecutable') ? ' checked="true"' : '';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ACCESS_WORLD_EXEC', $sStr);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Delete" button. Leave empty if we are creating a new document
        if ($Edit->get('id') == 0) {
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
        echo $Tpl->parse('plugin.admin.web.edit.tpl');
    }

} // of plugin component

?>
