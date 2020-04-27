<?php

/**
 *
 * $Id: class.FileInputStream.php 19 2011-01-04 03:52:35Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     io
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
 * coWiki - File input stream class
 *
 * @package     io
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class FileInputStream extends AbstractInputStream {

    protected
        $rFile     = null,
        $bOpener   = false,
        $sFileName = '';

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  The name of file to open.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @throws  FileNotFoundException
     * @throws  FileNotReadableException
     */
    public function __construct($sFileName) {

        // {{{ DEBUG }}}
        Logger::io('Trying to open for reading "'.$sFileName.'"');

        if (!file_exists($sFileName)) {
            throw new FileNotFoundException($sFileName);
        }

        if (!is_readable($sFileName)) {
            throw new FileNotReadableException($sFileName);
        }

        if (!($this->rFile = @fopen($sFileName, 'rb'))) {
            throw new FileNotReadableException($sFileName);
        }

        // This object opened the resource
        $this->bOpener   = true;
        $this->sFileName = $sFileName;
    }

    // --------------------------------------------------------------------

    /**
     * Returns the amount of bytes that are available for reading.
     *
     * @access  public
     * @return  integer Amount of available bytes.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function available() {
        if ($this->sFileName) {
            return @filesize($this->sFileName);
        }

        return 0;
    }

    // --------------------------------------------------------------------

    /**
     * Read n bytes from input stream.
     *
     * @access  public
     * @param   integer Number of bytes to read.
     * @return  string  The requested byte stream.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     readAll
     */
    public function &read($nLen) {
        if (is_resource($this->rFile)) {
            return @fread($this->rFile, $nLen);
        }
        return '';
    }

    // --------------------------------------------------------------------

    /**
     * Read all available bytes from input stream.
     *
     * @access  public
     * @return  string  The requested byte stream.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     available
     * @see     read
     */
    public function &readAll() {
        $sBuf = '';

        if (is_resource($this->rFile)) {
            $sBuf = @fread($this->rFile, $this->available());
        }

        return $sBuf;
    }

    // --------------------------------------------------------------------

    /**
     * Skip n bytes in input stream.
     *
     * @access  public
     * @param   integer Number of bytes to skip.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function skip($nLen) {
        if (is_resource($this->rFile)) {
            @fseek($this->rFile, $nLen, SEEK_CUR);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Close stream.
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function close() {
        if ($this->bOpener && is_resource($this->rFile)) {
            @fclose($this->rFile);
        }
        $this->rFile   = null;
        $this->bOpener = false;
    }

} // of class

?>
