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
 *           PersonService web service client implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/webservice/duep_PersonService.php');

class personservice_test extends UnitTestCase {
    
    private $personService;
    
    public function setUp () {
        $this->personService = new PersonService ();
    }//setUp
    
    public function tearDown () {
        $this->personService = null;
    }//tearDown
    
    function testPersonInfoNotExist () {
        try {
            $this->personService->PersonInfo(99999);
            $this->fail('An PersonNotExistsException was expected here.');
        } catch (PersonNotExistsException $e) {
            $this->assertEqual ($e->legalEntityId, 99999);
            //$this->assertEqual($e->__toString(), "PersonNotExistsException: LegalEntity with id 99999 does not exist.\n");
        }//catch
    }//testPersonInfoNotExist
    
    function testPersonInfoExist () {
        $personInfo = $this->personService->PersonInfo(12231);
        $this->assertEqual($personInfo['name'], 'Heusinger, Marcel'); 
        $this->assertEqual($personInfo['id'], '12231');
        $this->assertEqual($personInfo['id'], 12231);   
    }//testPersonInfoExist
    
    function testDeleteNotExist () {
        $this->assertFalse($this->personService->DeletePerson(999999999));
    }//testDeleteNotExist
    
    function testFalseExists () {
        $this->assertFalse($this->personService->ExistsPerson(999999));
        $this->assertFalse($this->personService->ExistsPerson('999999'));
    }//testFalseExists
    
    function testCreateExistDelete () {
        $parameters = array ();
        $parameters['academictitle']  = 'Prof. Dr.';
        $parameters['name']           = 'X, Y';
        $parameters['originid']       = '05.04.10';
        $parameters['publishContact'] = 'false';
        $parameters['contactType']    = 'home';
        $parameters['institution']    = 'University of Duisburg Essen, Campus Essen';
        $parameters['address']        = 'Universitaetsstr 1';
        $parameters['phone']          = '0201 182 21 12';
        $parameters['fax']            = '0201 182 21 12';
        $parameters['email']          = 'x@y.de';
        $parameters['homepage']       = 'http://www.xy.de';
        $parameters['contactComment'] = 'Contact Comment';
        $parameters['personComment']  = 'Person Comment';
        $parameters['gebPlace']       = 'Essen';
        $parameters['gebDate']        = '20.08.1981';
        
        $lid1 = $this->personService->CreatePerson($parameters);
        $lid2 = $this->personService->CreatePerson($parameters);
        
        $this->assertTrue($this->personService->ExistsPerson($lid1));
        $this->assertTrue($this->personService->ExistsPerson($lid2));
        $this->assertEqual($lid1 + 1, $lid2);
        
        $this->assertTrue($this->personService->DeletePerson($lid1));
        $this->assertTrue($this->personService->DeletePerson($lid2));
        $this->assertFalse($this->personService->DeletePerson($lid1));
        
        $this->assertFalse($this->personService->ExistsPerson($lid1));
        $this->assertFalse($this->personService->ExistsPerson($lid2));
    }//testCreateExistDelete
     
}//personservice_test
?>
