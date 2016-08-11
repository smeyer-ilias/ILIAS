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
 * @abstract Test client for the <c>CourseObject</c> class implememtation.
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

require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_courseobject.php');

class testcourseobject extends UnitTestCase {

    private $courseObject;

    public function setUp () {
        $this->courseObject = new CourseObject ();
        $this->courseObject->readUserUname = 'uread';
        $this->courseObject->readUserPword = 'pread';
        $this->courseObject->writeUserUname = 'uwrite';
        $this->courseObject->writeUserPword = 'pwrite';
        $this->courseObject->courseDocumentTitle = 'Course document title';
        $this->courseObject->documentId = '12124';
        $this->courseObject->courseId = '2';
    }//setUp
    
    public function tearDown () {
        $this->courseObject = null;
    }//tearDown    

    function testDeAndSerialization () {
        $ser = $this->courseObject->SerializeObject ();
        
        $newObject = new CourseObject ();
        $this->assertEqual ('', $newObject->readUserUname);
        $this->assertEqual ('', $newObject->readUserPword);
        $this->assertEqual ('', $newObject->writeUserUname);
        $this->assertEqual ('', $newObject->writeUserPword);
        $this->assertEqual ('', $newObject->courseDocumentTitle);
        $this->assertEqual (-1, $newObject->documentId);
        $this->assertEqual (-1, $newObject->courseId);
        
        $newObject->UnserializeObject ($ser);
        
        $this->assertEqual ('uread', $newObject->readUserUname);
        $this->assertEqual ('pread', $newObject->readUserPword);
        $this->assertEqual ('uwrite', $newObject->writeUserUname);
        $this->assertEqual ('pwrite', $newObject->writeUserPword);
        $this->assertEqual ('Course document title', $newObject->courseDocumentTitle);
        $this->assertEqual (12124, $newObject->documentId);
        $this->assertEqual (2, $newObject->courseId);
        
    }//testDeAndSerialization

}//Class: testtestcourseobject
?>
