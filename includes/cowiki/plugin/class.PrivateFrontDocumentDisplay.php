<?php

/**
 *
 * $Id: class.PrivateFrontDocumentDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontDocumentDisplay
 * #purpose:   Display the content of document node
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      01. November 2002
 * #author:    Daniel T. Gorski <daniel.gorski@develnet.org>
 *
 * Please read and understand the README.PLUGIN file before you touch
 * something here.
 * </pre>
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Display the content of document node
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class PrivateFrontDocumentDisplay extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    /**
     * Init
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
    public function init() {
        return parent::init(self::REQUIRED_INTERFACE_VERSION);
    }

    /**
     * Perform
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {

        // Search engine query string names
        $aSrch['yahoo']         = 'p';
        $aSrch['altavista']     = 'q';
        $aSrch['google']        = 'q';
        $aSrch['lycos']         = 'query';
        $aSrch['hotbot']        = 'query';
        $aSrch['msn']           = 'q';
        $aSrch['webcrawler']    = 'qkw';
        $aSrch['excite']        = 'qkw';
        $aSrch['netscape']      = 'query';
        $aSrch['mamma']         = 'query';
        $aSrch['alltheweb']     = 'q';
        $aSrch['northernlight'] = 'q';
        $aSrch['fireball']      = 'q';

        // ---

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        if (!$Node || $Node->get('id') == 0) {
            $this->Context->addError(404);        // Not found
            $this->Context->resume();             // Do not stop script
            return true;
        }

        // ----------------------------------------------------------------

        // Check validity and user access
        if (!$Node->isReadable()) {

            // Robots are not permitted to spider this area or to follow
            // links
            $this->Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');

            $this->Context->addError(403);        // Forbidden
            $this->Context->resume();             // Do not stop script
            return true;
        }

        // Increment views counter
        $this->Context->getDocumentDao()->incrementViews($Node);

        // ----------------------------------------------------------------

        // Transform
        $sStr = FrontHtmlTransformer::getInstance()->transform($Node);

        // Do not highlight document search strings
        if (!$this->Registry->get('PLUGIN_DOC_DISPLAY_SEARCH_HIGHLIGHT')) {
            echo $sStr;
            return true;
        }

        // Highlight search strings
        $UriInfo = new UriInfo($this->Request->getReferrer());

        // Check if we have an query string in the referrer
        $sQuery = '';
        if ($UriInfo->get('query')) {

            // Check for engine and its query string name
            foreach($aSrch as $k => $v) {

                if (strpos($UriInfo->get('host'), $k) !== false) {
                    parse_str($UriInfo->get('query'), $aQuery);

                    if (isset($aQuery[$v])) {
                        $sQuery = $aQuery[$v];
                        break;
                    }
                }
            }
        }

        // No query string name found, output and leave
        if ($sQuery == '') {
            echo $sStr;
            return true;
        }

        // Clean query
        $sQuery = preg_replace('/[^A-Za-z0-9 ]/', ' ', $sQuery);
        $aQuery = split(' ', $sQuery);

        // Gather query items
        $sQuery = '';
        for ($i=0, $n=sizeof($aQuery); $i<$n; $i++) {

            $sItem = trim($aQuery[$i]);

            if (strlen($sItem) < 3) {
                continue;
            }

            $sQuery .= $sItem . '|';
        }

        // Remove trailing pipe
        $sQuery    =    substr($sQuery, 0, -1);

        // If query empty, output and leave
        if ($sQuery == '') {
            echo $sStr;
            return true;
        }

        $sStr = preg_replace_callback(
            '#((<[^>]+)|' . $sQuery. ')#i',
            array(&$this, 'highlight'),
            $sStr
        );

        echo $sStr;
    }

    /**
     * Highlight
     *
     * @access  public
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function highlight(&$aMatches) {
        if (isset($aMatches[2]) && $aMatches[2] == $aMatches[1]) {
            return $aMatches[1];
        }

        // If changing this, look at "processRemainedTemplateVariables()"
        // in "core.finish.php" file. Constants would not be recognized
        // otherwise!
        return '<span style="color:'.$this->Registry->get('COLOR_FOUND').'">'
                .$aMatches[1]
                .'</span>';
    }

} // of plugin component

/*
    Love has gone a-rocketing,
    that is not the worst
    I could do without the thing,
    and not be the first

    Joy has gone the way it came,
    that is nothing new
    I could get along the same,
    many people do

    Dig for me the narrow bed,
    now I am bereft
    all my pretty hates are dead,
    and what have I left?
*/

?>
