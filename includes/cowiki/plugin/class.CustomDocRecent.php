<?php

/**
 *
 * $Id: class.CustomDocRecent.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomDocRecent
 * #purpose:   Display recently changed documents
 * #param:     title   the title of the output box (default none)
 * #param:     limit   number of recently changed documents  you wish to
 *                     display (15 is default)
 * #param:     cutoff  cut long document names after n characters (30
 *                     is default)
 * #param:     style   CSS style of the output <table> or <div> container
 *                     (default: template dependent)
 * #caching:   yes, internal cache
 * #comment:   none
 * #version:   1.0
 * #date:      04. December 2002
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
 * coWiki - Display recently changed documents
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
class CustomDocRecent extends AbstractPlugin
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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

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
                    : 15;
        $nCutOff = $this->Context->getPluginParam('cutoff')
                    ? abs($this->Context->getPluginParam('cutoff'))
                    : 30;

        // ----------------------------------------------------------------

        // Set plugin parameters for template
        $this->Template->set('TPL_TABLE_STYLE', $sStyle);
        $this->Template->set('TPL_TITLE', $sTitle);

        // ----------------------------------------------------------------

        $RecentNode = $this->Context->getDocumentDAO()
                        ->getRecentlyChangedNodes($nLimit);

        $aTplItem = array();

        // Iterate through children
        $It = $RecentNode->getItems()->iterator();

        while ($Obj = $It->next()) {

            $aItem = array();

            $aItem['TIME']   = '|' . (int)$Obj->get('recTan') . '|';
            $aItem['NAME']   = escape(cutOff($Obj->get('name'), $nCutOff));
            $aItem['HREF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                               );
            $aItem['DIFF']   = $this->Response->getControllerHref(
                                  'node='.$Obj->get('id')
                                  .'&cmd='.CMD_DIFFHIST
                                  .'&refnode='.$Node->get('id')
                               );
            // Append item to template items
            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.doc.recent.tpl');

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
