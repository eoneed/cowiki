<?php

/**
 *
 * $Id: class.CommentDAO.php 19 2011-01-04 03:52:35Z eoneed $
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
 * @version     $Revision: 19 $
 *
 */

/**
 * coWiki - Comment DAO class
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
class CommentDAO extends PersistentObservable  {

    protected static
        $Instance = null;

    protected
        $Storage   = null,
        $Request   = null,
        $Registry  = null,
        $Context   = null,
        $aComCache = array(),
        $sComTable = 'cowiki_comment';

    /**
     * Get instance
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
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new CommentDAO;
        }
        return self::$Instance;
    }

    /**
     * Class constructor
     *
     * @access  protected
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function __construct() {
        $this->Context  = RuntimeContext::getInstance();
        $this->Request  = $this->Context->getRequest();
        $this->Registry = $this->Context->getRegistry();
        $this->Storage  = StorageFactory::getInstance()->createDocStorage();
    }

    /**
     * Get recent comments for node
     *
     * @access  public
     * @param   object
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check the parameter type of "$nLimit"
     * @todo    [D11N]  Check return type
     */
    public function getRecentCommentsForNode($Node, $nLimit = 4) {
        $Coms = new Vector();

        $sQuery = " SELECT  *,
                            UNIX_TIMESTAMP(created) AS created
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Node->get('id')."
                  ORDER BY  created DESC";

        // Limit the query to required count
        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, $nLimit);
        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Coms->add($this->createComment($aData));
        }

        return $Coms;
    }

    /**
     * Get recent comments
     *
     * @access  public
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nLimit"
     * @todo    [D11N]  Check return type
     */
    public function getRecentComments($nLimit = 50) {
        $Coms = new Vector();

        $sQuery = " SELECT  *,
                            UNIX_TIMESTAMP(created) AS created
                      FROM  ".$this->sComTable."
                  ORDER BY  created DESC";

        // Limit the query to required count
        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, $nLimit);
        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Coms->add($this->createComment($aData));
        }

        return $Coms;
    }

    /**
     * Get comment count
     *
     * @access  public
     * @param   object
     * @return  integer
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function getCommentCount($Node) {
        $sQuery = " SELECT  count(comment_id) AS rec_count
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Node->get('id');

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return (int)$aData['rec_count'];
    }

    /**
     * Get thread count
     *
     * @access  public
     * @param   object
     * @return  integer
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function getThreadCount($Node) {
        $sQuery = " SELECT  count(comment_id) AS rec_count
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Node->get('id')."
                       AND  lft = 1";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return (int)$aData['rec_count'];
    }

    /**
     * Get comment count for tree id
     *
     * @access  public
     * @param   integer
     * @return  integer
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     */
    public function getCommentCountForTreeId($nId) {
        $sQuery = " SELECT  count(comment_id) AS rec_count
                      FROM  ".$this->sComTable."
                     WHERE  tree_id = ".$nId;

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return (int)$aData['rec_count'];
    }

    /**
     * Get comment list
     *
     * @access  public
     * @param   object
     * @param   integer
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check the parameter type of "$nStart"
     * @todo    [D11N]  Check the parameter type of "$nLimit"
     * @todo    [D11N]  Check return type
     */
    public function getCommentList($Node, $nStart, $nLimit) {
        $Coms = new Vector();

        $sQuery = " SELECT  *,
                            UNIX_TIMESTAMP(created) AS created
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Node->get('id')."
                       AND  lft = 1
                  ORDER BY  created DESC";

        // Limit the query to required count
        $sQuery  = $this->Storage->addLimitToQuery($sQuery, $nStart, $nLimit);
        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Coms->add($this->createComment($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Coms;
    }

    /**
     * Get comment by id
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     */
    public function getCommentById($nId) {
        $nId = (int)$nId;

        if (isset($this->aComCache[$nId])) {
            return $this->aComCache[$nId];
        }

        // ---

        $sQuery = " SELECT  *,
                            floor((rgt-lft) / 2) AS replies,
                            UNIX_TIMESTAMP(created) AS created
                      FROM  ".$this->sComTable."
                     WHERE  comment_id = ".$nId;

        // Limit the query to required count
        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if (!$aData) {
            return null;
        }

        $Com = $this->createComment($aData);
        $this->aComCache[$nId] = $Com;

        return $Com;
    }

    /**
     * Get thread
     *
     * @access  public
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     */
    public function getThread($nId) {

        $sQuery = " SELECT  table1.comment_id,
                            table1.node_id,
                            table1.subject,
                            table1.author_name,
                            UNIX_TIMESTAMP(table1.created) AS created,
                            COUNT(*) AS level
                      FROM  ".$this->sComTable." AS table1,
                            ".$this->sComTable." AS table2
                     WHERE  table1.tree_id = ".$nId."
                       AND  table2.tree_id = ".$nId."
                       AND  table1.lft BETWEEN table2.lft AND table2.rgt
                  GROUP BY  table1.lft";

        $rResult = $this->Storage->query($sQuery);
        $aData = $this->Storage->fetchArray($rResult);

        if (!$aData) {
            return false;
        }

        $aNodes = array();

        // Root node
        $Com = $this->createComment($aData);
        $aNodes[$Com->get('level')] = $Com;

        // Further nodes
        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Com = $this->createComment($aData);

            $aNodes[$Com->get('level')] = $Com;
            $aNodes[$Com->get('level')-1]->addItem($Com);
        }

        $this->Storage->freeResult($rResult);

        return $aNodes[0];
    }

    /**
     * Get prev thread id
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Com"
     */
    public function getPrevThreadId($Com) {

        $sQuery = " SELECT  comment_id
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Com->get('nodeId')."
                       AND  tree_id < ".$Com->get('treeId')."
                       AND  lft = 1
                  ORDER BY  tree_id DESC";

        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, 1);
        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData) {
            return (int)$aData['comment_id'];
        }

        return null;
    }

    /**
     * Get next thread id
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Com"
     */
    public function getNextThreadId($Com) {

        $sQuery = " SELECT  comment_id
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Com->get('nodeId')."
                       AND  tree_id > ".$Com->get('treeId')."
                       AND  lft = 1
                  ORDER BY  tree_id ASC";

        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, 1);
        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData) {
            return (int)$aData['comment_id'];
        }

        return null;
    }

    /**
     * Get prev posting id
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Com"
     */
    public function getPrevPostingId($Com) {

        $sQuery = " SELECT  comment_id
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Com->get('nodeId')."
                       AND  tree_id = ".$Com->get('treeId')."
                       AND  lft < ".$Com->get('left')."
                  ORDER BY  lft DESC";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData) {
            return (int)$aData['comment_id'];
        }

        return null;
    }

    /**
     * Get next posting id
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Com"
     */
    public function getNextPostingId($Com) {
        $sQuery = " SELECT  comment_id
                      FROM  ".$this->sComTable."
                     WHERE  node_id = ".$Com->get('nodeId')."
                       AND  tree_id = ".$Com->get('treeId')."
                       AND  lft > ".$Com->get('left')."
                  ORDER BY  lft ASC";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData) {
            return (int)$aData['comment_id'];
        }

        return null;
    }

    // --- STORE LOGIC ----------------------------------------------------

    /**
     * Increment views
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Com"
     */
    public function incrementViews($Com) {
        $Com->set('views', $Com->get('views') + 1);

        $aUpdate = array(
            'table'  => $this->sComTable,
            'fields' => array('views' => $Com->get('views')),
            'where'  => "comment_id = '".$Com->get('id')."'"
        );

        // Execute update (terminates on error)
        $this->Storage->update($aUpdate);
    }

    /**
     * Store
     *
     * @access  public
     * @param   object
     * @param   integer
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$ComItem"
     * @todo    [D11N]  Check the parameter type of "$nReplyToId"
     */
    public function store($ComItem, $nReplyToId = 0) {

        $Node = $this->Context->getCurrentNode();
        $CurrUser = $this->Context->getCurrentUser();

        // Check if user is logged in
        if (!$CurrUser->get('isValid')) {
            $this->Context->addError(403);
            return false;
        }

        // Clean, remove \r in content text
        $ComItem->set('subject', trim($ComItem->get('subject')));
        $ComItem->set(
            'content',
            trim(str_replace("\r", '', $ComItem->get('content')))
        );

        // Check for empty fields
        if ($ComItem->get('subject') == '') {
             $this->Context->addError(424);
        }

        if ($ComItem->get('content') == '') {
            $this->Context->addError(425);
        }

        // Remove forbidden characters
        $ComItem->set(
            'subject',
            str_replace(array('|','¦','#'), '', $ComItem->get('subject'))
        );

        // ----------------------------------------------------------------

        // Check qoutation
        $nCite = 1;
        $nNew  = 1;

        $aLines = explode("\n", $ComItem->get('content'));

        for ($i=0, $n=sizeof($aLines); $i<$n; $i++) {

            if (strlen($aLines[$i])) {
                if ($aLines[$i]{0} == '>') {
                    $nCite++;
                    continue;
                }
                if (trim($aLines[$i]) != '') {
                    $nNew++;
                    continue;
                }
            }
        }

        // Max. quotation ratio
        if ($nCite/$nNew >= 3) {
            $this->Context->addError(445);
        }

        if (substr(trim($aLines[sizeof($aLines)-1]), 0, 1) == '>') {
            $this->Context->addError(446);
        }

        // Check max. length
        if (strlen($ComItem->get('content')) > 3900) {
            $this->Context->addError(441);
        }

        // ----------------------------------------------------------------

        // Leave if we have errors
        if ($this->Context->hasErrors()) {
            return false;
        }

        // ----------------------------------------------------------------

        // Lookup host name
        $sHost = '';
        if ($this->Registry->get('RUNTIME_LOOKUP_DNS')) {
            $sHost = $this->Request->getRemoteHost();
        }

        // Gather basic data for insert/update
        $aFields = array(
            'rec_tan'      => $this->Storage->generateTan(),
            'rec_mod_id'   => $CurrUser->get('userId'),
            'rec_mod_ip'   => $this->Request->getRemoteAddr(),
            'rec_mod_host' => $sHost,

            'subject'      => $ComItem->get('subject'),
            'wikisubject'  => wikiWord($ComItem->get('subject')),
            'content'      => $ComItem->get('content'),
            'notify'       => $ComItem->get('notify') ? 'Y' : ''
        );

        // Lookup host name
        $aFields['rec_mod_host'] = '';
        if ($this->Registry->get('RUNTIME_LOOKUP_DNS')) {
            $aFields['rec_mod_host'] = $this->Request->getRemoteHost();
        }

        $this->Storage->begin();

        // Is this is an update or an initial insert?
        if ($ComItem->get('commentId')) {
            // Update

        } else {
            // Initial insert

            // Gather data
            $aInitial = array(
                'rec_state'    => 'R',
                'node_id'      => $Node->get('id'),

                'author_id'    => $CurrUser->get('userId'),
                'author_name'  => $CurrUser->get('name'),
                'author_email' => $CurrUser->get('email'),
                'author_ip'    => $this->Request->getRemoteAddr(),
                'author_host'  => $sHost,
                'created'      => $this->Storage->getDateTimeAsString(time()),
            );

            // Prepare insert
            $aInsert = array(
                'table'  => $this->sComTable,
                'fields' => array_merge($aFields, $aInitial)
            );

            // Execute insert, get last insert id
            $this->Storage->insert($aInsert);
            $nLast = $this->Storage->getLastInsertId($this->sComTable);

            // Get nested set data
            $sQuery = " SELECT  rgt, tree_id
                          FROM  ".$this->sComTable."
                         WHERE  comment_id = ".(int)$nReplyToId;

            $rResult = $this->Storage->query($sQuery);
            $aData   = $this->Storage->fetchArray($rResult);
                       $this->Storage->freeResult($rResult);

            // First node in branch?
            if (!$aData) {
                $aData['tree_id'] = $nLast;
                $aData['rgt']     = 1;

            } else {

                // Update nested set LFT-RGT value pairs
                $sQuery = " UPDATE  ".$this->sComTable."
                               SET  lft = lft + 2
                             WHERE  tree_id = ".$aData['tree_id']."
                               AND  lft > ".$aData['rgt'];
                $this->Storage->query($sQuery);

                $sQuery = " UPDATE  ".$this->sComTable."
                               SET  rgt = rgt + 2
                             WHERE  tree_id = ".$aData['tree_id']."
                               AND  rgt >= ".$aData['rgt'];
                $this->Storage->query($sQuery);
            }

            // Update additional data in our new record
            $aFields = array(
                'tree_id' => $aData['tree_id'],
                'lft'     => $aData['rgt'],
                'rgt'     => $aData['rgt'] + 1
            );

            $aUpdate = array(
                'table'  => $this->sComTable,
                'fields' => $aFields,
                'where'  => "comment_id = ".$nLast
            );

            // Execute update
            $this->Storage->update($aUpdate);
        }

        // ----------------------------------------------------------------

        // Update root element, set data for faster list display
        $nReplies = $this->getCommentCountForTreeId($aData['tree_id']);
        $sLatest = $this->Storage->getDateTimeAsString(time());

        $aFields = array(
            'replies' => $nReplies - 1,
            'latest'  => $sLatest
        );

        $aUpdate = array(
            'table'  => $this->sComTable,
            'fields' => $aFields,
            'where'  => "comment_id = ".$aData['tree_id']
        );

        // Execute update pn the root node
        $this->Storage->update($aUpdate);

        // ----------------------------------------------------------------

        $this->Storage->commit();

        // Tell the observers that something has changed
        $this->notifyObservers();

        return true;
    }

    // === HELPER =========================================================

    /**
     * Generic comment object creator
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createComment(&$aData) {

        // No data?
        if (!$aData) {
            return null;
        }

        $ComItem = new CommentItem();
        $this->invokePropertyMutator($ComItem, $aData);

        return $ComItem;
    }

    /**
     * Invoke property mutator
     *
     * @access  protected
     * @param   object
     * @param   array
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$ComItem"
     */
    protected function invokePropertyMutator($ComItem, $aData) {

        // --- Internal document data -------------------------------------

        if (isset($aData['rec_tan'])) {
            $ComItem->set('recTan', $aData['rec_tan']);
            $ComItem->set('modified', (int)$aData['rec_tan']);
        }

        if (isset($aData['rec_mod_id'])) {
            $ComItem->set('modifiedByUid', (int)$aData['rec_mod_id']);
        }

        if (isset($aData['rec_mod_ip'])) {
            $ComItem->set('remoteAddr', $aData['rec_mod_ip']);
        }

        if (isset($aData['rec_mod_host'])) {
            $ComItem->set('remoteHost', $aData['rec_mod_host']);
        }

        if (isset($aData['rec_state'])) {
            $ComItem->set('recState', $aData['rec_state']);
        }

        if (isset($aData['comment_id'])) {
            $ComItem->set('id', (int)$aData['comment_id']);
        }

        if (isset($aData['node_id'])) {
            $ComItem->set('nodeId', (int)$aData['node_id']);
        }

        if (isset($aData['tree_id'])) {
            $ComItem->set('treeId', (int)$aData['tree_id']);
        }

        // --- User/Group -------------------------------------------------

        if (isset($aData['user_id'])) {
            $ComItem->set('userId', (int)$aData['user_id']);
        }

        if (isset($aData['group_id'])) {
            $ComItem->set('groupId', (int)$aData['group_id']);
        }

        // --- Creation and co. -------------------------------------------

        if (isset($aData['created'])) {
            $ComItem->set('created', (int)$aData['created']);
        }

        if (isset($aData['author_id'])) {
            $ComItem->set('authorId', (int)$aData['author_id']);
        }

        if (isset($aData['replies'])) {
            $ComItem->set('replies', (int)$aData['replies']);
        }

        // --- Payload ----------------------------------------------------

        $ComItem->set('level', 0);

        if (isset($aData['level'])) {
            $ComItem->set('level', ((int)$aData['level']) - 1);
        }

        if (isset($aData['subject'])) {
            $ComItem->set('subject', $aData['subject']);
        }

        if (isset($aData['wikisubject'])) {
            $ComItem->set('wikiSubject', $aData['wikisubject']);
        }

        if (isset($aData['encoding'])) {
            $ComItem->set('encoding', $aData['encoding']);
        }

        if (isset($aData['content'])) {
            $ComItem->set('content', $aData['content']);
        }

        // --- Supplement data --------------------------------------------

        if (isset($aData['author_id'])) {
            $ComItem->set('authorId', (int)$aData['author_id']);
        }

        if (isset($aData['author_id'])) {
            $ComItem->set('authorId', (int)$aData['author_id']);
        }

        if (isset($aData['author_name'])) {
            $ComItem->set('authorName', $aData['author_name']);
        }

        if (isset($aData['author_email'])) {
            $ComItem->set('authorEmail', $aData['author_email']);
        }

        if (isset($aData['views'])) {
            $ComItem->set('views', (int)$aData['views']);
        }

        if (isset($aData['tenor'])) {
            $ComItem->set('tenor', (int)$aData['tenor']);
        }

        if (isset($aData['notify'])) {
            $ComItem->set('notify', $aData['notify'] == 'Y');
        }

        if (isset($aData['meta'])) {
            $ComItem->set('meta', $aData['meta']);
        }

        if (isset($aData['lft'])) {
            $ComItem->set('left', (int)$aData['lft']);
        }

        if (isset($aData['rgt'])) {
            $ComItem->set('right', (int)$aData['rgt']);
        }

    } // of invokePropertyMutator()

} // of class

/*
    This is not heresy, I will not repent ...
*/

?>
