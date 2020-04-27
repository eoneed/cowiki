<?php

/**
 *
 * $Id: class.XmlObjectGraph.php 27 2011-01-09 12:37:59Z eoneed $
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
 * @version     $Revision: 27 $
 *
 */

/**
 * Parse an XML document into an object graph representation using an event
 * based parser. The resulting tree can be easily traversed and searched.
 *
 * @package     parse
 * @subpackage  class
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.3.1
 *
 * @todo        FIX: real CDATA handling
 */
class XmlObjectGraph extends XmlEventParser {

    protected
        $Node = null;

    // --------------------------------------------------------------------

    /**
     * The class constructor initializes its element event handlers
     * (start-, end-, character- and default handler).
     *
     * @access  public
     * @param   string  The encoding definition (optional). The default
     *                  encoding is UTF-8.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function __construct($sEnc = 'UTF-8') {
        parent::__construct($sEnc);
    }

    // --------------------------------------------------------------------

    /**
     * Parse a given XML document into an object graph representation.
     *
     * @access  public
     * @param   string  The wellformed(!) XML string.
     * @return  array   The parsed XML structure as an nested array.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function parse(&$sStr) {
        $aItems = parent::parse($sStr);

        $Root = new XmlNode();
        $Graph = $this->createObjectGraph($Root, $aItems);

        return $Graph;
    }

    // --------------------------------------------------------------------

    /**
     * This helper method creates a composite object tree.
     *
     * @access  protected
     * @param   object  The composite container node.
     * @return  array   Array of children items.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    protected function createObjectGraph($Node, $aItems) {

        $NewNode = new XmlNode();
        $NewNode->set(Xml::ELM_NAME, $aItems[Xml::ELM_NAME]);

        // Any attributes?
        if (isset($aItems[Xml::ELM_ATTR])) {
            $NewNode->set(Xml::ELM_ATTR, $aItems[Xml::ELM_ATTR]);
        }

        // Set character data
        if (isset($aItems[Xml::ELM_CDATA])) {
            $NewNode->set(Xml::ELM_CDATA, $aItems[Xml::ELM_CDATA]);
        } else {
            $NewNode->set(Xml::ELM_CDATA, null);
        }

        // Any children?
        if (isset($aItems[Xml::ELM_INDEX])) {
            for ($i=0, $n=sizeof($aItems[Xml::ELM_INDEX]); $i<$n; $i++) {
                $this->createObjectGraph($NewNode, $aItems[Xml::ELM_INDEX][$i]);
            }
        }

        $Node->addItem($NewNode);
        $this->Node = $Node;

        return $Node;
    }

    // --------------------------------------------------------------------

    /**
     * Get an element by its id attribute. The search for the elements
     * starts always at the root of the XML document, hence you will find
     * the first appearance of the id only.
     *
     * @access  public
     * @param   string  Id of the XML element you are looking for.
     *                  The search is casesensitve.
     * @return  object  The XmlNode that matches the id.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getElementById($sId) {
        return $this->getElementByIdRecusive($this->Node, $sId);
    }

    // --------------------------------------------------------------------

    /**
     * Lookup elements recursive, traverse the tree. This method is a
     * helper for getElementById().
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @see     getElementById
     */
    protected function getElementByIdRecusive($Node, $sId) {
        static $Found = null;

        $It = $Node->getItems()->iterator();
        while ($Obj = $It->next()) {

            // Do we have attributes in this node? Did we found what we are
            // looking for?
            $aAttr = $Obj->get(Xml::ELM_ATTR);

            if (isset($aAttr['id']) && $aAttr['id'] == $sId) {
                $Found = $Obj;
                break;
            }

            // Recursion?
            if ($Obj->hasItems()) {
                if ($this->getElementByIdRecusive($Obj, $sId)) {
                    break;
                }
            }
        }

        return $Found;
    }

    // --------------------------------------------------------------------

