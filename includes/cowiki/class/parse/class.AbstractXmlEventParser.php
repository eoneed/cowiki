<?php

/**
 *
 * $Id: class.AbstractXmlEventParser.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     parse
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
 * Abstract XML event parser
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
abstract class AbstractXmlEventParser extends Xml {

    protected
        $sEncoding = '',
        $rParser = null,
        $sStart = '',
        $sEnd = '',
        $sCData = '',
        $sDefault = '';

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  The encoding definition (optional). The default
     *                  encoding is UTF-8.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct($sEncoding = 'UTF-8') {
        $this->sEncoding = strtoupper($sEncoding);
    }

    // --------------------------------------------------------------------

    /**
     * Class destructor. Free allocated parser space.
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __destruct() {
        $this->free();
    }

    // --------------------------------------------------------------------

    /**
     * Initialize the XML parser and set its event handlers. This mehod
     * is used by derived classes and hence protected.
     *
     * @access  protected
     * @param   string  Start element handler method
     * @param   string  End element handler method
     * @param   string  CDATA element handler method
     * @param   string  Default element handler method
     * @return  boolean true if successful, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function init($sStart, $sEnd, $sCData, $sDefault) {
        $this->sStart   = $sStart;
        $this->sEnd     = $sEnd;
        $this->sCData   = $sCData;
        $this->sDefault = $sDefault;

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Free allocated parser space.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function free() {
        if ($this->rParser) {
            xml_parser_free($this->rParser);
            $this->rParser = null;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Return the XML encoding (eg. ISO-8859-1 or UTF-8)
     *
     * @access  public
     * @return  string  The encoding of the XML document.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getEncoding() {
        return $this->sEncoding;
    }

    // --------------------------------------------------------------------

    /**
     * Return the XML definition and the document encoding.
     *
     * @access  public
     * @return  string  The XML definition string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getXmlDefinition() {
        $sXmlDefinition = '<?xml version="1.0" encoding="'.
            $this->getEncoding() . '"?>' . "\n\n";

        return $sXmlDefinition;
    }

    // --------------------------------------------------------------------

    /**
     * Parse the given XML string.
     *
     * @access  public
     * @param   string  Complete XML string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     *
     * @todo    [FIX] add error handling
     */
    public function parse(&$sStr) {
        if (preg_match('#<\?xml\s+.*encoding=\s*(["|\']*?)(\S+?)\\1.*\?>#Usm',
            $sStr, $aMatches)) {
            $this->sEncoding = strtoupper($aMatches[2]);
        }

        $this->rParser = xml_parser_create($this->sEncoding);

        if (!$this->rParser) {
            return false;
        }

        xml_set_object($this->rParser, $this);

        // Set parser options
        xml_parser_set_option($this->rParser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->rParser, XML_OPTION_SKIP_WHITE, 0);

        // Set event handler
        xml_set_element_handler($this->rParser, $this->sStart, $this->sEnd);
        xml_set_character_data_handler ($this->rParser, $this->sCData);
        xml_set_default_handler($this->rParser, $this->sDefault);

        $aHtmlTranslationTable = array_flip(
            get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES)
        );
        unset($aHtmlTranslationTable['&amp;']);
        unset($aHtmlTranslationTable['&lt;']);
        unset($aHtmlTranslationTable['&gt;']);

        // FIX: add error handling
        $bSuccess = xml_parse(
            $this->rParser,
            strtr($sStr, $aHtmlTranslationTable),
            true
        );

        if (!$bSuccess) {
            $nErrorCode    = xml_get_error_code($this->rParser);
            $sErrorString  = xml_error_string($nErrorCode);
            $nLineNumber   = xml_get_current_line_number($this->rParser);
            $nColumnNumber = xml_get_current_column_number($this->rParser);
        }

        $this->free();
    }

    // --------------------------------------------------------------------

    /**
     * Abstract start element handler. A derived class has to implement it.
     *
     * @access  protected
     * @param   resource  XML parser handle
     * @param   string    Element name
     * @param   array     Element attributes
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function startElementHandler($rParser, $sElm, $aAttr);

    // --------------------------------------------------------------------

    /**
     * Abstract end element handler. A derived class has to implement it.
     *
     * @access  protected
     * @param   resource  XML parser handle
     * @param   string    Element name
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function endElementHandler($rParser, $sElm);

    // --------------------------------------------------------------------

    /**
     * Abstract CDATA handler. A derived class has to implement it.
     *
     * @access  protected
     * @param   resource  XML parser handle
     * @param   string    CDATA
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function characterDataHandler($rParser, $sData);

    // --------------------------------------------------------------------

    /**
     * Abstract default data handler. A derived class has to implement it.
     *
     * @access  protected
     * @param   resource  XML parser handle
     * @param   string    Arbitrary string data
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract protected function defaultDataHandler($rParser, $sData);

} // of class

?>