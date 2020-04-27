<?php

/**
 *
 * $Id: class.CurrentUser.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     core
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
 * coWiki - Current user class
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class CurrentUser extends User {
    protected static
        $Instance = null;

    protected
        $aMemberGroups = array(),
        $UserPreferences = null,
        $bIsReseted = false,
        $aRestore = array();

    /**
     * Get instance
     *
     * @access  public
     * @return  CurrentUser
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new CurrentUser;
        }
        return self::$Instance;
    }

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
        $this->UserPreferences = UserPreferences::getInstance();
    }

    /**
     * Reset
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function reset() {

        $this->aRestore['userId']  = $this->get('userId');
        $this->aRestore['groupId'] = $this->get('groupId');
        $this->aRestore['login']   = $this->get('login');
        $this->aRestore['email']   = $this->get('email');
        $this->aRestore['name']    = $this->get('name');
        $this->aRestore['isValid'] = $this->get('isValid');
        $this->aRestore['member']  = $this->getMemberGroups();

        $this->set('userId',   65535);        // default user id
        $this->set('groupId',  65535);        // default group id
        $this->set('login',   'guest');
        $this->set('name',    __('I18N_GUEST_FULLNAME'));
        $this->set('isValid', false);
        $this->setMemberGroups(new Vector()); // remove member

        $this->bIsReseted = true;
    }

    /**
     * Is reseted
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isReseted() {
        return $this->bIsReseted;
    }

    /**
     * Restore original user settings
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function restore() {
        if ($this->isReseted()) {
            $this->set('userId',   $this->aRestore['userId']);
            $this->set('groupId',  $this->aRestore['groupId']);
            $this->set('login',    $this->aRestore['login']);
            $this->set('email',    $this->aRestore['email']);
            $this->set('name',     $this->aRestore['name']);
            $this->set('isValid',  $this->aRestore['isValid']);
            $this->setMemberGroups($this->aRestore['member']);
        }
    }

    /**
     * Is current user a root user?
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isRoot() {
        $Registry = RuntimeContext::getInstance()->getRegistry();

        // WARNING: Overrides all access checks. All users will get root
        // access!
        if ((int)$Registry->get('.AUTH_ROOT_FOR_ALL') !== 0) {
            return true;
        }

        // Check user- and group ids
        if ($this->get('userId') === 0 || $this->get('groupId') === 0) {
            return true;
        }

        // Check member groups
        $It = $this->getMemberGroups()->iterator();

        while ($Obj = $It->next()) {
            if ($Obj->get('groupId') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is current user a guest user?
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function isGuest() {
        return $this->get('userId') === 65535;
    }

    /**
     * Get current user preferences
     *
     * @access  public
     * @return  UserPreferences
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getPreferences() {
        return $this->UserPreferences;
    }

    /**
     * Get current user encoding
     *
     * @access  public
     * @return  string
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getEncoding() {
        $sCatalog = $this->getPreferences()->get('catalog');
        $sEncoding = strtoupper(end(explode('.', $sCatalog)));

        return $sEncoding;
    }

} // of class

?>
