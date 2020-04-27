<?php

/**
 *
 * $Id: class.CustomWikiWordReference.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomWikiWordReference
 * #purpose:   Provide a WikiWord reference to a document
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      22. January 2003
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
 * coWiki - Provide a wikiword reference to a document
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
class CustomWikiWordReference extends AbstractPlugin {

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

        // Get document data access object
        $DocDAO = $this->Context->getDocumentDAO();

        // ----------------------------------------------------------------

        // Get base URI of this HTTP document
        $sUri = $this->Request->getHostUri() . $this->Request->getBasePath();

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();
        
        // Check validity and user access
        if (!$Node->isReadable()) {
            return true;  // leave plugin
        }

        $sWikiName = $Node->get('wikiName');

        // Get current web name
        $Web = $DocDAO->getWebById($Node->get('treeId'));
        if (!is_object($Web)) {
            return true;
        }
        $sWeb = $Web->get('wikiName') . '/';

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM',  $sUri . $sWeb . $sWikiName);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.wikiword.reference.tpl');
    }

} // of plugin component

?>
