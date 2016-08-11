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
 *           FileService web service client implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_filesystemservice.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_FileService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');
   
class fileservice_test extends UnitTestCase {
    
    private $fileService;
    private $docParams;
    private $documentService;
    private $filesystemService;
    
    function setUp () {
        $this->fileService = new FileService ();
        $this->documentService = new DocumentService ();
        $this->filesystemService = FilesystemService::GetFileSystemService();
        $this->docParams = array ('language' => 'de', 'title' => 'Upload Test Document',
        'title2' => 'Upload Test Document2', 'authorId' => '12231', 'role' => 'author',
        'description' => 'This document is used for testing the upload methods.', 'keyword' => 'Moodle, Test, Upload',
        'geaendertDatum' => '29.04.2009', 'gueltigVonDatum' => 'tt.mm.jjjj', 'gueltigBisDatum' => 'tt.mm.jjjj',
        'typeid' => 'a.10', 'formatid' => '5', 'originid' => 'LV');
        $this->docParams['erstelltDatum'] = date ('d.m.y');
    }//setUp
    
    function tearDown () {
        $this->fileService = null;
        $this->documentService = null; 
        $this->docParams = null;
    }//tearDown
    
    function testCreateExistDelete () {
        $documentId = $this->CreateDocument();
        $derivateId = $this->CreateDerivate($documentId);
        $this->assertTrue($this->fileService->DeleteFileDerivate($derivateId));
        $this->DeleteDocument($documentId);
    }//testCreateExistDelete
    
