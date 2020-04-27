<?php

/**
 *
 * $Id: class.CustomReferrer.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomReferrer
 * #purpose:   Display recent referrers
 * #param:     title   the title of the output box (default "Referrers")
 * #param:     limit   number of referrers to display (10 is default,
 *                     20 is maximum)
 * #param:     cutoff  cut long referrer names to max. n characters (20
 *                     is default). The beginning of a name will be cutted.
 * #param:     style   CSS style of the output <table> or <div> container
 *                     (default: template dependent)
 * #caching:   yes, internal cache
 * #comment:   Content is not cached as the referrer is saved every time
 *             in the core.base.php file.
 * #version:   1.0
 * #date:      14. April 2003
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
 * coWiki - Display recent referrers
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
class CustomReferrer extends AbstractPlugin {

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

        // Check if this plugin should be executed
        if (!$this->Registry->get('PLUGIN_REFERRER_ENABLE')) {
            return true;  // leave plugin
        }

        // Save referrer
        $Refer = $this->Context->getReferrerDAO();

        // Check if referrer vector is empty, leave if so
        $Refs = $Refer->getReferrers();
        if ($Refs->isEmpty()) {
            return true;
        }

        // ----------------------------------------------------------------

        // Set plugin parameters, if passed by a plugin call, or set
        // defaults
        $sTitle = $this->Context->getPluginParam('title')
                    ? $this->Context->getPluginParam('title')
                    : 'Referrers';
        $sStyle = $this->Context->getPluginParam('style')
                    ? $this->Context->getPluginParam('style')
                    : '';
        $nLimit = $this->Context->getPluginParam('limit')
                    ? abs($this->Context->getPluginParam('limit'))
                    : 10;
        $nCutOff = $this->Context->getPluginParam('cutoff')
                    ? abs($this->Context->getPluginParam('cutoff'))
                    : 20;

        // ----------------------------------------------------------------

        // Set plugin parameters for template
        $this->Template->set('TPL_TABLE_STYLE', $sStyle);
        $this->Template->set('TPL_TITLE', $sTitle);

        // ----------------------------------------------------------------

        $aTplItem = array();

        $nCount = 0;
        $It = $Refs->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();

            $aItem['NAME'] = cutOffAtStart($Obj->get('host'), $nCutOff);
            $aItem['HREF'] = $Obj->get('url');

            // Append item to template items
            $aTplItem[] = $aItem;

            $nCount++;
            if ($nCount >= $nLimit) {
                break; // the loop
            }
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.referrer.tpl');
    }

} // of plugin component

?>
