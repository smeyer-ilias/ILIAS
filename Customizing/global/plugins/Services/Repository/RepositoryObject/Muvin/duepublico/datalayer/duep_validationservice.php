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
 * @abstract This class provides methods for validation user input and data that
 *           should be transfered to the Miless/MyCoRe server to avoid 
 *           unneccessary web service calls as theses class are quite time
 *           consuming and expensive.
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

class ValidationService {

    /**
     * An <c>array</c> of the Miless/MyCoRe groups.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $groups;
    /**
     * An <c>array</c> that contains all available help buttons.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $helpButtons;
    /**
     * An <c>array</c> of all Miless/MyCoRe classification identifiers.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $classifications;
    /**
     * Holds the singelton instance of this class after its initialization.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private static $service = null;
    
    /**
     * Default constructor of this class that initializes the
     * groups definied above.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function __construct () {
        $this->groups = array ('admins', 'creators', 'disshab', 'osap', 'submitters');
        $this->helpButtons = array ('query', 'results', 'upload', 'account', 'newAcc', 'backup');
        $this->classifications = array ('ANGLISTIK','DDC', 'ELISE', 'FORMAT', 'LINSE', 'ORIGIN', 'PACS', 'PHYSIK', 'TYPE', 'UNIKATE', 'WIFORUM');
    }//Constructor
    
    /**
     * Returns the instance of this singelton class.
     * @return ValidationService The instance of this singelton implementation.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public static function GetValidationService () {
        if (null == self::$service) {
            self::$service = new ValidationService ();
        }//if
        return self::$service;
    }//GetValidationService
    
    /**
     * Checks if the passed <c>string</c> equals one of the
     * Miless/MyCoRe defined classifications.
     * @param string $button A <c>string</c> that should be compared with the definied classifications.
     * @return bool <c>True</c> if the passed <c>string</c> equals one of the definied classifications; otherwise <c>false</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateClassification ($classification) {
        return $this->ContainsValue($this->classifications, $classification);
    }//ValidateClassification
    
    /**
     * Checks if the passed <c>string</c> equals one of the
     * defined help buttons.
     * @param string $button A <c>string</c> that should be compared with the definied help buttons.
     * @return bool <c>True</c> if the passed <c>string</c> equals one of the definied help button names; otherwise <c>false</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateHelpButtons ($button) {
        return $this->ContainsValue($this->helpButtons, $button);
    }//ValidateHelpButtons

    /**
     * Checks if every <c>string</c> in the passed <c>array</c> equals one of the
     * defined Miless/MyCoRe groups.
     * @param array $groupArray An <c>array</c> of <c>string</c>s which should be compared with the definied groups.
     * @return array An <c>array</c> that contains the status information of the method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateGroups ($groupArray) {
        if (is_array ($groupArray)) {
            foreach ($groupArray as $group) {
                if (!$this->ValidateGroup($group)) {
                    return false;
                }//if
            }//foreach
            return true;
        } else {
            throw new InvalidArgumentException ('ValidationService [ValidateGroups]: Passsed parameter $groupArray is not an array.');
        }//else
    }//ValidateGroups

    /**
     * Checks if the passed <c>string</c> equals one of the
     * defined Miless/MyCoRe groups.
     * @param string $group A <c>string</c> that should be compared with the definied groups.
     * @return bool <c>True</c> if the passed <c>string</c> equals one of the definied group names; otherwise <c>false</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateGroup ($group) {
        return $this->ContainsValue($this->groups, $group);
    }//ValidateGroup
     
    /**
     * Checks if the passed <c>array</c> contains the passed <c>$value</c>.
     * @return bool <c>True</c> if the value is contained in the <c>array</c>; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */ 
    private function ContainsValue ($array, $value) {
        if (is_array($array)) {
            foreach ($array as $entry) {
                if (strcasecmp($entry, $value) == 0) {
                    return true;
                }//if
            }//foreach
            return false;
        } else {
            if (strcasecmp($array, $value) == 0) {
                return true;
            } else {
                return false;
            }//else 
        }//else 
    }//ContainsValue 
  
