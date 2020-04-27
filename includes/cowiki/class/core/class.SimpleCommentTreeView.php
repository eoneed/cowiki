<?php

/**
 *
 * $Id: class.SimpleCommentTreeView.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     core
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
 * Simple comment tree view class
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class SimpleCommentTreeView extends AbstractTreeView {

    /**
     * Build a single tree item.
     *
     * @access  protected
     * @param   object  Comment (tread) tree
     * @param   integer Id of entry to be highlighted
     * @param   integer Level depth
     * @return  array
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &buildItem($Com, &$nCurrId, &$nLevel) {

        $aItem = array();

        // Vertical thread connectors
        $aItem['CONNECTOR'] = $this->getVertConnectors($Com, $nLevel);

        if ($Com->hasSuccessorItem()) {
            $aItem['BRANCH'] = $this->aTree['CROSS'];
        } else {

            // No branch image for the very frist entry
            if ($nLevel != 0) {
                $aItem['BRANCH'] = $this->aTree['LAST'];
            }
        }

        $nCutOff =  50 - $nLevel * 2.7;
        $aItem['SUBJECT'] = escape(cutOff($Com->get('subject'), $nCutOff));
        $aItem['HREF']    = $this->Response->getCommentHref(
                                $Com->get('id'),
                                'node='.$Com->get('nodeId')
                            );
        $aItem['AUTHOR']  = escape(cutOff($Com->get('authorName'), 20));
        $aItem['TIME']    = $this->Context->makeDateTimeRelative(
                                $Com->get('created')
                            );

        // Highlight current
        if ($Com->get('id') == $nCurrId) {
            $sColor = $this->Registry->get('COLOR_FOUND');

            $sStr = '<span style="color:'.$sColor.'">';
            $sStr .=    $aItem['SUBJECT'];
            $sStr .= '</span>';

            $aItem['SUBJECT'] = $sStr;
        }

        return $aItem;
    }

    // --------------------------------------------------------------------

    /**
     * Get vertical branch connectors.
     *
     * @access  protected
     * @param   object  Comment node (single thread)
     * @param   integer Level depth
     * @return  string  String containing the branch connectors
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function &getVertConnectors($Com, &$nLevel) {
        static $aConn;

        $aConn[$nLevel] = $Com->hasSuccessorItem();

        $sCode = '';
        for ($i=1; $i<$nLevel;  $i++) {
            $sCode .= (isset($aConn[$i]) && $aConn[$i])
                        ? $this->aTree['VERT']
                        : $this->aTree['VOID'];
        }

        return $sCode;
    }

} // of class

/*
    How can you see into my eyes like open doors
    Leading you down into my core where I've become so numb
    Without a soul my spirit's sleeping somewhere cold
    Until you find it there and lead it back home

    Wake me up - Wake me up inside
    I can't wake up - Wake me up inside
    Save me - Call my name and save me from the dark
    Wake me up - Bid my blood to run
    I can't wake up -  Before I come undone
    Save me - Save me from the nothing I've become
*/

?>
