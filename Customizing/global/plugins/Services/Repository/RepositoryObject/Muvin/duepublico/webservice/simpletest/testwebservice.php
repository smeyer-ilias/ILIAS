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
 * @abstract This file contains a test client for the base class of the 
 *           Miless/MyCoRe web service client implementation.
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
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_FileService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PermissionService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PersonService.php');
require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');

define ('PUB1', 'http://localhost/MoodlePublish');
define ('PUB2', 'http://localhost/MoodlePublish/');
define ('PUB3', 'localhost/MoodlePublish');
define ('PUB4', 'localhost/MoodlePublish/');

class webservice_test extends UnitTestCase {
    function testExpectations () {
        $array = array ('foo' => 'bar', 100 => 0, 'color' => 'red', 'elem' => array (0 => 1));
        $this->assert(new ArrayHasValueExpectation($array), 'bar');
        $this->assert(new ArrayNotHasValueExpectation($array), 'blue');
        $this->assert(new ArrayHasKeyExpectation($array), 100);
        $this->assert(new ArrayNotHasKeyExpectation($array), 'colour');
        $this->assert(new ArrayHasKeyExpectation($array), 'elem');
        $this->assert(new ArrayHasValueExpectation($array), array (0 => 1));
        $this->assert(new ArrayNotHasValueExpectation($array), array (1 => 0)); 
    }//testExpectation
    
    function testProtectedWebServiceMethods () {
        $service = new QueryService ();
        
        $this->assertEqual('http://www.google.de', $service->CheckUrl('http://www.google.de'));
        $this->assertEqual('http://www.google.de', $service->CheckUrl('www.google.de'));
        
        $this->assertTrue ($service->EndsWith('E:\\temp\\', '\\'));
        $this->assertTrue ($service->EndsWith('/var/user/', '/'));
        
        $this->assertFalse ($service->EndsWith('E:\\temp', '\\'));
        $this->assertFalse ($service->EndsWith('/var/user', '/'));
        
        $endings = array ('\\', '/');
        $this->assertTrue ($service->EndsWithAny('E:\\temp\\', $endings));
        $this->assertTrue ($service->EndsWithAny('/var/user/', $endings));
        $this->assertFalse ($service->EndsWithAny('E:\\temp', $endings));
        $this->assertFalse ($service->EndsWithAny('/var/user', $endings));
        
        $this->assertEqual('http://www.google.de/test.zip', $service->CreateUploadUri('http://www.google.de', 'test.zip'));
        $this->assertEqual('http://www.google.de/test.zip', $service->CreateUploadUri('http://www.google.de/', 'test.zip'));
        $this->assertEqual('http://www.google.de/test.zip', $service->CreateUploadUri('www.google.de', 'test.zip'));
        $this->assertEqual('http://www.google.de/test.zip', $service->CreateUploadUri('www.google.de/', 'test.zip'));
    }//testProtectedWebServiceMethods
    
    function testGetNameMethod () {
        $PermissionService = new PermissionService ();
        $UserService = new UserService ();
        $DocumentService = new DocumentService ();
        $FileService = new FileService ();
        $PersonService = new PersonService ();
        $QueryService = new QueryService ();
        
        $this->assertEqual ('PermissionService', $PermissionService->GetName ());
        $this->assertEqual ('UserService', $UserService->GetName ());
        $this->assertEqual ('DocumentService', $DocumentService->GetName ());
        $this->assertEqual ('FileService', $FileService->GetName ());
        $this->assertEqual ('PersonService', $PersonService->GetName ());
        $this->assertEqual ('QueryService', $QueryService->GetName ());
    }//testGetNameMethod

}//webservice_test
?>