    /**
     * Checks if entered date meets DuEPublico requirements.
     * @param string $date Date that should be checked
     * @return bool <c>True</c> if the date is valid, otherwise <c>false</c> is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateDate($date) {
        //TODO: Check if month and days are valid
        if (!(preg_match('/(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[012])\.(19|20)[0-9]{2}$/', $date) == 1)) {
            return false;
        } else {
            return true;
        }//else
    }//ValidateDate
        
    /**
     * Checks if the passed email id is a valid email address
     * @param string $email The email address that should be checked
     * @return bool <c>True</c> if the email address is valid, otherwise <c>false</c> is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateEmail($email) {
        if (!(eregi ('^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$', $email) == 1)) {
            return false;    
        } else {
            return true;
        }//else  
    }//ValidateEmail
    
    /**
     * Checks if the passed username meets the DuEPublico requirements.
     * The returned <c>array</c> has three parts or keys: The error key
     * is a <c>bool</c> value that descibes if an error occurred or not.
     * The second key contains an error numner, which is one of the
     * following list_
     * <ul>
     * <li>1 -> The username is empty</li>
     * <li>2 -> The username is already assigned to someone</li>
     * <li>3 -> The username contains invalid characters or is longer than 16 characters</li>
     * <li>0 -> If none of the constraints mentioned before is true</li>
     * </ul>
     * The thrid key contains a human readable message of the validation error.
     * @param string $username The username that should be validated
     * @return array Returns an <c>array</c> that contains the validation information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateUsername($username) {
        if ($username == '') {
            throw new ValidationException ('Validation of username failed. The username must not be empty.', $username, 1);
        } else if (!(preg_match('/^(-|[a-z0-9]){4,16}$/', $username))) {
            throw new ValidationException ('Validation of username failed. The username must be composed of 4 to 16 small characters', $username, 2);
        } else {
            return true;
        }//else
    }//ValidateUsername
    
    /**
     * Checks is the passed value meets the DuEPublico requirements for a valid
     * phonenumber.
     * @param string $phonenumber The value that should be validated as phonenumber
     * @return array Returns an <c>array</c> that contains the validation information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidatePhone($phonenumber) {
        if ($phonenumber == '') {
            throw new ValidationException ('Validation of phonenumber failed.The phonenumber must not be empty.', $phonenumber, 2);
        } else if (!(preg_match ('/^[\d\(\)\/\-\s]{4,20}$/', $phonenumber) == 1)) {
            throw new ValidationException ('Validation of phonenumber failed.The phonenumber contains illegal characters.', $phonenumber, 1);
        } else {
            return true;
        }//else
    }//ValidatePhone
    
    /**
     * Checks if the passed <c>string</c> matches an url.
     * @param string $url The url that should be validated by this method.
     * @return array Returns an <c>array</c> that contains the validation information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateUrl ($url) {
        if (preg_match ('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $url)) {
            return true;
        } else {
            throw new ValidationException ('Validation of url faild. The string '.$url.' is not a valid Url.', $url, 1);
        }//else
    }//ValidateUrl
    
    /**
     * Checks is the passed values meet the DuEPublico requirements 
     * for a valid passwords.
     * @param string $pword1 The first entered password
     * @param string $pword2 The second entered password
     * @return array Returns an <c>array</c> that contains the validation information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidatePasswords ($pword1, $pword2) {
        if (($pword1 == '') or ($pword2 == '')) {
             throw new ValidationException ('Validation of password fail. The passwords must not be empty strings.', array ($pword1, $pword2), 1);
         } else if ($pword1 != $pword2) {
             throw new ValidationException ("Validation of password fail. The passwords don't match.", array ($pword1, $pword2), 2);
         } else if (!(preg_match ('/^[^\s]{1,10}$/', $pword1) == 1)) {
             throw new ValidationException ('Validation of password fail. The passwords must not contain spaces and they must not have more then 9 characters.', array ($pword1, $pword2), 3); 
         } else {
             return true;
         }//else
    }//ValidatePasswords
    
    /**
     * Checks if the passed <c>$name</c> meets the DuEPublico requirements for
     * a valid surename.
     * @param string $name The surename that should be validated.
     * @return array Returns an <c>array</c> that contains the validation information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateSurename($name) {
        if ($name == '') {
           throw new ValidationException ('Validation of surename fail. The surename must not be empty.', $name, 1);
        } else if (!(preg_match('/^[a-zA-Z\s-]+$/', $name) == 1)) {
            throw new ValidationException ('Validation of surename fail. The surename contains illegal characters.', $name, 2);
        } else {
            return true;
        }//else
    }//ValidateSurename

    /**
     * Checks if the passed <c>$string</c> ends with
     * one of the <c>string<c>s in the passed 
     * <c>array</c> <c>$endings</c>.
     * @param string $string The <c>string</c> to be check.
     * @param string $endings An <c>array</c> of <c>string</c>s used for the comparison.
     * @return bool <c>True</c> if <c>$string</c> ends with one of the <c>string</c>s in <c>$endings</c>; oterhwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
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
     * @return bool <c>True</c> if <c>$string</c> ends with <c>$ending</c>; oterhwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function EndsWith ($string, $ending) {
       $len = strlen($ending);
       $original = substr($string, strlen($string) - $len);
       return $original == $ending;
    }//EndsWith

    /**
     * Validates the user input entered for document creations. The following
     * keys of the passed <c>array</c> will be validated.
     * <li>email</li>
     * <li>phone</li>
     * <li>name</li>
     * <li>gebDate</li>
     * <li>pword</li>
     * <li>pword2</li>
     * <li>uname</li> 
     * @param array $params The legal entity parameters as described above.
     * @return array The returned <c>array</c> contains a valid information 
     *               of each of the keys specified above, as well as an entry 
     *               'error' that indicates if there was an error during the
     *               validation (<c>true</c>) or not (<c>false</c>).
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateLegalEntity ($params) {
        $valid = true;
        $fails = array ();
        
        if (!$this->ValidateEmail($params['email'])) {
            $valid = false;
            $fails[] = 'email';
        }//if
        
        try {
            $this->ValidatePhone($params['phone']);
        } catch (ValidationException $e) {
            $valid = false;
            $fails[] = 'phone';
        }//catch
        
        try {
            $this->ValidateSurename($params['name']);
        } catch (ValidationException $e) {
            $valid = false;
            $fails = 'name';
        }//catch
        
        if (isset ($params['gebDate'])) {
            try {
                $this->ValidateDate($params['gebDate']);
            } catch (ValidationException $e) {
                $valid = false;
                $fails = 'gebDate';
            }//catch
        }//if
        
        if (isset($params['uname']) and isset($params['pword'])) {
            try {
                $this->ValidatePasswords($params['pword'], $params['pword2']);
            } catch (ValidationException $e) {
                $valid = false;
                $fails = 'pword';
            }//catch
            
            try { 
                $this->ValidateUsername($params['uname']); 
            } catch (ValidationException $e) {
                $valid = false;
                $fails = 'uname';
            }//catch
        }//if
        
        if (!$valid) {
            throw new LegalEntityCreationException ('Validation of legal entity parameters failed.', $params, $fails);
        } else {
            return true;
        }//else
    }//ValidateLegalEntity

    /**
     * Validates if the passed parameters are a valid Miless/MyCoRe query. The
     * below mentioned <c>array</c> keys are validated:
     * <li>keywords</li>
     * <li>name</li>
     * <li>originid</li>
     * <li>typeid</li>
     * <li>formatid</li>
     * <li>keywords</li>
     * <li>date</li>
     * <li>datum</li> 
     * @param array $paramsSearchQuery The parameter which should be validated.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateQueryParameters ($params) {
        $valid = true;
        $error = array ();
        //If the date drop down list doesn't equal 'noChoose' then the date must be valid
        if ((isset($params['date']) and 
            ($params['date'] != 'noChoose'))) {
            //If an entry of the date drop down list was choosen, then check correct date
            try {
                $this->ValidateDate($params['datum']);
            } catch (ValidationException $e) {
                $error[] = 'datum';
                $valid = false;
            }//catch
        }//if
        
        //At least one search criteria must be entered for the query
        if ((isset ($params['keywords']) and $params['keywords'] == ''          ) and
            (isset ($params['name'])     and $params['name']     == ''          ) and
            (isset ($params['originid']) and $params['originid'] == 'noChoose'  ) and
            (isset ($params['typeid'])   and $params['typeid']   == 'noChoose'  ) and
            (isset ($params['formatid']) and $params['formatid'] == 'noChoose'  ) and
            (isset ($params['keywords']) and $params['keywords'] == ''          ) and
            (isset ($params['date'])     and $params['date']     == 'noChoose'  ) and
            ((isset ($params['datum'])   and ($params['datum'] == '' ) or $params['datum'] == 'tt.mm.jjjj'))) {
            $error[] = 'all';
            $valid = false;
        }//if
       
        if ($valid) {
            return true;
        } else {
            throw new QueryValidationException ('Validation of query parameters failed.', $params, $error);
        }//else 
    }//ValidateQueryParameters

    /**
     * Validates the user input entered for document creations. This
     * method validates the following keys of the passed <c>array</c>:
     * <li>erstelltDatum</li> 
     * <li>gueltigVonDatum</li> 
     * <li>gueltigBisDatum</li> 
     * <li>title</li>
     * <li>formatid</li>
     * <li>typeid</li>
     * <li>originid</li>
     * <li>uname</li>
     * @param array $paramsSreachQuery The user input from the document creation table
     * @return array The returned <c>array</c> contains a valid information 
     *               of each of the keys specified above, as well as an entry 
     *               'error' that indicates if there was an error during the
     *               validation (<c>true</c>) or not (<c>false</c>).
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ValidateDocCreation ($params) {
        $errors = array ();
        $isValid = true;
        
        if (count($params) > 0) {
            
            $fails =$this->CheckDateParameter($params, 'geaendertDatum');
            
            if (count($fails) > 0) {
                $errors[] = 'geaendertDatum';
                $isValid = false;
            }//if
            
            $fails =$this->CheckDateParameter($params, 'erstelltDatum');
            if (count($fails) > 0) {
                $errors[] = 'erstelltDatum';
                $isValid = false;
            }//if
            
            $fails =$this->CheckDateParameter($params, 'gueltigVonDatum');
            if (count($fails) > 0) {
                $errors[] = 'gueltigVonDatum';
                $isValid = false;
            }//if
            
            $fails =$this->CheckDateParameter($params, 'gueltigBisDatum');
            if (count($fails) > 0) {
                $errors[] = 'gueltigBisDatum';
                $isValid = false;
            }//if
            
            if (isset ($params['title'])) {
                if ($params['title'] == '') {
                    $isValid = false;
                    $errors[] = 'title';
                }//if
            }//if
            
            if (isset ($params['formatid'])) {
                if (($params['formatid'] == 'noChoose')  or
                    ($params['formatid'] == '')            ) {
                    $isValid = false;
                    $errors[] = 'formatid';
                }//if
            }//if
            
            if (isset ($params['typeid'])) {
                if (($params['typeid'] == 'noChoose') or
                    ($params['typeid'] == '')            ) {
                    $isValid = false;
                    $errors[] = 'typeid';
                }//if
            }//if
            
            if (isset ($params['originid'])) {
                if (($params['originid'] == 'noChoose')  or
                    ($params['originid'] == '')            ) {
                    $isValid = false;
                    $errors[] = 'originid';
                }//if
            }//if
            
            if (isset ($params['uname'])) {
                if ($params['uname'] == '') {
                    $isValid = false;
                    $errors[] = 'uname';
                }//if
            }//if
        } else {
            $isValid = false;
            $errors[] = 'empty';
        }//else
        
        if (!$isValid) {
            throw new DocumentCreationException ('Validation of document creation parameters failed.', $params, $errors);
        } else {
            return true;
        }//else
    }//ValidateDocCreation

    private function CheckDateParameter ($params, $dateName) {
        if (isset ($params[$dateName])) {
            if (!$this->ValidateDate($params[$dateName]) and 
                !(($params[$dateName] == '') or ($params[$dateName]) == 'tt.mm.jjjj')){
                $fails[] = $dateName;
                return $fails;
            } else {
                return array();
            }//else
        }//if
    }//CheckDateParameter
    
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
    
}//Class: ValidationService
 
?>
