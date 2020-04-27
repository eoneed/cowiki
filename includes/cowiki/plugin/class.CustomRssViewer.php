<?php

/**
 *
 * $Id: class.CustomRssViewer.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      RssViewer
 * #purpose:   Display content of RSS files
 * #param:     src      RSS file (required, default: none)
 * #param:     style    CSS style of the output <table> or <div>  container
 *                      (default: template dependent)
 * #param:     expires  Time in seconds for the internal cache to expire.
 *                      Feed will be fetched again after this time period.
 *                      (default: 3600 - which is one hour)
 * #param:     target   URI Target (default: _blank)
 * #param:     prefix   URI Prefix (default: none)
 * #caching:   yes, internal cache for the template
 * #version:   1.0
 * #date:      18. October 2003
 * #author:    Kai Schröder <k.schroeder@php.net>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Kai Schröder {@link http://kai.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * Display content of RSS files
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.3
 */
class CustomRssViewer extends AbstractPlugin {

    // Put in the interface version the plugin works with.
    // This has nothing to do with the @version of this plugin!
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean   true if initialization successful,
     *                    false otherwise
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform the plugin purpose. This is the main method of the plugin.
     *
     * @access  public
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function perform() {

        // Set plugin parameters, if passed by a plugin call, or set
        // defaults
        $sSrc = $this->Context->getPluginParam('src')
                    ? $this->Context->getPluginParam('src')
                    : '';

        $sStyle = $this->Context->getPluginParam('style')
                    ? $this->Context->getPluginParam('style')
                    : '';

        $nExpire = $this->Context->getPluginParam('expires')
                    ? (int)abs($this->Context->getPluginParam('expires'))
                    : 3600;

        $sTarget = $this->Context->getPluginParam('target')
                 ? $this->Context->getPluginParam('target')
                 : '_blank';

        $sPrefix = $this->Context->getPluginParam('prefix')
                 ? $this->Context->getPluginParam('prefix')
                 : '';

        // ---

        // Build ident depending on plugin parameters
        $sIdent = $this->Context->getPluginParamIdent();

        // If cached result exists, put it out and leave the plugin
        if ($sStr = $this->Context->getFromCache($this, $sIdent, $nExpire)) {
            echo $sStr;
            return true; // leave plugin
        }

        // ---

        // Get current user encoding
        $sEnc = $this->Context->getCurrentUser()->getEncoding();

        // Try to read the feed
        $RssManager = new RssManager();
        $RssFeed = $RssManager->readFeed($sSrc, $sEnc);

        // Give feed errors to runtime context and exit plugin
        if (!$RssFeed->getErrors()->isEmpty()) {
            $It = $RssFeed->getErrors()->iterator();
            while ($Error = $It->next()) {
                if ($Error->has('message')) {
                    $this->Context->addError(
                        $Error->get('code'),
                        $Error->get('message')
                    );
                } else {
                    $this->Context->addError($Error->get('code'));
                }

                $this->Context->resume();
            }

            return true;
        }

        if ($RssFeed->getItems()->isEmpty()) {
            $this->Context->addError(316, 'RSS feed contains no items.');
            $this->Context->resume();

            return true;
        }

        // ----------------------------------------------------------------

        $aTplItem = array();
        $nDesc = 0;
        $aDates = array();

        $It = $RssFeed->getItems()->iterator();
        while ($Item = $It->next()) {
            if (strlen($Item->get('description')) >
                strlen($Item->get('title'))) {
                $nDesc++;
            }
            $aTplItem[] = array(
                'HREF'   => $sPrefix.$Item->get('link'),
                'NAME'   => htmlentities($Item->get('title')),
                'TEASER' => htmlentities($Item->get('description')),
                'DATE'   => $Item->getRelativeDate(),
                'TARGET' => $sTarget,
            );
            $aDates[] = $Item->getRelativeDate();
        }

        // ----------------------------------------------------------------

        $this->Template->set('TPL_TABLE_STYLE', $sStyle);

        if ($RssFeed->get('title')) {
            $this->Template->set('TPL_TITLE', $RssFeed->get('title'));
        }
        if ($RssFeed->get('link')) {
            $this->Template->set('TPL_HREF', $RssFeed->get('link'));
        } else {
            $this->Template->set('TPL_HREF', $sSrc);
        }
        if ($RssFeed->get('description')) {
            $this->Template->set(
                'TPL_DESCRIPTION',
                $RssFeed->get('description')
            );
        }

        $aDates = array_unique($aDates);
        if ($nDesc/$RssFeed->getItems()->size() > 0.5) {
            if (sizeof($aDates)/$RssFeed->getItems()->size() > 0.5) {
                $this->Template->set('TPL_SHOW_TEASER_DATE', true);
            } else {
                $this->Template->set('TPL_SHOW_TEASER', true);
            }
        } else {
            if (sizeof($aDates)/$RssFeed->getItems()->size() > 0.5) {
                $this->Template->set('TPL_SHOW_NAME_DATE', true);
            } else {
                $this->Template->set('TPL_SHOW_NAME_ONLY', true);
            }
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.rss.viewer.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr, $sIdent);

        echo $sStr;
    }

} // of plugin component

?>
