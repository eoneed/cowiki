<?php

/**
 *
 * $Id: class.Lister.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Lister class
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
class Lister extends Object {

    protected
        $Rows    = null,
        $Context = null;

    // Field types
    const TYPE_RAW      = 0;
    const TYPE_STRING   = 1;
    const TYPE_TEXT     = 2;
    const TYPE_BOOLEAN  = 3;
    const TYPE_UNIXTIME = 4;

    /**
     * Class constructor
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {
        $this->Context = RuntimeContext::getInstance();
        $this->Rows = new Vector();
    }

    /**
     * Add row
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Obj"
     */
    public function addRow($Obj) {
        if ($Obj->isA('ListerRow')) {
            $this->Rows->add($Obj);
        }
    }

    /**
     * &generate
     *
     * @access  public
     * @param   string
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function &generate($sTplItemName = 'TPL_ITEM') {
        $aTplItem = array();

        // Iterate through rows
        $RowIt = $this->Rows->iterator();

        while ($RowObj = $RowIt->next()) {
            $aItem = array();

            // Iterate through columns
            $ColIt = $RowObj->getColumns()->iterator();

            while ($ColObj = $ColIt->next()) {

                // Iterate through column content(s)
                $ContIt = $ColObj->getContent()->iterator();

                while ($Obj = $ContIt->next()) {

                    if ($Obj->has('name')) {
                        $sKey = strtoupper($Obj->get('name'));
                    } else {
                        $sKey = strtoupper($Obj->get('prop'));
                    }

                    $aItem[$sKey] = $this->format(
                                        $RowObj->get('data'),
                                        $Obj
                                    );
                }
            }

            $aTplItem[] = $aItem;
        }

        return $aTplItem;
    }

    /**
     * Format
     *
     * @access  protected
     * @param   object
     * @param   object
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$RowData"
     * @todo    [D11N]  Check the parameter type of "$Obj"
     */
    protected function format($RowData, $Obj) {
        $sStr = '';

        // Get value from row data or from the object content directly if
        // the "value" property exists
        if ($Obj->has('value')) {
            $mValue = $Obj->get('value');
        } else {
            $mValue = $RowData->get($Obj->get('prop'));
        }

        switch ($Obj->get('type')) {
            case self::TYPE_RAW:
                $sStr = $mValue;
                break;

            case self::TYPE_TEXT:
                break;

            case self::TYPE_BOOLEAN:
                break;

            case self::TYPE_UNIXTIME:
                $sStr = $this->Context->makeDateTimeRelative($mValue);
                break;

            // TYPE_STRING
            default:
                $sStr = escape($mValue);
        }

        return $sStr;
    }

} // of class

/*
    Was sehen Sie wenn Sie im Dunkeln allein sind, und die Daemonen kommen?
*/

?>
