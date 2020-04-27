<?php

/**
 *
 * $Id: class.HttpResponse.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - HTTP response class
 *
 * @package     util
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class HttpResponse extends Object {

    protected static
        $Instance = null;

    protected
        $Context     = null,
        $Registry    = null,
        $Request     = null,
        $Env         = null,
        $bRewritable = false;

    protected
        $aGetParam   = null;

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
            self::$Instance = new HttpResponse;
        }
        return self::$Instance;
    }

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

      $this->Context = RuntimeContext::getInstance();
      $this->Registry = $this->Context->getRegistry();
      $this->Request = $this->Context->getRequest();
      $this->Env = $this->Context->getEnvironment();

      // Are we using Apaches mod_rewrite?
      $sVal1 = strtolower($this->Env->get('REDIRECT_COWIKI_URL_REWRITE'));
      $sVal2 = strtolower($this->Env->get('COWIKI_URL_REWRITE'));
      $bVal3 = $this->Registry->get('COWIKI_CONTROLLER_REWRITABLE');

      $this->bRewritable = ($sVal1 == 'on' || $sVal2 == 'on') && $bVal3;

      // Init get-parameter container
      $this->clearGetParams();
    }

    /**
     * Add get param
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function addGetParam($sKey, $sValue) {
        $sPlugin = $this->Context->getPluginName();
        $this->aGetParam[$sPlugin][$sKey] = urlencode($sValue);
    }

    /**
     * Clean get-parameter container
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function clearGetParams() {
        $this->aGetParam[$this->Context->getPluginName()] = array();
    }

    /**
     * Retrieve query array build by "addGetParam()"
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    public function getGetParamArray() {
        return $this->aGetParam[$this->Context->getPluginName()];
    }

    /**
     * Retrieve query string determined by "addGetParam()"
     *
     * @access  protected
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function getGetParamString() {

        $sPlugin = $this->Context->getPluginName();

        $sStr  = '';
        $aKeys = array();

        if (isset($this->aGetParam[$sPlugin])
            && is_array($this->aGetParam[$sPlugin])) {

            $aKeys = array_keys($this->aGetParam[$sPlugin]);
        }

        for ($i=0, $n=sizeof($aKeys); $i<$n; $i++) {
            $sStr .= strtoupper(substr(md5($sPlugin), 0, 6))
                    .'01'
                    .':'
                    .$aKeys[$i]
                    .'='
                    .$this->aGetParam[$sPlugin][$aKeys[$i]]
                    .'&';
        }

        if ($sStr) {
            return substr($sStr, 0, -1);
        }

        return $sStr;;
    }

    /**
     * Get controller path
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    protected function getControllerPath($sQuery) {

        $sGetParam = $this->getGetParamString();

        if ($sQuery && $sGetParam) {
            $sQuery = $sQuery . '&' . $sGetParam;
        } else if ($sGetParam) {
            $sQuery = $sGetParam;
        }

        // Treat query string
        $sQuery = str_replace('&amp;', '&', $sQuery);

        // Default controller - without URL rewriting
        $sFileName = $this->Registry->get('COWIKI_CONTROLLER_NAME');

        if ($this->bRewritable) {
            // Find 'node=xxx' in query string
            preg_match('#^node=([0-9]+)&?#', $sQuery, $aMatches);

            // If we are rewriting, create the new file name which will
            // match the rewrite rule
            if (isset($aMatches[1])) {
                $sFileName = $aMatches[1].'.html';

                // Replace 'node=xxx' in query string, not needed now
                $sQuery = preg_replace('#^node=[0-9]+&?#', '', $sQuery);
            }
        }

        // Append query string?
        if (strlen($sQuery) > 0) {
            $sQuery = '?'.str_replace('&', '&amp;', $sQuery);
        }

        return $sFileName . $sQuery;
    }

    /**
     * Get controller href
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
    public function getControllerHref($sQuery = '') {
        return
            $this->Request->getBasePath()
            . $this->getControllerPath($sQuery);
    }

    /**
     * Add query to request URI, but remove any possible doublettes
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    public function getControllerAction($sQuery = '') {

        $sGetParam = $this->getGetParamString();

        if ($sQuery && $sGetParam) {
            $sQuery = $sQuery . '&' . $sGetParam;
        } else if ($sGetParam) {
            $sQuery = $sGetParam;
        }

        $sReqUri = $this->Request->getRequestUri();

        // Remove possible trailing trash
        $sReqUri = preg_replace('#[&|?]$#', '', $sReqUri);

        // Split passed query string, and remove each occurance of a
        // passed query-key-value-pair in the REQUEST_URI.
        // This removes doublettes, if a key with an existing name is added.
        $sQuery = str_replace('&amp;', '&', $sQuery);

        // Split by key-value-pair
        $aQuery = explode('&', $sQuery);

        // Remove keywords and its values from request uri
        for ($i=0, $n=sizeof($aQuery); $i<$n; $i++) {
            $aKeyValue = explode('=', $aQuery[$i]);

            $sReqUri = preg_replace(
                          '#[?|&]'.$aKeyValue[0].'=[^&|?]*#S',
                           '',
                          $sReqUri
                       );
        }

        $sDeli = '';
        if (strlen($sQuery) > 0) {
            if (strpos($sReqUri, '?') > 0) {
                $sDeli = '&amp;';
            } else {
                $sDeli = '?';
            }
        }

        return $sReqUri . $sDeli . $sQuery;
    }

    /**
     * Redirect response (to controller)
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function redirectToController($sQuery = '') {

        // Don't forget your session id
        if (defined('SID') && SID != '' && ini_get('session.use_trans_sid')) {
            if ($sQuery == '') {
                $sQuery = SID;
            } else {
                $sQuery .= '&'.SID;
            }
        }

        // Get rid of "&amp;" entities
        $sPath = str_replace('&amp;','&',$this->getControllerPath($sQuery));

        header('Location: '
               . $this->Request->getHostUri()
               . $this->Request->getBasePath()
               . $sPath);
        exit;
    }

    /**
     * Redirect response
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function redirectTo($sPath) {

        // Don't forget your session id
        if (defined('SID') && SID != '' && ini_get('session.use_trans_sid')) {
            if (strpos($sPath, '?') > 0)  {
                $sPath .= '&'.SID;
            } else {
                $sPath .= '?'.SID;
            }
        }

        // Assemble path
        $sInfo = $this->Request->getBasePath()
                 . str_replace('&amp;', '&', $sPath);
        $sInfo = str_replace('//', '/', $sPath);

        header('Location: ' . $this->Request->getHostUri() . $sInfo);
        exit;
    }

    /**
     * Get comment href
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     */
    public function getCommentHref($nId, $sQuery = '') {

        $sHref = $this->Request->getBasePath()
                 . $this->getControllerPath($sQuery);

        if ($this->bRewritable) {
            $nPos = strrpos($sHref, '.');
            return substr($sHref, 0, $nPos) . '.' . $nId . '.html';
        } else {
            return $sHref . '&comid=' . $nId;
        }
    }

} // of class

/*
    The machines rose from the
    ashes of nuclear fire.
    Their war to exterminate
    mankind had raged for
    decades, but the final
    battle would not be fought
    in the future.

    It would be fought here,
    in our present.
*/

?>
