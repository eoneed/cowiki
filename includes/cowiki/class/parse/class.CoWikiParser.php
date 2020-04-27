<?php

/**
 *
 * $Id: class.CoWikiParser.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - coWiki parser class
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class CoWikiParser extends WikiParser {

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
            self::$Instance = new CoWikiParser;
        }
        return self::$Instance;
    }

    /**
     * Create link element
     *
     * @access  protected
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    protected function __construct() {}

    // --- Element creators -----------------------------------------------

    /**
     * Create a <link>-element. This method overwrites the parent one.
     *
     * @access  protected
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createLinkElement($sStr) {

        $aStr = explode(')(', $sStr);

        $sRef   = false;
        $sAlias = false;

        if (isset($aStr[0])) {
            $sRef = trim($this->restoreTokens($aStr[0]));
        }
        if (isset($aStr[1])) {
            $sAlias = trim($this->restoreTokens($aStr[1]));
        }

        // Check if reference string is an URI, if so return the <link>
        // element
        $sPattern = '^(http://|https://|ftp://|mailto:|news:).*';

        if (preg_match('=' . $sPattern . '=', $sRef)) {
            return $this->createUriLinkElement($sRef, $sAlias);
        }

        // Obviously the reference string is not an URI. Check if it has
        // a reference to an other web. Means: "webname|documentname"
        $aWebRef = explode('|', $sRef);

        // Do we have a reference to an other web?
        if (sizeof($aWebRef) > 1) {
            $sWeb = trim($aWebRef[0]);
            $sRef = trim($aWebRef[1]);
            return $this->createGlobalLinkElement($sWeb, $sRef, $sAlias);
        }

        // The reference string was not an URI and it has no reference
        // to an other web -> Look up in database.
        return $this->createLocalLinkElement($sRef, $sAlias);
    }

    /**
     * If a link is an URI
     *
     * @access  protected
     * @param   string
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createUriLinkElement($sRef, $sAlias) {
        $sStr =  '<link href="'. $sRef . '">';

        if ($sAlias && $sAlias != '') {
            $sStr .= $sAlias ;
        }

        $sStr .= '</link>';

        return $sStr;
    }

    /**
     * If a link references to an other web (global)
     *
     * @access  protected
     * @param   string
     * @param   string
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createGlobalLinkElement($sWeb, $sRef, $sAlias) {
        $Context = RuntimeContext::getInstance();

        $sWebStr = unescape($sWeb);
        $sRefStr = unescape($sRef);

        // Find web by its name
        $WebNode = $Context->getDocumentDAO()->getWebByName($sWebStr);

        // Found web
        if (is_object($WebNode)) {

            // If document reference string is empty ((web|)), just link
            // to the web directly
            if ($sRefStr == '') {

                $Node = $WebNode;

            } else {

                // Otherwise find a document by its name (in the
                // referenced web)
                $Node = $Context->getDocumentDAO()->getNodeByName(
                            $sRefStr,
                            $WebNode->get('treeId'),
                            'node_id, name'
                        );
            }

            // Found document
            if (is_object($Node)) {

                // Remember resolved reference
                $this->addReferencedNode($Node);

                // Create element
                $sStr = '<link idref="' . $Node->get('id') . '">';
                $sStr .=    $sAlias;
                $sStr .= '</link>';
            }
        }

        if (!is_object($WebNode) || !is_object($Node)) {
            $sStr = '<link strref="' . $sWeb . '¦' . $sRef . '">';
            $sStr .=    $sAlias;
            $sStr .= '</link>';
        }

        return $sStr;
    }

    /**
     * If a link references within a web only (local)
     *
     * @access  protected
     * @param   string
     * @param   string
     * @return  string
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createLocalLinkElement($sRef, $sAlias) {
        $Context = RuntimeContext::getInstance();

        // Get current directory/document object
        $Node = $Context->getCurrentNode();

        $aData   = false;
        $sRefStr = unescape($sRef);

        // Find document by its name (in the local web)
        $NewNode = $Context->getDocumentDAO()->getNodeByName(
                    $sRefStr,
                    $Node->get('treeId'),
                    'node_id, name'
                );

        if (is_object($NewNode)) {

            // Remember resolved reference
            $this->addReferencedNode($NewNode);

            // Create element
            $sStr = '<link idref="' . $NewNode->get('id') . '">';
            $sStr .=    $sAlias;
            $sStr .= '</link>';
        }

        if (!is_object($NewNode)) {
            $sStr = '<link strref="' . $sRef . '">';
            $sStr .=    $sAlias;
            $sStr .= '</link>';
        }

        return $sStr;
    }

} // of class

/*
    Moments lost though time remains
    I am so proud of what we were
    No pain remains, no feeling
    Eternity awaits

    Grant me wings that I might fly
    My restless soul is longing
    No pain remains, no feeling
    Eternity awaits
*/

?>
