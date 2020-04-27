<?php

/**
 *
 * $Id: class.AbstractUserDAO.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Abstract user DAO class
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
abstract class AbstractUserDAO extends PersistentObservable {

    protected
        $aIgnLogin   = array(),
        $aIgnUid     = array(),
        $aIgnGrpName = array(),
        $aIgnGid     = array(),

        $Users = null,
        $Groups = null;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $Context = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();

        // Initialize ignored account/groups names and ids
        $sLogin = $Registry->get('.USER_IGNORE_LOGIN');
        $sId    = $Registry->get('.USER_IGNORE_UID');
        $this->aIgnLogin = explode(',', $sLogin);
        $this->aIgnUid   = explode(',', $sId);

        // Initialize ignored account/groups names and ids
        $sName = $Registry->get('.USER_IGNORE_GROUP_NAME');
        $sId   = $Registry->get('.USER_IGNORE_GID');
        $this->aIgnGrpName = explode(',', $sName);
        $this->aIgnGid     = explode(',', $sId);

        // ----------------------------------------------------------------

        $this->Groups = new Vector;
        $this->Users = new Vector;

        // Set default groups
        $Group = new Group();
        $Group->set('groupId', 0);
        $Group->set('name', 'wheel');
        $this->Groups->add($Group);

        // ---

        $Group = new Group();
        $Group->set('groupId', 65535);
        $Group->set('name', 'guests');
        $this->Groups->add($Group);

        // ---

        // Set default users
        $User = new User();
        $User->set('userId', 0);
        $User->set('groupId', 0);
        $User->set('login', 'root');
        $User->set('name', __('I18N_ROOT_FULLNAME'));
        $User->set('isActive', true);
        $User->set('isLocked', false);

        $this->Users->add($User);

        // ---

        $User = new User();
        $User->set('userId', 65535);
        $User->set('groupId', 65535);
        $User->set('login', 'guest');
        $User->set('name', __('I18N_GUEST_FULLNAME'));
        $User->set('isActive', true);
        $User->set('isLocked', false);

        $this->Users->add($User);
    }

    // --------------------------------------------------------------------

    /**
     * Abstract methods to be implemented by a subclass
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function hasUserActiveFlag();

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function hasUserLockedFlag();

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getUserByUid($nId);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getUserByLogin($sLogin);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getAllUsers($bGetGuests = true);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function storeUser($User);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function storeUsers($Collection);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function removeUser($User);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getGroupByGid($nId);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getAllGroups();

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function storeGroup($Group);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function storeGroups($Collection);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function removeGroup($Group);

    // --------------------------------------------------------------------

    /**
     * Is ignored account
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    abstract function getMemberGroupsOfUid($nId);

    // --------------------------------------------------------------------

    /**
     * Check if given user id and/or user login has to be ignored
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$nUid"
     */
    public function isIgnoredAccount($nUid, $sLogin) {
        if (in_array($nUid, $this->aIgnUid)) {
            return true;
        }

        for ($i=0, $n=sizeof($this->aIgnLogin); $i<$n; $i++) {
            if (preg_match(','.trim($this->aIgnLogin[$i]).',iS', $sLogin)) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Check if given group id and/or group name has to be ignored
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$nGid"
     */
    public function isIgnoredGroup($nGid, $sName) {
        if (in_array($nGid, $this->aIgnGid)) {
            return true;
        }

        for ($i=0, $n=sizeof($this->aIgnGrpName); $i<$n; $i++) {
            if (preg_match(','.trim($this->aIgnGrpName[$i]).',iS', $sName)) {
                return true;
            }
        }

        return false;
    }

} // of class

?>
