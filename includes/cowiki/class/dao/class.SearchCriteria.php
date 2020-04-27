<?php

/**
 *
 * $Id: class.SearchCriteria.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     dao
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
 * coWiki - Search criteria class
 *
 * @package     dao
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class SearchCriteria extends Object {

    protected
        $Exp      = null,
        $sFilter  = null,
        $aOrdProp = array(),
        $aOrdSort = array(),
        $nFirst   = 0,
        $nMax     = 1E6;

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
     * Set query filter
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function setQueryFilter($sStr) {
        $this->sFilter = trim($sStr);
    }
    /**
     * Get query filter
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
    public function getQueryFilter() {
        return $this->sFilter;
    }

    /**
     * Add expression
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Exp"
     */
    public function addExpression(AbstractExpression $Exp) {
        if (is_object($Exp) && $Exp->isA('AbstractExpression')) {
            $this->Exp->add($Exp);
        }
    }

    /**
     * Add all expressions
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Vector"
     */
    public function addAllExpressions(Vector $Vector) {
        $It = $Vector->iterator();
        while ($Obj = $It->next()) {
            $this->addExpression($Obj);
        }
    }

    /**
     * Has expressions
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
    public function hasExpressions() {
        return !$this->Exp->isEmpty();
    }

    /**
     * Get expressions
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
    public function getExpressions() {
        return $this->Exp;
    }

    /**
     * Reset expressions
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function resetExpressions() {
        $this->Exp = new Vector();
    }

    /**
     * Add order
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function addOrder($sProp, $sOrder = 'ASC') {
        if (strtolower($sOrder) == 'asc' || strtolower($sOrder) == 'desc') {
            $this->aOrdProp[] = strtolower($sProp);
            $this->aOrdSort[] = strtoupper($sOrder);
        }
    }

    /**
     * Has orders
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
    public function hasOrders() {
        return sizeof($this->aOrdProp) > 0;
    }

    /**
     * Reset orders
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function resetOrders() {
        $this->aOrdProp = array();
        $this->aOrdSort = array();
    }

    /**
     * Get orders
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getOrders() {
        if ($this->hasOrders()) {
            return $this->aOrdProp;
        }
        return null;
    }

    /**
     * Get first order
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getFirstOrder() {
        if ($this->hasOrders()) {
            return $this->aOrdProp[0];
        }
        return null;
    }

    /**
     * Get sorts
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getSorts() {
        if ($this->hasOrders()) {
            return $this->aOrdSort;
        }
        return null;
    }

    /**
     * Get first sort
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getFirstSort() {
        if ($this->hasOrders()) {
            return $this->aOrdSort[0];
        }
        return null;
    }

    /**
     * Set first result
     *
     * @access  public
     * @param   integer
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nFirst"
     */
    public function setFirstResult($nFirst) {
        $this->nFirst = abs((int)$nFirst);
    }

    /**
     * Get first result
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
    public function getFirstResult() {
        return $this->nFirst;
    }

    /**
     * Set max results
     *
     * @access  public
     * @param   integer
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nMax"
     */
    public function setMaxResults($nMax) {
        $this->nMax = abs((int)$nMax);
    }

    /**
     * Get max results
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
    public function getMaxResults() {
        return $this->nMax;
    }

    /**
     * Get query
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getQuery() {

        // Generate expression string
        $It = $this->Exp->iterator();
        $sExp = $It->hasNext() ? ' WHERE ' : '';

        while ($Obj = $It->next()) {
            $sExp .= $Obj->toSqlString();

            if ($It->hasNext()) {
                $sExp .= ' AND ';
            }
        }

        return $sExp;
    }

    /**
     * Generate order string
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getOrderBy() {

        $sOrder = '';
        $n = sizeof($this->aOrdProp);

        if ($n != 0) {
            $sOrder = "\n ORDER BY ";

            for ($i=0; $i<$n; $i++) {
                $sOrder .= $this->aOrdProp[$i]
                           . ' '
                           . $this->aOrdSort[$i]
                           . ', ';
            }

            // Remove last comma
            if ($sOrder != '') {
                $sOrder = substr($sOrder, 0, -2);
            }
        }

        return $sOrder;
    }

} // of class

/*
    Overall length eight hundred and seventy millimetres,
    Length of barrel four hundred and fifteen millimetres,
    Length of sighting line three-hundred and seventy-eight millimetres.

    Weight of magazine empty point-four-two kilograms,
    Weight of magazine loaded point-nine-two kilograms.

    Overall weight with loaded magazine four-point-eight kilograms
    Chamber Pressure forty thousand, five hundred and fifty pounds per
    square inch.

    The seven-point-six-two millimetre Kalashnikov rifle,
    Fires seven-point-two-six-six-two millimetre rounds, M one-ninety.

    Muzzle velocity seven hundred and ten metres per second,
    Two thousand, three hundred and eighty feet per second,
    Specified rate of fire: six hundred rounds per minute.

    Six hundred rounds per minute

    Service ammunition is divided into full cartridges,
    and special purpose cartridges.
    Full ammunition is used to destroy personnel

    Special ammunition, depending upon its construction,
    is designed for target identification and correction of fire,
    ignition of fuel and highly flammable objects
    or destroying lightly armoured targets.

    Tracer cartridges are used for target identification,
    fire adjustment, signal purposes and destroying personnel

    Destroying personnel

    Tracer bullets can ignite, can ignite.
    The path of the bullet is indicated, by a red flame.
    Eight B incendiary cartridges are used to ignite fuel,
    gasoline and for destroying targets protected by thick armour plate.

    The standard cartridge used by the AK-47 is the M-43,
    Bullet weight one-two-two grains, powder weight 25 grains.
    Standard markings, fool model PS, no colour.
    Tracer model T-four-five, green tip.
    Eight B I model BZ, black and red tip.
    Incendiary model T, type Z, red tip.
    Special cartridges, plastic, blank with metal case,
    Finland red, Germany black, Egypt white.

    Short range cartridges, full, round nose, lacquered steel case, white
    tip. Tracer, round nose, lacquered steel case, white and dark green tip.

    There are three basic models of the AK:
    AK-47: machined receiver, no bayonet lug,
    Polished hold and bolt cabinet,
    Sighted up to eight hundred metres.

    AK-M: stamped receiver, bayonet lug,
    Plug lined bolt, beaver tailed fore-grip
    Range up to a thousand metres

    RP-K: squad LMD, longer barrel,
    Equipped with seventy-five round drum magazine,
    Forty round box magazine,
    Or may use a standard thirty round magazine,

    Despite a specification of six-hundred rounds per minute,
    Extensive experience of all models, proves the full-automatic
    rate to be approximately eight-hundred rounds per minute.

    Destroy personnel
*/

?>
