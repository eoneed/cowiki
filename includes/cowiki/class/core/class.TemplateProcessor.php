<?php

/**
 *
 * $Id: class.TemplateProcessor.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Template processor class
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
class TemplateProcessor extends Object {

    protected static
        $Instance = null;

    protected
        $Context  = null,
        $Registry = null,
        $Template = null,
        $PluginLoader = null,
        $aForEachItems = array(),
        $nForEachPos = 0;

    // --------------------------------------------------------------------

    /**
     * Get instance
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
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new TemplateProcessor;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $this->Context  = RuntimeContext::getInstance();
        $this->Registry = $this->Context->getRegistry();
        $this->Template = $this->Context->getTemplateRegistry();
        $this->PluginLoader = $this->Context->getPluginLoader();
    }

    // --------------------------------------------------------------------

    /**
     * Parse
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function parse($sTplName) {

        try {

            $Loader = new TemplateLoader();
            $sFile = $Loader->load($sTplName);

        } catch (Exception $e) {
            return $this->Context->resume();
        }

        // Check for template "include". Calls $this->parse() recursive
        $sFile = preg_replace_callback(
            '=\{include\s+([A-Z0-9_.]+)\s*\}=USsi',
            array($this, 'processInclude'),
            $sFile
        );

        return $this->processTemplate($sFile);
    }

    // --------------------------------------------------------------------

    /**
     * Process include
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function processInclude(&$aMatches) {
        return $this->parse(trim($aMatches[1]));;
    }

    // --------------------------------------------------------------------

    /**
     * Start main processing
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processTemplate(&$sFile) {

        // Get rid of comments {* ... *}
        $sFile = preg_replace('=\{\*.+\*\}=Us', '', $sFile);

        // Get rid of vars. {var ...}{/var} is for future use
        $sFile = preg_replace(
                    '=\{var\s+[A-Z0-9_]+\}.+\{/var\}=Us',
                    '',
                    $sFile
                 );

        // ----------------------------------------------------------------

        // Check for "foreach"
        $sFile = preg_replace_callback(
            '=\{foreach\s+\%([A-Z0-9_]+)%\}(.+)(\{/foreach\})=Us',
            array($this, 'processForEach'),
            $sFile
        );

        // ----------------------------------------------------------------

        // Check for "ifdefined"
        $sFile = preg_replace_callback(
            '=\{ifdefined\s+\%([A-Z0-9_]+)%\}(.+)(\{/ifdefined\})=Us',
            array($this, 'processIfDefined'),
            $sFile
        );

        // Check for "ifnotdefined"
        $sFile = preg_replace_callback(
            '=\{ifnotdefined\s+\%([A-Z0-9_]+)%\}(.+)(\{/ifnotdefined\})=Us',
            array($this, 'processIfNotDefined'),
            $sFile
        );

        // ----------------------------------------------------------------

        // Check for "ifempty"
        $sFile = preg_replace_callback(
            '#\{ifempty\s+\%([A-Z0-9_]+)%\}(.+)(\{/ifempty\})#Us',
            array($this, 'processIfEmpty'),
            $sFile
        );

        // Check for "ifnotempty"
        $sFile = preg_replace_callback(
            '#\{ifnotempty\s+\%([A-Z0-9_]+)%\}(.+)(\{/ifnotempty\})#Us',
            array($this, 'processIfNotEmpty'),
            $sFile
        );

        // ----------------------------------------------------------------

        // Replace I18N contants in template
        $sFile = preg_replace_callback(
            '=\{%(I18N_[A-Z0-9_]+)%\}=Us',
            array($this, 'processI18N'),
            $sFile
        );

        // Replace remaining constants
        $sFile = preg_replace_callback(
            '=\{%([A-Z0-9_]+)%\}=Us',
            array($this, 'processConstant'),
            $sFile
        );

        // ----------------------------------------------------------------

        // Call referenced plugins
        $sFile = preg_replace_callback(
            '=\{plugin\s+([A-Z0-9_.]+)(\s+[^}]+)?\s*\}=Usi',
            array($this, 'processPlugin'),
            $sFile
        );

        return $sFile;
    }

    // --------------------------------------------------------------------

    /**
     * Process constant
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processConstant(&$aMatches) {
        $Source = null;

        // Check for hidden section constants (with leading dot)
        if ($aMatches[1]{0} != '.') {

            if ($this->Template->has($aMatches[1])) {
                $Source = $this->Template;
            } else if ($this->Registry->has($aMatches[1])) {
                $Source = $this->Registry;
            }

            if (is_object($Source)) {

                if (is_array($Source->get($aMatches[1]))) {
                    $this->Context->addError(322, '{%'.$aMatches[1].'%}');
                    $this->Context->terminate();
                }

                // Return possible open curly braces as entities to avoid
                // re-parsing and code injection with e.g. document
                // names like "{plugin foo}"
                return str_replace('{', '&#123;', $Source->get($aMatches[1]));
            }
        }

        return '{%'.$aMatches[1].'%}';
    }

    // --------------------------------------------------------------------

    /**
     * Process I18N
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function processI18N(&$aMatches) {
        return __($aMatches[1]);
    }

    // --------------------------------------------------------------------

    /**
     * Process plugin
     *
     * @access  protected
     * @param   array
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processPlugin(&$aMatches) {
        ob_start();

        // Reset Layouter attributes
        $this->Context->getLayouter()->init();

        // Any parameters given?
        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $this->Context->setPluginParam($aMatches[2]);
        }

        // Buffered ...
        echo $this->PluginLoader->load($aMatches[1]);

        $sContent = ob_get_contents(); ob_end_clean();

        return $sContent;
    }

    // --------------------------------------------------------------------

    /**
     * Process for each
     *
     * @access  protected
     * @param   array
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processForEach(&$aMatches) {
        $this->aForEachItems = $this->Template->get($aMatches[1]);

        $sStr = '';

        for ($i=0, $n=sizeof($this->aForEachItems); $i<$n; $i++) {
            $this->nForEachPos = $i;

            $sStr .= preg_replace_callback(
                '=\{%'.$aMatches[1].'\[(\'|")([A-Z0-9_]+)(\1)\]%\}=USs',
                array($this, 'processForEachItem'),
                $aMatches[2]
            );
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process for each item
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processForEachItem(&$aMatches) {

        // Return possible open curly braces as entities to avoid
        // re-parsing and code injection with e.g. document
        // names like "{plugin foo}"
        if (isset($this->aForEachItems[$this->nForEachPos][$aMatches[2]])) {
            return str_replace(
                      '{',
                      '&#123;',
                      $this->aForEachItems[$this->nForEachPos][$aMatches[2]]
                   );
        }
        return '';
    }

    // --------------------------------------------------------------------

    /**
     * Process if defined
     *
     * @access  protected
     * @param   array
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processIfDefined($aMatches) {
        if ($this->Template->has($aMatches[1])) {
            return $aMatches[2];
        }

        if ($this->Registry->has($aMatches[1])) {
            return $aMatches[2];
        }

        return '';
    }

    // --------------------------------------------------------------------

    /**
     * Process if not defined
     *
     * @access  protected
     * @param   array
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processIfNotDefined($aMatches) {
        if ($this->Template->has($aMatches[1])) {
            return '';
        }

        if ($this->Registry->has($aMatches[1])) {
            return '';
        }

        return $aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Process if empty
     *
     * @access  protected
     * @param   array
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    protected function processIfEmpty($aMatches) {
        if ($this->Template->has($aMatches[1])
            && $this->Template->get($aMatches[1]) != '') {
            return '';
        }

        if ($this->Registry->get($aMatches[1]) != '') {
            return '';
        }

        return $aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Process if not empty
     *
     * @access  protected
     * @param   array
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    protected function processIfNotEmpty($aMatches) {
        if ($this->Template->has($aMatches[1])
            && $this->Template->get($aMatches[1]) != '') {
            return $aMatches[2];
        }

        if ($this->Registry->get($aMatches[1]) != '') {
            return $aMatches[2];
        }

        return '';
    }

} // of class

?>
