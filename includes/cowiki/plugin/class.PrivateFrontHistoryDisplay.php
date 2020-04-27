<?php

/**
 *
 * $Id: class.PrivateFrontHistoryDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontHistoryDisplay
 * #purpose:   Show historical versions of a document
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      30. January 2003
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
 * coWiki - Show historical versions of a document
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
class PrivateFrontHistoryDisplay extends AbstractPlugin {

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

        // Node is deleted
        $bDeleted = false;

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // Get current directory/document object
        if (!$Node = $this->Context->getCurrentNode()) {
            $bDeleted = true;
            $Node = $DocDAO->getHistNodeForId($this->Request->get('node'));
        }

        // Check result
        if (!$Node || $Node->get('id') == 0) {
            $this->Context->addError(404);          // Not found
            $this->Context->resume();               // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Check validity and user access
        if (!$Node->isReadable()) {

            // Robots are not permitted to spider this area or to follow
            // links
            $this->Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');

            $this->Context->addError(403);        // Forbidden
            $this->Context->resume();             // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                $sQuery = 'node='.$Node->get('id');
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
        $sStr =  '<input type="hidden" name="submit"' .
                 ' value="'.$this->Context->getSubmitId().'">';
        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ----------------------------------------------------------------

        // Prepare management URI's
        $sCompare = 'node='.$Node->get('id').'&cmd='.CMD_COMPHIST;
        if ($this->Request->has('module')) {
            $sCompare .= '&module='.$this->Request->get('module');
        }

        $sDiff    = 'node='.$Node->get('id').'&cmd='.CMD_DIFFHIST;
        if ($this->Request->has('module')) {
            $sDiff .= '&module='.$this->Request->get('module');
        }

        $sRecover = 'node='.$Node->get('id').'&cmd='.CMD_RECOVHIST;
        if ($this->Request->has('module')) {
            $sRecover .= '&module='.$this->Request->get('module');
        }

        // ----------------------------------------------------------------

        // Set properties of current node

        // Get user
        $User = $UserDAO->getUserByUid($Node->get('modifiedByUid'));
        $sUser = $User ? $User->get('login') : $Node->get('modifiedByUid');

        // Get host or IP. Shorten (mask) output for users with
        // unsufficient access rights. FIX: this goes for guests too!
        $sRemoteHost = $this->getMaskedHostStr(
                          $Node->get('remoteHost'),
                          $Node->get('remoteAddr'),
                          $Node->isWritable()
                       );

        $this->Template->set('TPL_ITEM_DATE',
                        $this->Context->makeDateTimeRelative(
                          $Node->get('modified')
                        )
                      );
        $this->Template->set('TPL_ITEM_REVISION', $Node->get('revision'));
        $this->Template->set('TPL_ITEM_USER', $sUser);
        $this->Template->set('TPL_ITEM_HOST', $sRemoteHost);
        if ($bDeleted) {
            $this->Template->set(
                'TPL_ITEM_LINK_RECOVER',
                sprintf(
                    '&nbsp;<a href="%s">%s</a>&nbsp;',
                    $this->Response->getControllerHref(
                        $sRecover.'&histnode='.$Node->get('histId')
                    ),
                    __('I18N_RECOVER')
                )
            );
        } else {
            $this->Template->set('TPL_ITEM_LINK_RECOVER', '&nbsp;');
        }

        // ----------------------------------------------------------------

        // Set properties of historical nodes

        $aTplItem = array();

        // Get historical nodes
        $HistNode = $DocDAO->getHistNodesForId($Node->get('id'));
        $It = $HistNode->getItems()->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();

            // Ignore history node that is equal to current node
            if ($bDeleted && $Obj->get('histId') == $Node->get('histId')) {
                continue;
            }

            // Get user
            $User = $UserDAO->getUserByUid($Obj->get('modifiedByUid'));
            $sUser = $User ? $User->get('login') : $Obj->get('userId');

            // Get host or IP. Shorten (mask) output for users with
            // unsufficient access rights. FIX: this goes for guests too!
            $sRemoteHost = $this->getMaskedHostStr(
                              $Obj->get('remoteHost'),
                              $Obj->get('remoteAddr'),
                              $Node->isWritable()
                           );

            $aItem['DATE']     = $this->Context->makeDateTimeRelative(
                                    $Obj->get('modified')
                                  );
            $aItem['REVISION'] = $Obj->get('revision');
            $aItem['USER']     = $sUser;
            $aItem['HOST']     = $sRemoteHost;

            $aItem['HREF_COMPARE'] = $this->Response->getControllerHref(
                $sCompare.'&histnode='.$Obj->get('histId')
            );

            $aItem['HREF_DIFF'] = $this->Response->getControllerHref(
                $sDiff.'&histnode='.$Obj->get('histId')
            );

            $aItem['HREF_RECOVER'] = $this->Response->getControllerHref(
                $sRecover.'&histnode='.$Obj->get('histId')
            );

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Whether to provide the "recover" button
        $this->Template->set('TPL_ITEM_HAS_RECOVER', $Node->isWritable());

        // ----------------------------------------------------------------

        // "OK/Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.history.display.tpl');
    }

    /**
     * Get masked host str
     *
     * @access  private
     * @param   string
     * @param   string
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    private function getMaskedHostStr($sHost, $sIP, $bWritable) {

        if (!empty($sHost)) {

            // Return full host string?
            if ($bWritable) { return $sHost; }

            // Any dots in host name? Mask it.
            if (strpos($sHost, '.')) {
                return '<tt>*</tt>' . substr($sHost, strpos($sHost, '.'));
            }

            return $sHost;
        }

        // ---

        if (!empty($sIP)) {

            // Return full IP?
            if ($bWritable) { return $sIP; }

            // Mask IP
            return substr($sIP, 0, strrpos($sIP, '.') + 1) . '<tt>*</tt>';
        }

        return 'n/a';
    }

} // of plugin component

/*
    Ecce gratum et optatum ver reducit gaudia,
    purpuratum floret pratum, sol serenat omnia.
    Iamiam cedant tristia! Iamiam cedant tristia!
    Estas redit, nunc recedit hyemis sevitia.
*/

?>
