<?php

/**
 *
 * $Id: class.Mailer.php 27 2011-01-09 12:37:59Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     lib
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 27 $
 *
 */

/**
 * coWiki - Mailer class
 *
 * Simplest usage example:
 * <pre>
 *  $Mail = new Mailer();
 *  $Mail->setFrom('From-Name', 'From-Email');
 *  $Mail->addTo('To-Name', 'To-Email');
 *  $Mail->setSubject('Subject');
 *  $Mail->setBody('Body');
 *  $Mail->send();
 * </pre>
 *
 * @package     lib
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.4
 *
 * @todo        [D11N]  Complete documentation
 */
class Mailer extends Object {

    private
        $sSelfName = 'AMOK Omni Mail Automaton';

    private
        $sReturn      = '',
        $sFromName    = '',
        $sFromMail    = '',
        $sReplyToName = '',
        $sReplyToMail = '';

    private
        $aToName  = array(),
        $aToMail  = array(),
        $aCcName  = array(),
        $aCcMail  = array(),
        $aBccName = array(),
        $aBccMail = array();

    private
        $sSubj = '',
        $sBody = '';

    private
        $bSenderInfo = false;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function Mailer() {

        // Initialize default values
        $this->setFrom($_SERVER['HTTP_HOST'], $_SERVER['SERVER_ADMIN']);
        $this->setReturnPath($_SERVER['SERVER_ADMIN']);
    }

    // --------------------------------------------------------------------

    /**
     * Invoke mail process
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function send()  {

        $sReturn = $this->getReturnPathAsString();
        $sFrom   = $this->getFromAsString();
        $sTo     = $this->getToAsString();
        $sCc     = $this->getCcAsString();
        $sSubj   = $this->getSubject();
        $sBody   = $this->getBody();

        // Check headers
        if (!$sFrom || !$sTo || !$sSubj || !$sBody) {

            // {{{ DEBUG }}}
            Logger::err('Cancelling send(). Insufficient data.');
            return false;
        }

        // Generate message id
        $sMid  = strtoupper(time().'.'.substr(md5(uniqid(time())), 0, 6));

        // Assemble header
        $aHead = array();
        $aHead[] = 'Return-Path: <'.$sReturn.'>';

        if ($sCc) {
            $aHead[] = 'Cc: '.$sCc;
        }

        $aHead[] = 'From: '.$sFrom;
        $aHead[] = 'Date: '.date('D, d M Y H:i:s').' +0200 GMT';
        $aHead[] = 'Message-ID: <'.$sMid.'.AMOK@'.$_SERVER['HTTP_HOST'].'>';
        $aHead[] = 'Precedence: bulk';
        $aHead[] = 'X-Mailer: '.$this->sSelfName;
        $aHead[] = 'MIME-Version: 1.0';
        $aHead[] = 'Content-Type: text/plain; charset=UTF-8';
        $aHead[] = 'Content-Transfer-Encoding: 8bit';

        // {{{ DEBUG }}}
        Logger::info('Sending mail to '.$sTo);

        // Add sender info
        if ($this->isSenderInfoEnabled()) {
            $sBody .= $this->generateSenderInfo();
        }

        @mail(
            $sTo,
            $sSubj,
            $sBody . "\n",
            join("\n", $aHead),
            '-f'.$sReturn
        );

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Set return path
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function setReturnPath($sStr) {
        $this->sReturn = $this->cleanHeaderString($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Get return path
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getReturnPathAsString() {
        return $this->sReturn;
    }

    // --------------------------------------------------------------------

    /**
     * Set From: field
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function setFrom($sName, $sMail) {
        $this->sFromName = $this->cleanHeaderString($sName);
        $this->sFromMail = $this->cleanHeaderString($sMail);
    }

    // --------------------------------------------------------------------

    /**
     * Get From: field as complete address
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getFromAsString() {
        return $this->_generateAddress($this->sFromName, $this->sFromMail);
    }

    // --------------------------------------------------------------------

    /**
     * Set To: field
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function addTo($sName, $sMail) {
        $this->aToName[] = $this->cleanHeaderString($sName);
        $this->aToMail[] = $this->cleanHeaderString($sMail);
    }

    // --------------------------------------------------------------------

    /**
     * Get To: field as complete address
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getToAsString() {
        $aTo = array();

        for ($i=0, $n=sizeof($this->aToName); $i<$n; $i++) {
            $aTo[] = $this->_generateAddress(
                          $this->aToName[$i],
                          $this->aToMail[$i]
                     );
        }

        return join(', ', $aTo);
    }

    // --------------------------------------------------------------------

    /**
     * Set Cc: field
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function addCc($sName, $sMail) {
        $this->aCcName[] = $this->cleanHeaderString($sName);
        $this->aCcMail[] = $this->cleanHeaderString($sMail);
    }

    // --------------------------------------------------------------------

    /**
     * Get Cc: field as complete address
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getCcAsString() {
        $aCc = array();

        for ($i=0, $n=sizeof($this->aCcName); $i<$n; $i++) {
            $aCc[] = $this->_generateAddress(
                          $this->aCcName[$i],
                          $this->aCcMail[$i]
                     );
        }

        return join(', ', $aCc);
    }

    // --------------------------------------------------------------------

    /**
     * Set Bcc: field
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function addBcc($sName, $sMail) {
        $this->aBccName[] = $this->cleanHeaderString($sName);
        $this->aBccMail[] = $this->cleanHeaderString($sMail);
    }

    // --------------------------------------------------------------------

    /**
     * Get Bcc: field as complete address
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     * @todo    Implement! :)
     */
    public function getBccAsString() {
        return '';
    }

