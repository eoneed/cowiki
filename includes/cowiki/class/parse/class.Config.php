<?php

/**
 *
 * $Id: class.Config.php 19 2011-01-04 03:52:35Z eoneed $
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
 * Config file reader
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.4.0
 */
class Config extends Object {

    /**
     * Class constructor
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     */
    public function __construct() {
    }

    // --------------------------------------------------------------------

    /**
     * Parse an .ini style configuration file.
     *
     * @access  public
     * @param   string  The name (plus path) of the config file to read.
     * @return  array   Associative array with key-value pairs.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     *
     * @throws  ConfigNotReadableException
     * @throws  ConfigSyntaxException
     */
    public function &getIniConfigAsArray($sFile) {

        try {
            $In = new FileInputStream($sFile);
            $sStr = $In->readAll();
            $In->close();

        } catch (GenericException $e) {
            throw new ConfigNotReadableException('Failed to read '.$sFile);
        }

        // ----------------------------------------------------------------

        $aConf = array();
        $aTrue  = array('yes', 'true',  'on');
        $aFalse = array('no',  'false', 'off');

        // ----------------------------------------------------------------

        // Remove all possible \r
        $sStr = str_replace("\r", '', $sStr);

        // Remove comments
        $sStr = preg_replace('#^\s*;.*\n#m', '', $sStr);

        // Remove empty lines
        $sStr = str_replace("\n\n", "\n", $sStr);

        // ----------------------------------------------------------------

        // Try to match .ini style config with [sections] ...
        preg_match_all(
            '#\[(.+)\]s*\n([^\[]+)#',
            $sStr,
            $aMatches
        );

        // ConfigSyntaxException?
        if (!isset($aMatches[2])) {
            throw new ConfigSyntaxException();
        }

        // ----------------------------------------------------------------

        // Iterate through section matches and extract values
        for ($i=0, $n=sizeof($aMatches[1]); $i<$n; $i++) {

            // Try to match .ini style section content
            preg_match_all(
                '#\n\s*([a-zA-Z0-9_]+)\s*=\s*("?)([^"]*)\2#',
                "\n".$aMatches[2][$i]."\n",
                $aEntries
            );

            // ConfigSyntaxException?
            if (!isset($aEntries[3])) {
                throw new ConfigSyntaxException();
            }

            // Extract values
            for ($j=0, $m=sizeof($aEntries[1]); $j<$m; $j++) {

                $k = $aMatches[1][$i].'_'.$aEntries[1][$j];

                // Check for boolean value
                if (in_array(strtolower($aEntries[3][$j]), $aTrue)) {
                    $aConf[$k] = true;
                } else if (in_array(strtolower($aEntries[3][$j]), $aFalse)) {
                    $aConf[$k] = false;
                } else {
                    $aConf[$k] = $aEntries[3][$j];
                }
            }
        }

        return $aConf;
    }

} // of class

/*

    Heute bin ich recht gut ausgeschlafen aufgestanden
    Meine Zaehne geputzt und auf die Strasse gestolpert
    Und am Tageslicht erkannt, dass diese Gegend nicht dafuer vorgesehen ist
    Dass freundliche Menschen sich in ihr gegenseitig gut verstehen

    1 Uhr 15, 1 Uhr 30, 1 Uhr 45
    Manche sagen dreiviertel 2 dazu
    2 Uhr, 2 Uhr 15, sich gegenseitig gut verstehen

    Gestern war es aehnlich, lies es ruhig nach
    Nachts ist es meist dunkel und hell wird es dann am Tag
    Nicht dass ich es wollte, nur ich wuesste wirklich nicht warum ich mich
    Dafuer ... interessieren sollte

*/

?>
