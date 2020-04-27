<?php

/**
 *
 * $Id: status.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    htdocs
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19 $
 *
 */

// Version constants
@include 'version.php';

// ------------------------------------------------------------------------

$aStatus[0]  =  'Unknown error.';
$aReason[0]  =  '';
$aSolve [0]  =  '';

// ------------------------------------------------------------------------

$aStatus[110]=  'Program terminated.';
$aReason[110]=  'This status is informational and appears mostly after a
                fatal error occured. Program execution has stopped probably
                after a previous serious error.';
$aSolve [110]=  'Not necessary, look up the serious error status instead
                (if any).';

// ------------------------------------------------------------------------

$aStatus[111]=  'Program resumed.';
$aReason[111]=  'This status is informational and appears mostly after a
                fatal but recoverable error has occured. Program execution
                has been resumed after a serious error.';
$aSolve [111]=  'Look up the serious error status code and its solution
                (if any).';

// ------------------------------------------------------------------------

$aStatus[115]=  'Login refused.';
$aReason[115]=  'Your credentials (login and/or password) are wrong or
                expired and has been rejected.';
$aSolve [115]=  'Make sure your caps-lock is off: credentials are
                casesensitive. If it does not help, ask the
                administrator for what is wrong with your account.';

// ------------------------------------------------------------------------

$aStatus[116]=  'Account has expired.';
$aReason[116]=  'This account has expired, it is not valid anymore.';
$aSolve [116]=  'Ask your administrator to extend the validity of your
                account.';

// ------------------------------------------------------------------------

$aStatus[117]=  'Account is locked. Credentials rejected.';
$aReason[117]=  'This account has been locked for security reasons after
                several unsuccessful logins within a "period of time".
                This lock will be suspended after a time period (minutes,
                hours or days), which is defined by the '.COWIKI_NAME.'
                administrator.

                <br /><br />

                All available data (access time, IP (ISP) etc.) of the
                client that caused the lock is logged and can be used to
                trace back any abuse attempts (intentional or unintentional).';
$aSolve [117]=  'Ask your administrator or wait.';

// ------------------------------------------------------------------------

$aStatus[118]=  'Account is not active. Credentials rejected.';
$aReason[118]=  'This account has been deactivated.';
$aSolve [118]=  'Ask your administrator.';

// ------------------------------------------------------------------------

$aStatus[210]=  'Task successfully completed.';
$aReason[210]=  '';
$aSolve [210]=  '';

// ------------------------------------------------------------------------

$aStatus[310]=  'Internal plugin error.';
$aReason[310]=  'This error should not occure. It is reserved for
                future use.';
$aSolve [310]=  'none';

// ------------------------------------------------------------------------

$aStatus[310]=  'Plugin warning.';
$aReason[310]=  'This is an informational warning.';
$aSolve [310]=  'none';

// ------------------------------------------------------------------------

$aStatus[311]=  'Plugin not found.';
$aReason[311]=  'The requested plugin was not found or could
                not be loaded.  An other reason might be a syntax error
                within the plugin component.';
$aSolve [311]=  'Make sure you typed the plugin name correctly and
                that the plugin resides in its directory (<tt>plugins/</tt>).
                When calling plugins you have to omit their file extension.
                <em>Plugin names are not casesensitve</em>.

                <br /><br />

These calls are wrong ...
<pre>
{plugin PrivateFrontBreadcrumbDisplay<b>.php</b>}
&lt;plugin <b>Custom.</b>UsefulPlugin<b>.php</b>>
</pre>
... and these are correct
<pre>
{plugin PrivateFrontBreadcrumbDisplay}
&lt;plugin UsefulPlugin>
</pre>';

// ------------------------------------------------------------------------

$aStatus[312]=  'Context interface mismatch.';
$aReason[312]=  'The API version used by a plugin is not correct.
                You have tried to invoke a plugin with an incompatible
                API, thus it can not be executed.';
$aSolve [312]=  'Get a compatible version of the plugin you require.';

// ------------------------------------------------------------------------

$aStatus[313]=  'Missing plugin parameter.';
$aReason[313]=  'A plugin invocation misses a mandatory parameter.';
$aSolve [313]=  'Please make sure, that you provide the parameter and watch
                out for typos.';

// ------------------------------------------------------------------------

$aStatus[314]=  'Malformed plugin parameter.';
$aReason[314]=  'A plugin invocation ran into a wrong formated parameter
                (e.g. an illegal URI).';
