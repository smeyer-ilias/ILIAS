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

class StateObject {
    
    private $hiddenParams;
    private $globalAuthorId;
    
    public function __construct () {
        $this->hiddenParams = array ();
        $this->globalAuthorId = -1;
    }//Constructor
    
    public function SetGobalAuthorId ($globalAuthorId) {
        $this->globalAuthorId = $globalAuthorId;
    }//SetGlobalAuthorId
    
    public function GetGlobalAuthorId () {
        return $this->globalAuthorId;
    }//GetGlobalAuthorId
    
    public function SetHiddenParameter ($hiddenParameters) {
        $this->hiddenParams = $hiddenParameters;
    }//SetHiddenParameter
    
    public function NumberOfHiddenParameters () {
        return count($this->hiddenParams);
    }//NumberOfHiddenParameters
    
    public function GetHiddenParameters () {
        return $this->hiddenParams;
    }//GetHiddenParameters
    
    public function AddHiddenParameter ($key, $value) {
        if (array_key_exists($key, $this->hiddenParams)) {
            throw HiddenParameterException ('Could not add hidden parameter because the array already contains such a key.', $key, $value, 1);    
        } else {
            $this->hiddenParams[$key] = $value;
            return true;
        }//else
    }//AddHiddenParameter
    
    public function OverrideHiddenParameter ($key, $value) {
        if (array_key_exists($key, $this->hiddenParams)) {
            $this->hiddenParams[$key] = $value;
            return true;    
        } else {
            throw HiddenParameterException ('Could not override hidden parameter because the array does not contain such a key.', $key, $value, 2);
        }//else
    }//OverrideHiddenParameter
    
    public function RemoveHiddenParameter ($key) {
        if (array_key_exists($key, $this->hiddenParams)) {
            unset($this->hiddenParams[$key]);
            return true;    
        } else {
            throw HiddenParameterException ('Could not remove hidden parameter because the array does not contain such a key.', $key, '', 3);
        }//else
    }//RemoveHiddenParameter
    
    public function UnserializeObject ($string) {
        $object = unserialize ($string);
        if ($object instanceof StateObject) {
            $this->globalAuthorId = $object->GetGlobalAuthorId ();
            $this->hiddenParams = $object->GetHiddenParameters ();
            return true;
        } else {
            throw InvalidArgumentException ('Passed $string is not a valid serialized StateObject instance.');
        }//else
    }//UnserializeObject
    
    public function SerializeObject () {
        return serialize ($this);
    }//Serialize
    
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
    
}//Class: StateObject
?>
