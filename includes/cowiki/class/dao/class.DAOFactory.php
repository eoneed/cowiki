<?php

/**
 *
 * $Id: class.DAOFactory.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - DAO factory class
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
class DAOFactory extends Object {

    protected static
        $Instance = null;

    // --------------------------------------------------------------------

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
            self::$Instance = new DAOFactory;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Create document dao
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function __construct() {
    }

    // --------------------------------------------------------------------

    /**
     * Create document dao
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
    public function createDocumentDAO() {

        $Context = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();
        $UriInfo = new UriInfo($Registry->get('.DOCUMENT_RESOURCE'));

        // ----------------------------------------------------------------

        if ($UriInfo->get('scheme') == 'mysql') {

            if (!function_exists('mysql_connect')) {

                // {{{ DEBUG }}}
                Logger::error('MySQL extension not available.');

                $Context->addError(540, 'MySQL extension not available');
                $Context->terminate();
            }

            return DocumentDAO::getInstance();
        }

        // ----------------------------------------------------------------

        // {{{ DEBUG }}}
        Logger::error('Unknown URL scheme "'.$UriInfo->get('scheme').'"');

        // API error
        $Context->addError(550, $UriInfo->get('scheme'));
        $Context->terminate();
    }

    // --------------------------------------------------------------------

    /**
     * Create shout box dao
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
    public function createShoutBoxDAO() {
        // Implementation class
        return ShoutBoxDAO::getInstance();
    }

    // --------------------------------------------------------------------

    /**
     * Create user dao
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
    public function createUserDAO() {

        $Context = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();
        $UriInfo = new UriInfo($Registry->get('.USER_RESOURCE'));

        // ----------------------------------------------------------------

        if ($UriInfo->get('scheme') == 'mysql') {

            if (!function_exists('mysql_connect')) {

                // {{{ DEBUG }}}
                Logger::error('MySQL extension not available.');

                $Context->addError(540, 'MySQL extension not available');
                $Context->terminate();
            }

            return UserDAOMySQL::getInstance();
        }

        // ----------------------------------------------------------------

        // {{{ DEBUG }}}
        Logger::error('Unknown URL scheme "'.$UriInfo->get('scheme').'"');

        // API error
        $Context->addError(554, $UriInfo->get('scheme'));
        $Context->terminate();
    }

} // of class

/*
    Ueberall ist Bitterkeit
    Farbenfroh ein Schicksalsschlag
    Rasch bohren Naegel
    Aus denen das Verlangen schreit

    Tausend haben Paradiese
    Ich hab ueber tausend Ohren
    Trauernd Opfer treten suechtig
    Ueber unsren Massenwahn
*/

?>