$aSolve [314]=  'Take a look on the parameter/value format for the plugin.';

// ------------------------------------------------------------------------

$aStatus[315]=  'Foreign host not responding.';
$aReason[315]=  'A foreign net resource is not reachable.';
$aSolve [315]=  'Is your '.COWIKI_NAME.' "online"?';

// ------------------------------------------------------------------------

$aStatus[316]=  'Format not supported.';
$aReason[316]=  COWIKI_NAME.' tried to load and parse a file and did not
                recognized its format properly.';
$aSolve [316]=  'Make sure that the format of the file is proper due to
                given standards. If this file has been loaded from a
                foreign resource, please check (or let check) if this
                resource is broken.
                <br /><br />
                If this error was caused by a plugin invocation, remove the
                invocation.';

// ------------------------------------------------------------------------

$aStatus[320]=  'Template not found.';
$aReason[320]=  COWIKI_NAME.' tried to load a HTML template but failed to
                do so. Apparently a required template was found not in its
                place.';
$aSolve [320]=  'Check if your '.COWIKI_NAME.' installation is complete.';

// ------------------------------------------------------------------------

$aStatus[321]=  'Template syntax error.';
$aReason[321]=  'The '.COWIKI_NAME.' template engine has detected a not
                recoverable error.';
$aSolve [321]=  'Check (or let check) your template syntax.';

// ------------------------------------------------------------------------

$aStatus[322]=  'Template variable is an array.';
$aReason[322]=  '';
$aSolve [322]=  '';

// ------------------------------------------------------------------------

$aStatus[330]=  'Plugin reflection not allowed.';
$aReason[330]=  'The administrator of this software does not allow
                 '.COWIKI_NAME.' plugins to reflect themself.';
$aSolve [330]=  'You will have ask the administrator directly about
                the information you desire.';

// ------------------------------------------------------------------------

$aStatus[403]=  'Forbidden.';
$aReason[403]=  'You lack of permissions to access this resource.';
$aSolve [403]=  'Log in to get the appropriate permissions or ask your
                administrator to get the permissions you think you require.
                <br />
                <br />
                If you have clicked on a link with a trailing question mark
                (like <u>A foo document</u><strong>?</strong>) and get this
                error, it means that the linked document does not yet exist
                and you do not have the appropriate access to create one in
                this directory.';

// ------------------------------------------------------------------------

$aStatus[404]=  'Not found.';
$aReason[404]=  'As simple as it is: the requested resource is passed on.
                 It is no more. It has ceased to be. It is expired and gone
                 to meet its maker. It rests in peace.';
$aSolve [404]=  'Facing this fact you can do nothing.
                <br /><br />

                ';

// ------------------------------------------------------------------------

$aStatus[410]=  'Web not found.';
$aReason[410]=  'Your database contains no webs.';
$aSolve [410]=  'Log in, go to the administration panel and create
                a web.';

// ------------------------------------------------------------------------

$aStatus[411]=  'Web already exists.';
$aReason[411]=  'A web with an equal name already exists in the database.
                 Web names are not casesensitve.';
$aSolve [411]=  'Try another name for your new web.';

// ------------------------------------------------------------------------

$aStatus[412]=  'Missing web name.';
$aReason[412]=  'You did not enter a name for the web, which is mandatory.';
$aSolve [412]=  'Enter a name for a new web.';

// ------------------------------------------------------------------------

$aStatus[420]=  'Directory already exists (in this web).';
$aReason[420]=  'Directories must have unique names within a web.';
$aSolve [420]=  'Choose an other name for your directory.';

// ------------------------------------------------------------------------

$aStatus[421]=  'Missing directory name.';
$aReason[421]=  'You did not enter a name for the directory which is
                 mandatory.';
$aSolve [421]=  'Enter a name for your directory.';

// ------------------------------------------------------------------------

$aStatus[422]=  'Document already exists (in this web).';
$aReason[422]=  'Documents must have unique names (titles) within a web.';
$aSolve [422]=  'Choose an other name for your document.';

// ------------------------------------------------------------------------

$aStatus[423]=  'Missing document name.';
$aReason[423]=  'You did not enter a name for the document which is
                 mandatory.';
$aSolve [423]=  'Enter a name for your document.';

// ------------------------------------------------------------------------

$aStatus[424]=  'Missing subject.';
$aReason[424]=  'You did enter a subject for your posting.';
$aSolve [424]=  'Enter a suggestive subject for your posting.';

