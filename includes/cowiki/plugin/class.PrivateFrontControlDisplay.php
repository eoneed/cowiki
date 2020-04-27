<?php

/**
 *
 * $Id: class.PrivateFrontControlDisplay.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * <pre>
 * #name:      PrivateFrontControlDisplay
 * #purpose:   Provide control buttons ("edit" etc.) for a document
 *             or directory node
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
 * coWiki - Private front control display class
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
class PrivateFrontControlDisplay extends AbstractPlugin {

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

        // Get current directory/document object
        $Node = $this->Context->getCurrentNode();

        if (!is_object($Node)) {
            return true;  // leave plugin
        }

        // Get current user object
        $CurrUser = $this->Context->getCurrentUser();

        // ----------------------------------------------------------------

        // Submission sentinel(s)
        $bFlag = false;
        if ($this->Request->get('cmd')) {

            // "Change user" mask displayed?
            if ($this->Request->get('cmd') == CMD_CHUSR) {
                return true;    // leave
            }

            switch ($this->Request->get('cmd')) {
                case CMD_NEWDIR:
                case CMD_EDITDIR:
                case CMD_EDITDOC:
                case CMD_NEWDOC:
                case CMD_SIMDOC:
                case CMD_MOVEDOC:
                case CMD_PRNTDOC:
                case CMD_XMLDOC:
                case CMD_SRCHDOC:
                case CMD_PREFUSR:
                case CMD_NEWCOM:
                case CMD_LISTCOM:
                    $bFlag = true;
                    break;
            }
        }

        // ----------------------------------------------------------------

        $aItem = array();
        $aTplItem = array();

        $sQuery = 'node='.$Node->get('id').'&';

        // If we are in a directory
        if ($Node->get('id') != 0 && $Node->get('isContainer') &&
            $Node->isExecutable() && $Node->isWritable()) {

            if (!$bFlag) {
                $aItem['NAME']   = __('I18N_DOC_NEW');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_NEWDOC
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;

                // ---

                $aItem['NAME']   = __('I18N_DIR_NEW');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_NEWDIR
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;

                // --

                // Display "edit" button, only if node is editable for
                // user/group or members
                if ($Node->isEditable()) {
                    $aItem['NAME']   = __('I18N_EDIT');
                    $aItem['TARGET'] = '_self';
                    $aItem['HREF']   = $this->Response->getControllerHref(
                                          $sQuery . 'cmd='.CMD_EDITDIR
                                       );

                    // Append item to template items
                    $aTplItem[] = $aItem;
                }
            }
        }

        // ----------------------------------------------------------------

        // If we are in a document.
        if (!$Node->get('isContainer') && $Node->isParentExecutable()) {

            if (!$bFlag) {
                $aItem['NAME']   = __('I18N_DIR_LIST');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_SHOWDIR
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;
    /*
                $aItem['NAME']   = __('I18N_XML_SHOW');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_XMLDOC
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;
    */
                // ---

                switch ($this->Request->get('cmd')) {
                    case CMD_SHOWHIST:  break;
                    case CMD_COMPHIST:  break;
                    case CMD_DIFFHIST:  break;
                    case CMD_RECOVHIST: break;

                    default:
                        $aItem['NAME']   = __('I18N_HISTORY');
                        $aItem['TARGET'] = '_self';
                        $aItem['HREF']   = $this->Response->getControllerHref(
                                              $sQuery . 'cmd='.CMD_SHOWHIST
                                           );
                        // Append item to template items
                        $aTplItem[] = $aItem;
                }

                // --
                $aItem['NAME']   = __('I18N_SIMI');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_SIMDOC
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;

                // --

                $aItem['NAME']   = __('I18N_PRINT_VERSION');
                $aItem['TARGET'] = '_blank';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_PRNTDOC
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;
            }

            // We are in a document. Provide a "edit" button, if this
            // document node is writable
            if (!$bFlag && $Node->isWritable()) {
                $aItem['NAME']   = __('I18N_EDIT');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = $this->Response->getControllerHref(
                                      $sQuery . 'cmd='.CMD_EDITDOC
                                   );

                // Append item to template items
                $aTplItem[] = $aItem;
            }
        }

        // ----------------------------------------------------------------

        if ($CurrUser->isRoot()) {
            if (!$bFlag) {
                $aItem['NAME']   = __('I18N_ADMIN');
                $aItem['TARGET'] = '_self';
                $aItem['HREF']   = 'admin.php';

                // Append item to template items
                $aTplItem[] = $aItem;
            }
        }

        // ----------------------------------------------------------------

        $this->Template->set('TPL_ITEM',  $aTplItem);

        // Parse template
        $Tpl = $this->Context->getTemplateProcessor();
        echo $Tpl->parse('plugin.front.control.tpl');
    }

} // of plugin component

?>
