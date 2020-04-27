<?php

/**
 *
 * $Id: class.HttpRequest.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     util
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
 * This class provides wrapped access to HTTP request values.
 *
 * @package     util
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class HttpRequest extends Object {

    protected static
        $Instance = null;

    protected
        $Context = null,
        $Env = null;

    // --------------------------------------------------------------------

    /**
     * Get the unique instance of the class (This class is implemented as
     * Singleton).
     *
     * @access  public
     * @return  Object  The class instance
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new HttpRequest;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * The class constructor prepares incoming POST and GET key-value
     * pairs for further processing. All values are provided unescaped,
     * so you don't have to care about them, regardless of PHPs
     * magic_quotes_gpc settings.
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        global $HTTP_POST_VARS, $HTTP_GET_VARS;

        // Different PHP versions may provide different predefinded
        // variables - PHP core developers are stoned, we knew that

        if (isset($_GET)) {
            $aRequest = array_merge($_GET, $_POST);
        } else {
            $aRequest = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS);
        }

        // Working with ecaped values is annoying and confusing
        foreach ($aRequest as $k => $v) {
            if (ini_get('magic_quotes_gpc')) {
                $this->set($k, stripslashes($v));
            } else {
                $this->set($k, $v);
            }
        }

        $this->Context = RuntimeContext::getInstance();
        $this->Env = $this->Context->getEnvironment();
    }

    // --------------------------------------------------------------------

    /**
     * Returns the IP of remote client.
     *
     * @access  public
     * @return  string  The remote IP
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getRemoteAddr() {
        if ($this->Env->has('X_FORWARDED_FOR')) {
            return array_pop(explode(',',$this->Env->get('X_FORWARDED_FOR')));
        }

        return $this->Env->get('REMOTE_ADDR');
    }

    // --------------------------------------------------------------------

    /**
     * Returns the reverse DNS name of the remote client.
     *
     * @access  public
     * @return  string  The hostmask of the remote client.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getRemoteHost() {
        return @gethostbyaddr($this->getRemoteAddr());
    }

    // --------------------------------------------------------------------

    /**
     * Returns the reverse DNS name of any host.
     *
     * @access  public
     * @param   string  Host IP
     * @return  mixed   The hostmask, or IP if name could not be resolved.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getRemoteHostByAddr(String $sAddr) {
        return @gethostbyaddr($sAddr);
    }

    // --------------------------------------------------------------------

    /**
     * Returns the server name (webserver settings).
     *
     * @access  public
     * @return  mixed   The server name
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.2
     */
    public function getServerName() {
        $sPort = ':' . $this->Env->get('HTTP_PORT');

        if ($sPort == ':80' || $sPort == ':') {
            $sPort = '';
        }

        return $this->Env->get('SERVER_NAME') . $sPort;
    }

    // --------------------------------------------------------------------

    /**
     * Returns the host name given in a HTTP 1.1 request.
     *
     * @access  public
     * @return  mixed   The name of the host from HTTP request.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getHost() {
        $sPort = ':' . $this->Env->get('HTTP_PORT');

        if ($sPort == ':80' || $sPort == ':') {
            $sPort = '';
        }

        return $this->Env->get('HTTP_HOST') . $sPort;
    }

    // --------------------------------------------------------------------

    /**
     * Returns the complete URI using the host name given in a HTTP 1.1
     * request. The URI has a leading protocol scheme (like http://).
     *
     * @access  public
     * @return  string  The complete URI string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getHostUri() {
        $sProto = 'http';

        if (strtolower($this->Env->get('HTTPS')) == 'on'
            || $this->Env->has('SSL_PROTOCOL_VERSION')) {
            $sProto = 'https';
        }

        return $sProto . '://' . $this->getHost();
    }

    // --------------------------------------------------------------------

    /**
     * Returns the referrer.
     *
     * @access  public
     * @return  string  The referrer string, or an empty string if no
     *                  referrer was passed in request.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getReferrer() {
        return $this->Env->get('HTTP_REFERER');
    }

    // --------------------------------------------------------------------

    /**
     * Returns the user agent string of the requesting client.
     *
     * @access  public
     * @return  string  User agent string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getAgent() {
        return $this->Env->get('HTTP_USER_AGENT');
    }

    // --------------------------------------------------------------------

    /**
     * Returns the base path of currently running script.
     *
     * @access  public
     * @return  string  The base path.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getBasePath() {
        return getRequestBasePath($this->Env->get('PHP_SELF'));
    }

    // --------------------------------------------------------------------

    /**
     * Returns the current request URI.
     *
     * @access  public
     * @return  string  The request URI without session name and session id.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getRequestUri() {
        $sStr = $this->Env->get('REQUEST_URI');

        // Remove session id from requested uri
        $sStr = preg_replace(
                    '#[?|&]'.session_name().'=[^&|?]*#',
                    '',
                    $sStr
                );

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch the content from a foreign host. This method implements the
     * HTT protocol to fetch its ressource.
     *
     * @access  public
     * @param   object  The UriInfo object that describes the target URI.
     * @return  array   The result array provides following keys:
     *                    status  = the HTTP return status
     *                    header  = complete response headers
     *                    content = response payload data
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [FIX]   implement getUri()
     * @todo    [FIX]   React on header status and throw an error
     */
    public function fetchContent(UriInfo $UriInfo) {

        $Registry = $this->Context->getRegistry();

        // Default values
        $aReturn = array(
            'status'  => array('code' => 0, 'text' => 'not set'),
            'header'  => '',
            'content' => ''
        );

        if (!$UriInfo->isValid()) {
            return $aReturn;
        }

        // Shall we use a proxy?
        if ($Registry->get('RUNTIME_HTTP_PROXY_ENABLE')) {
            $rHandle = @fsockopen(
                $Registry->get('RUNTIME_HTTP_PROXY_HOST'),
                $Registry->get('RUNTIME_HTTP_PROXY_PORT')
                    ? $Registry->get('RUNTIME_HTTP_PROXY_PORT')
                    : 3128,
                $sErrNo,
                $sErrStr,
                5
            );

        } else {
            $rHandle = @fsockopen(
                $UriInfo->get('host'),
                $UriInfo->get('port') ? $UriInfo->get('port') : 80,
                $sErrNo,
                $sErrStr,
                5
            );
        }

        if (!$rHandle) {
            // FIX: implement getUri()
            //$this->Context->addError(315, $UriInfo->getUri());
            $this->Context->addError(315);
            $this->Context->resume();
            return false;
        }

        // Assemble HTTP-header. Do not attract attention with user agent.
        $aHeader = array();

        if ($Registry->get('RUNTIME_HTTP_PROXY_ENABLE')) {
            $aHeader[] = 'GET '.$UriInfo->get('fullUri').' HTTP/1.0';
        } else {
            $aHeader[] = 'GET '.$UriInfo->get('path').' HTTP/1.0';
        }

        if ($Registry->get('RUNTIME_HTTP_PROXY_ENABLE')) {
            $aHeader[] = 'Host: '.$Registry->get('RUNTIME_HTTP_PROXY_HOST');
        } else {
            $aHeader[] = 'Host: '.$UriInfo->get('host');
        }

        $aHeader[] = 'Accept: */*';
        $aHeader[] = 'Connection: close';
        $aHeader[] = 'User-Agent: Mozilla/5.0 (X11; Linux i686)';
        $aHeader[] = 'Cache-Control: max-age=0';

        $sRequest =  @join("\r\n", $aHeader) . "\r\n"; // last line CRLF

        // ----------------------------------------------------------------

        @fputs($rHandle, $sRequest . "\r\n"); // CRLF, end of headers

        $sContent = '';
        while (!feof($rHandle)) {
            $sContent .= @fgets($rHandle, 16384);
        }

        // FIX: React on header status and throw an error
        $nHeadEnd = strpos($sContent,"\r\n\r\n");
        $aReturn['header']  = substr($sContent, 0, $nHeadEnd);
        $aReturn['content'] = substr($sContent, $nHeadEnd + 4);

        $sPattern = '#HTTP/([\d|\.]+)\s+(\d+)\s+(.*)#';
        if (preg_match($sPattern, $aReturn['header'], $aMatches)) {
            $aReturn['status'] = array(
                'code' => (int)$aMatches[2],
                'text' => $aMatches[3]
            );
        }

        return $aReturn;
    }

} // of class

/*
    Having reached the end of my poor sinner's life, my hair now
    whiteconfined now with my heavy, ailing body in this cell in the
    dear monastery of Melk, I prepare to leave on this parchment my
    testimony as to the wondrous and terrible events that I happened
    to observe in my youth and may my hand remain steady as I prepare
    to tell what happened.
*/

?>
