<?php

/**
 *
 * $Id: class.CustomConstantInfo.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomConstantInfo
 * #purpose:   Display basic constants that may be used in templates and
 *             documents
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      16. February 2003
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
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
 * coWiki - Custom constant info class
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
class CustomConstantInfo extends AbstractPlugin {

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

        $aShow = array();
        $aProp = $this->Registry->getDynamicProperties();

        // Pessimistic filter
        $sFilter = '^(\.|COLOR|PATH|TPL|COWIKI|EDIT|PAGE|ABOUT|
                      META|DOCUMENT|MENU|BUTTON|PLUGIN|RUNTIME|MAIL
                     )';

        // Filter properties, do not show all possibilites to a normal user
        foreach($aProp as $k => $v) {
            if (!preg_match('#'.$sFilter.'#xi', $k)) {
                $aShow[$k] = $v;
            }
        }

        // Sort properties by key for better reading
        ksort($aShow);

        // Output properties. This echos HTML directly as there is no need
        // to use a template for this task IMO.
        echo '<table cellpadding="0" cellspacing="3" border="0"';
        echo  ' class="rappsboxsimple">';

        foreach($aShow as $k => $v) {
            echo  '<tr valign="top">';
            echo    '<td>';
            echo      '<tt>%'.$k.'%</tt>';
            echo    '</td>';

            echo    '<td>&nbsp;&nbsp;</td>';

            echo    '<td>';
            echo      escape($v);
            echo    '</td>';
            echo  '</tr>';
        }

        echo '</table>';
    }

} // of plugin component

/*
   Shiny, shiny, shiny boots of leather
   Whiplash girlchild in the dark
   Comes in bells, your servant, don't forsake him
   Strike, dear mistress, and cure his heart

   Downy sins of streetlight fancies
   Chase the costumes she shall wear
   Ermine furs adorn the imperious
   Severin, Severin awaits you there

   I am tired, I am weary
   I could sleep for a thousand years
   A thousand dreams that would awake me
   Different colors made of tears

   Kiss the boot of shiny, shiny leather
   Shiny leather in the dark
   Tongue of thongs, the belt that does await you
   Strike, dear mistress, and cure his heart

   Severin, Severin, speak so slightly
   Severin, down on your bended knee
   Taste the whip, in love not given lightly
   Taste the whip, now plead for me

   I am tired, I am weary
   I could sleep for a thousand years
   A thousand dreams that would awake me
   Different colors made of tears

   Shiny, shiny, shiny boots of leather
   Whiplash girlchild in the dark
   Severin, your servant comes in bells, please don't forsake him
   Strike, dear mistress, and cure his heart
*/

?>
