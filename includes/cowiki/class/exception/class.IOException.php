<?php

/**
 *
 * $Id: class.IOException.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     exception
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

 /**
 * "IOException" exception. Parent of all Input/Output exceptions.
 *
 * @package     exception
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.4.0
 */
class IOException extends GenericException {

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  Message for the exception to clarify the cause
     *                  (optional).
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function __construct($sMsg = null) {
        parent::__construct($sMsg);
        $this->setName(__CLASS__);
    }

} // of class

?>