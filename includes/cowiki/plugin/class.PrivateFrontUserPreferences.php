<?php

/**
 *
 * $Id: class.PrivateFrontUserPreferences.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontUserPreferences
 * #purpose:   Manage user preferences
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      26. March 2003
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
 * coWiki - Manage user preferences
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
class PrivateFrontUserPreferences extends AbstractPlugin {

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

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // Cancel?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {
          
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

            // Reset?
            if ($this->Request->has('button_reset')) {
                $this->Context->unsetCookieVar('preferences', '');

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

            // Save?
            if ($this->Request->has('button_save')) {

                $aPayload = array();
                $aPayload['TEMPLATE']    = $this->Request->get('template');
                $aPayload['CATALOG']     = $this->Request->get('catalog');
                $aPayload['FONT_FAMILY'] = $this->Request->get('font_family');
                $aPayload['FONT_ALIGN']  = $this->Request->get('font_align');
                $aPayload['FONT_SIZE']   = $this->Request->get('font_size');

                $this->Context->setCookieVar(
                    'preferences',
                    serialize($aPayload)
                );

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

        // Template names
        $aTplItem = array();

        $sDir = $this->Context->getEnvironment()->get('DOCUMENT_ROOT')
                .$this->Registry->get('PATH_TEMPLATE');

        $aActTpl = $this->Registry->get('RUNTIME_TEMPLATE_ACTIVE');

        // Read all template directories
        $rDir = @opendir($sDir);

        while ($sDirName = @readdir($rDir)) {
            if ($sDirName == '.'
                || $sDirName == '..'
                || strtolower($sDirName) == 'cvs') {
                continue;
            }

            $aItem = array();

            if ($sDirName == $aActTpl) {
                $aItem['SELECTED'] = ' selected="true" ';
            }

            $sInfoFile = $sDir . $sDirName . '/tpl.info';

            // Fetch data from .info file
            if (is_readable($sInfoFile)) {
                $aTpl = @parse_ini_file($sInfoFile);

                $aItem['VALUE']  = escape($sDirName);
                $aItem['OPTION'] = escape($aTpl['NAME']);

                if (isset($aTpl['DESCRIPTION'])
                    && $aTpl['DESCRIPTION'] != '') {

                    $sDesc = escape(cutOff($aTpl['DESCRIPTION'], 40));
                    $aItem['OPTION'] = escape($aTpl['NAME']);
                    $aItem['OPTION'] .= ' ('.$sDesc .')';
                }

            } else {

                $aItem['VALUE']  = escape($sDirName);
                $aItem['OPTION'] = escape($sDirName);
            }

            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_TEMPLATE', $aTplItem);

        // ----------------------------------------------------------------

        // Language select
        $aTplItem = array();

        $sDir = realpath(getDirName(__FILE__) . '../locale');
        $sActCat = strtolower(
                      $this->Registry->get('RUNTIME_LANGUAGE_LOCALE')
                   );

        // Read all catalog (locale) files
        $rDir = @opendir($sDir);

        while ($sFileName = @readdir($rDir)) {
            if ($sFileName == '.'
                || $sFileName == '..'
                || strtolower($sFileName) == 'error.cat'
                || substr(strtolower($sFileName), -4) != '.cat') {
                continue;
            }

            // Remove suffix (.cat)
            $sFileName = substr($sFileName, 0, -4);

            $aItem = array();

            if (strtolower($sFileName) == $sActCat) {
                $aItem['SELECTED'] = ' selected="true" ';
            }

            $aItem['VALUE']  = escape($sFileName);
            $aItem['OPTION'] = $aItem['VALUE'];

            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_CATALOG', $aTplItem);

        // ----------------------------------------------------------------

        // Font align select
        $aTplItem = array();
        $sAlign = $this->Registry->get('FONT_ALIGN');

        $aItem = array();
        $aItem['VALUE']  = '';
        $aItem['OPTION'] = __('I18N_FONT_ALIGN_NORMAL');
        if (!$sAlign) {
            $aItem['SELECTED'] = ' selected="true" ';
        }
        $aTplItem[] = $aItem;

        // ---

        $aItem = array();
        $aItem['VALUE']  = 'justify';
        $aItem['OPTION'] = __('I18N_FONT_ALIGN_JUSTIFY');
        if ($sAlign == $aItem['VALUE']) {
            $aItem['SELECTED'] = ' selected="true" ';
        }
        $aTplItem[] = $aItem;

        $this->Template->set('TPL_FONT_ALIGN', $aTplItem);

        // ----------------------------------------------------------------

        // Font family
        $sStr =  '<input type="text" name="font_family" size="20"';
        $sStr .= ' maxlength="32"';
        $sStr .= ' value="'.$this->Registry->get('FONT_FAMILY').'">';
        $this->Template->set('TPL_ITEM_FONT_FAMILY', $sStr);

        // Font size
        $sStr =  '<input type="text" name="font_size" size="2" maxlength="2"';
        $sStr .= ' value="'.(int)$this->Registry->get('FONT_SIZE').'">';
        $this->Template->set('TPL_ITEM_FONT_SIZE', $sStr);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save"';
        $sStr .= ' class="submit" value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Reset" button
        $sStr =  '<input type="submit" name="button_reset"';
        $sStr .= ' class="submit" value="'.__('I18N_RESET').'">';

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel"';
        $sStr .= ' class="submit" value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON3', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.user.preferences.tpl');

    }

} // of plugin component

/*
    In the beginning, God created the heaven and the earth
    And the earth was without form and void

    And darkness was on the face of the deep
    And God said let there be light and there was light

    And God saw the light that it was good
    And God divided the light from the darkness

    And God called the light "day" and the darkness he called "night"
    And God saw everything that he had made and behold that it was good

    And God created man and man created machine
    And machine, machine created music,
    And machine saw everything it had made and said "behold ..."

    ...

    And on the seventh day, machine pressed "stop" ...
*/

?>
