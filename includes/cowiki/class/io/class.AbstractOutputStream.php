<?php

/**
 *
 * $Id: class.AbstractOutputStream.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     io
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
 * coWiki - Abstract output stream class
 *
 * @package     io
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
abstract class AbstractOutputStream extends Object {

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    abstract public function __construct($mOut, $bAppend = false);

    // --------------------------------------------------------------------

    /**
     * Class destructor
     *
     * @access  public
     * @return  void
     *
     * @author  Niels Braczek, <nbraczek@bsds.de>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check the parameter type of "$mOut"
     */
    abstract public function __destruct();

    // --------------------------------------------------------------------

    /**
     * Open
     *
     * @access  public
     * @param   string Filename
     * @param   string Access mode
     * @return  boolean
     *
     * @author  Niels Braczek, <nbraczek@bsds.de>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    abstract public function open($sFilename, $sMode);

    // --------------------------------------------------------------------

    /**
     * Write
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
    abstract public function write($sOut);

    // --------------------------------------------------------------------

    /**
     * Flush
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    abstract public function flush();

    // --------------------------------------------------------------------

    /**
     * Close
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    abstract public function close();

} // of class

?>