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
 * @abstract This class is the implementation of 
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

class QueryService extends WebService { 
 
    private $paramsSearchQuery;
    private $searchFieldIdValue;
    
    /**
     * Creates a new instance of the QueryService web service 
     * client implementation out of the passed parameters:
     * <para>
     * The parameter <c>$wwwduepublico</c> is the url of the 
     * Miless/MyCoRe server whose web QueryService should be called.
     * </para>
     * <para>
     * The <c>$dirrot</c> parameter is the path to the root directory
     * of the Moodle instance.
     * </para>
     * <para>
     * The parameter <c>$searchFieldIdValue</c> is the index of the 
     * search fields this instance uses for querying the Miless/MyCore
     * instance this client is bound to. Possible values for the search 
     * field index are:
     * <ul>
     *  <li>ubo: This index contains metadata of Universitätsbibliographie Online (module-dozbib) </li>
     *  <li>scorm: This index contains scorm data (module-scorm)</li>
     *  <li>mildocument: This index contains metadata of documents</li>
     *  <li>milcontent: Search in MCRFile metadata and content</li>
     * </ul>
     * <para>
     * @param string $wwwduepublico The base url of the DuEPublico web services.
     * @param string $dirroot Path to the Moodle directory on the harddisk.
     * @param string $searchFieldIdValue The index identifier of the search fields 
     *                                   understood by the DuEPublico server. The default
     *                                   value is 'mildocument'. For other search field indices
     *                                   see the description above or the 
     *                                   {@link ParseSearchFields ParseSearchFields} method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct ($searchFieldIdValue = 'mildocument') {
        parent::__construct ('QueryService');
        $this->searchFieldIdValue = $searchFieldIdValue;
    }//Constructor
    
    
    public function GetSearchFields () {
        $xml = $this->PerformRequest('getSearchFields');
        
        if (($xml != null) and ($xml != '')) {
            return $this->ParseSearchFields($xml);
        } else {
            throw new WebServiceReturnException ('QueryService', 'GetSearchFields', 'Web service response is empty.');
        }//else    
    }//GetSearchFields
    
    public function GetFieldTypes () {
        $xml = $this->PerformRequest('getFieldTypes');
        if (($xml != null) and ($xml != '')) {
            return $xml->GetChildXpath('/fieldtypes/type');
        } else {
            throw new WebServiceReturnException ('QueryService', 'GetFieldTypes', 'Web service response is empty.');
        }//else
    }//GetFieldTypes
       
    /**
     * Calls the DuEPublico QueryService web services to find the Moodle course's document
     * If there is such a document then the id will be returned, otherwise -1
     * will be returned.
     * @param integer $courseId Id of the Moodle course
     * @return integer The document id of the course document or -1 if there is no such document
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetCourseDocumentId ($title) {
        $paramsSearchQuery = array ('title' => $title, 
                                    'operatortitle' => '=',
                                    'maxResults' => '10',
                                    'connPopup' => 'AND');
        $this->paramsSearchQuery = $paramsSearchQuery;
        
        $xml = $this->PerformRequest('doXMLQuery', array ('query' => array ('content' => $this->CreateDoXmlQueryRequest (), 'type' => 'apachesoap:Document')));
        
        try {
            if ($xml->GetChild('results')->GetAttributeValue('numHits') > 0) {
                $hits = $xml->GetChildXpath('/results/hit');
                if ($hits instanceof XMLElement) {
                    return $hits->GetAttributeValue('id');
                } else if (is_array($hits)){
                    foreach ($hits as $hit) {
                        return $hit->GetAttributeValue('id');
                    }//foreach
                }//else
            } else {
                return false;
            }//else
        } catch (ChildNotFoundException $e) {
//            throw new DocumentNotFoundException ($title);
        }//catch
        return false;
    }//GetCourseDocumentId
    
    /**
     * Calls the doRetrieveClassfication method an returns the classification
     * categories returned by the web service.Possible values that can be
     * passed to this method are:
     * <ul>
     *  <li>ANGLISTIK: Classification scheme of anglistics</li>
     *  <li>DDC: Dewey Decimal-Classification</li>
     *  <li>ELISE: ELiS_e number and volume</li>
     *  <li>FORMAT: classification of media types</li>
     *  <li>LINSE: LINSE classification</li>
     *  <li>ORIGIN: classification of faculties and organisations</li>
     *  <li>PACS: Physics and Astronomy Classification Scheme</li>
     *  <li>PHYSIK: Classification of physics</li>
     *  <li>TYPE: document type classification</li>
     *  <li>UNIKATE: research and teaching reports</li>
     *  <li>WIFORUM: forum of economics</li>
     * </ul>
     * @param string $classification One of the classifications <c>string</c>s described above.
     * @return XMLElement The <c>XMLElement</c> that contains the classification's entries.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DoRetrieveClassification ($classification) {
        $xml = $this->PerformRequest ('doRetrieveClassification', array ('id' => array ('content' => $classification, 'type' => 'string')));
        return $xml->GetChild('classification');
    }//DoRetrieveClassification
    
    /**
     * Uploads the file, specified by <c>$filename</c> to the
     * document specified by <c>$documentId</c>. The DuEPublico 
     * server will download the file from <c>$moodlePublish/$filename</c>. 
     * So <c>$moodlePublish</c> must be a server directory accessable from
     * the internet.
     * <example>
     *  $moodlePublish: www.myMoodleServer.org/public
     *  $filename: myFile.zip
     *  So the DuEPublico server will try to download the file from:
     *  http://www.myMoodleServer.org/public/myFile.zip
     * </example>
     * @param string $moodlePublish A Moodle server directory accessable from the internet.
     * @param string $filename The name of the file that should be transfered to the DuEPublico server.
     * @param string $uname The name of an account to which writting access on the document was granted.
     * @param string $pword The password of the user name to which writing access was granted.
     * @param int $documentId The unique identifier of the document in which a new derivate will be created that will contain the uploaded file.
     * @return bool <c>True</c> if the upload was succsesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    public function DoUpload ($moodlePublish, $filename, $uname, $pword, $documentId) {
        $request = $this->CreateDoUploadRequest($this->CreateUploadUri($moodlePublish, $filename), $uname, $pword, $documentId, $filename);
        $xml = $this->PerformRequest ('doUpload', array ('request' =>  array ('content' => $request, 'type' => 'apachesoap:Document')));
        
        if ($xml->GetChild('packageUpload')->GetAttributeValue ('success') == 'false') {
            return false;
        } else {
            return true;
        }//else
    }//DoUpload
    
    /**
     * Calls the lastModifiedInfo method and returns the 
     * web service's response in form of a <c>XmlElement</c>.
     * @return array The <c>XmlElement</c> that contains the time stamp when a DuEPublico method was updated.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckTimeStamps () {
        $ret = array ();
        $xml = $this->PerformRequest('getLastModifiedInfo', null);
        return $xml->GetChildXpath('/timestamps/timestamp');
    }//CheckTimeStamps
    
    /**
     * Calls the lastModifiedInfo method and returns an <c>array</c>
     * composed of method names as keys and time stamps as values. This
     * <c>array</c> is used to update stored content if neccessary.
     * @return array The <c>array</c> that contains the time stamp when a DuEPublico method was updated.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckTimeStampsAsArray () {
        $ret = array ();
        $xml = $this->CheckTimeStamps();
        if (is_array ($xml)) {
            foreach ($xml as $timestamp) {
                if ($timestamp instanceof XMLElement) {
                    $method = $timestamp->GetAttributeValue('method');
                    $content =  $timestamp->GetElementText ();
                    $ret[$method] = $content;
                } else {
                    throw new WebServiceReturnException ('QueryService', 'getLastModifiedInfo', 'Error while parsing  time stamps.');
                }//else
            }//foreach
        } else if ($xml instanceof XMLElement) {
            $method = $xml->GetAttributeValue('method');
            $content = $xml->GetElementText ();
            $ret[$method] = $content;
        } else {
            throw new WebServiceReturnException ('QueryService', 'getLastModifiedInfo', 'Error while parsing  time stamps.');
        }//else
        return $ret;
    }//CheckTimeStampsAsArray
    
    /**
     * Calls the doXmlQuery web service method and returns the result
     * element of the web service response.
     * The passed <c>array</c> {{@link $searchQuery $searchQuery} must have 
     * the following keys and values (the optional key-value-pairs are marked
     * with an asterisk [*]):
     * <ul>
     *  <li>[*]$searchQuery['maxResults']   = Number of results to be returned</li>
     *  <li>[*]$searchQuery['connPopup']    = Boolesche operator to connect conditions</li>
     *  <li>$searchQuery['title']        = Title of the document</li>
     *  <li>$searchQuery['name']         = Name of person</li>
     *  <li>$searchQuery['originid']     = Faculty</li>
     *  <li>$searchQuery['typeid']       = Type of the documents</li>
     *  <li>$searchQuery['formatid']     = Media type</li>
     *  <li>$searchQuery['keywords']     = Keyowords</li>
     *  <li>$searchQuery['datecreated']  = Date of creation; format dd.mm.yyy</li>
     *  <li>$searchQuery['datemodified'] = Data of modification; format dd.mm.yyy</li>
     *  <li>$searchQuery['datesubmitted']= Date of submit; format dd.mm.yyy</li>
     *  <li>$searchQuery['dateaccepted'] = Date of accaptance; format dd.mm.yyy</li>
     *  <li>$searchQuery['legalentityid']= Unique identifier of a legal entity</li>
     *  <li>$searchQuery['classifcateg'] = Classification of the document</li>
     *  <li>$searchQuery['datevalidfrom']= Date from which the document is valid</li>
     *  <li>$searchQuery['datevalidto']  = Date till which the document is valid</li>
     * </ul>
     * At least one of the fields not marked as required must be passed in order
     * to create a valid request.
     * @param array $searchQuery The above described required parameters for creating a valid Xml request.
     * @return XmlElement The web service's response.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DoXmlQuery ($paramsSearchQuery) {
        $this->paramsSearchQuery = $paramsSearchQuery;
        $xml = $this->PerformRequest ('doXMLQuery', array ('query' => array ('content' => $this->CreateDoXmlQueryRequest (), 'type' => 'apachesoap:Document')));
        return $xml->GetChild('results');
    }//DoXmlQuery

    /**
     * Creates the XML request for the QueryService web service out
     * of the parameters stored in this instance by the calling method.
     * @return string A XML <c>string</c> that is used for the QueryService web service request
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */     
    private function CreateDoXmlQueryRequest () {
        $ret = '';

        if (isset ($this->paramsSearchQuery['maxResults']) and 
            isset($this->paramsSearchQuery['connPopup'])) {
            //Create a DuEPublico xml query as described in Webserivce.txt in Miless source code 
            $ret  = '<query maxResults="'.$this->paramsSearchQuery['maxResults'].'" index="lucenem" recordSchema="miless">';
            $ret .= '<conditions format="xml">';
            $ret .= '<boolean operator="'.$this->paramsSearchQuery['connPopup'].'">';
            
            //Remove allready read conditions
            unset ($this->paramsSearchQuery['maxResults']);
            unset ($this->paramsSearchQuery['connPopup']);
        } else {
            throw new InvalidArgument ('Passed search query parameters are not valid: maxResults and connPopup must be set.');
        }//else
        //Is needed because the date field has 4 array elements that have to be reduced to 3
        //otherwise the get_field_row function doesn't work corretly
        //TODO: Remove this workaround
        $this->ModifyDate();
        
        while (list ($key, $value, $operator) = $this->FieldRow()) {
            
            //Check if the value is set and add the condition to the XML file
            if ($this->isValueInitialized ($value)) {
                $ret .= '<condition field="'.$key
                        .'" operator="'.str_replace (array ('<', '>'), array ('&lt;', '&gt;'), $operator)
                        .'" value="'.$this->MakeUnicode($value).'" />';
            }//if
        }//while
        
        //TODO: This part could be customized if it is implemented on the Miless/MyCoRe side.
        $ret .= '</boolean>';
        $ret .= '</conditions>';
        $ret .= '<resultFields>';
        $ret .=      '<field field="title"   />';
        $ret .= '</resultFields>';
        $ret .= '<sortBy>';
        $ret .=      '<field name="datecreation" order="descending" />';
        $ret .=      '<field name="title"        order="descending" />';
        $ret .=      '<field name="name"         order="ascending"  />';
        $ret .= '</sortBy>';
        $ret .= '</query>'; 
        
        return $ret;
    }//CreateRequest
    
