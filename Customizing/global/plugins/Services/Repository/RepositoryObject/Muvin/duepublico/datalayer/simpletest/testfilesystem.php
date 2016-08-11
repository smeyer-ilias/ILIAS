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
 * @abstract Test client for the <c>FilesystemService</c> implementation.
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

require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_filesystemservice.php');
require_once ($CFG->dirroot.'/lib/duepublico/datalayer/duep_courseobject.php');
define('TEST_BASE', $CFG->dataroot.'temp');

class filesystem_test extends UnitTestCase {
   
    private $filesystem;
    
    public function setUp() {
        $this->filesystem = FilesystemService::GetFilesystemService ();   
        //$this->filesystem->RemoveDirectories(TEST_BASE);
        $this->SetUpTestEnvironment();
    }//setUp
    
    public function tearDown () {
        @unlink(TEST_BASE.'/f/first/second/third/test.pdf');
        @unlink(TEST_BASE.'\sb\first\second\third\test.pdf');
        @unlink(TEST_BASE.'\\first\\second\\third\\test.pdf');
        
        //$this->filesystem->RemoveDirectories(TEST_BASE);
    }//tearDown
    
    function SetUpTestEnvironment () {
        $this->filesystem->CreateDirectories (TEST_BASE);
        
        $this->filesystem->CreateDirectories (TEST_BASE.'/f/file/second/third/testfile.php');
        $this->filesystem->CreateDirectories (TEST_BASE.'/f/first/second/third');
        $this->filesystem->CreateDirectories (TEST_BASE.'/f/second/third/');
        $this->filesystem->CreateDirectories (TEST_BASE.'/f/with/umlautsäöü/');
        $this->filesystem->CreateDirectories (TEST_BASE.'/f/with2/umlautsäöü');
        
        $this->assertTrue (file_exists (TEST_BASE.'/f/file/second/third/'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/first/second/third/'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/second/third/'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/with/umlautsäöü/'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/with2/umlautsäöü/'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/file/second/third'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/first/second/third'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/second/third'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/with/umlautsäöü'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/with2/umlautsäöü'));
        
        $this->filesystem->CreateDirectories (TEST_BASE.'\sb\file\\second\third\testfile.php');
        $this->filesystem->CreateDirectories (TEST_BASE.'\sb\first\second\third');
        $this->filesystem->CreateDirectories (TEST_BASE.'\sb\second\third\\');
        $this->filesystem->CreateDirectories (TEST_BASE.'\sb\with\umlautsäöü\\');
        $this->filesystem->CreateDirectories (TEST_BASE.'\sb\with2\umlautsäöü');
    
        $this->assertTrue (file_exists (TEST_BASE.'\sb\file\\second\third\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\first\second\third\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\second\third\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\with\umlautsäöü\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\with2\umlautsäöü\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\file\\second\third'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\first\second\third'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\second\third'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\with\umlautsäöü'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\with2\umlautsäöü'));
    
        $this->filesystem->CreateDirectories (TEST_BASE.'\\db\\file\\second\\third\\testfile.php');
        $this->filesystem->CreateDirectories (TEST_BASE.'\\db\\first\\second\\third');
        $this->filesystem->CreateDirectories (TEST_BASE.'\\db\\second\\third\\');
        $this->filesystem->CreateDirectories (TEST_BASE.'\\db\\with\\umlautsäöü\\');
        $this->filesystem->CreateDirectories (TEST_BASE.'\\db\\with2\\umlautsäöü');
    
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\file\\second\\third\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\first\\second\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\second\\third\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\with\\umlautsäöü\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\with2\\umlautsäöü\\'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\file\\second\\third'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\first\\second'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\second\\third'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\with\\umlautsäöü'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\with2\\umlautsäöü'));
    
        $this->filesystem->WriteFile('TestContent', TEST_BASE.'/f/first/second/third/test.pdf');
        $this->filesystem->WriteFile('TestContent', TEST_BASE.'\sb\first\second\third\test.pdf');
        $this->filesystem->WriteFile('TestContent', TEST_BASE.'\\first\\second\\third\\test.pdf');
    }//SetUpTestEnvironment
    
    function testDownloadDocument () {
        $url = 'http://radar.oreilly.com/2009/04/jack-dangermond-interview-web-mapping.html';
        $destination = TEST_BASE.'/f/first/jack-dangermond-interview-web-mapping.html.html';
        $this->assertFalse (file_exists ($destination));
        $this->filesystem->DownloadDocument($url, $destination);
        $this->assertTrue (file_exists ($destination));
        $this->assertTrue (@unlink ($destination));
        $this->assertFalse (file_exists ($destination));
    }//testDownloadDocument
    
