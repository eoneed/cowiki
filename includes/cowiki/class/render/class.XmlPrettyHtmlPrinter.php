<?php

/**
 *
 * $Id: class.XmlPrettyHtmlPrinter.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     render
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
 * coWiki - XML pretty HTML printer class
 *
 * @package     render
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class XmlPrettyHtmlPrinter extends Object {
    protected static
        $Instance = null;

    private
        $rParser     = null,
        $nIndent     = null,
        $sCDataColor = '#000000',
        $sWordWrap   = null;

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
            self::$Instance = new XmlPrettyHtmlPrinter;
        }
        return self::$Instance;
    }

    /**
     * Init
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function __construct() {}

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
     */
    public function init() {
        if (!function_exists('xml_parser_create')) {
            return false;
        }

        $this->rParser = @xml_parser_create();

        if (!$this->rParser) {
            return false;
        }

        xml_parser_set_option($this->rParser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->rParser, XML_OPTION_SKIP_WHITE, 0);

        return true;
    }

    /**
     * Get pretty
     *
     * @access  public
     * @param   string
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nWordWrap"
     */
    public function getPretty($sStr, $nWordWrap = 60) {

        // Check if init() was successful
        if (!$this->rParser) {
            return false;
        }

        $this->sCDataColor = RuntimeContext::getInstance()->
                                getRegistry()->get('COLOR_CODE_HTML');
        $this->nWordWrap = $nWordWrap;

        xml_parse_into_struct($this->rParser, $sStr, $aVal, $aIndex);

        $sStr = '';

        for ($i=0, $n=sizeof($aVal); $i<$n; $i++) {
            $this->nIndent = $aVal[$i]['level'] - 1;

            switch ($aVal[$i]['type']) {
                case 'open':
                    $sStr .= $this->_getIndent();

                    $sStr .= '&lt;';
                    $sStr .=    $aVal[$i]['tag'];

                    if (isset($aVal[$i]['attributes'])) {
                        $sStr .=  $this->_getAttr($aVal[$i]['attributes']);
                    }

                    $sStr .= '&gt;';
                    $sStr .= "\n";
                    if (isset($aVal[$i]['value'])) {
                       $sStr .= $this->_formatCData($aVal[$i]['value']);
                       $sStr .= "\n";
                    }

                    break;

                case 'close':
                    $sStr .= $this->_getIndent();

                    $sStr .= '&lt;/';
                    $sStr .=    $aVal[$i]['tag'];
                    $sStr .= '&gt;';
                    $sStr .= "\n";
                    break;

                case 'cdata':
                    if (trim($aVal[$i]['value']) == '') {
                        break;
                    }

                    $sStr .= $this->_formatCData($aVal[$i]['value']);
                    $sStr .= "\n";
                    break;

                case 'complete':
                    $sStr .= $this->_getIndent();

                    if (isset($aVal[$i]['value'])) {
                        $sStr .= '&lt;';
                        $sStr .=    $aVal[$i]['tag'];

                        if (isset($aVal[$i]['attributes'])) {
                            $sStr .= $this->_getAttr($aVal[$i]['attributes']);
                        }

                        $sStr .= '&gt;';
                        $sStr .= "\n";

                        $sStr .= $this->_formatCData($aVal[$i]['value']);
                        $sStr .= "\n";

                        $sStr .=    $this->_getIndent();

                        $sStr .= '&lt;/';
                        $sStr .=    $aVal[$i]['tag'];
                        $sStr .= '&gt;';
                        $sStr .= "\n";

                    } else {
                        $sStr .= '&lt;';
                        $sStr .=    $aVal[$i]['tag'];

                        if (isset($aVal[$i]['attributes'])) {
                            $sStr .= $this->_getAttr($aVal[$i]['attributes']);
                        }

                        $sStr .= ' /&gt;';
                        $sStr .= "\n";
                    }
                    break;
            }
        }

        return $sStr;
    }

    /**
     * _get attr
     *
     * @access  private
     * @param   array
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    private function _getAttr($aAttr) {
        $sStr = '';

        foreach ($aAttr as $k => $v) {
            $sStr .= ' ' . $k . '="' . htmlentities(htmlentities($v)) . '"';
        }

        return $sStr;
    }

    /**
     * _get indent
     *
     * @access  private
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    private function _getIndent() {
        return str_repeat('  ', $this->nIndent);
    }

    /**
     * _format cdata
     *
     * @access  private
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    private function _formatCData($sStr) {
        $aArr = explode("\n", trim($sStr));
        $sStr = '';

        for ($i=0, $n=sizeof($aArr); $i<$n; $i++) {
            $sData = wordwrap(
                        $aArr[$i],
                        $this->nWordWrap,
                        "\n" . $this->_getIndent() . '  '
                     );
            $sStr .= $this->_getIndent(). '  '. $sData  ."\n";
        }

        $sStr = rtrim($sStr);

        if ($sStr == '') {
            return '';
        }

        $sNewStr =     '<font color="'.$this->sCDataColor.'">';
        $sNewStr .=       htmlentities(htmlentities($sStr));
        $sNewStr .=    '</font>';

        return $sNewStr;
    }

} // of class

?>