// ------------------------------------------------------------------------

$aStatus[425]=  'Missing content.';
$aReason[425]=  'What do you think you are doing here? Nonsense? Are you
                 serious?';
$aSolve [425]=  'Try it with your keyboard and the big text area.';

// ------------------------------------------------------------------------

$aStatus[430]=  'No items selected.';
$aReason[430]=  'You did not select any items for your action.';
$aSolve [430]=  'Please select (check) the items you want to act with.';

// ------------------------------------------------------------------------

$aStatus[431]=  'Can\'t delete. Directory is not empty.';
$aReason[431]=  'The directory could not be deleted because it contains
                one or more directories or documents.';
$aSolve [431]=  'Remove all content from the directory and delete it then.';

// ------------------------------------------------------------------------

$aStatus[432]=  'Can\'t recover.';
$aReason[432]=  'The document (or directory) could not be recovered because
                it is not editable for you or the old location does not
                exist anymore.';
$aSolve [432]=  '';

// ------------------------------------------------------------------------

$aStatus[440]=  'Resource has been changed meanwhile.';
$aReason[440]=  'Oops... somebody was faster. The document (or directory)
                you are working on was changed by somebody while you\'ve
                edited it.';
$aSolve [440]=  'Usually you will be warned only once - if you
                insist on saving your data, click the "save" button (again).
                <br />
                <br />
                If you are curious to see what has been changed, open an
                other browser window, and call the node URI and compare.';

// ------------------------------------------------------------------------

$aStatus[441]=  'Data exceeds available space.';
$aReason[441]=  'The data to be saved is too large.';
$aSolve [441]=  'Split or shorten your data.';

// ------------------------------------------------------------------------

$aStatus[445]=  'Unbalanced quotation ratio.';
$aReason[445]=  'You have quoted too much and/or wrote too little.';
$aSolve [445]=  'Shorten the quoted text to a <i>minimum</i> that is needed
                to understand what matters, refer solely to what
                is absolutely required to understand the thread or write
                more text yourself to increase the ratio.';

// ------------------------------------------------------------------------

$aStatus[446]=  'Broken quotation.';
$aReason[446]=  'You quotation is broken, probably you have placed your
                text on top the quotation.';
$aSolve [446]=  'Write your text <i>beneath</i> the quotation your refer to.

<br /><br />
<strong>Bad example</strong> (which causes this error):

<pre>
Hey, just do it that way!

> this is the text you are referring to ...
> this is the text you are referring to ...
>
> TIA, foobar
</pre>

<strong>Good example:</strong>
<pre>
Hello,

> this is the text you are referring to ...
> this is the text you are referring to ...

Hey, just do it that way!

> TIA, foobar

No problem, hope that helps, regards ...
</pre>
';

// ------------------------------------------------------------------------

$aStatus[450]=  'Unspecified form validation error.';
$aReason[450]=  '';
$aSolve [450]=  '';

// ------------------------------------------------------------------------

$aStatus[451]=  'Missing data.';
$aReason[451]=  'You did not enter data that has been required.';
$aSolve [451]=  'Think about what you have (not) done.';

// ------------------------------------------------------------------------

$aStatus[452]=  'Malformed email.';
$aReason[452]=  'The email you have entered is not valid.
                Either your email is not syntactically recognized or
                the administrator decided to disallow the domain name
                of your address.
                This might e.g. happen if you use an email address of a
                mass email provider to anonymize yourself.';
$aSolve [452]=  'Use an other email address. Use your main email address.';

// ------------------------------------------------------------------------

$aStatus[453]=  'Date out of range.';
$aReason[453]=  'The date range you have entered seems to be invalid.';
$aSolve [453]=  'Enter a valid date range.';

// ------------------------------------------------------------------------

$aStatus[454]=  'Login already exists.';
$aReason[454]=  'A user with this login name already exists in this
                '.COWIKI_NAME.' installation.';
$aSolve [454]=  'Please choose an other login name.';

// ------------------------------------------------------------------------

$aStatus[455]=  'User id (uid) already exists.';
$aReason[455]=  'The user identifier is already there and can not be
                changed.';
$aSolve [455]=  'Use an other user id.';

// ------------------------------------------------------------------------

$aStatus[456]=  'Unsecure password.';
$aReason[456]=  'The password you have entered can\'t be accepted.';
$aSolve [456]=  'Your password is probably too short.
                 Choose an other - better - password. ';