    function testDeleteDirectory () {
        $this->assertTrue(@unlink(TEST_BASE.'/f/first/second/third/test.pdf'));
        $this->assertTrue(@unlink(TEST_BASE.'\sb\first\second\third\test.pdf'));
        $this->assertTrue(@unlink(TEST_BASE.'\\first\\second\\third\\test.pdf'));
        
        $this->assertTrue (file_exists  (TEST_BASE.'/f/first/'));
        $this->assertTrue (file_exists  (TEST_BASE.'\sb\first\\'));
        $this->assertTrue (file_exists  (TEST_BASE.'\db\\first\\'));
        
        $this->filesystem->RemoveDirectories(TEST_BASE.'/f/first/');
        $this->filesystem->RemoveDirectories(TEST_BASE.'\sb\first\\');
        $this->filesystem->RemoveDirectories(TEST_BASE.'\\db\\first\\');
        
//        $this->assertFalse (file_exists  (TEST_BASE.'/f/first/'));
//        $this->assertFalse (file_exists  (TEST_BASE.'\sb\first\\'));
//        $this->assertFalse (file_exists  (TEST_BASE.'\\db\\first\\'));
        
        $this->assertTrue (file_exists  (TEST_BASE.'/f/second'));
        $this->assertTrue (file_exists  (TEST_BASE.'\sb\second'));
        $this->assertTrue (file_exists  (TEST_BASE.'\\db\\second'));
        
        $this->filesystem->RemoveDirectories(TEST_BASE.'/f/second');
        $this->filesystem->RemoveDirectories(TEST_BASE.'\sb\second');
        $this->filesystem->RemoveDirectories(TEST_BASE.'\\db\\second');
        
        $this->assertFalse (file_exists  (TEST_BASE.'/f/second'));
        $this->assertFalse (file_exists  (TEST_BASE.'\sb\second'));
        $this->assertFalse (file_exists  (TEST_BASE.'\\db\\second'));
    }//testDeleteDirectory
    
    function testWriteFileMethod () {
        $this->filesystem->WriteFile('This is the content of the test file.', TEST_BASE.'/f/first/test1.txt');
        $this->filesystem->WriteFile('This is the content of the test file.', TEST_BASE.'\\db\\first\\test1.txt');
        $this->filesystem->WriteFile('This is the content of the test file.', TEST_BASE.'\sb\first\test1.txt');
        
        $this->assertTrue (file_exists (TEST_BASE.'/f/first/test1.txt'));
        $this->assertTrue (file_exists (TEST_BASE.'\\db\\first\\test1.txt'));
        $this->assertTrue (file_exists (TEST_BASE.'\sb\first\test1.txt'));
        
        $this->assertEqual ('This is the content of the test file.', $this->filesystem->ReadFileContent(TEST_BASE.'/f/first/test1.txt'));
        $this->assertEqual ('This is the content of the test file.', $this->filesystem->ReadFileContent(TEST_BASE.'\\db\\first\\test1.txt'));
        $this->assertEqual ('This is the content of the test file.', $this->filesystem->ReadFileContent(TEST_BASE.'\sb\first\test1.txt'));
        
        try {
            $this->filesystem->ReadFileContent(TEST_BASE.'/f/first/test2.txt');
        } catch (FileException $e) {
            $this->assertEqual ($e->__toString(), "FileException [0]: File ".TEST_BASE."/f/first/test2.txt doesn't exist.\n");
        }//catch
        
        try {
            $this->filesystem->ReadFileContent(TEST_BASE.'\\db\\first\\test2.txt');
        } catch (FileException $e) {
            $this->assertEqual ($e->__toString(), "FileException [0]: File ".TEST_BASE."\\db\\first\\test2.txt doesn't exist.\n");
        }//catch
        
        try {
            $this->filesystem->ReadFileContent(TEST_BASE.'\sb\first\test2.txt');
        } catch (FileException $e) {
            $this->assertEqual ($e->__toString(), "FileException [0]: File ".TEST_BASE."\sb\\first\\test2.txt doesn't exist.\n");
        }//catch
        
        $this->assertTrue (@unlink(TEST_BASE.'/f/first/test1.txt'));
        $this->assertTrue (@unlink(TEST_BASE.'\\db\\first\\test1.txt'));
        $this->assertTrue (@unlink(TEST_BASE.'\sb\first\test1.txt'));
    }//testWriteFileMethod
    
    function testCopyFile () {
        $this->filesystem->WriteFile('This is the content of the test file.', TEST_BASE.'/f/first/test1.txt');
        
        $this->assertTrue (file_exists (TEST_BASE.'/f/first/test1.txt'));
        $this->assertFalse (file_exists (TEST_BASE.'/f/first/test2.txt'));
        
        $this->assertTrue ($this->filesystem->CopyFile(TEST_BASE.'/f/first/test1.txt', 
                                                       TEST_BASE.'/f/first/test2.txt'));
        $this->assertTrue (file_exists (TEST_BASE.'/f/first/test2.txt'));
        $this->assertTrue (@unlink (TEST_BASE.'/f/first/test1.txt'));
        $this->assertTrue (@unlink (TEST_BASE.'/f/first/test2.txt'));
    }//testCopyFile
    
}//Class: testFileSystemService
?>
