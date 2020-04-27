<?php

/**
 *
 * $Id: crypt.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     htdocs
 * @subpackage  admin
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

echo  '<html>';
    echo  '<head>';
    echo    '<title>coWiki password encryption</title>';
    echo    '<meta name="robots" content="noindex, nofollow" />';
    echo  '</head>';
    echo  '<body>';

    echo  '<h1>Generate a crypted password for coWiki root user.</h1>';

    echo  '<form action="crypt.php" method="post">';

    if (isset($HTTP_POST_VARS['pass']) || isset($_POST['pass'])) {
        $sPass = isset($HTTP_POST_VARS)
                 ?  trim($HTTP_POST_VARS['pass'])
                 :  trim($_POST['pass']);

        if (strlen($sPass) < 5) {
            echo '<p>Your password is too short (min. 5 chars).</p>';
        } else {

            $sCrypted = crypt($sPass);

            echo  '<p>';
            echo      'The encrypted password is: &nbsp; ';
            echo      '<b>'.$sCrypted.'</b>';
            echo  '</p>';
            echo  '<p>';
            echo    'Now, open your <tt>includes/cowiki/core.conf</tt>
                    file with a simple ASCII editor and scroll down to
                    the <tt>[.AUTH]</tt> section. Locate the
                    <tt>ROOT_PASSWD</tt> entry and replace its value
                    with the crypted password.';
            echo  '</p>';
            echo  '<p>';
            echo    'It should look like this: &nbsp; &nbsp;
                    <tt>ROOT_PASSWD = &quot;'.$sCrypted.'&quot;</tt>';
            echo  '</p>';
            echo  '<p>';
            echo    'With this new password you are able to change the
                    current coWiki user to <tt>root</tt> with
                    administrative privileges. It\'s recommended to
                    change the password periodically for security reasons.';
            echo  '</p>';
        }

      echo  '<hr noshade size="1" />';
      echo  '<h3>Encrypt an other password</h3>';
    }

    echo    '<p>';
    echo      'Enter a password to be crypted: ';
    echo      '<input name="pass" type="password" size="10" value="">';
    echo      '&nbsp;';
    echo      '<input type="submit" value="crypt">';
    echo    '</p>';
 
    echo  '</form>';

    echo  '</body></html>';

?>
