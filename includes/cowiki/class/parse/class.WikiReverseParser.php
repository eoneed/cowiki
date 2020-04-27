<?php

/**
 *
 * $Id: class.WikiReverseParser.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Wiki reverse parser class
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class WikiReverseParser extends Object {
    protected static
        $Instance = null;

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
            self::$Instance = new WikiReverseParser;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Parse
     *
     * @access  protected
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function __construct() {}

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
     * @todo    [D11N]  Check return type
     */
    public function parse($sStr) {

        // Replace possible tabulators
        $sStr = str_replace("\t", '    ', $sStr);

        // Replace possible \r\n or \n\r with \n
        $sStr = str_replace("\r\n", "\n", $sStr);
        $sStr = str_replace("\n\r", "\n", $sStr);

        // Mark all newlines in <pre>, <code>, <posting> and <rem> as tabs
        $sStr = preg_replace_callback(
            '=(<pre>.*</pre>)=Usi',
            array(&$this, 'newlineToTab'),
            $sStr
        );
        $sStr = preg_replace_callback(
            '=(<code>.*</code>)=Usi',
            array(&$this, 'newlineToTab'),
            $sStr
        );
        $sStr = preg_replace_callback(
            '=(<posting>.*</posting>)=Usi',
            array(&$this, 'newlineToTab'),
            $sStr
        );
        $sStr = preg_replace_callback(
            '=(<rem>.*</rem>)=Usi',
            array(&$this, 'newlineToTab'),
            $sStr
        );

        // Remove all newlines
        $sStr = str_replace("\n", '', $sStr);
        // Restore newlines in <pre>s, <code>s and <posting>s
        $sStr = str_replace("\t", "\n", $sStr);

        // ---

        $sStr = $this->processToc($sStr);
        $sStr = $this->processNoop($sStr);
        $sStr = $this->processRemark($sStr);
        $sStr = $this->processPre($sStr);
        $sStr = $this->processCode($sStr);
        $sStr = $this->processPosting($sStr);
        $sStr = $this->processParagraph($sStr);
        $sStr = $this->processHeading($sStr);
        $sStr = $this->processEmphasis($sStr);
        $sStr = $this->processHorizontalRule($sStr);
        $sStr = $this->processVariable($sStr);
        $sStr = $this->processPlugin($sStr);
        $sStr = $this->processLink($sStr);
        $sStr = $this->processUri($sStr);
        $sStr = $this->processBreak($sStr);
        $sStr = $this->processSup($sStr);
        $sStr = $this->processSub($sStr);
        $sStr = $this->processList($sStr);
        $sStr = $this->processTable($sStr);
        $sStr = $this->processQuote($sStr);    // last one!

        return trim(unescape($sStr));
    }

    // --------------------------------------------------------------------

    /**
     * Newline to tab
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
     * @todo    [FIX]   THIS METHOD DO NOT COVER ALL POSSIBLE OCCURANCES OF DELIMITERS
     */
    protected function newlineToTab(&$aMatches) {
        return str_replace("\n", "\t", $aMatches[1]);
    }

    // --------------------------------------------------------------------

    // FIX: THIS METHOD DO NOT COVER ALL POSSIBLE OCCURANCES OF DELIMITERS
    // YET! THIS HAS TO BE CHECKED AND FIXED.

    /**
     * If a string reference contains delimiters, escape them with <noop>
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    protected function noopDelimiters(&$sStr) {

        // Escape leading delimiters
        if (substr($sStr, 0, 2) == '()') {
            $sStr = '&lt;noop&gt;()&lt;/noop&gt;'.substr($sStr, 2);
        } else if (substr($sStr, 0, 1) == '(') {
            $sStr = '&lt;noop&gt;(&lt;/noop&gt;'. substr($sStr, 1);
        }

        // Escape trailing delimiters
        if (substr($sStr, -2) == '()') {
            $sStr = substr($sStr, 0, -2).'&lt;noop&gt;()&lt;/noop&gt;';
        } else if (substr($sStr, -1) == ')') {
            $sStr = substr($sStr, 0, -1).'&lt;noop&gt;)&lt;/noop&gt;';
        }

        // Escape double closing
        $sStr = str_replace('))', '&lt;noop&gt;))&lt;/noop&gt;', $sStr);

        // Replace web/document delimiter
        return str_replace('¦', '|', $sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Process toc
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processToc(&$sStr) {
        $sStr = preg_replace(
            '=<toc>(.*)</toc>=Ums',
            "\n&lt;toc&gt;\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process noop
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processNoop(&$sStr) {
        $sStr = preg_replace(
            '=<noop>(.*)</noop>=Ums',
            "&lt;noop&gt;\\1&lt;/noop&gt;",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process remark
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processRemark(&$sStr) {
        $sStr = preg_replace(
            '=<rem>(.*)</rem>=Ums',
            "&lt;rem&gt;\\1&lt;/rem&gt;",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process pre
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processPre(&$sStr) {
        $sStr = preg_replace(
            '=<pre>(.*)</pre>=Ums',
            "\n&lt;pre&gt;\\1&lt;/pre&gt;\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process code
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processCode(&$sStr) {
        $sStr = preg_replace(
            '=<code>(.*)</code>=Ums',
            "\n&lt;code&gt;\\1&lt;/code&gt;\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process posting
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processPosting(&$sStr) {
        $sStr = preg_replace(
            '=<posting>(.*)</posting>=Ums',
            "\n&lt;posting&gt;\\1&lt;/posting&gt;\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process paragraph
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processParagraph(&$sStr) {
        $sStr = preg_replace(
            '=<p>(.*)</p>=Ums',
            "\n\\1\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process heading
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processHeading(&$sStr) {
        $sStr = preg_replace_callback(
            '=<h([1-6])>(.*)</h\1>=Ums',
            array(&$this, 'buildHeading'),
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build heading
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
    protected function buildHeading(&$aMatches) {
        return "\n"
               .str_repeat('+', $aMatches[1])
               .' '
               .$aMatches[2]
               ."\n";
    }

    // --------------------------------------------------------------------

    /**
     * Process emphasis
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processEmphasis(&$sStr) {
        $sStr = preg_replace(
            '=<em>(.*)</em>=Ums',
            '/\1/',
            $sStr
        );

        $sStr = preg_replace(
            '=<strong>(.*)</strong>=Ums',
            '*\1*',
            $sStr
        );

        $sStr = preg_replace(
            '=<tt>(.*)</tt>=Ums',
            '=\1=',
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process horizontal rule
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processHorizontalRule(&$sStr) {
        $sStr = preg_replace(
            '=<hr/>=Ums',
            "\n---\n",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process variable
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processVariable(&$sStr) {
        $sStr = preg_replace(
            '=<var name\="(.*)"/>=Ums',
            '%\1%',
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process link
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processLink(&$sStr) {
        $sStr = preg_replace_callback(
            '=<link strref\="(.*)">(.*)</link>=Ums',
            array(&$this, 'buildStrRefLink'),
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build str ref link
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
    protected function buildStrRefLink(&$aMatches) {
        if ($aMatches[1] == $aMatches[2] || trim($aMatches[2]) == '') {
            return '((' . $this->noopDelimiters($aMatches[1]) . '))';
        }

        return '((' . $this->noopDelimiters($aMatches[1]) . ')'
                .'(' . $aMatches[2] . '))';
    }

    // --------------------------------------------------------------------

    /**
     * Process uri
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processUri(&$sStr) {
        $sStr = preg_replace(
            '=<uri strref\="(.*)"/>=Ums',
            '\1',
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process break
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processBreak(&$sStr) {
        $sStr = preg_replace(
            '=<br/>=Ui',
            "&lt;br&gt;",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process plugin
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processPlugin(&$sStr) {
        $sStr = preg_replace_callback(
            '=<plugin name\="([^"]+)"(.*)/>=Ums',
            array(&$this, 'buildPlugin'),
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build plugin
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
    protected function buildPlugin(&$aMatches) {
        if ($aMatches[2] == '') {
            return '&lt;plugin '.$aMatches[1].'&gt;';
        } else {
            return '&lt;plugin '.$aMatches[1].' '.trim($aMatches[2]).'&gt;';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Process list
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processList(&$sStr) {
        $sStr = preg_replace_callback(
            '=<list>(.*)</list>=Ums',
            array(&$this, 'buildList'),
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build list
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
    protected function buildList(&$aMatches) {
        // Remove closing </li>
        $sStr = preg_replace('=</li>=i', '', $aMatches[1]);
        $this->nListDepth = 0;

        $sList = '';
        return "\n" . $this->buildMixedList($sStr, $sList);
    }

    // --------------------------------------------------------------------

    /**
     * Build mixed list
     *
     * @access  protected
     * @param   string
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function buildMixedList(&$sStr, &$sList) {
        static $aListType = array();

        $bMatch = preg_match('=<([^>]*)>([^<]*)(.*)=sS', $sStr, $aMatches);

        if ($bMatch) {

            if ($aMatches[1] == 'ul') {
                $sListTag = '*';
                $aListType[$this->nListDepth] = '*';
                $this->nListDepth++;
                $this->buildMixedList($aMatches[3], $sList);
            }

            if ($aMatches[1] == 'ol') {
                $aListType[$this->nListDepth] = '#';
                $this->nListDepth++;
                $this->buildMixedList($aMatches[3], $sList);
            }

            if ($aMatches[1] == 'li') {
                $sList .= str_repeat(' ',  $this->nListDepth - 1)
                          . $aListType[$this->nListDepth - 1] . ' '
                          . str_replace("\n", '', $aMatches[2]) . "\n";
                $this->buildMixedList($aMatches[3], $sList);
            }

            if ($aMatches[1] == '/ul') {
                $this->nListDepth--;
                $this->buildMixedList($aMatches[3], $sList);
            }

            if ($aMatches[1] == '/ol') {
                $this->nListDepth--;
                $this->buildMixedList($aMatches[3], $sList);
            }
        }

        return  $sList;
    }

    // --------------------------------------------------------------------

    /**
     * Process table
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processTable(&$sStr) {
        $sStr = preg_replace_callback(
            '=<table([^>]*)>(.*)</table>=Ums',
            array(&$this, 'buildTable'),
            $sStr
        );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build table
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
    protected function buildTable(&$aMatches) {
        $sStr = "\n&lt;table".$aMatches[1]."&gt;\n";

        // Isolate rows
        preg_match_all(
            '=<tr[^>]*>(.+)</tr>=USs',
            $aMatches[2],
            $aRows
        );

        // Treat cells
        for ($i=0, $n=sizeof($aRows[1]); $i<$n; $i++) {
            preg_match_all(
                '=<td colspan\="([0-9]+)">(.*)</td>=USs',
                $aRows[1][$i],
                $aCells
            );

            for ($j=0, $m=sizeof($aCells[1]); $j<$m; $j++) {
                $sStr .= str_repeat('|', $aCells[1][$j]);
                $sStr .= $aCells[2][$j];
            }

            $sStr .= "\n";
        }

        return $sStr . "&lt;/table&gt;\n";
    }

    // --------------------------------------------------------------------

    /**
     * Process sup
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    protected function processSup(&$sStr) {
        $sStr = preg_replace(
            '=<sup>(.*)</sup>=Ums',
            "&lt;sup&gt;\\1&lt;/sup&gt;",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process sub
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    protected function processSub(&$sStr) {
        $sStr = preg_replace(
            '=<sub>(.*)</sub>=Ums',
            "&lt;sub&gt;\\1&lt;/sub&gt;",
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Process quote
     *
     * @access  protected
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function processQuote(&$sStr) {
        $sStr = preg_replace_callback(
            '=<q>(.*)</q>=Ums',
            array(&$this, 'buildQuote'),
            $sStr
        );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Build quote
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
    protected function buildQuote(&$aMatches) {
        return "\n&lt;q&gt;\n".trim($aMatches[1])."\n&lt;/q&gt;\n";
    }

} // of class

?>
