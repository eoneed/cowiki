<?php

/**
 *
 * $Id: class.ReferrerDAO.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Referrer DAO class
 *
 * @package     dao
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class ReferrerDAO extends Object {

    protected static
        $Instance = null;

    protected
        $Context = null,
        $Request = null,
        $Refs    = null;

    // --------------------------------------------------------------------

    /**
     * Get the unique instance of the class (This class is implemented as
     * Singleton).
     *
     * @access  public
     * @return  Object  The class instance
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new ReferrerDAO;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $this->Context = RuntimeContext::getInstance();
        $this->Request = $this->Context->getRequest();
    }

    // --------------------------------------------------------------------

    /**
     * Get recent referrers.
     *
     * @access  public
     * @return  object  Vector of recent referrers.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getReferrers() {
        if (is_object($this->Refs) && $this->Refs->isA('Vector')) {
            return $this->Refs;
        }

        try {
            $sStr = $this->Context->readTempFile('referrer');
            $this->Refs = @unserialize($sStr);

        } catch (IOException $e) {
            // swallow
        }

        if (!is_object($this->Refs) || !$this->Refs->isA('Vector')) {
            $this->Refs = new Vector();
        }

        return $this->Refs;
    }

    // --------------------------------------------------------------------

    /**
     * Store the referrer.
     *
     * @access  public
     * @param   string    Complete referrer URI
     * @return  boolean   true if successful, false otherwise (false will
     *                    be returned also if the URI was illegal)
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function store($sStr) {

        // Do not store empty referrers
        if ($sStr == '') {
            return false;
        }

        // Do not store file:// referrers
        if (substr($sStr, 0, 7) == 'file://') {
            return false;
        }

        // Remove possible leaking session id (from other coWikis)
        $sStr = preg_replace('#cowiki=[a-z0-9]+&?#', '', $sStr);
        $sStr = preg_replace('#[&|?]$#', '', $sStr);

        $UriInfo = new UriInfo($sStr);
        if (!$UriInfo->isValid()) {
            return false;
        }

        // Do not store localhost referrers
        if ($UriInfo->get('host') == 'localhost'
            || $UriInfo->get('host') == '127.0.0.1') {
            return false;
        }

        // Check for referres from the same host
        if ($this->Request->getHost() == $UriInfo->get('fullHost')) {
            return false;
        }

        // Get referrer vector
        $Refs = $this->getReferrers();

        // Escape ampersand entity
        $sStr = str_replace('&', '&amp;', $sStr);

        // Do not track doublettes
        if ($Refs->findByPropertyValue('url', $sStr)) {
            return false;
        }

        $Referrer = new Object();
        $Referrer->set('host', $UriInfo->get('host'));
        $Referrer->set('url',  $sStr);

        $Refs->unshift($Referrer);
        $Refs->cut(20);

        $this->Refs = $Refs;

        // Cache referres
        return $this->Context->writeTempFile('referrer', serialize($Refs));
    }

} // of class

?>
