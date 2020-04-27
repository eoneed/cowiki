<?php

/**
 *
 * $Id: class.PrivateFrontHistoryCompare.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontHistoryCompare
 * #purpose:   Display current and historical versions of a document
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      03. February 2003
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
 * coWiki - Display current and historical versions of a document
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
class PrivateFrontHistoryCompare extends AbstractPlugin {

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
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // node is deleted
        $bDeleted = false;

        // Get DAOs
        $DocDAO  = $this->Context->getDocumentDAO();
        $UserDAO = $this->Context->getUserDAO();

        // Get current directory/document object
        if (!$OrgNode = $this->Context->getCurrentNode()) {
            $bDeleted = true;
            $OrgNode = $DocDAO->getHistNodeForId($this->Request->get('node'));
        }

        // Get historical directory/document object
        if (!$this->Request->has('histnode')) {
            $HistNode = $DocDAO->getHistNodeForId($OrgNode->get('id'));
        } else {
            $HistNode = $DocDAO->getHistNodeById(
                $this->Request->get('histnode')
            );
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // RECOVER?
            if ($this->Request->has('button_recover')) {
                // Go to history recover
                $sQuery = 'node=' . $OrgNode->get('id') .
                          '&histnode=' . $HistNode->get('histId') .
                          '&cmd=' . CMD_RECOVHIST;
                if ($this->Request->has('module')) {
                    $sQuery .= '&module=' . $this->Request->get('module');
                }
                $this->Response->redirectToController($sQuery);
            }

            // CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // Go back to history view
                $sQuery = 'node='.$OrgNode->get('id').'&cmd='.CMD_SHOWHIST;
                if ($this->Request->has('module')) {
                    $sQuery .= '&module=' . $this->Request->get('module');
                }
                $this->Response->redirectToController($sQuery);
            }
        }

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

        // Set defaults for error case
        $this->Template->set('TPL_ITEM_ORIG_NAME',      '');
        $this->Template->set('TPL_ITEM_ORIG_MODE',      '');
        $this->Template->set('TPL_ITEM_ORIG_USER',      '');
        $this->Template->set('TPL_ITEM_ORIG_GROUP',     '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_DATE',  '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_NAME',  '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_LOGIN', '');
        $this->Template->set('TPL_ITEM_ORIG_REVISION',  '');

        $this->Template->set('TPL_ITEM_HIST_NAME',      '');
        $this->Template->set('TPL_ITEM_HIST_MODE',      '');
        $this->Template->set('TPL_ITEM_HIST_USER',      '');
        $this->Template->set('TPL_ITEM_HIST_GROUP',     '');
        $this->Template->set('TPL_ITEM_HIST_MOD_DATE',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_NAME',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_LOGIN', '');
        $this->Template->set('TPL_ITEM_HIST_REVISION',  '');

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM_HIST_CONTENT',  '');
        $this->Template->set('TPL_ITEM_ORIG_CONTENT',  '');

        // If the current document (original) is forbidden, do not show
        // the historical one.
        $bOrgForb = false;

        // Set flag if current document and historical docment do not
        // share the same node id. Avoids lurking in different documents by
        // manipulating the URL.
        $bIdent = false;

        if ($HistNode) {
            $bIdent = $OrgNode->get('id') === $HistNode->get('id');
        }

        // Transform
        $Trans = FrontHtmlTransformer::getInstance();

        // Check access and transform original document
        if (!$OrgNode || $OrgNode->get('id') == 0) {
            $this->Context->addError(404);        // Not found
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_ORIG_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else if (!$OrgNode->isReadable()) {
            // Display of historical document is automatically forbidden too
            $bOrgForb = true;

            $this->Context->addError(403);        // Forbidden
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_ORIG_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else {

            // Get user & group objects
            $User = $UserDAO->getUserByUid($OrgNode->get('userId'));
            $Group = $UserDAO->getGroupByGid($OrgNode->get('groupId'));

            // Set template values
            $this->Template->set(
                'TPL_ITEM_ORIG_NAME',
                escape(cutOff($OrgNode->get('name'), 100))
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_MODE',
                $OrgNode->getAccessModeAsString()
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_USER',
                $User ? $User->get('login') : $OrgNode->get('userId')
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_GROUP',
                $Group ? $Group->get('name') : $OrgNode->get('groupId')
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_MOD_DATE',
                $this->Context->makeDateTimeRelative(
                    $OrgNode->get('modified')
                )
            );

            $UserObj = $UserDAO->getUserByUid($OrgNode->get('modifiedByUid'));

            $sLogin = $UserObj
                          ? $UserObj->get('login')
                          : $OrgNode->get('modifiedByUid');

            $sName = $UserObj
                          ? $UserObj->get('name')
                          : $OrgNode->get('modifiedByUid');

            $this->Template->set('TPL_ITEM_ORIG_MOD_LOGIN', $sLogin);
            $this->Template->set('TPL_ITEM_ORIG_MOD_NAME', $sName);

            if (!$bDeleted) {
                $this->Template->set(
                    'TPL_ITEM_ORIG_REVISION',
                    $OrgNode->get('revision').' ('.__('I18N_CURRENT').')'
                );
            } else {
                $this->Template->set(
                    'TPL_ITEM_ORIG_REVISION',
                    $OrgNode->get('revision').' ('.__('I18N_DELETED').')'
                );
            }

            $this->Template->set(
                'TPL_ITEM_ORIG_CONTENT',
                $Trans->transform($OrgNode)
            );
        }

        // ----------------------------------------------------------------

        // Check access and transform historical document
        if (!$HistNode || $HistNode->get('id') == 0) {
            $this->Context->addError(404);        // Not found
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_HIST_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else if (!$HistNode->isReadable() || $bOrgForb || !$bIdent) {
            $this->Context->addError(403);        // Forbidden
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_HIST_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else {

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

            $this->Template->set(
                'TPL_ITEM_HIST_CONTENT',
                $Trans->transform($HistNode, false)
            );

        }

        // ----------------------------------------------------------------

        // "Recover" button
        $sStr =  '<input type="submit" name="button_recover" class="submit"';
        $sStr .= ' value="'.__('I18N_RECOVER').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "OK/Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.history.compare.tpl');
    }

} // of plugin component

?>
