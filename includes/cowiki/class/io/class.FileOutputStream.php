<?php

/**
 *
 * $Id: class.FileOutputStream.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - File output stream class
 *
 * @package     io
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class FileOutputStream extends AbstractOutputStream {

    protected
        $rFile     = null,
        $bOpener   = false,
        $sLockfile = '';

    /**
     * Class constructor
     *
     * @access  public
     * @param   object
     * @param   boolean
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$mOut"
     */
    public function __construct($mOut, $bAppend = false) {

        if (is_string($mOut)) {

            // {{{ DEBUG }}}
            Logger::io('Trying to open for writing "'.$mOut.'"');

            $sMode = $bAppend ? 'ab' : 'wb';

            if (!$this->open($mOut, $sMode)) {
                throw new Exception();
            }

            // This object opened the resource
            $this->bOpener = true;
        }

        if (is_resource($mOut)) {
           $this->rFile = $mOut;
        }

    }

    // --------------------------------------------------------------------

    /**
     * Class destructor
     *
     * @access  public
     * @return  void
     *
     * @author  Niels Braczek, <nbraczek@bsds.de>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check the parameter type of "$mOut"
     */
    public function __destruct() {
        $this->close();
    }

    // --------------------------------------------------------------------

    /**
     * Open
     *
     * @access  public
     * @param   string Filename
     * @param   string Access mode
     * @return  boolean
     *
     * @author  Niels Braczek, <nbraczek@bsds.de>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    public function open($sFilename, $sMode) {

        $Registry  = RuntimeContext::getInstance()->getRegistry();

        if ($Registry->get('RUNTIME_LOCKING_METHOD') == 'FILE') {
            if (!$this->lock($sFilename, LOCK_EX)) {
                return false;
            }
        }

        if (!($this->rFile = @fopen($sFilename, $sMode))) {
            return false;
        }

        if ($Registry->get('RUNTIME_LOCKING_METHOD') != 'FILE') {
            if (!$this->lock($this->rFile, LOCK_EX)) {
                $this->close();
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Write
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function write($sOut) {
        if (is_resource($this->rFile)) {
            if (!fwrite($this->rFile, $sOut)) {
                throw new Exception();
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Flush
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function flush() {
        if (is_resource($this->rFile)) {
            if(!fflush($this->rFile)) {
                throw new Exception();
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Close
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function close() {
        if ($this->bOpener && is_resource($this->rFile)) {
            $this->lock($this->rFile, LOCK_UN);
            @fclose($this->rFile);
        }
        $this->rFile   = null;
        $this->bOpener = false;
    }

    // --------------------------------------------------------------------

    /**
     * Lock
     * Portable locking. Depending on settings in core.conf
     * STD  - Standard flock().
     * FILE - Uses lockfile.
     * NONE - No locking is performed.
     * @see flock()
     *
     * @access  public
     * @param   mixed Filename if FILE, filehandle if STD,
     * @param   int   Lock mode
     * @return  boolean
     *
     * @author  Niels Braczek, <nbraczek@bsds.de>
     * @since   coWiki 0.3.4
     *
     * @todo    [D11N]  Check description
     */
    private function lock($mFile, $iOperation) {

        $Registry  = RuntimeContext::getInstance()->getRegistry();
        $bSuccess  = false;

        switch ($Registry->get('RUNTIME_LOCKING_METHOD')) {

            case 'NONE':
                // No locking
                $bSuccess = true;
                break;

            case 'FILE':
                // dot-locking
                if ($iOperation == LOCK_UN && !empty($this->sLockfile)) {

                    if (@unlink($this->sLockfile)) {
                        $this->sLockfile = '';
                        $bSuccess = true;
                    }

                } else {

                    $sLockfile = $mFile.'.lock';
                    if (function_exists('link')) {
                        if (@link($mFile, $sLockfile)) {
                            $this->sLockfile = $sLockfile;
                            $bSuccess = true;
                        }
                    } else {
                        if ($rLock = @fopen($sLockfile, 'w')) {
                            @fclose($rLock);
                            $this->sLockfile = $sLockfile;
                            $bSuccess = true;
                        }
                    }

                }
                break;

            case 'STD':
            default: // this case includes null value
                $bSuccess = @flock($mFile, $iOperation);
                break;
        }
        return $bSuccess;
    }

} // of class

?>