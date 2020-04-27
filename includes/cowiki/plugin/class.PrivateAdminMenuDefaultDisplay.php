<?php

/**
 *
 * $Id: class.PrivateAdminMenuDefaultDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateAdminMenuDefaultDisplay
 * #purpose:   Display main menu for the administration panel
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
 * coWiki - Display main menu for the administration panel
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
class PrivateAdminMenuDefaultDisplay extends AbstractPlugin {

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
     * @todo    [FIX]   better check
     */
    public function perform() {

        $UriInfo = new UriInfo($this->Registry->get('.AUTH_RESOURCE'));

        // Check if we are able to edit users defined by the AuthDAO
        $bCanEditMySqlUser = false;
        if (in_array($UriInfo->get('scheme'), array('mysql'))) {

            // FIX: better check
            $bCanEditMySqlUser = true;
        }

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // EXIT?
            if ($this->Request->has('button_close_x')) {
                $this->Registry->set('COWIKI_CONTROLLER_NAME', '');

                // {{{ DEBUG }}}
                Logger::info('Redirecting to controller (EXIT)');

                // Exit now
                $this->Response->redirectToController();
            }
        }

        // ----------------------------------------------------------------

        $sSelf = $this->Registry->get('COWIKI_CONTROLLER_NAME');

        $aMenu = array();

#        $aMenu[]= array(
#            'label' => __('I18N_ADMIN_MENU_LABEL_SETUP'),
#            'link'  => $sSelf.'?module=setup'
#        );

        $aMenu[]= array(
            'label' => __('I18N_ADMIN_MENU_LABEL_STRUCT'),
            'link'  => $sSelf.'?module=struct'
        );

        // Conditional button
        if ($bCanEditMySqlUser) {
            $aMenu[]= array(
                'label' => __('I18N_ADMIN_MENU_LABEL_USER'),
                'link'  => $sSelf.'?module=user'
            );

            $aMenu[]= array(
                'label' => __('I18N_ADMIN_MENU_LABEL_GROUP'),
                'link'  => $sSelf.'?module=group'
            );
        }

        // Conditional button
        if ($this->Registry->get('RUNTIME_CACHE_ENABLE')) {
            $aMenu[]= array(
                'label' => __('I18N_ADMIN_MENU_LABEL_CACHE'),
                'link'  => $sSelf.'?module=cache'
            );
        }

#        $aMenu[]= array(
#            'label' => __('I18N_ADMIN_MENU_LABEL_UPDATE'),
#            'link'  => $sSelf.'?module=update'
#        );

        $aMenu[]= array(
            'label' => __('I18N_ADMIN_MENU_LABEL_TRASH'),
            'link'  => $sSelf.'?module=trash'
        );

        $aMenu[]= array(
            'label' => __('I18N_ADMIN_MENU_LABEL_EXIT'),
            'link'  => $this->Request->getBasePath()
        );

        // ----------------------------------------------------------------

        $aItem = array();

        for ($i=0, $n=ceil(sizeof($aMenu)/2); $i<$n; $i++) {
            $aItem['LABEL1'] = $aMenu[$i]['label'];
            $aItem['LINK1']  = $aMenu[$i]['link'];

            $aItem['LABEL2'] = '';
            $aItem['LINK2']  = '';

            // Check for odd length of $aMenu
            if (isset($aMenu[$i+$n]['label'])) {
                $aItem['LABEL2'] = $aMenu[$i+$n]['label'];
                $aItem['LINK2']  = $aMenu[$i+$n]['link'];
            }

            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

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

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.admin.menu.default.tpl');
    }

} // of plugin component

/*
    There is a little place in a little room
    where a little chap hides away amidst the gloom.
    Tucks his little legs underneath a well-worn chair
    plucks a piece of paper and attacks at his despair.

    A stubby lead pencil scratches through the fears
    of every little cruelness that reduces us to tears.
    Sharp is the lead but will it penetrate
    all the nooks and crannies that this world creates?

    There is so little time for us to stop and look
    as he places the cover upon his little book.
    There will come a day when this little man will die
    and they'll put him in a tiny hole underneath the sky
    His little lead pencil, book and chair
    will be placed inside a plastic bag and taken who knows where ...
*/

?>
