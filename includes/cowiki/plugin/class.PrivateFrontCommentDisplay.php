<?php

/**
 *
 * $Id: class.PrivateFrontCommentDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontCommentDisplay
 * #purpose:   Display a comment (a posting)
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      07. May 2003
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
 * Display a comment (a posting) and its thread
 *
 * @package     plugin
 * @subpackage  PrivateFront
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class PrivateFrontCommentDisplay extends AbstractPlugin
                                 implements CustomObserver {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    protected
        $sIdent = null;

    /**
     * Initialize the plugin and check the interface version. This method
     * is used by the PluginLoader only.
     *
     * @access  public
     * @return  boolean true if initialization successful, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
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
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function perform() {

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Comment data access
        $ComDAO = $this->Context->getCommentDAO();

        // Get utility gofer class
        $Util = $this->Context->getUtility();

        // Get requested comment
        $Com = $ComDAO->getCommentById($this->Request->get('comid'));

        // Comment not found?
        if (!is_object($Com)) {
            // FIX: exit to where you came from
            // Exit now
            $this->Response->redirectToController(
                'node='.$Node->get('id')
            );
        }

        // ----------------------------------------------------------------

        // Make this plugin observe the comment DAO. Observed DAO will
        // signal if this plugin should clean up its cache. See "update()"
        $this->Context->getCommentDAO()->addObserver($this);

        // ----------------------------------------------------------------

        // Build ident depending on plugin parameters
        $sIdent = $this->Context->getPluginParamIdent();

        // Build more specific ident
        $sIdent .= '_' . $Com->get('treeId');

        // Remember for implemented observer "update()" method (see below)
        $this->sIdent = $sIdent;

        // If cached result exists, put it out and leave the plugin
# FIX: check behaviour, and make it working
#        if ($sStr = $this->Context->getFromCache($this, $sIdent, 10000000)) {
#            echo $sStr;
#            return true; // leave plugin
#        }

        // ----------------------------------------------------------------

        // Check parameters
        if (!is_object($Com)) {
            $this->Context->addError(404);
            return $this->Context->resume();
        }

        // Check url manipulation (comments that do not belong to document)
        if ($Node->get('id') !== $Com->get('nodeId')) {
            $this->Context->addError(404);
            return $this->Context->resume();
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // REPLY?
            if ($this->Request->has('button_reply')) {

                // Exit now
                $this->Response->redirectToController(
                    'node='.$Node->get('id')
                    .'&comid='.$Com->get('id')
                    .'&cmd='.CMD_REPLYCOM
                );
            }

            // CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // FIX: go back to where you came from
                // Exit now
                $this->Response->redirectToController(
                    'node='.$Node->get('id')
                );
            }
        }

        // ----------------------------------------------------------------

        // Form action
        $this->Template->set(
            'TPL_FORM_ACTION',
            $this->Response->getControllerAction()
        );

        // Form control data
        $sStr =  '<input type="hidden" name="submit"'
                  .' value="'.$this->Context->getSubmitId().'">';

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ----------------------------------------------------------------

        // Set a new page title
        $sStr = $this->Registry->get('DOCUMENT_TITLE_PREFIX');
        $sStr .= __('I18N_SUBJECT') . ': ' . escape($Com->get('subject'));
        $sStr .= $this->Registry->get('DOCUMENT_TITLE_SUFFIX');
        $this->Registry->set('PAGE_TITLE', $sStr);

        // ----------------------------------------------------------------

        // Actions href for "previous" thread
        $sHref = null;
        $nComId = $ComDAO->getPrevThreadId($Com);

        if ($nComId) {
             $sHref = $this->Response->getCommentHref(
                          $nComId,
                          'node='.$Node->get('id')
                       );
        }
        $this->Template->set('TPL_PREV_THREAD_HREF', $sHref);

        // ---

        // Action href for "next" thread
        $sHref = null;
        $nComId = $ComDAO->getNextThreadId($Com);

        if ($nComId) {
             $sHref = $this->Response->getCommentHref(
                          $nComId,
                          'node='.$Node->get('id')
                       );
        }
        $this->Template->set('TPL_NEXT_THREAD_HREF', $sHref);

        // ---

        // Action href for "previous" posting
        $sHref = null;
        $nComId = $ComDAO->getPrevPostingId($Com);

        if ($nComId) {
             $sHref = $this->Response->getCommentHref(
                          $nComId,
                          'node='.$Node->get('id')
                       );
        }
        $this->Template->set('TPL_PREV_POSTING_HREF', $sHref);

        // ---

        // Action href for "next" posting
        $sHref = null;
        $nComId = $ComDAO->getNextPostingId($Com);

        if ($nComId) {
             $sHref = $this->Response->getCommentHref(
                          $nComId,
                          'node='.$Node->get('id')
                       );
        }
        $this->Template->set('TPL_NEXT_POSTING_HREF', $sHref);

        // ----------------------------------------------------------------

        $sStr = escape($Com->get('authorName'));
        $this->Template->set('TPL_ITEM_AUTHOR', $sStr);

        // ---

        $sStr = $this->Context->makeDateTimeRelative($Com->get('created'));
        $this->Template->set('TPL_ITEM_TIME', $sStr);

        // ---

        $this->Template->set('TPL_ITEM_REPLIES', $Com->get('replies'));
        $this->Template->set('TPL_ITEM_VIEWS', $Com->get('views') + 1);

        // ---

        $sStr = escape(cutOff($Com->get('subject'), 55));
        $this->Template->set('TPL_ITEM_SUBJECT', $sStr);

        // ---

        // Prepare content, obfuscate URI if necessary
        $sStr = $Com->get('content');

        if ($this->Registry->get('RUNTIME_EMAIL_OBFUSCATE')) {
            $sStr = $Util->colorizeQuote(
                        obfuscateEmail(
                            $Util->clickable(escape($sStr))
                        )
                    );
        } else {
            $sStr = $Util->colorizeQuote($Util->clickable(escape($sStr)));
        }

        $this->Template->set('TPL_ITEM_CONTENT', $sStr);

        // ---

        // "Reply" button
        $sStr =  '<input type="submit" name="button_reply"';
        $sStr .= ' class="submit" value="'.__('I18N_REPLY').'">';
        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel"';
        $sStr .= ' class="submit" value="'.__('I18N_BACK').'">';
        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // ---

        // Increment views counter
        $ComDAO->incrementViews($Com);

        // Get thread
        $View = new SimpleCommentTreeView();
        $aTplItem = $View->getTreeView(
                        $ComDAO->getThread($Com->get('treeId')),
                        $Com->get('id')
                    );

        $this->Template->set('TPL_THREAD', $aTplItem);

        // ---

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        $sStr = $Tpl->parse('plugin.front.comment.display.tpl');

        // Cache result
        $this->Context->putToCache($this, $sStr, $sIdent);

        // Output result
        echo $this->generateTime($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Although the output of this plugin is cached, the time has to be
     * displayed relative to the current time, let's trick a bit.
     *
     * @access  private
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    private function generateTime($sStr) {
        $sStr = preg_replace_callback(
                  '#\|([0-9]{10,})\|#U',
                  array(&$this, 'generateTimeCallback'),
                  $sStr
                );
        return $sStr;
    }

    // --------------------------------------------------------------------

    /**
     * Generate time callback
     *
     * @access  private
     * @param   The array with RegEx matches from generateTime().
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     generateTime
     */
    private function generateTimeCallback($aMatches) {
        return $this->Context->makeDateTimeRelative($aMatches[1]);
    }

    // --------------------------------------------------------------------

    /**
     * Implement observer "update()" method to clean the cache if necessary
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function update() {
# FIX, $this->sIdent is unknown to the observer's "notifyObservers()"
#        $this->Context->removeFromCache($this, $this->sIdent);
    }

} // of plugin component

/*
    I lost myself in shapeless oceans
    Whose depths concealed more than they showed
    Beliefs obscured by mists around them
    A legacy they'd been bestowed

    Columns of ice paint awkward pictures
    Distorted forms that once seemed real
    Engulfed inside transparent textures
    Billowing curtains as hard as steel

    For all the noise filling the landscape
    Whispers and cries with no reply
    It's quiet here within these boundaries
    And thoughts collect like pools of light

    My eyes divide the sky
    As sirens sound in heaven
    My will brings down the moon
    And shatters it to pieces
*/

?>
