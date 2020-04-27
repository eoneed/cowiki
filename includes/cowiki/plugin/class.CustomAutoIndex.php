<?php

/**
 *
 * $Id: class.CustomAutoIndex.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      AutoIndex
 * #purpose:   This plugin replace Apache's mod_autoindex
 * #param:     dir        path of directory that should indexed
 *                        (required, default: none)
 * #param:     order      sort order of index entries
 *                        [ na | nd | ta | td | sa | sd ]
 *                        (default: na)
 * #param:     title      headline of output (default: none)
 * #param:     style      CSS style of the output <table> or <div>
 *                        container (default: template dependent)
 * #param:     cutoff     maximal character width of file name
 *                        (default: 40)
 * #param:     longunits  use long unit names instead of B, KB, MB, ...
 *                        (default: false)
 * #param:     showbytes  show size in bytes instead of KB, MB, ...
 *                        (default: false)
 * #param:     head       show table column head (default: true)
 * #param:     uri        URI prefix for file hyper links
 *                        (default: none)
 * #caching:   yes, internal cache for the template
 * #comment:   Handle with care!
 * #comment:   Indexing of sub directories removed in version 1.1.
 * #version:   1.2
 * #date:      27. August 2003
 * #author:    Kai Schröder <k.schroeder@php.net>
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Kai Schröder, {@link http://kai.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - This plugin replace apache's mod_autoindex
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.0
 */
class CustomAutoIndex extends AbstractPlugin {

    // Put in the interface version the plugin works with.
    // This has nothing to do with the @version of this plugin!
    const REQUIRED_INTERFACE_VERSION = 1;

    // Sort order
    const PCAI_SORT_NAME_ASC = 0;
    const PCAI_SORT_NAME_DSC = 1;
    const PCAI_SORT_TIME_ASC = 2;
    const PCAI_SORT_TIME_DSC = 3;
    const PCAI_SORT_SIZE_ASC = 4;
    const PCAI_SORT_SIZE_DSC = 5;

    // --------------------------------------------------------------------

    private
        $sParamDirectory   = '.',
        $nParamOrder       = -1,
        $sParamTitle       = '',
        $sParamTableStyle  = '',
        $sParamUriPrefix   = '',
        $nParamCutOff      = '',
        $bParamSrtUnits    = true,
        $bParamHumanSize   = true,
        $bParamPreventHead = false,
        $sDocumentRoot     = '',
        $sQuery            = '';

    // --------------------------------------------------------------------

