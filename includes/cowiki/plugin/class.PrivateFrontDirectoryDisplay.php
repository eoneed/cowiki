<?php

/**
 *
 * $Id: class.PrivateFrontDirectoryDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontDirectoryDisplay
 * #purpose:   Display the content of directory node
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
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
 * coWiki - Display the content of directory node
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
class PrivateFrontDirectoryDisplay extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

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

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        if ($Node->get('id') == 0) {
            $this->Context->addError(404);          // Not found
            $this->Context->resume();               // Do not stop script
            return true;
        }

        // Check user access
        if (!$Node->isReadable() || !$Node->isExecutable()) {
            $this->Context->addError(403);          // Forbidden
            $this->Context->resume();               // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Set plugin parameters, if given by a plugin call, or set defaults
        $nCutOff = $this->Context->getPluginParam('cutoff')
                    ? $this->Context->getPluginParam('cutoff')
                    : 55;

        // ----------------------------------------------------------------

        $aTplItem = array();
        $sImgPath = $this->Registry->get('PATH_IMAGES');

        // ----------------------------------------------------------------

        // Show self
        $aItem = array();

        // Get user & group objects
        $User = $UserDAO->getUserByUid($Node->get('userId'));
        $Group = $UserDAO->getGroupByGid($Node->get('groupId'));

        // Get user login and group name
        $sUser  = $User  ? $User->get('login') : $Node->get('userId');
        $sGroup = $Group ? $Group->get('name') : $Node->get('groupId');

        $aItem['MODE']  = $Node->getAccessModeAsString();
        $aItem['USER']  = $sUser;
        $aItem['GROUP'] = $sGroup;

        $aItem['NAME']  = '.';

        if ($Node->get('isContainer')) {
            $aItem['ICON'] =  '<img src="'.$sImgPath.'dir.gif" width="18"';
            $aItem['ICON'] .= ' height="20" alt="'.__('I18N_DIR').'"';
            $aItem['ICON'] .= ' border="0">';
        } else {
            $aItem['ICON'] =  '<img src="'.$sImgPath.'doc.gif" width="18"';
            $aItem['ICON'] .= ' height="20" alt="'.__('I18N_DOC').'"';
            $aItem['ICON'] .= ' border="0">';
        }

        if ($Node->get('isContainer')) {
            $aItem['TOKEN'] = __('I18N_DIR_TOKEN');
        } else {
            $aItem['TOKEN'] = __('I18N_DOC_TOKEN');
        }

        $aItem['HREF'] = $this->Response->getControllerHref(
                              'node=' . $Node->get('id')
                          );

        $aItem['CTIME'] = $this->Context->makeDateTimeRelative(
                              $Node->get('created')
                          );

        $aItem['MTIME'] = $this->Context->makeDateTimeRelative(
                              $Node->get('modified')
                          );

        // Append item to template items
        $aTplItem[] = $aItem;

        // ----------------------------------------------------------------

        // Show parent if this node has one
        $Parent = $Node->get('parent');

        if (is_object($Parent)) {
            $aItem = array();

            // Get user & group objects
            $User = $UserDAO->getUserByUid($Parent->get('userId'));
            $Group = $UserDAO->getGroupByGid($Parent->get('groupId'));

            // Get user login and group name
            $sUser  = $User  ? $User->get('login') : $Parent->get('userId');
            $sGroup = $Group ? $Group->get('name') : $Parent->get('groupId');

            $aItem['MODE']  = $Parent->getAccessModeAsString();
            $aItem['USER']  = $sUser;
            $aItem['GROUP'] = $sGroup;

            $aItem['NAME'] = '..';

            if ($Parent->get('isContainer')) {
                $aItem['ICON'] =  '<img src="'.$sImgPath.'dir.gif"';
                $aItem['ICON'] .= ' width="18" height="20"';
                $aItem['ICON'] .= ' alt="'.__('I18N_DIR').'" border="0">';
            } else {
                $aItem['ICON'] =  '<img src="'.$sImgPath.'doc.gif"';
                $aItem['ICON'] .= ' width="18" height="20"';
                $aItem['ICON'] .= ' alt="'.__('I18N_DOC').'" border="0">';
            }

            if ($Parent->get('isContainer')) {
                $aItem['TOKEN'] = __('I18N_DIR_TOKEN');
            } else {
                $aItem['TOKEN'] = __('I18N_DOC_TOKEN');
            }

            $aItem['HREF'] = $this->Response->getControllerHref(
                                'node=' . $Parent->get('id')
                             );

            $aItem['CTIME'] = $this->Context->makeDateTimeRelative(
                                  $Parent->get('created')
                              );

            $aItem['MTIME'] = $this->Context->makeDateTimeRelative(
                                  $Parent->get('modified')
                              );

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        // ----------------------------------------------------------------

        // Get all directories and documents that belong to this directory
        $Node = $this->Context->getDocumentDAO()->getAllChildren($Node);

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {
            
            if (!$Obj->isReadable()) {
                continue;
            }

            $aItem = array();

            // Get user & group objects
            $User = $UserDAO->getUserByUid($Obj->get('userId'));
            $Group = $UserDAO->getGroupByGid($Obj->get('groupId'));

            // Get user login and group name
            $sUser  = $User  ? $User->get('login') : $Obj->get('userId');
            $sGroup = $Group ? $Group->get('name') : $Obj->get('groupId');

            $aItem['MODE']  = $Obj->getAccessModeAsString();
            $aItem['USER']  = $sUser;
            $aItem['GROUP'] = $sGroup;

            $aItem['NAME'] = escape(cutOff($Obj->get('name'), $nCutOff));

            if ($Obj->get('isContainer')) {
                $aItem['ICON'] =  '<img src="'.$sImgPath.'dir.gif"';
                $aItem['ICON'] .= ' width="18" height="20"';
                $aItem['ICON'] .= ' alt="'.__('I18N_DIR').'" border="0">';
            } else {
                $aItem['ICON'] =  '<img src="'.$sImgPath.'doc.gif"';
                $aItem['ICON'] .= ' width="18" height="20"';
                $aItem['ICON'] .= ' alt="'.__('I18N_DOC').'" border="0">';
            }

            if ($Obj->get('isContainer')) {
                $aItem['TOKEN'] = __('I18N_DIR_TOKEN');
            } else {
                $aItem['TOKEN'] = __('I18N_DOC_TOKEN');
            }

            $aItem['HREF'] = $this->Response->getControllerHref(
                                'node='.$Obj->get('id')
                             );

            $aItem['CTIME'] = $this->Context->makeDateTimeRelative(
                                  $Obj->get('created')
                              );

            $aItem['MTIME'] = $this->Context->makeDateTimeRelative(
                                  $Obj->get('modified')
                              );

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ---

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.dir.display.tpl');
    }

} // of plugin component

/*
    When I became the sun, I shone life into the man's hearts.
*/

?>
