<?php

/**
 *
 * $Id: class.XmlEventParser.php 27 2011-01-09 12:37:59Z eoneed $
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
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Kai Schröder, {@link http://kai.cowiki.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 27 $
 *
 */

/**
 * Parse an XML document into an array representation using an event
 * based parser.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 *
 * @since       coWiki 0.3.0
 */
class XmlEventParser extends AbstractXmlEventParser {

    protected
        $nDepth = 0,
        $aStack = array();

    // --------------------------------------------------------------------

    /**
     * The class constructor initializes its element event handlers
     * (start-, end-, character- and default handler).
     *
     * @access  public
     * @param   string  The encoding definition (optional). The default
     *                  encoding is UTF-8.
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.1
     */
    public function __construct($sEncoding = 'UTF-8') {
        parent::__construct($sEncoding);

        $this->init(
            'startElementHandler',
            'endElementHandler',
            'characterDataHandler',
            'defaultDataHandler'
        );
    }

    // --------------------------------------------------------------------

    /**
     * Parse a given XML document into an array representation.
     *
     * @access  public
     * @param   string  The wellformed(!) XML string.
     * @return  array   The parsed XML structure as an nested array.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function parse(&$sStr) {
        parent::parse($sStr);

        if (isset($this->aItems[1])) {
            $this->aItems = $this->aItems[1][0];
        }

        return $this->getItems();
    }

    // --------------------------------------------------------------------

    /**
     * Return the previously parsed string without reparsing it.
     *
     * @access  public
     * @param   string  The output encoding.
     * @return  array   The parsed XML structure as an nested array.
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     *
     * @throws  XmlSyntaxException
     */
    public function getItems($sEncoding = '') {
        if (!isset($this->aItems)) {
            throw new XmlSyntaxException();
        }

        if ($sEncoding == '') {
            return $this->aItems;

        } else {
            return $this->getEncodedItems($sEncoding);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Return the previously parsed string without reparsing it.
     *
     * @access  public
     * @param   string  The output encoding.
     * @return  array   The parsed XML structure as an nested array.
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getEncodedItems($sEncoding) {
        if ($sEncoding == $this->getEncoding()) {
            return $this->getItems();
        }

        return $this->convertEncoding($this->aItems, $sEncoding);
    }

    // --------------------------------------------------------------------

    protected function convertEncoding($aInputItem, $sEncoding) {
        $aOutputItem = $aInputItem;

        if (isset($aInputItem[Xml::ELM_INDEX])) {
            foreach (array_keys($aInputItem[Xml::ELM_INDEX]) as $nIndex) {
                $aOutputItem[Xml::ELM_INDEX][$nIndex] = $this->convertEncoding(
                    $aInputItem[Xml::ELM_INDEX][$nIndex],
                    $sEncoding
                );
            }
        }

        if (isset($aInputItem[Xml::ELM_CDATA])) {
            if (function_exists('mb_convert_encoding')) {
                $aOutputItem[Xml::ELM_CDATA] = mb_convert_encoding(
                    $aInputItem[Xml::ELM_CDATA],
                    $sEncoding,
                    $this->getEncoding()
                );
            } else {
                if ($this->getEncoding() == 'UTF-8' &&
                    $sEncoding == 'ISO-8859-1') {
                    $aOutputItem[Xml::ELM_CDATA] =
                        utf8_decode($aInputItem[Xml::ELM_CDATA]);
                } elseif ($this->getEncoding() == 'ISO-8859-1' &&
                    $sEncoding == 'UTF-8') {
                    $aOutputItem[Xml::ELM_CDATA] =
                        utf8_encode($aInputItem[Xml::ELM_CDATA]);
                }
            }
        }

        return $aOutputItem;
    }

    // --------------------------------------------------------------------

    /**
     * Start element handler
     *
     * @access  protected
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    protected function startElementHandler($rParser, $sElm, $aAttr) {
        $this->nDepth++;

        $aNode = array(
            Xml::ELM_NAME  => $sElm,
            Xml::ELM_ATTR  => $aAttr,
            Xml::ELM_CDATA => '',
            Xml::ELM_LEVEL => $this->nDepth
        );

        // prepend node to the beginning of stack
        array_unshift($this->aStack, $aNode);
    }

    // --------------------------------------------------------------------

    /**
     * End element handler
     *
     * @access  protected
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    protected function endElementHandler($rParser, $sElm) {
        static $nLevel = 0;

        // Shift a node off the beginning of stack
        $aNode = array_shift($this->aStack);

        // Remove useless white spaces and empty attributes
        $sCData = preg_replace('#\s{2,}#', ' ', trim($aNode[Xml::ELM_CDATA]));

        if ($sCData == '') {
            unset($aNode[Xml::ELM_CDATA]);
        } else {
            $aNode[Xml::ELM_CDATA] = $sCData;
        }

        if (empty($aNode[Xml::ELM_ATTR])) {
            unset($aNode[Xml::ELM_ATTR]);
        }

        // add node to items list
        if ($nLevel > $aNode[Xml::ELM_LEVEL]) {
            if (isset($this->aItems[$aNode[Xml::ELM_LEVEL] + 1])) {

                $aNode[Xml::ELM_INDEX] =
                    $this->aItems[$aNode[Xml::ELM_LEVEL] + 1];

                unset($this->aItems[$aNode[Xml::ELM_LEVEL] + 1]);
            }
        }

        if (!isset($this->aItems[$aNode[Xml::ELM_LEVEL]])) {
            $this->aItems[$aNode[Xml::ELM_LEVEL]] = array();
        }

        $nLevel = $aNode[Xml::ELM_LEVEL];
        unset($aNode[Xml::ELM_LEVEL]);
        array_push($this->aItems[$nLevel], $aNode);

        $this->nDepth--;
    }

    // --------------------------------------------------------------------

    /**
     * Character data handler
     *
     * @access  protected
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    protected function characterDataHandler($rParser, $sData) {
        if (!empty($this->aStack)) {
            $this->aStack[0][Xml::ELM_CDATA] .= html_entity_decode($sData);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Default data handler
     *
     * @access  protected
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    protected function defaultDataHandler($rParser, $sData) {
        if (!empty($this->aStack)) {
            $this->aStack[0][Xml::ELM_CDATA] .= '';
        }
    }
} // of class

/*
    If you get the inspiration, to increase the population /
    take your girl friend behind a door,
    and lay her softly on the floor /
    then take away her decoration
    and begin the operation /
    when your girl friend falls in action,
    you will get your satisfaction /
    if your babe gets a baby,
    join the Army or the Navy!
*/

?>
