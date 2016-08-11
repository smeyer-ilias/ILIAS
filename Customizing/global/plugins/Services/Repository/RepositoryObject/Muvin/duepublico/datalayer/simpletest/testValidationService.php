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
 * @abstract Test client for the <c>ValidationService</c> implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_validationservice.php');
 
 class validation_service_test extends UnitTestCase {
 
    private $validationService;
 
    public function setUp () {
        $this->validationService =  ValidationService::GetValidationService ();
    }//setUp

    public function tearDown () {
        $this->validationService = null;
    }//tearDown
    
    function testDateValidation () {
        $this->assertTrue ($this->validationService->ValidateDate('12.12.2001'));
        $this->assertTrue ($this->validationService->ValidateDate('24.12.2000'));
        $this->assertTrue ($this->validationService->ValidateDate('20.08.1981'));
        
        $this->assertFalse ($this->validationService->ValidateDate('34.12.2001'));
        $this->assertFalse ($this->validationService->ValidateDate('12.45.2001'));
        $this->assertFalse ($this->validationService->ValidateDate('12.2.2001'));
        $this->assertFalse ($this->validationService->ValidateDate('2.12.2001'));
        $this->assertFalse ($this->validationService->ValidateDate('12.12.1701'));
        $this->assertFalse ($this->validationService->ValidateDate('12.12.2301'));
    }//testDateValidation
    
    function testEmailValidation () {
        $this->assertTrue ($this->validationService->ValidateEmail ('marcel.heusinger@mycore.de'));
        $this->assertTrue ($this->validationService->ValidateEmail ('marcelheusinger@mycore.de'));
        $this->assertTrue ($this->validationService->ValidateEmail ('marcel.heusinger@uni.mycore.de'));
        $this->assertFalse ($this->validationService->ValidateEmail ('1 xml@example'));
        $this->assertFalse ($this->validationService->ValidateEmail ('?xm@example.com'));
        $this->assertFalse ($this->validationService->ValidateEmail ('xml[AT]example'));
        $this->assertFalse ($this->validationService->ValidateEmail ('xml@beispiel.tooLong'));
        $this->assertFalse ($this->validationService->ValidateEmail ('xml example'));
    }//testEmailValidation

    public function testUsernameValidation () {
        $this->assertTrue ($this->validationService->ValidateUsername ('valid'));
        $this->assertTrue ($this->validationService->ValidateUsername ('test'));
        $this->assertTrue ($this->validationService->ValidateUsername ('marcel'));
        
        try {
            $this->assertTrue ($this->validationService->ValidateUsername ('nums123'));
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'nums123');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of username failed. The username must be composed of 4 to 16 small characters\n");
        }//catch
        
        try {
            $this->validationService->ValidateUsername ('');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of username failed. The username must not be empty.\n");
        }//catch
        
//        $this->assertTrue ($error['error']);
//        $this->assertEqual(1, $error['code']);
//        $this->assert(new ArrayHasKeyExpectation($error), 'message');
        
        try {
            $this->validationService->ValidateUsername ('notvalidbecausetoolong');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'notvalidbecausetoolong');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of username failed. The username must be composed of 4 to 16 small characters\n");
        }//catch
//        $this->assertTrue ($error['error']);
//        $this->assertEqual (3, $error['code']);
//        $this->assert(new ArrayHasKeyExpectation($error), 'message');
        
        try {
            $this->validationService->ValidateUsername ('NotValidBecauseUpperCases');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'NotValidBecauseUpperCases');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of username failed. The username must be composed of 4 to 16 small characters\n");
        }//catch
//        $this->assertTrue ($error['error']);
//        $this->assertEqual (3, $error['code']);
//        $this->assert(new ArrayHasKeyExpectation($error), 'message');
        
        try {
            $this->validationService->ValidateUsername ('spec%$§');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'spec%$§');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of username failed. The username must be composed of 4 to 16 small characters\n");
        }//catch