// ------------------------------------------------------------------------

$aStatus[457]=  'Group name already exists.';
$aReason[457]=  'A user group with the name you have chosen already exists.';
$aSolve [457]=  'Please use an other group name - or rename the existing
                one first and try again.';

// ------------------------------------------------------------------------

$aStatus[458]=  'Group id (gid) already exists.';
$aReason[458]=  'The user group identifier is already there and can not be
                changed.';
$aSolve [458]=  'Use an other user group id.';

// ------------------------------------------------------------------------

$aStatus[459]=  'Login required.';
$aReason[459]=  'Login required';
$aSolve [459]=  'Login required';

// ------------------------------------------------------------------------

$aStatus[460]=  'Root is not allowed.';
$aReason[460]=  '';
$aSolve [460]=  '';

// ------------------------------------------------------------------------

$aStatus[510]=  'File not found or not readable.';
$aReason[510]=  'Either a file has not been found or it is not readable
                for the webserver process (uid/gid). An other reason might
                be a syntax error within the required file.';
$aSolve [510]=  'Make sure the file is in its path and is
                readable for the webserver process. If so, check
                (or let check) if the file code is syntactically correct.';

// ------------------------------------------------------------------------

$aStatus[511]=  'Directory not found or not readable.';
$aReason[511]=  'Either a directory has not been found or it is not readable
                for the webserver process (uid/gid). An other reason might
                be a insecure location of the required directory.';
$aSolve [511]=  'Make sure the directory is in its path and is
                readable for the webserver process. If so, check
                (or let check) if the directory is on a secure place.';

// ------------------------------------------------------------------------

$aStatus[512]=  'File or directory not writeable.';
$aReason[512]=  COWIKI_NAME.' could not write to a file.';
$aSolve [512]=  'Adjust read/write access to the particular directory or
                file. Check if the volume is not full.';

// ------------------------------------------------------------------------

$aStatus[515]=  'Configuration file not found or not readable.';
$aReason[515]=  'Either the configuration file has not been found or it is
                not readable for the webserver process (uid/gid).';
$aSolve [515]=  'Make sure you have copied the
                <tt>includes/'.COWIKI_NAME.'/core.conf-dist</tt> file to
                <tt>core.conf</tt>. This file have to be readable for the
                webserver process (uid/gid). An other reason might be a
                syntax error within this configuration file. Make sure
                the file is in its path and is readable for the webserver
                process. If so, check (or let check) if the file code is
                syntactically correct.';

// ------------------------------------------------------------------------

$aStatus[516]=  'Missing main configuration directive or version mismatch.';
$aReason[516]=  'You main configuration file (<tt>core.conf</tt>) is not
                complete, lacks a required entry or is wrong version.
                Maybe you have updated '.COWIKI_NAME.' without adjusting
                the configuration file?';
$aSolve [516]=  'Please use the
                <tt>includes/'.COWIKI_NAME.'/core.conf-dist</tt>
                file as example. Make sure you have copied the
                <tt>includes/cowiki/core.conf-dist</tt> completely to
                <tt>core.conf</tt>

                <br /><br />

                This may be especially necessary, if you have updated your
                existing '.COWIKI_NAME.' installation.';

// ------------------------------------------------------------------------

$aStatus[530]=  'Unsupported operating system.';
$aReason[530]=  'This application is probably not designed to run on your
                platform, because either your platform lacks of important
                features of an operating system or has been not
                recognized as such.';
$aSolve [530]=  'Use Linux or any Unix flavours to make yourself and
                '.COWIKI_NAME.' happy.';

// ------------------------------------------------------------------------

$aStatus[531]=  'PHP version too old.';
$aReason[531]=  'You are using an (too) old version of PHP.';
$aSolve [531]=  'Upgrade your PHP to at least version '
                .COWIKI_REQUIRED_PHP_VERSION.'. Any older
                version will cause subtle bugs within '.COWIKI_NAME.'
                routines. You might download the latest version of PHP from
                <a target="_blank"
                href="http://www.php.net/">http://www.php.net/</a>

                <br /><br />

                '.COWIKI_NAME.' will not run properly on any prior PHP
                version!
                <em>Do not</em> submit occuring '.COWIKI_NAME.' bugs beyond
                the required PHP version! Don\'t waste our time, thank you.';

// ------------------------------------------------------------------------

