<?php

/**
 *
 * $Id: class.AbstractPlugin.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     plugin
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
 * coWiki - Abstract plugin class
 *
 * @package     plugin
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
abstract class AbstractPlugin extends Object {

    protected
        $Context  = null,
        $Request  = null,
        $Response = null,
        $Env      = null,
        $Registry = null,
        $Template = null;

    /**
     * Before the perform() method is executed, every plugin will be
     * initialized.
     *
     * @access  protected
     * @param   integer
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function init($nVersion) {

        // "$this->Context" is the instance of the RuntimeContext. This
        // is the one and only way for you to get variables from outside
        // this plugin class! You MUST NOT use the "globals" statement nor
        // the $GLOBALS hash, be warned.
        $this->Context = RuntimeContext::getInstance();

        // Have request, response and environment objects ready
        $this->Request  = $this->Context->getRequest();
        $this->Response = $this->Context->getResponse();
        $this->Env      = $this->Context->getEnvironment();

        // Have registry amd template variable container objects ready
        $this->Registry = $this->Context->getRegistry();
        $this->Template = $this->Context->getTemplateRegistry();

        // Always check for the interface version - keep this here for
        // future compatibility!
        return $this->Context->initPlugin($nVersion);
    }

    /**
     * The main mathod of a plugin, business logic and screen output will
     * happen here
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    abstract function perform();

    /**
     * XML export callback. (XML) export filters have to call this method to
     * get a XML representation of the plugin output. This method is not
     * abstract as most plugins do not use it by now, but it has to be
     * implemented (overwritten) by a plugin subclass if it wants to export
     * its output.
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    function exportXml() {
        return '';
    }

} // of class

?>
