<?php

/**
 *
 * $Id: admin.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     htdocs
 * @subpackage  admin
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * Basic imports, checks and inits
 *
 * @package     htdocs
 * @subpackage  admin
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */

    $aIncludePath = array(
        dirname(__FILE__),
        realpath(dirname(__FILE__).'/../includes/cowiki/'),
        ini_get('include_path'),
    );

    ini_set('include_path', implode(PATH_SEPARATOR, $aIncludePath));
    //echo ini_get('include_path');

    /**
     * Include once "core.base.php"
     *
     * @todo  [D11N]  Fix description
     */
    include_once 'core.base.php';

    main(); // Start the main controller

    /**
     * Main
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    function main() {

        // === CONTROLLER FLOW ============================================

        // Get context, registry, request, document DAO and template
        // processor
        $Context  = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();
        $Request  = $Context->getRequest();
        $DocDAO   = $Context->getDocumentDAO();
        $Tpl      = TemplateProcessor::getInstance();

        // Set basic controller constants
        $Registry->set('COWIKI_CONTROLLER_NAME', basename(__FILE__));
        $Registry->set('COWIKI_CONTROLLER_REWRITABLE', false);
        $Registry->set('COWIKI_BASE_HREF',
            $Request->getHostUri() . $Request->getBasePath()
        );

        // Other usefull constants
        $Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');
        $Registry->set('META_KEYWORDS', '');
        $Registry->set(
            'PAGE_TITLE',
            $Registry->get('COWIKI_FULL_NAME').' '.__('I18N_ADMINISTRATION')
        );

        // ----------------------------------------------------------------

        // Get user object, check permissions
        $CurrUser = $Context->getCurrentUser();
        if (!$CurrUser->isRoot()) {
            $Context->addError(403);
            $Context->terminate();
        }

        // ----------------------------------------------------------------

        $sModule     = $Request->get('module');
        $sCmd        = $Request->get('cmd');
        $bDispatched = false;

        // ----------------------------------------------------------------

        // Include HTML-<HEAD> (stylesheets/JavaScipt)
        echo $Tpl->parse('front.page.head.tpl');

        // Include page header (BODY-Tag and further topmost HTML)
        echo $Tpl->parse('admin.page.header.tpl');

        // ----------------------------------------------------------------

        // Dispatch controller actions

        // Webstructure
        if ($sModule == 'struct') {

            // First create an empty dummy node and register it
            $Node = new DocumentContainer();
            $Context->setCurrentNode($Node);

            if ($Request->has('web')) {
                // Build a web node
                $Node = $DocDAO->getNodeById($Request->get('web'));
                $Context->setCurrentNode($Node);

                // Check if we got an object returned, if not we'll display
                // a 404
                if (!is_object($Node)) {
                    $Context->addError(404);
                    $bDispatched = true;
                }
            }

            // ------------------------------------------------------------

            if (!$bDispatched) {
                if ($sCmd == CMD_EDITWEB || $sCmd == CMD_NEWWEB) {
                    echo $Tpl->parse('admin.web.edit.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                echo $Tpl->parse('admin.web.list.edit.tpl');
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        // User management
        if ($sModule == 'user') {

            if (!$bDispatched) {
                if ($sCmd == CMD_EDITUSR || $sCmd == CMD_NEWUSR) {
                    echo $Tpl->parse('admin.user.edit.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                echo $Tpl->parse('admin.user.list.edit.tpl');
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        // Group management
        if ($sModule == 'group') {

            if (!$bDispatched) {
                if ($sCmd == CMD_EDITGRP || $sCmd == CMD_NEWGRP) {
                    echo $Tpl->parse('admin.group.edit.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                echo $Tpl->parse('admin.group.list.edit.tpl');
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        // Cache management
        if ($sModule == 'cache') {

            if (!$bDispatched) {
                echo $Tpl->parse('admin.cache.edit.tpl');
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        // node restoration management
        if ($sModule == 'trash') {

            if (!$bDispatched) {
                if ($sCmd == CMD_SHOWHIST) {
                    echo $Tpl->parse('admin.trash.history.display.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                if ($sCmd == CMD_COMPHIST) {
                    echo $Tpl->parse('admin.trash.history.compare.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                if ($sCmd == CMD_DIFFHIST) {
                    echo $Tpl->parse('admin.trash.history.diff.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                if ($sCmd == CMD_RECOVHIST) {
                    echo $Tpl->parse('admin.trash.history.recover.tpl');
                    $bDispatched = true;
                }
            }

            if (!$bDispatched) {
                echo $Tpl->parse('admin.trash.display.tpl');
                $bDispatched = true;
            }
        }

        // ----------------------------------------------------------------

        if (!$bDispatched || $Context->hasErrors()) {
            $Registry->set(
                'TPL_ITEM_MESSAGE',
                $Context->getErrorQueueFormatted()
            );

            echo $Tpl->parse('admin.menu.display.tpl');
            $bDispatched = true;
        }

        // ----------------------------------------------------------------

        // Include page footer
        echo $Tpl->parse('admin.page.footer.tpl');

        // ----------------------------------------------------------------

        @include_once 'core.finish.php';

    } // of main() controller

/*
    We caught the tread of dancing feet,
    We loitered down the moonlit street,
    And stopped beneath the harlot's house.
    Inside, above the din and fray,
    We heard the loud musicians play
    The "Treues Liebes Herz" of Strauss.

    Like strange mechanical grotesques,
    Making fantastic arabesques,
    The shadows raced across the blind.

    We watched the ghostly dancers spin
    To sound of horn and violin,
    Like black leaves wheeling in the wind.

    Like wire-pulled automatons,
    Slim silhouetted skeletons
    Went sidling through the slow quadrille.

    They took each other by the hand,
    And danced a stately saraband;
    Their laughter echoed thin and shrill.

    Sometimes a clockwork puppet pressed
    A phantom lover to her breast,
    Sometimes they seemed to try to sing.

    Sometimes a horrible marionette
    Came out, and smoked its cigarette
    Upon the steps like a living thing.

    Then turning to my love, I said,
    "The dead are dancing with the dead,
    The dust is whirling with the dust."

    But she - she heard the violin,
    And left my side, and entered in:
    Love passed into the house of lust.

    Then suddenly the tune went false,
    The dancers wearied of the waltz,
    The shadows ceased to wheel and whirl.

    And down the long and silent street,
    The dawn, with silver-sandalled feet,
    Crept like a frightened girl.
*/

?>
