<?php

/**
 *
 * $Id: class.Xml.php 19 2011-01-04 03:52:35Z eoneed $
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
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @copyright   (C) Daniel T. Gorski, {@link http://www.develnet.org}
 * @license     http://www.gnu.org/licenses/gpl.html
 * @version     $Revision: 19 $
 *
 */

/**
 * XML constant definitions
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.1
 */
class Xml extends Object {

    const
        ELM_NAME   = 'ELM',
        ELM_ATTR   = 'ATTR',
        ELM_PCDATA = 'PCDATA',
        ELM_CDATA  = 'CDATA',
        ELM_LEVEL  = 'LEVEL',
        ELM_INDEX  = 'INDEX';

} // of class

?>