$aStatus[532]=  'PHP safe mode is on.';
$aReason[532]=  'PHP is running in <tt>safe mode</tt> (which is default).
                This means, it is restricted in its functionality for
                security reasons.
                With the default <tt>safe mode=on</tt> '.COWIKI_NAME.'
                won\'t run properly and you might (or will) lose data!';
$aSolve [532]=  'Services that require <tt>safe mode=on</tt> are not
                used in this version, so ignore this message.';

// ------------------------------------------------------------------------

$aStatus[533]=  'crypt() function not implemented.';
$aReason[533]=  'Your PHP does not support the <tt>crypt()</tt> function
                which is urgently required to encrypt passwords.';
$aSolve [533]=  'Install a PHP version with <tt>crypt()</tt> implemented.';

// ------------------------------------------------------------------------

$aStatus[534]=  'Standard DES encryption not implemented.';
$aReason[534]=  'Your PHP is not able to handle standard DES-based
                encryption with a two character salt.';
$aSolve [534]=  'Install a PHP version with standard DES encryption
                implemented.';

// ------------------------------------------------------------------------

$aStatus[535]=  'Configuration mismatch.';
$aReason[535]=  'Your httpd and/or vhost and/or PHP setups are not
                 configured as expected.';
$aSolve [535]=  'If you encounter this error, please provide the output of
                 <tt>&lt;?php phpinfo(); ?&gt;</tt> <i>on your server</i>
                 and get in touch with the '.COWIKI_NAME.' developers via
                 the developer mailing list. Please <i>do not</i> send the
                 output of your <tt>phpinfo()</tt> directly to this list.
                 The description of your problem and the URL to your server
                 and its <tt>phpinfo()</tt> is adequate.
                 <br /><br />
                 Do not expect any help if you pollute the developer list.
                 <br /><br />
                 <i>Also, please provide your '.COWIKI_NAME.' version (which
                 is '.COWIKI_VERSION.', '.COWIKI_VERSION_DATE.'
                 ).</i>';

// ------------------------------------------------------------------------

$aStatus[540]=  'Call to undefined PHP extension.';
$aReason[540]=  COWIKI_NAME.' tries to use PHP extensions that are not
                compiled in your PHP-binary. Required functions were not
                found.';
$aSolve [540]=  'Update your PHP or compile in the required extensions.
                Check the <tt>[.DOCUMENT]</tt>, <tt>[.AUTH]</tt> and
                <tt>[.USER]</tt> sections in your <tt>core.conf</tt>.
                Maybe you are trying to use a not implemented service
                (such as MYSQL, LDAP etc.) for storage, authentification
                or user resolving. Lack of the GD library 2.0.1 or later
                might cause this error too.

                <br />
                <br />

                a) change the entries as needed

                <br />
                <br />

                b) use a small script like ...
<pre>
    &lt;?php phpinfo(); ?&gt;
</pre>
                ... to check your installed extensions and/or

                <br />
                <br />

                c) ask your friend, your adminstrator or your ISP
                for further information.';

// ------------------------------------------------------------------------

$aStatus[550]=  'Unsupported storage service API.';
$aReason[550]=  'Either your <tt>core.conf</tt> is broken, or you are
                trying to use an API layer which is not handled.';
$aSolve [550]=  'Enter a correct API layer identification scheme in your
                <tt>core.conf</tt>.

                <br /><br />

                An example for your <tt>core.conf</tt>:
<pre>
[.DOCUMENT]

  RESOURCE = &quot;mysql://LOGIN:PASSWD@localhost/DATABASE&quot;
</pre>';

// ------------------------------------------------------------------------

$aStatus[551]=  'Failed to connect storage service.';
$aReason[551]=  COWIKI_NAME.' either can not connect your storage service
                (mainly database), or your credentials or database name are
                wrong.';
$aSolve [551]=  'a) make sure your PHP scripts are basically able to access
                your database:
                <ul>
                    <li>is your database up and running?</li>
                    <li>are you sure, really?</li>
                    <li>did you reload your (MySQL-) database after you
                        have created a new user account?
                    </li>
                    <li>did you granted proper privileges (SELECT, INSERT
                        etc.) to your database or user before you reloaded
                        it?
                    </li>
                </ul>

                b) check user, password and database name settings in the
                   <tt>core.conf</tt> file (<tt>[.DOCUMENT]</tt> section).

                <br />
                <br />

                c) make sure you have created the '.COWIKI_NAME.' tables in
                   your database. You will find the table schemas in the
                   <tt>/misc/database/</tt> directory.';

