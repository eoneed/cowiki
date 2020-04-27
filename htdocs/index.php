<?php

/**
 *
 * $Id: index.php 30 2011-01-09 14:48:12Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    htdocs
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 30 $
 *
 */

/**
 * Basic imports, checks and inits
 *
 * @package  htdocs
 * @access   public
 *
 * @author   Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since    coWiki 0.3.0
 */

    if (extension_loaded('http')) {

        echo '<a href="/status.php?hi=580#status580">';
        echo   '580 - Extension "http" installed.';
        echo '</a>';
        exit;
    }

    $sInclude = realpath(dirname(__FILE__).'/../includes');

    $aIncludePath = array(
        dirname(__FILE__),
        $sInclude.'/cowiki',
        $sInclude.'/cowiki/class',
        ini_get('include_path'),
    );
    ini_set('include_path', implode(PATH_SEPARATOR, $aIncludePath));

    // Basic requirements and initialzations
    include_once 'core.base.php';

    // Start the main controller
    try {

        // {{{ DEBUG }}}
        Logger::info('Started processing.');

        main();

        // {{{ DEBUG }}}
        Logger::info('Stopped processing - a clean run.');

    } catch (GenericException $e) {
        $e->printStackTrace();

        // {{{ DEBUG }}}
        Logger::info(
            'Crashed with '.$e->getName().' "'.$e->getExceptionMessage().'"
            in line '.$e->getLine()
        );

        exit;

    } catch (Exception $e) {

        // This should never should happen - do not throw only Exception,
        // but a GenericException instead.
        echo  'Build-in exception in '.$e->getFile().' in line '.$e->getLine();
        echo  '<br />';
        echo  '<pre>';
        echo    $e->getTraceAsString();
        echo  '</pre>';
        echo  'This should never happen. Report it to the coWiki developers.';

        // {{{ DEBUG }}}
        Logger::info('Crashed with build-in exception in line '.$e->getLine());
        Logger::info('THIS IS SERIOUS - Report it to the coWiki developers.');

        exit;
    }

    // --------------------------------------------------------------------

    /**
     * Main
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @throws  GenericException
     */
    function main() {

        /**
         * Bind requested node
         *
         * @access  public
         * @return  void
         *
         * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
         * @since   coWiki 0.3.0
         */
        function bindRequestedNode() {
            $Request = RuntimeContext::getInstance()->getRequest();
            $Node = RuntimeContext::getInstance()
                      ->getDocumentDAO()->getNodeById($Request->get('node'));

            if (is_object($Node)) {
                // Register the node object to the RuntimeContext
                RuntimeContext::getInstance()->setCurrentNode($Node);
            }
        }

        // ----------------------------------------------------------------

        /**
         * Bind default node
         *
         * @access  public
         * @return  void
         *
         * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
         * @since   coWiki 0.3.0
         */
        function bindDefaultNode() {
            $Context = RuntimeContext::getInstance();
            $User = $Context->getCurrentUser();
            $Node = $Context->getDocumentDAO()->getDefaultNode($User);

            if (is_object($Node)) {
                // Register the node object to the RuntimeContext
                RuntimeContext::getInstance()->setCurrentNode($Node);
            }
        }

        // ----------------------------------------------------------------

        /**
         * Bind node by wiki name
         *
         * @access  public
         * @return  mixed
         *
         * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
         * @since   coWiki 0.3.0
         */
        function bindNodeByWikiName() {

            $Context = RuntimeContext::getInstance();
            $Request = $Context->getRequest();
            $DocDAO  = $Context->getDocumentDAO();

            $sWebName = $Request->get('webname');
            $sDocName = $Request->get('docname');

            if (!$sWebName && !$sDocName) {
                return;
            }

            $Criteria = new SearchCriteria;

            $Criteria->addExpression(new EqExpression('parent_id', '0'));
            $Criteria->addExpression(new EqExpression('is_dir', 'Y'));
            $Criteria->addExpression(new EqExpression('wikiname', $sWebName));

            // Get one or more (web) results
            $Result = $DocDAO->getUnguardedNodesByCriteria(
                          $Criteria,
                          $Context->getCurrentUser()
                      );

            // If document name is given, get all documents within the web
            // with the same wiki name
            if ($sDocName && !$Result->isEmpty()) {

                $nTreeId = $Result->elementAt(0)->get('treeId');

                $Criteria = new SearchCriteria;
                $Criteria->addOrder('is_dir');

                $Criteria->addExpression(
                              new EqExpression('tree_id', $nTreeId)
                           );
                $Criteria->addExpression(
                              new EqExpression('wikiname', $sDocName)
                           );

                // Get one or more (document) results
                $Result = $DocDAO->getUnguardedNodesByCriteria(
                              $Criteria,
                              $Context->getCurrentUser(),
                              array('*')
                          );
            }

            // ----------------------------------------------------------------

            if ($Result->isEmpty()) {
                return;
            }

            // Non-ambiguous result, fine.
            if ($Result->size() == 1) {
                // Get first node element
                $Node = $Result->elementAt(0);

                // Register the node object to the RuntimeContext
                $Context->setCurrentNode($Node);
                return;
            }

            // Fake request command if we have ambiguous results.
            $Request->set('cmd', CMD_AMBIDOC);
        }

        // ----------------------------------------------------------------

        /**
         * Prepare node
         *
         * @access  public
         * @return  void
         *
         * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
         * @since   coWiki 0.3.0
         */
        function prepareNode() {

            // Get context, registry, request and document access object
            $Context  = RuntimeContext::getInstance();
            $Registry = $Context->getRegistry();
            $Request  = $Context->getRequest();
            $DocDAO   = $Context->getDocumentDAO();

            // If one of these commands has to be performed, do not get
            // a possible "index"-document if the node is a container.
            $aNoIndexFor = array(
                CMD_NEWDIR,    CMD_EDITDIR,  CMD_SHOWDIR,  CMD_NEWDOC,
                CMD_EDITDOC,   CMD_MOVEDOC,  CMD_XMLDOC,   CMD_AMBIDOC,
                CMD_SIMDOC,    CMD_SHOWHIST, CMD_COMPHIST, CMD_DIFFHIST,
                CMD_RECOVHIST, CMD_SRCHDOC
            );

            $Node = $Context->getCurrentNode();

            if (is_object($Node)) {

                // Check if this node is a directory and if none of the
                // commands set in "$aNoIndexFor" has to be performed.
                // If so, overwrite the last node with the content of the
                // new (possible) index document. From now on, we are not
                // working on the requested node(-id), but on its child,
                // its "index" - if there is one of course.

                if ($Node->get('isContainer')) {
                    if (!in_array($Request->get('cmd'), $aNoIndexFor)) {

                        // Get "index" node
                        $IndexNode = $DocDAO->getIndexNodeOf($Node);

                        if (is_object($IndexNode)) {
                            $Node = $IndexNode;
                        }
                    }
                }

                // Get nodes along path to root node
                $Node = $DocDAO->getNodePath($Node);

                // --------------------------------------------------------

                // Default value parent title
                $Registry->set('PARENT_TITLE', '');

                // Get parent if any
                $Parent = $Node->get('parent');

                if (is_object($Parent)) {
                    // Transfer parent title property to Registry
                    $Registry->set(
                        'PARENT_TITLE',
                        escape($Parent->get('name'))
                    );

                    // Special treatment for 'showdir' command
                    if ($Request->get('cmd') == CMD_SHOWDIR) {
                        $Node = $Parent;
                    }
                }

                // --------------------------------------------------------

                // Get current web name
                $Web = $DocDAO->getWebById($Node->get('treeId'));
                if (is_object($Web)) {
                    $Registry->set('WEB_TITLE', escape($Web->get('name')));
                }

                // Set node info in registry
                $Registry->set('TITLE', escape($Node->get('name')));

                $Registry->set(
                    'META_KEYWORDS',
                    escape($Node->get('keywords'))
                );

                $Registry->set(
                    'CREATED',
                    $Context->makeDate($Node->get('created'))
                    .', '
                    .$Context->makeTime($Node->get('created'))
                );

                $Registry->set(
                    'MODIFIED',
                    $Context->makeDate($Node->get('modified'))
                    .', '
                    .$Context->makeTime($Node->get('modified'))
                );

                $Registry->set('REVISION', $Node->get('revision'));
                $Registry->set('VIEWS', $Node->get('views'));

                // --------------------------------------------------------

                $Context->setCurrentNode($Node);
            }
        }

        // ----------------------------------------------------------------

        /**
         * Process node command
         *
         * @access  public
         * @return  void
         *
         * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
         * @since   coWiki 0.3.0
         */
        function processNodeCommand() {

            // Get context, request, registry and template processor
            $Context  = RuntimeContext::getInstance();
            $Request  = $Context->getRequest();
            $Registry = $Context->getRegistry();
            $Tpl      = $Context->getTemplateProcessor();

            // Comment data access
            $ComDAO = $Context->getCommentDAO();

            // Current node
            $Node = $Context->getCurrentNode();

            // Flags
            $bDone = false;
            $bIndexFollow = true;

            // ------------------------------------------------------------

            // Output printable version?
            if (!$bDone && $Request->get('cmd') == CMD_PRNTDOC) {
                echo $Tpl->parse('print.page.head.tpl');
                echo $Tpl->parse('print.page.header.tpl');
                echo $Tpl->parse('print.display.tpl');
                echo $Tpl->parse('print.page.footer.tpl');

                $Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');
                $bDone = true;
            }

            // ------------------------------------------------------------

            if (!$bDone) {
                // Normal page header
                echo $Tpl->parse('front.page.head.tpl');
                echo $Tpl->parse('front.page.header.tpl');
            }

            // ------------------------------------------------------------

            // Display or edit comment?
            if (!$bDone && $Request->has('comid')) {

                // Add new "comments" node for breadcrumb
                $NodeCom = $Node->clonePrimitives();
                $NodeCom->set('name', __('I18N_COM_COMMENTS'));
                $NodeCom->set('isComment', true);
                $Node->addItem($NodeCom);

                // Add new "subject" node for breadcrumb
                $Com = $ComDAO->getCommentById($Request->get('comid'));
                if (is_object($Com)) {
                    $NodeSubject = $Node->clonePrimitives();
                    $NodeSubject->set('name', $Com->get('subject'));
                    $NodeCom->addItem($NodeSubject);
                }

                // Dispatch commands
                switch ($Request->get('cmd')) {

                    case CMD_NEWCOM:
                    case CMD_REPLYCOM:
                        echo $Tpl->parse('front.comment.edit.tpl');
                        break;

                    default:
                        echo $Tpl->parse('front.comment.display.tpl');
                        break;
                }

                // Include page footer
                echo $Tpl->parse('front.page.footer.tpl');
                $bDone = true;
            }

            // ------------------------------------------------------------

            if (!$bDone) {
                $bIndexFollow = false;

                // Dispatch commands
                switch ($Request->get('cmd')) {
                    case CMD_EDITDOC:
                    case CMD_NEWDOC:
                        echo $Tpl->parse('front.doc.edit.tpl');
                        break;

                    case CMD_EDITDIR:
                    case CMD_NEWDIR:
                        echo $Tpl->parse('front.dir.edit.tpl');
                        break;

                    case CMD_CHUSR:
                        echo $Tpl->parse('front.user.change.tpl');
                        break;

                    case CMD_PREFUSR:
                        echo $Tpl->parse('front.user.preferences.tpl');
                        break;

                    case CMD_DETAILUSR:
                        echo $Tpl->parse('front.user.details.tpl');
                        break;

                    case CMD_XMLDOC:
                        echo $Tpl->parse('front.doc.xmlpretty.tpl');
                        break;

                    case CMD_AMBIDOC:
                        echo $Tpl->parse('front.wikiword.ambiguous.list.tpl');
                        break;

                    case CMD_SIMDOC:
                        echo $Tpl->parse('front.wikiword.similar.list.tpl');
                        break;

                    case CMD_SRCHDOC:
                        echo $Tpl->parse('front.search.list.tpl');
                        break;

                    case CMD_SHOWHIST:
                        echo $Tpl->parse('front.doc.history.display.tpl');
                        break;

                    case CMD_COMPHIST:
                        echo $Tpl->parse('front.doc.history.compare.tpl');
                        break;

                    case CMD_DIFFHIST:
                        echo $Tpl->parse('front.doc.history.diff.tpl');
                        break;

                    case CMD_RECOVHIST:
                        echo $Tpl->parse('front.doc.history.recover.tpl');
                        break;

                    case CMD_LISTCOM:
                        // Add new "comments" node for breakcrumb
                        $NodeCom = $Node->clonePrimitives();
                        $NodeCom->set('name', __('I18N_COM_COMMENTS'));
                        $Node->addItem($NodeCom);

                        echo $Tpl->parse('front.comment.list.tpl');
                        break;

                    default:
                        if ($Node->get('isContainer')) {
                            echo $Tpl->parse('front.dir.display.tpl');
                        } else {
                            echo $Tpl->parse('front.doc.display.tpl');
                            $bIndexFollow = true;
                        }
                        break;
                }

                if ($bIndexFollow) {
                    $Registry->set('META_ROBOT_INDEX', 'index, follow');
                } else {
                    $Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');
                }

                // Include page footer
                echo $Tpl->parse('front.page.footer.tpl');
                $bDone = true;
            }
        }

        // === CONTROLLER FLOW ============================================

        /**
         *
         */
        try {
            // Get context, registry and request
            $Context  = RuntimeContext::getInstance();
            $Registry = $Context->getRegistry();
            $Request  = $Context->getRequest();

            // Set basic controller constants
            $Registry->set('COWIKI_CONTROLLER_NAME', basename(__FILE__));
            $Registry->set('COWIKI_CONTROLLER_REWRITABLE', true);
            $Registry->set('COWIKI_BASE_HREF',
                $Request->getHostUri() . $Request->getBasePath()
            );

            // Bind dummy node for error cases
            $Context->setCurrentNode(new DocumentContainer());
        }
        catch(Exception $e) {
            echo $e->getMessage();
            exit;
        }

        // ----------------------------------------------------------------

        $bDispatched = false;

        // Dispatch controller actions

        if (!$bDispatched) {
            if ($Request->has('webname') || $Request->has('docname')) {
                bindNodeByWikiName();
                prepareNode();
                processNodeCommand();
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        if (!$bDispatched) {
            // ... more actions here

            //$bDispatched = true;
        }

        // ----------------------------------------------------------------

        // Default behaviour
        if (!$bDispatched) {
            // "node" parameter is set? Get the node, otherwise get default
            if ($Request->has('node')) {
                bindRequestedNode();
            } else {
                bindDefaultNode();
            }

            prepareNode();
            processNodeCommand();
            $bDispatched = true;
        }

        // ----------------------------------------------------------------

        include_once 'core.finish.php';

    } // of main() controller

/*
    Time passes slow - fields stretch out across open acres
    Time passes slow - the giant steps of mankind touch us so little
                       in the great lands
*/

?>
