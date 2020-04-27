<?php

/**
 *
 * $Id: class.PrivateFrontUserChangeDialog.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontUserChangeDialog
 * #purpose:   Change user status (login/logout)
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
 * coWiki - Change user status (login/logout)
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
class PrivateFrontUserChangeDialog extends AbstractPlugin {

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // Get AuthManager
        $AuthManager = $this->Context->getAuthManager();

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Cancel?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // A back link reference given?
                if ($this->Request->has('ref')) {
                    $this->Response->redirectTo($this->Request->get('ref'));
                }

                // Go back to the normal node display
                $this->Response->redirectToController(
                    'node='.$Node->get('id')
                );
            }

            // Register?
            if ($this->Request->has('button_register')) {

                // Go to registration controller
                $this->Response->redirectTo('/register.php');
            }

            // Logout?
            if ($this->Request->has('button_logout')) {

                $this->Context->unsetSessionVar('loginData', 'core');

                // A back link reference given?
                if ($this->Request->has('ref')) {
                    $this->Response->redirectTo($this->Request->get('ref'));
                }

                // Go back to the normal node display
                $this->Response->redirectToController(
                    'node='.$Node->get('id')
                );
            }

            // Login?
            if ($this->Request->has('button_login')) {
                if ($this->Request->has('login')
                    && $this->Request->has('passwd')) {

                    $sLogin  = $this->Request->get('login');
                    $sPasswd = $this->Request->get('passwd');

                    // Validate
                    if ($AuthManager->validate($sLogin, $sPasswd)) {

                        $UserDAO = $this->Context->getUserDAO();
                        $User = $UserDAO->getUserByLogin($sLogin);

                        if (is_object($User)) {
                            $aTmp['userId']   = $User->get('userId');
                            $aTmp['loggedIn'] = true;
                        
                            // Save changed user in session
                            $this->Context->setSessionVar(
                                'loginData', $aTmp, 'core'
                            );
                        }

                        // A back link reference given?
                        if ($this->Request->has('ref')) {
                            $this->Response->redirectTo(
                                $this->Request->get('ref')
                            );
                        }

                        // Go back to the normal node display
                        $this->Response->redirectToController(
                            'node='.$Node->get('id')
                        );
                    }

                    // Spit error if no success
                    $this->Context->addError(115);
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
        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ----------------------------------------------------------------

        // Form fields
        $sStr =  '<input type="text" name="login" size="12"';
        $sStr .= ' maxlength="32" value="">';
        $this->Template->set('TPL_ITEM_LOGIN', $sStr);

        $sStr =  '<input type="password" name="passwd" size="12"';
        $sStr .= ' maxlength="32" value="">';
        $this->Template->set('TPL_ITEM_PASSWORD', $sStr);

        // ----------------------------------------------------------------

        // "Login" button
        $sStr =  '<input type="submit" name="button_login"';
        $sStr .= ' class="submit" value="'.__('I18N_LOGIN').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Logout" button?
        $sStr = '';
        if ($CurrUser->get('isValid')) {
            $sStr =  '<input type="submit" name="button_logout"';
            $sStr .= ' class="submit" value="'.__('I18N_LOGOUT').'">';
        } else {
#            $sStr =  '<input type="submit" name="button_register"';
#            $sStr .= ' class="submit"';
#            $sStr .= ' value="'.__('I18N_REGISTER').' / '.__('I18N_HELP').'"';
#            $sStr .= '>';
        }

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel"';
        $sStr .= ' class="submit" value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON3', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.user.change.tpl');
    }

} // of plugin component

?>
