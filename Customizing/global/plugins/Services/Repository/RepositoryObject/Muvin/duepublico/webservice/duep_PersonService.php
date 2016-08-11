<?php
/**
 * @copyright Copyright &copy; 2009, Marcel Heusinger
 * @version 1.0 
 * 
 * Created on 26.05.2009 by Marcel Heusinger (marcel.heusinger [at] uni-essen.de)       
 *                                                                               
 * This file is part of the DuEPublico to Moodle interface that was developed        
 * during the project 'Systemkonvergenz' (see project's website 
 * http://systemkonvergenz.de/) sponsored by the German Research Association (DFG). 
 * If you have any suggestions, comments, or questions do not hesitate to contact
 * the author at the below-mentioned email address.
 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
 * 
 * @abstract This class
 * 
 * 
 * 
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License
 * <para>
 *  This program is free software; you can use it, redistribute it
 *  and / or modify it under the terms of the GNU General Public License
 *  (GPL) as published by the Free Software Foundation; either version 2
 *  of the License or (at your option) any later version.
 * </para>
 * <para>
 *  This program is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * </para> 
 * <para>
 *  You should have received a copy of the GNU General Public License
 *  along with this program, see root folder.
 *  If not, write to the Free Software Foundation Inc.,
 *  59 Temple Place - Suite 330, Boston, MA  02111-1307 USA
 * </para>
 */

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_WebService.php');
require_once ($CFG->dirroot.'/lib/duepublico/XmlParser/duep_XMLElement.php');
require_once ($CFG->dirroot.'/lib/duepublico/XmlParser/duep_XMLExceptions.php');
require_once ($CFG->dirroot.'/lib/duepublico/exceptions/duep_Exceptions.php');

class PersonService extends WebService {
    
