<?php

/**
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontUserDetails
 * #purpose:   Allow user to edit own details (password and email so far)
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      11 October 2003
 * #author:    Rich Churcher <rmch@xtra.co.nz>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 */

/**
 * coWiki - Allow user to edit own details
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Rich Churcher <rich@eloquentgeek.com>
 * @since
 *
 * @todo        [D11N]  Complete documentation
 */

// Much of this is ripped unashamedly from PrivateAdminUserEditor.
// As such, the lion's share of the credit should go to Daniel Gorski.
class PrivateFrontUserDetails extends AbstractPlugin {

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
     * @author  Rich Churcher <rich@eloquentgeek.com>
     * @since
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Get user data access object
        $UserDAO = $this->Context->getUserDAO();

        $CurrUser = $this->Context->getCurrentUser();
        $nUid = (int)$CurrUser->get('userId');

        // Check for reserved user ids, leave if necessary
        if ($nUid == 0) {
            // FIX: Error
            $this->Context->addError(460);
            $this->Context->resume();
            return true;
        }

        if ($nUid == 65535) {
            // FIX: Error
            $this->Context->addError(459);
            $this->Context->resume();
            return true;
        }

        // We are editing an existing user
        $Edit = $UserDAO->getUserByUid($nUid);

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Populate the edit-node with posted data
                $Edit->set('email',   $this->Request->get('email'));

                // Set password only if it has been changed, otherwise
                // set it to "null". A "null" password must be handled by
                // the DAO as if no changes on password are needed.
                // If we have a password string, it has to be passed plain
                // (not crypted), but without any magic quotes.
                // Plausibility/encryption have to be done by the DAO.
                if ($this->Request->get('password') == '') {
                    $Edit->set('password', null);
                } else {
                    $Edit->set('password', $this->Request->get('password'));
                }

                // SAVE
                if ($UserDAO->storeUser($Edit)) {

                    // Exit now
                    $this->Response->redirectToController('');
                }
            }

            // Main form: CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // Exit now
                $this->Response->redirectToController('');
            }

        }

        // Form action
        $this->Template->set(
            'TPL_FORM_ACTION',
            $this->Response->getControllerAction()
        );

        // Form control data
        $sStr =  '<input type="hidden" name="submit"'
                  .' value="'.$this->Context->getSubmitId().'">';
        $sStr .= '<input type="hidden" name="rec_id"'
                  .' value="'.$Edit->get('userId').'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ---

        // User password
        $sStr = '<input type="password" name="password" size="32"';
        $sStr .=  ' value="">';

        $this->Template->set('TPL_ITEM_PASSWORD', $sStr);

        // User email
        $sStr = '<input type="text" name="email" size="32"';
        $sStr .=  ' maxlength="64" value="'.escape($Edit->get('email')).'">';

        $this->Template->set('TPL_ITEM_EMAIL', $sStr);

        // ---

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.user.details.tpl');
    }
}
?>