// ------------------------------------------------------------------------

$aStatus[552]=  'Unknown authentification service callback.';
$aReason[552]=  'The '.COWIKI_NAME.' handler class that verifies the
                authentification credentials of a user was not found.';
$aSolve [552]=  'Please check the <tt>[.AUTH]</tt> <tt>HANDLER</tt> entry
                in the <tt>core.conf</tt> file.';

// ------------------------------------------------------------------------

$aStatus[553]=  'Failed to connect authentification service.';
$aReason[553]=  COWIKI_NAME.' could not connect the authentification
                service defined to check the users credentials.';
$aSolve [553]=  'Please change the <tt>[.AUTH]</tt> <tt>RESOURCE</tt> entry
                in the <tt>core.conf</tt> file adequately to get rid of
                this error.';

// ------------------------------------------------------------------------

$aStatus[554]=  'Unsupported user service API.';
$aReason[554]=  COWIKI_NAME.' is not able to use the access protocol/scheme
                you defined in <tt>[.USER]</tt> <tt>RESOURCE</tt> in your
                <tt>conf.conf</tt> file.';
$aSolve [554]=  'Please check the URL scheme parameter (e.g. mysql://) in
                the <tt>[.USER]</tt> <tt>RESOURCE</tt> entry in the
                <tt>conf.conf</tt> file.';

// ------------------------------------------------------------------------

$aStatus[555]=  'Failed to connect user service.';
$aReason[555]=  'The resource defined in <tt>[.USER]</tt> <tt>RESOURCE</tt>
                could not be accessed.';
$aSolve [555]=  'Your credentials (login and password) in your
                <tt>conf.conf</tt> are probably wrong.';

// ------------------------------------------------------------------------

$aStatus[560]=  'Illegal query error.';
$aReason[560]=  'An illegal service query raised this error.';
$aSolve [560]=  'Check and fix your query code. If you think, this it is
                not your fault, please report a bugreport to the developers.
                Take a look at the <a href="#top">head</a> of this
                document for the URI of the bug-report-eating-machine.';

// ------------------------------------------------------------------------

$aStatus[580]=  'Extension "http" installed.';
$aReason[580]=  'coWiki and the PHP Extension "http" won\'t work together.';
$aSolve [580]=  'Disable the extension.';

// ------------------------------------------------------------------------

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title><?php echo COWIKI_NAME; ?> status and error codes</title>

<style type="text/css" media="screen">
<!--
    H1, H2, H3, P, LI, DL, TD {
        font-family: Helvetica, Arial, sans-serif;
    }
    PRE, TT {
        font-family: "Courier New", Courier, monospace;
    }
-->
</style>
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#000099" vlink="#000099">

<a name="top"></a>

<h1><?php echo COWIKI_NAME; ?> status and error codes</h1>

<h2>Valid for version <?php echo COWIKI_VERSION.', '.COWIKI_VERSION_DATE; ?></h2>
<hr />

<p>
    This document explains the <?php echo COWIKI_NAME; ?> status and error
    codes and how to solve errors if necessary. Explanation of the status
    codes might be incomplete. If you find any bugs in this document or in
    the application itself, please report them at
    <a href="http://bugs.cowiki.org">http://bugs.cowiki.org</a>.
</p>
<p>
    For more information about the
    <?php echo COWIKI_NAME . ' ' . COWIKI_SUBNAME; ?>
    itself, please visit its home at <a target="_blank"
    href="http://www.cowiki.org">http://www.cowiki.org</a>.
</p>

<hr />

<h2>Content:</h2>

<ul>
    <li><a href="#fam">Status families</a>
        <ul>
            <li><a href="#fam100">100 - 199: Informational</a></li>
            <li><a href="#fam200">200 - 299: Successful</a></li>
            <li><a href="#fam300">300 - 399: Plugin/Template error</a></li>
            <li><a href="#fam400">400 - 499: Client error</a></li>
            <li><a href="#fam500">500 - 599: Server error</a></li>
        </ul>
    </li>
    <li><a href="#status">Status codes explained</a>
        <ul>
<?php

    foreach ($aStatus as $k => $v) {
        echo    '<li>';
        echo        '<a href="#status'.$k.'">';
        echo            'Status '.$k.' - '.$v;
        echo        '</a>';
        echo    '</li>';
    }

