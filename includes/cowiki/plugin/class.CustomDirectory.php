<?php

/**
 *
 * $Id: class.CustomDirectory.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomDirectory
 * #purpose:   A simple plugin example for documentation purposes
 * #param:     id       id of the directory to list
 *                      (required, default: 1)
 * #param:     title    headline of output (default: none)
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      03.08.2007
 * #author:    Alexander Klein, <a.klein@eoneed.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 * @copyright   (C) Alexander Klein, {@link http://ageless.de}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

 /**
 * coWiki - A simple plugin example for documentation purposes
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 */
class CustomDirectory extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean true if initialization successful, false otherwise
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform the plugin purpose. This is the main method of the plugin.
     *
     * @access  public
     * @return  void
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function perform() {

        $Curr = $this->Context->getCurrentNode();

        $nParamId = $this->Context->getPluginParam('id')
                  ? $this->Context->getPluginParam('id')
                  : 1;

        // ----------------------------------------------------------------

        $DocumentDAO = $this->Context->getDocumentDAO();
        $Node = $DocumentDAO->getNodeById($nParamId);

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        if ($Node->get('id') == 0) {
            $this->Context->addError(404);          // Not found
            $this->Context->resume();               // Do not stop script
            return true;
        }

        // Check user access
        if (!$Node->isReadable()
         || !$Node->isExecutable()) {
            $this->Context->addError(403);          // Forbidden
            $this->Context->resume();               // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        $sTplTitle = $this->Context->getPluginParam('title')
                   ? $this->Context->getPluginParam('title')
                   : $Node->get('title');

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

        	  if ($Obj->get('id') == $Curr->get('id')) {
        	  	continue;
        	  }

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
        $this->Template->set('TPL_TITLE', $sTplTitle);

        // ---

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.directory.tpl');
    }

} // of plugin component

/*

    Schenk mir deine Seele
    Ich trinke deinen Schmerz
    Dein Blut ist meine Sehnsucht
    Dein Fleisch bricht mir das Herz

    Schenk mir deine Seele
    Bist du dafür bereit
    Wie du es einst geschworen hast
    Vor langer Zeit

*/

?>
