<?php

/**
 *
 * $Id: class.AbstractSimpleExpression.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Abstract simple expression class
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
abstract class AbstractSimpleExpression extends AbstractExpression {

    protected
        $sProp  = null,
        $sValue = null;

    /**
     * Class constructor
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct($sProp, $sValue) {
        $this->sProp  = $sProp;
        $this->sValue = $sValue;
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
     * @todo    [D11N]  Check return type
     */
    public function toSqlString() {
        return $this->sProp
               . ' '
               . $this->getOperator()
               . ' \''
               . $this->sValue
               . '\'';
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