<?php

/**
 *
 * $Id: class.CustomMailbox.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      Mailbox
 * #purpose:   This Version is a Simple Mailbox Reader with the Zend
 *             Framework. Further implemenations should go to manage
 *             group mailings and such like
 * #param:     dsn      Connection dsn, valid types are "mbox, mbox+folder,
 *                      maildir, maildir+folder, pop3, pop3+ssl, pop3+tls,
 *                      imap, imap+ssl, imap+tls"
 *                      Samples:
 *                      imap://user:pass@host:993/INBOX
 *                      imap+tls://user:pass@host:993/INBOX
 *                      imap+ssl://user:pass@host:993/INBOX
 *                      %LOGIN% will be replaced with the current user-login
 *                      %EMAIL% will be replaced with the current user-email
 *                      (required, default: '')
 * #param:     user     The connection user for IMAP and POP3
 * #param:     pass     The connection pass for IMAP and POP3
 * #param      ssl      whether to use ssl, 'SSL' or 'TLS'
 * #param      port     port for IMAP server [optional, default = 110]
 * #param      folder   select this folder [optional, default = 'INBOX']
 * #param:     zend 	The path to the ZendFramework
 * #caching:   not used
 * #comment:   none
 * #version:   1.1
 * #date:      04.08.2007
 * #author:    Alexander Klein, <a.klein@eoneed.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 * @copyright   (C) Alexander Klein, {@link http://ageless.de}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * A Simple Mailreader Plugin
 * Display ie mail from a neewsgroup, internal mailing list
 * In this state, there is no additional function as reading
 *
 * @package     plugin
 * @subpackage  custom
 * @access      public
 *
 * @author      Alexander Klein, <a.klein@eoneed.org>
 */
