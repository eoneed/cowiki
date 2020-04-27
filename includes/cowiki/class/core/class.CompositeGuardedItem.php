<?php

/**
 *
 * $Id: class.CompositeGuardedItem.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Composite guarded item class
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
class CompositeGuardedItem extends Object {

    const ITEM_READABLE   = 1;
    const ITEM_WRITABLE   = 2;
    const ITEM_EXECUTABLE = 3;

    // FIX: Cache access status in member variables, check it only once!
    // Eg. isUserReadable() must return the value of the member variable then.

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
        parent::__construct();
    }

    /**
     * Set access by umask
     *
     * @access  public
     * @param   integer
     * @param   integer
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nDefaultAccess"
     * @todo    [D11N]  Check the parameter type of "$nUmask"
     */
    public function setAccessByUmask($nDefaultAccess, $nUmask) {

        $nAccess = $nDefaultAccess &~ octdec($nUmask);

        $this->set('isUserReadable',    $nAccess & 0400);
        $this->set('isUserWritable',    $nAccess & 0200);
        $this->set('isUserExecutable',  $nAccess & 0100);

        $this->set('isGroupReadable',   $nAccess & 040);
        $this->set('isGroupWritable',   $nAccess & 020);
        $this->set('isGroupExecutable', $nAccess & 010);

        $this->set('isWorldReadable',   $nAccess & 04);
        $this->set('isWorldWritable',   $nAccess & 02);
        $this->set('isWorldExecutable', $nAccess & 01);
    }

    /**
     * Get access mode as string
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getAccessModeAsString() {
        $sStr  = $this->get('isUserReadable')    ? 'r' : '-';
        $sStr .= $this->get('isUserWritable')    ? 'w' : '-';
        $sStr .= $this->get('isUserExecutable')  ? 'x' : '-';

        $sStr .= $this->get('isGroupReadable')   ? 'r' : '-';
        $sStr .= $this->get('isGroupWritable')   ? 'w' : '-';
        $sStr .= $this->get('isGroupExecutable') ? 'x' : '-';

        $sStr .= $this->get('isWorldReadable')   ? 'r' : '-';
        $sStr .= $this->get('isWorldWritable')   ? 'w' : '-';
        $sStr .= $this->get('isWorldExecutable') ? 'x' : '-';

        return $sStr;
    }

    /**
     * Is readable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isReadable() {
        // Check if current user may enter this node
        if (!$this->isParentExecutable()) {
            return false;
        }

        // If user may enter this node, check if it is readable for him
        return $this->checkPerm(self::ITEM_READABLE, $this);
    }

    /**
     * Is parent readable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isParentReadable() {
        $Parent = $this->get('parent');

        // Is there any parent? Return 'true'(!) if not, because we do not
        // care then.
        if (!$Parent) {
            return true;
        }

        // Check if current user may enter the parent node
        if (!$Parent->isExecutable()) {
            return false;
        }

        return $this->checkPerm(self::ITEM_READABLE, $Parent);
    }

    /**
     * Is writable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isWritable() {
        // Check if current user may enter this node
        if (!$this->isParentExecutable()) {
            return false;
        }

        // If user may enter this node, check if it is writable for him
        return $this->checkPerm(self::ITEM_WRITABLE, $this);
    }

    /**
     * Is parent writable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isParentWritable() {
        $Parent = $this->get('parent');

        // Is there any parent? Return 'true'(!) if not, because we do not
        // care then.
        if (!$Parent) {
             return true;
        }

        // Check if current user may enter the parent node
        if (!$Parent->isExecutable()) {
            return false;
        }

        return $this->checkPerm(self::ITEM_WRITABLE, $Parent);
    }

    /**
     * Is executable
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
    public function isExecutable() {
        return $this->checkPermAlongPath(self::ITEM_EXECUTABLE, $this);
    }

    /**
     * Is parent executable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isParentExecutable() {
        if (!$this->has('parent')) {
            return true;
        }

        return $this->checkPermAlongPath(
                    self::ITEM_EXECUTABLE,
                    $this->get('parent')
               );
    }

    /**
     * Is editable
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function isEditable() {
        // Check if current user may enter this node
        if (!$this->isParentExecutable()) {
            return false;
        }

        // Check if current user may *edit* this node. That means:
        // only user/group/member may edit this node - world is not
        // allowed to. This is used for directory nodes.
        return $this->checkPerm(self::ITEM_WRITABLE, $this, false);
    }

    /**
     * Check permissions along the path to the root node
     *
     * @access  protected
     * @param   string
     * @param   object
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function checkPermAlongPath($sType, $Node, $bAccess = true) {
        $CurrUser = RuntimeContext::getInstance()->getCurrentUser();

        // If user is root or node is only a top dummy container with id 0
        if ($CurrUser->isRoot()
            || (!$Node->has('parent') && $Node->get('id') === 0)) {
            return true;        // free access
        }

        $bFlag = $this->checkPerm($sType, $Node);

        // Once set to "false" bAccess can not be "true" anymore, or in
        // other words: bAccess can only be changed once if it is "true"
        $bAccess = $bAccess ? $bFlag : $bAccess;

        // If bAccess is still true, ask the parent node for its permissions
        if ($bAccess) {
            if ($Node->has('parent')) {
                $bAccess = $this->checkPermAlongPath(
                                    $sType,
                                    $Node->get('parent'),
                                    $bAccess
                                 );
            }
        }

        return $bAccess;
    }

    /**
     * Check perm
     *
     * @access  protected
     * @param   integer
     * @param   object
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nType"
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function checkPerm($nType, $Node, $bCheckWwrite = true) {
        $CurrUser = RuntimeContext::getInstance()->getCurrentUser();

        // If user is root
        if ($CurrUser->isRoot()) {
            return true;        // free access for user "root"
        }

        $bFlag = false;
        $MemberIt = $CurrUser->getMemberGroups()->iterator();

        // ----------------------------------------------------------------

        switch ($nType) {

            case self::ITEM_READABLE:

                // Is this node world readable?
                if ($Node->get('isWorldReadable')) {
                    $bFlag = true;
                    break;
                }

                // If this node belongs to user, and is user readable?
                if ($CurrUser->get('userId') === $Node->get('userId')) {
                    if ($Node->get('isUserReadable')) {
                        $bFlag = true;
                        break;
                    }
                }

                // Check all groups where user belongs to - if group is
                // readable
                if ($Node->get('isGroupReadable')) {
                    if ($CurrUser->get('groupId') === $Node->get('groupId')) {
                        $bFlag = true;
                        break;
                    }

                    // Check for member groups
                    $MemberIt->reset();
                    while ($Obj = $MemberIt->next()) {
                        if ($Obj->get('groupId') === $Node->get('groupId')) {
                            $bFlag = true;
                            break;
                        }
                    }
                }
                break;

            case self::ITEM_WRITABLE:

                // Check world-writable - not for isEditable(), which does
                // not care about world-access bits
                if ($bCheckWwrite) {

                    // Is this node world writable?
                    if ($Node->get('isWorldWritable')) {
                        $bFlag = true;
                        break;
                    }
                }

                // If this node belongs to user, and is user writable?
                if ($CurrUser->get('userId') === $Node->get('userId')) {
                    if ($Node->get('isUserWritable')) {
                        $bFlag = true;
                        break;
                    }
                }

                // Check all groups where user belongs to - if group is
                // writable
                if ($Node->get('isGroupWritable')) {
                    if ($CurrUser->get('groupId') === $Node->get('groupId')) {
                        $bFlag = true;
                        break;
                    }

                    // Check for member groups
                    $MemberIt->reset();
                    while ($Obj = $MemberIt->next()) {
                        if ($Obj->get('groupId') === $Node->get('groupId')) {
                            $bFlag = true;
                            break;
                        }
                    }
                }
                break;

            case self::ITEM_EXECUTABLE:

                // Is this node world executable?
                if ($Node->get('isWorldExecutable')) {
                    $bFlag = true;
                    break;
                }

                // If this node belongs to user, and is user executable?
                if ($CurrUser->get('userId') === $Node->get('userId')) {
                    if ($Node->get('isUserExecutable')) {
                        $bFlag = true;
                        break;
                    }
                }

                // Check all groups where user belongs to - if group is
                // executable
                if ($Node->get('isGroupExecutable')) {
                    if ($CurrUser->get('groupId') === $Node->get('groupId')) {
                        $bFlag = true;
                        break;
                    }

                    // Check for member groups
                    $MemberIt->reset();
                    while ($Obj = $MemberIt->next()) {
                        if ($Obj->get('groupId') === $Node->get('groupId')) {
                            $bFlag = true;
                            break;
                        }
                    }
                }
                break;
        }

        return $bFlag;
    }

} // of class

/*
    Be a thunderstorm in the north,
    Be a hurricane in the south,
    Be a typhoon in the east,
    Be a tornado in the west

    My little cherubim.
*/

?>
