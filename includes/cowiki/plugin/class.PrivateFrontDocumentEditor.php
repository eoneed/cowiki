<?php

/**
 *
 * $Id: class.PrivateFrontDocumentEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontDocumentEditor
 * #purpose:   Edit a document node
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
 * coWiki - Edit a document node
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
class PrivateFrontDocumentEditor extends AbstractPlugin {

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
     * @todo    Define upload directory per config
     * @todo    Define allowed filextensions per config
     * @todo    Create thumbnails of uploaded images
     * @todo    Create filereferences
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // ----------------------------------------------------------------

        $bSubmitted = false;
        $bCreateNew = $this->Request->get('cmd') == CMD_NEWDOC;

        // ----------------------------------------------------------------

        // Check user access.
        if (!$Node->isWritable()) {
            $this->Context->addError(403);            // Forbidden
            $this->Context->resume();                 // Do not stop script
            return true;
        }

        // Check if node is a document, we can not edit a directory here
        if (!$bCreateNew && $Node->get('isContainer')) {
            $this->Context->addError(404);            // Not found
            $this->Context->resume();
            return true;
        }

        // Create an object to work on

        // Is this an existing document, or are we editing a new one?
        // Create appropriate object.
        if (!$bCreateNew && $Node)  {

            // We are editing an existing document
            $Edit = $Node;

            // Get ReverseParser
            $RevParser = CoWikiReverseParser::getInstance();

            // Parse into raw coWiki representation
            $Edit->set('content', $RevParser->parse($Edit->get('content')));

        } else {

            // Create a new (empty) DocumentItem
            $Edit = new DocumentItem();

            /**
             *
             */
            $bExtend = true;
            if ($bExtend == true) {

                $Parent = $DocDAO->getNodeById($Node->get('id'));

                $Edit->set('userId', $Parent->get('userId'));
                $Edit->set('groupId', $Parent->get('groupId'));

                $Edit->set('isUserReadable',
                    $Parent->get('isUserReadable')
                );
                $Edit->set('isUserWritable',
                    $Parent->get('isUserWritable')
                );
                $Edit->set('isUserExecutable',
                    $Parent->get('isUserExecutable')
                );
                $Edit->set('isGroupReadable',
                    $Parent->get('isGroupReadable')
                );
                $Edit->set('isGroupWritable',
                    $Parent->get('isGroupWritable')
                );
                $Edit->set('isGroupExecutable',
                    $Parent->get('isGroupExecutable')
                );
                $Edit->set('isWorldReadable',
                    $Parent->get('isWorldReadable')
                );
                $Edit->set('isWorldWritable',
                    $Parent->get('isWorldWritable')
                );
                $Edit->set('isWorldExecutable',
                    $Parent->get('isWorldExecutable')
                );
            }
            else {

                // Set user and user ids
                $Edit->set('userId', $CurrUser->get('userId'));
                $Edit->set('groupId', $CurrUser->get('groupId'));

                // Set default creation mask
                $Edit->setAccessByUmask(
                    0666,
                    $this->Registry->get('RUNTIME_UMASK')
                );
            }

            // ---

            // The new tree id is equal to the tree id of the parent node.
            $Edit->set('treeId', $Node->get('treeId'));

            // For a document, the parent id is equal to the id of its
            // container
            $Edit->set('parentId', $Node->get('id'));

            // Any name given?
            if ($this->Request->get('newdocname')) {
                $Edit->set('name', $this->Request->get('newdocname'));
            }

            // Set default content
            $sContent = str_replace(
                            '\n',
                            "\n",
                            $this->Registry->get('DOCUMENT_CONTENT_DEFAULT')
                        );
            $Edit->set('content', $sContent);
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                $Parser = CoWikiParser::getInstance();
                $sContent = $Parser->parse($this->Request->get('content'));

                /**
                 * test upload
                 * $this->Registry->get('PATH_TEMPLATE_ACTIVE')
                 */
                $aAllowedExt = preg_split('~,~',
                    $this->Registry->get('.DOCUMENT_UPLOAD_FILE_TYES'),
                    -1, PREG_SPLIT_NO_EMPTY
                );
                $sPattern = '~\.('.implode('|', $aAllowedExt).')$~U';

                if (isset($_FILES['attachment']['name'])) {
                    $aUpload = $_FILES['attachment'];

                    if (preg_match($sPattern, $aUpload['name'])) {
                    	$aPieces = explode('.', $aUpload['name']);

                    	$sExtension = array_pop($aPieces);

                    	$sBasename = $this->cleanFileName(
                    	    implode('.', $aPieces)
                    	);

                        $sSaveName = $this->Env->get('DOCUMENT_ROOT')
                                   . $this->Registry->get('.DOCUMENT_UPLOAD_PATH')
                                   . '/'.$sBasename.'.'.$sExtension;

                        $sWikiName = $this->Registry->get('.DOCUMENT_UPLOAD_PATH')
                                   . '/'.$sBasename.'.'.$sExtension;

                        move_uploaded_file($aUpload['tmp_name'], $sSaveName);

                        $sTmpContent = $this->Request->get('content');
                        $sAddition = '<plugin Embed src="'.$sWikiName.'">';

                        if (strpos($sTmpContent, $sAddition) === false) {
                            $sTmpContent .= "\r\n".$sAddition."\r\n";
                        }
                        $sContent = $Parser->parse($sTmpContent);
                    }
                }

                // -----

                // Populate the edit-node with posted data
                $Edit->set('id',     $this->Request->get('rec_id'));
                $Edit->set('recTan', $this->Request->get('rec_tan'));

                $Edit->set('name',     trim($this->Request->get('name')));
                $Edit->set('keywords', trim($this->Request->get('keywords')));
                $Edit->set('content',  $sContent);

                // "Guest" must not change it
                if ($CurrUser->get('userId') == 65535
                    && $CurrUser->get('groupId') == 65535) {

                } else {
                    $Edit->set(
                        'isCommentable',
                        $this->Request->get('comment') != null
                    );
                }

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
                $Edit->set(
                    'notifyUser',
                    $this->Request->get('notify_user')
                );
                $Edit->set(
                    'notifyGroup',
                    $this->Request->get('notify_group')
                );

                // "Minor changes" checkbox (documents owned by guest/guests
                // are always backuped)
                $bBackup = $CurrUser->get('userId') == 65535
                           && $CurrUser->get('groupId') == 65535;

                $bBackup = $bBackup
                           || $this->Request->get('minor_change') == null;

                // ------------------------------------------------------------

                // SAVE
                if ($DocDAO->store($Edit, $bBackup)) {

                    // do notifications
                    $this->doNotification();

                    // repair references
                    if ($bCreateNew) {
                        $nRefNode = $this->Request->get('refnode');
                        $sNewDocName = $this->Request->get('newdocname');
                        if ($RefNode = $DocDAO->getNodeById($nRefNode)) {
                            $sRefContent = $RefNode->get('content');
                            $RefNode->set(
                                'content',
                                str_replace(
                                    '<link strref="' . $sNewDocName .
                                    '">',
                                    '<link idref="' . $Edit->get('id') .
                                    '">',
                                    $sRefContent
                                )
                            );
                            if ($RefNode->isWritable()) {
                                $DocDAO->store($RefNode, $bBackup);
                            }
                        }
                    }

                    // Generate a RSS feed file(s)
                    RssManager::writeFeed('recent.rdf');

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController(
                        'node='.$Edit->get('id')
                    );
                }

                // Recover content if something went wrong
                $Edit->set('content', $this->Request->get('content'));
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

                // Form texts

                $this->Template->set(
                    'TPL_ITEM_CONFIRM_HEADER',
                    __('I18N_DOC_HEAD_DELETE')
                );

                $this->Template->set(
                    'TPL_ITEM_CONFIRM_TEXT',
                    sprintf(
                      __('I18N_DOC_CONFIRM_DELETE'),
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

                    // Generate a RSS feed file(s)
                    RssManager::writeFeed('recent.rdf');

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
            $sStr .= '<input type="hidden" name="refnode"'
                      .' value="'.$this->Request->get('refnode').'">';
        }

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ---

        // Document name
        $sStr = '<input type="text" name="name" size="';
        $sStr .=  $this->Registry->get('EDIT_DOC_NAME_INPUT_WIDTH').'"';
        $sStr .=  ' maxlength="128" value="'.escape($Edit->get('name')).'">';

        $this->Template->set('TPL_ITEM_NAME', $sStr);

        // ---

        // Document keywords
        $sStr = '<input type="text" name="keywords" size="';
        $sStr .=  $this->Registry->get('EDIT_DOC_KEYWORDS_INPUT_WIDTH').'"';
        $sStr .=  ' value="'.escape($Edit->get('keywords')).'"';
        $sStr .=  ' maxlength="255">';

        $this->Template->set('TPL_ITEM_KEYWORDS', $sStr);

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

        $sStr =   '<textarea wrap="virtual" name="content"';
        $sStr .=    ' rows="'.$this->Registry->get('EDIT_DOC_AREA_ROWS').'"';
        $sStr .=    ' cols="'.$this->Registry->get('EDIT_DOC_AREA_COLS').'"';
        $sStr .=  '>';
        $sStr .=    escape($Edit->get('content'));
        $sStr .=  '</textarea>';

        $this->Template->set('TPL_ITEM_CONTENT', $sStr);

        // ----------------------------------------------------------------

        /**
         * Disable uploads for guests
         */
        $sStr = '';
        $bEnabled = null;
        $sWarning = null;

        if ($CurrUser->get('userId') == 65535
         || $CurrUser->get('groupId') == 65535) {
        }
        else if (!is_writable($this->Env->get('DOCUMENT_ROOT').'/var/')) {
        	$sWarning = 'Upload directory not writeable';
        }
        else {
            $bEnabled = true;
        }

        $this->Template->set('TPL_ENABLE_UPLOAD', $bEnabled);
        $this->Template->set('TPL_UPLOAD_WARNING', $sWarning);

        $this->Template->set('TPL_UPLOAD_FILE_NAME', 'File');
        $this->Template->set('TPL_UPLOAD_MAX_MSG', 'Max Attachmentsize');
        $this->Template->set('TPL_UPLOAD_MAX_SIZE', ini_get('post_max_size'));
        $this->Template->set('TPL_UPLOAD_DISPOSTION_ATTACH', 'Attachment');
        $this->Template->set('TPL_UPLOAD_DISPOSTION_INLINE', 'Inline');

        // ----------------------------------------------------------------

        // "Minor change" checkbox (not for guests)
        $bDisable = $CurrUser->get('userId') == 65535
                    || $CurrUser->get('groupId') == 65535
                    || (int)$Edit->get('id') == 0;

        if ($bDisable) {
            $sDisabled = ' disabled="true"';
            $sChecked  = '';
        } else {
            $sDisabled = '';
            $sChecked  = ''; // alternatively ' checked="true"'
        }

        $sStr =  '<input type="checkbox"' . $sDisabled . $sChecked;
        $sStr .=    ' name="minor_change" value="on">';

        $this->Template->set('TPL_ITEM_MINOR_CHANGE', $sStr);

        // ----------------------------------------------------------------

        // "Allow comments" checkbox (not for guests)
        $bDisable = $CurrUser->get('userId') == 65535
                    || $CurrUser->get('groupId') == 65535;

        if ($bDisable) {
            $sDisabled = ' disabled="true"';
        } else {
            $sDisabled = '';
        }

        if ($Edit->get('isCommentable')) {
            $sChecked  = ' checked="true"';
        }

        $sStr =  '<input type="checkbox"' . $sDisabled . $sChecked;
        $sStr .=    ' name="comment" value="on"';
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_ALLOW_COMMENTS', $sStr);

        // ----------------------------------------------------------------

        // Notifications checkbox [user|group] (not for guests)

        $bDisable = $this->Registry->get('RUNTIME_NOTIFICATION_ALLOW') == 'off'
                    || $CurrUser->get('userId') == 65535
                    || $CurrUser->get('groupId') == 65535
                    || (int)$Edit->get('id') == 0;

        if ($bDisable) {
            $sDisabled = ' disabled="true"';
        } else {
            $sDisabled = '';
        }

        if ($Edit->get('notifyUser')) {
            $sCheckedUser  = ' checked="true"';
        }
        else { $sCheckedUser  = ''; }

        if ($Edit->get('notifyGroup')) {
            $sCheckedGroup  = ' checked="true"';
        }
        else { $sCheckedGroup  = ''; }

        $sStrUser =  '<input type="checkbox"' . $sDisabled . $sCheckedUser;
        $sStrUser .=    ' name="notify_user" value="on" disabled="disabled"';
        $sStrUser .= '>';

        $sStrGroup =  '<input type="checkbox"' . $sDisabled . $sCheckedGroup;
        $sStrGroup .=    ' name="notify_group" value="on" disabled="disabled"';
        $sStrGroup .= '>';

        $this->Template->set('TPL_ITEM_NOTIFICATION_USER', $sStrUser);
        $this->Template->set('TPL_ITEM_NOTIFICATION_GROUP', $sStrGroup);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.doc.edit.tpl');
    }

    // --------------------------------------------------------------------

    /**
     * Do notification
     *
     * @access  private
     *
     * @author  Franziskus Domig <fd@php.net>
     * @since   coWiki 0.4.0
     *
     * @todo    [D11N]  Check description
     * @todo    Finish mail routine! (add mail handler)
     */
    private function doNotification() {
        $Node = $this->Context->getCurrentNode();

        // Create Notify Array
        $aNotify = array(
            'notifyUser'  => $Node->get('notifyUser'),
            'notifyGroup' => $Node->get('notifyGroup')
        );

        if ($aNotify['notifyUser']) {

            $NotifyUser = $this->Context->getUserDAO()->getUserByUid(
                $Node->get('userId')
            );
            $ModifyUser = $this->Context->getCurrentUser();

            $aNotifyMailItems = array(
                'MAIL_FULL_NAME' => $this->Registry->get('MAIL_FULL_NAME'),
                'MAIL_RETURN_PATH' => $this->Registry->get('MAIL_RETURN_PATH'),
                'NOTIFY_USER' => $NotifyUser->get('name'),
                'NOTIFY_USER_EMAIL' => $NotifyUser->get('email'),
                'CHANGE_USER' => $ModifyUser->get('name') .
                    ' (' . $ModifyUser->get('login') . ')',
            );

            // do mail stuff
        }
        if ($aNotify['notifyGroup']) {

            // notify group routine

            // do mail stuff
        }
    }

    // --------------------------------------------------------------------

    /**
     * Clean up the Uploaded Filename
     *
     * @access  private
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     * @todo    Move to a custom class?
     */
    private function cleanFileName($sStr) {

        static $aTrans = array(
            'À' => 'A',  'Á' => 'A',  'Â' => 'A',  'Ã' => 'A',  'Ä' => 'Ae',
            'Å' => 'A',  'Æ' => 'Ae', 'Ç' => 'C',  'È' => 'E',  'É' => 'E',
            'Ê' => 'E',  'Ë' => 'E',  'Ì' => 'I',  'Í' => 'I',  'Î' => 'I',
            'Ï' => 'I',  'Ñ' => 'N',  'Ò' => 'O',  'Ó' => 'O',  'Ô' => 'O',
            'Õ' => 'O',  'Ö' => 'Oe', 'Ø' => 'O',  'Ù' => 'U',  'Ú' => 'U',
            'Û' => 'U',  'Ü' => 'Ue', 'Ý' => 'Y',
            'ß' => 'ss', 'à' => 'a',  'á' => 'a',  'â' => 'a',  'ã' => 'a',
            'ä' => 'ae', 'å' => 'a',  'æ' => 'ae', 'ç' => 'c',  'è' => 'e',
            'é' => 'e',  'ê' => 'e',  'ë' => 'e',  'ì' => 'i',  'í' => 'i',
            'î' => 'i',  'ï' => 'i',  'ñ' => 'n',  'ò' => 'o',  'ó' => 'o',
            'ô' => 'o',  'õ' => 'o',  'ö' => 'oe', 'ø' => 'o',  'ù' => 'u',
            'ú' => 'u',  'û' => 'u',  'ü' => 'ue', 'ý' => 'y',  'ÿ' => 'y',
            '±' => 'a',  'ê' => 'e',  'æ' => 'c',  'ó' => 'o',  '³' => 'l',
            'ñ' => 'n',  '¶' => 's',  '¿' => 'z',  '¼' => 'z',
            '¡' => 'A',  'Ê' => 'E',  'Æ' => 'C',  'Ó' => 'O',  '£' => 'L',
            'Ñ' => 'N',  '¦' => 'S',  '¯' => 'Z',  '¬' => 'Z'
        );
        $sStr = strtr($sStr, $aTrans);

        // Get rid of quotation mark
        $sStr = str_replace("'", '', $sStr);

        // Mark delimiters
        $sStr = preg_replace(
                    "#[\x20-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]#",
                    "\x01",
                    $sStr
                );

        // Get rid of special chars
        $sStr = preg_replace("#[^a-zA-Z0-9\x01]#", "", $sStr);

        // Recover delimiters as spaces
        $sStr = str_replace("\x01", " ", $sStr);

        // Capitalize the first character of each word
        return str_replace(" ", "_", strtolower($sStr));
    }

} // of plugin component

?>