    /**
     * Creates a new instance of the PersonService web service client
     * implementation.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct () {
        parent::__construct ('PersonService');
    }//Constructor
    
    /**
     * Checks if the legal entity specified by the passed id exsits.
     * @param int $legalEntityId The unique identifier of the person.
     * @return bool <c>True</c> if a person with the specified unqiue 
     *              identifier exists; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsPerson ($legalEntityId) {
        $xml = $this->PerformRequest ('personExists', array('ID' => array ('content' => $legalEntityId, 'type' => 'int')));
        if ($xml->GetElementText () == 'true') {
            return true;
        } else {
            return false;
        }//else
    }//ExistsPerson
     
    /**
     * Deletes the legal entity specified by the passed identifier.
     * @param int $legalEntityId The unique identifier of the person.
     * @return bool <c>True</c> if legal entity specified by the passed identifier
     *               was deleted succesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeletePerson ($legalEntityId) {
        if ($this->ExistsPerson($legalEntityId)) {
            return $this->PerformRequest ('deletePerson', array('ID' => array ('content' => $legalEntityId, 'type' => 'int')), 'no');
        } else {
            return false;
        }//else
    }//DeletePerson 
     
    /**
     * Returns an <c>array</c> with the following information about
     * the legal entity, specified by the passed <c>$legalEntityId</c>:
     * <ul>
     *  <li>array['name']: The name of the legal entity.</li>
     *  <li>array['id']: The unique identifier of the legal entity.</li>
     *  <li>array['origin']: The DuEPublico origin identifier.</li>
     * <ul>
     * @param integer $legalEntityId The unique legal entity identifier
     * @return array An <c>array</c> that contains information described above.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function PersonInfo($legalEntityId) {
        if ($this->ExistsPerson($legalEntityId)) {
            $xml = $this->PerformRequest ('retrievePerson', array('ID' => array ('content' => $legalEntityId, 'type' => 'int')));
            $legalEntity = $xml->GetChild('legalEntity');
            
            try {$ret['name'] = $legalEntity->GetChild('name')->GetElementText();} catch (Exception $e) {$ret['name'] = '';}
            try {$ret['id'] = $legalEntity->GetAttributeValue ('id');} catch (Exception $e) {$ret['id'] = '';}
            try {$ret['pid'] = $legalEntity->GetAttributeValue ('pid');} catch (Exception $e) {$ret['pid'] = '';}
//            To add the origin id, it must be translated but that means to parse the abbreviation out of the total list, that takes time
//            try {$ret['origin'] = $legalEntity->GetChild('origin')->GetElementText();} catch (Exception $e) {$ret['origin'] = '';}
            try {$ret['phone'] = $legalEntity->GetChildXpath('/contact/phone')->GetElementText();} catch (Exception $e) {$ret['phone'] = '';}
            try {$ret['email'] = $legalEntity->GetChildXpath('/contact/email')->GetElementText();} catch (Exception $e) {$ret['email'] = '';}
            return $ret;
        } else {
            throw new PersonNotExistsException ($legalEntityId);
        }//else
    }//personInfo
    
    /**
     * Createa a new legal entity out of the passed parameters.
     * The passed <c>array</c> must
     * have following structure to be processable:
     * <ul>
     *  <li>$parameters['academictitle']: Academic title of the legal entity.</li>
     *  <li>$parameters['name']: Name of the legal entity.</li>
     *  <li>$parameters['originid']: One of the DuEPublico origin ids.</li>
     *  <li>$parameters['publishContact']: .</li>
     *  <li>$parameters['contactType']: .</li>
     *  <li>$parameters['institution']: The institution the legal entity is working at.</li>
     *  <li>$parameters['address']: The address of the legal entity.</li>
     *  <li>$parameters['phone']: The phone number of the legal entity.</li>
     *  <li>$parameters['fax']: The fax number of the legal entity.</li>
     *  <li>$parameters['email']: The email id if the legal entity.</li>
     *  <li>$parameters['homepage']: The homepage of the legal entity.</li>
     *  <li>$parameters['contactComment']: A comment about the contact details.</li>
     *  <li>$parameters['personComment']: A comment about the legal entity itself.</li>
     *  <li>$parameters['gebPlace']: the birthplace in form of a <c>string</c>.</li>
     *  <li>$parameters['gebDate']: the birth date in the following format "dd.mm.yyyy".</li>
     * </ul>
     * @param array $personParams An <c>array</c> that contains the information as described above.
     * @return int The unique identifier of the created legal entity.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreatePerson ($personParams) {
        $xml = $this->PerformRequest ('createPerson', array ('w3doc' => array ('content' => $this->CreateRequest($personParams), 'type' => 'apachesoap:Document')));
        return $xml->GetElementText ();
    }//CreatePerson
    
    /**
     * Creates an url encoded Xml <c>string</c> that is required for the
     * creation of a DuEPublico legal entity. The passed <c>array</c> must
     * have following structure to be processable:
     * <ul>
     *  <li>$parameters['academictitle']: Academic title of the legal entity.</li>
     *  <li>$parameters['name']: Name of the legal entity.</li>
     *  <li>$parameters['originid']: One of the DuEPublico origin ids.</li>
     *  <li>$parameters['publishContact']: .</li>
     *  <li>$parameters['contactType']: .</li>
     *  <li>$parameters['institution']: The institution the legal entity is working at.</li>
     *  <li>$parameters['address']: The address of the legal entity.</li>
     *  <li>$parameters['phone']: The phone number of the legal entity.</li>
     *  <li>$parameters['fax']: The fax number of the legal entity.</li>
     *  <li>$parameters['email']: The email id if the legal entity.</li>
     *  <li>$parameters['homepage']: The homepage of the legal entity.</li>
     *  <li>$parameters['contactComment']: A comment about the contact details.</li>
     *  <li>$parameters['personComment']: A comment about the legal entity itself.</li>
     *  <li>$parameters['gebPlace']: the birthplace in form of a <c>string</c>.</li>
     *  <li>$parameters['gebDate']: the birth date in the following format "dd.mm.yyyy".</li>
     * </ul>
     * @param array $parameters The parameters required for creating a legal entity.
     * @return string A url encoded Xml <c>string</c> that is processed by the cretePerson method of the PersonService web service.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreateRequest ($parameters) {
        $ret= '<legalEntity id="0" type="person">';
        if (($parameters['gebPlace'] != '') and
            ($parameters['gebDate']  != '')     ) {
               $ret .= '<born>
                           <place>'.$this->MakeUnicode ($parameters['gebPlace']).'</place>
                           <date format="dd.MM.yyyy">'.$parameters['gebDate'].'</date>
                       </born>';
        } //if
        $ret .= '<title>'.$this->MakeUnicode ($parameters['academictitle']).'</title> 
                 <name>'.$this->MakeUnicode ($parameters['name']).', '.$this->MakeUnicode ($parameters['firstname']).'</name>
                 <origin>'.$parameters['originid'].'</origin>
                 <contact publish="'.$parameters['publishContact'].'" type="'.$parameters['contactType'].'">
                      <institution>'.$this->MakeUnicode ($parameters['institution']).'</institution>
                      <address>'.$this->MakeUnicode ($parameters['address']).'</address>
                      <phone>'.$parameters['phone'].'</phone>
                      <fax>'.$parameters['fax'].'</fax>
                      <email>'.$parameters['email'].'</email>
                      <url>'.$parameters['homepage'].'</url>
                      <comment>'.$this->MakeUnicode ($parameters['contactComment']).'</comment>
                 </contact>
                 <comment>'.$this->MakeUnicode ($parameters['personComment']).'</comment>
                </legalEntity>';   
        return $ret;
    }//CreateRequest

    /**
     * Magic method used for debugging purposes. It makes debugging
     * more convienient because its called if another class tries to 
     * call a method on an instance of this class but that method is 
     * not defined within this class.
     * @param string $method The name of the method that was called.
     * @param mixed $parameters The parameters that should be passed to the called method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __call($method, $parameters) {
        throw new Exception ("Method $method is not implemented within ".__CLASS__.".");
    }//__call
    
}//Class: PersonService
?>
