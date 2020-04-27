<?php

/**
 *
 * $Id: class.PrivateXmlPrettyPrinter.php 27 2011-01-09 12:37:59Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateXmlPrettyPrinter
 * #purpose:
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
 * @subpackage  Private
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 27 $
 *
 */

/**
 * coWiki - Private XML pretty printer
 *
 * @package     plugin
 * @subpackage  Private
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 * @todo        [D11N]  Add plugin purpose
 */
class PrivateXmlPrettyPrinter extends AbstractPlugin {

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

        // FIX!
        if (!$Node) {
            echo 'No document found.';
            return true;
        }

        // ----------------------------------------------------------------

        // Check validity and user access
        if ($Node->get('id') == 0 || !$Node->isReadable()) {

            // Robots are not permitted to spider this area or to follow
            // links
            $this->Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');

            if ($Node->get('id') == 0) {
                $this->Context->addError(404);    // Not found
            } else {
                $this->Context->addError(403);    // Forbidden
            }

            $this->Context->resume();             // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Init, check for PHP extension
        if (!XmlPrettyHtmlPrinter::getInstance()->init()) {
            $this->Context->addError(540, 'XML parser support');
            $this->Context->resume();   // leave plugin
        }

        // Prepare XML, add root element

        $sStr =  '<document';
        $sStr .=    ' title="'.escape($Node->get('name')).'"';
        $sStr .=    ' docid="'.$Node->get('id').'"';
        $sStr .=    ' parentid="'.$Node->get('parentId').'"';
        $sStr .=    ' webid="'.$Node->get('treeId').'"';
        $sStr .=  '>';
        $sStr .=    $Node->get('content');
        $sStr .= '</document>';

        // Print pretty XML
        $sStr = XmlPrettyHtmlPrinter::getInstance()->getPretty($sStr, 60);

        echo  '<pre class="code">';
        echo    '&lt;?xml version="1.0" encoding="UTF-8" ?&gt;';
        echo    "\n\n";
        echo    $sStr;
        echo  '</pre>';
    }

} // of plugin component

?>
