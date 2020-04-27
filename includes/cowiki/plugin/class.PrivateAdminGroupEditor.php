<?php

/**
 *
 * $Id: class.PrivateAdminGroupEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminGroupEditor
 * #purpose:   Edit a coWiki group
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      27. December 2002
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
 * coWiki - Edit a coWiki group
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
class PrivateAdminGroupEditor extends AbstractPlugin {

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

        // ----------------------------------------------------------------

        $bCreateNew = $this->Request->get('cmd') == CMD_NEWGRP;

        // ----------------------------------------------------------------

        // Is this an existing group, or are we editing a new one?
        // Create appropriate object.
        if ($this->Request->has('group') && !$bCreateNew) {

            $nGid = (int)$this->Request->get('group');

            // Check for reserved group ids, leave if necessary
            if ($nGid == 0 || $nGid == 65535) {
                // FIX: Error
                $this->Context->addError(0);
                return true;
            }

            // We are editing an existing group
            $Edit = $UserDAO->getGroupByGid($nGid);

        } else {

            // Create a new group
            $Edit = new Group();
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Populate the edit-node with posted data
                $Edit->set('recTan',      $this->Request->get('rec_tan'));

                $Edit->set('name',        $this->Request->get('name'));
                $Edit->set('description', $this->Request->get('desc'));

                // SAVE
                if ($UserDAO->storeGroup($Edit)) {

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (SAVE)');

                    // Exit now
                    $this->Response->redirectToController('module=group');
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
                      __('I18N_ADMIN_GROUP_CONFIRM_DELETE'),
                      escape($Edit->get('name')),
                      $Edit->get('groupId')
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
                $this->Response->redirectToController('module=group');
            }

            // ------------------------------------------------------------

            // Confirmation form: DELETE?
            if ($this->Request->has('button_confirm_delete')) {
                if ($UserDAO->removeGroup($Edit)) {

                    // {{{ DEBUG }}}
                    Logger::info('Redirecting to controller (DELETE)');

                    // Exit now
                    $this->Response->redirectToController('module=group');
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
                  .' value="'.$Edit->get('groupId').'">';
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

        // Group name
        $sStr = '<input type="text" name="name" size="25" maxlength="8"';
        $sStr .=  ' value="'.escape($Edit->get('name')).'">';

        $this->Template->set('TPL_ITEM_NAME', $sStr);

        // Group description
        $sStr = '<input type="text" name="desc" size="25" maxlength="255"';
        $sStr .=  ' value="'.escape($Edit->get('description')).'">';

        $this->Template->set('TPL_ITEM_DESC', $sStr);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Delete" button. Leave empty if we are creating a new record
        if ($Edit->get('groupId') == 0) {
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
        echo $Tpl->parse('plugin.admin.group.edit.tpl');
    }

} // of plugin component

?>
