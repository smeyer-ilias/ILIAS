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
 
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_WebService.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLElement.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLExceptions.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/exceptions/duep_Exceptions.php');

 class FileService extends WebService {
    
    /**
     * Creates a new instance of the FileService web service client.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    public function __construct () {
        parent::__construct ('FileService');
    }//Constructor
     
    /**
     * Checks if the derivate, specified by the passed <c>$derivateId</c>
     * exists.
     * @param int $derivateId A possible identifier of a derivate.
     * @return bool <c>True</c> if a derivate with the passed unique identifier exists, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsFileDerivate ($derivateId) {
       $xml = $this->PerformRequest ('derivateExists', array ('derivateID' => array ('content' => $derivateId, 'type' => 'int')));
       if ($xml->GetElementText () == 'true') {
           return true;
       } else {
           return false;
       }//else
    }//ExistsFileDerivate
     
    /**
     * Deletes the derivate specified by the passed unique identifier.
     * @param int $derivateId The unique identifier of the derivate that should be deleted.
     * @return bool <c>True</c> if the derivate was removed succesfully, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteFileDerivate ($derivateId) {
       return $this->PerformRequest ('deleteDerivate', array ('derivateID' => array ('content' => $derivateId, 'type' => 'int')), 'no');
    }//DeleteFilDerivate
     
     /**
     * Creates a file derivate within a specific DuEpublico document, specified
     * by <c>$documentId</code>.
     * @param array $documentId The unique identifier of the document that shoul contain the derivate.
     * @return int The unique identifier of the created derivate
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateFileDerivate($documentId) {
        $xml = $this->PerformRequest ('createFileDerivate', array ('documentID' => array ('content' => $documentId, 'type' => 'int')));
        return $xml->GetElementText ();
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
        $params = array ('derivateID' => array ('content' => $derivateId, 'type' => 'int'),
                         'path' => array ('content' => $filename, 'type' => 'string'), 
                         'uri' => array ('content' => $this->CreateUploadUri($moodlePublish, $filename), 'type' => 'string'));
        $xml = $this->PerformRequest('storeFile', $params);
        return $xml->GetElementText ();
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
        $params = array ('derivateID'=> array ('content' => $derivateId, 'type' => 'int'),
                         'uri' => array ('content' => $this->CreateUploadUri($moodlePublish, $filename), 'type' => 'string'));
        $xml = $this->PerformRequest('storeZipFileContents', $params);
        return $xml->GetElementText ();
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
    public function GetUploadSessionkey ($documentId, $derivateId, $returnUrl) {
        $params = array ('documentID' => array ('content' => $documentId, 'type' => 'int'),
                         'derivateID' => array ('content' => $derivateId, 'type' => 'int'), 
                         'returnURL'  => array ('content' => $this->Amps($this->CheckUrl($returnUrl)), 'type' => 'string'));
        $xml = $this->PerformRequest('startUpload', $params);
        return $xml->GetElementText ();
    }//GetUploadSessionKey
     
    /**
     * Returns a Streaming Media URL
     * <code>
     * http://muvin.uaruhr.de/services/FileService?
     *  method=getDeliveryURL
     *  &userName=XXX
     *  &password=XXX
     *  &derivateID=202
     *  &path=von_einander_lernen_1.flv
     *  &protocol=rtmp
     * </code>
     * @param string $username MUVIN username (read access)
     * @param string $password MUVIN password (read access)
     * @param int $derivateId A unique identifier of the derivate
     * @param string $path to the relative path of the requested file within the derivate 
     * @param string $protocol either "binary" or a streaming protocol like "rtsp", "rtmp", "mms"
     * @return a URL that contains a ticket to deliver the requested content to client browser or embedded players
     * @author Jan Rocho <jan.rocho [at] fh-dortmund.de> 
     */ 
     public function GetStreamingURL ($username, $password, $derivateId, $path, $protocol)
     {
        $params = array ('username' => array('content' => $username, 'type' => 'string'),
                         'password' => array('content' => $password, 'type' => 'string'),
                         'path' => array('content' => $path, 'type' => 'string'),
                         'derivateID' => array('content' => $derivateId, 'type' => 'int'),
                         'protocol' => array('content' => $protocol, 'type' => 'string'));
                         
        $xml = $this->PerformRequest('getDeliveryURL', $params);
        return $xml->GetElementText ();
     }//GetStreamingURL
     
    /**
     * Calls the <c>getDerivateData</c> with the passed identifier
     * and returns the web serivce's reponse.
     * @param int $derivateId The DuEPublico derivate identifier.
     * @return XmlElement The web service's reponse in form of a <c>XmlElement</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetDerivateData ($derivateId) {
        return $this->PerformRequest('getDerivateData', array ('derivateID' => array ('content' => $derivateId, 'type' => 'int')));
    }//GetDerivateData
     
    /**
     * Returns the filepath of the main file or the only file of
     * the DuEPublico derivate, specified by the passed identifier.
     * @param int $derivateId The unique identifier of a DuEPublico derivate
     * @return string The filename of the main or the only file of the derivate.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    public function FilenameByDerivateId ($derivateId) {
        $ret = '';
        $xml = $this->GetDerivateData($derivateId);
        
        try {
            $ret['filecount'] = $xml->GetChildXpath('/derivate/files')->GetNumberOfChildren();
            
            if ($ret['filecount'] == 0) {
                throw new UploadFailedException ($derivateId);
            }//if
            
            try {
                $ret['mainfile'] = $xml->GetChildXpath('/derivate/files')->GetAttributeValue('main');
            } catch (AttributeNotFoundException $e) {
                $ret['mainfile'] = '';    
            }
            
            if ($ret['filecount'] == 1) {
                $ret['filepath'] = $xml->GetChildXpath('/derivate/files/file/path')->GetElementText();
            }//if
            return $ret;
        } catch (Exception $e) {
            throw new UploadFailedException ($derivateId);
        }//catch
    }//FilenameByDerivateId

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
    
}//Class: FileService
?>