    /**
     * Init
     *
     * @access  public
     * @return  boolean
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Set plugin parameters, if passed by a plugin call, or set defaults
     *
     * @access  private
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function getParameters() {

        $this->sParamDirectory = $this->Context->getPluginParam('dir')
            ? $this->Context->getPluginParam('dir') : '.';

        switch (strtolower($this->Request->get('autoindexorder'))) {

            case 'na':
                $this->nParamOrder = self::PCAI_SORT_NAME_ASC;
                break;
            case 'nd':
                $this->nParamOrder = self::PCAI_SORT_NAME_DSC;
                break;
            case 'ta':
                $this->nParamOrder = self::PCAI_SORT_TIME_ASC;
                break;
            case 'td':
                $this->nParamOrder = self::PCAI_SORT_TIME_DSC;
                break;
            case 'sa':
                $this->nParamOrder = self::PCAI_SORT_SIZE_ASC;
                break;
            case 'sd':
                $this->nParamOrder = self::PCAI_SORT_SIZE_DSC;
                break;

            default:
                $sParamOrder = $this->Context->getPluginParam('order');

                switch (strtolower($sParamOrder)) {

                    case 'na':
                        $this->nParamOrder = self::PCAI_SORT_NAME_ASC;
                        break;
                    case 'nd':
                        $this->nParamOrder = self::PCAI_SORT_NAME_DSC;
                        break;
                    case 'ta':
                        $this->nParamOrder = self::PCAI_SORT_TIME_ASC;
                        break;
                    case 'td':
                        $this->nParamOrder = self::PCAI_SORT_TIME_DSC;
                        break;
                    case 'sa':
                        $this->nParamOrder = self::PCAI_SORT_SIZE_ASC;
                        break;
                    case 'sd':
                        $this->nParamOrder = self::PCAI_SORT_SIZE_DSC;
                        break;

                    default:
                        $this->nParamOrder = self::PCAI_SORT_NAME_ASC;
                        break;
                }
            break;
        }

        $this->sParamTitle = $this->Context->getPluginParam('title')
            ? $this->Context->getPluginParam('title')
            : '';

        $this->sParamTableStyle = $this->Context->getPluginParam('style')
            ? $this->Context->getPluginParam('style')
            : '';

        $this->sParamUriPrefix = $this->Context->getPluginParam('uri')
            ? $this->Context->getPluginParam('uri')
            : '';

        $this->nParamCutOff = $this->Context->getPluginParam('cutoff')
            ? abs($this->Context->getPluginParam('cutoff'))
            : 40;

        $this->bParamSrtUnits = $this->Context->hasPluginParam('longunits')
            ? $this->Context->getPluginParamBoolean('longunits')
            : true;

        $this->bParamHumanSize = $this->Context->hasPluginParam('showbytes')
            ? $this->Context->getPluginParamBoolean('showbytes')
            : true;

        $this->bParamPreventHead = $this->Context->hasPluginParam('head')
            ? !$this->Context->getPluginParamBoolean('head')
            : false;

    }

    // --------------------------------------------------------------------

    /**
     * Perform
     *
     * @access  public
     * @return  mixed
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    public function perform() {

        // Build ident depending on plugin parameters
        $sIdent = $this->Context->getPluginParamIdent();

        // If cached result exists, put it out and leave the plugin
        if ($sStr = $this->Context->getFromCache($this, $sIdent, 60)) {
            echo $sStr;
            return true; // leave plugin
        }

        // Set plugin parameters, if passed by a plugin call, or set
        // defaults
        $this->getParameters();

        $this->sDocumentRoot = realpath('.');

        $Node = $this->Context->getCurrentNode();
        if ($Node) {
            $this->sQuery = 'node=' . $Node->get('id') . '&';
        }

        if (!$sDirectory = $this->getDirectory()) {
            $this->Context->addError(511);
            $this->Context->resume();
            return true;
        }

        $aTplItem = $this->readDirectory($sDirectory);
        $this->sortDirectory($aTplItem);

        // Set plugin parameters for template
        $this->Template->set('TPL_TABLE_STYLE', $this->sParamTableStyle);

        if ($this->sParamTitle != '') {
            $this->Template->set('TPL_TITLE', $this->sParamTitle);
        }

        if ($this->bParamPreventHead === false && sizeof($aTplItem) >= 2) {
            $this->Template->set('TPL_SHOW_HEAD', 1);
        }
        $this->Template->set('TPL_ITEM', $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.auto.index.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr);

        echo $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Get directory name
     *
     * @access  private
     * @return  mixed
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function getDirectory() {

        $sRealPath = realpath($this->sParamDirectory);

        // Real path does not exist
        if ($sRealPath === false) {
            return false;
        }

        // Real path is not a subdirectory of document root (string too short)
        if (strlen($sRealPath) <= strlen($this->sDocumentRoot)) {
            return false;
        }

        // Real path is not a subdirectory of document root ($sRealPath
        // does not begin with $sDocRoot)
        if (strpos($sRealPath, $this->sDocumentRoot) !== 0) {
            return false;
        }

        // $sRealPath is not a directory
        if (!is_dir($sRealPath)) {
            return false;
        }

        // $sRealPath is not readable for the web server user
        if (!is_readable($sRealPath)) {
            return false;
        }

        return $sRealPath;
    }

    // --------------------------------------------------------------------

    /**
     * Read directory
     *
     * @access  private
     * @param   string
     * @return  array
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function readDirectory($sDirectory) {

        $sImgPath = $this->Registry->get('PATH_IMAGES');
        $sIconFile = sprintf(
            '<img src="%sdoc.gif" width="18" height="20" alt="%s"'
            . ' border="0">',
            $sImgPath,
            __('I18N_DOC')
        );

        $sUriPrefix = $this->sParamUriPrefix
                    ? $this->sParamUriPrefix
                    : substr($sDirectory, strlen($this->sDocumentRoot) + 1);

        $aIndex = array();

        if ($rDirectory = @opendir($sDirectory)) {

            while (($sFile = @readdir($rDirectory))) {

                // Dot file -> ignore
                if ($sFile{0} == '.') {
                    continue;
                }

                $sFilePath = $sDirectory . '/' . $sFile;

                // Not a file -> ignore
                if (!is_file($sFilePath)) {
                    continue;
                }

                // File system soft link -> ignore
                if (is_link($sFilePath)) {
                    continue;
                }

                // File is not readable -> ignore
                if (!is_readable($sFilePath)) {
                    continue;
                }

                $nLastModifiedAbsolute = filemtime($sFilePath);
                $sLastModifiedRelative =
                    $this->Context->makeDateTimeRelative(
                        $nLastModifiedAbsolute
                    );
                $nSizeAbsolute = filesize($sFilePath);
                $sSizeFormated = $this->formatSize(
                                    $nSizeAbsolute,
                                    $this->bParamSrtUnits
                                 );

                $aIndex[] = array(
                    'NAME'              => escape(cutOff(
                                                      $sFile,
                                                      $this->nParamCutOff
                                                  )
                                           ),
                    'TITLE'             => $sFile,
                    'HREF'              => $sUriPrefix . '/' . $sFile,
                    'LAST_MODIFIED_ABS' => $nLastModifiedAbsolute,
                    'LAST_MODIFIED'     => $sLastModifiedRelative,
                    'SIZE_ABS'          => $nSizeAbsolute,
                    'SIZE'              => $sSizeFormated,
                    'ICON'              => $sIconFile
                );
            }
        }

        return $aIndex;
    }

    // --------------------------------------------------------------------

    /**
     * Compare file names
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function compareName($aA, $aB) {
        return strcasecmp($aA['TITLE'], $aB['TITLE']);
    }

    // --------------------------------------------------------------------

    /**
     * Compare file last modification time
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function compareTime($aA, $aB) {
        $iCmp = strcasecmp($aA['LAST_MODIFIED_ABS'], $aB['LAST_MODIFIED_ABS']);

        if ($iCmp === 0) {
            return strcasecmp($aA['TITLE'], $aB['TITLE']);
        } else {
            return $iCmp;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Compare file size
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function compareSize($aA, $aB) {
        if ($aA['SIZE_ABS'] == $aB['SIZE_ABS']) {
            return 0;
        }

        return ($aA['SIZE_ABS'] < $aB['SIZE_ABS']) ? -1 : 1;
    }

    // --------------------------------------------------------------------

    /**
     * Format size
     *
     * @access  private
     * @param   integer
     * @param   boolean
     * @return  string
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function formatSize($nSize, $bShortUnits = false) {
        static
            $aLocaleConv,
            $aUnits;

        if (!is_array($aLocaleConv)) {
            $aLocaleConv = localeconv();

        /* set default values if we don't get anything from the
           locale settings */

