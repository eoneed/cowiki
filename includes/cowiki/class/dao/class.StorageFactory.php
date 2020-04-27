<?php

/**
 *
 * $Id: class.StorageFactory.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Storage factory class
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
class StorageFactory extends Object {

    protected static
        $Instance = null;

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
            self::$Instance = new StorageFactory;
        }
        return self::$Instance;
    }

    /**
     * Create doc storage
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
     * Create doc storage
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
    public function createDocStorage() {
        $Registry = RuntimeContext::getInstance()->getRegistry();
        return $this->_create($Registry->get('.DOCUMENT_RESOURCE'), 550, 551);
    }

    /**
     * Create user storage
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
    public function createUserStorage() {
        $Registry = RuntimeContext::getInstance()->getRegistry();
        return $this->_create($Registry->get('.USER_RESOURCE'), 554, 555);
    }

    /**
     * _create
     *
     * @access  protected
     * @param   string
     * @param   integer
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nApiErr"
     * @todo    [D11N]  Check the parameter type of "$nConnErr"
     * @todo    [D11N]  Check return type
     * @todo    [FIX]   think about resource-recycling here
     */
    protected function _create($sResource, $nApiErr, $nConnErr) {
        $UriInfo = new UriInfo($sResource);
        if ($UriInfo->get('scheme') == 'mysql') {

            // FIX: think about resource-recycling here

            // New abstraction layer
            $Storage = new StorageMySQL();

            // Connect service
            $bSuccess = $Storage->connect(
                $UriInfo->get('host'),
                $UriInfo->get('port'),
                $UriInfo->get('user'),
                $UriInfo->get('pass'),
                $UriInfo->get('basepath')
            );

            if ($bSuccess) {
                return $Storage;
            }

            // Connect failed
            RuntimeContext::getInstance()->addError($nConnErr);
            RuntimeContext::getInstance()->terminate();
        }

        // ---

        // Unknown storage API
        RuntimeContext::getInstance()->addError($nApiErr);
        RuntimeContext::getInstance()->terminate();
    }

} // of class

?>
