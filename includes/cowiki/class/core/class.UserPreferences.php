<?php

/**
 *
 * $Id: class.UserPreferences.php 28 2011-01-09 14:00:39Z eoneed $
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
 * @version     $Revision: 28 $
 *
 */

/**
 * coWiki - User preferences class
 *
 * @package     core
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class UserPreferences extends Object {

    protected static
        $Instance = null;

    // --------------------------------------------------------------------

    /**
     * Get the unique instance of the class (This class is implemented as
     * Singleton).
     *
     * @access  public
     * @return  Object  The class instance
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new UserPreferences;
        }
        return self::$Instance;
    }

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $this->__wakeup();
    }

    // --------------------------------------------------------------------

    /**
     * The __wakeup callback handler is called automatically when the HTTP
     * session is (re)initialized.
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __wakeup() {
        static $bWokenUp = false;

        if ($bWokenUp) {
            return;
        }
        $bWokenUp = true;

        $Context = RuntimeContext::getInstance();
        $Registry = $Context->getRegistry();

        // Get preference settings carried by a cookie
        $aPref = null;
        $sPref = $Context->getCookieVar('preferences');

        // Go on only if we have a cookie
        if ($sPref) {
            $aPref = @unserialize($sPref);
        }

        if (!is_array($aPref)) {
            return;
        }

        $sPath = $Context->getEnvironment()->get('PHP_SELF');
        $sTpl = getRequestBasePath($sPath) . 'tpl/';

        // Override template name with user settings
        if (isset($aPref['TEMPLATE'])) {

            // Clean, do not trust incoming parameters
            $aPref['TEMPLATE'] = preg_replace(
                                    '#[^a-zA-Z0-9_ ]#',
                                    '',
                                    $aPref['TEMPLATE']
                                 );
        } else {
            $aPref['TEMPLATE'] = 'default';
        }

        // Concat path to active template
        $sActTpl = $sTpl . $aPref['TEMPLATE'] . '/';
        $this->set('template', $sActTpl);

        // Set active template
        $Registry->set('RUNTIME_TEMPLATE_ACTIVE', $aPref['TEMPLATE']);
        $Registry->set('PATH_TEMPLATE_ACTIVE', $sActTpl);

        // Set path to active images
        $Registry->set('PATH_IMAGES', $sActTpl . 'img/');

        // Get template configuration file and define constants
        $Registry->getTplConf($sActTpl);

        // ------------------------------------------------------------

        // Override language catalog with user settings
        if (isset($aPref['CATALOG'])) {

            // Clean, do not trust incoming parameters
            $aPref['CATALOG'] = preg_replace(
                                    '#[^a-zA-Z0-9-_ .]$#',
                                    '',
                                    $aPref['CATALOG']
                                );

            // Set active catalog (language file)
            $this->set('catalog', $aPref['CATALOG']);

            // English is already loaded in core.base
            if ($aPref['CATALOG'] != 'en.utf-8') {
                setCatalog('locale/', $aPref['CATALOG']);
                $Registry->set('RUNTIME_LANGUAGE_LOCALE', $aPref['CATALOG']);
            }
        }

        // Override font family name with user settings
        if (isset($aPref['FONT_FAMILY'])
            && trim($aPref['FONT_FAMILY']) != '') {

            // Clean, do not trust incoming parameters
            $aPref['FONT_FAMILY'] = preg_replace(
                                      '#[^a-zA-Z0-9_ ,-]#',
                                      '',
                                      $aPref['FONT_FAMILY']
                                    );

            // Set active font
            $this->set('fontFamily', $aPref['FONT_FAMILY']);
            $Registry->set('FONT_FAMILY', $aPref['FONT_FAMILY']);
        }

        // Override font size with user settings
        if (isset($aPref['FONT_SIZE'])
             && trim($aPref['FONT_SIZE']) != '') {

            // Clean, do not trust incoming parameters
            $aPref['FONT_SIZE'] = preg_replace(
                                    '#[^0-9]#',
                                    '',
                                    $aPref['FONT_SIZE']
                                  );

            // Check for too small (unreadable font) values
            if ((int)$aPref['FONT_SIZE'] >= 8) {

                // Set active font size
                $this->set('fontSize', $aPref['FONT_SIZE'] . 'px');
                $Registry->set('FONT_SIZE', $aPref['FONT_SIZE'] . 'px');
            }
        }

        // Override font align with user settings
        if (isset($aPref['FONT_ALIGN'])
            && trim($aPref['FONT_ALIGN']) != '') {

            // Clean, do not trust incoming parameters
            $aPref['FONT_ALIGN'] = preg_replace(
                                      '#[^a-zA-Z]#',
                                      '',
                                      $aPref['FONT_ALIGN']
                                   );

            // Set active font align
            $this->set('fontAlign', $aPref['FONT_ALIGN']);
            $Registry->set('FONT_ALIGN', $aPref['FONT_ALIGN']);
        }
    }

} // of class

?>
