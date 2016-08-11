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
 * @abstract This class contains all Miless/MyCoRe data that are stored for 
 *           a single Moodle course.
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
class CourseObject {

    /**
     * Name of the user that has read access to course documents.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $readUserUname;
    /**
     * Password of the user account that has read access.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $readUserPword;
    /**
     * Name of the user account that has write access.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $writeUserUname;
    /**
     * Password of the user account with write access.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $writeUserPword;
    /**
     * Title of the Miless/MyCoRe document that is used by the 
     * Moodle course to which this instance is assigned.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $courseDocumentTitle;
    /**
     * Unique identifier of the Miless/MyCoRe document that is 
     * used by the Moodle course to which this instance is assigned.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $documentId;
    /**
     * The Moodle course identifier of the course to which this
     * instance is assigned.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $courseId;
    
    /**
     * Default constructor of this class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct () {
        $this->readUserUname = '';
        $this->readUserPword = '';
        $this->writeUserUname = '';
        $this->writeUserPword = '';
        $this->courseDocumentTitle = '';
        $this->documentId = -1;
        $this->courseId = -1;
    }//Costructor
    
    /**
     * Sets the Moodle course identifier to the passed
     * integer.
     * @param integer $courseId The new Moodle course identifier of this instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetCourseId ($courseId) {
        $this->courseId = $courseId;
    }//SetCourseId
    
    /**
     * Sets the Miless/MyCoRe document title to the passed <c>string</c>.
     * @param string $documentTitle The new Miless/MyCoRe document title.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetDocumentTitel ($documentTitle) {
        $this->courseDocumentTitle = $documentTitle;
    }//SetDocumentTitle
    
    /**
     * Sets the Miless/MyCoRe document identifier to the passed
     * integer.
     * @param integer $documentId The new Miless/MyCoRe document idenifier.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetDocumentId ($documentId) {
        $this->documentId = $documentId;
    }//SetDocumentId
    
    /**
     * Sets the user account that has read access.
     * @param string $uname The new username of the read access account.
     * @param string $pword The new password of the read access account.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetReadUser ($uname, $pword) {
        $this->readUserUname = $uname;
        $this->readUserPword = $pword;
    }//SetReadUser
    
    /**
     * Sets the user account that has write access.
     * @param string $uname The new username of the write access account.
     * @param string $pword The new password of the write access account.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetWriteUser ($uname, $pword) {
        $this->writeUserUname = $uname;
        $this->writeUserPword = $pword;
    }//SetWriteUser
    
    /**
     * Deserializes the <c>CourseObject</c> from the passed <c>string</c>
     * and sets the members of this instance to the values read from the
     * deserialized instance.
     * @param string $string A serialized <c>CourseObject</c> instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function UnserializeObject ($string) {
        $object = unserialize ($string);
        if ($object instanceof CourseObject) {
            $this->readUserUname = $object->readUserUname;
            $this->readUserPword = $object->readUserPword;
            $this->writeUserUname = $object->writeUserUname;
            $this->writeUserPword = $object->writeUserPword;
            $this->courseDocumentTitle = $object->courseDocumentTitle;
            $this->documentId = $object->documentId;
            $this->courseId = $object->courseId;
            return true;
        } else {
            throw InvalidArgumentException ("Passed $string is not a valid serialized CourseObject instance.");
        }//else
    }//DeSerialize
    
    /**
     * Returns a serialized version of this instance in form of 
     * a <c>string</c>.
     * @return string The serialized version of this instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
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
    
}//Class: CourseObject 
?>
