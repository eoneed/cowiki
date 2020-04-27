<?php

/**
 *
 * $Id: class.AuthManager.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Auth manager class
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
class AuthManager extends Object {

    private
        $CallbackObj = null;

    /**
     * Register callback
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$CallbackObj"
     */
    public function registerCallback($CallbackObj) {
        $this->CallbackObj = $CallbackObj;
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

        // Check for "root" user
        if ($sLogin == 'root') {
            $Registry = RuntimeContext::getInstance()->getRegistry();

            return
                crypt($sPasswd, $Registry->get('.AUTH_ROOT_PASSWD'))
                    == $Registry->get('.AUTH_ROOT_PASSWD');
        }

        if (!is_object($this->CallbackObj)) {
            return false;
        }

        return $this->CallbackObj->validate($sLogin, $sPasswd);
    }

} // of class

/*
    The path of the righteous man is beset on all sides by the iniquities
    of the selfish, and the tyranny of evil men.

    Blessed is he who in the name of charity and goodwill shepards the weak
    through the valley of darkness, for he is truly his brothers keeper and
    the finder of lost children.

    And I will strike down upon thee with great vengeance and furious anger,
    those who attempt to poison and destroy my brothers.

    And you will know my name is the Lord, when I lay my vengeance upon thee!
*/

?>
