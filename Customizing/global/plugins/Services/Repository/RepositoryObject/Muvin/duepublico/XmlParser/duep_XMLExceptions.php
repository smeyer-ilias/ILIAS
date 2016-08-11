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

class XmlParserException extends Exception {
    
    /**
     * Default constructor of the <c>XmlParserException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor
    
    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}//XmlParserException

class ChildNotFoundException extends Exception {
    
    /**
     * Default constructor of the <c>ChildNotFoundException</c> class.
     */
	public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//ChildNotFoundException

class AttributeNotFoundException extends Exception {
    
    /**
     * Default constructor of the <c>AttributeNotFoundException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//AttributeNotFoundException

class NoChildrenException extends Exception {
    
    /**
     * Default constructor of the <c>NoChildrenException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//ElementHasNoChildrenException

class ChildNotValidException extends Exception {
    
    /**
     * @var The element that should be added as a child to <c>XMLElement</c>.
     */
    public $child;
    
    /**
     * Default constructor of the <c>ChildNotValidException</c> class.
     */
    public function __construct($message, $child, $code = 0) {
        parent::__construct($message, $code);
        $this->child = $child;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }//toString
   
}//ChildNotValidException

class NoParentElementException extends Exception {
    
    /**
     * Default constructor of the <c>NoParentElementException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//NoParentElementException
class NoImplementedException extends Exception {
    
    /**
     * Default constructor of the <c>NoImplementedException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//NoImplementedException

class JustOneParentElementException extends Exception {
    
    /**
     * Default constructor of the <c>JustOneParentElementException</c> class.
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}//JustOneParentElementException
?>
