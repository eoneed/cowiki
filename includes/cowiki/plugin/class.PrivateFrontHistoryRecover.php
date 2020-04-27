<?php

/**
 *
 * $Id: class.PrivateFrontHistoryRecover.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      FrontHistoryRecover
 * #purpose:   Recover a historical versions of a document
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      5. September 2003
 * #author:    Kai Schröder <k.schroeder@php.net>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Kai Schröder, {@link http://kai.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Recover a historical versions of a document
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateFrontHistoryRecover extends AbstractPlugin {

    // Put in the interface version the plugin works with.
    // This has nothing to do with the @version of this plugin!
    const REQUIRED_INTERFACE_VERSION = 1;

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

    /**
     * Perform
     *
     * @access  public
     * @return  mixed
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // Get historical document to recover
        $HistNode = $DocDAO->getHistNodeForRecover(
            $this->Request->get('histnode')
        );

        // Check result
        if (!$HistNode || $HistNode->get('histId') == 0) {
            $this->Context->addError(404); // Not found
            $this->Context->resume();      // Do not stop script
            return true;
        }

        if (!$HistNode->isReadable()) {
            $this->Context->addError(403); // Forbidden
            $this->Context->resume();      // Resumed
            return true;
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // Go back to history view
                $sQuery = 'node='.$HistNode->get('id').'&cmd='.CMD_SHOWHIST;
                if ($this->Request->has('module')) {
                    $sQuery .= '&module=' . $this->Request->get('module');
                }
                $this->Response->redirectToController($sQuery);
            }

            // RECOVER?
            if ($this->Request->has('button_confirm_recover')) {

                if ($DocDAO->recover($HistNode)) {
                    $sQuery = 'node='.$HistNode->get('id');
                    if ($this->Request->has('module')) {
                        $sQuery .= '&module=' . $this->Request->get('module');
                    }
                    $this->Response->redirectToController($sQuery);
                }

            }

        }

        // ----------------------------------------------------------------

        // Set defaults for error case
        $this->Template->set('TPL_ITEM_HIST_NAME',      '');
        $this->Template->set('TPL_ITEM_HIST_MODE',      '');
        $this->Template->set('TPL_ITEM_HIST_USER',      '');
        $this->Template->set('TPL_ITEM_HIST_GROUP',     '');
        $this->Template->set('TPL_ITEM_HIST_MOD_DATE',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_NAME',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_LOGIN', '');
        $this->Template->set('TPL_ITEM_HIST_REVISION',  '');

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

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ----------------------------------------------------------------

        // Get user & group objects
        $User = $UserDAO->getUserByUid($HistNode->get('userId'));
        $Group = $UserDAO->getGroupByGid($HistNode->get('groupId'));

        // Set template values
        $this->Template->set(
            'TPL_ITEM_HIST_NAME',
            escape(cutOff($HistNode->get('name'), 100))
        );

        $this->Template->set(
            'TPL_ITEM_HIST_MODE',
            $HistNode->getAccessModeAsString()
        );

        $this->Template->set(
            'TPL_ITEM_HIST_USER',
            $User ? $User->get('login') : $HistNode->get('userId')
        );

        $this->Template->set(
            'TPL_ITEM_HIST_GROUP',
            $Group ? $Group->get('name') : $HistNode->get('groupId')
        );

        $this->Template->set(
            'TPL_ITEM_HIST_MOD_DATE',
            $this->Context->makeDateTimeRelative(
                $HistNode->get('modified')
            )
        );

        $UserObj = $UserDAO->getUserByUid($HistNode->get('modifiedByUid'));

        $sLogin = $UserObj
                      ? $UserObj->get('login')
                      : $OrgNode->get('modifiedByUid');

        $sName = $UserObj
                      ? $UserObj->get('name')
                      : $OrgNode->get('modifiedByUid');

        $this->Template->set('TPL_ITEM_HIST_MOD_LOGIN', $sLogin);
        $this->Template->set('TPL_ITEM_HIST_MOD_NAME', $sName);

        $this->Template->set(
            'TPL_ITEM_HIST_REVISION',
            $HistNode->get('revision') . ' ('.__('I18N_OLD').')'
        );

        // Parse into raw coWiki representation
        $this->Template->set(
            'TPL_ITEM_HIST_CONTENT',
            FrontHtmlTransformer::getInstance()->transform($HistNode) . "\n"
        );

        // ----------------------------------------------------------------

        // "Recover" button
        if ($HistNode->isRecoverable()) {
            $sStr =  '<input type="submit" name="button_confirm_recover"' .
                     ' class="submit" value="'.__('I18N_RECOVER').'">';
        } else {
            $sStr = '&nbsp;';
        }
        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"' .
                 ' value="'.__('I18N_CANCEL').'">';
        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.history.recover.tpl');

    }

} // of plugin component

?>
