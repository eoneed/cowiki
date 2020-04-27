<?php

/**
 *
 * $Id: class.PrivateAdminGroupListEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminGroupListEditor
 * #purpose:   Edit/Display user group list in admin panel
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
 * coWiki - Edit/display user group list in admin panel
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
class PrivateAdminGroupListEditor extends AbstractPlugin {

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

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        // Get all groups
        $Groups = $UserDAO->getAllGroups();

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

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
        $aItem['NAME']   = __('I18N_GROUP_NEW');
        $aItem['TARGET'] = '_self';
        $aItem['HREF']   = $this->Response->getControllerHref(
                              'module=group&cmd='.CMD_NEWGRP
                           );

        $aTplItem[] = $aItem;

        $this->Template->set('TPL_ACTION', $aTplItem);

        // ----------------------------------------------------------------

        // "Save" button. Leave empty.
        $this->Template->set('TPL_ITEM_BUTTON1', '');

        // "Delete" button. Leave empty.
        $this->Template->set('TPL_ITEM_BUTTON2', '');

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_OK').'">';

        $this->Template->set('TPL_ITEM_BUTTON3', $sStr);

        // ----------------------------------------------------------------

        $aTplItem = array();

        // Iterate through users
        $It = $Groups->iterator();

        while ($Obj = $It->next()) {

            // Ignore wheel & guests groups
            if ($Obj->get('groupId') == 0 || $Obj->get('groupId') == 65535) {
                continue;
            }

            // ------------------------------------------------------------

            $aItem = array();

            $aItem['NAME'] = escape($Obj->get('name'));
            $aItem['DESC'] = escape($Obj->get('description'));
            $aItem['HREF'] = $this->Response->getControllerHref(
                'module=group&cmd='.CMD_EDITGRP.'&group='.$Obj->get('groupId')
            );

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.admin.group.list.edit.tpl');
    }

} // of plugin component

?>
