<?php

/**
 *
 * $Id: class.PrivateFrontHistoryDiff.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontHistoryDiff
 * #purpose:   Show diff between current and historical version of a
 *             document
 * #param:     none
 * #caching:   not used
 * #comment:   none
 * #version:   1.2
 * #date:      03. February 2003
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
 * coWiki - Private front history diff class
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
class PrivateFrontHistoryDiff extends AbstractPlugin {

    // Put in the interface version the plugin works with
    const REQUIRED_INTERFACE_VERSION = 1;

    private
        $sColVoid   = '',
        $sColChng   = '',
        $sColAdd    = '',
        $sColAddWrd = '',
        $sColRemed  = '',
        $sColRemWrd = '';
        
    // --------------------------------------------------------------------

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

    // --------------------------------------------------------------------

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

        // Get color definitions from Registry for faster access later
        $this->sColVoid   = $this->Registry->get('COLOR_DIFF_VOID');
        $this->sColChng   = $this->Registry->get('COLOR_DIFF_CHANGED');
        $this->sColAdd    = $this->Registry->get('COLOR_DIFF_ADDED');
        $this->sColAddWrd = $this->Registry->get('COLOR_DIFF_ADDED_WORD');
        $this->sColRemed  = $this->Registry->get('COLOR_DIFF_REMOVED');
        $this->sColRemWrd = $this->Registry->get('COLOR_DIFF_REMOVED_WORD');
    
        // ---

        // node is deleted
        $bDeleted = false;

        // Get DAOs
        $DocDAO  = $this->Context->getDocumentDAO();
        $UserDAO = $this->Context->getUserDAO();

        // Get current directory/document object
        if (!$OrgNode = $this->Context->getCurrentNode()) {
            $bDeleted = true;
            $OrgNode = $DocDAO->getHistNodeForId($this->Request->get('node'));
        }

        // Get historical directory/document object
        if (!$this->Request->has('histnode')) {
            $HistNode = $DocDAO->getHistNodeForId($OrgNode->get('id'));
        } else {
            $HistNode = $DocDAO->getHistNodeById(
                $this->Request->get('histnode')
            );
        }

        // No backuped history node? Fake one.
        if (!is_object($HistNode)) {
            $HistNode = new DocumentItem();
        }

        // ----------------------------------------------------------------

        // Check submission
        if ($this->Request->get('submit') == $this->Context->getSubmitId()) {

            // RECOVER?
            if ($this->Request->has('button_recover')) {
                // Go to history recover
                $sQuery = 'node=' . $OrgNode->get('id') .
                          '&histnode=' . $HistNode->get('histId') .
                          '&cmd=' . CMD_RECOVHIST;
                if ($this->Request->has('module')) {
                    $sQuery .= '&module=' . $this->Request->get('module');
                }
                $this->Response->redirectToController($sQuery);
            }

            // CANCEL?
            if ($this->Request->has('button_cancel')
                || $this->Request->has('button_close_x')) {

                // Go directly to document view?
                if ($this->Request->has('refnode')) {
                    $this->Response->redirectToController(
                        'node='.$this->Request->get('refnode')
                    );
                }

                // Go back to history view
                $sQuery = 'node='.$OrgNode->get('id').'&cmd='.CMD_SHOWHIST;
                if ($this->Request->has('module')) {
                    $sQuery .= '&module=' . $this->Request->get('module');
                }
                $this->Response->redirectToController($sQuery);
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

        // ---

        // Errors
        $this->Template->set(
            'TPL_ITEM_MESSAGE',
            $this->Context->getErrorQueueFormatted()
        );

        // ----------------------------------------------------------------

        // Set defaults for error case
        $this->Template->set('TPL_ITEM_ORIG_NAME',      '');
        $this->Template->set('TPL_ITEM_ORIG_MODE',      '');
        $this->Template->set('TPL_ITEM_ORIG_USER',      '');
        $this->Template->set('TPL_ITEM_ORIG_GROUP',     '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_DATE',  '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_NAME',  '');
        $this->Template->set('TPL_ITEM_ORIG_MOD_LOGIN', '');
        $this->Template->set('TPL_ITEM_ORIG_REVISION',  '');

        $this->Template->set('TPL_ITEM_HIST_NAME',      '');
        $this->Template->set('TPL_ITEM_HIST_MODE',      '');
        $this->Template->set('TPL_ITEM_HIST_USER',      '');
        $this->Template->set('TPL_ITEM_HIST_GROUP',     '');
        $this->Template->set('TPL_ITEM_HIST_MOD_DATE',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_NAME',  '');
        $this->Template->set('TPL_ITEM_HIST_MOD_LOGIN', '');
        $this->Template->set('TPL_ITEM_HIST_REVISION',  '');

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM_HIST_RESTRICTED', '');
        $this->Template->set('TPL_ITEM_HIST_CONTENT',    '');
        $this->Template->set('TPL_ITEM_ORIG_RESTRICTED', '');
        $this->Template->set('TPL_ITEM_ORIG_CONTENT',    '');

        // ----------------------------------------------------------------

        // If the current document (original) is forbidden, do not show
        // the historical one.
        $bOrgForb = false;

        // Set flag if current document and historical docment do not
        // share the same node id. Avoids lurking in different documents by
        // manipulating the URL.
        $bIdent = false;

        if ($HistNode) {
            $bIdent = $OrgNode->get('id') === $HistNode->get('id');
        }

        // ----------------------------------------------------------------

        // Get ReverseParser
        $RevParser = CoWikiReverseParser::getInstance();

        // Parse into raw coWiki representation
        $sOrg  = $RevParser->parse($OrgNode->get('content')) . "\n";
        $sHist = $RevParser->parse($HistNode->get('content')). "\n";

        // Get diff
        $this->Template->set('TPL_ITEM_CONTENT', $this->diff($sOrg, $sHist));

        // ----------------------------------------------------------------

        // Check access and transform original document
        if (!$OrgNode || $OrgNode->get('id') == 0) {
            $this->Context->addError(404);        // Not found
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_ORIG_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else if (!$OrgNode->isReadable()) {
            // Display of historical document is automatically forbidden too
            $bOrgForb = true;

            $this->Context->addError(403);        // Forbidden
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_ORIG_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else {

            // Get user & group objects
            $User = $UserDAO->getUserByUid($OrgNode->get('userId'));
            $Group = $UserDAO->getGroupByGid($OrgNode->get('groupId'));

            // Set template values
            $this->Template->set(
                'TPL_ITEM_ORIG_NAME',
                escape(cutOff($OrgNode->get('name'), 100))
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_MODE',
                $OrgNode->getAccessModeAsString()
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_USER',
                $User ? $User->get('login') : $OrgNode->get('userId')
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_GROUP',
                $Group ? $Group->get('name') : $OrgNode->get('groupId')
            );

            $this->Template->set(
                'TPL_ITEM_ORIG_MOD_DATE',
                $this->Context->makeDateTimeRelative(
                    $OrgNode->get('modified')
                )
            );

            $UserObj = $UserDAO->getUserByUid($OrgNode->get('modifiedByUid'));

            $sLogin = $UserObj
                          ? $UserObj->get('login')
                          : $OrgNode->get('modifiedByUid');

            $sName = $UserObj
                          ? $UserObj->get('name')
                          : $OrgNode->get('modifiedByUid');

            $this->Template->set('TPL_ITEM_ORIG_MOD_LOGIN', $sLogin);
            $this->Template->set('TPL_ITEM_ORIG_MOD_NAME', $sName);

            $this->Template->set(
                'TPL_ITEM_ORIG_REVISION',
                $OrgNode->get('revision') . ' ('.__('I18N_CURRENT').')'
            );

            // Unset previousy set template variable
            $this->Template->remove('TPL_ITEM_ORIG_RESTRICTED');
        }

        // ----------------------------------------------------------------

        // Check access and transform historical document
        if (!$HistNode || $HistNode->get('id') == 0) {
            $this->Context->addError(404);        // Not found
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_HIST_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else if (!$HistNode->isReadable() || $bOrgForb || !$bIdent) {
            $this->Context->addError(403);        // Forbidden
            $this->Context->addError(111);        // Resumed
            $this->Template->set(
                'TPL_ITEM_HIST_CONTENT',
                $this->Context->getErrorQueueFormatted()
            );

        } else {

            // Get user & group objects
            $User = $UserDAO->getUserByUid($HistNode->get('userId'));
            $Group = $UserDAO->getGroupByGid($HistNode->get('groupId'));

            // Set template values
            $this->Template->set(
                'TPL_ITEM_HIST_NAME',
                escape(cutOff($HistNode->get('name'), 100))
            );

            $this->Template->set(
                'TPL_ITEM_HIST_MODE',
                $HistNode->getAccessModeAsString()
            );

            $this->Template->set(
                'TPL_ITEM_HIST_USER',
                $User ? $User->get('login') : $HistNode->get('userId')
            );

            $this->Template->set(
                'TPL_ITEM_HIST_GROUP',
                $Group ? $Group->get('name') : $HistNode->get('groupId')
            );

            $this->Template->set(
                'TPL_ITEM_HIST_MOD_DATE',
                $this->Context->makeDateTimeRelative(
                    $HistNode->get('modified')
                )
            );

            $UserObj = $UserDAO->getUserByUid($HistNode->get('modifiedByUid'));

            $sLogin = $UserObj
                          ? $UserObj->get('login')
                          : $OrgNode->get('modifiedByUid');

            $sName = $UserObj
                          ? $UserObj->get('name')
                          : $OrgNode->get('modifiedByUid');

            $this->Template->set('TPL_ITEM_HIST_MOD_LOGIN', $sLogin);
            $this->Template->set('TPL_ITEM_HIST_MOD_NAME', $sName);

            $this->Template->set(
                'TPL_ITEM_HIST_REVISION',
                $HistNode->get('revision') . ' ('.__('I18N_OLD').')'
            );

            // Unset previousy set template variable
            $this->Template->remove('TPL_ITEM_HIST_RESTRICTED');
        }

        // ----------------------------------------------------------------

        if ($this->Template->has('TPL_ITEM_ORIG_RESTRICTED')
            || $this->Template->has('TPL_ITEM_HIST_RESTRICTED')) {
              $this->Template->set('TPL_ITEM_RESTRICTED', true);
        }

        // ----------------------------------------------------------------

        // "Recover" button
        $sStr =  '<input type="submit" name="button_recover" class="submit"';
        $sStr .= ' value="'.__('I18N_RECOVER').'">';

        $this->Template->set('TPL_ITEM_BUTTON1', $sStr);

        // "OK/Cancel" button
        $sStr =  '<input type="submit" name="button_cancel" class="submit"';
        $sStr .= ' value="'.__('I18N_CANCEL').'">';

        $this->Template->set('TPL_ITEM_BUTTON2', $sStr);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.history.diff.tpl');
    }

    // --------------------------------------------------------------------

    // Simple diff. This implementation is based on "diff2html" (an awk
    // script) by Daniel Lundin <daniel@codefactory.se>. Credits!

    /**
     * diff
     *
     * @access  private
     * @param   string
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    private function diff($sStr1, $sStr2) {

        // Check if path to "diff" is set and if safe_mode if "off"
        if ($this->Registry->get('PATH_DIFF') == '' || ini_get('safe_mode')) {
            return '';
        }

        $sOrgFile  = uniqid($this->Context->getTempFileName('org_'), true);
        $sHistFile = uniqid($this->Context->getTempFileName('hist_'), true);

        // Write temporary file
        try {
            $Out = new FileOutputStream($sOrgFile);
            $Out->write($sStr1);
            $Out->close();

        } catch (Exception $e) {
            return '';
        }

        // Write temporary file
        try {
            $Out = new FileOutputStream($sHistFile);
            $Out->write($sStr2);
            $Out->close();
            $bSuccess2 = true;

        } catch (Exception $e) {
            return '';
        }

        // Try to execute the "diff" command
        $sCmd = $this->Registry->get('PATH_DIFF')
                .'diff -U 65535 -u ' . $sHistFile . ' ' . $sOrgFile;

        $sFlag = @exec($sCmd, $aDiff);

        // Clean up
        @unlink($sOrgFile);
        @unlink($sHistFile);

        // ----------------------------------------------------------------

        // Get rid of header lines
        $aDiff  = array_splice($aDiff, 3);

        // Init defaults
        $sOrg  = '';
        $sHist = '';
        $aItem = array();
        $aTplItem = array();

        $sLineState = $sCurrState = $sBlock = '';

        for ($i=0, $n=sizeof($aDiff); $i<$n; $i++) {

            if (!empty($aDiff[$i])) {
                $sLineState = $aDiff[$i]{0};
            }

            $sLine = escape(substr($aDiff[$i], 1));

            if ($sLineState == $sCurrState) {

                $sBlock = $sBlock
                          . ($sBlock == '' ? '' : "\n")
                          . $sLine;

            } else {

                if ($sLineState == "+" && $sCurrState == "-") {
                    $sDiffBlock = $sBlock;
                } else {
                    $aRes = $this->generateBlock(
                                $sBlock,
                                $sDiffBlock,
                                $sLineState,
                                $sCurrState
                            );

                    // Restore string

                    $aRes[1] = str_replace(
                                  "\x02",
                                  "<span style='color:".$this->sColRemWrd."'>",
                                  $aRes[1]
                               );
                    $aRes[1] = str_replace(
                                  "\x04",
                                  "</span>",
                                  $aRes[1]
                               );

                    $aRes[3] = str_replace(
                                  "\x03",
                                  "<span style='color:".$this->sColAddWrd."'>",
                                  $aRes[3]
                               );
                    $aRes[3] = str_replace(
                                  "\x04",
                                  "</span>",
                                  $aRes[3]
                               );

                    $aItem['COLOR1'] = $aRes[0];
                    $aItem['BLOCK1'] = str_replace("\n","<br />",$aRes[1]);
                    $aItem['COLOR2'] = $aRes[2];
                    $aItem['BLOCK2'] = str_replace("\n","<br />",$aRes[3]);

                    $aTplItem[] = $aItem;
                }

                $sBlock = $sLine;
                $sCurrState = $sLineState;
            }
        }

        $aRes = $this->generateBlock(
                    $sBlock,
                    $sDiffBlock,
                    $sLineState,
                    $sCurrState
                );

        // Restore string

        $aRes[1] = str_replace(
                      "\x02",
                      "<span style='color:".$this->sColRemWrd."'>",
                      $aRes[1]
                    );
        $aRes[1] = str_replace(
                      "\x04",
                      "</span>",
                      $aRes[1]
                   );

        $aRes[3] = str_replace(
                      "\x03",
                      "<span style='color:".$this->sColAddWrd."'>",
                      $aRes[3]
                    );
        $aRes[3] = str_replace(
                      "\x04",
                      "</span>",
                      $aRes[3]
                   );

        $aItem['COLOR1'] = $aRes[0];
        $aItem['BLOCK1'] = str_replace("\n", "<br />", $aRes[1]);
        $aItem['COLOR2'] = $aRes[2];
        $aItem['BLOCK2'] = str_replace("\n", "<br />", $aRes[3]);

        $aTplItem[] = $aItem;

        // Identical?
        if (sizeof($aTplItem) <= 1) {
            $aItem['COLOR1'] = $aRes[0];
            $aItem['BLOCK1'] = '<div align="center">'
                                  .'<strong>'
                                    .__('I18N_IDENTICAL')
                                  .'</strong>'
                               .'</div>';
            $aItem['COLOR2'] = $aRes[2];
            $aItem['BLOCK2'] = $aItem['BLOCK1'];

            $aTplItem[] = $aItem;
        }

        return $aTplItem;
    }

    // === HELPER =========================================================

    /**
     * Generate diff block. Parameters has to passed by reference!
     *
     * @access  private
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    private function generateBlock(&$sBlock, &$sDiffBlock,
                                   &$sLineState, &$sCurrState) {

        // Default values
        $sCol1 = $sCol2 = $this->Registry->get('COLOR_BGCOLOR');
        $sBlk1 = $sBlk2 = '';

        // Changed lines
        if ($sDiffBlock != '') {
            $aDiff = $this->diffString($sDiffBlock, $sBlock);

            $sBlk1 = $aDiff[0];
            $sBlk2 = $aDiff[1];
            $sCol1 = $sCol2 = $this->sColChng;
            $sDiffBlock = '';

        // Common lines
        } else if ($sCurrState == ' ') {
            $sBlk1 = $sBlk2 = $sBlock;
            $sCol1 = $sCol2 = $this->Registry->get('COLOR_BGCOLOR');

        // Added lines
        } else if ($sCurrState == '+') {
            $sBlk1 = '';
            $sBlk2 = $sBlock;
            $sCol1 = $this->sColVoid;
            $sCol2 = $this->sColAdd;

        // Removed lines
        } else if ($sCurrState == '-') {
            $sBlk1 = $sBlock;
            $sBlk2 = '';
            $sCol1 = $this->sColRemed;
            $sCol2 = $this->sColVoid;
        }

        // Clear block buffer
        $sBlock = $sDiffBlock = '';

        return array($sCol1, $sBlk1, $sCol2, $sBlk2);
    }

    // --------------------------------------------------------------------

    /**
     * Emphasize difference between strings
     *
     * @access  private
     * @param   string
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    private function diffString($sStr1, $sStr2) {

        $aArr1 = explode(' ', trim($sStr1));
        $aArr2 = explode(' ', trim($sStr2));

        $nMax1 = sizeof($aArr1);
        $nMax2 = sizeof($aArr2);

        $nStart1 = $nStart2 = 0;
        $nJump1 = $nJump2 = 0;

        while ($nStart1 < $nMax1 && $nStart2 < $nMax2){

            $nPos11 = $nPos12 = $nStart1;
            $nPos21 = $nPos22 = $nStart2;
            $nDiff2 = 0;

            while ($nPos11 < $nMax1 && $aArr1[$nPos11] != $aArr2[$nPos21]) {
                ++$nPos11;
            }

            if ($nPos11 == $nMax1) {
                $nStart2++;
                continue;
            }

            if (($nDiff1 = $nPos11 - $nPos21) > 1) {
                while ($nPos22 < $nMax2 && $aArr1[$nPos12] != $aArr2[$nPos22]) {
                    ++$nPos22;
                }

                $nDiff2 = $nPos22 - $nPos12 + $nJump2;
            }

            if ($nPos22 == $nMax2) {
                $nStart1++;
                continue;
            }

            $nDiff1 += $nJump1;

            if ($nDiff1 >= $nDiff2 && $nDiff2) {

                unset($aArr1[$nPos12], $aArr2[$nPos22]);
                $nStart1 = $nPos12 + 1;
                $nStart2 = $nPos22 + 1;
                $nJump2 = $nDiff2;

            } else {

                unset($aArr1[$nPos11], $aArr2[$nPos21]);
                $nStart1 = $nPos11 + 1;
                $nStart2 = $nPos21 + 1;
                $nJump1 = $nDiff1;
            }
        }

        $aSave1 = explode(' ', $sStr1);

        while (list($sKey,) = each($aArr1)) {
            $aSave1[$sKey] = "\x02" . $aSave1[$sKey] . "\x04";
        }

        $aSave2 = explode(' ', $sStr2);

        while (list($sKey,) = each($aArr2)) {
            $aSave2[$sKey] = "\x03" . $aSave2[$sKey] . "\x04";
        }

        return array(join(' ', $aSave1), join(' ', $aSave2));
    }

} // of plugin component

/*
    May you find solace in the gentle arms of sleep
    Despite the wolves outside your door
    In time you will see them all as harmless
    And their idle threats easy to ignore

    And if ever fate should choose to smite you
    Stand your ground, never walk away
    Please don't ever let the world defeat you
    Don't get buried in its decay

    As you drift into the gauzy realm of dreams
    May you take comfort in the thought that you are safe
    For it only takes a fraction of a second
    For all of this to change

    As you sink beneath the soothing streams of time
    May you be thankful that you had another day
    For there comes a time when each of us will enter
    A sleep from which we will never wake

    Close your eyes now, if only for a moment
    For it's time you get some rest
    The wolves are gone and nothing here can harm you
    Let go of your fragile consciousness
*/

?>
