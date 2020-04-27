<?php

/**
 *
 * $Id: class.RssItem.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * The RSSItem represents a single RRS/RDF item.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Kai Schröder, <k.schroeder@php.net>
 * @since       coWiki 0.3.3
 */
class RssItem extends Xml {

    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function __construct() {}

    // --------------------------------------------------------------------

    /**
     * Get the publishing date as unix time stamp
     *
     * @access  public
     * @return  integer
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getDate() {
        $iDate = 0;

        if ($this->has('pubDate')) {
            $iDate = strtotime($this->get('pubDate'));
        } elseif ($this->has('dc:date')) {
            $iDate = strtotime($this->get('dc:date'));
        }

        return $iDate;
    }

    // --------------------------------------------------------------------

    /**
     * Get the publishing date as relative string
     *
     * @access  public
     * @return  string
     *
     * @author  Kai Schröder, <k.schroeder@php.net>
     * @since   coWiki 0.3.3
     */
    public function getRelativeDate() {
        $sDate = '';

        $iTime = $this->getDate();

        if ($iTime > 0) {
            $Context = RuntimeContext::getInstance();
            $sDate = $Context->makeDateTimeRelative($iTime);
        }

        return $sDate;
    }

} // of class

?>
