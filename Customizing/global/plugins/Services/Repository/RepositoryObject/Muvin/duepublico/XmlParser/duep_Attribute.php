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
 
class Attribute {
	
	/**
	 * Name of this instance
	 * @access private
	 */
	private $name;
	/**
	 * Value of this attribute
	 * @access private
	 */
	private $value;
	/**
	 * Type of this instance
	 * @access private
	 */
	private $type;
	/**
	 * The attribute's namespace
	 * @access private
	 */
	private $namespaceValue;
	
	/**
	 * Default constructor of this class that creates a new instance of
	 * this XML attribute class.
	 * @param string $name Name of this attribute
	 * @param string $value Value of this instance
	 * @param string $type Type of this instance. Default value is an empty string
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function __construct ($name, $value, $type = '', $namespace) {
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
	}//Constructor
	
	/**
	 * Returns the namespace of this instance.
	 * @return string Namespace of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetNamespace () {
		return $this->namespaceValue;
	}//GetNamespace
	
	/**
	 * Returns the name of this instance.
	 * @return string Name of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetName () {
		return $this->name;
	}//GetName
	
	/**
	 * Returns the value of this instance
	 * @return string Value of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetValue () {
		return $this->value;
	}//GetValue
	
	/**
	 * Returns the type of this instance or an empty string
	 * if now value is set.
	 * @return string Value of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetAttributeType () {
		return $this->type;
	}//GetAttributeType
	
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
    
}//class Attribute
?>