//        $this->assertTrue ($error['error']);
//        $this->assertEqual (3, $error['code']);
//        $this->assert(new ArrayHasKeyExpectation($error), 'message');
    }//testUsernameValidation
    
    public function testPhonenumberValidation () {
        $this->assertTrue ($this->validationService->ValidatePhone ('0201-183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('02011833139'));
        $this->assertTrue ($this->validationService->ValidatePhone ('0201 / 183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('0201/183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('0201 - 183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('(0201)-183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('49 201-183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('(0201) 183 31 39'));
        $this->assertTrue ($this->validationService->ValidatePhone ('(0201)183 31 39'));
        
        try {
            $this->validationService->ValidatePhone ('(0201)/183-12oo');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '(0201)/183-12oo');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of phonenumber failed.The phonenumber contains illegal characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePhone ('(0201)/183+12');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '(0201)/183+12');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of phonenumber failed.The phonenumber contains illegal characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePhone ('(0201)/183 31 or 21');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '(0201)/183 31 or 21');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of phonenumber failed.The phonenumber contains illegal characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePhone ('');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of phonenumber failed.The phonenumber must not be empty.\n");
        }//catch
    }//testPhonenumberValidation
    
    public function testPasswordValidation () {
        $this->assertTrue ($this->validationService->ValidatePasswords ('valid', 'valid'));
        $this->assertTrue ($this->validationService->ValidatePasswords ('marcel', 'marcel'));
        $this->assertTrue ($this->validationService->ValidatePasswords ('duepublico', 'duepublico'));
        $this->assertTrue ($this->validationService->ValidatePasswords ('num3', 'num3'));
        $this->assertTrue ($this->validationService->ValidatePasswords ('spec$', 'spec$'));
        
        try {
            $this->validationService->ValidatePasswords ('', '');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), '');
            $this->assert(new ArrayHasValueExpectation($parameters), '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of password fail. The passwords must not be empty strings.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('test', '');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'test');
            $this->assert(new ArrayHasValueExpectation($parameters), '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of password fail. The passwords must not be empty strings.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('', 'test');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'test');
            $this->assert(new ArrayHasValueExpectation($parameters), '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of password fail. The passwords must not be empty strings.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('test', 'test1');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'test');
            $this->assert(new ArrayHasValueExpectation($parameters), 'test1');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of password fail. The passwords don't match.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('test1', 'test');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'test1');
            $this->assert(new ArrayHasValueExpectation($parameters), 'test');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of password fail. The passwords don't match.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords (' test$1', ' test$1');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), ' test$1');
            $this->assert(new ArrayHasValueExpectation($parameters), ' test$1');
            $this->assertEqual ($e->__toString (), "ValidationException [3]: Validation of password fail. The passwords must not contain spaces and they must not have more then 9 characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('t st', 't st');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 't st');
            $this->assertEqual ($e->__toString (), "ValidationException [3]: Validation of password fail. The passwords must not contain spaces and they must not have more then 9 characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('thisistolong', 'thisistolong');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'thisistolong');
            $this->assertEqual ($e->__toString (), "ValidationException [3]: Validation of password fail. The passwords must not contain spaces and they must not have more then 9 characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidatePasswords ('thisistolong', '');
        } catch (ValidationException $e) {
            $parameters = $e->parameter;
            $this->assert(new ArrayHasValueExpectation($parameters), 'thisistolong');
            $this->assert(new ArrayHasValueExpectation($parameters), '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of password fail. The passwords must not be empty strings.\n");
        }//catch
    }//testPasswordValidation
    
    public function testSurenameValidation() {
        $this->assertTrue ($this->validationService->ValidateSurename ('valid'));
        $this->assertTrue ($this->validationService->ValidateSurename ('Marcel'));
        $this->assertTrue ($this->validationService->ValidateSurename ('Heusinger'));
        
        try {
            $this->validationService->ValidateSurename ('');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, '');
            $this->assertEqual ($e->__toString (), "ValidationException [1]: Validation of surename fail. The surename must not be empty.\n");
        }//catch
        
        try {
            $this->validationService->ValidateSurename ('spec%$§');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'spec%$§');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of surename fail. The surename contains illegal characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidateSurename ('num3');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'num3');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of surename fail. The surename contains illegal characters.\n");
        }//catch
        
        try {
            $this->validationService->ValidateSurename ('Space Notvalid');
        } catch (ValidationException $e) {
            $this->assertEqual($e->parameter, 'Space Notvalid');
            $this->assertEqual ($e->__toString (), "ValidationException [2]: Validation of surename fail. The surename contains illegal characters.\n");
        }//catch
    }//testSurenameValidation

    public function testUrlValidation () {
        $this->assertTrue($this->validationService->ValidateUrl ('http://www.google.de'));
        $this->assertTrue($this->validationService->ValidateUrl ('https://www.google.de'));
        $this->assertTrue($this->validationService->ValidateUrl ('http://mail.google.com'));
        $this->assertTrue($this->validationService->ValidateUrl ('http://www.linkedin.com/in/marcelheusinger'));
        $this->assertTrue($this->validationService->ValidateUrl ('http://duepublico.uni-duisburg-essen.de/servlets/LegalEntityServlet?XSL.docID=&id=12231'));
    }//testUrlValidation

 }//Clas: validation_service_test
?>
