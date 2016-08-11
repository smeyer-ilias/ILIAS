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
 * @abstract This file contains a test client for the Miless/MyCoRe 
 *           QueryService web service client implementation.
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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

@set_time_limit(0);     

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_QueryService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PermissionService.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_filesystemservice.php');
     
class queryservice_test extends UnitTestCase {
    
    private $queryService;
    private $userService;
    private $documentService;
    private $permissionService;
    private $filesystemService;        
        
        
    public function setUp () {
        $this->queryService = new QueryService ();
        $this->userService = new UserService ();
        $this->documentService = new DocumentService ();
        $this->permissionService = new PermissionService ();
        $this->filesystemService = FilesystemService::GetFileSystemService();
    }//setUp
   
    public function tearDown () {
        $this->queryService = null;
        $this->documentService = null;
        $this->userService = null;
        $this->permissionService = null;  
    }//tearDown
   
    function testDoXmlQueryName () {
        $searchQuery = array ('connPopup' => 'AND', 'maxResults' => 20, 
                              'name' => 'Heusinger',
                              'typeidoperator' => 'contains');
        $xml = $this->queryService->DoXmlQuery($searchQuery);
        $this->assertTrue ($xml->GetNumberOfChildren () <= 20);
    }//testDoXmlQueryName
   /*
    function testDoXmlQueryNoResults () {     
        $searchQuery = array ('connPopup' => 'AND', 'maxResults' => 20, 
                              'name' => 'xederdeeeeeeeerdfd',
                              'typeidoperator' => 'contains');
        $xml = $this->queryService->DoXmlQuery($searchQuery);
        $this->assertEqual (0, $xml->GetNumberOfChildren ());
    }//testDoXmlQueryNoResults
    
    function testDoXmlQueryKeywords () {    
        $searchQuery = array ('connPopup' => 'AND', 'maxResults' => 30, 'keywords' => 'Moodle',
                              'keywordsoperator' => 'contains');
        $xml = $this->queryService->DoXmlQuery($searchQuery);
        $this->assertEqual (30, $xml->GetNumberOfChildren ());
    }//testDoXmlQueryKeywords
     
           
    function testDoXmlQueryDate () {     
        $searchQuery = array ('maxResults' => 30, 'connPopup' => 'AND', 
                              'date' => 'datecreation', 'datum' => '12.12.2008', 'operatorDatum' => '>');
        $xml = $this->queryService->DoXmlQuery($searchQuery);
        $this->assertEqual (30, $xml->GetNumberOfChildren ());
        
        / *
        print_r($xml);
        * /
        / *
        $searchQuery['typeid'] = 5;
        
        $searchQuery['originid'] = 
        $searchQuery['title'] =
        $searchQuery['formatid'] =
        $searchQuery['keywords'] =
        $searchQuery['datemodified'] =
        $searchQuery['datesubmitted']=
        $searchQuery['dateaccepted'] =
        $searchQuery['legalentityid']=
        $searchQuery['classifcateg'] =
        $searchQuery['datevalidfrom']=
        $searchQuery['datevalidto'] =
        * /
    }//testDoXmlQueryDate
   */
   
    function testGetTimeStamp () {
        $timeStampXml = $this->queryService->CheckTimeStamps();
        
        $this->assertTrue (is_array($timeStampXml));
        
        $methods = array ('getLanguages', 'getRoles', 'getFieldTypes', 'getSearchFields', 
                          'doListMetadataFormats', 'doListOperators');
        $services = array ('DocumentService', 'QueryService', );
        
        foreach ($timeStampXml as $timeStamp) {
            $this->assertTrue ($timeStamp instanceof XMLElement);
            $methodName = $timeStamp->GetAttributeValue('method');
            $serviceName = $timeStamp->GetAttributeValue('service');
            $this->assertNotNull ($timeStamp->GetContent());
            $this->assert(new ArrayHasValueExpectation($methods), $methodName);
            $this->assert(new ArrayHasValueExpectation($services), $serviceName);
        }//foreach
    }//testGetTimeStamp
    
