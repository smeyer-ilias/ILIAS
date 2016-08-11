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
 * @author Jan Rocho <jan.rocho@fh-dortmund.de>
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
 
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_WebService.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLElement.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLExceptions.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/exceptions/duep_Exceptions.php');

class DocumentService extends WebService {
    
    public function __construct () {
        parent::__construct ('DocumentService');
    }//Constructor
    
    /**
     * Create a new DuEPublico document with the validated parameters
     * <code>$docParams</code>, grant access for the user wo created 
     * the document and load Java applet to upload files.
     * @param array $docParams The attributes of the DuEPublico document
     * @return string The unique identifier of the newly created document.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateDocument ($docParams) {
        $xml = $this->PerformRequest ('createDocument', array ('w3doc' => array ('content' => $this->CreateRequest ($docParams), 'type' => 'apachesoap:Document')));
        return $xml->GetElementText ();
    }//CreateDocument
    
    
    /**
     * Update an existing DuEPublico document with the validated parameters
     * <code>$docParams</code>, grant access for the user wo created 
     * the document and load Java applet to upload files.
     * @param int $documentId The identifier of the document that should be updated.
     * @param array $docParams The attributes of the DuEPublico document
     * @return string The unique identifier of the newly created document.
     * @author Jan Rocho <jan.rocho@fh-dortmund.de>
     */
    public function UpdateDocument ($docParams, $documentId=FALSE) {
        //$xml = $this->PerformRequest ('updateDocument', array ('ID' => array ('content' => $documentId, 'type' => 'int'), 'w3doc' => array ('content' => $this->CreateRequest ($docParams), 'type' => 'apachesoap:Document')));
        $xml = $this->PerformRequest ('updateDocument', array ('w3doc' => array ('content' => $this->CreateRequest ($docParams,$documentId), 'type' => 'apachesoap:Document')),'no');
        //return $xml->GetElementText ();
        return true;
    }//CreateDocument


    
    /**
     * Deletes the DuEPublico document specified by the passed identifier
     * @param int $documentId The identifier of the document that should be deleted.
     * @return bool <c>True</c> if the document was removed successfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteDocument ($documentId) {
        return $this->PerformRequest ('deleteDocument', array ('ID' => array ('content' => $documentId, 'type' => 'int')), 'no');
    }//DeleteDocument
    
    /**
     * Checks if the document with the specific identifier 
     * exists on the DuEPublico server.
     * @param int $documentId The document's identifier.
     * @return bool <c>True</c> a document with the passed identifier exists; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistDocument ($documentId) {
        $xml = $this->PerformRequest ('documentExists', array ('ID' => array ('content' => $documentId, 'type' => 'int')));
        if ($xml->GetElementText () == 'true') {
            return true;
        } else {
            return false;
        }//else
    }//ExistDocument
    
    /**
     * Returns an <c>array</c> of languages that are available on the
     * DuEPublico server for the annotation of doucment metadata.
     * @param string $goalLanguage An identifier in which language the labels of the list should be returned. Possible values are German and English.
     * @return array An <c>array</c> that contains the labels and the abbriviations of all available languages.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetLanguages ($goalLanguage) {
        $xml = $this->PerformRequest('getLanguages', null);
        
        $return = array ();
        $code = '';
        $label = array ();
        $languages = $xml->GetChildXpath('/languages/language');
        foreach ($languages as $language) {
            $code  = $language->GetAttributeValue('biblcode');
            $label = $language->GetChild ('label');
            if ($label instanceof XMLElement) {
                $return[$code] = $label->GetElementText ();
            } else if (is_array($label)) {
                $lab = array ();
                //If there is a German translation, than it will be the last element
                //the first entry will be the English translation
                if ($goalLanguage == 'English') {
                    $lab = array_pop($label);
                } else if ($goalLanguage == 'German') {
                    $lab = array_shift ($label);
                } else {
                    throw new InvalidArgumentException ('Possible arguments for GetLanguages method in DocumentService are "German" and "English".');
                }//else
                $return[$code] = $lab->GetElementText ();
            }//elseif
        }//foreach
        return $return;
    }//GetLanguages
    
    /**
     * Returns the metadata of the document, specified by the
     * passed identifier.
     * @param int $documentId The document's identifier.
     * @return XmlElement The document's metadata in form of a <c>XmlElement</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetDocumentData ($documentId) {
        return $this->PerformRequest('retrieveDocumentDetailsForOutput', array ('id' => array ('content'=> $documentId, 'type'=> 'int')));
    }//GetDocumentData
    
    /**
     * Returns the smallest identifier a derivate contained in the the 
     * document, specified by the passed identifier.
     * @param integer $documentId The document's identifier.
     * @return integer The smallest identifier of the derivates contained in the document.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SmallestDerivateId ($documentId) {
        //Get document information from the webservice
        $xml = $this->GetDocumentData($documentId);
        
        //Parse the smallest derivate id out of the document
        $derivates = $xml->GetChildXpath('/document/derivates/derivate');
        if ($derivates instanceof XMLElement) {
            return $derivates->GetAttributeValue ('ID');
        } else if (is_array($derivates)) {
            $smallest = -1;
            foreach ($derivates as $derivate) {
                if ($smallest < 0) {
                    $smallest = $derivate->GetAttributeValue ('ID');
                } else if ($smallest > $derivate->GetAttributeValue ('ID')) {
                    $smallest = $derivate->GetAttributeValue ('ID');
                }//else if
            }//foreach
            return $smallest;
        }//else if
    }//SmallestDerivateId
        
     /**
     * Creates the XML structure that is required by the web service to create
     * a new document on the DuEPublico server.
     * @param array $requestParams The attributes of the newly created document
     * @return string The XML request for creating a new document
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     * @author Jan Rocho <jan.rocho@fh-dortmund.de>
     */    
    private function CreateRequest ($requestparams,$documentId=FALSE) {
        if($documentId == FALSE)
        {
            $docId = 0;
        }
        else
        {
            $docId = $documentId;
        }
        $ret = '<document id="'.$docId.'" status="published" collection="LuL">
                <title lang="'.$requestparams['language'].'">'.$this->MakeUnicode ($requestparams['title']).'</title>';
        if ($requestparams['title2'] != '') {
            $ret .= '<title lang="'.$requestparams['language'].'">'.$this->MakeUnicode ($requestparams['title2']).'</title>';
        }//if
        $ret .= '<contributor id="'.$requestparams['authorId'].'" role="'.$requestparams['role'].'" type="creator" />';
        $ret .= '<lang>'.$requestparams['language'].'</lang>';
            
        if ($requestparams['description'] != '') {
            $ret .= '<text type="description" lang="'.$requestparams['language'].'">'.$requestparams['description'].'</text>';
        }//if
        if ($requestparams['keyword'] != '') {
            $ret .= '<keywords>'.$this->MakeUnicode($requestparams['keyword']).'</keywords>';
        }//if
        if (($requestparams['erstelltDatum'] != '') and 
            ($requestparams['erstelltDatum'] != 'tt.mm.jjjj')){
            $ret .= '<date type="created" format="dd.MM.yyyy">'.$requestparams['erstelltDatum'].'</date>';
        }//if
        if (($requestparams['geaendertDatum'] != '') and 
            ($requestparams['geaendertDatum'] != 'tt.mm.jjjj')){
            $ret .= '<date type="modified" format="dd.MM.yyyy">'.$requestparams['geaendertDatum'].'</date>';
        }//if 
        if (($requestparams['gueltigVonDatum'] != '') and 
            ($requestparams['gueltigVonDatum'] != 'tt.mm.jjjj')){
            $ret .= '<date type="validFrom" format="dd.MM.yyyy">'.$requestparams['gueltigVonDatum'].'</date>';
        }//if 
        if (($requestparams['gueltigBisDatum'] != '') and 
            ($requestparams['gueltigBisDatum'] != 'tt.mm.jjjj')){
            $ret .= '<date type="validTo" format="dd.MM.yyyy">'.$requestparams['gueltigBisDatum'].'</date>';
        }//if   
        if ($requestparams['typeid'] != '') {  
            $ret .=  '<memberOf classif="TYPE"   categ="'.$requestparams['typeid'].'"/>';
        } else {
            $ret .=  '<memberOf classif="TYPE"   categ="'.$requestparams['typeid'].'"/>';
        }//else
        $ret .= '<memberOf classif="FORMAT" categ="'.$requestparams['formatid'].'"/>
                 <memberOf classif="ORIGIN" categ="'.$requestparams['originid'].'"/>
                 </document>';               
        return utf8_encode ($ret);
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
    

}//Class: DocumentService
?>
