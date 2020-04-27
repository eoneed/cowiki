<?php

/**
 *
 * $Id: class.PrivateFrontDirectoryEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontDirectoryEditor
 * #purpose:   Edit a directory node and its children
 * #param:     cutoff  cut long document names after <tt>n</tt>
 *                     characters (70 is default)
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
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Edit a directory node and its children
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateFrontDirectoryEditor extends AbstractPlugin {

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

        $bCreateNew = $this->Request->get('cmd') == CMD_NEWDIR;

        // ----------------------------------------------------------------

        // Check user access.
        if (!$Node->isWritable()) {
            $this->Context->addError(403);            // Forbidden
            $this->Context->resume();                 // Do not stop script
            return true;
        }

        // Check if node is editable (if user tries to edit an existing one)
        // The difference between isWritable() and isEditable() is:
        // isEditable() does not care about world-access bits, which is
        // required in the "------rwx root wheel" case.
        if (!$bCreateNew && !$Node->isEditable()) {
            $this->Context->addError(403);           // Forbidden
            $this->Context->terminate(false);         // Do not stop script
            return true;
        }

        // Check if node is a directory, we can not edit a document here
        if (!$bCreateNew && !$Node->get('isContainer')) {
            $this->Context->addError(404);            // Not found
            $this->Context->resume();                 // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Set plugin parameters, if given by a plugin call, or set
        // defaults
        $nCutOff = $this->Context->getPluginParam('cutoff')
                      ?   $this->Context->getPluginParam('cutoff')
                      :   70;

        // ----------------------------------------------------------------

        // Get possible node structure data from session
        $SessNode = $this->Context->getSessionVar('node');

        // If node from session is equal to the current node
        if (is_object($SessNode)
            && $SessNode->get('id') == $Node->get('id')) {
            $Node = $SessNode;
        } else {
            // Get all directories and documents belonging to this dir node
            $Node = $DocDAO->getAllChildren($Node);
        }
        // ----------------------------------------------------------------

        // Is this an existing document, or are we editing a new one?
        // Create appropriate object.
        if ($Node && !$bCreateNew) {

            // We are editing an existing directory
            $Edit = $Node;

        } else {

            // Get current directory/document object (again), because it
            // might has been overwritten by session
            $Node = $this->Context->getCurrentNode();

            // Create a new (empty) DocumentContainer
            $Edit = new DocumentContainer();

            // The new tree id is equal to the tree id of the parent node
            $Edit->set('treeId', $Node->get('treeId'));

            // For a document, the parent id is equal to the id of its
            // container
            $Edit->set('parentId', $Node->get('id'));

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

                // --------------------------------------------------------

                // Set changed child node properties
                $It = $Edit->getItems()->iterator();

                while ($Obj = $It->next()) {

                    // Set index
                    if ($this->Request->has('index')) {
                        $Obj->set(
                            'isIndex',
                            $Obj->get('id') == $this->Request->get('index')
                        );
                    }

                    // Set "in menu"
                    $Obj->set('isInMenu', false);
                    if ($this->Request->has('menu'.$Obj->get('id'))) {
                        $Obj->set('isInMenu', true);
                    }
                }

                // --------------------------------------------------------

                // Set sort order
                $nSort = 10;

                $It->reset();
                while ($Obj = $It->next()) {
                    $Obj->set('sortOrder', $nSort);
                    $nSort += 10;
                }

                // SAVE
                if ($DocDAO->storeWithLazyChildren($Edit)) {

                    // Unset node structure in session before leaving
                    $this->Context->unsetSessionVar('node');

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController(
                        'node='.$Edit->get('id')
                    );
                }
            }

            // ------------------------------------------------------------

            // Define defaults
            $nSortUpId = null;
            $nSortDownId = null;

            // Main form: SORT UP or SORT DOWN?
            $It = $Edit->getItems()->iterator();

            while ($Obj = $It->next()) {

                // Lookup if we should move a child row up. First check
                // if an appropiate key is in the request.
                $nSortUpId = null;
                if ($this->Request->has('up_'.$Obj->get('id').'_x')) {
                    $nSortUpId = $Obj->get('id');
                    break;
                }

                // Lookup if we should move a child row down. First check
                // if an appropiate key is in the request.
                $nSortDownId = null;
                if ($this->Request->has('down_'.$Obj->get('id').'_x')) {
                    $nSortDownId = $Obj->get('id');
                    break;
                }
            }

            if ($nSortUpId) {
                $Edit->sortItemUp($nSortUpId);
            }

            if ($nSortDownId) {
                $Edit->sortItemDown($nSortDownId);
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

            // Main form: MOVE?
            if ($this->Request->has('button_move')){

                // Check if any markers are selected
                $It = $Edit->getItems()->iterator();

                $bSelected = false;
                while ($Obj = $It->next()) {
                    if ($this->Request->has('marker_'.$Obj->get('id'))) {
                        $bSelected = true;
                        break;
                    }
                }

                if (!$bSelected) {
                    $this->Context->addError(430);
                }
            }

            // ------------------------------------------------------------

            // Main form: CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // Unset node structure in session before leaving
                $this->Context->unsetSessionVar('node');

                // {{{ DEBUG }}}
                Logger::info('Redirecting to controller (CANCEL)');

                if ($this->Request->has('refnode')) {
                    $this->Response->redirectToController(
                        'node='.$this->Request->get('refnode')
                    );
                }

                $this->Response->redirectToController(
                    'node='.$Node->get('id')
                );
            }

            // ------------------------------------------------------------

            // Confirmation form: DELETE?
            if ($this->Request->has('button_confirm_delete')) {

                if ($DocDAO->remove($Edit)) {

                // Unset node structure in session before leaving
                $this->Context->unsetSessionVar('node');

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (DELETE)');

                    if ($Edit->has('parentId')) {
                        $this->Response->redirectToController(
                            'node='.$Edit->get('parentId')
                        );
                    } else {
                        $this->Response->redirectToController();
                    }
                }
            }
        }

        // ----------------------------------------------------------------

        // Remember structure in session
        $this->Context->setSessionVar('node', $Edit);

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

        if ($this->Request->has('refnode')) {
            $sStr .= '<input type="hidden" name="refnode"';
            $sStr .= ' value="'.$this->Request->get('refnode').'">';
        }

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);
        $this->Template->set('TPL_PLUGIN_ID', $this->Context->getSubmitId());

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

        $sImgPath = $this->Registry->get('PATH_IMAGES');
        $aTplItem = array();

        // Iterate through children
        $It = $Edit->getItems()->iterator();

        while ($Obj = $It->next()) {
            $bWritable = $Obj->isWritable();

            $aItem = array();

            $aItem['MARKER'] =  '<input';
            $aItem['MARKER'] .= ' name="marker_'.$Obj->get('id').'"';
            $aItem['MARKER'] .= ' type="checkbox"';
            $aItem['MARKER'] .= '>';

            // ---

            $aItem['BUTTON1'] = '&nbsp;';
            $aItem['BUTTON2'] = '&nbsp;';

            // Sort button "up", but not for the frist entry
            if (!$It->isFirst()) {
                $aItem['BUTTON1'] =  '<input type="image"';
                $aItem['BUTTON1'] .= ' style="border-width:0px"';
                $aItem['BUTTON1'] .= ' name="up_'.$Obj->get('id').'"';
                $aItem['BUTTON1'] .= ' src="'.$sImgPath.'up.gif"';
                $aItem['BUTTON1'] .= ' width="18" height="20"';
                $aItem['BUTTON1'] .= ' alt="'.__('I18N_UP').'" border="0"';
                $aItem['BUTTON1'] .= '/>';
            }

            // Sort button "down", but not for the last entry
            if (!$It->isLast()) {
                $aItem['BUTTON2'] =  '<input type="image"';
                $aItem['BUTTON2'] .= ' style="border-width:0px"';
                $aItem['BUTTON2'] .= ' name="down_'.$Obj->get('id').'"';
                $aItem['BUTTON2'] .= ' src="'.$sImgPath.'down.gif"';
                $aItem['BUTTON2'] .= ' width="18" height="20"';
                $aItem['BUTTON2'] .= ' alt="'.__('I18N_DOWN').'" border="0"';
                $aItem['BUTTON2'] .= '/>';
            }

            // ---

            // Entry icon image
            if ($Obj->get('isContainer')) {
                $aItem['IMAGE'] = 'dir.gif';
            } else {
                $aItem['IMAGE'] = 'doc.gif';
            }

            // ---

            // Entry name
            $aItem['NAME'] = escape(cutOff($Obj->get('name'), $nCutOff));

            // Entry item in menu?
            if ($bWritable) {
                $aItem['IN_MENU'] =  '<input';
                $aItem['IN_MENU'] .= ' name="menu'.$Obj->get('id').'"';
                $aItem['IN_MENU'] .= ' type="checkbox"';
                if ($Obj->get('isInMenu')) {
                    $aItem['IN_MENU'] .= ' checked="on"';
                }
                $aItem['IN_MENU'] .= '>';

            } else {
                $aItem['IN_MENU'] = '&nbsp;';
            }

            // ---

            // Entry is index file?
            $aItem['IS_INDEX'] = '&nbsp;';
            if ($bWritable && !$Obj->get('isContainer')) {

                $aItem['IS_INDEX'] =  '<input';
                $aItem['IS_INDEX'] .= ' name="index" type="radio"';
                $aItem['IS_INDEX'] .= ' value="'.$Obj->get('id').'"';
                if ($Obj->get('isIndex')) {
                    $aItem['IS_INDEX'] .= ' checked="on"';
                }
                $aItem['IS_INDEX'] .= '>';
            }

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ---

        $sStr = ' <input name="index" type="radio" value="0">';

        $this->Template->set('TPL_ITEM_NO_INDEX', $sStr);

        // ----------------------------------------------------------------

        // "Move" button
        $sStr =  '<input type="submit" name="button_move" class="submit"';
        $sStr .= ' value="'.__('I18N_MOVE').'">';

        $this->Template->set('TPL_ITEM_ACTION1', $sStr);
        
        // ----------------------------------------------------------------

        // Clear template variable if we have no children
        if ($Edit->getItems()->isEmpty()) {
            $this->Template->set('TPL_ITEM', null);
        }

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.dir.edit.tpl');
    }

} // of plugin component

?>
