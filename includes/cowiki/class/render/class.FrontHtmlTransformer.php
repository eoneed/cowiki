<?php

/**
 *
 * $Id: class.FrontHtmlTransformer.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     render
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
 * The FrontHtmlTransformer is a low-end replacement for a XSLT processor.
 * Its main purpose is to transform and render the internal coWiki documents
 * (that are stored as simple XML) into HTML. This class is a Singelton.
 * You can not instantiate this class directly (with $foo = new class), but
 * have to get its instance:
 *
 * Example:
 *   <code>
 *      // This won't work
 *      $Trans = new FrontHtmlTransformer();
 *
 *      // This is the right way
 *      $Trans = FrontHtmlTransformer::getInstance();
 *   </code>
 *
 * @package     render
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class FrontHtmlTransformer extends Object {
    protected static
        $Instance = null;

    protected
        $Context     = null,
        $DocDAO      = null,
        $Response    = null,
        $Registry    = null,
        $Utility     = null,
        $nTopicCount = 0,
        $aNodeBackup = array(),
        $bChangedRef = false,
        $bRepair     = true;

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
            self::$Instance = new FrontHtmlTransformer;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

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
        $this->DocDAO = $this->Context->getDocumentDAO();
        $this->Response = $this->Context->getResponse();
        $this->Registry = $this->Context->getRegistry();
        $this->Utility  = $this->Context->getUtility();
    }

    // --- Simple HTML transformer for front page display -----------------

    /**
     * Return the transformed (converted) HTML
     *
     * @access  public
     * @param   object    The node (document object) you are working on
     * @param   boolean   Determines whether to 'repair' and store document
     *                    references. Repairing means lookup of new
     *                    documents by their name (title).
     * @return  string    The transformed HTML
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function &transform($Node, $bRepair = true) {

        $this->nTopicCount = 1;
        $this->bChangedRef = false;
        $this->bRepair = $bRepair;

        // Set <meta keywords=...>
        $this->Registry->set('META_KEYWORDS', $Node->get('keywords'));

        $sStr = $Node->get('content');

        // Look for new and lost documents, gather the names of existing
        // ones
        $sStr = preg_replace_callback(
            '=<link (strref|idref)\="([^"]*)">(.*)</link>=Usi',
            array($this, '_checkDocReferences'),
            $sStr
        );

        // If we have found a new or lost document, we have to save
        // the modified source
        if ($this->bChangedRef && $this->bRepair) {
            $Node->set('content', $sStr);

            // Store modified source
            $this->Context->getDocumentDAO()->storeContentOnly($Node);
        }

        // --- Now the transforming starts --------------------------------

        // Transform remarks
        $sStr = preg_replace_callback(
            '=<rem>(.*)</rem>=Usi',
            array($this, '_transformRemark'),
            $sStr
        );

        // Transform preformatted text
        $sStr = preg_replace_callback(
            '=<pre>(.*)</pre>=Usi',
            array($this, '_transformPre'),
            $sStr
        );

        // Transform code
        $sStr = preg_replace_callback(
            '=<code>(.*)</code>=Usi',
            array($this, '_transformCode'),
            $sStr
        );

        // Transform posting
        $sStr = preg_replace_callback(
            '=<posting>(.*)</posting>=Usi',
            array($this, '_transformPosting'),
            $sStr
        );

        // Transform missing document (link)
        $sStr = preg_replace_callback(
            '=<link strref\="([^"]*)">(.*)</link>=Usi',
            array($this, '_transformMissingDocument'),
            $sStr
        );

        // Transform exisisting document (link)
        $sStr = preg_replace_callback(
            '=<link idref\="([^"]*)">(.*)</link>=Usi',
            array($this, '_transformExistingDocument'),
            $sStr
        );

        // Transform link with URI
        $sStr = preg_replace_callback(
            '=<link href\="([^"]*)">(.*)</link>=Usi',
            array($this, '_transformUri'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<link topicref\="([^"]*)">(.*)</link>=Usi',
            array($this, '_transformTopic'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<uri strref\="(.*)"/>=Usi',
            array($this, '_transformUri'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<plugin name\="([^"]+)"(.*)/>=Usi',
            array($this, '_transformPlugin'),
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<h([1-6])>(.*)</h\1>=Usi',
            array($this, '_transformHeading'),
            $sStr
        );

        $sStr = preg_replace(
            '=<var name\="([^"]*)"/>=Usi',
            '{%\1%}',
            $sStr
        );

        $sStr = preg_replace_callback(
            '=<q>(.*)</q>=Usi',
            array($this, '_transformQuote'),
            $sStr
        );

        // Get rid of remaining XML elements
        $aArr = array(
                  '<toc>', '</toc>',
                  '<noop>', '</noop>',
                  '<list>', '</list>',
                  '<rem>', '</rem>'
                );
        $sStr = str_replace($aArr, '', $sStr);

        $sStr = $this->finish($sStr, $Node);

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Check document references callback.
     *
     * @access  protected
     * @param   array   RegEx matches defined in preg_replace_callback().
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_checkDocReferences(&$aMatches) {
        $sAlias = $aMatches[3];

        // Remove <noop>s if any
        $sRef = str_replace('<noop>', '', $aMatches[2]);
        $sRef = str_replace('</noop>', '', $sRef);

        if ($aMatches[1] == 'strref') {
            // Check if 'strref' is a reference to an other web.
            // Means: "webname¦documentname"
            $aWebRef = explode('¦', $sRef);

            // Do we have a reference to an other web?
            if (sizeof($aWebRef) > 1) {
                $sGlobalStrRef = $this->_checkGlobalStrRef(
                          $aWebRef[0],
                          $aWebRef[1],
                          $sAlias
                       );
                return $sGlobalStrRef;
            }

            // If the reference does not point to an other web
            $sLocalStrRef = $this->_checkLocalStrRef($sRef, $sAlias);
            return $sLocalStrRef;
        }

        if ($aMatches[1] == 'idref') {

            $Node = $this->DocDAO->getNodeById(
                        $sRef, 'node_id, tree_id, name'
                    );

            if (is_object($Node)) {

                // Save document node for further use
                $this->aNodeBackup[$Node->get('id')] = $Node;

                $sStr =  '<link idref="'.$Node->get('id').'">';
                    // Do we have an alias?
                    if ($Node->get('name') != $sAlias) {
                        $sStr .= $sAlias;
                    }
                $sStr .= '</link>';

            }

            // Referenced document not found, try history
            if (!is_object($Node)) {
                $Node = $this->DocDAO->getHistNodeForId($sRef, 'name');

                if (is_object($Node)) {
                    $this->bChangedRef = true;

                    $sStr =  '<link strref="'.$Node->get('name').'">';
                        // Do we have an alias?
                        if ($Node->get('name') != $sAlias) {
                            $sStr .= $sAlias;
                        }
                    $sStr .= '</link>';
                }
            }

            // "Deleted document"
            if (!is_object($Node)) {
                $this->bChangedRef = true;
                $sStr = '['.__('I18N_DOC_DELETED_DOCUMENT').']';
            }
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Check references to an other web (global) callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_checkGlobalStrRef(&$sWeb, &$sRef, &$sAlias) {

        $sWebStr = trim(unescape($sWeb));
        $sRefStr = trim(unescape($sRef));

        // Find web node by its name
        $WebNode = $this->DocDAO->getWebByName($sWebStr);

        // Found web
        if (is_object($WebNode)) {

            // Find document by its name (in the referenced web)
            $Node = $this->DocDAO->getNodeByName(
                        $sRefStr,
                        $WebNode->get('treeId'),
                        'node_id, name'
                    );

            // Found document
            if (is_object($Node)) {

                // Save document node for further use
                $this->bChangedRef = true;
                $this->aNodeBackup[$Node->get('id')] = $Node;

                $sStr =  '<link idref="'.$Node->get('id').'">';

                // Do we have an alias?
                if ($Node->get('name') != $sAlias) {
                    $sStr .= $sAlias;
                }

                $sStr .= '</link>';
            }
        }

        // Did not found web, keep the link as it is
        if (!is_object($WebNode) || !is_object($Node)) {
            $sStr =   '<link strref="'. $sWeb . '|' . $sRef.'">';
            $sStr .=      $sAlias;
            $sStr .=  '</link>';
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Check references within a web only (local) callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_checkLocalStrRef(&$sRef, &$sAlias) {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        $sRefStr = trim(unescape($sRef));

        $Node = $this->DocDAO->getNodeByName(
            $sRefStr,
            $Node->get('treeId'),
            'node_id, tree_id, name'
        );

        // Save document info for further use
        if (is_object($Node)) {

            // Save document node for further use
            $this->bChangedRef = true;
            $this->aNodeBackup[$Node->get('id')] = $Node;

            $sStr = '<link idref="'.$Node->get('id').'">';
                // Do we have an alias?
                if ($Node->get('name') != $sAlias) {
                    $sStr .= $sAlias;
                }
            $sStr .= '</link>';
        }

        if (!is_object($Node)) {
            // Keep the link as it is
            $sStr   =   '<link strref="'.$sRef.'">';
            $sStr   .=      $sAlias;
            $sStr   .=  '</link>';
        }

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on missing documents callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformMissingDocument(&$aMatches) {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Check if 'strref' is a reference to an other web.
        // Means: "webname|documentname"
        $aWebRef = explode('|', $aMatches[1]);

        // Do we have a reference to an other web?
        if (sizeof($aWebRef) > 1) {
            $sStr =  '<span class="error">[';
            $sStr .=    __('I18N_DOC_UNRESOLVED_WEB_REFERENCE').': ';
            $sStr .=    $aMatches[1];
            $sStr .= ']</span>';
            return $sStr;
        }

        // Remove <noop>s if any
        $aMatches[1] = str_replace('<noop>', '', $aMatches[1]);
        $aMatches[1] = str_replace('</noop>', '', $aMatches[1]);

        // Missing local document, provide a link to edit
        $sQuery = 'cmd=' . CMD_NEWDOC . '&newdocname=' .
                  urlencode(unescape($aMatches[1]));
        if (is_object($Node)) {
            $sQuery .= '&node=' . $Node->get('parentId') .
                       '&refnode='.$Node->get('id');
        }

        $sStr  = '<a href="';
        $sStr .= $this->Response->getControllerHref($sQuery);
        $sStr .=  '">';

        // Do we have an alias?
        $sStr .=    ($aMatches[2] == '') ? $aMatches[1] : $aMatches[2];
        $sStr .= '</a>';
        $sStr .= '<strong class="missing">?</strong>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on existing documents callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformExistingDocument(&$aMatches) {

        $sStr =  '<a href="';
        $sStr .=    $this->Response->getControllerHref('node='.$aMatches[1]);
        $sStr .=  '">';

        // Possible alias
        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $sStr .= $aMatches[2];
        } else {
            $sStr .= escape($this->aNodeBackup[$aMatches[1]]->get('name'));
        }

        $sStr .= '</a>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on remarks callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformRemark(&$aMatches) {
         $sStr = '';
         return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on preformatted text callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformPre(&$aMatches) {
        $sStr = '<pre>' . $aMatches[1] . '</pre>';
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on code callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function _transformCode(&$aMatches) {

        if ($this->Registry->get('COLOR_CODE_COLORIZE')) {
            return '<pre class="code">'
                      .$this->Utility->colorizeCode($aMatches[1])
                   .'</pre>';
        }

        $sStr = '<pre class="code">' . $aMatches[1] . '</pre>';
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Act on posting callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformPosting(&$aMatches) {
        $sStr = '<pre>'
                  .$this->Utility->colorizeQuote($aMatches[1])
               .'</pre>';
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Execute plugins callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformPlugin(&$aMatches) {

        // Reset Layouter attributes
        $this->Context->getLayouter()->init();

        // Set plugin parameters
        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $this->Context->setPluginParam($aMatches[2]);
        }

        // Load plugin
        $sStr = $this->Context->getPluginLoader()->load('Custom'.$aMatches[1]);

        // Clean plugin data
        $this->Context->cleanPluginParams();

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Created (URI) links and obfuscate them callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformUri(&$aMatches) {
        $sStr1 = $aMatches[1];

        if (isset($aMatches[2]) && $aMatches[2] != '') {
            $sStr2 = $aMatches[2];
        } else {

            // Because extremely long URIs won't wrap in browser output
            // and shred the output horizontally, we'll shorten them a bit.
            // If you change it, also change the PrintHtmlTransformer.

            // FIX: This behaviour might me toggleable or configureable in
            // length via the core.conf file.

            $sStr2 = shortenUrl($aMatches[1]);
        }

        $sTarget = ' target="_blank"';

        // No new window for mailto: URIs
        if (substr($sStr1, 0, 7) == 'mailto:') {
            $sTarget = '';
        }

        // obfuscate email if set in config and if no ftp link
        if ($this->Registry->get('RUNTIME_EMAIL_OBFUSCATE')
             && substr($sStr1, 0, 7) == 'mailto:') {

            $sStr1 = obfuscateEmail($sStr1);
            $sStr2 = obfuscateEmail($sStr2);
        }

            $sStr = '<a'.$sTarget.' href="'.$sStr1.'">'.$sStr2.'</a>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Create a topic callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformTopic(&$aMatches) {
        $sUri = $this->Response->getControllerAction();

        $sStr =  '<a href="' . $sUri . '#A' . $aMatches[1] . '">';
        $sStr .=     $aMatches[2];
        $sStr .= '</a>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Create a header (title) callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformHeading(&$aMatches) {
        $sStr =  '<a name="A'. ($this->nTopicCount++) .'"></a>';
        $sStr .= '<h'.$aMatches[1].'>'.$aMatches[2].'</h'.$aMatches[1].'>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Create an inteded text callback.
     *
     * @access  protected
     * @return  string  Converted string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &_transformQuote(&$aMatches) {
        $sStr =  '<blockquote>';
        $sStr .=     $aMatches[1];
        $sStr .= '</blockquote>';

        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Manipulate the finished string, if necessary
     *
     * @access  protected
     * @return  string  The manipulated string.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &finish(&$sStr, $Node) {
        return $sStr;
    }

} // of class

/*
    Rachel:   Do you like our owl?
    Deckard:  Is it artificial?
    Rachel:   Of course it is.
    Deckard:  Must be expensive.
    Rachel:   Very.
    Rachel:   I'm Rachel.
    Deckard:  Deckard.
    Rachel:   Its seems you feel our work is not a benefit to the public.
    Deckard:  Replicants are like any other machines. They are either a
              benefit or a hazard. If they're a benefit, it's not my problem.
    Rachel:   May I ask you a personal question?
    Deckard:  Sure.
    Rachel:   Have you ever retired a human, by mistake?
    Deckard:  No.
    Rachel:   But in your position that is a risk?
    Tyrell:   Is this to be an empathy test? Capilary dilation of the so
              called blush response ... fluctuation of the pupil ...
              involuntary dilation of the iris ...
    Deckard:  We call it Voight-Kampff for short.
    Rachel:   Mr. Deckard, Dr. Elden Tyrell.
    Tyrell:   Demonstrate it. I want to see it work.
    Deckard:  Were is the subject?
    Tyrell:   I want to see it work on a person. I want to see a negative
              before I provide you with a positive.
    Deckard:  What's that gonna prove?
    Tyrell:   Indulge me.
    Deckard:  On you?
    Tyrell:   Try her.
*/

?>