    // --------------------------------------------------------------------

    private function _generateAddress($sName, $sMail) {
        $sStr = '';

        if (is_string($sName) && $sName != '') {
            if (preg_match('#^[a-z0-9]+$#i', $sName)) {
                $sStr .= $sName;
            } else {
                $sStr .= '"'.$sName.'"';
            }
        }

        if (is_string($sMail) && $sMail != '') {
            $sStr .= ' <'.$sMail.'>';
        }

        return trim($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Set email subject
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function setSubject($sStr) {
        $this->sSubj = $this->cleanHeaderString($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Get email subject
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getSubject() {
        return $this->sSubj;
    }

    // --------------------------------------------------------------------

    /**
     * Set email body
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function setBody($sStr) {
        $this->sBody = trim($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Get email body
     *
     * @access  public
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function getBody() {
        return $this->sBody;
    }

    // --------------------------------------------------------------------

    private function cleanHeaderString($sStr) {

        // Remove line breaks
        $sStr = trim(preg_replace('#\r|\n#', '', $sStr));

        // Remove multiple spaces
        $sStr = preg_replace('# +#', ' ', $sStr);

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Set if sender info is enabled
     *
     * @access  public
     * @param   boolean
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function setIsSenderInfoEnabled($bFlag) {
        $this->bSenderInfo = (bool)$bFlag;
    }

    // --------------------------------------------------------------------

    /**
     * Get if sender info is enabled
     *
     * @access  public
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function isSenderInfoEnabled() {
        return $this->bSenderInfo;
    }

    // --------------------------------------------------------------------

    private function generateSenderInfo() {
        $aArr = array();

        $aArr['IP']   = @$_SERVER['REMOTE_ADDR'];
        $aArr['Host'] = gethostbyaddr(@$_SERVER['REMOTE_ADDR']);

        // Implement proxy chain
        #$_SERVER['HTTP_X_FORWARDED_FOR']

        $aArr['Agent']  = @$_SERVER['HTTP_USER_AGENT'];

        return  "\n\n\n"
                .str_repeat('-', 72)
                ."\nSender information:\n"
                .$this->padArray($aArr)
                ."\n"
                .str_repeat('-', 72);
    }

    // --------------------------------------------------------------------

    public function padArray($aArr) {
        $nMaxKeyLen = 0;

        // Get max. length of key string
        foreach ($aArr as $k => $v) {
            if (strlen($k) > $nMaxKeyLen) {
                $nMaxKeyLen = strlen($k);
            }
        }

        // Assemble info string
        $sStr = '';
        foreach ($aArr as $k => $v) {
            $sStr .= str_pad($k, $nMaxKeyLen, ' ', STR_PAD_LEFT);
            $sStr .= ': ';

            $sStr .= wordwrap(
                        trim($v),
                        72 - 2 - $nMaxKeyLen,
                        "\n" . str_repeat(' ', $nMaxKeyLen + 2),
                        true
                     );
            $sStr .= "\n";
        }

        return rtrim($sStr);
    }

    // --------------------------------------------------------------------

    public function formatString($sStr, $nLineLen, $nIndent) {

        // Break all lines with \n
        $sStr = trim(wordwrap($sStr, $nLineLen, "\n", true));

        // Indent lines
        $sStr = str_replace("\n", "\n".str_repeat(' ', $nIndent), $sStr);

        // Concat leading indentation plus trimmed string
        $sStr = str_repeat(' ', $nIndent) . trim($sStr);

        return $sStr;
    }

} // of class

/*
    In the year 2525
    If man is still alive
    If woman can survive, they may find:

    In the year 1994
    War goes on just like before
    War goes on, it never ends
    War brings bigger dividence

    In the year 1995
    Brand new war is born to die
    From total damage to damage limitation
    Fear is the key to defend the nation

    In the year 1996
    There is no need for politics
    Seeing life with unseeing eyes
    Seeing man see through the disguise

    In the year 1997
    The world whirls on the face of heaven
    Dragon tears washed away thy youth
    Wash thy hands of eternal truth

    In the year 1998
    Why shut the door of the Golden Gate
    Rivers of people flow like blood
    New race rises from the mud

    In the year 1999
    War destroys the last sky line
    A flaming cross appears in the sky
    Man goes down as the bullets fly

    Now it's been 2000 years
    Man has cried a billion tears
    For what he never knew
    Now man's reign is through
    But through eternal night
    The twinkling of starlight
    So very far away
    Maybe it's only yesterday

    In the year 2525
    If man is still alive
    If woman can survive
    We survive

    In the year 3535
*/

?>