    /**
     * Returns a Vector of all the elements with a given tag name in the
     * order in which they are encountered in a preorder traversal of the
     * composite graph. The search for the elements starts always at the
     * root of the XML document, hence you will find all appearances of
     * the element.
     *
     * @access  public
     * @param   string  Tag name of the XML element you are looking for.
     *                  The search is casesensitve.
     * @return  object  Vector containing all matching XmlNodes.
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function getElementsByTagName($sTag) {
        $Vector = new Vector();
        return $this->getElementsByTagNameRecusive(
                    $Vector,
                    $this->Node,
                    $sTag
               );
    }

    // --------------------------------------------------------------------

    /**
     * Lookup elements recursive, traverse the tree. This method is a
     * helper for getElementsByTagName().
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @see     getElementsByTagName
     */
    protected function getElementsByTagNameRecusive($Vector, $Node, $sTag) {

        $It = $Node->getItems()->iterator();
        while ($Obj = $It->next()) {

            // Do we have the name what we are looking for?
            if ($Obj->get(Xml::ELM_NAME) == $sTag) {
                $Vector->add($Obj);
            }

            // Recursion?
            if ($Obj->hasItems()) {
                $this->getElementsByTagNameRecusive($Vector, $Obj, $sTag);
            }
        }

        return $Vector;
    }

    // --------------------------------------------------------------------

    /**
     * Return the XML representation (ASCII) of the the object graph.
     *
     * @access  public
     * @param   The XmlNode you want to be the topmost element (optional)
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     *
     * @see     getElementById
     */
    public function getXml($Node = null) {
        if ($Node) {
            return $this->createXml($Node);
        } else {
            return $this->createXml($this->Node);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Store the XML graph as ASCII file.
     *
     * @access  public
     * @param   string  File name (with leading path)
     * @return  boolean true if successful, false otherwise
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    public function store($sFile) {
        $sStr = $this->getXmlDefinition()
                .ltrim($this->createXml($this->Node));

        try {
            $Out = new FileOutputStream($sFile);
            $Out->write($sStr);
            $Out->close();
        } catch (Exception $e) {
            // Swallow exception
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * (Re)Build an ASCII representation of the object graph
     *
     * @access  protected
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     * @since   coWiki 0.3.1
     */
    protected function createXml($Node) {
        static
            $sStr = '',
            $nLevel = 0;

        $It = $Node->getItems()->iterator();
        while ($Obj = $It->next()) {

            $sAttr = '';

            // Assemble attributes
            if ($Obj->get(Xml::ELM_ATTR)) {
                foreach ($Obj->get(Xml::ELM_ATTR) as $k => $v)  {
                    $sAttr .= ' '.$k.'="'.$v.'"';
                }
            }

            // Assemble opening tag element
            $sLine = "\n" . str_repeat('  ', $nLevel);
            $sLine .= '<'.$Obj->get(Xml::ELM_NAME).$sAttr;

            // Any children or character data or close tag directly?
            if ($Obj->hasItems() || $Obj->get(Xml::ELM_CDATA)) {
                $sLine .= '>';

                $bOneLine = false;

                if ($Obj->get(Xml::ELM_CDATA)) {

                    // Does it fit in one line?
                    $sData = htmlentities($Obj->get(Xml::ELM_CDATA));

                    if (!$Obj->hasItems() && strlen($sLine.$sData) <= 78) {
                        $sLine .= $sData;
                        $bOneLine = true;

                    } else {
                        $sLine .= "\n";
                        $sData = wordwrap(
                                    $Obj->get(Xml::ELM_CDATA),
                                    68 - $nLevel*2
                                 );
                        $aData = explode("\n", $sData);

                        for ($i=0, $n=sizeof($aData); $i<$n; $i++) {
                            $aData[$i] = str_repeat('  ', $nLevel)
                                         . '  ' . trim($aData[$i]);
                        }

                        $sLine .= htmlentities(join("\n", $aData));
                    }
                }
            } else {
                $sLine .= ' />';
            }

            $sStr .= $sLine;

            // Recursion?
            if ($Obj->hasItems()) {
                $nLevel++;
                $this->createXml($Obj);
                $nLevel--;
            }

            // Assemble closing tag element
            if ($Obj->hasItems() || $Obj->get(Xml::ELM_CDATA)) {
                if (!$bOneLine) {
                    $sStr .= "\n" . str_repeat('  ', $nLevel);
                    $sStr .= '</'.$Obj->get(Xml::ELM_NAME).'>';
                } else {
                    $sStr .= '</'.$Obj->get(Xml::ELM_NAME).'>';
                }
            }
        }

        return $sStr;
    }

} // of class

/*
    Karma police, arrest this man
    He talks in maths
    He buzzes like a fridge
    He's like a detuned radio

    Karma police, arrest this girl
    Her Hitler hairdo is
    Making me feel ill
    And we have crashed her party

    This is what you get
    This is what you get
    This is what you get when you mess with us
*/

?>