    function testStoreFilePDF () {
        global $CFG, $DUEP;
        
        $file = 'RDF.pdf';
        $target = $CFG->dirroot.'/lib/duepublico/webservice/simpletest/external/';
        $this->filesystemService->CopyFile($target.$file, $this->filesystemService->AddSlash($DUEP->publishdir).$file);
        
        $documentId = $this->CreateDocument ();
        $derivateId = $this->CreateDerivate($documentId);
        
        $md51 = $this->fileService->StoreFile(PUB1, $file,  $derivateId);
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB1, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB2, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB3, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB4, $file, $derivateId));
       
        $this->DeleteDerivate($derivateId);
        $this->DeleteDocument($documentId);
    }//testStoreFilePDF
    
    function testStoreFileDOC () {
        global $CFG, $DUEP;
        $file = 'Z3950.doc';
        $target = $CFG->dirroot.'/lib/duepublico/webservice/simpletest/external/';
        $this->filesystemService->CopyFile($target.$file, $this->filesystemService->AddSlash($DUEP->publishdir).$file);
        
        $documentId = $this->CreateDocument();
        $derivateId = $this->CreateDerivate($documentId);
        
        $md51 = $this->fileService->StoreFile(PUB1, $file,  $derivateId);
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB1, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB2, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB3, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB4, $file, $derivateId));
       
        $this->DeleteDerivate($derivateId);
        $this->DeleteDocument($documentId);
    }//testStoreFileDoc
    
    function testStoreFileZIP () {
        global $CFG, $DUEP;
        $file = 'XRD.zip';
        $target = $CFG->dirroot.'/lib/duepublico/webservice/simpletest/external/';
        $this->filesystemService->CopyFile($target.$file, $this->filesystemService->AddSlash($DUEP->publishdir).$file);
        
        $documentId = $this->CreateDocument ();
        $derivateId = $this->CreateDerivate($documentId);
        
        $md51 = $this->fileService->StoreFile(PUB1, $file,  $derivateId);
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB1, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB2, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB4, $file, $derivateId));
        $this->assertEqual ($md51, $this->fileService->StoreFile(PUB3, $file, $derivateId));
         
        $derivateData = $this->fileService->GetDerivateData($derivateId);
        
        $mainFile = $derivateData->GetChildXpath('/derivate/files')->GetAttributeValue('main');
        $this->assertEqual($file, $mainFile);
        $derivateInfo = $this->fileService->FilenameByDerivateId ($derivateId);
        
        $this->assert(new ArrayHasKeyExpectation($derivateInfo), 'mainfile');
        $this->assert(new ArrayHasKeyExpectation($derivateInfo), 'filecount');
        $this->assert(new ArrayHasKeyExpectation($derivateInfo), 'filepath');
        
        $this->assertEqual ($mainFile, $derivateInfo['mainfile']);
        $this->assertEqual (1, $derivateInfo['filecount']);
        $this->DeleteDerivate($derivateId);
        $this->DeleteDocument($documentId);
    }//testStoreFileZIP
    
    function testStoreZipFileContents () {
        $file = 'XRD.zip';
        
        $documentId = $this->CreateDocument();
        
        $derivate4Id = $this->CreateDerivate($documentId);
        $derivate5Id = $this->CreateDerivate($documentId);
        $derivate6Id = $this->CreateDerivate($documentId);
        $derivate7Id = $this->CreateDerivate($documentId);
        
        $this->assertEqual (3, $this->fileService->StoreZipFileContents(PUB1, $file,  $derivate4Id));
        $this->assertEqual (3, $this->fileService->StoreZipFileContents(PUB2, $file,  $derivate5Id));
        $this->assertEqual (3, $this->fileService->StoreZipFileContents(PUB3, $file,  $derivate6Id));
        $this->assertEqual (3, $this->fileService->StoreZipFileContents(PUB4, $file,  $derivate7Id));
        
        $this->DeleteDerivate($derivate4Id);
        $this->DeleteDerivate($derivate5Id);
        $this->DeleteDerivate($derivate6Id);
        $this->DeleteDerivate($derivate7Id);
        
        $this->DeleteDocument($documentId);
    }//testStoreZipFileContents
    
    private function CreateDerivate ($documentId) {
        $derivateId = $this->fileService->CreateFileDerivate($documentId);
        $this->assertNotNull($derivateId);
        $this->assertTrue($this->fileService->ExistsFileDerivate($derivateId));
        return $derivateId;
    }//CreateDerivate  
      
    private function CreateDocument () {
        $documentId = $this->documentService->CreateDocument($this->docParams);
        $this->assertNotNull ($documentId);
        $this->assertNotEqual ($documentId, '');
        $this->assertTrue ($this->documentService->ExistDocument ($documentId));
        return $documentId;    
    }//CreateDocument
    
    private function DeleteDocument ($documentId) {
        $this->assertTrue ($this->documentService->DeleteDocument($documentId));
        $this->assertFalse ($this->documentService->ExistDocument ($documentId));
    }//DeleteDocument
    
    private function DeleteDerivate ($derivateId) {
        $this->assertTrue ($this->fileService->DeleteFileDerivate($derivateId));
        $this->assertFalse ($this->fileService->ExistsFileDerivate($derivateId));
    }//DeleteDerivate
      
    function testUploadSessionKey () {
        $returnUrl1 = 'www.moodle.de/mod/resource/type/fileupload/duep_uploadmain.php';
        $returnUrl2 = 'http://www.moodle.de/mod/resource/type/fileupload/duep_uploadmain.php';
        
        $derivate1 = $this->fileService->CreateFileDerivate(15315);
        $derivate2 = $this->fileService->CreateFileDerivate(15315);
        $this->assertEqual ($derivate1 + 1, $derivate2);
        
        $this->assertTrue ($this->fileService->ExistsFileDerivate($derivate1));
        $this->assertTrue ($this->fileService->ExistsFileDerivate($derivate2));
        
        $uploadKey1 = $this->fileService->GetUploadSessionkey(15315, $derivate2, $returnUrl1);
        $uploadKey2 = $this->fileService->GetUploadSessionkey(15315, $derivate1, $returnUrl2);
        $this->assertNotNull ($uploadKey1);
        $this->assertNotNull ($uploadKey2);
        $this->assertFalse ($uploadKey1 == $uploadKey2);
        
        $this->DeleteDerivate($derivate1);
        $this->DeleteDerivate($derivate2);
    }//testUploadSessionkey
    
}//fileservice_test
?>
