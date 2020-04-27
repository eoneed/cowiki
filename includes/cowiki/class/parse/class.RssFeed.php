<?php

/**
 *
 * $Id: class.RssFeed.php 27 2011-01-09 12:37:59Z eoneed $
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
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 27 $
 *
 */

/**
 * The RSSFeed represents a RRS/RDF feed.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.3
 */
class RssFeed extends Xml {

    const FORMAT_UNKNOWN  = 0;
    const FORMAT_RSS_0_9  = 1;
    const FORMAT_RSS_0_91 = 2;
    const FORMAT_RSS_0_92 = 3;
    const FORMAT_RSS_0_93 = 4;
    const FORMAT_RSS_0_94 = 5;
    const FORMAT_RSS_1_0  = 6;
    const FORMAT_RSS_2_0  = 7;

    // --------------------------------------------------------------------

    protected
        $nFormat = 0,
        $Items = null,
        $Errors = null;

    // --------------------------------------------------------------------
    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function __construct() {
        $this->Errors = new Vector;
    }

    // --------------------------------------------------------------------

    /**
     * ...
     *
     * @access  public
     * @param   string    ...
     * @param   string    ...
     * @return  boolean
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     *
     * @todo    Fix [D11N]
     */
    public function read($sFeed, $sEncoding = '') {
        $this->Items = new Vector;

        $aFeed = array(
            'channel' => null,
            'items'   => array(),
            'errors'  => array()
        );

        $XmlEventParser = new XmlEventParser;
        $XmlEventParser->parse($sFeed);

        $aItems = $XmlEventParser->getItems($sEncoding);

        switch ($aItems[Xml::ELM_NAME]) {

            case 'rss':
                if (isset($aItems[Xml::ELM_ATTR]['version'])) {
                    switch ($aItems[Xml::ELM_ATTR]['version']) {
                        case '0.91':
                            $this->nFormat = self::FORMAT_RSS_0_91;
                            break;
                        case '0.92':
                            $this->nFormat = self::FORMAT_RSS_0_92;
                            break;
                        case '0.93':
                            $this->nFormat = self::FORMAT_RSS_0_93;
                            break;
                        case '0.94':
                            $this->nFormat = self::FORMAT_RSS_0_94;
                            break;
                        case '2.0':
                            $this->nFormat = self::FORMAT_RSS_2_0;
                            break;
                    }
                }
                break;

            case 'rdf:RDF':
                if (isset($aItems[Xml::ELM_ATTR]['xmlns'])) {
                    switch ($aItems[Xml::ELM_ATTR]['xmlns']) {
                        case 'http://my.netscape.com/rdf/simple/0.9/':
                            $this->nFormat = self::FORMAT_RSS_0_9;
                            break;
                        case 'http://purl.org/rss/1.0/':
                            $this->nFormat = self::FORMAT_RSS_1_0;
                            break;
                        default:
                            $this->nFormat = self::FORMAT_RSS_0_9;
                    }
                }
                break;

            default:
                $this->nFormat = self::FORMAT_UNKNOWN;
                $this->addError(316);
                return false;
        }

        foreach (array_keys($aItems[Xml::ELM_INDEX]) as $nIndex) {
            $aItem = &$aItems[Xml::ELM_INDEX][$nIndex];

            switch ($aItem[Xml::ELM_NAME]) {
                case 'channel':
                    $this->readChannel($aItem);
                    break;
                case 'item':
                    $this->readItem($aItem);
                    break;
                default:
                    //printr($aItem);
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Reads a RSS <channel>
     *
     * @access  protected
     * @param   array      The channel
     * @return  boolean
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     *
     * @todo    parse <items> element
     */
    protected function readChannel(&$aChannel) {
        if ('channel' != $aChannel[Xml::ELM_NAME]) {
            return false;
        }

        if (isset($aChannel[Xml::ELM_ATTR]['rdf:about'])) {
            $this->set('about', $aChannel[Xml::ELM_ATTR]['rdf:about']);
        }

        foreach (array_keys($aChannel[Xml::ELM_INDEX]) as $nIndex) {
            $aElm = &$aChannel[Xml::ELM_INDEX][$nIndex];

            if (isset($aElm[Xml::ELM_CDATA])) {
                $this->set($aElm[Xml::ELM_NAME], $aElm[Xml::ELM_CDATA]);
            } else {
                if (in_array($this->nFormat, array(self::FORMAT_RSS_0_91,
                    self::FORMAT_RSS_0_92, self::FORMAT_RSS_2_0))) {
                    if ($aElm[Xml::ELM_NAME] == 'item') {
                        $this->readItem($aElm);
                    }
                }
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Reads a RSS <item>
     *
     * @access  protected
     * @param   array      The item
     * @return  boolean
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    protected function readItem(&$aItem) {
        if ('item' != $aItem[Xml::ELM_NAME]) {
            return false;
        }

        $Item = new RssItem;

        if (isset($aItem[Xml::ELM_ATTR]['rdf:about'])) {
            $Item->set('about', $aItem[Xml::ELM_ATTR]['rdf:about']);
        }

        foreach (array_keys($aItem[Xml::ELM_INDEX]) as $nIndex) {
            $aElm = &$aItem[Xml::ELM_INDEX][$nIndex];

            if (isset($aElm[Xml::ELM_CDATA])) {
                $Item->set($aElm[Xml::ELM_NAME], $aElm[Xml::ELM_CDATA]);
            }
        }

        $this->Items->add($Item);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Get the items Vector
     *
     * @access  public
     * @return  Vector
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getItems() {
        return $this->Items;
    }

    // --------------------------------------------------------------------

    /**
     * Get the errors Vector
     *
     * @access  public
     * @return  Vector
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getErrors() {
        return $this->Errors;
    }

    // --------------------------------------------------------------------

    /**
     * Add an error
     *
     * @access  protected
     * @param   integer    The error code.
     * @param   string     The error message.
     * @return  void
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function addError($iCode, $sMessage = '') {
        $Error = new Object;
        $Error->set('code', $iCode);
        if (!empty($sMessage)) {
            $Error->set('message', $sMessage);
        }
        $this->Errors->add($Error);
    }

    // --------------------------------------------------------------------

    public function write($sName) {
        $Context = RuntimeContext::getInstance();
        $sCowikiRoot = $Context->getEnvironment()->get('COWIKI_ROOT');

        if (!is_writable($sCowikiRoot)) {
            return;
        }

        // Get registry, request and response
        $Registry = $Context->getRegistry();
        $Request  = $Context->getRequest();
        $Response = $Context->getResponse();

        // Get DAOs
        $DocDAO = $Context->getDocumentDAO();
        $UserDAO = $Context->getUserDAO();

        // Get base URI of this HTTP document
        $sUri = 'http://' . $Request->getServerName() . $Request->getBasePath();

        $sNs =  "<rdf:RDF\r\n";
        $sNs .= " xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'\r\n";
        $sNs .= " xmlns:dc='http://purl.org/dc/elements/1.1/'\r\n";
        $sNs .= " xmlns='http://purl.org/rss/1.0/'\r\n";
        $sNs .= ">\r\n";

        try {
            $Out = new FileOutputStream($sCowikiRoot . '/' . $sName);
        } catch (Exception $e) {
            throw $e; // rethrow
        }

        $Node = $Context->getDocumentDAO()
                    ->getRecentlyChangedNodesForGuest(15);

        $sTitle = escape(cutoff($Registry->get('ABOUT_TITLE'), 40));
        $sDesc  = escape($Registry->get('ABOUT_DESCRIPTION'));

        $sStr =   "<?xml version='1.0' encoding='UTF-8'?>\r\n\r\n";
        $sStr .=    $sNs . "\r\n";

        $sStr .=    "<channel rdf:about='". $sUri . $sName . "'>\r\n";
        $sStr .=    "  <title>" . $sTitle . "</title>\r\n";
        $sStr .=    "  <link>" . $sUri . "</link>\r\n";
        $sStr .=    "  <description>" . $sDesc  . "</description>\r\n\r\n";

        $sSeq  = array();
        $aItem = array();

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {

            // Get current web name
            $Web = $DocDAO->getWebById($Obj->get('treeId'));

            $sTitle = $Obj->get('name');
            $sTitle = escape($Web->get('name') . ': ' . $sTitle);

            $sLink  = $Response->getControllerHref(
                          'node='.$Obj->get('id')
                      );
            $sLink  = $sUri . substr($sLink, 1);

            // Get and cut the summary
            $sSummary = cutOffWord(escape(trim($Obj->get('summary'))), 250);

            // Get creator
            $User = $UserDAO->getUserByUid($Obj->get('authorId'));
            $sName = escape($User->get('name'));

            // Generate date
            $sFormat = 'Y-m-d H:i:s O';
            $sDate = date($sFormat, $Obj->get('modified'));

            // ---

            $sSeq = "      <li rdf:resource='" . $sLink . "' />\r\n";

            $sItem =  "<item rdf:about='" . $sLink . "'>\r\n";
            $sItem .= "  <title>" . $sTitle . "</title>\r\n";
            $sItem .= "  <link>" . $sLink . "</link>\r\n";
            if (!empty($sSummary)) {
                $sItem .= "  <description>" . $sSummary . "</description>\r\n";
            } else {
                $sItem .= "  <description />\r\n";
            }
            $sItem .= "  <dc:creator>" . $sName . "</dc:creator>\r\n";
            $sItem .= "  <dc:date>" . $sDate . "</dc:date>\r\n";
            $sItem .= "</item>\r\n\r\n";

            $aSeq[]  = $sSeq;
            $aItem[] = $sItem;
        }

        // Concat sequence strings
        $sStr .=    "  <items>\r\n";
        $sStr .=    "    <rdf:Seq>\r\n";

        for ($i=0, $n=sizeof($aSeq); $i<$n; $i++) {
            $sStr .= $aSeq[$i];
        }

        $sStr .=    "    </rdf:Seq>\r\n";
        $sStr .=    "  </items>\r\n\r\n";

        $sStr .=    "</channel>\r\n\r\n";

        // Concat item strings
        for ($i=0, $n=sizeof($aItem); $i<$n; $i++) {
            $sStr .= $aItem[$i];
        }

        try {
            $Out->write($sStr . "</rdf:RDF>\r\n");
            $Out->close();
        } catch (Exception $e) {
            throw $e; // rethrow
        }
    }

} // of class

?>
