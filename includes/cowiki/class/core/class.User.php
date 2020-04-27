<?php

/**
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
 * coWiki - User class
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
class User extends Object {
    protected
        $MemberGroups = null,
        $bGotMemberGroups = false;

    /**
     * Set member groups
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Vector"
     */
    public function __construct() {}

    /**
     * Set member groups
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Vector"
     */
    public function setMemberGroups($Vector) {
        $this->MemberGroups = $Vector;
        $this->bGotMemberGroups = true;
    }

    /**
     * Get member groups
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
    public function getMemberGroups() {

        // Be lazy, as we do not need all member groups very often
        // This is not very nice, but saves queries to the storage container

        if (!$this->bGotMemberGroups) {
            $UserDAO = RuntimeContext::getInstance()->getUserDAO();
            $nId = $this->get('userId');

            // Exclude builtin users
            if ($nId !== 0 && $nId !== 65535) {
                $this->MemberGroups = $UserDAO->getMemberGroupsOfUid($nId);
                $this->bGotMemberGroups = true;
            }
        }

        return $this->MemberGroups;
    }

} // of class

/*
    Gib mir mein Destillat
    Gib mir mein Alltagstod
    Gib mir mein Gnadenbrot
    Zur Ewigkeit
*/

?>
