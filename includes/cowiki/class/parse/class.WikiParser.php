<?php

/**
 *
 * $Id: class.WikiParser.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * Helping hands: Matt Ho <matt@xtreme.com>
 * </pre>
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

    // We should solve this with lex/yacc (flex/bison), but mass hosters
    // usually do not allow their users to execute binaries. So let's
    // fight this (dumb) way.

    // On the other hand, the coWiki syntax is quite loose and is not easy
    // to express by a BNF parser, IMO.

    define('WIKI_TOKEN_NOOP',          1);
    define('WIKI_TOKEN_PRE',           2);
    define('WIKI_TOKEN_CODE',          3);
    define('WIKI_TOKEN_POSTING',       4);
    define('WIKI_TOKEN_LINK',          5);
    define('WIKI_TOKEN_URI',           6);
    define('WIKI_TOKEN_TOC',           7);
    define('WIKI_TOKEN_PARAM_PLUGIN',  8);
    define('WIKI_TOKEN_SIMPLE_PLUGIN', 9);
    define('WIKI_TOKEN_HEADING',       10);
    define('WIKI_TOKEN_HR',            11);
    define('WIKI_TOKEN_LIST',          12);
    define('WIKI_TOKEN_QUOTE',         13);
    define('WIKI_TOKEN_VAR',           14);
    define('WIKI_TOKEN_BREAK',         15);
    define('WIKI_TOKEN_PARAM_TABLE',   16);
    define('WIKI_TOKEN_SIMPLE_TABLE',  17);
    define('WIKI_TOKEN_REM',           18);
    define('WIKI_TOKEN_SUB',           19);
    define('WIKI_TOKEN_SUP',           20);

/**
 * coWiki - Wiki parser class
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class WikiParser extends Object {

    protected static
        $Instance = null;

    // Containers for the <toc> - table of contents (headings).
    protected
        $aToc = array(),
        $sDocToc = false;

    // Container for tokenized strings
    protected
        $aToken = array();

    // Elements that do NOT reside in <P>-paragraphs
    protected
        $aNonPara = array(
            WIKI_TOKEN_PRE,
            WIKI_TOKEN_CODE,
            WIKI_TOKEN_POSTING,
            WIKI_TOKEN_TOC,
            WIKI_TOKEN_PARAM_PLUGIN,
            WIKI_TOKEN_SIMPLE_PLUGIN,
            WIKI_TOKEN_HEADING,
            WIKI_TOKEN_HR,
            WIKI_TOKEN_LIST,
            WIKI_TOKEN_QUOTE,
            WIKI_TOKEN_PARAM_TABLE,
            WIKI_TOKEN_SIMPLE_TABLE,
            WIKI_TOKEN_SUB,
            WIKI_TOKEN_SUP
        );

    // Helper
    protected
        $RefNodes    = null,
        $aList       = array(),
        $aTblRowNoop = array();

    // --------------------------------------------------------------------

    /**
     * Get instance
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new WikiParser;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {}

    // --- Helper methods -------------------------------------------------

    /**
     * Set doc toc
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function setDocToc($sStr) {
        $this->sDocToc = $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Get doc toc
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function getDocToc() {
        return $this->sDocToc;
    }

    // --------------------------------------------------------------------

    /**
     * Get token count
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function getTokenCount() {
        return sizeof($this->aToken);
    }

    // --------------------------------------------------------------------

    /**
     * Set restore flag
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function setRestoreFlag($bFlag) {
        $this->bRestoreFlag = $bFlag;
    }

    // --------------------------------------------------------------------

    /**
     * Get restore flag
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function getRestoreFlag() {
        return $this->bRestoreFlag;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenize($sStr, $nType, $mMeta = false) {
        $i = $this->getTokenCount();

        $this->aToken[$i]['CONTENT'] = $sStr;
        $this->aToken[$i]['TYPE']    = $nType;
        $this->aToken[$i]['META']    = $mMeta;

        // (Token-)Elements which do NOT reside in <P>-paragraphs, are
        // marked with a leading \r as such.
        if (in_array($nType, $this->aNonPara)) {
            return "\r\t" . $i . "\t";
        }

        return "\t" . $i . "\t";
    }

    // --------------------------------------------------------------------

    /**
     * Restore tokens
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function restoreTokens($sStr) {

        while (true) {
            $this->setRestoreFlag(false);

            $sStr = preg_replace_callback(
                '=\r?\t([0-9]+)\t=US',
                array(&$this, 'invokeTokens'),
                $sStr
            );

            if ($this->getRestoreFlag() == false) {
                break;
            }
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Invoke tokens
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function invokeTokens(&$aMatches) {
        $this->setRestoreFlag(true);

        $sStr  = $this->aToken[$aMatches[1]]['CONTENT'];
        $nType = $this->aToken[$aMatches[1]]['TYPE'];
        $mMeta = $this->aToken[$aMatches[1]]['META'];

        $sStr = $this->restoreTokens($sStr);

        switch ($nType) {
            case WIKI_TOKEN_NOOP:
                $sStr = $this->createNoopElement($sStr);
                break;

            case WIKI_TOKEN_REM:
                $sStr = $this->createRemarkElement($sStr);
                break;

            case WIKI_TOKEN_PRE:
                $sStr = $this->createPreElement($sStr);
                break;

            case WIKI_TOKEN_CODE:
                $sStr = $this->createCodeElement($sStr);
                break;

            case WIKI_TOKEN_POSTING:
                $sStr = $this->createPostingElement($sStr);
                break;

            case WIKI_TOKEN_LINK:
                $sStr = $this->createLinkElement($sStr);
                break;

            case WIKI_TOKEN_URI:
                $sStr = $this->createUriElement($sStr);
                break;

            case WIKI_TOKEN_TOC:
                $sStr = $this->createTocElement();
                $this->setDocToc($sStr);
                break;

            case WIKI_TOKEN_PARAM_PLUGIN:
                $sStr = $this->createParamPluginElement($sStr, $mMeta);
                break;

            case WIKI_TOKEN_SIMPLE_PLUGIN:
                $sStr = $this->createSimplePluginElement($sStr);
                break;

            case WIKI_TOKEN_HEADING:
                $sStr = $this->createHeadingElement($sStr, $mMeta);
                break;

            case WIKI_TOKEN_LIST:
                $sStr = $this->createList($sStr);
                break;

            case WIKI_TOKEN_SIMPLE_TABLE:
                $sStr = $this->createSimpleTableElement($sStr);
                break;

            case WIKI_TOKEN_PARAM_TABLE:
                $sStr = $this->createParamTableElement($sStr, $mMeta);
                break;

            case WIKI_TOKEN_HR:
                $sStr = $this->createRuleElement();
                break;

            case WIKI_TOKEN_QUOTE:
                $sStr = $this->createQuoteElement($sStr);
                break;

            case WIKI_TOKEN_VAR:
                $sStr = $this->createVarElement($sStr, $mMeta);
                break;

            case WIKI_TOKEN_BREAK:
                $sStr = $this->createBreakElement();
                break;

            case WIKI_TOKEN_SUB:
                $sStr = $this->createSubElement($sStr);
                break;

            case WIKI_TOKEN_SUP:
                $sStr = $this->createSupElement($sStr);
                break;
        }

        return $sStr;
    }

    // --- Element creators -----------------------------------------------

    /**
     * Create a <noop>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createNoopElement(&$sStr) {
        return '<noop>'.$sStr.'</noop>';
    }

    // --------------------------------------------------------------------

    /**
     * Create a <rem>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createRemarkElement(&$sStr) {
        return '<rem>'.$sStr.'</rem>';
    }

    // --------------------------------------------------------------------

    /**
     * Create a <pre>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createPreElement(&$sStr) {
        return "<pre>\r".str_replace("\n", "\r", $sStr)."\r</pre>";
    }

    // --------------------------------------------------------------------

    /**
     * Create a <code>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createCodeElement(&$sStr) {
        return "<code>\r".str_replace("\n", "\r", $sStr)."\r</code>";
    }

    // --------------------------------------------------------------------

    /**
     * Create a <posting>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createPostingElement(&$sStr) {
        return "<posting>\r".str_replace("\n", "\r", $sStr)."\r</posting>";
    }

    // --------------------------------------------------------------------

    /**
     * Create a simple <plugin>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createSimplePluginElement(&$sStr) {
        return '<plugin name="'.$sStr.'"/>';
    }

    // --------------------------------------------------------------------

    /**
     * Create a parameterized <plugin>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createParamPluginElement(&$sStr, &$sAttr) {
        if (trim($sAttr) == '') {
            return $this->createSimplePluginElement($sStr);
        }
        return '<plugin name="'.$sStr.'" '.trim(unescape($sAttr)).'/>';
    }

    // --------------------------------------------------------------------

    /**
     * Create a <h>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createHeadingElement(&$sStr, &$sLevel) {
        return '<h'.$sLevel.'>'.$sStr.'</h'.$sLevel.'>';
    }

    // --------------------------------------------------------------------

    /**
     * Create a <link>-element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createLinkElement($sStr) {
        $aStr = explode(')(', $sStr);

        $sStr =  '<link strref="';
        $sStr .=   str_replace('|','¦',trim($this->restoreTokens($aStr[0])));
        $sStr .= '">';
        $sStr .=   trim($this->restoreTokens($aStr[0]));
        $sStr .= '</link>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Create uri element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createUriElement(&$sStr) {
        $sStr = '<uri strref="'.$sStr.'"/>';
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Create toc element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createTocElement() {
        // Setting $i = 0 leads to undesired repetition of the first
        // "title" element in a bullet list

        // The default behaviour of <toc> is not to display the first
        // level
        $i = 1;
        $sStr = '';

        if (sizeof($this->aToc) > 0) {
            $sStr = $this->buildToc($i, $this->aToc[$i]['DEPTH']);
            if ($sStr != '') {
                $sStr = '<ul>' . $sStr . '</ul>';
            }
        }

        return '<toc>'.$this->restoreTokens($sStr).'</toc>';
    }

    // --------------------------------------------------------------------

    /**
     * Create rule element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createRuleElement() {
        return '<hr/>';
    }

    // --------------------------------------------------------------------

    /**
     * Create simple table element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createSimpleTableElement(&$sStr) {
        return '<table>'
                  .$this->buildTable($sStr)
              .'</table>';
    }

    // --------------------------------------------------------------------

    /**
     * Create param table element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createParamTableElement(&$sStr, &$sAttr) {
        if (trim($sAttr) == '') {
            return $this->createSimpleTableElement($sStr);
        }
        return '<table '.trim(unescape($sAttr)).'>'
                  .$this->buildTable($sStr)
              .'</table>';
    }

    // --------------------------------------------------------------------

    /**
     * Create quote element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createQuoteElement(&$sStr) {
        return '<q>'.$this->invokeParagraphs($sStr).'</q>';
    }

    // --------------------------------------------------------------------

    /**
     * Create var element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createVarElement(&$sStr, &$sName) {
        return '<var name="'.$sName.'"/>';
    }

    // --------------------------------------------------------------------

    /**
     * Create break element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createBreakElement() {
        return '<br/>';
    }

    // --------------------------------------------------------------------

    /**
     * Create sub element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function createSubElement(&$sStr) {
        return '<sub>'.$sStr.'</sub>';
    }

    // --------------------------------------------------------------------

    /**
     * Create sup element
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function createSupElement(&$sStr) {
        return '<sup>'.$sStr.'</sup>';
    }

    // --- Element creator helpers ----------------------------------------

    /**
     * Generate "toc" (Document Table of Contents)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function buildToc(&$i, &$nDepth) {
        $sStr = '';

        // Iterate though all toc-entries
        while (isset($this->aToc[$i]['DEPTH'])
               && $this->aToc[$i]['DEPTH'] == $nDepth) {

            $sAlias = preg_replace(
                        '=<link[^>]*>=US',
                        '',
                        $this->aToc[$i]['TEXT']
                      );

            // replace link tokens
            preg_match_all(
                '=\r?\t([0-9]+)\t=US',
                $sAlias,
                $aMatchesAll,
                PREG_SET_ORDER
            );

            foreach($aMatchesAll as $aMatches) {
                if ($this->aToken[$aMatches[1]]['TYPE'] == WIKI_TOKEN_LINK) {
                    $sLinkElement = $this->createLinkElement(
                        $this->aToken[$aMatches[1]]['CONTENT']
                    );

                    preg_match(
                        '=<link\s+href\="([^>]+)">([^<]*)</link>=US',
                        $sLinkElement,
                        $aLinkMatches
                    );

                    $sAlias = !empty($aLinkMatches[2])
                            ? str_replace(
                                $aMatches[0],
                                $aLinkMatches[2],
                                $sAlias
                            )
                            : str_replace(
                                $aMatches[0],
                                $aLinkMatches[1],
                                $sAlias
                            );
                }
            }

            $sStr .= '<li>';
            $sStr .=    '<link topicref="'.($i+1).'">'.$sAlias.'</link>';

            $i++;

            // Recurse (indent) if toc-entry is nested
            if (isset($this->aToc[$i]['DEPTH'])) {

                if ($this->aToc[$i]['DEPTH'] > $nDepth) {
                    $sStr .= '<ul>';
                    $sStr .=   $this->buildToc($i, $this->aToc[$i]['DEPTH']);
                    $sStr .= '</ul>';
                }
            }

            $sStr .= '</li>';
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Catch paragraphs
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function invokeParagraphs(&$sStr) {
        // Remove multiple newlines
        $sStr = preg_replace('=\n+=', "\n", $sStr);

        // Insert paragraphs
        $sStr = preg_replace('=\n([^\r]+)$=Um', '<p>\1</p>', $sStr);

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * All strings passed to this method MUST NOT contain any "<" or ">".
     * Treat this string with "htmlentities()" or a similar function first.
     *
     * @access  public
     * @param   string  The wiki source string
     * @return  string  Wiki XML
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function parse($sStr) {

        // {{{ DEBUG }}}
        Logger::info('Start parsing wiki document.');

        // ---

        $Registry = Registry::getInstance();

        // Init reference collection
        $this->RefNodes = new Vector;

        $sStr = trim(escape($sStr));

        // WARNING: Be VERY VERY careful! The order of these processing
        // steps is EXTREMELY important. Change only if you know what
        // you are doing! AND then: test it, test it and test it again.

        // Replace possible tabulators
        $sStr = str_replace("\t", '    ', $sStr);

        // Replace possible \r\n or \n\r with \n
        $sStr = str_replace("\r\n", "\n", $sStr);
        $sStr = str_replace("\n\r", "\n", $sStr);

        // Let the string always start and end with a newline (\n).
        // This makes pattern matching much easier.
        $sStr = "\n" . $sStr . "\n";

        // --- Tokenizing and preparing -----------------------------------

        // After this step all <noop></noop> are tokenized for further
        // processing.
        $sStr = $this->processNoOperation($sStr);

        // After this step all <rem></rem> are tokenized for further
        // processing.
        $sStr = $this->processRemark($sStr);

        // After this step all <pre></pre> are tokenized for further
        // processing.
        $sStr = $this->processPre($sStr);

        // After this step all <code></code> are tokenized for further
        // processing.
        $sStr = $this->processCode($sStr);

        // After this step all <posting></posting> are tokenized for further
        // processing.
        $sStr = $this->processPosting($sStr);

        // After this step all parameterized <plugin ... param> are
        // tokenized for further processing.
        $sStr = $this->processParamPlugin($sStr);

        // After this step all simple <plugin ...> are tokenized for further
        // processing.
        $sStr = $this->processSimplePlugin($sStr);

        // After this step all bracket-links ((foo)), ((foo)(bar)) are
        // tokenized for further processing.
        $sStr = $this->processLink($sStr);

        // After this step all clickable URIs (http:// etc.) are tokenized
        // for further processing.
        $sStr = $this->processClickable($sStr);

        // After this step all WikiWords are tokenized for further processing.
        if ($Registry->get('RUNTIME_WIKIWORDS')) {
            $sStr = $this->processWikiWord($sStr);
        }

        // After this step all variables are tokenized for further
        // processing.
        $sStr = $this->processVariable($sStr);

        // After this step all <toc ...> are tokenized for further
        // processing.
        $sStr = $this->processToc($sStr);

        // After this step all emphasis markups (bold, italic, ...) are
        // converted. These markups are not tokenized.
        $sStr = $this->processEmphasis($sStr);

        // After this step all headings (+, ++, +++ -> H1, H2, H3 etc.) are
        // tokenized for further processing.
        $sStr = $this->processHeading($sStr);

        // After this step all horizontal rules (---) are tokenized for
        // further processing.
        $sStr = $this->processHorizontalRule($sStr);

        // After this step all breaks (<br>) are tokenized for further
        // processing.
        $sStr = $this->processBreak($sStr);

        // After this step all subs (<sub>) are tokenized for further
        // processing.
        $sStr = $this->processSub($sStr);

        // After this step all sups (<sup>) are tokenized for further
        // processing.
        $sStr = $this->processSup($sStr);

        // After this step lists are tokenized for further processing.
        $sStr = $this->processList($sStr);

        // After this step all parameterized <table></table> are tokenized
        // for further processing.
        $sStr = $this->processParamTable($sStr);

        // After this step all simple <table></table> are tokenized for
        // further processing.
        $sStr = $this->processSimpleTable($sStr);

        // After this step all (block)-quotes are tokenized for further
        // processing.
        $sStr = $this->processQuote($sStr);

        // ----------------------------------------------------------------

        // Insert paragraphs
        $sStr = $this->invokeParagraphs($sStr);

        // Restore references (tokens)
        $sStr = $this->restoreTokens($sStr);

        // <pre> or <code> escaped the paragraph invocation by replacing
        // their newline delimiters to \r.
        // Now we replace the delimiters back into \n again.
        $sStr = str_replace("\r", "\n", $sStr);

        // {{{ DEBUG }}}
        Logger::info('Finished parsing wiki document.');

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Treat NOOP (no operation)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processNoOperation(&$sStr) {

        // Treat <noop> ... </noop>
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=&lt;noop&gt;(.+)&lt;/noop&gt;=Usi',
            array(&$this, 'tokenizeNoOperation'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize no operation
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeNoOperation(&$aMatches) {
       return $this->tokenize($aMatches[1], WIKI_TOKEN_NOOP);
    }

    // --------------------------------------------------------------------

    /**
     * Treat REMARKS
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processRemark(&$sStr) {

        // Treat <rem> ... </rem>
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=&lt;rem&gt;(.+)&lt;/rem&gt;=Usi',
            array(&$this, 'tokenizeRemark'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize remark
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeRemark(&$aMatches) {
       return $this->tokenize($aMatches[1], WIKI_TOKEN_REM);
    }

    // --------------------------------------------------------------------

    /**
     * Treat preformatted PRE
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processPre(&$sStr) {

        // Treat <pre> ... </pre>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;pre&gt;\n(.+)\n&lt;/pre&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizePre'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize pre
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizePre(&$aMatches) {
        return $this->tokenize(
                  $aMatches[1], WIKI_TOKEN_PRE
               )
               .$aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Treat preformatted CODE
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processCode(&$sStr) {

        // Treat <code> ... </code>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;code&gt;\n(.+)\n&lt;/code&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizeCode'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize code
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeCode(&$aMatches) {
       return $this->tokenize(
                  $aMatches[1],
                  WIKI_TOKEN_CODE
              )
              .$aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Treat POSTING
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processPosting(&$sStr) {

        // Treat <posting> ... </posting>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;posting&gt;\n(.+)\n&lt;/posting&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizePosting'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize posting
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizePosting(&$aMatches) {
       return $this->tokenize(
                  $aMatches[1], WIKI_TOKEN_POSTING
              )
              .$aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * ((foo)), ((foo)(bar))
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processLink(&$sStr) {

        // Treat square-bracket-links ((foo)), ((foo)(bar))
        // These tags can not be nested.
        $sPattern = '=\(\(([^\(\)]+|[^\(\)]+\([^\(\)]+\)[^\(\)]*|' .
            '[^\(\)]+\)\([^\(\)]+)\)\)=U';

        $sStr = preg_replace_callback(
            $sPattern,
            array(&$this, 'tokenizeLink'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize link
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeLink(&$aMatches) {
        return $this->tokenize($aMatches[1], WIKI_TOKEN_LINK);
    }

    // --------------------------------------------------------------------

    /**
     * Convert WikiWords
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processWikiWord(&$sStr) {

        // Treat WikiWordLinks
        $sStr = preg_replace_callback(
            '=([ \n\[\{\']|&quot;)([A-Z][a-z]+([A-Z][a-z]+)+)=',
            array(&$this, 'tokenizeWikiWord'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize wiki word
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeWikiWord(&$aMatches) {
        $sLink = preg_replace('=([A-Z])=', ' \1', $aMatches[2]);
        return $aMatches[1].$this->tokenize(ltrim($sLink), WIKI_TOKEN_LINK);
    }

    // --------------------------------------------------------------------

    /**
     * Automatically clickable URIs
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processClickable(&$sStr) {

        // Treat clickable URIs (http:, mailto: etc.)
        $sStr = preg_replace_callback(
            '=  (http://|https://|ftp://|mailto:|news:)
                (\S+)
                (\*\s|\=\s|&quot;|&lt;|&gt;|<|>|\(|\)|\s|$)
             =Usmix',
            array(&$this, 'tokenizeClickable'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize clickable
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeClickable(&$aMatches) {
       return $this->tokenize(
                  $aMatches[1] . $aMatches[2], WIKI_TOKEN_URI
              )
              .$aMatches[3];
    }

    // --------------------------------------------------------------------

    /**
     * <toc ...>
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processToc(&$sStr) {
        // Treat <toc ...>
        $sStr = preg_replace_callback(
            '=\n&lt;toc&gt;=Usi',
            array(&$this, 'tokenizeToc'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize toc
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeToc(&$aMatches) {
        return $this->tokenize(false, WIKI_TOKEN_TOC);
    }

    // --------------------------------------------------------------------

    /**
     * <plugin ...>
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processSimplePlugin(&$sStr) {

        // Treat <plugin ...>
        $sStr = preg_replace_callback(
            '=&lt;plugin +([a-z0-9_.]+)&gt;=Usi',
            array(&$this, 'tokenizeSimplePlugin'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize simple plugin
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeSimplePlugin(&$aMatches) {
        return $this->tokenize($aMatches[1], WIKI_TOKEN_SIMPLE_PLUGIN);
    }

    // --------------------------------------------------------------------

    /**
     * <plugin ... parameters>
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processParamPlugin(&$sStr) {

        // Treat <plugin ... parameters>
        $sStr = preg_replace_callback(
            '=&lt;plugin +([a-z0-9_.]+) +(.*)&gt;=Umsi',
            array(&$this, 'tokenizeParamPlugin'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize param plugin
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeParamPlugin(&$aMatches) {
        return $this->tokenize(
                  $aMatches[1], WIKI_TOKEN_PARAM_PLUGIN, $aMatches[2]
               );
    }

    // --------------------------------------------------------------------

    /**
     * Emphasis: bold, italic, monospaced ...
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processEmphasis(&$sStr) {

        // These are the possible start delimiters for bold, italic
        // and monospace emphasis markups
        $sStart = '(\[';

        // These are the possible end delimiters for bold, italic
        // and monospace emphasis markups
        $sEnd = '\s,.;:!?()\[\]_-';

        // Italic first, because we do not want to match "</foo>/bar"
        $sStr = preg_replace(
            '~  ([^<])?
                (\s|=|\*|&lt;br&gt;|['.$sStart.'])
                (/)
                ([^ \n/](\S?|.*(\S|\t)))
                \3
                (?=['.$sEnd.']|\*|&lt;br&gt;|=)             # do not eat up
                                                            # trailing chars
            ~Umix',
            '\1\2<em>\4</em>\7',
            $sStr
        );

        // Bold
        $sStr = preg_replace(
            '~  (\s|=|<em>|&lt;br&gt;|['.$sStart.'])
                (\*)
                ([^ \n\*](\S?|.*(\S|\t)))
                \2
                (?=['.$sEnd.']|=|&lt;br&gt;|</em>)          # do not eat up
                                                            # trailing chars
            ~Umix',
            '\1<strong>\3</strong>\6',
            $sStr
        );

        // Mono
        $sStr = preg_replace(
            '~  (\s|<em>|<strong>|&lt;br&gt;|['.$sStart.'])
                (=)
                ([^ \n=](\S?|.*(\S|\t)))
                \2
                (?=['.$sEnd.']|&lt;br&gt;|</em>|</strong>)  # do not eat up
                                                            # trailing chars
            ~Umix',
            '\1<tt>\3</tt>\6',
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * (Again) treat %VARIABLES%, that may occur in the document.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processVariable(&$sStr) {
        $sStr = preg_replace_callback(
                '=%([A-Z0-9_]+)%=U',
                array(&$this, 'tokenizeVariable'),
                $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize variable
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeVariable(&$aMatches) {
        return $this->tokenize(false, WIKI_TOKEN_VAR, $aMatches[1]);
    }

    // --------------------------------------------------------------------

    /**
     * Headings (+ -> H1, ++ -> H2, etc.)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processHeading(&$sStr) {

        // Treat headings (+, ++, +++ etc);
        // Each tag will be recognized at the begin of a line only!
        $sStr = preg_replace_callback(
            '=^(\+{1,6}) ([^\r\n]+)=m',
            array(&$this, 'tokenizeHeading'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize heading
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeHeading(&$aMatches) {

        $nDepth = strlen($aMatches[1]);

        // Remember headings for <toc> - table of contents
        $i = sizeof($this->aToc);
        $this->aToc[$i]['TEXT']  = $aMatches[2];
        $this->aToc[$i]['DEPTH'] = $nDepth;

        return $this->tokenize($aMatches[2], WIKI_TOKEN_HEADING, $nDepth);
    }

    // --------------------------------------------------------------------

    /**
     * Headings (+ -> H1, ++ -> H2, etc.)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processHorizontalRule(&$sStr) {

        // Treat horizontal rules (---)
        $sStr = preg_replace_callback(
            '=^-{3,}(\s|$)=m',
            array(&$this, 'tokenizeHorizontalRule'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize horizontal rule
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeHorizontalRule(&$aMatches) {
        return $this->tokenize(false, WIKI_TOKEN_HR) . $aMatches[1];
    }

    // --------------------------------------------------------------------

    /**
     * Breaks (<br>)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processBreak(&$sStr) {

        // Treat a break
        $sStr = preg_replace_callback(
            '=&lt;br&gt;=Ui',
            array(&$this, 'tokenizeBreak'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize break
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeBreak(&$aMatches) {
        return $this->tokenize(false, WIKI_TOKEN_BREAK);
    }

    // --------------------------------------------------------------------

    /**
     * Subs (<sub>)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function processSub(&$sStr) {

        // Treat a <sub>
        $sStr = preg_replace_callback(
            '=&lt;sub&gt;(.*)&lt;/sub&gt;=Ui',
            array(&$this, 'tokenizeSub'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize sub
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function tokenizeSub(&$aMatches) {
        return $this->tokenize($aMatches[1], WIKI_TOKEN_SUB);
    }

    // --------------------------------------------------------------------

    /**
     * Sups (<sup>)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function processSup(&$sStr) {

        // Treat a <sup>
        $sStr = preg_replace_callback(
            '=&lt;sup&gt;(.*)&lt;/sup&gt;=Ui',
            array(&$this, 'tokenizeSup'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize sup
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     */
    protected function tokenizeSup(&$aMatches) {
        return $this->tokenize($aMatches[1], WIKI_TOKEN_SUP);
    }

    // --------------------------------------------------------------------

    /**
     * Ordered and unordered bullet lists (Asterisk *, Hash #)
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processList(&$sStr) {

        // Treat lists
        $sStr = preg_replace_callback(
            '=\n((\*|#) .*\n)(?! {0,}(\*|#))=Us',
            array(&$this, 'tokenizeList'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize list
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeList(&$aMatches) {
        return "\n".$this->tokenize($aMatches[1], WIKI_TOKEN_LIST)."\n";
    }

    // --------------------------------------------------------------------

    /**
     * Create list
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createList(&$sStr) {

        // Fix multiple bullets at the beginning of the line.
        // E.g. fix sick things like this to make it intuitive:
        //
        // * Item 1     <-->   * Item 1
        //  * Item 2    <-->    * Item 2
        // ** Item 3    <-->    * Item 3
        // *** Item 4   <-->     * Item 4
        // **** Item 5  <-->      * Item 5
        // ** Item 6    <-->    * Item 6

        $aArr = explode("\n", $sStr);

        for ($i=0, $n=sizeof($aArr); $i<$n; $i++) {
            $aArr[$i] = preg_replace_callback(
                          '=^((\*|#)*)(\2) (.*)$=',
                          array($this, 'createListMultiBulletHelper'),
                          $aArr[$i]
                        );
        }

        $sStr = join("\n", $aArr);

        preg_match_all(
            '=^( {0,})(\*|#)+ (.*)$=Ums',
            $sStr,
            $aList
        );

        $this->aList = array();

        for ($i=0; $i<sizeof($aList[1]); $i++) {
            $this->aList[$i]['TEXT']  = $aList[3][$i];
            $this->aList[$i]['DEPTH'] = strspn($aList[1][$i], ' ');
            $this->aList[$i]['TYPE']  = $aList[2][$i];
        }

        $i = 0;
        $nDepth = $this->aList[$i]['DEPTH'];

        // Determine the type of the first list element:
        // Ordered or unordered?
        $sListType = ($aList[2][0] == '*') ? 'ul' : 'ol';

        return '<list>'
                  .'<'.$sListType.'>'
                      .$this->buildList($i, $nDepth)
                  .'</'.$sListType.'>'
              .'</list>';
    }

    private function createListMultiBulletHelper(&$aMatches) {
        return str_repeat(' ', strlen($aMatches[1]))
                .$aMatches[3].' '.$aMatches[4];
    }

    // --------------------------------------------------------------------

    /**
     * Build list
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function buildList(&$i, &$nDepth) {
        $sStr = '';

        // Iterate though all list-entries
        while (isset($this->aList[$i]['DEPTH'])
               && $this->aList[$i]['DEPTH'] == $nDepth) {

            $sStr .= '<li>';
            $sStr .=    $this->aList[$i]['TEXT'];

            $i++;

            // Recurse (indent) if list-entry is nested
            if (isset($this->aList[$i]['DEPTH'])) {

                if ($this->aList[$i]['DEPTH'] > $nDepth) {

                    // Correct items that are intended too deep
                    $this->aList[$i]['DEPTH'] = $nDepth + 1;

                    $sListType = $this->aList[$i]['TYPE'] == '*'
                                  ? 'ul'
                                  : 'ol';

                    $sStr .=  '<'.$sListType.'>';
                    $sStr .=    $this->buildList(
                                    $i,
                                    $this->aList[$i]['DEPTH']
                                );
                    $sStr .=  '</'.$sListType.'>';
                }
            }

            $sStr .= '</li>';
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Treat TABLE
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processSimpleTable(&$sStr) {

        // Treat <table> ... </table>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;table&gt;\n(.+)\n&lt;/table&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizeTable'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize table
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeTable(&$aMatches) {
        return "\n"
                  .$this->tokenize($aMatches[1], WIKI_TOKEN_SIMPLE_TABLE)
               ."\n";
    }

    // --------------------------------------------------------------------

    /**
     * Process param table
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processParamTable(&$sStr) {

        // Treat <table> ... </table>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;table +([^>]*)&gt;\n(.+)\n&lt;/table&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizeParamTable'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize param table
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeParamTable(&$aMatches) {
        return "\n"
                  .$this->tokenize(
                      $aMatches[2], WIKI_TOKEN_PARAM_TABLE, $aMatches[1]
                  )
                ."\n";
    }

    // --------------------------------------------------------------------

    /**
     * &build table
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &buildTable(&$sStr) {

        $aRows = explode("\n", $sStr);
        $aTable = array();

        for ($i=0, $n=sizeof($aRows); $i<$n; $i++) {

            // Row MUST start with pipe
            if (strlen(trim($aRows[$i])) == 0 || $aRows[$i]{0} != '|') {
                return '<tr valign="top"><td colspan="1">'.$sStr.'</td></tr>';
            }

            $aRows[$i] = substr($aRows[$i], 1);

            // Row MAY have one (or more) trailing pipes
            if (substr($aRows[$i], -1) == '|') {
                $aRows[$i] = substr($aRows[$i], 0, -1);
            }

            // Mark and remember all <noop>s in table row
            $this->aTblRowNoop = array();
            $aRows[$i] = preg_replace_callback(
                            '#(<noop>.*</noop>)#U',
                            array(&$this, 'extractTableRowNoops'),
                            $aRows[$i]
                         );

            // Exchange column delemiter
            $aRows[$i] = str_replace('|', "\x02", $aRows[$i]);

            // Restore <noop>s in table row
            for ($j=0, $m=sizeof($this->aTblRowNoop); $j<$m; $j++) {
                $aRows[$i] = preg_replace(
                                "#\x01#",
                                $this->aTblRowNoop[$j],
                                $aRows[$i],
                                1
                             );
            }

            // Divide columns
            $aCols = explode("\x02", $aRows[$i]);

            $nSpan = 0;
            $sRow = '';

            for ($j=0, $m=sizeof($aCols); $j<$m; $j++) {

                if (strlen($aCols[$j]) == 0) {
                    $nSpan++;
                    if ($j < $m-1) {
                        continue;
                    }
                }

                if ($nSpan) {
                    $sRow .= '<td colspan="'.($nSpan+1).'">';
                    $sRow .=   $aCols[$j];
                    $sRow .= '</td>';
                    $nSpan = 0;
                    continue;
                }

                $sRow .= '<td colspan="1">'.$aCols[$j].'</td>';
            }

            $aTable[] = '<tr valign="top">'
                          .$sRow
                        .'</tr>';
        }

        $sTable = join('', $aTable);
        return $sTable;
    }

    // --------------------------------------------------------------------

    private function extractTableRowNoops(&$aMatches) {
        $this->aTblRowNoop[] = $aMatches[1];
        return "\x01";
    }

    // --------------------------------------------------------------------

    /**
     * Treat (block-)quote
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function processQuote(&$sStr) {

        // Treat <q> ... </q>
        // Each tag will be recognized at the begin of a line only!
        // This tag can not be nested.
        $sStr = preg_replace_callback(
            '=^&lt;q&gt;(.+)\n+&lt;/q&gt;(\s|$)=Umsi',
            array(&$this, 'tokenizeQuote'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Tokenize quote
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function tokenizeQuote(&$aMatches) {
        return $this->tokenize($aMatches[1], WIKI_TOKEN_QUOTE)
               .$aMatches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Collect referenced nodes to where the document is linking to
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function addReferencedNode($Obj) {
        $this->RefNodes->add($Obj);
    }

    // --------------------------------------------------------------------

    /**
     * Get referenced nodes
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getReferencedNodes() {
        return $this->RefNodes;
    }

} // of class

/*
    Prospero:  That cross you wear around your neck; is it only a decoration,
               or are you a true Christian believer?

    Francesca: Yes, I believe - truly.

    Prospero:  Then I want you to remove it at once! - and never to wear it
               within this castle again! Do you know how a falcon is trained
               my dear? Her eyes are sown shut. Blinded temporarily she
               suffers the whims of her God patiently, until her will is
               submerged and she learns to serve - as your God taught and
               blinded you with crosses.

    Francesca: You had me take off my cross because it offended ...

    Prospero:  It offended no-one. No - it simply appears to me to be
               discourteous to ... to wear the symbol of a deity long dead.
               My ancestors tried to find it. And to open the door that
               seperates us from our Creator.

    Francesca: But you need no doors to find God. If you believe ...

    Prospero:  Believe?! If you believe you are gullible. Can you look
               around this world and believe in the goodness of a god who
               rules it? Famine, Pestilence, War, Disease and Death!
               They rule this world.

    Francesca: There is also love and life and hope.

    Prospero:  Very little hope I assure you. No. If a god of love and life
               ever did exist ... he is long since dead. Someone ...
               something rules in his place.
*/

?>