?>
</ul>
    </li>
</ul>

<hr />

<a name="fam"></a>
<h3>Status families</h3>

<p>
    All status code numbers are divided into &quot;families&quot;
    which are borrowed from the HTT-Protocol.
    There are five code families:
</p>

<a name="fam100"></a>
<dl>
    <dt>
        <strong>Status codes 100 - 199: Informational</strong>
    </dt>
    <dd>
        Informational status codes mostly will give you a hint, what
        happend <em>after</em> an other status event or error.
        This could be e.g. a program termination information.
        These codes are not really errors, if they occur, then it
        is just for your - guess what :) - information. Look up the
        previous status code, which might describe the error, instead.
    </dd>
</dl>

<a name="fam200"></a>
<dl>
    <dt>
        <strong>Status codes 200 - 299: Successful</strong>
    </dt>
    <dd>
        Good to see those.
    </dd>
</dl>

<a name="fam300"></a>
<dl>
    <dt>
        <strong>Status codes 300 - 399: Plugin/Template error</strong>
    </dt>
    <dd>
        These codes are plugin- or template errors.
    </dd>
</dl>

<a name="fam400"></a>
<dl>
    <dt>
        <strong>Status codes 400 - 499: Client error</strong>
    </dt>
    <dd>
        Client errors occur if you or your contributors did something
        wrong. These errors are not really serious, probably you missed
        to fill out a form or maybe you lack of access permissions.
        Think about it logically and/or lookup the status code directly
        for a solution.
    </dd>
</dl>

<a name="fam500"></a>
<dl>
    <dt>
        <strong>Status codes 500 - 599: Server error</strong>
    </dt>
    <dd>
        If you have got any errors of this kind, you are in trouble.
        Server errors usually occur if something went really wrong
        - deep in the <?php echo COWIKI_NAME; ?> base (e.g. no storage-
        (database), authentification- or user-service connection).
        Anyhow, you probably will not be able to run <?php echo COWIKI_NAME; ?>
        as expected. As usual, read the description of the particular
        error code to get a solution. Ask your friend, an administrator
        or your ISP. Good luck ;)

        <br />
        <br />

        At least, you may look up in
        <a target="_blank" href="http://groups.google.com">groups.google.com</a>
        or
        <a target="_blank" href="http://www.google.com">google.com</a>
        with the relevant keywords - it might help.
    </dd>
</dl>

<hr />

<a name="status"></a>
<h3>Status codes explained</h3>

<?php

    foreach ($aStatus as $k => $v) {
        echo    '<a name="status'.$k.'"></a>';
        echo    '<table width="100%" cellpadding="5" cellspacing="0"';
        echo        ' style="border-width:1px;';
        echo                ' border-style: solid;';
        echo                ' border-top-color: #EEEEEE;';
        echo                ' border-left-color: #EEEEEE;';
        echo                ' border-right-color: #D4D4D4;';
        echo                ' border-bottom-color: #D4D4D4;';
        echo        '" border="0">';
        echo        '<tr bgcolor="#EEEEEE">';
        echo            '<td valign="top">';
        echo                '<strong>'.$k.' - '.$v.'</strong>';
        echo            '</td>';
        echo            '<td valign="top">';
        echo                'Solution:';
        echo            '</td>';
        echo        '</tr>';
        echo        '<tr>';
        echo            '<td width="50%" valign="top">';

        if (trim($aReason[$k]) == '') {
            echo    'n/a';
        } else {
            echo    $aReason[$k];
        }

        echo            '</td>';
        echo            '<td width="50%" valign="top">';

        if (trim($aSolve[$k]) == '') {
            echo    'n/a';
        } else {
            echo    $aSolve[$k];
        }

        echo            '</td>';
        echo        '</tr>';

        echo        '<tr>';
        echo            '<td colspan="2" align="right">';
        echo                '<a href="#top">Top</a>&nbsp;';
        echo            '</td>';
        echo        '</tr>';

        echo    '</table>';

        echo    '<br />';
    }

?>
<br />
<br />

<p>
    End of this
    <a target="_blank" href="http://www.cowiki.org">
    <?php echo COWIKI_NAME; ?>
    </a>
    file. If you have unanswered questions please refer to the
    documentation on
    <a target="_blank" href="http://www.cowiki.org">http://www.cowiki.org</a>.
</p>

</body>
</html>
