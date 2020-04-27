<?php

/**
 *
 * $Id: class.PrivateFrontSearchQuery.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontSearchQuery
 * #purpose:   Provide input field for searching in documents
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      18. August 2003
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
 * coWiki - Provide input field for searching in documents
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
class PrivateFrontSearchQuery extends AbstractPlugin {

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

        // Form action
        $this->Template->set(
            'TPL_FORM_ACTION',
            $this->Response->getControllerAction()
        );

        // Form control data
        $sStr = '<input type="hidden" name="submit"'
                .' value="'.$this->Context->getSubmitId().'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // Input field width
        $nWidth = ((int)$this->Registry->get('MENU_MIN_WIDTH')) - 22;
        $this->Template->set('TPL_INPUT_WIDTH', $nWidth);

        // Input field value
        $this->Template->set('TPL_INPUT_QUERY', $this->Request->get('q').'');

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.search.query.tpl');

    }

} // of plugin component

/*
    We forgive as we forget
    As the day is long
    As the day is long

    Rain from Heaven

    As the water flows over the bridge
    As we walk on the floodland
    As we walk on the water
    We forget
    We forget

    Rain from heaven
*/

?>
