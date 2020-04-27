<?php

/**
 *
 * $Id: class.RssManager.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     parse
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
 * The RSSManager creates and reads RSS/RDF feeds.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class RssManager extends Object {

    protected
        $aItems    = array(),
        $sCData    = null,
        $sCurrElm  = null;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {}

    // --------------------------------------------------------------------

    /**
     * Generates and writes a RSS version 1.0 feed
     *
     * @access  public
     * @param   string  The name of the feed file.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.3
     *
     * @todo    [FIX] do not swallow the Exception
     */
    public function writeFeed($sName) {
        try {
            $RssFeed = new RssFeed;
            return $RssFeed->write($sName); // FIX, throw exception in write
        } catch (GenericException $e) {
            return false; // FIX rethrow
        }
    }

    // --------------------------------------------------------------------

    /**
     * Reads a RSS feed
     *
     * @access  public
     * @param   string  The URL of the feed file.
     * @param   string  The output encoding.
     * @return  RssFeed
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function readFeed($sUrl, $sEncoding = '') {
        $RssFeed = new RssFeed;

        $UriInfo = new UriInfo($sUrl);
        if ($UriInfo->isValid() === false) {
            $RssFeed->addError(314, '"' . $sUrl . '" is not a valid URL.');

            return $RssFeed;
        }

        $HttpRequest = HttpRequest::getInstance();
        $aReturn = $HttpRequest->fetchContent($UriInfo);

        $RssFeed->read($aReturn['content'], $sEncoding);

        return $RssFeed;
    }

} // of class

?>