class CustomMailbox extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    // --------------------------------------------------------------------

    // Sort order
    const SORT_NAME_ASC = 1;
    const SORT_NAME_DSC = 2;

    const SORT_TIME_ASC = 3;
    const SORT_TIME_DSC = 4;

    const SORT_SIZE_ASC = 5;
    const SORT_SIZE_DSC = 6;

    // --------------------------------------------------------------------

    /**
     * Current Node Object
     */
    protected
        $Node                = null;

    /**
     * Mail Storage Object
     */
    protected
        $Mail                = null;

    protected
        $nIdent              = null;

    protected
        $sParamRoot          = null,
        $sParamMailType      = null,
        $sParamMailHost      = null,
        $sParamMailPort      = null,
        $sParamMailUser      = null,
        $sParamMailPass      = null,
        $sParamMailFolder    = null,
        $sParamMailSsl       = null,
        $sParamMsgNum        = null,
        $nParamOrder         = null,
        $sParamZend          = null;

    protected
        $sQueryString        = null;

    protected
        $aHrefNameSort       = null,
        $aHrefTimeSort       = null,
        $aHrefSizeSort       = null;

    // --------------------------------------------------------------------

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean true if initialization successful, false otherwise
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    // --------------------------------------------------------------------

    /**
     * Perform the plugin purpose. This is the main method of the plugin.
     *
     * @access  public
     * @return  void
     *
     * @author  Alexander Klein, <a.klein@eoneed.org>
     */
    public function perform() {

        $this->nIdent              = null;
        $this->sQuery              = null;
        $this->sParamRoot          = null;
        $this->sParamMailType      = null;
        $this->sParamMailHost      = null;
        $this->sParamMailPort      = null;
        $this->sParamMailUser      = null;
        $this->sParamMailPass      = null;
        $this->sParamMailFolder    = null;
        $this->sParamMailSsl       = null;
        $this->sParamMsgNum        = null;
        $this->nParamOrder         = null;
        $this->sParamZend    	   = null;

        // ---------------------------------------------------------------

        $this->Node = $this->Context->getCurrentNode();
        $this->nIdent = $this->Context->getPluginParamIdent();

        // ---------------------------------------------------------------

        if (!$this->Context->getPluginParam('dsn')
         || $this->Context->getPluginParam('dsn') == '') {

            $this->Context->addError(313, 'dsn Parameter missing');
            $this->Context->resume();
            return true;
        }

        // ---------------------------------------------------------------

        // Get user data access object
        $UserDAO = $this->Context->getUserDAO();
        $CurrUser = $this->Context->getCurrentUser();
        $nUid = (int)$CurrUser->get('userId');

        // ---------------------------------------------------------------

        /**
         * Plugin warning
         */
        if (!$this->Context->getPluginParam('understand')) {

            $aMsg = array(
                '<strong>You are using a high risc Plugin</strong>',
                'To confirm that you have understood the risc of ',
                'using this plugin, you have to set the parameter ',
                'understand="yes"'
            );
            $this->Context->addError(317, $aMsg);
            $this->Context->resume();
            return true;
        }

        // ---------------------------------------------------------------

        /**
         * Plugin warning, the 2nd
         */
        if (!$this->Context->getPluginParam('enablepublic')) {

            /**
             * Disable this Plugin for root
             */
            //if ($nUid == 1) {
            //    // FIX: Error
            //    $this->Context->addError(459);
            //    $this->Context->resume();
            //    return true;
            //}

            /**
             * Disable this Plugin for guests
             */
            if ($nUid == 65535) {
                // FIX: Error
                $this->Context->addError(459);
                $this->Context->resume();
                return true;
            }

            /**
             * Print warning
             */
            if ($this->Node->get('isWorldReadable')
             || $this->Node->get('isWorldWritable')
             || $this->Node->get('isWorldExecutable')) {

                $aMsg = array(
                    '<strong>This Plugin is public accessible</strong>',
                    'To confirm that you have understood the risc of ',
                    'using this plugin in public mode, you have to set ',
                    'the parameter enablepublic="yes"'
                );
                $this->Context->addError(317, $aMsg);
                $this->Context->resume();
                return true;
            }
        }

        // ---------------------------------------------------------------

        $this->sParamZend = $this->Context->getPluginParam('zend')
            ? $this->Context->getPluginParam('zend')
            : null;

        /**
         * Zend_Loader
         */
        if ($this->sParamZend != null) {
            ini_set('include_path', ini_get('include_path')
                .PATH_SEPARATOR.$this->sParamZend
            );
        }
        @include_once 'Zend/Loader.php';

        if (!class_exists('Zend_Loader')) {
            $this->Context->addError(0, 'Zend Framework not found');
            $this->Context->resume();
            return true;
        }

        // ---------------------------------------------------------------

        $sDsn = $this->Context->getPluginParam('dsn');

        $sDsn = str_replace('%LOGIN%', $CurrUser->get('login'), $sDsn);
        $sDsn = str_replace('%EMAIL%', $CurrUser->get('email'), $sDsn);

        $UriInfo = new UriInfo($sDsn);

        switch($UriInfo->get('scheme')) {

            case 'mbox':
            case 'mbox+folder':

            case 'maildir':
            case 'maildir+folder':

                $this->sParamRoot = $UriInfo->get('path');
                $this->sParamMailType = $UriInfo->get('scheme');

            break;

            case 'imap':
            case 'imap+ssl':
            case 'imap+tls':

                $this->sParamMailType = 'imap';
                $this->sParamMailHost = $UriInfo->get('host');
                $this->sParamMailPort = $UriInfo->get('port');
                $this->sParamMailUser = rawurldecode($UriInfo->get('user'));
                $this->sParamMailPass = rawurldecode($UriInfo->get('pass'));

                if ($UriInfo->get('path') != ''
                 && $UriInfo->get('path') != '/') {
                    $this->sParamMailFolder = substr($UriInfo->get('path'), 1);
                }

                switch($UriInfo->get('scheme')) {
                    case 'imap+ssl':
                        $this->sParamMailSsl  = 'SSL';
                    break;
                    case 'imap+tls':
                        $this->sParamMailSsl  = 'TLS';
                    break;
                }
            break;

            case 'pop3':
            case 'pop3+ssl':
            case 'pop3+tls':

                $this->sParamMailType = 'pop3';
                $this->sParamMailHost = $UriInfo->get('host');
                $this->sParamMailPort = $UriInfo->get('port');
                $this->sParamMailUser = rawurldecode($UriInfo->get('user'));
                $this->sParamMailPass = rawurldecode($UriInfo->get('pass'));

                if ($UriInfo->get('path') != ''
                 && $UriInfo->get('path') != '/') {
                    $this->sParamMailFolder = substr($UriInfo->get('path'), 1);
                }

                switch($UriInfo->get('scheme')) {
                    case 'pop3+ssl':
                        $this->sParamMailSsl  = 'SSL';
                    break;
                    case 'pop3+tls':
                        $this->sParamMailSsl  = 'TLS';
                    break;
                }

            break;

            default:
                $this->Context->addError(314, 'Scheme not valid');
                $this->Context->resume();
                return true;
            break;
        }

        // ---------------------------------------------------------------

        if ($this->Request->has('message')
         && is_numeric($this->Request->get('message'))) {
            $this->sParamMsgNum = $this->Request->get('message');
        }

        // ---------------------------------------------------------------

        switch (strtolower($this->Request->get('mailboxorder'))) {

            case 'na':
                $this->nParamOrder = self::SORT_NAME_ASC;
                break;
            case 'nd':
                $this->nParamOrder = self::SORT_NAME_DSC;
                break;

            case 'ta':
                $this->nParamOrder = self::SORT_TIME_ASC;
                break;
            case 'td':
                $this->nParamOrder = self::SORT_TIME_DSC;
                break;

            case 'sa':
                $this->nParamOrder = self::SORT_SIZE_ASC;
                break;
            case 'sd':
                $this->nParamOrder = self::SORT_SIZE_DSC;
                break;

            default:
                $sParamOrder = $this->Context->getPluginParam('order');

                switch (strtolower($sParamOrder)) {

                    case 'na':
                        $this->nParamOrder = self::SORT_NAME_ASC;
                        break;
                    case 'nd':
                        $this->nParamOrder = self::SORT_NAME_DSC;
                        break;

                    case 'ta':
                        $this->nParamOrder = self::SORT_TIME_ASC;
                        break;
                    case 'td':
                        $this->nParamOrder = self::SORT_TIME_DSC;
                        break;

                    case 'sa':
                        $this->nParamOrder = self::SORT_SIZE_ASC;
                        break;
                    case 'sd':
                        $this->nParamOrder = self::SORT_SIZE_DSC;
                        break;

                    default:
                        $this->nParamOrder = self::SORT_TIME_DSC;
                        break;
                }
            break;
        }

        // ---------------------------------------------------------------

        $this->initVars();
        $this->loadClasses();

        // ---------------------------------------------------------------

        try {

            switch ($this->sParamMailType) {

                case 'mbox':
                    $aParams = array('filename' => $this->sParamRoot);
                    $this->Mail = new Zend_Mail_Storage_Mbox($aParams);
                break;

                case 'mbox+folder':
                    $aParams = array('dirname' => $this->sParamRoot);
                    $this->Mail = new Zend_Mail_Storage_Folder_Mbox($aParams);
                break;

                case 'maildir':
                    $aParams = array('dirname' => $this->sParamRoot);
                    $this->Mail = new Zend_Mail_Storage_Maildir($aParams);
                break;

                case 'maildir+folder':
                    $aParams = array('dirname' => $this->sParamRoot);
                    $this->Mail = new Zend_Mail_Storage_Folder_Maildir($aParams);
                break;

                case 'pop3':

                    $aParams = array(
                        'host'     => $this->sParamMailHost,
                        'port'     => $this->sParamMailPort,
                        'user'     => $this->sParamMailUser,
                        'password' => $this->sParamMailPass,
                        'ssl'      => $this->sParamMailSsl,
                    );
                    $this->Mail = new Zend_Mail_Storage_Pop3($aParams);
                break;

                case 'imap':
                    $aParams = array(
                        'host'     => $this->sParamMailHost,
                        'port'     => $this->sParamMailPort,
                        'user'     => $this->sParamMailUser,
                        'password' => $this->sParamMailPass,
                        'ssl'      => $this->sParamMailSsl,
                    );
                    $this->Mail = new Zend_Mail_Storage_Imap($aParams);
                break;

                default:
                    $this->Mail = null;
                break;
            }
        }
        catch(Exception $e) {

            /**
             * Catch failed logins
             */
            $this->Context->addError(0, $e->getMessage());
            $this->Context->resume();
            return true;
        }

        // ---------------------------------------------------------------

        try {

            $sInstance = 'Zend_Mail_Storage_Folder_Interface';
            if ($this->Mail instanceof $sInstance && $this->sParamMailFolder) {

                // could also be done in constructor of $this->Mail
                // with parameter 'sParamMailFolder' => '...'
                $this->Mail->selectFolder($this->sParamMailFolder);
            }
        }
        catch(Exception $e) {

            /**
             *
             */
            $this->Context->addError(0,
                array(
                    $e->getMessage(),
                    $this->sParamMailFolder
                )
            );
            $this->Context->resume();
            return true;
        }

        // ---------------------------------------------------------------

        $message = null;

        try {
            if ($this->sParamMsgNum) {
                $message = $this->Mail->getMessage($this->sParamMsgNum);
            }
        }
        catch(Zend_Mail_Exception $e) {
            // ignored, $message is still null and we display the list
        }

        // ---------------------------------------------------------------

        if (!$this->Mail) {
            //$this->showChooseType();
            $this->Context->addError(0, 'Credentials failed');
            $this->Context->resume();
            return true;
        }
        else if ($message) {
            $this->showMessage($message);
        }
        else {
            $this->showList();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Sort Index
     *
     * @access  private
     * @param   array
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     */
    private function sortIndex(&$aEntries) {
        $sNameOrder = 'na';
        $sTimeOrder = 'ta';
        $sSizeOrder = 'sa';

        switch ($this->nParamOrder) {

            case self::SORT_NAME_ASC:
                $sNameOrder = 'nd';
                usort($aEntries, array($this, 'compareName'));
                break;

            case self::SORT_NAME_DSC:
                $sNameOrder = 'na';
                usort($aEntries, array($this, 'compareName'));
                $aEntries = array_reverse($aEntries);
                break;

            case self::SORT_TIME_ASC:
                $sTimeOrder = 'td';
                usort($aEntries, array($this, 'compareTime'));
                break;

            case self::SORT_TIME_DSC:
                $sTimeOrder = 'ta';
                usort($aEntries, array($this, 'compareTime'));
                $aEntries = array_reverse($aEntries);
                break;

            case self::SORT_SIZE_ASC:
                $sSizeOrder = 'sd';
                usort($aEntries, array($this, 'compareSize'));
                break;

            case self::SORT_SIZE_DSC:
                $sSizeOrder = 'sa';
                usort($aEntries, array($this, 'compareSize'));
                $aEntries = array_reverse($aEntries);
                break;

            default:
                break;
        }

        $this->aHrefNameSort = $this->Response->getControllerHref(
            'node='.$this->Node->get('id').'&mailboxorder=' . $sNameOrder
        );
        $this->aHrefTimeSort = $this->Response->getControllerHref(
            'node='.$this->Node->get('id').'&mailboxorder=' . $sTimeOrder
        );
        $this->aHrefSizeSort = $this->Response->getControllerHref(
            'node='.$this->Node->get('id').'&mailboxorder=' . $sSizeOrder
        );
    }

    // --------------------------------------------------------------------

    /**
     * Compare file names
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     */
    private function compareName($aA, $aB) {
        return strcasecmp($aA['subj'], $aB['subj']);
    }

    // --------------------------------------------------------------------

    /**
     * Compare file last modification time
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function compareTime($aA, $aB) {
        $iCmp = strcasecmp($aA['time'], $aB['time']);

        if ($iCmp === 0) {
            return strcasecmp($aA['subj'], $aB['subj']);
        } else {
            return $iCmp;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Compare file size
     *
     * @access  private
     * @param   array
     * @param   array
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.0
     */
    private function compareSize($aA, $aB) {
        if ($aA['size'] == $aB['size']) {
            return 0;
        }

        return ($aA['size'] < $aB['size']) ? -1 : 1;
    }

    // --------------------------------------------------------------------

    /**
     * load needed classes
     */
    protected function loadClasses() {

        $classname = array(
            'mbox'           => 'Zend_Mail_Storage_Mbox',
            'mbox-folder'    => 'Zend_Mail_Storage_Folder_Mbox',
            'maildir'        => 'Zend_Mail_Storage_Maildir',
            'maildir-folder' => 'Zend_Mail_Storage_Folder_Maildir',
            'pop3'           => 'Zend_Mail_Storage_Pop3',
            'imap'           => 'Zend_Mail_Storage_Imap'
        );

        Zend_Loader::loadClass('Zend_Mail_Storage');

        if (isset($classname[$this->sParamMailType])) {
            Zend_Loader::loadClass($classname[$this->sParamMailType]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * init variables
     */
    protected function initVars() {

        $aQuery = array();
        if ($this->Request->has('folder')
         && $this->Request->get('folder') != '') {
            $this->sParamMailFolder = $this->Request->get('folder');
            $aQuery[] = 'folder='.$this->sParamMailFolder;
        }
        $this->sQueryString = implode('&', $aQuery);
    }

    // --------------------------------------------------------------------

    /**
     * output html header
     *
     * @param string $title page title
     */
    protected function showHeader($title = null) {

        $sOut  = '';
        $sOut .= '<style type="text/css">';
        $sOut .= 'table.mail {';
        $sOut .= '    padding: 5px;';
        $sOut .= '    border-style: solid;';
        $sOut .= '    border-width: 1px;';
        $sOut .= '    border-color: #CCCCCC;';
        //$sOut .= '    background-color: #F4F4F4;';
        $sOut .= '}';
        $sOut .= 'table.mail th {';
        $sOut .= '    font-size: 12px;';
        $sOut .= '    background-color: '.$this->Registry->get('COLOR_RAPPS_CONTENT');
        $sOut .= '}';
        $sOut .= 'table.mail td {';
        $sOut .= '    font-size: 12px;';
        //$sOut .= '    background-color: #FFFFFF';
        $sOut .= '}';
        $sOut .= 'tr.new td {';
        $sOut .= '    color: #800';
        $sOut .= '}';
        $sOut .= 'tr.unread td {';
        $sOut .= '    font-weight: bold';
        $sOut .= '}';
        $sOut .= 'tr.flagged td {';
        //$sOut .= '    background-color: #FFAAAA';
        $sOut .= '}';
        $sOut .= '.message {';
        $sOut .= '    xwhite-space: pre;';
        $sOut .= '    font-family: Courier New, monospace;';
        $sOut .= '    padding: 0.5em';
        $sOut .= '}';
        $sOut .= '.index {';
        $sOut .= '    font-family: Courier New, monospace;';
        $sOut .= '    padding: 2px 5px 2px 5px;';
        $sOut .= '    white-space: nowrap;';
        $sOut .= '}';
        $sOut .= '</style>';

        if ($title != null) {
            $sOut .= '<h2>'.$title.'</h2>';
        }

        echo $sOut;
    }

    // --------------------------------------------------------------------

    /**
     * output html footer
     */
    protected function showFooter() {
    }

    // --------------------------------------------------------------------

    /**
     * output type selection AKA "login-form"
     */
    protected function showChooseType() {
        $this->showHeader();

        $sStr  = '<form>';
        $sStr .=   '<strong>Mbox file</strong><br />';
        $sStr .=   '<input name="param" value="mbox/INBOX"/>';
        $sStr .=   '<input type="hidden" name="type" value="mbox"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        $sStr .= '<form>';
        $sStr .=   '<strong>Mbox folder</strong><br />';
        $sStr .=   '<input name="param" value="mbox"/>';
        $sStr .=   '<input type="hidden" name="type" value="mbox-folder"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        $sStr .= '<form>';
        $sStr .=   '<strong>Maildir file</strong><br />';
        $sStr .=   '<input name="param" value="maildir"/>';
        $sStr .=   '<input type="hidden" name="type" value="maildir"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        $sStr .= '<form>';
        $sStr .=   '<strong>Maildir folder</strong><br />';
        $sStr .=   '<input name="param" value="maildir"/>';
        $sStr .=   '<input type="hidden" name="type" value="maildir-folder"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        $sStr .= '<form>';
        $sStr .=   '<strong>Pop3 Host</strong><br />';
        $sStr .=   '<input name="param" value="localhost"/>';
        $sStr .=   '<input type="hidden" name="type" value="pop3"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        $sStr .= '<form>';
        $sStr .=   '<strong>IMAP Host</strong><br />';
        $sStr .=   '<input name="param" value="localhost"/>';
        $sStr .=   '<input type="hidden" name="type" value="imap"/>';
        $sStr .=   '<input type="submit"/>';
        $sStr .= '</form>';

        echo $sStr;

        $this->showFooter();
    }

    // --------------------------------------------------------------------

    /**
     * output message list
     */
    protected function showList() {
        $this->showHeader();

        if ($this->Node) {
            if ($this->sQueryString) {
                $sPrefix = $this->Response->getControllerHref(
                    'node=' . $this->Node->get('id').'&'.$this->sQueryString.'&message='
                );
            }
            else {
                $sPrefix = $this->Response->getControllerHref(
                    'node=' . $this->Node->get('id').'&message='
                );
            }
        }

        /**
         *
         */
        $this->Mail->rewind();
        $c = $this->Mail->countMessages();

        $a = 0;
        $aIndex = array();
        while($this->Mail->valid()) {
            $num = $this->Mail->key();
            $message = $this->Mail->current();

            // -----

            $aIndex[$a]['href'] = $sPrefix.$num;
            $aIndex[$a]['color'] = $this->Registry->get('COLOR_BGCOLOR');

            // -----

            if ($this->Mail->hasFlags) {
                $class = array();

                if ($message->hasFlag(Zend_Mail_Storage::FLAG_RECENT)) {
                    $class['unread'] = 'unread';
                    $class['new']    = 'new';
                }

                if (!$message->hasFlag(Zend_Mail_Storage::FLAG_SEEN)) {
                    $class['unread'] = 'unread';
                }

                if ($message->hasFlag(Zend_Mail_Storage::FLAG_FLAGGED)) {
                    $class['flagged'] = 'flagged';
                }

                $aIndex[$a]['class'] = implode(' ', $class);
            }

            // -----

            try {
                $nTime = strtotime($message->date);

                if ($nTime > 0) {

                    $aIndex[$a]['time'] = $nTime;
                    $aIndex[$a]['date'] = date(
                        'Y-m-d H:i:s', $nTime
                    );
                }
            }
            catch(Exception $e) {
            	$aIndex[$a]['time'] = 0;
                $aIndex[$a]['date'] = 'N/A';
            }

            // -----

            try {
                $aIndex[$a]['from'] = $message->from;
            }
            catch(Exception $e) {
                $aIndex[$a]['from'] = 'N/A';
            }

            // -----

            try {
                $aIndex[$a]['subj'] = $message->subject;
            }
            catch(Exception $e) {
                $aIndex[$a]['subj'] = 'N/A';
            }

            // -----

            //try {
            //    $aIndex[$a]['size'] = $message->getSize();
            //}
            //catch(Exception $e) {
            //    $aIndex[$a]['size'] = 0;
            //}

            // -----

            $this->Mail->next();
            $a++;
        }
        $this->sortIndex($aIndex);

        /**
         *
         */
        $sStr  = '<table width="100%" class="mail">';
        $sStr .= '<tr valign="top">';
        $sStr .=   '<th>';
        $sStr .=     '<a href="'.$this->aHrefTimeSort.'"><strong>Date</strong></a>';
        $sStr .=   '</th>';
        $sStr .=   '<th>';
        $sStr .=     '<a href="'.$this->aHrefNameSort.'"><strong>Subject</strong></a>';
        $sStr .=   '</th>';
        $sStr .= '</tr>';

        $i = 1;
        foreach($aIndex as $aRow) {

            $sStr .= '<tr valign="top" class="'.$aRow['class'].'"';
            $sStr .=   ' onmouseover="mover(this)"';
            $sStr .=   ' onmouseout="mout(this, \''.$aRow['color'].'\')"';
            $sStr .= '>';
            $sStr .=   '<td class="index">';
            $sStr .=     $aRow['date'];
            $sStr .=   '</td>';
            $sStr .=   '<td width="100%" class="index">';
            $sStr .=     '<a href="'.$aRow['href'].'">';
            $sStr .=       '<strong>'.$aRow['subj'].'</strong>';
            $sStr .=     '</a><br />';
            $sStr .=     'From: '.obfuscateEmail(escape($aRow['from'])).'<br />';
            $sStr .=   '</td>';
            $sStr .= '</tr>';

            if ($i == 25) {
            	break;
            }

            $i++;
        }
        $sStr .= '</table>';
        echo $sStr;

        /**
         *
         */
        if ($this->Mail instanceof Zend_Mail_Storage_Folder_Interface) {
            $this->showFolders();
        }

        $this->showFooter();
    }

    // --------------------------------------------------------------------

    /**
     * output folder list
     */
    protected function showFolders() {

        if ($this->Node) {
            $sPrefix = $this->Response->getControllerHref(
                'node=' . $this->Node->get('id').'&folder=%s'
            );
        }

        // ---------------------------------------------------------------

        $nExpire = 3600;

        // // Build ident depending on plugin parameters
        // $sIdent = $this->Context->getPluginParamIdent();
        //
        // // If cached result exists, put it out and leave the plugin
        // if ($sStr = $this->Context->getFromCache($this, $sIdent, $nExpire)) {
        //     echo $sStr;
        //     return true;
        // }

        // ---------------------------------------------------------------

        $Iter = new RecursiveIteratorIterator(
            $this->Mail->getFolders(),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $aFolderList = array();
        foreach ($Iter as $localName => $Folder) {

            if ($Folder->isSelectable()) {

                $sOutName = str_pad(
                    '', $Iter->getDepth() * 12,
                    '&nbsp;',  STR_PAD_LEFT
                ).$localName;

            	$aFolderList[] = array(
            	    'href' => sprintf($sPrefix, $localName),
            	    'name' => $sOutName,
            	);
            }
        }

        if (count($aFolderList) < 2) {
        	return true;
        }

        $sOut = '<ul>';
        foreach($aFolderList as $aFolderItem) {
            $sOut .= '<li>';
            $sOut .=   '<a href="'.$aFolderItem['href'].'">';
            $sOut .=      $aFolderItem['name'];
            $sOut .=   '</a>';
            $sOut .= '</li>';
        }
        $sOut .= '</ul>';

        echo $sOut;
    }

    // --------------------------------------------------------------------

    /**
     * output mail message
     */
    protected function showMessage($message) {
        $this->showHeader();

        try {
            $from = $message->from;
        }
        catch(Zend_Mail_Exception $e) {
            $from = '(unknown)';
        }

        try {
            $to = $message->to;
        }
        catch(Zend_Mail_Exception $e) {
            $to = '(unknown)';
        }

        try {
            $subject = $message->subject;
        }
        catch(Zend_Mail_Exception $e) {
            $subject = '(unknown)';
        }

        $this->showHeader($subject);

        if ($this->Node) {
            $aHref = $this->Response->getControllerHref(
                'node=' . $this->Node->get('id')
            );
        }

        $sStr  = '<table width="100%" class="mail">';
        $sStr .=   '<tr>';
        $sStr .=     '<th>From:</td>';
        $sStr .=     '<th align="left">';
        $sStr .=       obfuscateEmail(escape($from));
        $sStr .=     '</th>';
        $sStr .=   '</tr>';
        $sStr .=   '<tr>';
        $sStr .=     '<th>Subject:</td>';
        $sStr .=     '<th align="left">';
        $sStr .=       escape($subject);
        $sStr .=     '</th>';
        $sStr .=   '</tr>';
        $sStr .=   '<tr>';
        $sStr .=     '<th>To:</th>';
        $sStr .=     '<th align="left">';
        $sStr .=       obfuscateEmail(escape($to));
        $sStr .=     '</th>';
        $sStr .=   '</tr>';
        $sStr .=   '<tr>';
        $sStr .=     '<td colspan="2" class="message">';
        $sStr .=       '<br/>';

        if ($message->isMultipart()) {
            $i = 0;
            foreach (new RecursiveIteratorIterator($message) as $part) {

                if (substr($part->contentType, 0, 10) == 'text/plain') {

                    $sStr .= '<span style="float: right"><em>'.$part->contentType.'</em></span>';
                    $sStr .= '<p>'.nl2br(escape(quoted_printable_decode($part))).'</dd>';
                }
                else if (substr($part->contentType, 0, 9) == 'text/html') {

                    $sStr .= '<span style="float: right"><em>'.$part->contentType.'</em></span>';
                    $sStr .= '<p>'.nl2br(quoted_printable_decode($part)).'</dd>';
                }
                else {

                    $sStr .= '<li><a href="#'.$i.'">Part with type '.$part->contentType.'</a></li>';
                    //$sStr .= '<p>'.nl2br(escape($part)).'</dd>';
                }

                $i++;
            }
        }
        else {

            $sStr .= nl2br(escape(
                quoted_printable_decode(
                    $message->getContent()
                )
            ));
        }

        $sStr .=     '</td>';
        $sStr .=   '</tr>';
        $sStr .= '</table>';

        echo $sStr;

        // ---------------------------------------------------------------
?>

<br/>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr valign="top">
    <td><?php
        if ($this->sParamMsgNum > 1) {
            echo "<a href=\"".$aHref."?{$this->sQueryString}&message=", $this->sParamMsgNum - 1, '"><img src="/tpl/default/img/left.gif" border="0"/></a>';
        }
    ?></td>
    <td width="50%"><?php
        if ($this->sParamMsgNum > 1) {
            echo "<a href=\"".$aHref."?{$this->sQueryString}&message=", $this->sParamMsgNum - 1, '">prev</a>';
        }
    ?></td>
    <td align="center" nowrap="nowrap"><a href='<?php echo $aHref."?".$this->sQueryString; ?>'>back to list</a></td>
    <td width="50%" align="right"><?php
        if ($this->sParamMsgNum < $this->Mail->countMessages()) {
            echo "<a href=\"".$aHref."?{$this->sQueryString}&message=", $this->sParamMsgNum - 1, '">prev</a>';
        }
    ?></td>
    <td><?php
        if ($this->sParamMsgNum < $this->Mail->countMessages()) {
            echo "<a href=\"".$aHref."?{$this->sQueryString}&message=", $this->sParamMsgNum + 1, '"><img src="/tpl/default/img/right.gif" border="0"/></a>';
        }
    ?></td>
  </tr>
</table>
<?php

        $this->showFooter();
    }

    // --------------------------------------------------------------------

} //

/*

    I'm twenty two, don't know what I'm supposed to do
    or how to be, to get some more out of me.
    I'm twenty two, so far away from all my dreams
    I'm twenty two, feeling blue.

*/

?>