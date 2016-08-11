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
 *           UserService web service client implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_UserService.php');

class userservice_test extends UnitTestCase {
    
    private $userSerivce;
    
    function setUp () {
        $this->userService = new UserService ();
    }//setUp
    
    function tearDown () {
        $this->userService = null;
    }//tearDown
    
    function testGroupMethods () {
        $uname = 'grtest';
        $this->userService->DeleteUser($uname);
        $this->assertTrue($this->userService->CreateUser($uname, $uname, 12231));
        $this->assertTrue($this->userService->SetGroups($uname, array ('admins', 'creators', 'disshab', 'osap', 'submitters')));
        $groups = $this->userService->GetGroups($uname);
        $this->assert(new ArrayHasValueExpectation($groups), 'admins');
        $this->assert(new ArrayHasValueExpectation($groups), 'creators');
        $this->assert(new ArrayHasValueExpectation($groups), 'disshab');
        $this->assert(new ArrayHasValueExpectation($groups), 'osap');
        $this->assert(new ArrayHasValueExpectation($groups), 'submitters');
    }//testGroupMethods
    
    function testUserLogin () {
        $this->userService->DeleteUser('marcel');
        $this->assertTrue ($this->userService->CreateUser ('marcel', 'marcel', 12231));
        
        $this->assertTrue  ($this->userService->ExistsUser ('marcel'));
        $this->assertFalse ($this->userService->ExistsUser ('marcel2'));
        
        $this->assertFalse ($this->userService->DeleteUser ('marcel2'));
        $this->assertFalse ($this->userService->UserLogin ('marcel', 'xxx'));
        $this->assertEqual ($this->userService->UserLogin ('marcel', 'marcel'), 12231);
        $this->assertTrue ($this->userService->DeleteUser('marcel'));
        $this->assertFalse ($this->userService->ExistsUser('marcel'));
    }//testUserLogin
    
    function testCheckPassword () {
        $this->userService->DeleteUser('marcel');
        $this->assertTrue  ($this->userService->CreateUser ('marcel', 'marcel', 12231));
        $this->userService->DeleteUser ('marcel2');
        
        $this->assertTrue  ($this->userService->CheckPassword ('marcel', 'marcel'));
        $this->assertFalse ($this->userService->CheckPassword ('marcel2', 'marcel'));
        $this->assertFalse ($this->userService->CheckPassword ('marcel', 'marcel2'));
        
        $this->assertTrue ($this->userService->DeleteUser('marcel'));
        $this->assertFalse ($this->userService->ExistsUser('marcel'));
    }//testCheckPassword
    
    function testGetTicket () {
        $this->userService->DeleteUser('marcel');
        $this->userService->DeleteUser('marcel2');
        
        $this->assertTrue  ($this->userService->CreateUser ('marcel', 'marcel', 12231));
        
        $this->assertFalse ($this->userService->GetTicket ('marcel2', 'marcel'));
        $this->assertNotNull ($this->userService->GetTicket('marcel', 'marcel'));
        
        $this->assertTrue ($this->userService->DeleteUser('marcel'));
        $this->assertFalse ($this->userService->ExistsUser('marcel'));
    }//testGetTicket
    
    function testCreateExistDelete () {
        $this->userService->DeleteUser('marcel');
        
        $this->assertTrue  ($this->userService->CreateUser ('marcel', 'marcel', 12231));
        $this->assertFalse ($this->userService->CreateUser ('marcel', 'marcel', 12231));
        
        $this->assertTrue  ($this->userService->ExistsUser ('marcel'));
        $this->userService->DeleteUser ('marcel2');
        $this->assertFalse ($this->userService->ExistsUser ('marcel2'));
        
        $this->assertFalse ($this->userService->DeleteUser('marcel2'));
        $this->assertTrue ($this->userService->DeleteUser('marcel'));
        $this->assertFalse ($this->userService->ExistsUser('marcel'));
    }//testCreateExistDelete
    
}//userservice_test
?>
