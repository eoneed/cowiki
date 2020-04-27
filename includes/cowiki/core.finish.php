<?php

/**
 *
 * $Id: core.finish.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package    core
 * @access     public
 *
 * @author     Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright  (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license    http://www.gnu.org/licenses/gpl.html
 * @version    $Revision: 19 $
 *
 */

    $sContent = ob_get_contents();  ob_end_clean();

    // Set default <meta .. robots>. If it is already set must not be
    // changed.
    $Registry = $Context->getRegistry();
    if (!$Registry->has('META_ROBOT_INDEX')) {
        $Registry->set('META_ROBOT_INDEX', 'noindex, nofollow');
    }

    if (!$Registry->get('PAGE_TITLE')) {
        $Node = RuntimeContext::getInstance()->getCurrentNode();

        $sStr = '';
        if (is_object($Node)) {

            // Do not reveal page name if document is not readable
            if ($Node->isReadable()) {
                $sStr = escape($Node->get('name'));
            }
        }

        $Registry->set(
            'PAGE_TITLE',
            $Registry->get('DOCUMENT_TITLE_PREFIX')
            .$sStr
            .$Registry->get('DOCUMENT_TITLE_SUFFIX')
        );
    }

    // Replace all remained variables
    $sContent = preg_replace_callback(
        '#\{%(.+)%\}#USs',
        'processRemainedTemplateVariables',
        $sContent
    );

    /**
     * Process remained template variables
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
    function processRemainedTemplateVariables(&$aMatches) {

        // Remove <span>s produced by FrontDocumentDisplay that highlight
        // queries referred by a search engine
        $aMatches[1] = preg_replace(
                          '#<span[^>]*>(.*)</span>#Us',
                          '\1',
                          $aMatches[1]
                       );

        $Registry = RuntimeContext::getInstance()->getRegistry();

        // Check for hidden section variables (with leading dot)
        if ($aMatches[1]{0} != '.') {
            if ($Registry->has($aMatches[1])) {
                return $Registry->get($aMatches[1]);
            }
        }

        return '{%'.$aMatches[1].'%}';
    }

    ob_start($Registry->get('RUNTIME_OUTPUT_HANDLER'));

    echo $sContent;

    // Sometimes the apache gets confused by things I did not really figured
    // out. People say it may happen due to memory consumption and if the
    // httpd is not able to reactivate its child process after PHP has
    // finished its work. Fact is that apache leaves its children in a
    // sleeping state with no resident pages (no allocated memory) without
    // spawning new children ("SW" state on Linux). In that case we will
    // have to tell the httpd to terminate this useless child and keep up
    // its work.

    // "apache_child_terminate()" works only if it is allowed in php.ini
    // with "apache.child_terminate = on" - this might be a KO-criteria
    // for using this software at mass hosters, until PHP is able to handle
    // this situation itself.

    // This function all has to be the last line in the script.
    if (ini_get('apache.child_terminate')) {
        @apache_child_terminate();
    }

?>
