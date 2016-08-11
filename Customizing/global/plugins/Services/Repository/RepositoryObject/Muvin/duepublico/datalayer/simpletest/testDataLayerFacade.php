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
 * @abstract Test client for the <c>DataLayerFacade</c> class implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/duep_objectfactory.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_datalayerfacade.php');
require_once ($CFG->dirroot.'/lib/duepublico/exceptions/duep_Exceptions.php');
define ('MILESS_DIR', 'E:\own\programming\work\miless');
define ('TEMP_DIR', 'E:\temp');

class testDataLayerFacade extends UnitTestCase {

    private $dataLayerFacade;
    private $docParams;
    private $invalidDocParams;

    public function setUp () {
//        $this->BackupMilessFiles();
//        $this->CreateTestTempFiles();
        $factory = ObjectFactory::GetFactory ();
        $this->dataLayerFacade = $factory->BuildDataLayer();
        $this->invalidDocParams = array ();
        $this->docParams = array ();
        $this->docParams['language']        = 'de';
        $this->docParams['title']           = 'Moodle course document 2';
        $this->docParams['title2']          = 'Moodel test environment';
        $this->docParams['authorId']        = '12231';
        $this->docParams['role']            = 'author';
        $this->docParams['description']     = 'This is a moodle test environment for testing the DuEPublico interface';
        $this->docParams['keyword']         = 'Moodle, Test';
        $this->docParams['erstelltDatum']   = 'tt.mm.jjjj';
        $this->docParams['geaendertDatum']  = 'tt.mm.jjjj';
        $this->docParams['gueltigVonDatum'] = 'tt.mm.jjjj'; 
        $this->docParams['gueltigBisDatum'] = 'tt.mm.jjjj';
        $this->docParams['typeid']          = 'a.10';
        $this->docParams['formatid']        = '5';
        $this->docParams['originid']        = 'LV';
        $this->docParams['uname']           = 'marcel';
    }//setUp

     public function testGetPopups () {
        echo $this->dataLayerFacade->GetLanguagePopup('German');
        echo $this->dataLayerFacade->GetClassificationPopup ('type');
        echo $this->dataLayerFacade->GetClassificationPopup ('origin');
        echo $this->dataLayerFacade->GetClassificationPopup ('format');
        
    }//testLanguages

    public function tearDown () {
        $this->dataLayerFacade = null;
        $this->docParams = array ();
//        $s = MILESS_DIR.'\config\searchfields.xml';
//        $r = MILESS_DIR.'\config\roles.xml';
//        $l = MILESS_DIR.'\config\languages.xml';
//        $f = MILESS_DIR.'\config\fieldtypes.xml';
//        
//        $this->assertTrue(unlink ($s));
//        $this->assertTrue(unlink ($r));
//        $this->assertTrue(unlink ($l));
//        $this->assertTrue(unlink ($f));
//        
//        $this->assertFalse (is_file($s));
//        $this->assertFalse (is_file($r));
//        $this->assertFalse (is_file($l));
//        $this->assertFalse (is_file($f));
//        
//        $this->RestoreMilessBackups ();
//        
//        unlink (TEMP_DIR.'\formatIdTemp.ser');
//        unlink (TEMP_DIR.'\originIdTemp.ser');
//        unlink (TEMP_DIR.'\typeIdTemp.ser');
//        unlink (TEMP_DIR.'\roleIdTemp.ser');
//        unlink (TEMP_DIR.'\searchFieldTemp.ser');
//        unlink (TEMP_DIR.'\languagesTemp.ser');
//        unlink (TEMP_DIR.'\operatorsTemp.ser');
    }//tearDown

    function testGetPermissionLabel () {
        
    }//testGetPermissionLabel
    
    function testGetCourseDocumentId () {
        $this->assertFalse ($this->dataLayerFacade->GetCourseDocumentId ('Course Document Id 2'));
    }//testGetCourseDocumentId

