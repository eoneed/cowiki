<?php

/**
 *
 * $Id: class.PrivateFrontCommentEditor.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontCommentEditor
 * #purpose:   Create a new document comment
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      23. April 2003
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
 * coWiki - Create a new document comment
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
class PrivateFrontCommentEditor extends AbstractPlugin {

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
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function perform() {
        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Comment data access
        $ComDAO = $this->Context->getCommentDAO();

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // Check if user is valid
        if (!$CurrUser->get('isValid')) {
            $this->Response->redirectToController(
                'node='.$Node->get('id')
                .'&cmd='.CMD_CHUSR
                .'&ref='.urlencode($this->Env->get('REQUEST_URI'))
            );
        }

        // ----------------------------------------------------------------

        // Incoming parameters
        $sSubmitId = $this->Request->get('submit');
        $sCmd      = $this->Request->get('cmd');
        $nComId    = (int)$this->Request->get('comid');

        // Create a new (empty) CommentItem
        $Edit = new CommentItem();
        $Edit->set('nodeId', $Node->get('id'));

        $nReplyToId = $this->Request->get('comid');

        // ----------------------------------------------------------------

        // Check if it is a reply, and has been not submitted yet. Prepare
        // subject and quotation in that case.
        if ($sSubmitId != $this->Context->getSubmitId()) {
            if ($sCmd == CMD_REPLYCOM && $nComId) {

                $OldCom = $ComDAO->getCommentById($nComId);
                if (is_object($OldCom)) {

                    // Prepare subject
                    $sSubj = $OldCom->get('subject');
                    if (strtolower(substr($sSubj, 0, 4)) != 're: ') {
                        $sSubj = 'Re: '.$sSubj;
                    }
                    $Edit->set('subject', $sSubj);

                    // Prepare quotation body
                    $sContent = quote($OldCom->get('content'));
                    $nCreated = $OldCom->get('created');
                    $sDate = $this->Context->makeDate($nCreated);
                    $sDate .= ' ' . $this->Context->makeTime($nCreated);

                    $sAttrib = sprintf(
                                  __('I18N_COM_ATTRIBUTION'),
                                  $sDate,
                                  $OldCom->get('authorName')
                                );

                    $Edit->set('content', $sAttrib . "\n\n" . $sContent);
                }
            }
        }

        // Check submission
        if ($sSubmitId == $this->Context->getSubmitId()) {

            // Main form: SAVE?
            if ($this->Request->has('button_save')) {

                // Populate the edit-node with posted data
                $Edit->set('id',      $this->Request->get('rec_id'));
                $Edit->set('recTan',  $this->Request->get('rec_tan'));

                $Edit->set('subject', $this->Request->get('subject'));
                $Edit->set('content', $this->Request->get('content'));
                $Edit->set('notify',  $this->Request->get('notify'));

                // SAVE
                if ($ComDAO->store($Edit, $nReplyToId)) {

                    // Exit now
                    $this->Response->redirectToController(
                        'node='.$Edit->get('nodeId')
                        .'&cmd='.CMD_LISTCOM
                    );
                }
            }

            // ------------------------------------------------------------

            // Main form: CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                if ($this->Request->has('ref')) {
                    $this->Response->redirectToController(
                        'node='.$this->Request->get('ref')
                    );
                }

                // Return after "reply" or after "new comment"
                if ($this->Request->has('comid')) {
                    $this->Response->redirectToController(
                        'node='.$Node->get('id')
                        .'&comid='.$this->Request->get('comid')
                    );
                } else {
                    $this->Response->redirectToController(
                        'node='.$Node->get('id')
                    );
                }
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
        $sStr .= '<input type="hidden" name="rec_tan"'
                  .' value="'.$Edit->get('recTan').'">';

        if ($this->Request->has('ref')) {
            $sStr .= '<input type="hidden" name="ref"'
                      .' value="'.$this->Request->get('ref').'">';
        }

        $this->Template->set('TPL_FORM_CONTROL_DATA', $sStr);

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ---

        // Subject
        $sStr = '<input type="text" name="subject" size="';
        $sStr .=  $this->Registry->get('EDIT_DOC_NAME_INPUT_WIDTH').'"';
        $sStr .=  ' maxlength="64" value="'.escape($Edit->get('subject')).'"';
        $sStr .=  ' style="width:100%">';

        $this->Template->set('TPL_ITEM_SUBJECT', $sStr);

        // ----------------------------------------------------------------

        $sStr =   '<textarea wrap="hard" name="content"';
        $sStr .=    ' rows="'.$this->Registry->get('EDIT_DOC_AREA_ROWS').'"';
        $sStr .=    ' cols="72"';
        $sStr .=  '>';
        $sStr .=    escape($Edit->get('content'));
        $sStr .=  '</textarea>';

        $this->Template->set('TPL_ITEM_CONTENT', $sStr);

        // ----------------------------------------------------------------

        // Email notification
        $sStr =  '<input type="checkbox"';
        $sStr .=    ' name="notify"';
        if ($Edit->get('notify')) {
            $sStr .= ' checked="true"';
        }
        $sStr .= '>';

        $this->Template->set('TPL_ITEM_NOTIFY', $sStr);

        // ----------------------------------------------------------------

        // "Save" button
        $sStr =  '<input type="submit" name="button_save" class="submit"';
        $sStr .= ' value="'.__('I18N_SAVE').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // ----------------------------------------------------------------

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.comment.edit.tpl');
    }

} // of plugin component

?>