    /**
     * Creates a Xml request required for uploading a file via the
     * doUpload method.
     * @param string $uri The Url of the file to be downloaded by the DuEPublico server.
     * @param string $uname The username that has writting access on the document
     * @param string $pword The password of the account specified by {{@link $uname $uname} that has writing access.
     * @param int $documentId The unqiue identifier of the DuEPublico document that should contain the uploaded file.
     * @param string $name The name of the uploaded package.
     * @return string The Xml string required for calling the DoUpload method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreateDoUploadRequest ($uri, $uname, $pword, $documentId, $name) {
        return '<packageUpload>
                  <source downloadType="vfs" packageType="zip" loPackageType="vfs">
                    '.$uri.'
                  </source>
                  <name>'.$name.'</name>
                  <destination>
                    <user name="'.$uname.'" pwd="'.$pword.'" />
                    <action docID="'.$documentId.'" />
                  </destination>
                </packageUpload>';
    }//CreateDoUploadRequest
    
    /**
     * WORKAROUND that should be eliminated.
     * Takes the date field of the array and transforms it in that way,
     * it can be processed by the FieldRow method.ä
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function ModifyDate () {
        if (isset ($this->paramsSearchQuery['date']) and 
            isset ($this->paramsSearchQuery['datum']) and
            isset ($this->paramsSearchQuery['operatorDatum'])) {  
                
            $date  = $this->paramsSearchQuery['date'];
            $datum = $this->paramsSearchQuery['datum'];
            $operatorDatum = $this->paramsSearchQuery['operatorDatum'];
             
            unset($this->paramsSearchQuery['date']);
            unset($this->paramsSearchQuery['datum']);
            unset($this->paramsSearchQuery['operatorDatum']);
            
            if (($datum != '') and ($datum != 'tt.mm.jjjj')) {
                $this->paramsSearchQuery[$date] = $datum;
                $this->paramsSearchQuery['operatorDatum'] = $operatorDatum;
            }//if
        }//if     
    }//ModifyDate
 
     /**
     * Takes the first two elements of the passed <c>array</c>
     * and creates a new <c>array</c> out of them. The returned
     * <c>array</c> has the following form:
     * keyFirstElement valueFirstElement valueSecondElement
     * @return mixed The <c>array</c> specified above, or <c>false</c> if the passed <c>array &$fieldArray</c> is empty.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function FieldRow () {
        if (!empty($this->paramsSearchQuery)) {
            list($key) = array_keys($this->paramsSearchQuery);
            
            $first  = $key;
            $second = array_shift($this->paramsSearchQuery);
            $third  = array_shift($this->paramsSearchQuery);
            return array ($first, $second, $third);
        } else {
            return false;
        }//else
    }//FieldRow
     
   /**
     * Takes the SOAP document returned by the web service and returns
     * the element that contains the searchfields elements, specified
     * by the searchfield index passed to this instance or the set default
     * value. Possible values for the search field index are:
     * <ul>
     *  <li>ubo: This index contains metadata of Universitätsbibliographie Online (module-dozbib) </li>
     *  <li>scorm: This index contains scorm data (module-scorm)</li>
     *  <li>mildocument: This index contains metadata of documents</li>
     *  <li>milcontent: Search in MCRFile metadata and content</li>
     * </ul>
     * @param XMLElement $xml The SOAP document returned by the web service
     * @return XMLElement The XMLElement that contains the needed searchfields
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function ParseSearchFields ($xml) {
        $element = $xml->GetChildXpath('/searchfields/index');
        
        foreach($element as $entry) {
            if ($entry->GetAttributeValue('id') == $this->searchFieldIdValue) {
                return $entry;
            }//if
        }//foreach
        throw new WebServiceReturnException ('QueryService', 'ParseSearchFields', 'Could not find search field index '.$this->searchFieldIdValue.' in web service response.');  
    }//ParseSearchFields 
     
    private function GetLabels () {
        $labels = array ();
        $labels['title']           = 'Titel';
        $labels['name']            = 'Personen';
        $labels['originid']        = 'Fakultät/Institut';
        $labels['typeid']          = 'Dokumententyp';
        $labels['formatid']        = 'Medientyp';
        $labels['keywords']        = 'Stichworte';
        $labels['datecreation']    = 'Datum der Erstellung';
        $labels['created']         = 'Erstellung';
        $labels['modified']        = '&#xC4;nderung';
        $labels['submitted']       = 'Einreichung';
        $labels['accepted']        = 'Annahme';
        $labels['date']            = 'Datum der ';
        $labels['datecreated']     = $labels['date'].$labels['created'];
        $labels['datemodified']    = $labels['date'].$labels['modified'];
        $labels['datesubmitted']   = $labels['date'].$labels['submitted'];
        $labels['dateaccepted']    = $labels['date'].$labels['accepted'];
        $labels['legalentityid']   = '';
        $labels['classifcateg']    = '';
        $labels['datevalidfrom']   = '';
        $labels['datevalidto']     = '';
        return $labels;
    }//GetLabels 
    
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
    
}//Class: QueryService
?>
