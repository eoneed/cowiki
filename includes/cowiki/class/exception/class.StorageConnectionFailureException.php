<?php

/*
    Das vorliegende Programm und sein Quellcode sind durch das Gesetz ueber
    Urheberrecht und verwandte Schutzrechte (Urheberrechtsgesetz UrhG)  der
    Bundesrepublik Deutschland zugunsten von Daniel T. Gorski,  geschuetzt.

    Jede voruebergehende oder dauerhafte Vervielfaeltigung  der verwendeten
    Algorithmen, des kompletten Programmes,  oder Teilen hiervon sowie jede
    Uebersetzung, Bearbeitung, Umarbeitung, Verarbeitung oder sonstige Ver-
    wendung des Programms fuer andere Zwecke als dessen Anwendung im Rahmen
    der  von  Daniel T. Gorski geschaffenen  und  programmierten  Anwendung
    bedarf der vorherigen  schriftlichen  Zustimmung von  Daniel T. Gorski.

    Temporary or permanent  copying,  duplication  or  distribution of used
    algorithms,  the complete code or parts of it as well  as  translation,
    adaptation, conversion  or any re-engineering or similar utilisation of
    the program for an other purpose than the software application provided
    by Daniel T. Gorski is  not permitted without a positive affirmation of
    Daniel T. Gorski in written form.

    (C) Daniel T. Gorski <daniel.gorski@develnet.org> - All rights reserved

    $Id: class.StorageConnectionFailureException.php 19 2011-01-04 03:52:35Z eoneed $
*/

 /**
 * "Storage connection failure" exception.
 *
 * @package     exception
 * @access      public
 *
 * @author      Daniel T. Gorski, <daniel.gorski@develnet.org>
 * @since       coWiki 0.4.0
 */
class StorageConnectionFailureException extends StorageException {

    /**
     * Class constructor
     *
     * @access  public
     * @param   string  Message for the exception to clarify the cause
     *                  (optional).
     *
     * @author  Daniel T. Gorski, <daniel.gorski@develnet.org>
     */
    public function __construct($sMsg = null) {
        parent::__construct($sMsg);
        $this->setName(__CLASS__);
    }

} // of class

?>