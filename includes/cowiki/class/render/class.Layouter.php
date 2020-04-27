<?php

/**
 *
 * $Id: class.Layouter.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Layouter class
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
class Layouter extends Object {
    protected static
        $Instance = null;

    private
        $aAttr = array(),
        $aAttrSet = array(),
        $aCounter = array();

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
            self::$Instance = new Layouter;
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
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function init() {
        $this->aAttr    = array();
        $this->aCounter = array();

        $this->aAttr['tablepadding'][0] = '0';
        $this->aAttr['tablespacing'][0] = '0';
        $this->aAttr['tableborder'] [0] = '0';
        $this->aAttr['tablewidth']  [0] = '';
        $this->aAttr['tableclass']  [0] = '';
        $this->aAttr['tablestyle']  [0] = '';

        $this->aAttr['trclass']     [0] = '';
        $this->aAttr['trstyle']     [0] = '';

        $this->aAttr['thclass']     [0] = '';
        $this->aAttr['thstyle']     [0] = '';

        $this->aAttr['tdwidth']     [0] = '';
        $this->aAttr['tdclass']     [0] = '';
        $this->aAttr['tdstyle']     [0] = '';

        $this->aAttr['aclass']      [0] = '';
        $this->aAttr['astyle']      [0] = '';

        foreach($this->aAttr as $sKey => $sValue) {
            $this->aCounter[$sKey] = 0;
            $this->aAttrSet[$sKey] = false;
        }
    }

    /**
     * Add attribute
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function addAttribute($sKey, $sValue) {
        if (!isset($this->aAttr[$sKey])) {
            return;
        }

        $aValue = explode(',', $sValue);
        for ($i=0, $n=sizeof($aValue); $i<$n; $i++) {
            $this->aAttr[$sKey][$i] = trim($aValue[$i]);
        }

        // Attribute has been set
        $this->aAttrSet[$sKey] = true;
    }

    /**
     * A plugin may set its default attributes itself, if nothing has been
     * passed as parameter before
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function addDefaultAttribute($sKey, $sValue) {
        if ($this->aAttrSet[$sKey]) {
            return true;
        }

        $this->addAttribute($sKey, $sValue);
    }

    /**
     * Get attr by tag
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
    public function getAttrByTag($sTag) {
        $sTag = strtolower($sTag);

        switch ($sTag) {
            case 'table':
                $sStr =  $this->_getTableWidth();
                $sStr .= $this->_getTablePadding();
                $sStr .= $this->_getTableSpacing();
                $sStr .= $this->_getTableBorder();
                $sStr .= $this->_getClass($sTag);
                $sStr .= $this->_getStyle($sTag);
                return $sStr;
                break;

            case 'tr':
                $sStr =  $this->_getClass($sTag);
                $sStr .= $this->_getStyle($sTag);
                return $sStr;
                break;

            case 'th':
                $sStr =  $this->_getClass($sTag);
                $sStr .= $this->_getStyle($sTag);
                return $sStr;
                break;

            case 'td':
                $sStr =  $this->_getTdWidth();
                $sStr .= $this->_getClass($sTag);
                $sStr .= $this->_getStyle($sTag);
                return $sStr;
                break;

            case 'a':
                $sStr =  $this->_getClass($sTag);
                $sStr .= $this->_getStyle($sTag);
                return $sStr;
                break;
        }

        return null;
    }

    /**
     * Get tpl item attributes
     *
     * @access  public
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getTplItemAttributes() {
        $aTplItem['TABLE_LAYOUT'] = $this->getAttrByTag('table');
        $aTplItem['TR_LAYOUT']    = $this->getAttrByTag('tr');
        $aTplItem['TH_LAYOUT']    = $this->getAttrByTag('th');
        $aTplItem['TD_LAYOUT']    = $this->getAttrByTag('td');
        $aTplItem['A_LAYOUT']     = $this->getAttrByTag('a');

        return $aTplItem;
    }

    // ====================================================================

    /**
     * _get table width
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _getTableWidth() {
        $sAttr = $this->_getAttr('tablewidth');

        // Return nothing
        if ($sAttr == '') { return $sAttr; }

        return ' width="'.$sAttr.'"';
    }

    /**
     * _get table padding
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function _getTablePadding() {
        $sAttr = $this->_getAttr('tablepadding');

        // Set required attribute value
        if ($sAttr == '') { $sAttr = '0'; }

        return ' cellpadding="'.$sAttr.'"';
    }

    /**
     * _get table spacing
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function _getTableSpacing() {
        $sAttr = $this->_getAttr('tablespacing');

        // Set required attribute value
        if ($sAttr == '') { $sAttr = '0'; }

        return ' cellspacing="'.$sAttr.'"';
    }

    /**
     * _get table border
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function _getTableBorder() {
        $sAttr = $this->_getAttr('tableborder');

        // Set required attribute value
        if ($sAttr == '') { $sAttr = '0'; }

        return ' border="'.$sAttr.'"';
    }

    /**
     * _get td width
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _getTdWidth() {
        $sAttr = $this->_getAttr('tdwidth');

        // Return nothing
        if ($sAttr == '') { return $sAttr; }

        return ' width="'.$sAttr.'"';
    }

    /**
     * _get class
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _getClass($sAttr) {
        $sAttr = $this->_getAttr($sAttr . 'class');

        // Return nothing
        if ($sAttr == '') { return $sAttr; }

        return ' class="'.$sAttr.'"';
    }

    /**
     * _get style
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _getStyle($sAttr) {
        $sAttr = $this->_getAttr($sAttr . 'style');
        if ($sAttr == '') { return $sAttr; }

        return ' style="'.$sAttr.'"';
    }

    // ====================================================================

    /**
     * _get attr
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function _getAttr($sAttr) {
        if ($this->aAttr[$sAttr][$this->aCounter[$sAttr]] == '') {
            $this->_incCounter($sAttr);
            return '';
        }

        $sStr = $this->aAttr[$sAttr][$this->aCounter[$sAttr]];
        $this->_incCounter($sAttr);

        return $sStr;
    }

    /**
     * _inc counter
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
    protected function _incCounter($sKey) {
        if (isset($this->aAttr[$sKey][$this->aCounter[$sKey]+1])) {
            $this->aCounter[$sKey]++;
        } else {
            $this->aCounter[$sKey] = 0;
        }
    }

} // of class

/*
    Welcome my son, welcome to the machine.
    Where have you been? It's alright we know where you've been.
    You've been in the pipeline, filling in time, provided with toys and
    'Scouting for Boys'.
    You bought a guitar to punish your ma,
    And you didn't like school, and you know you're nobody's fool,
    So welcome to the machine.

    Welcome my son, welcome to the machine.
    What did you dream? It's alright we told you what to dream.
    You dreamed of a big star, he played a mean guitar,
    He always ate in the Steak Bar. He loved to drive in his Jaguar.
    So welcome to the machine.
*/

?>
