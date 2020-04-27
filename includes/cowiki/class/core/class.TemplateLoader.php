<?php

/**
 *
 * $Id: class.TemplateLoader.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Template loader class
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class TemplateLoader extends Object {

    /**
     * Load
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function __construct() {}

    /**
     * Load
     *
     * @access  public
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function load($sTplName) {

        $Context  = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();

        // Get template file path
        $sFileName = $Context->getEnvironment()->get('DOCUMENT_ROOT')
                     .$Registry->get('PATH_TEMPLATE_ACTIVE')
                     .$sTplName;

        // Get fallback template file path, if active template lacks a file
        $sDefName =  $Context->getEnvironment()->get('DOCUMENT_ROOT')
                     .$Registry->get('PATH_TEMPLATE_DEFAULT')
                     .$sTplName;

        // Predefine for error case
        $sFile = '';

        // Get template file
        try {
            try {
                $Stream = new FileInputStream($sFileName);
                $sFile = $Stream->readAll();
                $Stream->close();

                if ((int)$Registry->get('SOFTWARE_DEBUG_TEMPLATES') === 1) {
                    $sFile = '<!-- start "' .
                        $Registry->get('PATH_TEMPLATE_ACTIVE') .
                        $sTplName . '" -->' . $sFile;
                    $sFile .= '<!-- end "' .
                        $Registry->get('PATH_TEMPLATE_ACTIVE') .
                        $sTplName . '" -->';
                }

            } catch (Exception $e) {

                try {
                    $Stream = new FileInputStream($sDefName);
                    $sFile = $Stream->readAll();
                    $Stream->close();

                    if ((int)$Registry->get('SOFTWARE_DEBUG_TEMPLATES') === 1) {
                        $sFile = '<!-- start "' .
                            $Registry->get('PATH_TEMPLATE_DEFAULT') .
                            $sTplName . '" -->' . $sFile;
                        $sFile .= '<!-- end "' .
                            $Registry->get('PATH_TEMPLATE_DEFAULT') .
                            $sTplName . '" -->';
                    }

                } catch (Exception $e) {
                    throw $e;
                }
            }

        } catch (Exception $e) {

            $Context->addError(320, $sFileName);

            // Terminate if error template was not found
            if ($sTplName == 'error.tpl') {
                $Context->terminate();
            }

            throw $e;
        }

        return $sFile;
    }

} // of class

/*
    We can have some more
    Nature is a whore
    Bruises on the fruit
    Tender age in bloom
*/

?>
