<?php

/**
 *
 * $Id: class.CustomComRecent.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomComRecent
 * #purpose:   Display recently created document comments
 * #param:     title   the title of the output box (default none)
 * #param:     limit   number of recently recently created document
 *                     comments you wish to display (10 is default)
 * #param:     cutoff  cut long subjects after n characters (30 is default)
 * #param:     style   CSS style of the output <table> or <div> container
 *                     (default: template dependent)
 * #caching:   yes, internal cache
 * #comment:   none
 * #version:   1.0
 * #date:      01. June 2003
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
 * coWiki - Display recently created document comments
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
class CustomComRecent extends AbstractPlugin
                      implements CustomObserver {

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
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Make this plugin observe the document DAO. Observed DAO will
        // signal if this plugin should clean up its cache. See "update()"
        $this->Context->getDocumentDAO()->addObserver($this);

        // ----------------------------------------------------------------

        // Build ident depending on plugin parameters
        $sIdent = $this->Context->getPluginParamIdent();

        // If cached result exists, put it out and leave the plugin
        if ($sStr = $this->Context->getFromCache($this, $sIdent)) {
            echo $this->generateTime($sStr);
            return true; // leave plugin
        }

        // ----------------------------------------------------------------

        // Set plugin parameters, if passed by a plugin call, or set
        // defaults
        $sTitle = $this->Context->getPluginParam('title')
                    ? $this->Context->getPluginParam('title')
                    : '';
        $sStyle = $this->Context->getPluginParam('style')
                    ? $this->Context->getPluginParam('style')
                    : '';
        $nLimit = $this->Context->getPluginParam('limit')
                    ? abs($this->Context->getPluginParam('limit'))
                    : 10;
        $nCutOff = $this->Context->getPluginParam('cutoff')
                    ? abs($this->Context->getPluginParam('cutoff'))
                    : 30;

        // ----------------------------------------------------------------

        // Set plugin parameters for template
        $this->Template->set('TPL_TABLE_STYLE', $sStyle);
        $this->Template->set('TPL_TITLE', $sTitle);

        // ----------------------------------------------------------------

        $RecentComs = $this->Context->getCommentDAO()
                          ->getRecentComments($nLimit);

        $aTplItem = array();

        // Iterate through result vector
        $It = $RecentComs->iterator();

        while ($Obj = $It->next()) {

            $aItem = array();

            $aItem['TIME'] = '|' . (int)$Obj->get('created') . '|';
            $aItem['NAME'] = escape(cutOff($Obj->get('subject'), $nCutOff));
            $aItem['HREF'] = $this->Response->getCommentHref(
                                $Obj->get('id'),
                                'node='.$Obj->get('nodeId')
                             );

            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.com.recent.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr, $sIdent);

        // Output result
        echo $this->generateTime($sStr);
    }

    /**
     * Although the output of this plugin is cached, the time has to be
     * displayed relative to the current time, lets trick a bit.
     *
     * @access  private
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    private function generateTime($sStr) {
        $sStr = preg_replace_callback(
                  '#\|([0-9]{10,})\|#U',
                  array(&$this, 'generateTimeCallback'),
                  $sStr
                );
        return $sStr;
    }

    /**
     * Generate time callback
     *
     * @access  private
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    private function generateTimeCallback($aMatches) {
        return $this->Context->makeDateTimeRelative($aMatches[1]);
    }

    /**
     * Implement observer "update()" method to clean the cache if necessary
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function update() {
        $this->Context->removeFromCache($this);
    }

} // of plugin component

?>
