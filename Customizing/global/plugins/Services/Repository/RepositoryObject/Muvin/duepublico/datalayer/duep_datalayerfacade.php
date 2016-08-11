<?php
/**
 * @copyright Copyright &copy; 2009, University of Duisburg-Essen, Campus Essen
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
 * @abstract This class provides access to all web service functions and some
 *           additional methods that are composed of different web service 
 *           calls. Furthermore, in this class caching and validation cross
 *           cutting concerns are added to the web service calls in order
 *           to provide a clean seperation of concerns.
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

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_FileService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PermissionService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PersonService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_QueryService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_filesystemservice.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_validationservice.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_transformationservice.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_helpButtons.php');
require_once ($CFG->dirroot.'/lib/duepublico/exceptions/duep_Exceptions.php');
require_once ($CFG->dirroot.'/lib/duepublico/duep_authorprofile.php');
require_once ($CFG->dirroot.'/lib/duepublico/XmlParser/duep_XMLExceptions.php');
class DataLayerFacade {
    
    private $wwwduepublico;
    private $dirroot;
    private $tempDir;
   
    private $formatIdTemp;
    private $originIdTemp;
    private $typeIdTemp;
    private $roleTemp;
    private $searchFieldTemp;
    private $languagesTemp;
   
    private $transformationService;
    private $filesystemService;
    private $helpButtons;
    private $validationService;
    
    private $documentService;
    private $fileService;
    private $permissionService;
    private $personService;
    private $queryService;
    private $userService;
    private $globalAuthorPath;
    
    private static $service = null;
    
    private function __construct () {

        global $CFG, $DUEP;
        
        $this->wwwduepublico   = $DUEP->wwwduepublico;
        $this->dirroot         = $CFG->dirroot;
        
        $this->tempDir         = $this->AddSlash($DUEP->duepublicoTemp);
        $this->formatIdTemp    = $this->tempDir.$DUEP->formatidTemp;
        $this->originIdTemp    = $this->tempDir.$DUEP->originidTemp;
        $this->typeIdTemp      = $this->tempDir.$DUEP->typeidTemp;
        $this->roleTemp        = $this->tempDir.$DUEP->roleTemp;
        $this->searchFieldTemp = $this->tempDir.$DUEP->searchFieldTemp;
        $this->languagesTemp   = $this->tempDir.$DUEP->languagesTemp;
        $this->operatorsTemp   = $this->tempDir.$DUEP->operatorTemp;
        
        $this->documentService   = new DocumentService ();
        $this->fileService       = new FileService ();
        $this->permissionService = new PermissionService ();
        $this->personService     = new PersonService ();
        $this->queryService      = new QueryService ();
        $this->userService       = new UserService ();
        
        $this->helpButtons = new HelpButtons ();
        
        $this->globalAuthorPath = $CFG->dataroot.'/globalAuthor.ser';
    }//Constructor

    public function SetValidationService ($validationService) {
        $this->validationService = $validationService;
    }//SetValidationService
    
    public function SetFilesystemService ($filesystemService) {
        $this->filesystemService = $filesystemService;
    }//SetFilesystemService
    
    public function SetTransformationService ($transformationService) {
        $this->transformationService = $transformationService;
    }//SetTransformationService

    public static function GetDataLayerFacade () {
        if (null == self::$service) {
            self::$service = new DataLayerFacade ();
        }//if
        return self::$service;
    }//GetDataLayerFacade

    public function GetClassificationPopup ($classification, $firstSelected = true, $keySelectedOption = '', $includeNoChoose = true) {
        $classificationId = $classification;
        if ($this->EndsWith($classificationId, 'id')) {
            $classificationId = substr($classificationId, 0, strlen($classificationId) -2);
        }//if
        
        try {
            $popup = $this->UnserializeObject($this->CassificationPath ($classificationId));
        } catch (FileException $e) {
            $xml = $this->queryService->DoRetrieveClassification($classificationId);
            $popupFields = $this->transformationService->CheckCategories($xml);
            $popup = $this->transformationService->CreatePopupFromArray($popupFields, $classification, ON_CHANGE, $firstSelected, $keySelectedOption, $includeNoChoose);
            $this->SerializeObject($popup, $this->CassificationPath ($classificationId));
        }//catch
        
        if ($keySelectedOption != '') {
            return $this->transformationService->ResetSelectedOption($popup, $keySelectedOption);
        } else {
            return $popup;
        }//else
    }//GetClassificationPopup
    
    private function CassificationPath ($classificationId) {
        global $DUEP;
        $path = '';
        switch ($classificationId) {
            case 'type':
                $path = $DUEP->typeidTemp;
            break;
            case 'origin':
                $path = $DUEP->originidTemp;
            break;
            case 'format':
                $path = $DUEP->formatidTemp;
            break;
            default:
                throw new InvalidArgumentException ('DataLayerFacade [LoadClassificationPopup]: Passed invalid classification '.$classificationId.'. Possible values are format, type, and origin.');
        }//swtich
        return $path;
    }//LoadClassificationPopup
    
    private function SerializeObject ($object, $path) {
        if ($path == '') {
            throw new InvalidArgumentException ('DataLayerFacade [SerializeObject]: Path to serialize instance must not be empty.');
        } else {
            $string = serialize ($object);
            return $this->filesystemService->WriteFile($string, $path);
        }//else
    }//SerializeObject
    
    private function UnserializeObject ($path) {
        if ($path == '') {
            throw new InvalidArgumentException ('DataLayerFacade [UnserializeObject]: Path to serialize instance must not be empty.');
        } else {
            $string = $this->filesystemService->ReadFileContent($path);
            return unserialize ($string);
        }//else
    }//UnserailizeObject
    
    /**
     * Creates a drop down list that contrains all the DuEPublico languages. The passed
     * key is used to selected a specific language in the drop down list.
     * @param string $goalLanguage The lanaguage of the labels. Possible values are English and German.
     * @param string $keySelectedOption The key of the option that should be marked as selected
     * @return array An <c>array</c> that contains the status information of the method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetLanguagePopup ($goalLanguage, $keySelectedOption = '') {
        global $DUEP;
        $popup = '';
        try {
            $popup = $this->UnserializeObject($DUEP->languagesTemp);
        } catch (FileException $e) {
            $popupFields = array ();
            $languages = $this->GetLanguages($goalLanguage);
            while (list ($key, $value) = each ($languages)) {
                $popupFields[] = array ('value' => $key, 'label' => $value, 'title' => $value);
            }//while
            $popup = $this->transformationService->CreatePopupFromArray($popupFields, 'language', ON_CHANGE, true, $keySelectedOption, false);
            $this->SerializeObject($popup, $DUEP->languagesTemp);
        }//catch
        
        if ($keySelectedOption != '') {
            $popup = $this->transformationService->ResetSelectedOption($popup, $keySelectedOption);
        }//else
        return $popup;
    }//GetLangauegPopup
 
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    
    /**
     * Returns an <c>array</c> of <c>CourseObject</c> instances stored within the
     * Moodle data root directory.
     * @return array An <c>array</c> of all <c>CourseObject</c> instances.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetCourseObjects () {
        global $CFG;
        return $this->filesystemService->GetCourseObjects ($CFG->dataroot);
    }//getCourseObjects
    
    /**
     * Create a new DuEPublico document with the validated parameters
     * <code>$docParams</code>, grant access for the user wo created 
     * the document and load Java applet to upload files.
     * @param array $docParams The attributes of the DuEPublico document
     * @return array An <c>array</c> that contains the status information if the validation failed. 
     *               If the validation was successful then the identifier of the created legal entity
     *               is inserted into the returned <c>array</c> and is available at the 'documentId'.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateDocument ($docParams) {
        $this->validationService->ValidateDocCreation($docParams);
        return $this->documentService->CreateDocument ($docParams);
    }//CreateDocument
    
    /**
     * Deletes the DuEPublico document specified by the passed identifier
     * @param int $documentId The identifier of the document that should be deleted.
     * @return bool <c>True</c> if the document was removed successfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteDocument ($documentId) {
        if (($documentId == '') or ($documentId <= 0)) {
            throw new ValidationException ('The document identifier must not be empty or negative.', $documentId, 1);
        } else {
            if ($this->documentService->DeleteDocument ($documentId)) {
                return true;
            } else {
                throw new WebServiceException ('DocumentService', 'deleteDocument', 'Error occured while deleting document.', $documentId, 2);
            }//else
        }//else
    }//DeleteDocument
    
    /**
     * Checks if the document with the specific identifier 
     * exists on the DuEPublico server.
     * @param int $documentId The document's identifier.
     * @return bool <c>True</c> a document with the passed identifier exists; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistDocument ($documentId) {
        if (($documentId == '') or ($documentId <= 0)) {
            throw new ValidationException ('The document identifier must not be empty or negative.', $documentId, 1);
        } else {
            return $this->documentService->ExistDocument ($documentId);
        }//else
    }//ExistDocument
    
    /**
     * Returns an <c>array</c> of languages that are available on the
     * DuEPublico server for the annotation of doucment metadata.
     * @param string $goalLanguage An identifier in which language the labels of the list should be returned. 
     *                             Possible values are German and English.
     * @return array An <c>array</c> that contains the labels and the abbriviations of all available languages.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetLanguages ($goalLanguage) {
        if (($goalLanguage == 'German') or ($goalLanguage == 'English')) {
            return $this->documentService->GetLanguages ($goalLanguage);;
        } else {
            throw new InvalidArgumentException ('Just German and English are allowed values for GetLanguages.');
        }//else
    }//GetLanguages
    
    /**
     * Returns the metadata of the document, specified by the
     * passed identifier.
     * @param int $documentId The document's identifier.
     * @return XmlElement The document's metadata in form of a <c>XmlElement</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetDocumentData ($documentId) {
        if (($documentId == '') or ($documentId <= 0)) {
            throw new ValidationException ('The document identifier must not be empty or negative.', $documentId, 1);
        } else {
            return $this->documentService->GetDocumentData ($documentId);
        }//else
    }//GetDocumentData
    
    /**
     * Returns the smallest identifier a derivate contained in the the 
     * document, specified by the passed identifier.
     * @param integer $documentId The document's identifier.
     * @return integer The smallest identifier of the derivates contained in the document.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SmallestDerivateId ($documentId) {
        if (($documentId == '') or ($documentId <= 0)) {
            throw new ValidationException ('The document identifier must not be empty or negative.', $documentId, 1);
        } else {
            return $this->documentService->SmallestDerivateId($documentId);
        }//else
    }//SmallestDerivateId
      
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
     
    /**
     * Checks if the derivate, specified by the passed <c>$derivateId</c>
     * exists.
     * @param int $derivateId A possible identifier of a derivate.
     * @return bool <c>True</c> if a derivate with the passed unique identifier exists, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsFileDerivate ($derivateId) {
       if (($derivateId == '') or ($derivateId <= 0)) {
            throw new ValidationException ('The derivate identifier must not be empty or negative.', $derivateId, 1);
        } else {
            return $this->fileService->ExistsFileDerivate ($derivateId);
       }//else
    }//ExistsFileDerivate
     
    /**
     * Deletes the derivate specified by the passed unique identifier.
     * @param int $derivateId The unique identifier of the derivate that should be deleted.
     * @return bool <c>True</c> if the derivate was removed succesfully, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteFileDerivate ($derivateId) {
        if (($derivateId == '') or ($derivateId <= 0)) {
            throw new ValidationException ('The derivate identifier must not be empty or negative.', $derivateId, 1);
        } else if (!$this->fileService->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('Can not delete derivate '.$derivateId.' because it does not exist.', $derivateId, 1);
        } else if ($this->fileService->DeleteFileDerivate ($derivateId)) {
            return true;
        } else {
            throw new WebServiceException ('FileService', 'DeleteFileDerivate', 'Error while deleting file derivate.', $derivateId, 1);
       }//else   
    }//DeleteFilDerivate
     
     /**
     * Creates a file derivate within a specific DuEpublico document, specified
     * by <c>$documentId</code>.
     * @param array $documentId The unique identifier of the document that shoul contain the derivate.
     * @return int The unique identifier of the created derivate
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateFileDerivate($documentId) {
        if (($documentId == '') or ($documentId <= 0)) {
            throw new ValidationException ('The document identifier must not be empty or negative.', $documentId, 1);
        } else { 
            return $this->fileService->CreateFileDerivate($documentId);
        }//else
    }//CreateFileDerivate
 
    /**
     * Stores a file, specified by <c>$filename</c> in the derivate
     * specified by <c>$derivateId</c>. The DuEPublico server will download
     * the file from <c>$moodlePublish/$filename</c>. 
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
     * @param int $derivateId The unique identifier of the derivate that should contain the file.
     * @return string The MD5 check sum of the file as the DuEPublico server recieved it.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function StoreFile ($moodlePublish, $filename, $derivateId) {
        if (($derivateId == '') or ($derivateId <= 0)) {
            throw new ValidationException ('The derivate identifier must not be empty or negative.', $derivateId, 1);
        } else if (!$this->fileService->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('Could not store '.$filename.' within derivate '.$derivateId.' because this derivate does not exist.', $derivateId, 1);
        } else {
            return $this->fileService->StoreFile($moodlePublish, $filename, $derivateId);
        }//else
    }//StoreFile
    
    /**
     * Stores a zip file, specified by <c>$filename</c> in the derivate
     * specified by <c>$derivateId</c>. The DuEPublico server will download
     * the zip file from <c>$moodlePublish/$filename</c>. 
     * So <c>$moodlePublish</c> must be a server directory accessable from
     * the internet.
     * <example>
     *  $moodlePublish: www.myMoodleServer.org/public
     *  $filename: myZip.zip
     *  So the DuEPublico server will try to download the file from:
     *  http://www.myMoodleServer.org/public/myZip.zip
     * </example>
     * @param string $moodlePublish A Moodle server directory accessable from the internet.
     * @param string $filename The name of the file that should be transfered to the DuEPublico server.
     * @param int $derivateId The unique identifier of the derivate that should contain the file.
     * @return int The number of files the DuEPublico server recieved.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function StoreZipFileContents ($moodlePublish, $filename, $derivateId) {
        if (($derivateId == '') or ($derivateId <= 0)) {
            throw new ValidationException ('The derivate identifier must not be empty or negative.', $derivateId, 1);
        } else if (!$this->fileService->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('Could not store '.$filename.' within derivate '.$derivateId.' because this derivate does not exist.', $derivateId, 1);
        } else {
            return $this->fileService->StoreZipFileContents ($moodlePublish, $filename, $derivateId);
        }//else
    }//StoreZipFileContents
 
    /**
     * Returns a sessions key that is required for uploading a file 
     * via the applet. After transfering the file the applet will 
     * redirect to the url specified by <c>$returnUrl</c>. Normally
     * the <c>$returnUrl</c> will have the following structure:
     * <code>
     * http://www.testMoodle.de/mod/resource/type/fileupload/duep_uploadmain.php?
     *  finishBt=set
     *  &id=[Moodle course identifier]
     *  &userid=[Moodle user identifier]
     *  &choose=[Reference to the textbox to which the result should be transfered]
     *  &documentId=[The DuEPublico document identifier]
     *  &sesskey=[The Moodle session key]
     *  &derivateId=[The DuEPublico derivate identifier]
     * </code>
     * @param int $documentId A unqiue identifier of the document.
     * @param int $derivateId A unquie identifier of the derivate to which the file should be uploaded.
     * @param strign $returnUrl The url that should be called after uploading the file.
     * @return string The upload session key for the Upload Applet.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function GetUploadSessionkey ($documentId, $derivateId, $returnUrl) {
        if (!$this->validationService->ValidateUrl($returnUrl)) {
            throw new ValidationException ('The passed Url '.$returnUrl.' is not a valid url.', $returnUrl, 1);
        } else if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('The document with '.$documentId.' identifier does not exist.', $documentId, 2);
        } else if (!$this->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('The derivate with '.$derivateId.' identifier does not exist.', $derivateId, 4);
        } else {
            return $this->fileService->GetUploadSessionkey ($documentId, $derivateId, $returnUrl);
        }//else
    }//GetUploadSessionKey
     
    /**
     * Calls the <c>getDerivateData</c> with the passed identifier
     * and returns the web serivce's reponse.
     * @param int $derivateId The DuEPublico derivate identifier.
     * @return XmlElement The web service's reponse in form of a <c>XmlElement</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetDerivateData ($derivateId) {
        if (!$this->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('The derivate with '.$derivateId.' identifier does not exist.', $derivateId, 1);
        } else {
            return $this->fileService->GetDerivateData ($derivateId);
        }//else
    }//GetDerivateData
    
    
    public function GetMoodleBackupDerivateId ($documentId, $metadata) {
        $derivateIds = array ();
        try {
            $derivates = $metadata->GetChild('derivates')->GetChildren();
            if (is_array ($derivates)) {
                foreach ($derivates as $derivate) {
                    if ($derivate instanceof XMLElement) {
                        $derivateIds = $this->CheckDerivates ($derivate, $derivateIds, $documentId);
                    } else if (is_array($derivate)) {
                        foreach ($derivate as $d) {
                            $derivateIds = $this->CheckDerivates ($d, $derivateIds, $documentId);
                        }//foreach
                    }//else
                }//foreach
            }//if   
        } catch (ChildNotFoundException $e) {}
        return $derivateIds;
    }//isDerivateMoodleBackup
    
    public function DownloadDocument ($url, $destination) {
        if ($destination == '') {
            throw new InvalidArgumentException ('DataLayerFacade [DownloadDocument]: Destination must not be an empty string.');
        }//if
        $this->validationService->ValidateUrl($url);
        return $this->filesystemService->DownloadDocument ($url, $destination);
    }//DownloadDocument
    
    private function CheckDerivates ($derivate, $derivateIds, $documentId) {
        $derivateId = $derivate->GetAttributeValue('ID');
        $files = $derivate->GetChild('files')->GetChildren ();
        foreach ($files as $file) {
            if ($file instanceof XMLElement) {
                if ($this->CheckMoodleXml ($file)) {
                    $derivateIds[] = array ('derivate' => $derivateId, 'document' => $documentId);
                    break;
                }//if
            } else if (is_array($file)) {
                foreach ($file as $f) {
                    if ($this->CheckMoodleXml ($f)) {
                        $derivateIds[] = array ('derivate' => $derivateId, 'document' => $documentId);
                        break;
                    }//if
                }//foreach
            }//else
        }//foreach
        return $derivateIds;
    }//CheckDerivates
    
    private function CheckMoodleXml ($file) {
        $path = $file->GetChild('path')->GetElementText();
        if ($this->EndsWith ($path, 'moodle.xml')) {
            return true;
        } else {
            return false;
        }//else
    }//CheckMoodleXml
     
    /**
     * Returns the filepath of the main file or the only file of
     * the DuEPublico derivate, specified by the passed identifier.
     * @param int $derivateId The unique identifier of a DuEPublico derivate
     * @return string The filename of the main or the only file of the derivate.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    public function FilenameByDerivateId ($derivateId) {
        if (!$this->ExistsFileDerivate($derivateId)) {
            throw new ValidationException ('The derivate with '.$derivateId.' identifier does not exist.', $derivateId, 1);
        } else {
            return $this->fileService->FilenameByDerivateId ($derivateId);
        }//else
    }//FilenameByDerivateId
    
    
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    
    /**
     * Calls the PermissionService webservice and grants the passed
     * permissions for the document spcified by the passed id. The
     * permissions <c>array</c> must have the following structure:
     * <ul>
     *   <li>key = The right that should be granted</li>
     *   <li>value = The user to which the right should be granted</li>
     * </ul>
     * If you want grant write access to a user account you can't grant
     * a read access at the same time, because that will override the
     * write access. In order to give write access to an user account
     * and restrict the reading access at the same time you must create
     * two user accounts: first an account with writing access that can 
     * read the document as well and secondly an account with reading 
     * access that protects your document by restricting the reading
     * access to this and the first account.
     * 
     * @param int $documentId The id of the document to which access should be granted
     * @param array $perms The permissions which should be granted
     * @return bool Returns <c>true</c> if the permissions were granted successfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetPermissions ($documentId, $perms) {  
        if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('The document specified by '.$documentId.' does not exist.', $documentId, 1);
        } else {
            if ($this->permissionService->SetPermissions($documentId, $perms)) {
                return true;
            } else {
                throw new WebServiceException ('PermissionService', 'SetPermissions', 'Error while setting permissions for document '.$documentId, $documentId, 2);
            }//else
        }//else
    }//SetPermissions

    /**
     * Removes all access restrictions from the document
     * specified by the passed unique identifier.
     * @param int $documentId The unique identifier of the document for which the access restrictions should be removed.
     * @return bool <c>True</c> if the access restrictions were removed sucesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function RemoveAllPermissions ($documentId) {
        if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('The document specified by '.$documentId.' does not exist.', $documentId, 1);
        } else {
            if ($this->permissionService->RemoveAllPermissions($documentId)) {
                return true;
            } else {
                throw new WebServiceException ('PermissionService', 'RemoveAllPermissions', 'Error while removing permissions for document '.$documentId, $documentId, 2);
            }//else
        }//else
    }//RemoveAllPermissions

    /**
     * Checks if the document, specified by the passed document id, has read
     * and/or write protections. The applied rights are returned in form of an
     * <c>array</c> that has the following structure:
     * <ul>
     *  <il>array['freeRead'] : <c>true</c> if the document can be access freely for reading, otherwise <c>false</c>.</il>
     *  <il>array['freeWrite'] : <c>true</c> if the document can be access freely for writting, otherwise <c>false</c>.</il>
     * </ul>
     * @param int $documentId The document that should be checked
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetPermissions ($documentId) {
        if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('The document specified by '.$documentId.' does not exist.', $documentId, 1);
        } else {
            return $this->permissionService->GetPermissions($documentId);
        }//else
    }//GetPermissions
    
    /**
     * Returns the document specified by the passed identifier in
     * form of a <c>XMLElement</c>.
     * @param integer $documentId The unique identifier of the document to be returned.
     * @param string $resultFile The temporary search result file
     * @return XMLElement The requested document in form of a <c>XMLElement</c> object.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetDocumentById ($documentId, $resultFile) {
        try {
            $results = $this->UnserializeObject($resultFile);
            if ($results != null) {
                $hits = $results->GetChild('hit');
                if ($hits instanceof XMLElement) {
                    if ($hits->GetAttributeValue('id') == $documentId) {
                        return $hits;
                    }//if
                } else if (is_array($hits)) {
                    foreach ($hits as $result) {
                        if ($result->GetAttributeValue('id') == $documentId) {
                            return $result;
                        }//if
                    }//foreach
                }//else
            }//if
        } catch (FileException $e) {}
        return $this->GetDocumentData($documentId);
    }//GetDocumentById

    /**
     * Checks if the user has access to the document, specified by the passed
     * document id. 
     * @param int $documentId Unique identifier of the document that should be checked
     * @param string $uname The username which should be checked
     * @return bool <c>True</c> if the user has access and <c>false</code> if not
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetUserRights ($documentId, $uname) {
        if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('The document specified by '.$documentId.' does not exist.', $documentId, 1);
        } else if (!$this->ExistsUser($uname)) {
            throw new ValidationException ('The user account specified by '.$uname.' does not exist.', $uname, 2);
        } else {
            return $this->permissionService->GetUserRights($documentId, $uname);
        }//else
    }//GetUserRights
    
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    /**
     * Checks if the legal entity specified by the passed id exsits.
     * @param int $legalEntityId The unique identifier of the person.
     * @return bool <c>True</c> if a person with the specified unqiue identifier exists; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsPerson ($legalEntityId) {
        return $this->personService->ExistsPerson ($legalEntityId);
    }//ExistsPerson
     
    /**
     * Deletes the legal entity specified by the passed identifier.
     * @param int $legalEntityId The unique identifier of the person.
     * @return bool <c>True</c> if legal entity specified by the passed identifier was deleted succesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeletePerson ($legalEntityId) {
        if (!$this->ExistsPerson($legalEntityId)) {
            throw new ValidationException ('The legal entity specified by '.$legalEntityId.' does not exist.', $legalEntityId, 1);
        } else {
            if ($this->personService->DeletePerson ($legalEntityId)) {
                return true;
            } else {
                throw new WebServiceException ('PersonService', 'DeletePerson', 'Error while deleting legal entity '.$legalEntityId.' .', $legalEntityId, 2);
            }//else
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
        if (!$this->ExistsPerson($legalEntityId)) {
            throw new ValidationException ('The legal entity specified by '.$legalEntityId.' does not exist.', $legalEntityId, 1);
        } else {
            return $this->personService->PersonInfo($legalEntityId);
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
     * @return array An <c>array</c> that contains the status information if the validation failed. 
     *               If the validation was successful then the identifier of the created legal entity
     *               is inserted into the returned <c>array</c> and is available at the 'legalEntityId'.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreatePerson ($personParams) {
        if ($this->validationService->ValidateLegalEntity ($personParams)) {
            return $this->personService->CreatePerson ($personParams);
        } else {
            throw new ValidationException ('Error while validation person create parameters.', $personParams, 1);
        }//else
    }//CreatePerson
    
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    
    /**
     * Calls the DuEPublico QueryService web services to find the Moodle course's document
     * If there is such a document then the id will be returned as payload of the returned
     * <c>array</c>.
     * @param integer $courseId Id of the Moodle course
     * @return integer The unique identifier of the course document or <c>false</c> is 
     *                 such a docuement does not exits
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetCourseDocumentId ($title) {
        if (strcasecmp ($title, '') == 0) {
            throw new ValidationException ('The title of the course document must not be empty.', $title, 1);
        } else {
            return $this->queryService->GetCourseDocumentId ($title);
        }//else
    }//GetCourseDocumentId
    
    public function GetSearchFields () {
        global $DUEP;
        $searchFields = null;
        $path = $DUEP->searchFieldTemp; 
        try {
            $searchFields = $this->UnserializeObject($path);
        } catch (FileException $e) {
            $searchFields = $this->queryService->GetSearchFields();
            $this->SerializeObject($searchFields, $path);
        }//catch
        return $searchFields;
    }//GetSearchFields
    
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
        if (!$this->validationService->ValidateClassification ($classification)) {
            throw new ValidationException ('Classification identifier '.$classification.' is not defnied.', $classification, 1);
        } else {
            return $this->queryService->DoRetrieveClassification ($classification);
        }//else
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
        if (!$this->ExistsUser($uname)) {
            throw new ValidationException ('The user '.$uname.' does not exist.', $uname, 1);
        } else if (!$this->CheckPassword ($uname, $pword)) {
            throw new ValidationException ('The passed credentials are not valid.', array ($uname, $pword), 2);
        } else if (!$this->ExistsDocument ($documentId)) {
            throw new ValidationException ('A document specified by '.$documentId.' does not exist.', $documentId, 3);
        } else {
            if ($this->queryService->DoUpload ($moodlePublish, $filename, $uname, $pword, $documentId)) {
                return $this->CreatePositiveReturn('DoUpload', true);
            } else {
                throw new WebServiceException ('QueryServce', 'DoUpload', 'Error while processing upload request.', array ($moodlePublish, $filename, $uname, $pword, $documentId), 4);
            }//else
        }//else
    }//DoUpload
    
    /**
     * Calls the lastModifiedInfo method and returns the 
     * web service's response in form of a <c>XmlElement</c>.
     * @return array The <c>XmlElement</c> that contains the time stamp when a DuEPublico method was updated.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckTimeStamps () {
        return $this->queryService->CheckTimeStamps ();
    }//CheckTimeStamps
    
    /**
     * Calls the lastModifiedInfo method and returns an <c>array</c>
     * composed of method names as keys and time stamps as values. This
     * <c>array</c> is used to update stored content if neccessary.
     * @return array The <c>array</c> that contains the time stamp when a DuEPublico method was updated.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CheckTimeStampsAsArray () {
        return $this->queryService->CheckTimeStampsAsArray ();
    }//CheckTimeStampsAsArray
    
    /**
     * Function calls the getLastModified webservice in order to reload
     * some of the updated DuEPublico fields if neccessary. Therefore,
     * the time stamps of the temporary files will be compared with the
     * last modified time stamps.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CompareTimeStamps () {
        global $DUEP;
        
        $fileStamps = array ();
        if (file_exists($DUEP->languagesTemp)) {
            $fileStamps['getLanguages'] = array ('date' => date('c', filemtime($DUEP->languagesTemp)),   'file' => $DUEP->languagesTemp);
        }//if
        if (file_exists($DUEP->formatidTemp)) {
            $fileStamps['doRetrieveClassification'] = array ('date' => date('c', filemtime($DUEP->formatidTemp)),   'file' => $DUEP->formatidPopup);
        }//if
        if (file_exists($DUEP->originidTemp)) {
            $fileStamps['doRetrieveClassification'] = array ('date' => date('c', filemtime($DUEP->originidTemp)),   'file' => $DUEP->originidPopup);
        }//if
        if (file_exists($DUEP->typeidTemp)) {
            $fileStamps['doRetrieveClassification'] = array ('date' => date('c', filemtime($DUEP->typeidTemp)),     'file' => $DUEP->typeidPopup);
        }//if
        if (file_exists($DUEP->roleTemp)) {
            $fileStamps['getRoles'] = array ('date' => date('c', filemtime($DUEP->roleTemp)),   'file' => $DUEP->rolePopupTemp);
        }//if
        if (file_exists($DUEP->searchFieldTemp)) {
            $fileStamps['getSearchFields'] = array ('date' => date('c', filemtime($DUEP->searchFieldTemp)), 'file' => $DUEP->SearchFieldTemp);
        }//if
        
        //TODO: Put this two statements into the web service class.,
        $xml = duep_call_webservice ('QueryService', 'getLastModifiedInfo');
        $timestamps = $xml->get_child('timestamps')->get_child ('timestamp');
        
        
        foreach ($timestamps as $timestamp) {
            $stamp = $timestamp->get_text ();
            $method = $timestamp->get_attribute_value ('method');
            if (($fileStamps[$method]['date'] < $stamp) and  
                isset($fileStamps[$method])         ) {
                unlink($fileStamps[$method]['file']);
            }//if
        }//foreach
    }//CompareTimeStamps
    
    
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
        if ($this->validationService->ValidateQueryParameters($paramsSearchQuery)) {
            return $this->queryService->DoXmlQuery ($paramsSearchQuery);
        } else {
            throw new ValidationException ('Validation of query parameters failed.', $paramsSearchQuery, 1);
        }//else
    }//DoXmlQuery
    
    
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    
    
     /**
     * Gets a download ticket for downloading restricted files. The ticket is
     * attached to he url where the file is stored on the DuEPublico server:
     * <example>
     *     [URL]?miless.ticket=XXX
     * </example>
     * @param string $uname Username
     * @param string $pword Password
     * @return string Ticket that will be attached to the URL in order to download restricted files
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */   
    public function GetTicket($uname, $pword) {
        if (!$this->ExistsUser($uname)) {
            throw new ValidationException ('User '.$uname.' does not exist.', $uname, 1);
        } else if (!$this->CheckPassword($uname, $pword)) {
            throw new ValidationException ('The passed credentials are not valid.', array ($uname, $pword), 2);
        } else {
            return $this->userService->GetTicket($uname, $pword);
        }//else
    }//GetTicket
    
    /**
     * Checks if there exists a user account with the passed username.
     * @param string $uname The username which should be checked for existance.
     * @return bool <c>True</c> if the username is allready assigned; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsUser ($uname) {
        return $this->userService->ExistsUser ($uname);
    }//UserExists
    
    /**
     * Creates the a new user on the DuEPublico server, specified by
     * <c>$uname</c> and <c>$pword</c> and associates them with the
     * passed <c>$authorId</c>.
     * @param string $uanme The username of the newly created user account.
     * @param string $pword The password of the newly created user account.
     * @param int $authorId The unique identifier of the author with which the user account will be associated.
     * @return bool <c>True</c> if the user account didn't existed and was created sucesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreateUser ($uname, $pword, $authorId) {
        return $this->userService->CreateUser ($uname, $pword, $authorId);
    }//CreateUser
    
    /**
     * Deletes the DuEPublico user specified by the passed <c>$uanme</c>.
     * @param string $uname A valid Miless/MyCoRe username.
     * @return bool <c>True</c> if the username existed and was deleted succesfully, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteUser ($uname) {
        if ($this->userService->DeleteUser ($uname)) {
            return true;
        } else {
            throw new WebServiceException ('UserService', 'DeleteUser', 'Error while deleting user. Maybe the user specified by '.$uname.' does not exist.', $uname, 2);
        }//else
    }//DeleteUser
    
    /**
     * Returns an <c>array</c> of groups to which the passed username 
     * is assigned to on the DuEPublico Server. Available groups are:
     * <ul>
     *  <li>admins</li>
     *  <li>creators</li>
     *  <li>disshab</li>
     *  <li>osap</li>
     *  <li>submitters</li>
     * </ul>
     * @param string $uname The name of a user, registered at the DuEPublico server.
     * @return array The groups the user is assigned to.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetGroups ($uname) {
        if ($this->userService->ExistsUser($uname)) {
            return $this->userService->GetGroups ($uname);
        } else {
            throw new ValidationException ('User '.$uname.' does not exist.', $uname, 1);
        }//else
    }//GetGroups
    
    /**
     * Adds the user, specified by the passed username, to the
     * groups with in the passed <c>$groups array</c>. Possible
     * group values are:
     * <ul>
     *  <li>admins</li>
     *  <li>creators</li>
     *  <li>disshab</li>
     *  <li>osap</li>
     *  <li>submitters</li>
     * </ul>
     * @param string $uname A DuEPublico username.
     * @param array $groups An <c>array</c> of <c>string</c>s as specified above.
     * @return bool <c>True</c> if the user was added to the specified groups; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetGroups ($uname, $groups) {
        if (is_array($groups)) {
            $groupVal = $this->validationService->ValidateGroups($groups);
        } else {
            $groupVal = $this->validationService->ValidateGroup($groups);
        }//else
        
        if (!$this->userService->ExistsUser($uname)) {
            throw new ValidationException ('User '.$uname.' does not exist.', $uname, 1);
        } else if (!$groupVal) {
            throw new ValidationException ('Passed group(s) is/are not valid.', '', 2);
        } else {
            if ($this->userService->SetGroups ($uname, $groups)) {
                return true;
            } else {
                throw new WebServiceException ('UserServuce', 'ExistsUser', 'Error while processing username.', $uname, 1);
            }//else
        }//else
    }//SetGroups
    
    /**
     * Uses the function {@link CheckPassword CheckPassword} to validate
     * if <c>$uname</c> and <c>$pword</c> are valid credentials. If they
     * are correct the coresponding legal entity identifier is returned; 
     * otherwise <c>false</c> is returned.
     * @param string $uname The username
     * @param string $pword The password
     * @return string The legal entity id if credetials are correct otherwise an empty string is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function UserLogin($uname, $pword) {
        if (!$this->ExistsUser($uname)) {
            throw new ValidationException ('User '.$uname.' does not exist.', $uname, 1);
        } else if (!$this->CheckPassword ($uname, $pword)) {
            throw new ValidationException ('Passed credentials are not valid.', array ($uname, $pword), 2);
        } else {
            return $this->userService->UserLogin($uname, $pword);
        }//else
    }//UserLogin
     
    /**
     * Checks if <c>$uname</c> and <c>$pword</c> are a valid
     * credential pair.
     * @param string $uname The username
     * @param string $pword The password
     * @return bool <c>True</c> if <c>$uname</c> and <c>$pword</c> are valid, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckPassword ($uname, $pword) {
        return $this->userService->CheckPassword ($uname, $pword);
    }//ChekPassword
    
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
 
    /**
     * Returns the help buttons for a specific part of the modules' workflow
     * @param string $type There are the following possible values:
     * <ul>
     *  <li>query: Help buttons for the query page</li>
     *  <li>results: Help buttons for the result page</li>
     *  <li>upload: Helpbuttons for the upload page</li>
     *  <li>account: Help button for the login page</li>
     *  <li>newAcc: Help buttons for creating a new account</li>
     *  <li>backup: Help buttons for the backup module</li>
     * </ul>
     * @return array An <c>array</c> the status information of the method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetHelpButton ($type) {
        if ($this->validationService->ValidateHelpButtons($type)) {
            return $this->helpButtons->GetButtons($type);
        } else {
            throw new ValidationException ('HelpButtons type '.$type.' is not definied.', $type, 1);
        }//else
    }//GetHelpButton
 
    /**
     * Creates a <c>string</c> of the passed <c>array</c> that has 
     * the following structure:
     * <code><input type="hidden" name="[key]" value="[value]"/></code>
     * @param array $values Contains key value pairs as specified above
     * @return string A list of HTML hidden parameters in form of a <c>string</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateHiddenParams($values) {
        if (is_array($values)) {
            return $this->transformationService->CreateHiddenParams($values);
        } else {
            return '';
        }//else
    }//CreateHiddenParams
 
    //#########################################################################################
    //#########################################################################################
    //#########################################################################################
    
    /**
     * Removes the files that became obsolete; files that are required
     * during the workflow will download again.
     * @todo The modification information for all three classifications is not returned by the web service.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function RemoveObsoleteFiles () {
        require_once ($this->dirroot.'/lib/duepublico/webservice/duep_QueryService.php');
        
        $queryService = new QueryService ();
        $timeStamps = $queryService->CheckTimeStamps();
        /*
        //TODO: This three modifications are not returned by the web service
        $fileStamps['doRetrieveClassification&Id=FormatId'] = array ('date' => date('c', filemtime($this->formatIdTemp)),    'file' => $this->formatIdTemp);
        $fileStamps['doRetrieveClassification&Id=OriginId'] = array ('date' => date('c', filemtime($this->originIdTemp)),    'file' => $this->originIdTemp);
        $fileStamps['doRetrieveClassification&Id=TypeId'] = array ('date' => date('c', filemtime($this->typeIdTemp)),      'file' => $this->typeIdTemp);
        */
        $fileStamps['getRoles']        = array ('date' => date('c', filemtime($this->roleTemp)),        'file' => $this->roleTemp);
        $fileStamps['getSearchFields'] = array ('date' => date('c', filemtime($this->searchFieldTemp)), 'file' => $this->searchFieldTemp);
        $fileStamps['getLanguages']    = array ('date' => date('c', filemtime($this->languagesTemp)),   'file' => $this->languagesTemp);
        $fileStamps['doListOperators'] = array ('date' => date('c', filemtime($this->operatorsTemp)),   'file' => $this->operatorsTemp);
        
        foreach ($timeStamps as $timeStamp) {
            $stamp = $timeStamp->GetElementText ();
            $method = $timeStamp->GetAttributeValue ('method');
            if (isset($fileStamps[$method]) and
                $fileStamps[$method]['date'] < $stamp) {
                if (is_file ($fileStamps[$method]['file'])) {
                    unlink($fileStamps[$method]['file']);
                }//if
            }//if
        }//foreach   
    }//RemoveObsolate
 
    public function GetCourseBackupPopup ($courseId, $keySelectedOption = '') {
        $backups = $this->filesystemService->GetCourseBackups($courseId);
        
        $popup = $this->transformationService->CreatePopupFromArray($backups, 'backupPopup', ON_CHANGE, true, $keySelectedOption, false);
        if ($keySelectedOption != '') {
            $popup = $this->transformationService->ResetSelectedOption($popup, $keySelectedOption);
        }//else
        return $popup;
    }//GetCourseBackupPopup
 
    /**
     * Calls the getFieldTypes method of the QueryService web service
     * and creates popups for all the different types. The <code>array
     * $keySelectedOptions</code> is used to controll the selected of
     * the different popup options.
     * @param array $keySelectedOptions Controlls the selection of the different options
     * @return array <code>array</code> that contains poups, default values and names of the different types
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetOperatorPopup ($keySelectedOptions = '') {
        global $DUEP;
        $return = array();
        try {
            $return = $this->UnserializeObject($DUEP->operatorTemp);
        } catch (FileException $e) {
            $fields = $this->queryService->GetFieldTypes();
            
            $setDefault = false;
            if ($keySelectedOptions == '') {
                $setDefault = true;
            }//if
            
            foreach ($fields as $fieldType) {
                $popup = '<select name="OPERATOR" onChange="'.ON_CHANGE.'" value="GO">';
                $operators = $fieldType->GetChildren();
                foreach($operators['operator'] as $operator) {
                    $token = $operator->GetAttributeValue('token');
                    $default = $fieldType->GetAttributeValue('default');
                    $name = $fieldType->GetAttributeValue('name');
                    if ((($setDefault) and ($token == $default)) or 
                        (($keySelectedOptions != '') and
                         ($token == $keySelectedOptions[$fieldType]))) {
                        $popup .= '<option value="'.$token.'" selected>'.$token.'</option>';
                    } else {
                        $popup .= '<option value="'.$token.'">'.$token.'</option>';
                    }//else
                }//foreach
                $popup .= '</select>';
                $return[$name] = array ('default' => $default, 'popup' => $popup);
            }//foreach
            $this->SerializeObject($return, $DUEP->operatorTemp);
        }//catch
        return $return;
    }//GetOperatorPopup
 
    //######################################################################################
    //######################################################################################
    //######################################################################################
    
    /**
     * Checks if the passed <c>XMLElement</c> contains
     * a child called derivates. If there is such a child then the types 
     * of its children will be check:
     * <ul>
     *  <li>internal -> There is at least one file stored on the server</li>
     *  <li>external -> There is at least one link stored on the server</li>
     *  <li>abstract -> Neither a file nor a link is stored on the server</li>
     * </ul>
     * @param XMLElement $document The element that contains the informaton about a specific document
     * @return string A <c>string</c> that summerizes the document types stored under the this documents
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CreateDocTypeString ($document) {
        try {
            $doctypeString = '';
            $derivates = $document->GetChildXpath('/derivates/derivate');
            if ($derivates instanceof XMLElement) {
                $doctypeString = $derivates->GetAttributeValue('type');
            } else if (is_array($derivates)) {
                //Check the doctypes with boolean varibales because
                //every derivate contains such information and we want
                //to avoid things like: internal / internal / external / external
                $internal = false;
                $external = false;
                foreach ($derivates as $derivate) {
                    $type = $derivate->GetAttributeValue('type');
                    if ($type == 'internal') {$internal = true;} 
                    if ($type == 'external') {$external = true;}
                    if ($internal and $external) {
                        break;
                    }//if
                }//foreach
                //Finally add the doctypes
                if ($internal) {$doctypeString .= 'internal / ';}
                if ($external) {$doctypeString .= 'external / ';}
                //Remove last separator
                $doctypeString = substr($doctypeString, 0, -3);
            }//else if
            return $doctypeString;
        } catch (ChildNotFoundException $e) {
            //If there is no derivate child than the document does not contain
            //any derivates so it is an abstract document
            return 'abstract';
        }//catch
    }//CreateDocTypeString
    
    //######################################################################################
    //######################################################################################
    //######################################################################################
 
    /**
     * Returns an upload session key for the Java Applet if the user, specified by
     * <c>$uname</c> and <c>$pword</c> has writting access to the document specified
     * by <c>$documentId</c>.
     * @param string $uname The username who wants to upload a file.
     * @param string $pword The password of the user
     * @param int $documentId The unique identifier of the Miless/MyCoRe document
     * @param int $derivateId The unique identifier of the Miless/MyCoRe derivate that should contain the uploaded file
     * @param string $returnUrl The url that should be called by the Java-Applet after uploading the file.
     * @return string An upload session key if the user has the required access rights.
      * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetAppletSessionkey ($uname, $pword, $documentId, $derivateId, $returnUrl) {
        if (!$this->ExistDocument($documentId)) {
            throw new ValidationException ('Document specified by '.$documentId.' does not exist.', $documentId, 6);
        } else if (!$this->ExistsUser($uname)) {
            throw new ValidationException ('User '.$uname.' does not exist.', $uname, 1);
        } else if (!$this->CheckPassword($uname, $pword)) {
            throw new ValidationException ('Passed credentials are not valid.', array ($uname, $pword), 2);
        } else {
            $rights = $this->GetUserRights($documentId, $uname);
            if (!$rights) {
                throw new WebServiceException ('PermissionService', 'getPermissions', array ($uname, $pword, $documentId, $derivateId, $returnUrl) , 7);
            } else {
                if ($rights['write']) {
                    $sessionKey = $this->GetUploadSessionkey($documentId, $derivateId, $returnUrl.'&uploadDerivate='.$derivateId);
                    if (!$sessionKey) {
                        throw new WebServiceException ('FileService', 'startUpload', array ($uname, $pword, $documentId, $derivateId, $returnUrl) , 4);
                    } else {
                        return $sessionKey;
                    }//else 
                } else {
                    throw new ValidationException ('User '.$uname.' does not have write access for document '.$documentId.'.', array ($uname, $documentId) , 5);
                }//else
            
            }//else
        }//else
    }//GetAppletSessionkey
    
    /**
     * Checks if the passed userid is allready assigned. If it is not assigned
     * and the passed flag <c>$create</c> is <c>true</c> than the
     * DuEPublico user id will be created.
     * @param string $uname The DuEPublico userid that should be checked
     * @param string $pword The password for the userid mentioned before
     * @param integer $authorId The DuEPublico author id that should be associated with the user id
     * @param bool $create The flag controlls if an unassigned userid will be created (<c>true</c>) or not (<c>false</c>).
     * @return array An <c>array</c> that contains the stauts information of this method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateUseraccount ($uname, $pword, $authorId, $groups) {
        try {
            $this->validationService->ValidateUsername($uname);
        } catch (ValidationException $e) {
            throw new ValidationException ('Username '.$uname.' does not match Miless/MyCoRe reqirements.', $uname, 2);
        }//catch
        
        if ($this->ExistsUser($uname)) {
            throw new ValidationException ('Username '.$uname.' is already assigned.', $uname, 1);
        } else if (!$this->ExistsPerson($authorId)) {
            throw new ValidationException ('Legal entity specified by '.$authorId.' does not exist.', $authorId, 3);
        } else if ($this->CreateUser ($uname, $pword, $authorId)) {
            try {
                $groupRes = $this->SetGroups ($uname, $groups);
                return true;
            } catch (ValidationException $e) {
                $this->DeleteUser($uname);
                throw new ValidationException ('Could not set groups for user '.$uname.'.',  array ($uname, $groups) , 5);
            }//catch
        } else {
            throw new WebServiceException ('UserService', 'createUser', 'Could not create '.$uname.' user account.', array ($uname, $pword, $authorId, $groups) , 6);
        }//else
    }//CreateUserAccount

    /**
     * Returns the <c>CourseObject</c> instance for the Moodle course
     * specified by <c>$courseId</c>. If such an object does not exists
     * and the passed global <c>$authorId</c> is not empty than 
     * a new <c>CourseObject</c> instance will be created; otherwise
     * an {@link GlobalProfileNotSetException GlobalProfileNotSetException}
     * will be thrown.
     * @param integer $courseId The unique identifier of the Moodle course
     * @param integer $authorId The unique identifier of the global Moodle author account.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetCourseObject ($courseId, $authorId) {
        $courseObject = null;
        try {
            $courseObject = $this->ReadCourseObject($courseId);
        } catch (FileException $e) {
            if (($authorId !== '') and ($authorId > 0)) {
                $courseObject = $this->CreateCoursePrerequisits($courseId, $authorId);
            } else {
                throw new GlobalProfilNotSetException ();
            }//else
        }//catch
        return $courseObject;
    }//GetCourseObject

    /**
     * Copies the backup file, specified by <c>$filename</c>
     * to the Moodle publish directory {{@link $DUEP $DUEP->publishdir}
     * @param string $filename Name of the backup file which should be 
     *                         copied to the publish directory.
     * @param integer $courseId The unique identifier of the backuped Moodle course
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function PublishBackup ($filename, $courseId) {
        global $CFG, $DUEP;
    
        require_once($CFG->dirroot.'/lib/filelib.php');
        require_once($CFG->dirroot.'/backup/lib.php');
        $backupconfig = backup_get_config();
        
        if (($path = (string) $backupconfig->backup_sche_destination) == '') {
            $path = $CFG->dataroot.'/'.$courseId.'/backupdata';
        }//if
        $this->filesystemService->CreateDirectories ($DUEP->publishdir.'/'.$filename);
        if (!copy ($path.'/'.$filename, $DUEP->publishdir.'/'.$filename)) {
            throw new Exception ('Error while publising archive '.$filename);
        }//if
    }//PublishBackup

    public function RemoveBackup ($filename) {
        global $DUEP;
        return unlink ($DUEP->publishdir.'/'.$filename);
    }//RemoveBackup

    public function StoreGlobalAuthorProfile ($authorProfile) {
        if ($authorProfile instanceof AuthorProfile) {
            $serObject = $authorProfile->SerializeObject();
            $this->filesystemService->WriteFile($serObject, $this->globalAuthorPath);
            return true;
        } else {
            throw new InvalidArgumentException ('DataLayerFacade [StoreGlobalAuthorProfile]: Passed invalid object to StoreGlobalAuthorProfile method.');
        }//else
    }//StoreGlobalAuthorProfile

    public function LoadGlobalAuthorProfile () {
        global $CFG;
        require_once ($CFG->dirroot.'/lib/duepublico/duep_authorprofile.php');
        $serObject = $this->filesystemService->ReadFileContent($this->globalAuthorPath);
        $authorProfile = AuthorProfile::GetAuthorProfile ();
        $authorProfile->UnserializeObject($serObject);
        return $authorProfile;
    }//LoadGlobalAuthorProfile

    /**
     * Returns the unique identifier of the global Moodle author
     * account.
     * @return integer The unique identifier of the global Moodle author account.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetGlobalAuthorId () {
        try {
            $serObject = $this->filesystemService->ReadFileContent($this->globalAuthorPath);
            $globalAuthor = AuthorProfile::GetAuthorProfile ();
            if ($globalAuthor->UnserializeObject($serObject)) {
                return $globalAuthor->authorId;
            } else {
                throw new GlobalProfilNotSetException ('Gloabl profile is not set.');
            }//else
        } catch (FileException $e) {
            throw new GlobalProfilNotSetException ('Gloabl profile is not set.');
        }//catch
    }//GetGlobalAuthorId

    private function CreateCoursePrerequisits ($courseId, $authorId) {
        global $CFG;
        $title = $this->GetCourseDocTitle($courseId);
        $documentId = $this->GetCourseDocumentId($title);
        if ($documentId == false) {
            $docParams = array ();
            $docParams['language']        = 'de';
            $docParams['title']           = $title;
            $docParams['authorId']        = $authorId;
            $docParams['role']            = 'author';
            $docParams['keyword']         = 'Moodle, Backup, Moodle Interface';
            $docParams['erstelltDatum']   = date("d.m.Y");
            $docParams['geaendertDatum']  = date("d.m.Y");
            $docParams['typeid']          = 'a.10';
            $docParams['formatid']        = '5';
            $docParams['originid']        = 'LV';
            $documentId = $this->CreateDocument($docParams);
            
            $readUname = 'mr-'.$this->GeneratePassword (4).'-'.$courseId;
            $readPword = $this->GeneratePassword ();
            $writeUname = 'mw-'.$this->GeneratePassword (4).'-'.$courseId;
            $writePword = $this->GeneratePassword ();
            
            $serObject = $this->filesystemService->ReadFileContent($this->globalAuthorPath);
            $globalAuthor = AuthorProfile::GetAuthorProfile ();
            if (!$globalAuthor->UnserializeObject($serObject)) {
                throw new GlobalProfilNotSetException ('Gloabl profile is not set.');
            }//if
            
            $this->CreateUser($readUname, $readPword, $authorId);
            $this->CreateUser($writeUname, $writePword, $authorId);
            $this->SetGroups($writeUname, array ('creators'));
            $perms = array ();
            $perms[] = array ('w' => $writeUname);
            $perms[] = array ('r' => $readUname);
            $perms[] = array ('r' => $globalAuthor->uname);
            $this->SetPermissions($documentId, $perms);
            
            require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_courseobject.php');
            $courseObject = new CourseObject ();
            $courseObject->SetReadUser($readUname,$readPword);
            $courseObject->SetWriteUser ($writeUname, $writePword);
            $courseObject->courseDocumentTitle = $title;
            $courseObject->documentId = $documentId;
            $courseObject->courseId = $courseId;
            
            $this->StoreCourseObject($courseObject);
            return $courseObject;
        } else {
            throw new CourseDocumentExistsException ('Course document already exists.', $documentId); 
        }//else
    }//CreateCoursePrerequisits
 
    public function ReadQueryResults ($queryParams, $page, $resultFile) {
        if ($page == -1) {
            $xml = $this->DoXmlQuery($queryParams);
            $numHits = $xml->GetAttributeValue('numHits');
            if ($numHits == 0) {
                throw new NoSearchResultsException ();
            } else {
                $this->filesystemService->WriteFile(serialize($xml), $resultFile);
                return $xml;
            }//else
        } else {
            return $this->filesystemService->ReadResultTemp($resultFile);
        }//else
    }//ReadQueryResults
 
    /**
     * Reads the <c>CourseObject</c> of the Moodle course
     * specified by the passed course identifier (<c>$courseId</c>).
     * @param integer $courseId The unique identifier of the Moodle 
     *                          course for which the <c>CourseObject</c> 
     *                          should be returned.
     * @return CourseObject The loaded <c>CourseObject</c> instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ReadCourseObject ($courseId) {
        global $CFG;
        if (($courseId !== '') and ($courseId > 0)) { 
            $courseObjectPath = $CFG->dataroot.'/'.$courseId.'/temp/courseObject.ser';
            $serObject = $this->filesystemService->ReadFileContent($courseObjectPath);
            require_once($CFG->dirroot.'/lib/duepublico/datalayer/duep_courseobject.php');
            $courseObject = new CourseObject ();
            $courseObject->UnserializeObject($serObject);
            return $courseObject;
        } else {
            throw new InvalidArgumentException ('Passed invalid parameter to ReadCourseObject function.');
        }//else
    }//ReadCourseObject
     
    /**
     * Serializes the passed <c>CourseObject</c> instance and
     * stores it within Moodle course workspace it belongs to.
     * @param CourseObject $courseObject The <c>CourseObject</c> that should be stored
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    public function StoreCourseObject ($courseObject) {
        global $CFG;
        if ($courseObject instanceof CourseObject) {
            $courseId = $courseObject->courseId;
            $courseObjectPath = $CFG->dataroot.'/'.$courseId.'/temp/courseObject.ser';
            $this->filesystemService->CreateDirectories($courseObjectPath);
            $serObject = $courseObject->SerializeObject();
            if ($this->filesystemService->WriteFile($serObject, $courseObjectPath)) {
                return true;
            } else {
                throw new FileCacheException ('Could not write CourseObject to '.$courseObjectPath, $courseObjectPath);
            }//else
        } else {
            throw new InvalidArgumentException ('Passed invalid parameter to StoreCourseObject function.');
        }//else
    }//StoreCourseObject
 
    //######################################################################################
    //######################################################################################
    //######################################################################################
    
    /**
     * Returns the document title for a given course identifier.
     * @return string The course document title of a Moodle course specified by <c>$courseId</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function GetCourseDocTitle ($courseId) {
        global $DUEP;
        return $DUEP->DocumentTitle.' '.$courseId;
    }//GetCourseDocTitle
    
    /**
     * Adds a slash to the end of the passed path.
     * @param string $path A path with or without a slash at the end.
     * @return string The {@link $path $path} passed to this method, to which a slash was added if not allready existend.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function AddSlash ($path) {
        if ($this->EndsWithAny ($path, array ('/', '\\'))) {
            return $path;
        } else {
            return $path.'/';
        }//else
    }//AddSlash
     
    /**
     * Checks if the passed <c>$string</c> ends with
     * one of the <c>string<c>s in the passed 
     * <c>array</c> <c>$endings</c>.
     * @param string $string The <c>string</c> to be check.
     * @param string $endings An <c>array</c> of <c>string</c>s used for the comparison.
     * @return bool <c>True</c> if <c>$string</c> ends with one of the <c>string</c>s in <c>$endings</c>; oterhwise <c>false</c> is returned.
     */
    public function EndsWithAny ($string, $endings) {
        foreach ($endings as $end) {
            if ($this->EndsWith($string, $end)) {
                return true;
            }//if
        }//foreach
        return false;
    }//EndsWithAny 
     
    /**
     * Checks if the passed <c>$string</c> ends with
     * <c>$ending</c>.
     * @param string $string The <c>string</c> to be check.
     * @param string $ending Another <c>string</c> used for the comparison.
     * @return bool <c>True</c> if <c>$string</c> ends with <c>$ending</c>; oterhwise 
     *              <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function EndsWith ($string, $ending) {
       $len = strlen($ending);
       $original = substr($string, strlen($string) - $len);
       return $original == $ending;
    }//EndsWith
 
    /**
     * Generates a password of a length specified by <c>$length</c> whereas
     * 6 is the default value. The characters that are used for generating 
     * the password are specified by the passed <c>$level</c> (default is 0).
     * Possible value levels are:
     * <li>0 for "0123456789abcdfghjkmnpqrstvwxyz"</li>
     * <li>1 for "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"</li>
     * <li>2 for "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/"</li>
     * @param integer $length The length of the password.
     * @param integer $level Flag that controlls which characters should be used 
     *                       for generating the password.
     */
    private function GeneratePassword ($length=6, $level=0) {
        if (($level < 0) and ($level > 2)) {
            $level = 0;
        }//if
        
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));
    
        $chars[0] = '0123456789abcdfghjkmnpqrstvwxyz';
        $chars[1] = '0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars[2] = '0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/';
    
        $password = "";
        $counter  = 0;
        while ($counter < $length) {
            $next = substr($chars[$level], rand(0, strlen($chars[$level])-1), 1);
            if (!strstr($password, $next)) {
                $password .= $next;
                $counter++;
            }//if
       }//while
       return $password;
    }//GeneratePassword
 
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
    
 
 }//Class: DataLayerFacade
?>
