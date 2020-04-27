<?php

/**
 *
 * $Id: class.StorageMySQL.php 28 2011-01-09 14:00:39Z eoneed $
 *
 * This file is part of coWiki. coWiki is free software under the terms of
 * the GNU General Public License (GPL). Read the LICENSE file. If you did
 * not receive a copy of the license and are not able to obtain it through
 * the internet, please send a note to <license@cowiki.org> so we can mail
 * you a copy immediately.
 *
 * @package     dao
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
 * coWiki - Storage MySQL class
 *
 * @package     dao
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 *
 * @todo        [D11N]  Complete documentation
 */
class StorageMySQL extends Object {

    protected
        $rLink = null,
        $rResult = null,
        $aLocked = array(),
        $sLastId = 0;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @access  public
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function __construct() {
        if (!function_exists('mysql_connect')) {
            RuntimeContext::getInstance()->addError(540, 'MySQL');
            RuntimeContext::getInstance()->terminate();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Connect
     *
     * @access  public
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function connect($sHost, $sPort, $sUser, $sPass, $sDatabase) {
        $bSuccess = true;

        if ($sPort != '') {
            $sPort = ':' . $sPort;
        }

        if (!$this->rLink = @mysql_connect($sHost.$sPort, $sUser, $sPass)) {

            // {{{ DEBUG }}}
            Logger::error('MySQL error '.mysql_errno().': '.mysql_error());
            $bSuccess = false;
        }

        if (!@mysql_select_db($sDatabase, $this->rLink)) {
            // {{{ DEBUG }}}
            Logger::error('MySQL error '.mysql_errno().': '.mysql_error());
            $bSuccess = false;
        }

        // Set charset
        $this->query("SET NAMES `utf8` COLLATE `utf8_unicode_ci`");

        return $bSuccess;
    }

    // --------------------------------------------------------------------

    /**
     * Query
     *
     * @access  public
     * @param   string
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$mQueryType"
     * @todo    [D11N]  Check return type
     */
    public function query($sQuery, $mQueryType = null) {
        //echo $sQuery.'<hr />'; // Debug output

        if (!$this->rResult = @mysql_query($sQuery, $this->rLink)) {
            // {{{ DEBUG }}}
            Logger::error('MySQL error '.mysql_errno().': '.mysql_error());

            RuntimeContext::getInstance()->addError(560, $mQueryType);
            RuntimeContext::getInstance()->terminate();
        }

        return $this->rResult;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch array
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nQueryId"
     * @todo    [D11N]  Check return type
     */
    public function fetchArray($nQueryId) {
        return @mysql_fetch_assoc($nQueryId);
    }

    // --------------------------------------------------------------------

    /**
     * Fetch object
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nQueryId"
     */
    public function fetchObject($nQueryId) {
        $aData = $this->fetchArray($nQueryId);

        if (!is_array($aData)) {
            return null;
        }

        $aKeys = array_keys($aData);
        $Obj = new Object();

        for ($i=0, $n=sizeof($aKeys); $i<$n; $i++) {
            $Obj->set($aKeys[$i], $aData[$aKeys[$i]]);
        }

        return $Obj;
    }

    // --------------------------------------------------------------------

    /**
     * Add limit to query
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nFrom"
     * @todo    [D11N]  Check the parameter type of "$nCount"
     * @todo    [D11N]  Check return type
     */
    public function addLimitToQuery($sQuery, $nFrom, $nCount) {
        return $sQuery . ' LIMIT ' . $nFrom . ',' . $nCount;
    }

    // --------------------------------------------------------------------

    /**
     * Get last insert id
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    public function getLastInsertId($sTableName) {
        return $this->sLastId;
    }

    // --------------------------------------------------------------------

    /**
     * Escape string
     *
     * @access  private
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check return type
     */
    private function escapeString($sStr) {
        return addslashes($sStr);
    }

    // --------------------------------------------------------------------

    /**
     * Remove
     *
     * @access  public
     * @param   array
     * @return  ressource
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function remove($aData) {

        $sQuery = 'DELETE FROM '.$aData['table'].' WHERE '.$aData['where'];

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->query($sQuery, 'DELETE');

        return $rResult;
    }

    // --------------------------------------------------------------------

    /**
     * Insert
     *
     * @access  public
     * @param   array
     * @return  ressource
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function insert($aData) {

        $sQuery = 'INSERT INTO ' . $aData['table'];
        $sValue = '';

        $sQuery .= ' (';
        $sValue .= ' VALUES (';

        while (list($key, $value) = each($aData['fields'])) {
            $sQuery .= $key;
            $sQuery .= ", ";
            $sValue .= "'";
            $sValue .= $this->escapeString($value);
            $sValue .= "', ";
        }

        // Remove last comma and space
        $sQuery = substr($sQuery, 0, -2);
        $sValue = substr($sValue, 0, -2);

        $sQuery .= ') ';
        $sValue .= ') ';

        // {{{ DEBUG }}}
        Logger::sql($sQuery . $sValue);

        $rResult = $this->query($sQuery . $sValue, 'INSERT');

        // Save the "last insert id", it would be falsified by the
        // "commit()" method otherwise.
        $this->sLastId = @mysql_insert_id($this->rLink);

        return $rResult;
    }

    // --------------------------------------------------------------------

    /**
     * Update
     *
     * @access  public
     * @param   array
     * @return  ressource
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function update($aData) {

        $sQuery = 'UPDATE ' . $aData['table'] . ' SET ';

        while (list($key, $value) = each($aData['fields'])) {
            $sQuery .= $key;
            $sQuery .= "='";
            $sQuery .= $this->escapeString($value);
            $sQuery .= "', ";
        }

        // Remove last comma and space
        $sQuery = substr($sQuery, 0, -2);
        $sQuery .= ' WHERE ' . $aData['where'];

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->query($sQuery, 'UPDATE');

        return $rResult;
    }

    // --------------------------------------------------------------------

    /**
     * Free result
     *
     * @access  public
     * @param   ressource
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function freeResult($rResult) {
        @mysql_free_result($rResult);
    }

    // --------------------------------------------------------------------

    /**
     * Begin transaction
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     *
     * @todo    [D11N]  Check description
     */
    public function begin() {
        if ($this->nTrans == 0) {

            // {{{ DEBUG }}}
            Logger::sql('BEGIN');

            $this->query('BEGIN');
        }

        $this->nTrans++;
    }

    // --------------------------------------------------------------------

    /**
     * Commit transaction
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     *
     * @todo    [D11N]  Check description
     */
    public function commit() {
        $this->nTrans--;

        if ($this->nTrans < 0) {
            $this->rollback();

            throw new StorageException(
                'Tried to commit after rollback or no transaction started.'
            );
        }

        if ($this->nTrans == 0) {

            // {{{ DEBUG }}}
            Logger::sql('COMMIT');

            $this->query('COMMIT');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Rollback transaction
     *
     * @access  public
     * @param   string
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.4.0
     *
     * @todo    [D11N]  Check description
     */
    public function rollback() {
        $this->nTrans = 0;

        // {{{ DEBUG }}}
        Logger::sql('ROLLBACK');

        $this->query('ROLLBACK');
    }

    // --------------------------------------------------------------------

    /**
     * Provide an unique number - used to determine and avoid (multiple)
     * changes in a database record (this number is the actual
     * unix-timestamp plus a dash plus random eight cipher number)
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    public function generateTan()  {
        mt_srand((double)microtime() * 1000000);
        return time() . '-' . mt_rand(10000000, 99999999);
    }

    // --------------------------------------------------------------------

    /**
     * Get date time as string
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nStamp"
     * @todo    [D11N]  Check return type
     */
    public function getDateTimeAsString($nStamp) {
        return date('Y-m-d H:i:s', $nStamp);
    }

    // --------------------------------------------------------------------

    /**
     * Capacity of fields containing textdata (in bytes)
     * Just for now, with MySQL as the only Storage, we use the Capacity
     * of MediumBlobs.
     *
     * @access  public
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check return type
     */
    public function getTextCapacity() {
        return 16777215;
    }

} // of class

/*
    With your feet on the air and your head on the ground
    Try this trick and spin it, yeah
    Your head will collapse if there's nothing in it
    And you'll ask yourself
    Where is my mind? Where is my mind? Where is my mind?

    Way out in the water, see it swimming

    I was swimming in the Caribbean
    Animals were hiding behind the rock
    Except for little fish
    When they told me east is west trying to talk to me, coy koi
    Where is my mind? Where is my mind? Where is my mind?

    Way out in the water, see it swimming

    With your feet on the air and your head on the ground
    Try this trick and spin it, yeah
    Your head will collapse if there's nothing in it
    And you'll ask yourself
    Where is my mind? Where is my mind? Where is my mind?

    Way out in the water, see it swimming

    With your feet on the air and your head on the ground
    Try this trick and spin it, yeah
*/

?>
