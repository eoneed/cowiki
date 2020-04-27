<?php

/**
 *
 * $Id: class.AuthMySQL.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     auth
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
 * coWiki - Auth MySQL class
 *
 * @package     auth
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class AuthMySQL extends Object
                implements AuthCallback {

    private
        $Context = null,
        $rLink = null,
        $bConnected = false;

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

        $this->Context = RuntimeContext::getInstance();
        $Registry = $this->Context->getRegistry();
        $UriInfo = new UriInfo($Registry->get('.AUTH_RESOURCE'));

        if ($UriInfo->get('scheme') == 'mysql') {
        
            if (!function_exists('mysql_connect')) {
                $this->Context->addError(540, 'MySQL extension not available');
                $this->Context->terminate();
            }
  
            $sPort = '';
            if ($UriInfo->get('port')) {
                $sPort = ':' . $sPort;
            }

            $this->rLink = @mysql_connect(
                $UriInfo->get('host') . $sPort,
                $UriInfo->get('user'),
                $UriInfo->get('pass')
            );

            $this->bConnected = $this->rLink
                && @mysql_select_db($UriInfo->get('basepath'), $this->rLink);
        }

        if (!$this->bConnected) {
            $this->Context->addError(553);
        }
    }

    /**
     * Validate
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
     */
    public function validate($sLogin, $sPasswd) {

        // No connection, no result
        if (!$this->bConnected) {
            return false;
        }

        // ----------------------------------------------------------------

        $Registry = $this->Context->getRegistry();

        // AuthMySQL handler requires .AUTH_QUERY
        if (!$Registry->has('.AUTH_QUERY')) {
            $this->Context->addError(516, '.AUTH_QUERY');
            return false;
        }

        // AuthMySQL handler requires .AUTH_ENCRYPTION
        if (!$Registry->has('.AUTH_ENCRYPTION')) {
            $this->Context->addError(516, '.AUTH_ENCRYPTION');
            return false;
        }

        // Check if .AUTH_QUERY starts with 'SELECT '
        $sStr = strtolower(substr(trim($Registry->get('.AUTH_QUERY')),0,7));
        if ($sStr != 'select ') {
            $this->Context->addError(560, $Registry->get('.AUTH_QUERY'));
            return false;
        }

        // Remove possible colons
        $sQuery = str_replace(';', '', $Registry->get('.AUTH_QUERY'));

        // Replace {%LOGIN%} placeholder
        $sQuery = str_replace('{%LOGIN%}', addslashes($sLogin), $sQuery);

        // Get user password
        $rResult = @mysql_query($sQuery, $this->rLink);
        if (!is_resource($rResult)) {
            $this->Context->addError(560, $Registry->get('.AUTH_QUERY'));
            return false; 
        }

        // ----------------------------------------------------------------

        $aData = @mysql_fetch_assoc($rResult);
        @mysql_free_result($rResult);

        if ($aData) {
            $sDbPass = array_pop($aData);

            switch (strtolower($Registry->get('.AUTH_ENCRYPTION'))) {

                case 'md5':
                    return $sDbPass == md5($sPasswd);
                    break;

                case 'crypt':
                    return $sDbPass == crypt($sPasswd, $sDbPass);
                    break;

                case 'plain':
                case '':
                    return $sDbPass == $sPasswd;
                    break;

                default:
                    return false;
            }
        }

        return false;
    }

} // of class

?>