            $aLocaleConv['mon_decimal_point'] =
          $aLocaleConv['mon_decimal_point'] ?
          $aLocaleConv['mon_decimal_point'] : '.';

            $aLocaleConv['mon_thousands_sep'] =
          $aLocaleConv['mon_thousands_sep'] ?
          $aLocaleConv['mon_thousands_sep'] : '\'';

        }

        if (!is_array($aUnits)) {
            $aUnits = array(
                'long' => array(
                    __('I18N_BYTES'),
                    __('I18N_KILOBYTES'),
                    __('I18N_MEGABYTES'),
                    __('I18N_GIGABYTES'),
                    __('I18N_TERABYTES')
                ),
                'short' => array(
                    'B',
                    'KB',
                    'MB',
                    'GB',
                    'TB'
                )
            );
        }

        $nUnits = $bShortUnits
                ? count($aUnits['short'])
                : count($aUnits['long']);

        if ($this->bParamHumanSize) {
            $nSize1024 = $nSize;
            $nUnit = 0;

            while ($nSize1024 >= 1024 && $nUnit < $nUnits - 1) {
                $nSize1024 = $nSize1024/1024;
                $nUnit++;
            }

            $sSize = sprintf(
                '%s %s',
                number_format(
                    $nSize1024,
                    $nUnit == 0 ? 0 : 2,
                    $aLocaleConv['mon_decimal_point'],
                    $aLocaleConv['mon_thousands_sep']
                ),
                $bShortUnits ? $aUnits['short'][$nUnit]
                             : $aUnits['long'][$nUnit]
            );

        } else {
            $sSize = sprintf(
                '%s %s',
                number_format(
                    $nSize,
                    0,
                    $aLocaleConv['mon_decimal_point'],
                    $aLocaleConv['mon_thousands_sep']
                ),
                $bShortUnits ? $aUnits['short'][0]
                             : $aUnits['long'][0]
            );
        }

        return $sSize;
    }

    // --------------------------------------------------------------------

    /**
     * Sort directory
     *
     * @access  private
     * @param   array
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    private function sortDirectory(&$aEntries) {
        $sNameOrder = 'na';
        $sTimeOrder = 'ta';
        $sSizeOrder = 'sa';

        switch ($this->nParamOrder) {

            case self::PCAI_SORT_NAME_ASC:
                $sNameOrder = 'nd';
                usort($aEntries, array($this, 'compareName'));
                break;

            case self::PCAI_SORT_NAME_DSC:
                $sNameOrder = 'na';
                usort($aEntries, array($this, 'compareName'));
                $aEntries = array_reverse($aEntries);
                break;

            case self::PCAI_SORT_TIME_ASC:
                $sTimeOrder = 'td';
                usort($aEntries, array($this, 'compareTime'));
                break;

            case self::PCAI_SORT_TIME_DSC:
                $sTimeOrder = 'ta';
                usort($aEntries, array($this, 'compareTime'));
                $aEntries = array_reverse($aEntries);
                break;

            case self::PCAI_SORT_SIZE_ASC:
                $sSizeOrder = 'sd';
                usort($aEntries, array($this, 'compareSize'));
                break;

            case self::PCAI_SORT_SIZE_DSC:
                $sSizeOrder = 'sa';
                usort($aEntries, array($this, 'compareSize'));
                $aEntries = array_reverse($aEntries);
                break;

            default:
                break;
        }

        $this->Template->set(
            'TPL_HREF_NAME',
            $this->Response->getControllerHref(
                $this->sQuery . 'autoindexorder=' . $sNameOrder
            )
        );
        $this->Template->set(
            'TPL_HREF_TIME',
            $this->Response->getControllerHref(
                $this->sQuery . 'autoindexorder=' . $sTimeOrder
            )
        );
        $this->Template->set(
            'TPL_HREF_SIZE',
            $this->Response->getControllerHref(
                $this->sQuery . 'autoindexorder=' . $sSizeOrder
            )
        );
    }

} // of plugin component

?>

