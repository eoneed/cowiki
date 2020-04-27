<?php

/**
 *
 * $Id: class.DocumentDAO.php 19 2011-01-04 03:52:35Z eoneed $
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
 * coWiki - Document DAO. The DAO carries methods for storing and
 * retrieving documents or document trees.
 *
 * @package     dao
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.0
 */
class DocumentDAO extends PersistentObservable {

    protected static
        $Instance = null;

    protected
        $sLazy = 'rec_tan, rec_mod_id, rec_mod_ip, rec_mod_host, rec_state,
                  node_id, tree_id, parent_id, is_dir, is_index,
                  user_id, group_id, mode,
                  UNIX_TIMESTAMP(created) as created,
                  author_id, revision, name, wikiname, encoding,
                  notify_user, notify_group,
                  menu, foot, views, sort_order';

    protected
        $Storage        = null,
        $Request        = null,
        $Registry       = null,
        $Context        = null,
        $WebComposite   = null,
        $aBackup        = array(),
        $sNodeTable     = 'cowiki_node',
        $sNodeHistTable = 'cowiki_node_hist',
        $sNodeRefTable  = 'cowiki_node_ref';

    // --------------------------------------------------------------------

    /**
     * Get the unique instance of the class (This class is implemented as
     * Singleton).
     *
     * @access  public
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public static function getInstance() {
        if (!self::$Instance) {
            self::$Instance = new DocumentDAO;
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
        // {{{ DEBUG }}}
        Logger::info('Initializing.');

        $this->Context  = RuntimeContext::getInstance();
        $this->Request  = $this->Context->getRequest();
        $this->Registry = $this->Context->getRegistry();
        $this->Storage  = StorageFactory::getInstance()->createDocStorage();
    }

    // --- LOAD LOGIC -----------------------------------------------------

    /**
     * Get the default node in the coWiki structure - the "index" node.
     *
     * @access  public
     * @return  object  A populated DocumentItem object.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getDefaultNode($User) {

        // {{{ DEBUG }}}
        Logger::info(
            'Fetching the default web node for user "'.$User->get('login').'"'
        );

        // Criteria expressions
        $Criteria = new SearchCriteria();

        $Conj = new Conjunction();
        $Conj->add(new EqPropertyExpression('parent_id', 0));
        $Conj->add(new EqExpression('is_dir', 'Y'));
        $Criteria->addExpression($Conj);

        $Criteria->addAllExpressions(
            $this->getGuardCheckCriteria($User)->getExpressions()
        );

        // ---

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                            ".$Criteria->getQuery()."
                  ORDER BY  sort_order";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get node by its node id.
     *
     * @access  public
     * @param   integer The node id.
     * @param   string  Database fields (optional). If not given, the
     *                  default properties will be retrieved.
     * @return  object  A populated DocumentItem or DocumentContainer object.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getNodeById($sId, $sFields = '*') {

        // {{{ DEBUG }}}
        Logger::info('Fetching node id #'.$sId);

        $sQuery = " SELECT  ".$sFields.",
                            is_dir, parent_id,
                            UNIX_TIMESTAMP(created) as created
                      FROM  ".$this->sNodeTable."
                     WHERE  node_id = '".$sId."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get node by its name.
     *
     * @access  public
     * @param   string  The name of the requested node.
     * @param   integer The id of the "web" (tree) too look in.
     * @param   string  Database fields (optional). If not given, the
     *                  default properties will be retrieved.
     * @return  object  A populated DocumentItem or DocumentContainer object.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getNodeByName($sName, $nTreeId, $sFields = '*') {

        // {{{ DEBUG }}}
        Logger::info('Fetching node by name "'.$sName.'".');

        $sQuery = " SELECT ".$sFields.",
                           is_dir, parent_id,
                           UNIX_TIMESTAMP(created) as created
                     FROM  ".$this->sNodeTable."
                    WHERE  tree_id = '".$nTreeId."'
                      AND  name = '".addslashes($sName)."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get nodes by criteria.
     *
     * @access  public
     * @param   object  The Criteria object.
     * @param   array   Database fields. If not given, the default
     *                  properties will be retrieved.
     * @return  object  The result Vector with DocumentItem and/or
     *                  DocumentContainer objects that match the criteria.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     getNodeIdsByCriteria
     * @see     getUnguardedNodesByCriteria
     */
    public function getNodesByCriteria($Criteria, $aFields = array()) {

        // {{{ DEBUG }}}
        Logger::info('Fetching nodes by criteria.');

        $sFields = $this->sLazy;

        // Additional fields?
        if (sizeof($aFields)) {
            if (in_array('*', $aFields)) {
                $sFields = '*';
            } else {
                $sFields .= ',' . join(',', $aFields);
            }
        }

        // Get all nodes that match the where clause (criteria)
        $sQuery = " SELECT  ".$sFields."
                      FROM  ".$this->sNodeTable."
                            ".$Criteria->getQuery()."
                            ".$Criteria->getOrderBy();

        $sQuery = $this->Storage->addLimitToQuery(
                      $sQuery,
                      $Criteria->getFirstResult(),
                      $Criteria->getMaxResults()
                   );

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        $Vector = new Vector();
        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Vector->add($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Vector;
    }

    // --------------------------------------------------------------------

    /**
     * Get node ids by Criteria. Retrieves ids only, which is faster than
     * getNodesByCriteria().
     *
     * @access  public
     * @param   object  The Criteria object.
     * @return  array   Array with node ids that match the criteria.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     getNodesByCriteria
     */
    public function getNodeIdsByCriteria($Criteria) {

        // {{{ DEBUG }}}
        Logger::info('Fetching node ids by criteria.');

        // Get all ids of nodes that match the where clause (criteria)
        $sQuery = " SELECT  node_id
                      FROM  ".$this->sNodeTable."
                            ".$Criteria->getQuery()."
                            ".$Criteria->getOrderBy();

        $sQuery = $this->Storage->addLimitToQuery(
                      $sQuery,
                      $Criteria->getFirstResult(),
                      $Criteria->getMaxResults()
                   );

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        $aIds = array();
        while ($aData = $this->Storage->fetchArray($rResult)) {
            $aIds[] = $aData['node_id'];
        }

        $this->Storage->freeResult($rResult);

        return $aIds;
    }

    // --------------------------------------------------------------------

    /**
     * Get unguarded nodes by Criteria. Unguarded nodes are all documents
     * where the given user has access to.
     *
     * @access  public
     * @param   object  The Criteria object.
     * @param   object  The User object.
     * @param   string  Database fields (optional). If not given, the
     *                  default properties will be retrieved.
     * @return  object  The result Vector with DocumentItem and/or
     *                  DocumentContainer objects that match the criteria.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     getNodesByCriteria
     */
    public function getUnguardedNodesByCriteria(
                        $Criteria, $User, $aFields = array()) {

        // {{{ DEBUG }}}
        Logger::info('Fetching unguarded nodes by criteria.');

        // Save expressions that are already in Criteria
        $Vector = $Criteria->getExpressions();
        $Criteria->resetExpressions();

        // Additional Criteria expressions
        $Criteria->addAllExpressions(
            $this->getGuardCheckCriteria($User)->getExpressions()
        );

        // Restore the old expressions
        $Criteria->addAllExpressions($Vector);

        // Step 1: Get all affected nodes that are directly readable
        //         by the current user.
        $TmpVector = $this->getNodesByCriteria($Criteria, $aFields);

        // Step 2: Due to the hierarchical order of documents following
        //         might happen: the node itself is readable/accessible but
        //         one of its parents isn't. Let's check it now.
        //
        // FIX: This is very time and memory consuming (especially for
        // bigger structures) and has to be rewritten and improved someday!

        $Vector = new Vector();

        $It = $TmpVector->iterator();
        while ($Node = $It->next()) {

            if ($this->getNodePath($Node)->isReadable()) {
                $Vector->add($Node);
            }

            // Save memory
            unset($Node);
        }

        return $Vector;
    }

    // --------------------------------------------------------------------

    /**
     * Get guard check criteria. It's a helper for the
     * getUnguardedNodesByCriteria() method. It assembles the Criteria.
     *
     * @access  protected
     * @param   object  The User object.
     * @param   object  A Criteria object.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [FIX] Postgres and Oracle do not know "conv()", do they?
     */
    protected function getGuardCheckCriteria($User) {

        $Criteria = new SearchCriteria();

        // No check for root user
        if ($User->isRoot()) {
            return $Criteria;
        }

        $CheckJunc = new Disjunction();

        // ---

        $Junc = new Conjunction();

        // Get user id of current user
        $Exp = new EqExpression('user_id', $User->get('userId'));
        $Junc->add($Exp);

        // Check for isUserReadable
        // FIX: Postgres and Oracle do not know "conv()", do they?
        $Exp = new LogicAndPropertyExpression('conv(mode,8,10)', 256);
        $Junc->add($Exp);

        $CheckJunc->add($Junc);

        // ---

        // Get default and member groups of current user
        $aGrp = array();
        $aGrp[] = $User->get('groupId');

        $It = $User->getMemberGroups()->iterator();
        while ($Obj = $It->next()) {
            $aGrp[] = $Obj->get('groupId');
        }

        if (sizeof($aGrp)) {

            $Junc = new Conjunction();

            // Create expression to filter groups where the user belongs to
            $Exp = new InExpression('group_id', $aGrp);
            $Junc->add($Exp);

            // Check for isGroupReadable
            // FIX: Postgres and Oracle do not know "conv()", do they?
            $Exp = new LogicAndPropertyExpression('conv(mode,8,10)', 32);
            $Junc->add($Exp);

            $CheckJunc->add($Junc);
        }

        // ---

        $Junc = new Conjunction();

        // Check if isWorldReadable
        // FIX: Postgres and Oracle do not know "conv()", do they?
        $Exp = new LogicAndPropertyExpression('conv(mode,8,10)', 4);
        $Junc->add($Exp);

        $CheckJunc->add($Junc);

        // ---

        $Criteria->addExpression($CheckJunc);

        return $Criteria;
    }

    // --------------------------------------------------------------------

    /**
     * Starting with any tree node this method builds the path references
     * of the nodes parent nodes. The reference building is stopped if
     * the root node of the branch is reached.
     *
     * @access  public
     * @param   object  The node you want to start with.
     * @return  object  The node with its (back) references (parents).
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getNodePath($Node) {

        if ($Node->get('parentId') == 0) {
            return $Node;
        }

        // {{{ DEBUG }}}
        Logger::info('Fetching node path for node id #'.$Node->get('id'));

        $NewNode = $this->getNodeById(
                      $Node->get('parentId'),
                      $this->sLazy

                   );

        if (is_object($NewNode)) {
            $NewNode->addItem($Node);
            $this->getNodePath($NewNode);
        }

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Returns the index node of a given DocumentContainer node if an
     * index is defined.
     *
     * @access  public
     * @param   object  The node you want the index for.
     * @return  object  A populated DocumentItem object (may contain empty
     *                  data if no index node is definded).
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getIndexNodeOf($Node) {

        // {{{ DEBUG }}}
        Logger::info('Fetching index node of node id #'.$Node->get('id'));

        $sQuery = " SELECT  *,
                            UNIX_TIMESTAMP(created) as created
                      FROM  ".$this->sNodeTable."
                     WHERE  tree_id   = '".$Node->get('treeId')."'
                       AND  parent_id = '".$Node->get('id')."'
                       AND  is_dir    = ''
                       AND  is_index  = 'Y'";

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Returns (dummy 'root') node with all webs as children.
     *
     * @access  public
     * @return  object  (Dummy root) node with all webs as its children.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    public function getWebComposite() {

        if (is_object($this->WebComposite)) {
            return $this->WebComposite;
        }

        // {{{ DEBUG }}}
        Logger::info('Fetching web composite.');

        // Create a (root) container and set its access to all
        $Node = new DocumentContainer();
        $Node->setAccessByUmask(0777, 0);

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                     WHERE  node_id = tree_id
                  ORDER BY  sort_order";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Node->addItem($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        // Possibly we need to recall the structure later
        $this->WebComposite = $Node;

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get recently changed nodes (DocumentItems only, no
     * DocumentContainers). This is a very 'expensive' method.
     * Use with care.
     *
     * @access  public
     * @param   integer Number of nodes to return (limit).
     * @return  object  (Dummy root) node with recently changed nodes.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     getRecentlyChangedNodesForGuest
     * @todo    [FIX] make it faster
     */
    public function getRecentlyChangedNodes($nLimit) {

        // {{{ DEBUG }}}
        Logger::info('Fetching recently changed nodes');

        $Node = new DocumentContainer();
        $nCount = 0;
        $nLimit = abs($nLimit);

        // Get all ids of documents that has been changed recently
        $sQuery = " SELECT  node_id
                      FROM  ".$this->sNodeTable."
                     WHERE  is_dir != 'Y'
                  ORDER BY  rec_tan DESC";

        // Limit the query to save memory
        $sQuery = $this->Storage->addLimitToQuery($sQuery, 0, 1000);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {

            // Get document for readability check
            $sQuery = " SELECT  ".$this->sLazy.", summary
                          FROM  ".$this->sNodeTable."
                         WHERE  node_id = '".$aData['node_id']."'";

            $rResult2 = $this->Storage->query($sQuery);
            $aData    = $this->Storage->fetchArray($rResult2);
                        $this->Storage->freeResult($rResult2);

            $NewNode = $this->createNode($aData);

            // Get path up to root node
            $NewNode = $this->getNodePath($NewNode);

            // Check readability
            if ($NewNode->isReadable()) {
                $Node->addItem($NewNode);

                if (++$nCount >= $nLimit) {
                    break; // the while loop
                }
            }
        }

        $this->Storage->freeResult($rResult);

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get recently changed nodes (DocumentItems only, no
     * DocumentContainers) visible for the 'guest' user. This is a very
     * 'expensive' method. Use with care.
     *
     * @access  public
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @see     getRecentlyChangedNodes
     */
    public function getRecentlyChangedNodesForGuest($nLimit) {

        // {{{ DEBUG }}}
        Logger::info('Fetching recently changed nodes for "guest" user.');

        // Reset the current user (make him "guest" temporarily)
        $this->Context->getCurrentUser()->reset();

        // Get recently changed nodes that are readable for current user
        $Node = $this->getRecentlyChangedNodes($nLimit);

        // Restore current user
        $this->Context->getCurrentUser()->restore();

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get hist nodes for id
     *
     * @access  public
     * @param   integer
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check the parameter type of "$nLimit"
     * @todo    [D11N]  Check return type
     */
    public function getHistNodesForId($nId, $nLimit = 50) {

        // {{{ DEBUG }}}
        Logger::info('Fetching historical nodes for node id #'.$nId);

        $Node = new DocumentContainer();

        // Get historical documents
        $sQuery = " SELECT  node_hist_id,
                            ".$this->sLazy."
                      FROM  ".$this->sNodeHistTable."
                     WHERE  node_id = '".$nId."'
                  ORDER BY  revision DESC";

        $sQuery = $this->Storage->addLimitToQuery($sQuery, 0, $nLimit);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Node->addItem($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get hist node for id
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getHistNodeForId($nId, $sFields = '*') {

        // {{{ DEBUG }}}
        Logger::info('Fetching historical node for node id #'.$nId);

        // Get historical document
        $sQuery = " SELECT  ".$sFields.",
                            node_hist_id,
                            is_dir, parent_id,
                            UNIX_TIMESTAMP(created) as created
                      FROM  ".$this->sNodeHistTable."
                     WHERE  node_id = '".$nId."'
                  ORDER BY  revision DESC";

        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, 1);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get hist deleted nodes
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
    public function getHistDeletedNodes($nLimit = 50) {

        // {{{ DEBUG }}}
        Logger::info('Fetching (historical) deleted nodes.');

        $Node = new DocumentContainer();

        $sQuery = "SELECT ".$this->sNodeHistTable.".*
                     FROM ".$this->sNodeHistTable."
                LEFT JOIN ".$this->sNodeTable." USING(node_id)
                LEFT JOIN ".$this->sNodeHistTable." AS nht
                       ON ".$this->sNodeHistTable.".node_id = nht.node_id
                      AND ".$this->sNodeHistTable.".revision < nht.revision
                    WHERE ".$this->sNodeTable.".node_id IS NULL
                      AND nht.node_id IS NULL
                ORDER BY rec_tan DESC";

        $sQuery = $this->Storage->addLimitToQuery($sQuery, 0, $nLimit);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Node->addItem($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Node;

    }

    // --------------------------------------------------------------------

    /**
     * Get hist node by id
     *
     * @access  public
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getHistNodeById($nId, $sFields = '*') {

        // {{{ DEBUG }}}
        Logger::info('Fetching historical node id #'.$nId);

        // Get historical document
        $sQuery = " SELECT  ".$sFields.",
                            node_hist_id,
                            is_dir, parent_id,
                            UNIX_TIMESTAMP(created) as created
                      FROM  ".$this->sNodeHistTable."
                     WHERE  node_hist_id = '".$nId."'";

        $rResult = $this->Storage->query($sQuery);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get hist node by name
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nTreeId"
     * @todo    [D11N]  Check return type
     */
    public function getHistNodeByName($sName, $nTreeId, $sFields = '*') {

        // {{{ DEBUG }}}
        Logger::info('Fetching historical node by name "'.$sName.'"');

        $sQuery = " SELECT ".$sFields.",
                           is_dir, parent_id,
                           UNIX_TIMESTAMP(created) as created
                     FROM  ".$this->sNodeHistTable."
                    WHERE  tree_id = '".$nTreeId."'
                      AND  name = '".addslashes($sName)."'
                 ORDER BY  revision DESC";

        $sQuery  = $this->Storage->addLimitToQuery($sQuery, 0, 1);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get hist node for recover
     *
     * @access  public
     * @param   integer
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$nId"
     * @todo    [D11N]  Check return type
     */
    public function getHistNodeForRecover($nId) {
        $HistNode = $this->getNodePath($this->getHistNodeById($nId));

        $HistNode->set(
            'original',
            $this->getNodeById($HistNode->get('id'))
        );

        if ($HistNode->get('original')) {
            $HistNode->get('original')->set(
                'parent',
                $HistNode->get('parent')
            );
        }

        return $HistNode;
    }

    // --------------------------------------------------------------------

    /**
     * Get web by name
     *
     * @access  public
     * @param   string
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     */
    public function getWebByName($sName) {

        // {{{ DEBUG }}}
        Logger::info('Fetching web by name "'.$sName.'"');

        $sName = strtolower($sName);

        // Get webs as composite
        $Node = $this->getWebComposite();

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {
            if (strtolower($Obj->get('name')) == $sName) {
                return $Obj;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Get web by id
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
    public function getWebById($nId) {

        // {{{ DEBUG }}}
        Logger::info('Fetching web with id #'.$nId);

        // Get webs as composite
        $Node = $this->getWebComposite();

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {
            if ($Obj->get('id') == $nId) {
                return $Obj;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------

    /**
     * Get container children
     *
     * @access  public
     * @param   object
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check return type
     */
    public function getContainerChildren($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Fetching direct children nodes for node id #'.$Node->get('id')
        );

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                     WHERE  tree_id   = '".$Node->get('treeId')."'
                       AND  parent_id = '".$Node->get('id')."'
                       AND  is_dir    = 'Y'
                  ORDER BY  sort_order";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Node->addItem($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get all children
     *
     * @access  public
     * @param   object
     * @return  object
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check return type
     */
    public function getAllChildren($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Fetching all children nodes for node id #'.$Node->get('id')
        );

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                     WHERE  tree_id   = '".$Node->get('treeId')."'
                       AND  parent_id = '".$Node->get('id')."'
                  ORDER BY  sort_order";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);

        // Remove all children
        $Node->removeItems();

        while ($aData = $this->Storage->fetchArray($rResult)) {
            $Node->addItem($this->createNode($aData));
        }

        $this->Storage->freeResult($rResult);

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get prev sibling item
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check return type
     */
    public function getPrevSiblingItem($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Fetching previous sibling of node #'.$Node->get('id')
        );

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                     WHERE  tree_id = '".$Node->get('treeId')."'
                       AND  parent_id = '".$Node->get('parentId')."'
                       AND  is_dir <> 'Y'
                       AND  sort_order < '".$Node->get('sortOrder')."'
                  ORDER BY  sort_order DESC";

        // We need on result
        $sQuery = $this->Storage->addLimitToQuery($sQuery, 0, 1);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
    }

    // --------------------------------------------------------------------

    /**
     * Get next sibling item
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check return type
     */
    public function getNextSiblingItem($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Fetching successive sibling of node #'.$Node->get('id')
        );

        $sQuery = " SELECT  ".$this->sLazy."
                      FROM  ".$this->sNodeTable."
                     WHERE  tree_id = '".$Node->get('treeId')."'
                       AND  parent_id = '".$Node->get('parentId')."'
                       AND  is_dir <> 'Y'
                       AND  sort_order > '".$Node->get('sortOrder')."'
                  ORDER BY  sort_order ASC";

        // We need on result
        $sQuery = $this->Storage->addLimitToQuery($sQuery, 0, 1);

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return $this->createNode($aData);
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
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function incrementViews($Node) {

        // {{{ DEBUG }}}
        Logger::info('Incrementing view counter of node #'.$Node->get('id'));

        $aUpdate = array(
            'table'  => $this->sNodeTable,
            'fields' => array('views' => $Node->get('views') + 1),
            'where'  => "node_id = '".$Node->get('id')."'"
        );

        // Execute update (terminates on error)
        $this->Storage->update($aUpdate);
    }

    // --------------------------------------------------------------------

    /**
     * Store
     *
     * @access  public
     * @param   object
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function store($Node, $bDoBackup = false) {

        // {{{ DEBUG }}}
        Logger::info('Started saving procedure.');

        $CurrUser = $this->Context->getCurrentUser();

        // Check access if document is writable
        if (!$Node->isWritable()) {

            // {{{ DEBUG }}}
            Logger::warn(
                'Document node id #'.$Node->get('id').' is not writable.
                Aborting.'
            );

            $this->Context->addError(403);
            return false;
        }

        // Check if it is a directory, and if it is "executable" and
        // "editable". isEditable() does not care about world-access bits
        if ($Node->get('isContainer')) {
            if (!$Node->isExecutable() || !$Node->isEditable()) {

                // {{{ DEBUG }}}
                Logger::warn(
                    'Directory node id #'.$Node->get('id').' is not
                    accessible or editable. Aborting.'
                );

                $this->Context->addError(403);
                return false;
            }
        }

        // ----------------------------------------------------------------

        // Remove forbidden characters
        $Node->set(
            'name',
            str_replace(array('|','¦','#'), '', $Node->get('name'))
        );

        // Check name
        $bCheck = $Node->get('name') == ''
                  || $Node->get('name') == '.'
                  || $Node->get('name') == '..';

        if ($bCheck) {
            // Missing directory name or missing document name
            $this->Context->addError($Node->get('isContainer') ? 421 : 423);

            // {{{ DEBUG }}}
            Logger::warn('Missing directory or document name. Aborting.');

            return false;
        }

        // ----------------------------------------------------------------

        // Check for too large data. The data will be splitted in future
        // versions and go to the "spill" tables.
        $nCapacity = $this->Storage->getTextCapacity();
        if (strlen($Node->get('content')) > $nCapacity) {
            $this->Context->addError(441);

            // {{{ DEBUG }}}
            Logger::warn('Data too large. Aborting.');

            return false;
        }

        // ----------------------------------------------------------------

        // Check for a duplicate names
        if ($this->getNameDupesCount($Node) > 0) {
            $this->Context->addError($Node->get('isWeb') ? 411 : 422);

            // {{{ DEBUG }}}
            Logger::warn('Name already exists. Aborting.');

            return false;
        }

        // ----------------------------------------------------------------

        // {{{ DEBUG }}}
        Logger::info('Fetching record TAN for node id #'.$Node->get('id'));

        // Check record tan
        $sQuery = " SELECT  rec_tan
                      FROM  ".$this->sNodeTable."
                     WHERE  node_id = '".$Node->get('id')."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData !== false && $aData['rec_tan'] != $Node->get('recTan')) {
            // Warn only once!
            $Node->set('recTan', $aData['rec_tan']);
            $this->Context->addError(440);

            // {{{ DEBUG }}}
            Logger::warn('Record TAN mismatch. Record has been changed.');

            return false;
        }

        // ----------------------------------------------------------------

        // Prepare for saving

        $nMode = 0;
        $nMode |= $Node->get('isUserReadable')    ? 00400 : 0;
        $nMode |= $Node->get('isUserWritable')    ? 00200 : 0;
        $nMode |= $Node->get('isUserExecutable')  ? 00100 : 0;

        $nMode |= $Node->get('isGroupReadable')   ? 00040 : 0;
        $nMode |= $Node->get('isGroupWritable')   ? 00020 : 0;
        $nMode |= $Node->get('isGroupExecutable') ? 00010 : 0;

        $nMode |= $Node->get('isWorldReadable')   ? 00004 : 0;
        $nMode |= $Node->get('isWorldWritable')   ? 00002 : 0;
        $nMode |= $Node->get('isWorldExecutable') ? 00001 : 0;

        // Prepare content summary. Leave spaces for search engines and
        // strip all tags.
        $sSummary = str_replace('>', '> ', $Node->get('content'));
        $sSummary = strip_tags($sSummary);
        $sSummary = trim(preg_replace('# +#', ' ', $sSummary));
        $sSummary = unescape($sSummary);

        // Gather data for insert/update
        $aFields = array(
            'rec_tan'      => $this->Storage->generateTan(),
            'rec_mod_id'   => $CurrUser->get('userId'),
            'rec_mod_ip'   => $this->Request->getRemoteAddr(),
            'user_id'      => $Node->get('userId'),
            'group_id'     => $Node->get('groupId'),

            'mode'         => sprintf('%04o', $nMode),

            'tree_id'      => $Node->get('treeId'),
            'parent_id'    => $Node->get('parentId'),
            'is_dir'       => $Node->get('isContainer') ? 'Y' :'',
            'is_index'     => $Node->get('isIndex') ? 'Y' : '',

            'notify_user'  => $Node->get('notifyUser') ? 'Y' : '',
            'notify_group' => $Node->get('notifyGroup') ? 'Y' : '',

            'name'         => $Node->get('name'),
            'wikiname'     => wikiWord($Node->get('name')),
            'metaphone'    => metaphone($Node->get('name')),
            'encoding'     => $Node->get('encoding'),
            'content'      => $Node->get('content'),
            'summary'      => $sSummary,
            'keywords'     => $Node->get('keywords'),
            'comments'     => $Node->get('isCommentable') ? 'Y' : '',
            'views'        => $Node->get('views')
        );

        // ----------------------------------------------------------------

        // Lookup host name
        if ($this->Registry->get('RUNTIME_LOOKUP_DNS')) {
            $aFields['rec_mod_host'] = $this->Request->getRemoteHost();
        }

        // ----------------------------------------------------------------

        // If this is an update
        if ($Node->get('id') != 0) {

            // {{{ DEBUG }}}
            Logger::info(
                'Preparing record update for node id #'.$Node->get('id')
            );

            if ($this->getNodeById($Node->get('id'))) {

                // Store only if changes has been made (if prepareBackup()
                // says, that this is necessary)
                if ($this->prepareBackup($Node, $aFields)) {

                    // Ok, some changes has been made, but do we need to
                    // increment the "revision"? Do not touch it if
                    // "minor change" has been chosen (if no backup is
                    // required)
                    if ($bDoBackup) {
                        $aFields['revision'] = (int)$Node->get('revision')+1;
                    }

                    // Set data for update, update only if the node still
                    // belongs to its parent - it may have been moved
                    // meanwhile
                    $aUpdate = array(
                        'table'  => $this->sNodeTable,
                        'fields' => $aFields,
                        'where'  => "node_id = '".$Node->get('id')."' AND "
                                    ."tree_id = '".$Node->get('treeId')."'"
                    );

                    // Execute update (terminates on error)
                    $this->Storage->update($aUpdate);

                    // Backup was prepared, store it now. No backup is done
                    // with containers (directories) here.
                    if ($bDoBackup && !$Node->get('isContainer')) {

                        // Store prepared backup data (if any) to history
                        $this->executeBackup($Node);
                    }

                } else {

                    // {{{ DEBUG }}}
                    Logger::warn(
                        'No backup has been performed.
                        No changes to the document?'
                    );
                }

            } else {

                $LastHistNode = $this->getHistNodeForId($Node->get('id'));

                // This is a recover insert: add required fields
                $aInitial = array(
                    'node_id'    => $Node->get('id'),
                    'tree_id'    => $Node->get('treeId'),
                    'rec_state'  => 'R',
                    'author_id'  => $LastHistNode->get('authorId'),
                    'revision'   => $LastHistNode->get('revision') + 1,
                    'created'    => $LastHistNode->get('created'),
                    'sort_order' => $this->getNextSortOrder($Node),
                    'views'      => $LastHistNode->get('views'),
                    'menu'       => $Node->get('menu'),
                    'foot'       => $Node->get('foot')
                );

                // Set further data for insert
                $aInsert = array(
                    'table'  => $this->sNodeTable,
                    'fields' => array_merge($aFields, $aInitial)
                );

                // Execute insert
                $this->Storage->insert($aInsert);
            }

        } else {

            // {{{ DEBUG }}}
            Logger::info('Preparing initial record insertion.');

            // This is an initial insert: add required fields
            $aInitial = array(
                'rec_state'  => 'R',

                'author_id'  => $CurrUser->get('userId'),

                'revision'   => '1',
                'created'    => $this->Storage->getDateTimeAsString(time()),
                'sort_order' => $this->getNextSortOrder($Node)
            );

            // Set further data for insert
            $aInsert = array(
                'table'  => $this->sNodeTable,
                'fields' => array_merge($aFields, $aInitial)
            );

            // Execute insert
            $this->Storage->insert($aInsert);

            // Set id of this node
            $Node->set(
                'id',
                $this->Storage->getLastInsertId($this->sNodeTable)
            );

            // --------------------------------------------------------

            // Update tree id, if it was not given (e.g. when creating
            // new web directory tree)
            if ($Node->get('treeId') == 0) {
                // Gather data for update
                $aFields = array(
                    'tree_id' => $Node->get('id')
                );

                $aUpdate = array(
                    'table'  => $this->sNodeTable,
                    'fields' => $aFields,
                    'where'  => "node_id = '".$Node->get('id')."' AND "
                                ."tree_id = '".$Node->get('treeId')."'"
                );

                // Execute update
                $this->Storage->update($aUpdate);

                // Set tree id in this node, too
                $Node->set('treeId', $Node->get('id'));
            }
        }

        // ----------------------------------------------------------------

        $this->updateParentOf($Node);

        // Store node ids this node is referencing to
        $this->storeNodeReferences($Node);

        // Tell the observers that something has changed
        $this->notifyObservers();

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Update record tan (update time) of the parent node (if any)
     *
     * @access  protected
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function updateParentOf($Node) {

        // {{{ DEBUG }}}
        Logger::info('Updating the parent of node id #'.$Node->get('id'));

        // Gather data for insert/update
        $aFields = array(
            'rec_tan' => $this->Storage->generateTan()
        );

        // Set data for update
        $aUpdate = array(
            'table'  => $this->sNodeTable,
            'fields' => $aFields,
            'where'  => "node_id = '".$Node->get('parentId')."'"
        );

        // Execute update
        $this->Storage->update($aUpdate);
    }

    // --------------------------------------------------------------------

    /**
     * Get name dupes count
     *
     * @access  protected
     * @param   object
     * @return  integer
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function getNameDupesCount($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Checking for duplicate names of node id #'.$Node->get('id')
        );

        // Modify query depending on if we are working with a web root
        // or with a simple document/directory
        if ($Node->get('isWeb')) {
            $sCond = "parent_id = '0'";
        } else {
            $sCond = "tree_id = '".((int)$Node->get('treeId'))."'";
        }

        $sQuery = " SELECT  count(node_id) AS rec_count
                      FROM  ".$this->sNodeTable."
                     WHERE  ".$sCond."
                       AND  name = '".addslashes($Node->get('name'))."'
                       AND  node_id <> '".((int)$Node->get('id'))."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        return (int)$aData['rec_count'];
    }

    // --------------------------------------------------------------------

    /**
     * Check if a document has been changed, do not prepare backup for
     * further storage in executeBackup() if no changes has been detected.
     *
     * @access  protected
     * @param   object
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function prepareBackup($Node, $aFields = null) {

        // {{{ DEBUG }}}
        Logger::info('Preparing backup of node id #'.$Node->get('id'));

        // Init defaults - with different values for error cases
        $sOld = true;
        $sNew = false;

        $aKeys = array(
            'tree_id', 'parent_id', 'name', 'keywords', 'content', 'comments',
            'user_id', 'group_id', 'mode', 'notify_user', 'notify_group'
        );

        // Query database
        $sQuery = " SELECT  *
                      FROM  ".$this->sNodeTable."
                     WHERE  node_id = '".$Node->get('id')."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aBackup = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        // ---

        // Get digests of new record contents
        if ($aBackup) {
            $sOld = '';
            foreach ($aKeys as $sKey) {
                $sOld .= $aBackup[$sKey];
            }
            $sOld = md5($sOld);
        }

        // ---

        // Get digests of new record contents
        if ($aFields) {
            $sNew = '';
            foreach ($aKeys as $sKey) {
                $sNew .= $aFields[$sKey];
            }
            $sNew = md5($sNew);
        }

        // ---

        if ($sNew == $sOld) {

            // No backup is required, reset $this->aBackup
            $this->aBackup = array();
            return false;

        } else {

            // Backup is required, assign $this->aBackup
            $this->aBackup = $aBackup;
            return true;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Execute backup
     *
     * @access  protected
     * @param   object
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function executeBackup($Node) {

          if (sizeof($this->aBackup) == 0) {
              return false;
          }

          $aInsert = array(
              'table'  => $this->sNodeHistTable,
              'fields' => $this->aBackup
          );

          // Store backup
          $this->Storage->insert($aInsert);
    }

    // --------------------------------------------------------------------

    /**
     * Store references to documents
     *
     * @access  protected
     * @param   object
     * @return  boolean
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [FIX]   TEMPORARILY DISABLED
     */
    protected function storeNodeReferences($Node) {

        return true; // FIX: TEMPORARILY DISABLED

        // Delete all references to this node
        $aDelete = array(
            'table' => $this->sNodeRefTable,
            'where' => "node_id = '".$Node->get('id')."'"
        );

        // Execute delete (remove)
        $this->Storage->remove($aDelete);

        // Gather and store references
        $It = $Node->getReferencedNodes()->iterator();

        while ($Obj = $It->next()) {

            $aFields = array(
                'node_id'     => $Node->get('id'),
                'ref_node_id' => $Obj->get('id')
            );

            // Set further data for insert
            $aInsert = array(
                'table'  => $this->sNodeRefTable,
                'fields' => $aFields
            );

            // Execute insert
            $this->Storage->insert($aInsert);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Store content only
     *
     * @access  public
     * @param   object
     * @return  void
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function storeContentOnly($Node) {

        // {{{ DEBUG }}}
        Logger::info(
            'Storing content only for id #'.$Node->get('id').'. Other
            fields won\'t be changed.'
        );

        // Gather data for insert/update
        $aFields = array(
            'content' => $Node->get('content')
        );

        // Set data for update
        $aUpdate = array(
            'table'  => $this->sNodeTable,
            'fields' => $aFields,
            'where'  => "node_id = '".$Node->get('id')."'"
        );

        // Execute update
        $this->Storage->update($aUpdate);
    }

    // --------------------------------------------------------------------

    /**
     * Store with lazy children
     *
     * @access  public
     * @param   object
     * @param   boolean
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function storeWithLazyChildren($Node, $bSaveParent = true) {

        // If no real parent "root" is given, it can not be saved
        if ($bSaveParent) {
            // Save the parent node first
            if (!$this->store($Node)) {
                return false;
            }
        }

        // Iterate through children
        $It = $Node->getItems()->iterator();

        while ($Obj = $It->next()) {

            // Gather data for update
            $aFields = array(
                'menu'       => $Obj->get('isInMenu')   ? 'Y' : '',
                'foot'       => $Obj->get('isInFooter') ? 'Y' : '',
                'is_index'   => $Obj->get('isIndex')    ? 'Y' : '',
                'sort_order' => $Obj->get('sortOrder')
            );

            // Set data for update
            $aUpdate = array(
                'table'  => $this->sNodeTable,
                'fields' => $aFields,
                'where'  => "node_id = '".$Obj->get('id')."'"
            );

            // Execute update
            $this->Storage->update($aUpdate);
        }

        // Tell the observers that something has changed
        $this->notifyObservers();

        return $this->Context->hasNoErrors();
    }

    // --------------------------------------------------------------------

    /**
     * Remove
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    public function remove($Node) {

        // {{{ DEBUG }}}
        Logger::info('Started removing node id #'.$Node->get('id'));

        if (!$Node->isWritable()) {
            $this->Context->addError(403);

            // {{{ DEBUG }}}
            Logger::warn(
                'Node id #'.$Node->get('id').' can not be removed. It\'s
                not writable. Aborting.'
            );

            return false;
        }

        // ----------------------------------------------------------------

        // Special treatment if "this" is a directory
        if ($Node->get('isContainer')) {

           // isEditable() does not care about world-access bits
           if (!$Node->isEditable()) {
                $this->Context->addError(403);

                // {{{ DEBUG }}}
                Logger::warn(
                    'Can not remove directory node id #'.$Node->get('id').'.
                    It is not editable. Aborting.'
                );

                return false;
            }

            // Get all children of this directory node
            $TmpNode = $this->getNodeById($Node->get('id'));
            $Node = $this->getAllChildren($TmpNode);

            // Do not delete tree if it has children
            if (!$Node->getItems()->isEmpty()) {
                $this->Context->addError(431);           // Not empty

                // {{{ DEBUG }}}
                Logger::warn(
                    'Can not remove directory node id #'.$Node->get('id').'.
                    Directory is not empty. Aborting.'
                );

                return false;
            }
        }

        // ----------------------------------------------------------------

        // Backup preparation
        $this->prepareBackup($Node);

        // Collect data to delete the node
        $aDelete = array(
            'table' => $this->sNodeTable,
            'where' => "node_id = '".$Node->get('id')."'"
        );

        // Execute delete (remove)
        $this->Storage->remove($aDelete);

        // Update record tan (update time) of the parent node (if any)
        $this->updateParentOf($Node);

        // Store prepared backup data (if any) to history
        $this->executeBackup($Node);

        // Tell the observers that something has changed
        $this->notifyObservers();

        return $this->Context->hasNoErrors();
    }

    // --------------------------------------------------------------------

    /**
     * Recover
     *
     * @access  public
     * @param   object
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$HistNode"
     */
    public function recover($HistNode) {

        // {{{ DEBUG }}}
        Logger::info('Started recovering node id #'.$HistNode->get('id'));

        if (!$HistNode->isRecoverable()) {
            $this->Context->addError(432);
            return false;
        }

        $OrigNode = $HistNode->get('original');
        if ($OrigNode) {
            $aData            = array();
            $aData['name']    = $HistNode->get('name');
            $aData['content'] = $HistNode->get('content');

            $this->invokePropertyMutator($OrigNode, $aData);

            if (!$this->store($OrigNode, true)) {
                return false;
            }
        } else {
            if (!$this->store($HistNode, false)) {
                return false;
            }
        }

        return true;
    }

    // === HELPER =========================================================

    /**
     * Generic node object creator
     *
     * @access  protected
     * @param   array
     * @return  mixed
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     */
    protected function createNode($aData) {

        // No data?
        if (!$aData) {
            return null;
        }

        // Generate a node object
        if ($aData['is_dir'] == 'Y') {
            $Node = new DocumentContainer();
        } else {
            $Node = new DocumentItem();
        }

        $this->invokePropertyMutator($Node, $aData);

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get next sort order
     *
     * @access  protected
     * @param   object
     * @return  integer
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.0
     *
     * @todo    [D11N]  Check description
     * @todo    [D11N]  Check the parameter type of "$Node"
     * @todo    [D11N]  Check return type
     */
    protected function getNextSortOrder($Node) {
        // Default sort order
        $nSortOrder = 10;

        // Get max sort order of the nodes which belong to the
        // same parent
        $nParentId = $Node->get('parentId');

        // Get new "sort_order" value
        $sQuery = " SELECT  max(sort_order) AS sort_order
                      FROM  ".$this->sNodeTable."
                     WHERE  parent_id = '".$nParentId."'";

        // {{{ DEBUG }}}
        Logger::sql($sQuery);

        $rResult = $this->Storage->query($sQuery);
        $aData   = $this->Storage->fetchArray($rResult);
                   $this->Storage->freeResult($rResult);

        if ($aData) {
            $nSortOrder = $aData['sort_order'] + 10;
        }

        return $nSortOrder;
    }

    // --------------------------------------------------------------------

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
     * @todo    [D11N]  Check the parameter type of "$Node"
     */
    protected function invokePropertyMutator($Node, $aData) {

        // --- Internal document data -------------------------------------

        if (isset($aData['rec_tan'])) {
            $Node->set('recTan', $aData['rec_tan']);
            $Node->set('modified', (int)$aData['rec_tan']);
        }

        if (isset($aData['rec_mod_id'])) {
            $Node->set('modifiedByUid', (int)$aData['rec_mod_id']);
        }

        if (isset($aData['rec_mod_ip'])) {
            $Node->set('remoteAddr', $aData['rec_mod_ip']);
        }

        if (isset($aData['rec_mod_host'])) {
            $Node->set('remoteHost', $aData['rec_mod_host']);
        }

        if (isset($aData['rec_state'])) {
            $Node->set('recState', $aData['rec_state']);
        }

        if (isset($aData['node_id'])) {
            $Node->set('id', (int)$aData['node_id']);
        }

        if (isset($aData['node_hist_id'])) {
            $Node->set('histId', (int)$aData['node_hist_id']);
        }

        if (isset($aData['tree_id'])) {
            $Node->set('treeId', (int)$aData['tree_id']);
        }

        if (isset($aData['parent_id'])) {
            $Node->set('parentId', (int)$aData['parent_id']);

            if ($Node->get('parentId') == 0) {
                $Node->set('isWeb', true);
            }
        }

        if (isset($aData['is_index'])) {
            $Node->set('isIndex', $aData['is_index'] == 'Y');
        }

        // --- User/Group -------------------------------------------------

        if (isset($aData['user_id'])) {
            $Node->set('userId', (int)$aData['user_id']);
        }

        if (isset($aData['group_id'])) {
            $Node->set('groupId', (int)$aData['group_id']);
        }

        // --- Access bits ------------------------------------------------

        if (isset($aData['mode'])) {
            $nMode = octdec($aData['mode']);

            $Node->set('isUserReadable',    ($nMode & 00400) == true);
            $Node->set('isUserWritable',    ($nMode & 00200) == true);
            $Node->set('isUserExecutable',  ($nMode & 00100) == true);

            $Node->set('isGroupReadable',   ($nMode & 00040) == true);
            $Node->set('isGroupWritable',   ($nMode & 00020) == true);
            $Node->set('isGroupExecutable', ($nMode & 00010) == true);

            $Node->set('isWorldReadable',   ($nMode & 00004) == true);
            $Node->set('isWorldWritable',   ($nMode & 00002) == true);
            $Node->set('isWorldExecutable', ($nMode & 00001) == true);
        }

        // --- Creation and revision --------------------------------------

        if (isset($aData['created'])) {
            $Node->set('created', (int)$aData['created']);
        }

        if (isset($aData['author_id'])) {
            $Node->set('authorId', (int)$aData['author_id']);
        }

        if (isset($aData['revision'])) {
            $Node->set('revision', (int)$aData['revision']);
        }

        // --- Payload ----------------------------------------------------

        if (isset($aData['name'])) {
            $Node->set('name', $aData['name']);
        }

        if (isset($aData['wikiname'])) {
            $Node->set('wikiName', $aData['wikiname']);
        }

        if (isset($aData['metaphone'])) {
            $Node->set('metaphone', $aData['metaphone']);
        }

        if (isset($aData['encoding'])) {
            $Node->set('encoding', $aData['encoding']);
        }

        if (isset($aData['content'])) {
            $Node->set('content', $aData['content']);
        }

        if (isset($aData['summary'])) {
            $Node->set('summary', $aData['summary']);
        }

        if (isset($aData['keywords'])) {
            $Node->set('keywords', $aData['keywords']);
        }

        if (isset($aData['comments'])) {
            $Node->set('isCommentable', $aData['comments'] == 'Y');
        }

        // --- Notification flags -----------------------------------------

        if (isset($aData['notify_user'])) {
            $Node->set('notifyUser', $aData['notify_user'] == 'Y');
        }

        if (isset($aData['notify_group'])) {
            $Node->set('notifyGroup', $aData['notify_group'] == 'Y');
        }

        // --- Supplement data --------------------------------------------

        if (isset($aData['menu'])) {
            $Node->set('isInMenu', $aData['menu'] == 'Y');
        }

        if (isset($aData['foot'])) {
            $Node->set('isInFooter', $aData['foot'] == 'Y');
        }

        if (isset($aData['views'])) {
            $Node->set('views', (int)$aData['views']);
        }

        if (isset($aData['sort_order'])) {
            $Node->set('sortOrder', (int)$aData['sort_order']);
        }

        if (isset($aData['next_node_id'])) {
            $Node->set('nextNodeId', (int)$aData['next_node_id']);
        }

        if (isset($aData['meta'])) {
            $Node->set('meta', $aData['meta']);
        }

    } // of invokePropertyMutator()

} // of class

/*
    Sunday morning I'm waking up
    Can't even focus on a coffee cup
    Don't even know who's bed I'm in
    Where do I start?
    Where do I begin?
*/

?>