    function testGetTimeStampArray () {
        $array = $this->queryService->CheckTimeStampsAsArray();
        $this->assertEqual ($array['getRoles'],'2009-04-17T09:30:47.406Z');
        $this->assertEqual ($array['getLanguages'],'2009-04-17T09:30:47.640Z');
        $this->assertEqual ($array['getFieldTypes'],'2009-04-17T09:30:47.656Z');
        $this->assertEqual ($array['getSearchFields'],'2009-04-17T09:30:47.500Z');
        $this->assertEqual ($array['doListOperators'],'2009-04-20T13:48:43.765Z');
        $this->assertEqual ($array['doListMetadataFormats'],'2009-04-20T13:48:43.734Z');
    }//testGetTimeStampArray
    
    function testDoUploadZIP () {
        global $CFG, $DUEP;
        $file = 'external.zip';
        $target = $CFG->dirroot.'/lib/duepublico/webservice/simpletest/external/';
        $this->filesystemService->CopyFile($target.$file, $this->filesystemService->AddSlash($DUEP->publishdir).$file);
        
        $documentId = $this->CreateDocument ();
        $this->CreateUser ('qtest', 'qtest', 12231, $documentId, array ('creators'));
        $this->DoUpload ($file, 'qtest', 'qtest', $documentId);
        $this->DeleteUser ('qtest');
        $this->DeleteDocument ($documentId);
    }//testDoUploadZIP
    
    function testGetCourseDocumentId () {
        $this->assertEqual ('', $this->queryService->GetCourseDocumentId("This title doesn't exist"));
        $this->assertEqual ('15315', $this->queryService->GetCourseDocumentId("Arbeitsteilung: Transparente Integration eines institutionellen Dokumenten- und Publikationsservers in Moodle"));
        $this->assertEqual (15315, $this->queryService->GetCourseDocumentId("Arbeitsteilung: Transparente Integration eines institutionellen Dokumenten- und Publikationsservers in Moodle"));  
    }//testGetCourseDocumentId
    
    private function CreateUser ($userName, $password, $authorId, $documentId, $group) {
        $this->userService->DeleteUser($userName);
        $this->assertTrue ($this->userService->CreateUser($userName, $password, $authorId));
        $this->assertTrue ($this->userService->ExistsUser($userName));
        $this->assertTrue ($this->permissionService->SetPermissions($documentId, array ('w' => $userName)));
        $this->assertTrue ($this->userService->SetGroups($userName, $group));
    }//CreateUser
    
    private function DeleteUser ($userName) {
        $this->assertTrue ($this->userService->DeleteUser($userName));
        $this->assertFalse ($this->userService->ExistsUser($userName));
    }//DeleteUser
    
    private function DeleteDocument ($documentId) {
        $this->assertTrue ($this->documentService->DeleteDocument($documentId));
        $this->assertFalse ($this->documentService->ExistDocument($documentId));
    }//DeleteDocument
    
    private function DoUpload ($file, $user, $password, $documentId) {
        $this->assertTrue ($this->queryService->DoUpload(PUB1, $file,  $user, $password, $documentId));
        $this->assertTrue ($this->queryService->DoUpload(PUB2, $file,  $user, $password, $documentId));
        $this->assertTrue ($this->queryService->DoUpload(PUB3, $file,  $user, $password, $documentId));
        $this->assertTrue ($this->queryService->DoUpload(PUB4, $file,  $user, $password, $documentId));
    }//DoUpload
    
    private function CreateDocument () {
        $docParams['language']        = 'de';
        $docParams['title']           = 'Upload Test Document';
        $docParams['title2']          = 'Upload Test Document2';
        $docParams['authorId']        = '12231';
        $docParams['role']            = 'author';
        $docParams['description']     = 'This document is used for testing the upload methods.';
        $docParams['keyword']         = 'Moodle, Test, Upload';
        $docParams['erstelltDatum']   = date ('d.m.y');
        $docParams['geaendertDatum']  = '29.04.2009';
        $docParams['gueltigVonDatum'] = 'tt.mm.jjjj'; 
        $docParams['gueltigBisDatum'] = 'tt.mm.jjjj';
        $docParams['typeid']          = 'a.10';
        $docParams['formatid']        = '5';
        $docParams['originid']        = 'LV';
        
        $documentId = $this->documentService->CreateDocument($docParams);
        $this->assertNotNull($documentId);
        $this->assertTrue ($this->documentService->ExistDocument($documentId));
        return $documentId;
    }//CreateDocument
    
}//queryservice_test
?>
