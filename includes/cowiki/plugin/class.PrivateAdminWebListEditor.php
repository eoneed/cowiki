<?php

/**
 *
 * $Id: class.PrivateAdminWebListEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminWebListEditor
 * #purpose:   Edit web list in admin panel
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
 * coWiki - Edit web list in admin panel
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
class PrivateAdminWebListEditor extends AbstractPlugin {

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
     * @todo    [FIX]   Check and improve
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        // Set plugin parameters, if given by a plugin call, or set
        // defaults
        $nCutOff = $this->Context->getPluginParam('cutoff')
                    ? $this->Context->getPluginParam('cutoff')
                    : 35;

        // ----------------------------------------------------------------

        // Get possible node structure data from session
        $SessNode = $this->Context->getSessionVar('node');

        // If node from session is equal to the current node
        if (is_object($SessNode) && $SessNode->get('id') == $Node->get('id')) {
            $Node = $SessNode;
        } else {
            // Get all webs
            $Node = $DocDAO->getWebComposite();
        }

        // ----------------------------------------------------------------

        // Lets work with $Edit for consistency with other plugins
        $Edit = $Node;

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Set changed child node properties
            $It = $Edit->getItems()->iterator();

            while ($Obj = $It->next()) {

                // Set "in menu"
                $Obj->set('isInMenu', false);
                if ($this->Request->has('menu'.$Obj->get('id'))) {
                    $Obj->set('isInMenu', true);
                }

                // Set "in footer"
                $Obj->set('isInFooter', false);
                if ($this->Request->has('footer'.$Obj->get('id'))) {
                    $Obj->set('isInFooter', true);
                }
            }

            // ------------------------------------------------------------

            // Define defaults
            $nSortUpId = null;
            $nSortDownId = null;

            // SORT UP or SORT DOWN?
            $It->reset();
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

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Set sort order
                $nSort = 10;

                $It->reset();
                while ($Obj = $It->next()) {
                    $Obj->set('sortOrder', $nSort);
                    $nSort += 10;
                }

                // SAVE
                if ($DocDAO->storeWithLazyChildren($Edit, false)) {

                    // Unset node structure in session before leaving
                    $this->Context->unsetSessionVar('node');

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

                // Unset node structure in session before leaving
                $this->Context->unsetSessionVar('node');

                // {{{ DEBUG }}}
                Logger::info('Redirecting to controller (CANCEL)');
                    
                // Exit now
                $this->Response->redirectToController();
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
        $sStr = '<input type="hidden" name="submit"'
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
        $aItem['NAME']   = __('I18N_DIR_NEW');
        $aItem['TARGET'] = '_self';
        $aItem['HREF']   = $this->Response->getControllerHref(
                              'module=struct&cmd='.CMD_NEWWEB
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

        $sImgPath = $this->Registry->get('PATH_IMAGES');
        $aTplItem = array();

        // Iterate through container children
        $It = $Edit->getItems()->iterator();

        while ($Obj = $It->next()) {

            $aItem = array();

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

            // Get user & group objects
            $User = $UserDAO->getUserByUid($Obj->get('userId'));
            $Group = $UserDAO->getGroupByGid($Obj->get('groupId'));

            // Get user login and group name
            $sUser  = $User  ? $User->get('login') : $Node->get('userId');
            $sGroup = $Group ? $Group->get('name') : $Node->get('groupId');

            $aItem['MODE']  = $Obj->getAccessModeAsString();
            $aItem['USER']  = $sUser;
            $aItem['GROUP'] = $sGroup;

            // Image and token
            $aItem['ICON'] =  '<img src="'.$sImgPath.'dir.gif" width="18"';
            $aItem['ICON'] .= ' height="20" alt="'.__('I18N_DIR').'"';
            $aItem['ICON'] .= ' border="0">';
            $aItem['TOKEN'] = __('I18N_DIR_TOKEN');

            // Entry name
            $aItem['NAME'] = escape(cutOff($Obj->get('name'), $nCutOff));

            $aItem['HREF'] = $this->Response->getControllerHref(
                                'module=struct&cmd='.CMD_EDITWEB
                                .'&web='.$Obj->get('id')
                             );

            $aItem['CTIME'] = $this->Context->makeDateTimeRelative(
                                  $Obj->get('created')
                              );

            $aItem['MTIME'] = $this->Context->makeDateTimeRelative(
                                  $Obj->get('modified')
                              );

            // Entry item visible in menu?
            $aItem['IN_MENU'] =  '<input';
            $aItem['IN_MENU'] .= ' name="menu'.$Obj->get('id').'"';
            $aItem['IN_MENU'] .= ' type="checkbox"';
            if ($Obj->get('isInMenu')) {
                $aItem['IN_MENU'] .= ' checked="on"';
            }
            $aItem['IN_MENU'] .= '>';

            // Entry is in footer menu?
            $aItem['IN_FOOTER'] =  '<input';
            $aItem['IN_FOOTER'] .= ' name="footer'.$Obj->get('id').'"';
            $aItem['IN_FOOTER'] .= ' type="checkbox"';
            if ($Obj->get('isInFooter')) {
                $aItem['IN_FOOTER'] .= ' checked="on"';
            }
            $aItem['IN_FOOTER'] .= '>';

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ---

        $sStr = ' <input name="index" type="radio" value="0">';

        $this->Template->set('TPL_ITEM_NO_INDEX', $sStr);

        // ----------------------------------------------------------------

        // Clear template variable if we have no children
        if ($Edit->getItems()->isEmpty()) {
            $this->Template->set('TPL_ITEM', null);
        }

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.admin.web.list.edit.tpl');

        // FIX: Check and improve
        // Fast solution for a strange behaviour: Session data won't be
        // saved if set in this plugin with "setSessionVar()". Setting
        // "session_write_close()" globally in "core.finish" leads
        // to unreproducible crashes! The whole thing is broken at this time
        // in PHP5. This should be a temporary patch. Fix it.
        session_write_close();
    }

} // of plugin component

?>
