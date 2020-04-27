<?php

/**
 *
 * $Id: class.UserDAOMySQL.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - User DAO MySQL class
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
class UserDAOMySQL extends AbstractUserDAO {

    protected static
        $Instance = null;

    protected
        $Users, $Groups,

        $aIgnLogin   = array(),
        $aIgnUid     = array(),
        $aIgnGrpName = array(),
        $aIgnGid     = array(),

        $bGotAllUsers = false,
        $bGotAllGroups = false;

    protected
        $Storage      = null,
        $Request      = null,
        $Registry     = null,
        $Context      = null,
        $sUserTable   = 'cowiki_user',
        $sGroupTable  = 'cowiki_group',
        $sAssignTable = 'cowiki_user_group';

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
            self::$Instance = new UserDAOMySQL;
        }
        return self::$Instance;
    }

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

        // Call parent constructor
        parent::__construct();

        $this->Context  = RuntimeContext::getInstance();
        $this->Request  = $this->Context->getRequest();
        $this->Registry = $this->Context->getRegistry();
        $this->Storage = StorageFactory::getInstance()->createUserStorage();
    }

    // --------------------------------------------------------------------

    /**
     * Users stored in MySQL has a "user active" flag, reflect it
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function hasUserActiveFlag() {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Users stored in MySQL has a "user locked" flag, reflect it
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function hasUserLockedFlag() {
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Get user by uid
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getUserByUid($nId) {
        // FIX, do not get all if it is not necessary
        $this->getAllUsers();

        return $this->Users->findByPropertyValue('userId', (int)$nId);
    }

    // --------------------------------------------------------------------

    /**
     * Get user by login
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function getUserByLogin($sLogin) {
        // FIX, do not get all if it is not necessary
        $this->getAllUsers();

        return $this->Users->findByPropertyValue('login', $sLogin);
    }

    // --------------------------------------------------------------------

    /**
     * Get all users
     *
     * @access  public
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   think about caching the results. What about 10.000 ldap
     */
    public function getAllUsers($bGetGuests = true) {

        // FIX: think about caching the results. What about 10.000 ldap
        // users?!

        if ($this->bGotAllUsers) {
            return $this->Users;
        }

        // Get users without guests?
        $sWhere = "WHERE guest_only <> 'Y'";
        if ($bGetGuests) {
            $sWhere = '';
        }

        // Get all userdata
        $sQuery = "SELECT  rec_tan,
                           rec_mod_id,
                           rec_mod_ip,
                           rec_mod_host,
                           rec_state,
                           user_id,
                           group_id,
                           guest_only,
                           name,
                           email,
                           login,
                           passwd,
                           UNIX_TIMESTAMP(expires) AS expires,
                           meta
                     FROM  ".$this->sUserTable ."
                           $sWhere
                 ORDER BY  user_id";

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {

            // Check for ignored accounts
            if ($this->isIgnoredAccount($aData['user_id'], $aData['login'])) {
                continue;
            }

            $User = new User();

            $User->set('recTan',   $aData['rec_tan']);
            $User->set('modified', (int)$aData['rec_tan']);

            $User->set('modifiedByUid', (int)$aData['rec_mod_id']);
            $User->set('remoteAddr',    $aData['rec_mod_ip']);
            $User->set('remoteHost',    $aData['rec_mod_host']);

            $User->set('isActive',    $aData['rec_state'] == 'R');
            $User->set('userId',      (int)$aData['user_id']);
            $User->set('groupId',     (int)$aData['group_id']);
            $User->set('isGuestOnly', $aData['guest_only'] == 'Y');
            $User->set('name',        $aData['name']);
            $User->set('email',       $aData['email']);
            $User->set('login',       $aData['login']);
            $User->set('password',    $aData['passwd']);
            $User->set('expires',     $aData['expires']);
            $User->set('meta',        $aData['meta']);

            $this->Users->add($User);
        }

        $this->Storage->freeResult($rResult);

        // Users has been already read
        $this->bGotAllUsers = true;

        return $this->Users;
    }

    // --------------------------------------------------------------------

    /**
     * Store user
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$User"
     */
    public function storeUser($User) {
        $Context = RuntimeContext::getInstance();
        $CurrUser = $Context->getCurrentUser();

        if (!$CurrUser->isRoot() && $CurrUser !== $this) {
            $Context->addError(403);
            return false;
        }

        // Skip root and guest users
        if ($User->get('userId') === 0
         || $User->get('userId') === 65535) {
            return true;
        }

        // ----------------------------------------------------------------

        // Plausibility checks
        $aErr = array();

        $User->set('name',  trim($User->get('name')));
        $User->set('login', trim($User->get('login')));

        if ($User->get('name') == '') {
            $aErr[] = __('I18N_NAME');
        }

        if ($User->get('login') == '') {
            $aErr[] = __('I18N_AUTH_LOGIN_NAME');
        }

        if (sizeof($aErr) > 0) {
            $Context->addError(451, $aErr);
        }

        // ----------------------------------------------------------------

        // Check if login already exists
        $sQuery = " SELECT  COUNT(login) AS rec_count
                      FROM  ".$this->sUserTable."
                     WHERE  login = '".addslashes($User->get('login'))."'
                       AND  user_id != '".$User->get('userId')."'";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        // Oops, doublette or previous errors
        if ($aData['rec_count'] > 0) {
            $Context->addError(454);
            return false;
        }

        // ----------------------------------------------------------------

        // Check record tan
        $sQuery = " SELECT  rec_tan
                      FROM  ".$this->sUserTable."
                     WHERE  user_id = '".$User->get('userId')."'";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData['rec_tan'] != $User->get('recTan')) {
            // Warn only once!
            $User->set('recTan', $aData['rec_tan']);
            $Context->addError(440);

            return false;
        }

        // ----------------------------------------------------------------

        // New password? Check plausibility. A "null" password must be
        // handled by the DAO as if no changes on password are needed
        // (this is only true for existing users of course).

        $bCryptNewPasswd = false;

        // Existing user?
        if ($User->has('userId') && $User->get('userId') != null) {

            if ($User->get('password') == null) {
                // No changes on password for an existing user
            } else {
                // Check the existing user with new password set

                // Check password length
                if (strlen(trim($User->get('password'))) < 5) {
                    $Context->addError(456); // Unsecure password
                }

                $bCryptNewPasswd = true;
            }

        // New user!
        } else {

            // Check password length
            if (strlen(trim($User->get('password'))) < 5) {
                $Context->addError(456); // Unsecure password
            }

            $bCryptNewPasswd = true;
        }

        // ---

        if ($Context->hasErrors()) {
            return false;
        }

        // ----------------------------------------------------------------

        // Begin transaction
        $this->Storage->begin();

        // ----------------------------------------------------------------

        // Gather data for insert/update
        $aFields = array(
            'rec_tan'    => $this->Storage->generateTan(),
            'rec_mod_id' => $CurrUser->get('userId'),
            'rec_mod_ip' => $this->Request->getRemoteAddr(),
            'rec_state'  => $User->get('isActive') ? 'R' : '',

            'group_id'   => $User->get('groupId'),

            'name'       => $User->get('name'),
            'email'      => $User->get('email'),
            'login'      => $User->get('login'),
            'expires'    => date('Y-m-d H:i:s', $User->get('expires')),
            'meta'       => $User->get('meta')
        );

        // ----------------------------------------------------------------

        // Lookup host name
        if ($this->Registry->get('RUNTIME_LOOKUP_DNS')) {
            $aFields['rec_mod_host'] = $this->Request->getRemoteHost();
        }

        // ----------------------------------------------------------------

        // Crypt the password if it has been changed
        if ($bCryptNewPasswd) {

            // Do not encrypt if password is marked to be already encrypted
            if ($User->get('passwordIsEncrypted')) {
                $sPasswd = trim($User->get('password'));
            } else {
                $sPasswd = crypt(trim($User->get('password')));
            }

            $aFields = array_merge(
                          $aFields,
                          array('passwd' => $sPasswd)
                       );

        }

        // ----------------------------------------------------------------

        // If this is an update
        if ($User->get('userId') != 0) {

            // Set data for update
            $aUpdate = array(
                'table'  => $this->sUserTable,
                'fields' => $aFields,
                'where'  => "user_id = '".$User->get('userId')."'"
            );

            // Execute update
            $this->Storage->update($aUpdate);

        } else {

            // This is an initial insert: add required fields
            $aInitial = array(
                'created' => $this->Storage->getDateTimeAsString(time()),
            );

            // Set further data for insert
            $aInsert = array(
                'table'  => $this->sUserTable,
                'fields' => array_merge($aFields, $aInitial)
            );

            // Execute insert
            $this->Storage->insert($aInsert);

            // Set id of this user
            $User->set(
                'userId',
                $this->Storage->getLastInsertId($this->sUserTable)
            );
        }

        // ----------------------------------------------------------------

        // Update member groups
        $this->storeMemberGroupsOfUid(
            $User->getMemberGroups(),
            $User->get('userId')
        );

        // End transaction
        $this->Storage->commit();

        // Tell the observers that something has changed
        $this->notifyObservers();

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Store users
     *
     * @access  public
     * @param   object
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Users"
     */
    public function storeUsers($Users) {
        $bSuccess = true;

        // Begin transaction
        $this->Storage->begin();

        $It = $Users->iterator();

        while ($Obj = $It->next()) {

            // Do not store passwords, set them "null"
            $Obj->set('password', null);

            if (!$this->storeUser($Obj)) {
                // FIX the error
                RuntimeContext::getInstance()->addError(0);
                $bSuccess = false;
                break;
            }
        }

        // End transaction
        $this->Storage->commit();

        return $bSuccess;
    }

    // --------------------------------------------------------------------

    /**
     * Remove user
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$User"
     */
    public function removeUser($User) {
        $Context = RuntimeContext::getInstance();
        $CurrUser = $Context->getCurrentUser();

        if (!$CurrUser->isRoot()) {
            $Context->addError(403);
            return false;
        }

        // ----------------------------------------------------------------

        // Collect data to delete the user
        $aDeleteUser = array(
            'table' => $this->sUserTable,
            'where' => "user_id = '".$User->get('userId')."'"
        );

        // Collect data to delete the user<->group assign
        $aDeleteAssign = array(
            'table' => $this->sAssignTable,
            'where' => "user_id = '".$User->get('userId')."'"
        );

        // Begin transaction
        $this->Storage->begin();

        // Execute delete (remove)
        $this->Storage->remove($aDeleteAssign);
        $this->Storage->remove($aDeleteUser);

        // End transaction
        $this->Storage->commit();

        return $Context->hasNoErrors();
    }

    // --------------------------------------------------------------------

    /**
     * Get group by gid
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getGroupByGid($nId) {
        // FIX, do not get all if it is not necessary
        $this->getAllGroups();

        return $this->Groups->findByPropertyValue('groupId', (int)$nId);
    }

    // --------------------------------------------------------------------

    /**
     * Get all groups
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [FIX]   think about caching the results
     */
    public function getAllGroups() {

        // FIX: think about caching the results

        if ($this->bGotAllGroups) {
            return $this->Groups;
        }

        $sQuery = "SELECT  rec_tan,
                           rec_mod_id,
                           rec_mod_ip,
                           rec_mod_host,
                           rec_state,
                           group_id,
                           name,
                           description
                     FROM  cowiki_group
                 ORDER BY  group_id";

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {

            // Check for ignored groups
            if ($this->isIgnoredGroup($aData['group_id'], $aData['name'])) {
                continue;
            }

            $Group = new Group();

            $Group->set('recTan',        $aData['rec_tan']);
            $Group->set('modifiedByUid', (int)$aData['rec_mod_id']);
            $Group->set('remoteAddr',    $aData['rec_mod_ip']);
            $Group->set('remoteHost',    $aData['rec_mod_host']);

            $Group->set('isActive',    $aData['rec_state'] == 'R');
            $Group->set('groupId',     (int)$aData['group_id']);
            $Group->set('name',        $aData['name']);
            $Group->set('description', $aData['description']);

            $this->Groups->add($Group);
        }

        $this->Storage->freeResult($rResult);

        // Groups has been already read
        $this->bGotAllGroups = true;

        return $this->Groups;
    }

    // --------------------------------------------------------------------

    /**
     * Store group
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Group"
     */
    public function storeGroup($Group) {
        $Context = RuntimeContext::getInstance();
        $CurrUser = $Context->getCurrentUser();

        if (!$CurrUser->isRoot()) {
            $Context->addError(403);
            return false;
        }

        // ----------------------------------------------------------------

        // Plausibility checks
        $aErr = array();

        if ($Group->get('name') == '') {
            $aErr[] = __('I18N_NAME');
        }

        if (sizeof($aErr) > 0) {
            $Context->addError(451, $aErr);
            return false;
        }

        // ----------------------------------------------------------------

        // Check if group already exists
        $sQuery = " SELECT  COUNT(name) AS rec_count
                      FROM  ".$this->sGroupTable."
                     WHERE  name  = '".addslashes($Group->get('name'))."'
                       AND  group_id != '".$Group->get('groupId')."'";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        // Oops, doublette or previous errors
        if ($aData['rec_count'] > 0) {
            $Context->addError(457);
            return false;
        }

        // ----------------------------------------------------------------

        // Check record tan
        $sQuery = " SELECT  rec_tan
                      FROM  ".$this->sGroupTable."
                     WHERE  group_id = '".$Group->get('groupId')."'";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData['rec_tan'] != $Group->get('recTan')) {
            // Warn only once!
            $Group->set('recTan', $aData['rec_tan']);
            $Context->addError(440);

            return false;
        }

        // ----------------------------------------------------------------

        // Gather data for insert/update
        $aFields = array(
            'rec_tan'     =>  $this->Storage->generateTan(),
            'rec_mod_id'  =>  $CurrUser->get('userId'),
            'rec_mod_ip'  =>  $this->Request->getRemoteAddr(),
            'rec_state'   =>  $Group->get('isActive') ? 'R' : '',

            'name'        =>  $Group->get('name'),
            'description' =>  $Group->get('description'),
            'meta'        =>  $Group->get('meta')
        );

        // ----------------------------------------------------------------

        // Lookup host name
        if ($this->Registry->get('RUNTIME_LOOKUP_DNS')) {
            $aFields['rec_mod_host'] = $this->Request->getRemoteHost();
        }

        // ----------------------------------------------------------------

        // Begin transaction
        $this->Storage->begin();

        // If this is an update
        if ($Group->get('groupId') != 0) {

            // Set data for update
            $aUpdate = array(
                'table'  => $this->sGroupTable,
                'fields' => $aFields,
                'where'  => "group_id = '".$Group->get('groupId')."'"
            );

            // Execute update
            $this->Storage->update($aUpdate);

        } else {

            $aInsert = array(
                'table'  => $this->sGroupTable,
                'fields' => $aFields
            );

            // This is an initial insert
            $this->Storage->insert($aInsert);

            // Set id of this group
            $Group->set(
                'groupId',
                $this->Storage->getLastInsertId($this->sGroupTable)
            );
        }

        // End transaction
        $this->Storage->commit();

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Store groups
     *
     * @access  public
     * @param   object
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Groups"
     */
    public function storeGroups($Groups) {
        $bSuccess = true;

        // Begin transaction
        $this->Storage->begin();

        $It = $Groups->iterator();

        while ($Obj = $It->next()) {
            if (!$this->storeGroup($Obj)) {
                // FIX the error
                RuntimeContext::getInstance()->addError(0);
                $bSuccess = false;
                break;
            }
        }

        // End transaction
        $this->Storage->commit();

        return $bSuccess;
    }

    // --------------------------------------------------------------------

    /**
     * Remove group
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Group"
     */
    public function removeGroup($Group) {
        $Context = RuntimeContext::getInstance();
        $CurrUser = $Context->getCurrentUser();

        if (!$CurrUser->isRoot()) {
            $Context->addError(403);
            return false;
        }

        // ----------------------------------------------------------------

        // Collect data to delete the group
        $aDeleteGroup = array(
            'table' => $this->sGroupTable,
            'where' => "group_id = '".$Group->get('groupId')."'"
        );

        // Collect data to delete the user<->group assign
        $aDeleteAssign = array(
            'table' => $this->sAssignTable,
            'where' => "group_id = '".$Group->get('groupId')."'"
        );

        // Begin transaction
        $this->Storage->begin();

        // Execute delete (remove)
        $this->Storage->remove($aDeleteAssign);
        $this->Storage->remove($aDeleteGroup);

        // End transaction
        $this->Storage->commit();

        return $Context->hasNoErrors();
    }

    // --------------------------------------------------------------------

    /**
     * Get member groups of uid
     *
     * @access  public
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getMemberGroupsOfUid($nId) {

        $this->getAllGroups();

        // Container for users member groups
        $MemberGroups = new Vector();

        $sQuery = " SELECT  group_id
                      FROM  cowiki_user_group
                     WHERE  user_id = '".$nId."'
                  ORDER BY  group_id";

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Obj = $this->Groups->findByPropertyValue(
                      'groupId',
                      (int)$aData['group_id']
                   );

            if (is_object($Obj)) {
                $MemberGroups->add($Obj);
            }
        }

        $this->Storage->freeResult($rResult);

        return $MemberGroups;
    }

    // --------------------------------------------------------------------

    /**
     * Store member groups of uid
     *
     * @access  protected
     * @param   object
     * @param   integer
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Groups"
     * @todo    [D11N]  Check the parameter type of "$nId"
     */
    protected function storeMemberGroupsOfUid($Groups, $nId) {

        // Begin transaction
        $this->Storage->begin();

        // Clean up and delete memeber groups frist
        $aDelete = array(
            'table' => $this->sAssignTable,
            'where' => "user_id = '".$nId."'"
        );

        // Execute delete (remove)
        $this->Storage->remove($aDelete);

        // ----------------------------------------------------------------

        // Store user <-> group assignments
        $It = $Groups->iterator();

        while ($Obj = $It->next()) {
            $aFields = array(
                'user_id'  => $nId,
                'group_id' => $Obj->get('groupId')
            );

            // Set further data for insert
            $aInsert = array(
                'table'  => $this->sAssignTable,
                'fields' => $aFields
            );

            // Execute insert
            $this->Storage->insert($aInsert);
        }

        // End transaction
        $this->Storage->commit();
    }

} // of class

/*
    Scribere proposui de contemptu mundano
    ut degentes seculi non mulcentur in vano.
    Iam est hora sugere a sompno mortis pravo.

    Vita brevis breviter in brevi finietur
    mors venit velociter quae neminem veretur
    Omnia mors perimit et nulli miseritur.

    Ad mortem festinamus!
    Peccare desistamus!

    Ni conversus fueris et sicut puer factus
    et vitam mutaveris in meliores actus
    intrare non poteris regnum Dei beatus.

    Tuba cum sonuerit dies erit extrema.
    Et iudex advenerit vocabit sempiterna
    electos in patria, prescitos ad inferna.

    Ad mortem festinamus!
    Peccare desistamus!
*/

?>
