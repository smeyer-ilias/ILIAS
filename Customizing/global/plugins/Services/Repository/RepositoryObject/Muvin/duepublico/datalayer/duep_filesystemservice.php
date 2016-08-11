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
 * @abstract This singelton class provides the functionality required to 
 *           perform filesystem operations like reading and copying files. 
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


 
class FilesystemService {
 
    /**
     * Holds the initialized singelton instance of this class.
     * @access private
     */
    private static $service = null;
 
    /**
     * Default constructor of this class that initilizes 
     * the singelton instance.
     */
    private function __construct () {
    }//Constructor
 
    /**
     * Returns the instance of this singelton class.
     * @return FilesystemService The singelton instance of this class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public static function GetFileSystemService () {
        if (null == self::$service) {
            self::$service = new FilesystemService ();
        }//if
        return self::$service;
    }//GetDataLayerFacade
 
    /**
     * Reads the stored doXMLQuery webservice response
     * and returns the content of the XML file.
     * @param string $resultFile The path of the XML file
     * @return mixed A <c>XMLElement</c> if the file could be read 
     *               otherwise <c>null</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ReadResultTemp ($resultFile) {
        $xml = unserialize($this->ReadFileContent ($resultFile));
        if ($xml instanceof XMLElement) {
            return $xml;
        } else {
            throw new FileCacheException ('Could not read search results from temp file.', $resultFile);
        }//else
        /*
        if ($xml != null) {
            require_once('../XmlParser/duep_XMLParser.php');
            $parser = new XMLParser ($xml);
            $xml = $parser->GetXmlArray();
            return $xml->GetChildXpath('/Body/doXMLQueryResponse/doXMLQueryReturn/results');
        } else {
            throw new FileCacheException ('Could not read search results from temp file.', $resultFile);
        }//else
        */
    }//ReadResultTemp
     
    /**
     * Returns the content of the file, specified by the passed filename.
     * @param string $path The path of the file that should be read
     * @return mixed A <c>string</c> that contains the file's content or 
     *               an <c>array</c> that contains the error information.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ReadFileContent ($path) {
        if (!is_file($path)) {
            throw new FileException ('File '.$path.' doesn\'t exist.', $path);
        }//if
        if (!$file = fopen($path, 'r')) {
            throw new FileException ('Could not open file '.$path, $path);
        }//if
        
        $content = '';
        while (!feof($file)) {
            $content .= fread($file, 2048);
        }//while
        
        fclose($file);
        return $content;
    }//ReadFile
    
    /**
     * Writes the passed <c>string</c> to a file, specified
     * by the passed path.
     * @param string $content The content of the file
     * @param string $path The path of the file
     * @return bool <c>True</c> if the passed <c>$content</c> was 
     *              succesfully written to the file specified by
     *              <c>$path</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function WriteFile ($content, $path) {
        $this->CreateDirectories ($path);
        $file = fopen($path, 'w+');
        fwrite($file, $content);
        fclose($file);
        return true;
    }//WriteFile

    /**
     * Removed the directory an all its sub-directories specified
     * by <c>$path</c>. If <c>$path</c> is not a valid directory an
     * <c>InvalidArgumentException</c> is thrown.
     * @param string $path The root direcotry which should be deleted.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function RemoveDirectories ($path) {
        if (file_exists ($path)) {
            $dirData = array_diff(scandir($path), array('.', '..'));
            foreach ($dirData as $v) {
                $v = $path.'\\'.$v;
                if (is_dir ($v)) {
                    $this->RemoveDirectories($v);
                } else if (is_file ($v)) {
                    unset ($v); 
                }//else-if
            }//foreach
            rmdir ($path);
        } else {
            throw new InvalidArgumentException ("FilesystemService [RemoveDirectories]: Passed path $path does not exits.");
        }//else
    }//RemoveDirectories

    /**
     * Creates recursively the directories with in the passed <c>$path</c>. If
     * a path like '/var/usr/temp' or 'C:\temp\test' is path to this method
     * even the last <c>string</c>s are interpreted as directories althouh
     * the used {@link pathinfo ()} function interprets them as a file. This why
     * the extension key of the <c>array</c> returned bis this function is checked 
     * with {@link isset ()}. If it is not set the basename - the last <c>string</c>
     * of the above given examples is added to the dirname.
     * @param string $path An absolute path for which the directories should be created.
     * @return mixed Returns <c>true</c> if the directories were created successfully, 
     *               otherwise an <c>array</c> that contains error information of 
     *               the directory creation is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateDirectories ($path) {
        $pathInfo = array ();
        $pathInfo = pathinfo($path);
        if ((!is_null ($pathInfo)) and (!file_exists($pathInfo['dirname']))) {
            $dir = $pathInfo['dirname'];
            if (!isset ($pathInfo['extension'])) {
                $dir .= '/'.$pathInfo['basename'];
            }//if
            
            if (mkdir ($dir, '0700', true)) {
                return true;
            } else {
                throw new FileCacheException ('Error while creating directories '.$path, $path);
            }//else
        } else {
            return true;
        }//else
    }//CreateDirectories
    
    /**
     * Copies the file, specified by <c>$source</c> to a new directory
     * specified by <c>$destination</c>.
     * @param string $source The source path
     * @param string $destination The destination path
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CopyFile ($source, $destination) {
        $this->CreateDirectories($destination);
        return copy ($source, $destination);
    }//CopyFile
    
    /**
     * Adds a slash to the end of the passed path.
     * @param string $path A path with or without a slash at the end.
     * @return string The {@link $path $path} passed to this method, to which a slash was added if not allready existend.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function AddSlash ($path) {
        if ($this->EndsWithAny ($path, array ('/', '\\'))) {
            return $path;
        } else {
            return $path.'/';
        }//else
    }//AddSlash
    
    /**
     * Checks if the passed <c>$string</c> ends with
     * one of the <c>string<c>s in the passed 
     * <c>array</c> <c>$endings</c>.
     * @param string $string The <c>string</c> to be check.
     * @param string $endings An <c>array</c> of <c>string</c>s used for the comparison.
     * @return bool <c>True</c> if <c>$string</c> ends with one of the <c>string</c>s in <c>$endings</c>; oterhwise <c>false</c> is returned.
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
     * @return bool <c>True</c> if <c>$string</c> ends with <c>$ending</c>; oterhwise 
     *              <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function EndsWith ($string, $ending) {
       $len = strlen($ending);
       $original = substr($string, strlen($string) - $len);
       return $original == $ending;
    }//EndsWith
 
    
    /**
     * Creates an <c>array</c> with the names of the files stored
     * in the backupdata directory of a moodle course specified by
     * <c>$courseId</c>.
     * @param integer $courseId The id of a specific Moodle course
     * @return array That contains all founded backups within the backup directory
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetCourseBackups($courseId) {
        global $CFG;
        
        require_once($CFG->dirroot.'/lib/filelib.php');
        //If the admin has changed the backup directory then it will be saved in
        //the following array. The default directory will be the backupdata directory
        //in the couse directory as specified in the if statement
        require_once($CFG->dirroot.'/backup/lib.php');
        $backupconfig = backup_get_config();
        
        if (($path = (string) $backupconfig->backup_sche_destination) == '') {
            $path = $CFG->dataroot.'/'.$courseId.'/backupdata';
        }//if
        
        $backups = array ();
        $dir  = opendir ($path);
        while ($file = readdir ($dir)) {
            if (is_dir ($path.$file) == false) {
                if (($file == '.') or ($file == '..')) {
                    //Exclude symbolic U/Linux directories  
                } else if (mimeinfo('type', $file) != 'application/zip') {
                    //exclude directories
                } else {
                    $backups[] = array ('value' => $file, 'label' => $file, 'title' => $file);
                }//else
            }//if 
        }//while
        closedir ($dir);
        return $backups;
    }//GetCourseBackups
     
    /**
     * Downloads a document specified by the passed <c>$url</c>
     * and stores it in a file, specified by <c>$destination</c>.
     * @param string $url The Url from which the document should be read.
     * @param string $destination An absolute path to which the document should be written.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */    
    function DownloadDocument ($url, $destination) {
        $file = fopen ($url, 'rb');   
        $this->CreateDirectories($destination);
        $handler = fopen ($destination,'wb');
        if ((!$file) or (!$handler)) {   
            throw new FileException ('Could not open remote file from '.$url, $url);
        } else {        
            $line = '';
            while (!feof ($file)) {
                $line = fread ($file, 1024); 
                fwrite ($handler, $line, 1024);
            }//while
            fclose ($file);
            fclose ($handler);
            return true;
        }//else
    }//DownloadFile

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
    
    public function GetCourseObjects ($dataroot) {
        global $CFG;
        $courseObjects = array ();
        if (is_dir ($dataroot)) {
            $dirData = array_diff(scandir($dataroot), array('.', '..'));
            foreach ($dirData as $v) {
                $v = $dataroot.'/'.$v;
                if (is_dir ($v)) {
                    if (file_exists($v.'/temp/courseObject.ser')) {
                        $courseObject = new CourseObject ();
                        $courseObject->UnserializeObject($this->ReadFileContent($v.'/temp/courseObject.ser'));
                        $courseObjects[] = $courseObject;
                    }//if
                }//if
            }//foreach
        } else {
            throw new InvalidArgumentException ("FilesystemService [GetCourseObjects]: Passed path $path does not exits.");
        }//else
        return $courseObjects;
    }//GetCourseObjects
    
    
    
}//Class: FilesystemService
?>
