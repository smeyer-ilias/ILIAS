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
 *           PermissionService web service client implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PermissionService.php');

class permissionservice_test extends UnitTestCase {
    
    private $permissionService;
    
    
    public function setUp () {
        $this->permissionService = new PermissionService ();
    }//setUp
    
    public function tearDown () {
        $this->permissionService = null;
    }//tearDown
    
    /**
     * To enable this test the private methods within the 
     * Permission Service implementation must be set to public.
     */
    /*
    function testPrivateMethods () {
        $permissions = array ();
        $permissions[] = array ('w' => 'write1');
        $permissions[] = array ('w' => 'write2');
        $permissions[] = array ('r' => 'read1');
        $permissions[] = array ('r' => 'read2');
        $request = $this->permissionService->CreatePermissonRequest($permissions);
        
        echo $request;
    }//testPrivateMethods
    */
    
    function testSetGetRemovePermWrite () {
        global $CFG;
        
        require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');
        require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');
        $documentService = new DocumentService ();
        $userService = new UserService ();
        
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
        
        $documentId = $documentService->CreateDocument($docParams);
        $this->assertNotNull($documentId);
        $documentService->ExistDocument($documentId);
        
        $userService->DeleteUser('ptest');
        $this->assertTrue($userService->CreateUser('ptest', 'ptest', 12231));
        $this->assertTrue($this->permissionService->SetPermissions($documentId, array ('w' => 'ptest')));
        $this->assertTrue($userService->SetGroups('ptest', array ('creators')));
    
        $permissions = $this->permissionService->GetPermissions($documentId);
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeRead');
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeWrite');
        $this->assertTrue($permissions['freeRead']);
        $this->assertFalse($permissions['freeWrite']);
        
        
        $userRights = $this->permissionService->GetUserRights($documentId, 'ptest');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'read');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'write');
        $this->assertTrue($userRights['read']);
        $this->assertTrue($userRights['write']);
    
        $this->permissionService->RemoveAllPermissions($documentId);
    
        $permissions = $this->permissionService->GetPermissions($documentId);
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeRead');
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeWrite');
        $this->assertTrue($permissions['freeRead']);
        $this->assertTrue($permissions['freeWrite']);
        
        $userRights = $this->permissionService->GetUserRights($documentId, 'ptest');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'read');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'write');
        $this->assertTrue($userRights['read']);
        $this->assertTrue($userRights['write']);
    
        $this->assertTrue($userService->DeleteUser('ptest'));
        $this->assertTrue($documentService->DeleteDocument($documentId));
        $this->assertFalse($userService->ExistsUser('ptest'));
        $this->assertFalse($documentService->ExistDocument($documentId));
    }//testSetGetRemovePermissionWrite
    
    function testSetGetRemovePermRead () {
        global $CFG;
        
        require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');
        require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_DocumentService.php');
        $documentService = new DocumentService ();
        $userService = new UserService ();
        
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
        
        $documentId = $documentService->CreateDocument($docParams);
        $this->assertNotNull($documentId);
        $documentService->ExistDocument($documentId);
        
        $userService->DeleteUser('ptest');
        $this->assertTrue($userService->CreateUser('ptest', 'ptest', 12231));
        $this->assertTrue($this->permissionService->SetPermissions($documentId, array ('r' => 'ptest')));
        $this->assertTrue($userService->SetGroups('ptest', array ('creators')));
    
        $permissions = $this->permissionService->GetPermissions($documentId);
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeRead');
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeWrite');
        $this->assertFalse($permissions['freeRead']);
        $this->assertFalse($permissions['freeWrite']);
    
        $userRights = $this->permissionService->GetUserRights($documentId, 'ptest');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'read');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'write');
        $this->assertTrue($userRights['read']);
        $this->assertFalse($userRights['write']);
    
        $this->permissionService->RemoveAllPermissions($documentId);
    
        $permissions = $this->permissionService->GetPermissions($documentId);
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeRead');
        $this->assert(new ArrayHasKeyExpectation($permissions), 'freeWrite');
        $this->assertTrue($permissions['freeRead']);
        $this->assertTrue($permissions['freeWrite']);
    
        $userRights = $this->permissionService->GetUserRights($documentId, 'ptest');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'read');
        $this->assert(new ArrayHasKeyExpectation($userRights), 'write');
        $this->assertTrue($userRights['read']);
        $this->assertTrue($userRights['write']);
    
        $this->assertTrue($userService->DeleteUser('ptest'));
        $this->assertTrue($documentService->DeleteDocument($documentId));
        $this->assertFalse($userService->ExistsUser('ptest'));
        $this->assertFalse($documentService->ExistDocument($documentId));
    }//testSetGetRemovePermissionRead
     
}//permissionservice_test
?>
