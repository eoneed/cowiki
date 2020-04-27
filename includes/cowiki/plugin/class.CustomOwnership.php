<?php

/**
 *
 * $Id: class.CustomOwnership.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomOwnership
 * #purpose:   Display the ownership of a directory or document node
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
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Display the ownership of a directory or document node
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class CustomOwnership extends AbstractPlugin {

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        if (!is_object($Node) || $Node->get('id') == 0) {
            return true;  // leave plugin
        }

        // Check validity and user access
        if (!$Node->isReadable()) {
            return true;  // leave plugin
        }

        // Get user DAO
        $UserDAO = $this->Context->getUserDAO();

        // Get user & group objects
        $User = $UserDAO->getUserByUid($Node->get('userId'));
        $Group = $UserDAO->getGroupByGid($Node->get('groupId'));

        // Get user login and group name
        $sUser  = $User  ? $User->get('login') : $Node->get('userId');
        $sGroup = $Group ? $Group->get('name') : $Node->get('groupId');

        $this->Template->set('TPL_ITEM_MODE', $Node->getAccessModeAsString());
        $this->Template->set('TPL_ITEM_USER',  $sUser);
        $this->Template->set('TPL_ITEM_GROUP', $sGroup);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.ownership.tpl');
    }

} // of plugin component

?>
