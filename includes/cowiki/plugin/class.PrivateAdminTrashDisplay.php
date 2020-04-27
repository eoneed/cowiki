<?php

/**
 *
 * $Id: class.PrivateAdminTrashDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      AdminTrashDisplay
 * #purpose:   Edit/Display restorable nodes list in admin panel
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      4. September 2003
 * #author:    Kai Schröder <k.schroeder@php.net>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  PrivateAdmin
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Kai Schröder, {@link http://kai.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Edit/display restorable nodes list in admin panel
 *
 * @package     plugin
 * @subpackage  PrivateAdmin
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateAdminTrashDisplay extends AbstractPlugin {

    // Put in the interface version the plugin works with.
    // This has nothing to do with the @version of this plugin!
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Init
     *
     * @access  public
     * @return  mixed
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
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
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        $aTplItem = array();

        $sRestore = 'module=trash&cmd='.CMD_SHOWHIST;

        $DelNode = $DocDAO->getHistDeletedNodes();
        $It = $DelNode->getItems()->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();

            // Get user
            $User = $UserDAO->getUserByUid($Obj->get('modifiedByUid'));
            $sUser = $User ? $User->get('login') : $Obj->get('userId');

            $aItem['DATE']     = $this->Context->makeDateTimeRelative(
                                    $Obj->get('modified')
                                 );
            $aItem['NAME']     = $Obj->get('name');
            $aItem['REVISION'] = $Obj->get('revision');
            $aItem['USER']     = $sUser;

            $aItem['HREF_RESTORE'] = $this->Response->getControllerHref(
                $sRestore.'&node='.$Obj->get('id')
            );

            $aTplItem[] = $aItem;

        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Form action
        $this->Template->set(
            'TPL_FORM_ACTION',
            $this->Response->getControllerAction()
        );

        // Form control data
        $sStr =  '<input type="hidden" name="submit"' .
                 ' value="'.$this->Context->getSubmitId().'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel"';
        $sStr .= ' class="submit" value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('admin.trash.list.tpl');

    }

} // of plugin component

?>
