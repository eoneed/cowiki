<?php

/**
 *
 * $Id: class.PrivateFrontUserControlDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontUserControlDisplay
 * #purpose:   Provide user controls in the front panel
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
 * coWiki - Provide user controls in the front panel
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
class PrivateFrontUserControlDisplay extends AbstractPlugin {

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
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Check if we are already using this controls
        if ($this->Request->get('cmd') == CMD_CHUSR
            || $this->Request->get('cmd') == CMD_PREFUSR
            || $this->Request->get('cmd') == CMD_DETAILUSR) {
            return true;  // leave
        }

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // ----------------------------------------------------------------

        $sLogin = $CurrUser->get('login');

        if ($CurrUser->isRoot()) {

            $sLogin = '<span class="wheel">'
                        .$sLogin
                      .'</span>';
        }

        $this->Template->set('TPL_ITEM_USER', $sLogin);

        // --

        $sItem = $this->Response->getControllerHref(
                    'node='.$Node->get('id')
                    .'&cmd='.CMD_CHUSR
                    .'&ref='.urlencode($this->Env->get('REQUEST_URI'))
                 );
        $this->Template->set('TPL_ITEM_CHANGE_HREF', $sItem);

        // --

        $sItem = $this->Response->getControllerHref(
                    'node='.$Node->get('id')
                    .'&cmd='.CMD_PREFUSR
                    .'&ref='.urlencode($this->Env->get('REQUEST_URI'))
                 );
        $this->Template->set('TPL_ITEM_PREF_HREF', $sItem);

        // --
    
        $sItem = $this->Response->getControllerHref(
                    'node='.$Node->get('id')
                    .'&cmd='.CMD_DETAILUSR
                    .'&ref='.urlencode($this->Env->get('REQUEST_URI'))
                 );
        $this->Template->set('TPL_ITEM_DETAILS_HREF',$sItem);

        // ---

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.user.control.tpl');
    }

} // of plugin component

?>