    function testUnAndSerializeCourseObject () {
        $courseObject = new CourseObject ();
        $courseObject->readUserUname = 'marcel';
        $courseObject->readUserPword = 'read';
        $courseObject->writeUserUname = 'heusinger';
        $courseObject->writeUserPword = 'write';
        $courseObject->courseDocumentTitle = 'Title of the course';
        $courseObject->documentId = 12345;
        $courseObject->courseId = 9999;
        
        $this->assertTrue($this->dataLayerFacade->StoreCourseObject($courseObject));
        
        $loadedCourseObject = $this->dataLayerFacade->ReadCourseObject(9999);
        $this->assertEqual($loadedCourseObject->readUserUname, 'marcel');
        $this->assertEqual($loadedCourseObject->readUserPword,'read');
        $this->assertEqual($loadedCourseObject->writeUserUname,'heusinger');
        $this->assertEqual($loadedCourseObject->writeUserPword,'write');
        $this->assertEqual($loadedCourseObject->courseDocumentTitle,'Title of the course');
        $this->assertEqual($loadedCourseObject->documentId,12345);
        $this->assertEqual($loadedCourseObject->courseId,9999);
    }//testUnAndSerializeCourseObject
    
    function testCreateUserAccount () {
        try {
            $this->dataLayerFacade->DeleteUser('crtest');
        } catch (WebServiceException $e) {}
        
        $validUname = 'crtest';
        $invalidUname1 = 'dilbert';
        $invalidUname2 = 'f@iled';
        $validAuthorId = '12231';
        $invalidAuthorId = '999999';
        $validGroups = array ('admins');
        $invalidGroups = array ('noGroup');
        
        try {
            $res = $this->dataLayerFacade->CreateUseraccount($invalidUname1, 'pword', $validAuthorId, $validGroups);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, $invalidUname1);
            $this->assertEqual ($e->__toString(), "ValidationException [1]: Username $invalidUname1 is already assigned.\n");
        }//catch
        
