<?php

/**
 *
 * $Id: class.PrivateFrontCommentList.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontCommentList
 * #purpose:   Display the comment list
 * #param:     limit   number of entries to display per page
 * #caching:   not used
 * #comment:   none
 * #version:   1.0
 * #date:      05. May 2003
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
 * coWiki - Display the comment list
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
class PrivateFrontCommentList extends AbstractPlugin {

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

        // Set plugin parameters, if passed by a plugin call, or set
        // defaults
        $nLimit = $this->Context->getPluginParam('limit')
                    ? $this->Context->getPluginParam('limit')
                    : 20;

        // ----------------------------------------------------------------

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        // Comment data access
        $ComDAO = $this->Context->getCommentDAO();

        // Get number of threads and comments
        $sStr = $ComDAO->getThreadCount($Node);
        $this->Template->set('TPL_THREAD_COUNT', $sStr);

        $sStr = $ComDAO->getCommentCount($Node);
        $this->Template->set('TPL_COMMENT_COUNT', $sStr);

        // ---

        $sQueryStr = 'node='.$Node->get('id').'&';

        $this->Template->set(
            'TPL_ITEM_WRITE_HREF',
            $this->Response->getControllerHref(
                $sQueryStr . 'comid=0&cmd='.CMD_NEWCOM
            )
        );

        // ----------------------------------------------------------------

        $aTplItem = array();
        $It = $ComDAO->getCommentList($Node, 0, $nLimit)->iterator();

        while ($Obj = $It->next()) {
            $aItem = array();
            $aItem['SUBJECT'] = escape(cutOff($Obj->get('subject'),50));
            $aItem['REPLIES'] = $Obj->get('replies');

            $aItem['HREF']    = $this->Response->getCommentHref(
                                    $Obj->get('id'),
                                    'node='.$Node->get('id')
                                );
            $aItem['NAME']    = escape($Obj->get('authorName'));
            $aItem['TIME']    = $this->Context->makeDateTimeRelative(
                                    $Obj->get('created')
                                );

            $aTplItem[] = $aItem;
        }

        $this->Template->set('TPL_ITEM', $aTplItem);

        $Lister = new Lister();
        $It = $ComDAO->getCommentList($Node, 0, $nLimit)->iterator();

        while ($Obj = $It->next()) {

            // Prepare a row for the Lister
            $Row = new ListerRow();
            $Row->set('data', $Obj);

            // Prepare column data
            $Col = new ListerColumn();

                $Content = new Object();
                $sHref = $this->Response->getCommentHref(
                                    $Obj->get('id'),
                                    'node='.$Node->get('id')
                                );
                $Content->set('type',  Lister::TYPE_RAW);
                $Content->set('prop',  'href');
                $Content->set('value', $sHref);
                $Col->addContent($Content);

                $Content = new Object();
                $Content->set('type', Lister::TYPE_STRING);
                $Content->set('prop', 'subject');
                $Col->addContent($Content);

                $Content = new Object();
                $Content->set('type', Lister::TYPE_STRING);
                $Content->set('prop', 'replies');
                $Col->addContent($Content);

            $Row->addColumn($Col);

            // ---

            // Prepare column data
            $Col = new ListerColumn();

                $Content = new Object();
                $Content->set('type', Lister::TYPE_STRING);
                $Content->set('prop', 'authorName');
                $Content->set('name', 'name');
                $Col->addContent($Content);

            $Row->addColumn($Col);

            // ---

            // Prepare column data
            $Col = new ListerColumn();

                $Content = new Object();
                $Content->set('type', Lister::TYPE_UNIXTIME);
                $Content->set('prop', 'created');
                $Col->addContent($Content);

            $Row->addColumn($Col);
/*
            // Prepare column data
            $Col = new ListerColumn();
            $Col->set('type', Lister::TYPE_STRING);
            $Col->set('prop', 'authorName');
            $Col->set('name', 'name');
            $Row->addColumn($Col);

            // ---

            // Prepare column data
            $Col = new ListerColumn();
            $Col->set('type', Lister::TYPE_UNIXTIME);
            $Col->set('prop', 'created');
            $Row->addColumn($Col);
*/
            $Lister->addRow($Row);

        }

        $this->Template->set('TPL_ITEM', $Lister->generate('TPL_ITEM'));

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.comment.list.tpl');
    }

} // of plugin component

/*
    When I became the sun, I shone life into the man's hearts.
*/

?>
