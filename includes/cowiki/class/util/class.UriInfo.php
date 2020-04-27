<?php

/**
 *
 * $Id: class.UriInfo.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     util
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
 * This info class splits an URI into its parts and tried to validate
 * the syntax of the URI. Usage:
 *
 * <code>
 *    $Info = new UriInfo('http://example.com/foo.html?a=b&c=d#anchor1');
 *
 *    if ($Info->isValid()) {
 *        echo $Info->get('scheme');
 *        echo $Info->get('query');
 *    }
 * </code>
 *
 * The possible parts of an URI are
 *
 * - 'scheme'
 * - 'user'
 * - 'pass'
 * - 'host'
 * - 'port'
 * - 'path'
 * - 'query'
 * - 'fragment'
 *
 * @package     util
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class UriInfo extends Object {

    protected
        $bIsValid = false;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  The URI to be splitted in its parts.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct($sUri) {

        // Default value
        $this->set('path', '/');

        // Check URI (this is a very simple check, FIX it sometime)
        $bFlag = preg_match(
                    '#^[+a-zA-Z0-9]+://[^\s]+$#',
                    $sUri
                 );

        if ($bFlag) {

            $aArr = @parse_url($sUri);

            // Set member values

            if (isset($aArr['scheme'])) {
                $this->set('scheme', strtolower($aArr['scheme']));
            }

            if (isset($aArr['host'])) {
                $this->set('host', $aArr['host']);
            }

            $sPort = '';

            if (isset($aArr['port'])) {
                $this->set('port', $aArr['port']);

                if ($aArr['port'] != '80') {
                    $sPort = ':' . $aArr['port'];
                }
            }

            $this->set('fullHost', $aArr['host'] . $sPort);

            if (isset($aArr['user'])) {
                $this->set('user', $aArr['user']);
            }

            if (isset($aArr['pass'])) {
                $this->set('pass', $aArr['pass']);
            }

            if (isset($aArr['path'])) {
                $this->set('path', $aArr['path']);
            }

            if (isset($aArr['path'])) {
                $this->set('basepath', basename($aArr['path']));
            }

            if (isset($aArr['query'])) {
                $this->set('query', $aArr['query']);
            }

            if (isset($aArr['fragment'])) {
                $this->set('fragment', $aArr['fragment']);
            }

            $this->set('fullUri', $sUri);

            $this->bIsValid = true;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Returns true if the constructor parameter was a valid URI.
     *
     * @access  public
     * @return  boolean true or false
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function isValid() {
        return $this->bIsValid;
    }

    // --------------------------------------------------------------------

    /**
     * Returns true if the constructor parameter was not a valid URI.
     *
     * @access  public
     * @return  boolean true or false
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function isNotValid() {
        return !$this->bIsValid;
    }

} // of class

/*
    Darwin avait raison
    seul plus fort sur vie
    il avait tout compris
    c’est évident

    Darwin avait raison
*/

?>