        try {
            $res = $this->dataLayerFacade->CreateUseraccount($invalidUname2, 'pword', $validAuthorId, $validGroups);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, $invalidUname2);
            $this->assertEqual ($e->__toString(), "ValidationException [2]: Username $invalidUname2 does not match Miless/MyCoRe reqirements.\n");
        }//catch
        
        try {
            $res = $this->dataLayerFacade->CreateUseraccount($validUname, 'pword', $invalidAuthorId, $validGroups);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, $invalidAuthorId);
            $this->assertEqual ($e->__toString(), "ValidationException [3]: Legal entity specified by $invalidAuthorId does not exist.\n");
        }//catch
        
        try {
            $res = $this->dataLayerFacade->CreateUseraccount($validUname, 'pword', $validAuthorId, $invalidGroups);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), $validUname);
            $this->assert(new ArrayHasValueExpectation($parameters), $invalidGroups);
            $this->assertEqual ($e->__toString(), "ValidationException [5]: Could not set groups for user crtest.\n");
        }//catch
        
        $this->assertTrue($this->dataLayerFacade->CreateUseraccount($validUname, 'pword', $validAuthorId, $validGroups));
        $this->assertTrue ($this->dataLayerFacade->DeleteUser ($validUname));
    }//testCreateUserAccount
    
    private function CreateTestUsers ($readUser, $writeUser, $adminUser, $documentId) {
        
        //Delete uers to create
        try {$this->dataLayerFacade->DeleteUser ($readUser); } catch (WebServiceException $e) {}
        try {$this->dataLayerFacade->DeleteUser ($writeUser); } catch (WebServiceException $e) {}
        try {$this->dataLayerFacade->DeleteUser ($adminUser);} catch (WebServiceException $e) {}
        
        //Create users and sets groups
        $group = 'creators';
        $this->assertTrue($this->dataLayerFacade->CreateUseraccount($readUser, $readUser, '12231', $group));
        $this->assertTrue($this->dataLayerFacade->ExistsUser($readUser));
        
        $this->assertTrue($this->dataLayerFacade->CreateUseraccount($writeUser, $writeUser, '12231', $group));
        $this->assertTrue($this->dataLayerFacade->ExistsUser($writeUser));
        
        $this->assertTrue($this->dataLayerFacade->CreateUseraccount($adminUser, $adminUser, '12231', 'admins'));
        $this->assertTrue($this->dataLayerFacade->ExistsUser($adminUser));
        
        //Set permissions for the docuemtn
        $this->dataLayerFacade->RemoveAllPermissions ($documentId);
        $perms = array ();
        $perms['r'] = $readUser;
        $this->assertTrue($this->dataLayerFacade->SetPermissions($documentId, $perms));
        
        $perms = array ();
        $perms['w'] = $writeUser;
        $this->assertTrue($this->dataLayerFacade->SetPermissions($documentId, $perms));
        
//        //TODO: Why is this required? Do admins have write and read acess by default? If not: Why not?
//        $perms = array ();
//        $perms['w'] = $adminUser;
//        $permissionReturn = $this->dataLayerFacade->SetPermissions($documentId, $perms);
//        $this->assertFalse($permissionReturn['error']);
    }//CreateTestUsers
    
    private function DeleteTestUsers ($readUser, $writeUser, $adminUser) {
        $this->assertTrue($this->dataLayerFacade->DeleteUser($readUser));
        $this->assertFalse($this->dataLayerFacade->ExistsUser($readUser));
        
        $this->assertTrue($this->dataLayerFacade->DeleteUser($writeUser));
        $this->assertFalse($this->dataLayerFacade->ExistsUser($writeUser));
        
        $this->assertTrue($this->dataLayerFacade->DeleteUser($adminUser));
        $this->assertFalse($this->dataLayerFacade->ExistsUser($adminUser));
    }//DeleteTestUsers
    
    function testGetAppletSessionkey () {
        $validUname   = 'marcel';
        $invalidUname = 'sesstest';
        $validPword   = 'marcel';
        $invalidPword = 'marcel2';
       
        try {
            $this->dataLayerFacade->CreateDocument($this->invalidDocParams);
            $this->fail('DocumentCreationException was expected'); 
        } catch (DocumentCreationException $e) {
            $this->assertNotNull($e->__toString());
        }//Catch
       
        $validDocumentId = $this->dataLayerFacade->CreateDocument($this->docParams);
        $invalidDocumentId = $validDocumentId + 1;
        $this->assertFalse($this->dataLayerFacade->ExistDocument($invalidDocumentId));
       
        $readUser = 'read';
        $writeUser = 'write';
        $adminUser = 'admiin';
        $this->CreateTestUsers($readUser, $writeUser, $adminUser, $validDocumentId);
       
        $validDerivateId = $this->dataLayerFacade->CreateFileDerivate($validDocumentId);
        $this->assertTrue($this->dataLayerFacade->ExistsFileDerivate($validDerivateId));
        $invalidDerivateId = '99999';
        $validReturnUrl  = 'http://www.google.com';
        $invalidReturnUrl  = 'http//ww.g00gle.com';
       
        try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($invalidUname, $validPword, $validDocumentId, $validDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [1]: User $invalidUname does not exist.\n");
            $this->assertEqual ($e->parameter, $invalidUname);
        }//catch

        try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($writeUser, $invalidPword, $validDocumentId, $validDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
        } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [2]: Passed credentials are not valid.\n");
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), $writeUser);
            $this->assert(new ArrayHasValueExpectation($parameters), $invalidPword);
        }//catch
       
       try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($readUser, $readUser, $validDocumentId, $validDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
       } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [5]: User $readUser does not have write access for document $validDocumentId.\n");
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), $readUser);
            $this->assert(new ArrayHasValueExpectation($parameters), $validDocumentId);
       }//catch
       
       try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($writeUser, $writeUser, $invalidDocumentId, $validDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
       } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [6]: Document specified by $invalidDocumentId does not exist.\n");
            $this->assertEqual ($e->parameter, $invalidDocumentId);
       }//catch
       
       try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($writeUser, $writeUser, $validDocumentId, $invalidDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
       } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [4]: The derivate with $invalidDerivateId identifier does not exist.\n");
            $this->assertEqual ($e->parameter, $invalidDerivateId);
       }//catch
       
       try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($writeUser, $writeUser, $validDocumentId, $validDerivateId, $invalidReturnUrl);
            $this->fail('ValidationException was expected'); 
       } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [1]: Validation of url faild. The string http//ww.g00gle.com&uploadDerivate=$validDerivateId is not a valid Url.\n");
            $this->assertEqual ($e->parameter, $invalidReturnUrl.'&uploadDerivate='.$validDerivateId);
       }//catch
       
       $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($writeUser, $writeUser, $validDocumentId, $validDerivateId, $validReturnUrl);
       $this->assertNotNull ($sessionKey);
       
       try {
            $sessionKey = $this->dataLayerFacade->GetAppletSessionkey ($adminUser, $adminUser, $validDocumentId, $validDerivateId, $validReturnUrl);
            $this->fail('ValidationException was expected'); 
       } catch (ValidationException $e) {
            $this->assertEqual($e->__toString(), "ValidationException [5]: User $adminUser does not have write access for document $validDocumentId.\n");
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), $adminUser);
            $this->assert(new ArrayHasValueExpectation($parameters), $validDocumentId);
       }//catch
       
       $this->DeleteTestUsers($readUser, $writeUser, $adminUser);
       $this->assertTrue($this->dataLayerFacade->DeleteFileDerivate($validDerivateId));
       $this->assertTrue($this->dataLayerFacade->DeleteDocument($validDocumentId));
    }//testGetAppletSessionkey
    
    function testRetrieveClassificationPopups () {
        $typePopup = $this->dataLayerFacade->GetClassificationPopup ('type');
        echo $typePopup;
    }//testRetrieveClassificationPopups
    
    function testAddSlash () {
        $this->assertEqual('path/', $this->dataLayerFacade->AddSlash('path'));
        $this->assertEqual('path/', $this->dataLayerFacade->AddSlash('path/'));
    }//testAddSlash
   
    function testDeleteDocument () {
        try {
            $this->dataLayerFacade->DeleteDocument (-1);
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, -1);
            $this->assertEqual ($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch

        try {
            $this->dataLayerFacade->DeleteDocument (0);
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, 0);
            $this->assertEqual ($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch

        try {
            $this->dataLayerFacade->DeleteDocument ('ffff');
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, 'ffff');
            $this->assertEqual ($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch
    }//testDeleteDocument
    
    function testExistDocument () {
        try {
            $this->dataLayerFacade->ExistDocument(-1);
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, -1);
            $this->assertEqual($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch
        
        try {
            $this->dataLayerFacade->ExistDocument(0);
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, 0);
            $this->assertEqual($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch
        
        try {
            $this->dataLayerFacade->ExistDocument('fffff');
        } catch (ValidationException $e) {
            $this->assertEqual ($e->parameter, 'fffff');
            $this->assertEqual($e->__toString(), "ValidationException [1]: The document identifier must not be empty or negative.\n");
        }//catch
    }//testExistDocument
    
    function testCreateExitsDelete () {
        $documentId1 = $this->dataLayerFacade->CreateDocument($this->docParams);
        $documentId2 = $this->dataLayerFacade->CreateDocument($this->docParams);
        $this->assertEqual($documentId1 + 1, $documentId2);
        
        $this->assertTrue($this->dataLayerFacade->ExistDocument($documentId1));
        $this->assertTrue($this->dataLayerFacade->ExistDocument($documentId2));
        
        $this->assertTrue($this->dataLayerFacade->DeleteDocument($documentId1));
        $this->assertTrue($this->dataLayerFacade->DeleteDocument($documentId2));
        
        $this->assertFalse($this->dataLayerFacade->ExistDocument($documentId1));
        $this->assertFalse($this->dataLayerFacade->ExistDocument($documentId2));
    }//testCreateExistsDelete
    
    function testCreateDocument () {
        $this->docParams['erstelltDatum']   = '12311212';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['geaendertDatum']  = '12323123';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['gueltigVonDatum'] = '12323123';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['gueltigBisDatum'] = '12323123';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['title'] = '';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['typeid'] = '';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['formatid'] = '';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
        
        $this->docParams['originid'] = '';
        try {
            $this->dataLayerFacade->CreateDocument($this->docParams);
        } catch (DocumentCreationException $e) {
            $this->assertEqual ($e->__toString(), "CreationException [0]: Validation of document creation parameters failed.\n");
        }//catch
    }//testCreateDocument
    
    private function BackupMilessFiles () {
        $s = MILESS_DIR.'\config\searchfields.xml';
        $r = MILESS_DIR.'\config\roles.xml';
        $l = MILESS_DIR.'\config\languages.xml';
        $f = MILESS_DIR.'\config\fieldtypes.xml';
        $this->assertTrue(copy ($s, $s.'.bak'));
        $this->assertTrue(copy ($r, $r.'.bak'));
        $this->assertTrue(copy ($l, $l.'.bak'));
        $this->assertTrue(copy ($f, $f.'.bak'));
    }//BackupMilessFiles
    
    private function RestoreMilessBackups () {
        $s = MILESS_DIR.'\config\searchfields.xml';
        $r = MILESS_DIR.'\config\roles.xml';
        $l = MILESS_DIR.'\config\languages.xml';
        $f = MILESS_DIR.'\config\fieldtypes.xml';
        
        $this->assertTrue(copy ($s.'.bak', $s));
        $this->assertTrue(copy ($r.'.bak', $r));
        $this->assertTrue(copy ($l.'.bak', $l));
        $this->assertTrue(copy ($f.'.bak', $f));
        
        $this->assertTrue(unlink ($s.'.bak'));
        $this->assertTrue(unlink ($r.'.bak'));
        $this->assertTrue(unlink ($l.'.bak'));
        $this->assertTrue(unlink ($f.'.bak'));
    }//RestoreMilessBackups
    
    private function CreateTestTempFiles () {
        $f  = TEMP_DIR.'\formatIdTemp.ser';
        $o  = TEMP_DIR.'\originIdTemp.ser';
        $t  = TEMP_DIR.'\typeIdTemp.ser';
        $r  = TEMP_DIR.'\roleIdTemp.ser';
        $s  = TEMP_DIR.'\searchFieldTemp.ser';
        $l  = TEMP_DIR.'\languagesTemp.ser';
        $op = TEMP_DIR.'\operatorsTemp.ser';
        $this->CreateTempFiles ($f, $o, $t, $r, $s, $l, $op);
    }//CreateTestTempFiles
    
    private function CreateTempFiles ($f, $o, $t, $r, $s, $l, $op) {
        $this->assertTrue(touch ($f));
        $this->assertTrue(touch ($o));
        $this->assertTrue(touch ($t));
        $this->assertTrue(touch ($r));
        $this->assertTrue(touch ($s));
        $this->assertTrue(touch ($l));
        $this->assertTrue(touch ($op));
    }//CreateTempFiles
    
    /*  
    public function testRemoveObsoleteFiles () {
        $f  = TEMP_DIR.'\formatIdTemp.ser';
        $o  = TEMP_DIR.'\originIdTemp.ser';
        $t  = TEMP_DIR.'\typeIdTemp.ser';
        $r  = TEMP_DIR.'\roleIdTemp.ser';
        $s  = TEMP_DIR.'\searchFieldTemp.ser';
        $l  = TEMP_DIR.'\languagesTemp.ser';
        $op = TEMP_DIR.'\operatorsTemp.ser';
        
        $this->assertTrue (is_file($f));
        $this->assertTrue (is_file($o));
        $this->assertTrue (is_file($t));
        $this->assertTrue (is_file($r));
        $this->assertTrue (is_file($s));
        $this->assertTrue (is_file($l));
        $this->assertTrue (is_file($op));
        
        $this->TouchMilessFiles ();
        
        $this->dataLayerFacade->RemoveObsoleteFiles();
        
        //$this->assertFalse (is_file($f));
        //$this->assertFalse (is_file($o));
        //$this->assertFalse (is_file($t));
        $this->assertFalse (is_file($r));
        $this->assertFalse (is_file($s));
        $this->assertFalse (is_file($l));
        $this->assertFalse (is_file($op));
    }//testRemoveObsoleteFiles
*/
    
    private function TouchMilessFiles () {
        $s = MILESS_DIR.'\config\searchfields.xml';
        $r = MILESS_DIR.'\config\roles.xml';
        $l = MILESS_DIR.'\config\languages.xml';
        $f = MILESS_DIR.'\config\fieldtypes.xml';
        $this->assertTrue(copy ($s, $s.'.bak'));
        $this->assertTrue(copy ($r, $r.'.bak'));
        $this->assertTrue(copy ($l, $l.'.bak'));
        $this->assertTrue(copy ($f, $f.'.bak'));
        
        $this->assertTrue(unlink ($s));
        $this->assertTrue(unlink ($r));
        $this->assertTrue(unlink ($l));
        $this->assertTrue(unlink ($f));
        
        $this->assertTrue(touch ($s));
        $this->assertTrue(touch ($r));
        $this->assertTrue(touch ($l));
        $this->assertTrue(touch ($f));
        
        $this->assertTrue($this->AttachFile ($s));
        $this->assertTrue($this->AttachFile ($r));
        $this->assertTrue($this->AttachFile ($l));
        $this->assertTrue($this->AttachFile ($f));
    }//TouchTempoarayFiles

    private function AttachFile ($filename) {
        if (!$handle = fopen($filename, "a")) {
                 print "Couldn't open $filename";
                return false;
            }
        
            if (!fwrite($handle, "dummy")) {
                
                print "Error while writting to $filename";
                return false;
            }
            fclose($handle);
            return true;
    }//AttacheFile
    
}//Class: testDataLayerFacade  
?>
