<?php

/**
 *
 * $Id: class.AbstractJunction.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     dao
 * @subpackage  expression
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Abstract junction class
 *
 * @package     dao
 * @subpackage  expression
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
abstract class AbstractJunction extends AbstractExpression {

    protected
        $Exp = array();

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
        $this->Exp = new Vector();
    }

    /**
     * Do not pass by reference!
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Exp"
     */
    public function add($Exp) {
        if (is_object($Exp) && $Exp->isA('AbstractExpression')) {
            $this->Exp->add($Exp);
        }
    }

    /**
     * To sql string
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function toSqlString() {

        // Generate expression string
        if ($this->Exp->isEmpty()) {
            return '1=1';
        }

        // Iterata through expressions
        $It = $this->Exp->iterator();
        $sExp = '';

        while ($Obj = $It->next()) {
            $sExp .= $Obj->toSqlString();

            if ($It->hasNext()) {
                $sExp .= $this->getOperator();
            }
        }

        return '(' . trim($sExp) . ')';
    }

    /**
     * Abstract method
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract function getOperator();

} // of class

?>
