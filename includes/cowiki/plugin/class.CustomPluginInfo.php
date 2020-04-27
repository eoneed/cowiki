<?php

/**
 *
 * $Id: class.CustomPluginInfo.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      CustomPluginInfo
 * #purpose:   Provide a simple summary of available plugins
 * #param:     plugin    name of a specified plugin
 * #caching:   yes, internal cache
 * #comment:   none
 * #version:   1.1
 * #date:      17. March 2003
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
 * coWiki - Provide a simple summary of available plugins
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
class CustomPluginInfo extends AbstractPlugin {

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

        // Build ident depending on plugin parameters
        $sIdent = $this->Context->getPluginParamIdent();

        // If cached result exists, put it out and leave the plugin
        if ($sStr = $this->Context->getFromCache($this, $sIdent, 3600)) {
            echo $sStr;
            return true; // leave plugin
        }

        // ----------------------------------------------------------------

        $sSinglePlugin = $this->Context->getPluginParam('plugin')
                       ? $this->Context->getPluginParam('plugin')
                       : '';
        $sSinglePlugin = strtolower($sSinglePlugin);

        $aFullArr = $this->Context->getPluginLoader()->getPluginPaths();
        $aArr = array();

        // Filter custom plugins
        $aKeys = array_keys($aFullArr);

        for ($i=0, $n=sizeof($aFullArr); $i<$n; $i++) {

            // We have two types of keys: "p"rivate and "c"ustom plugins
            if ($aKeys[$i]{0} == 'c') {
                $aArr[substr($aKeys[$i], 6)] = $aFullArr[$aKeys[$i]];
            }
        }

        // Sort
        ksort($aArr, SORT_STRING);

        $aTplItem = array();

        // Retrieve information
        $aKeys = array_keys($aArr);

        if ($sSinglePlugin && in_array($sSinglePlugin, $aKeys)) {
            $aItem = $this->getPluginInfo($aArr[$sSinglePlugin]);
            $aTplItem[] = $aItem;
        } else {
            for ($i=0, $n=sizeof($aArr); $i<$n; $i++) {
                $aItem = $this->getPluginInfo($aArr[$aKeys[$i]]);
                $aTplItem[] = $aItem;
            }
        }

        $this->Template->set('TPL_ITEM',  $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        if ($sSinglePlugin && in_array($sSinglePlugin, $aKeys)) {
            $sStr = $Tpl->parse('plugin.plugin.info.complete.tpl');
        } else {
            $sStr = $Tpl->parse('plugin.plugin.info.tpl');
        }

        // Cache result
        $this->Context->putToCache($this, $sStr);

        // Output result
        echo $sStr;
    }

    // === HELPER =========================================================

    /**
     * Extract information from the remark-head of a plugin
     *
     * @access  private
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    private function &getPluginInfo($sPath) {
        $sFile = @file_get_contents($sPath);

        // Default values for error case
        $aArr['NAME']    = 'n/a';
        $aArr['PURPOSE'] = 'n/a';
        $aArr['PARAM']   = '';
        $aArr['CACHING'] = 'n/a';
        $aArr['COMMENT'] = 'n/a';
        $aArr['VERSION'] = 'n/a';
        $aArr['DATE']    = 'n/a';
        $aArr['AUTHOR']  = 'n/a';

        preg_match(
            '#/\*\*[\r]?\n(.*)\*/#Us',
            $sFile,
            $aMatches
        );

        // Any info extracted?
        if (!isset($aMatches[1])) {
            return $aArr;
        }

        // Replace tabs (if any)
        $sStr = str_replace("\t", '    ', $aMatches[1]);
        $sStr = str_replace("\n *", "\n", $aMatches[1]);

        // ----------------------------------------------------------------

        // Get name
        preg_match(
            '#\#name:\s+(.+)#',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            // Cut off prefix
            if (substr(strtolower($aMatches[1]), 0, 6) == 'custom') {
                $aArr['NAME'] = substr($aMatches[1], 6);
            } else {
                $aArr['NAME'] = $aMatches[1];
            }
        }

        // ----------------------------------------------------------------

        // Get purpose
        preg_match(
            '#\#purpose:\s+(.+)\##Us',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $aArr['PURPOSE'] = htmlentities(trim($aMatches[1]));
        }

        // ----------------------------------------------------------------

        // Get param
        preg_match_all(
            '#\#param:\s+(.+)(?=\#)#Us',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {

            $aArr['PARAM'] .= '<table cellpadding="0" cellspacing="0"';
            $aArr['PARAM'] .= ' border="0">';

            for ($i=0, $n=sizeof($aMatches[1]); $i<$n; $i++) {
                $sStrParam = trim($aMatches[1][$i]);

                $j = strpos($sStrParam, ' ');

                // No param description
                if ($j == 0) {
                    $aArr['PARAM'] .= '<tr valign="top">';
                    $aArr['PARAM'] .=   '<td class="monospace">&nbsp;</td>';
                    $aArr['PARAM'] .= '</tr>';

                } else {

                    // Concat param and param description
                    $aArr['PARAM'] .= '<tr valign="top">';
                    $aArr['PARAM'] .=   '<td class="monospace">';
                    $aArr['PARAM'] .=     substr($sStrParam, 0, $j);
                    $aArr['PARAM'] .=   '</td>';
                    $aArr['PARAM'] .=   '<td class="monospace">';
                    $aArr['PARAM'] .=     '&nbsp;=&nbsp;';
                    $aArr['PARAM'] .=   '</td>';
                    $aArr['PARAM'] .=   '<td class="small">';
                    $aArr['PARAM'] .= htmlentities(substr($sStrParam, $j));
                    $aArr['PARAM'] .=   '</td>';
                    $aArr['PARAM'] .= '</tr>';
                }
            }

            $aArr['PARAM'] .= '</table>';
        }

        // ----------------------------------------------------------------

        // Get comments
        preg_match_all(
            '#\#comment:\s+(.+)(?=\#)#Us',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $aArr['COMMENT'] = '';
            for ($i=0, $n=sizeof($aMatches[1]); $i<$n; $i++) {
                $aArr['COMMENT'] .= htmlentities(
                    trim($aMatches[1][$i])
                ) . '<br />';
            }
        }

        // ----------------------------------------------------------------

        // Get caching
        preg_match(
            '#\#caching:\s+(.+)#',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $aArr['CACHING'] = htmlentities(trim($aMatches[1]));
        }

        // ----------------------------------------------------------------

        // Get version
        preg_match(
            '#\#version:\s+(.+)#',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $aArr['VERSION'] = htmlentities(trim($aMatches[1]));
        }

        // ----------------------------------------------------------------

        // Get date
        preg_match(
            '#\#date:\s+(.+)#',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $aArr['DATE'] = htmlentities(trim($aMatches[1]));
        }

        // ----------------------------------------------------------------

        // Get author
        preg_match(
            '#\#author:\s+(.+?)\s+<(.+)>.*#',
            $sStr,
            $aMatches
        );

        if (!empty($aMatches[1])) {
            $sAuthorName = htmlentities(trim($aMatches[1]));
            $sAuthorMail = obfuscateEmail(trim($aMatches[2]));

            $aArr['AUTHOR'] = sprintf(
                '%s &lt;<a href="mailto:%s">%s</a>&gt;',
                $sAuthorName,
                $sAuthorMail,
                $sAuthorMail
            );
        }

        return $aArr;
    }

} // of plugin component

?>
