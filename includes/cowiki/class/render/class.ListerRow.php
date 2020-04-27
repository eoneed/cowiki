<?php

/**
 *
 * $Id: class.ListerRow.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Lister row class
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
class ListerRow extends Object {

    protected
        $Vector = null;

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
        $this->Vector = new Vector();
    }

    /**
     * Add column
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
    public function addColumn($Obj) {
        if ($Obj->isA('ListerColumn')) {
            $this->Vector->add($Obj);
        }
    }

    /**
     * Get columns
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
    public function getColumns() {
        return $this->Vector;
    }

} // of class

/*
    Meint ihr nicht, wir koennten unterschreiben
    Auf dass uns ein bis zwei Prozent gehoeren
    Und tausende uns hoerig sind?
    Meint ihr nicht, wir koennten uns in Aether braten lassen
    Und bis zum letzten Tropfen dem Verpackungshandel frohnen?

    Wir koennten, aber ...

    Meint ihr nicht, wir koennten unsere Zuege 
    Zigtausendfach in falschen Farben weltbewegend scheinen lassen?
    Meint ihr nicht, wir koennten uns vergolden auf vierzig Sprossen
    Fuer unters Volk gebrachte Massen viele Monde thronen?

    Wir koennten, aber ...
    
    Meint ihr nicht, wir koennten es signieren
    Vielleicht sogar auch resignieren
    Und dieses Land gleich Eintagsfliegen
    Nur noch auf und ab und ab und auf bespielen
    Um spaeter dann zurueckzukehren
    Ganz aufgedunsen, laengst vergessen
    Nur noch kleine Kreise ziehen?

    Wir koennten, aber ..
*/

?>
