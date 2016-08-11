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

class PersonNotExistsException extends Exception {
    /**
     * The unique identifier of the legal entity that does not
     * exists on the Miless/MyCoRe server.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $legalEntityId;
    
    /**
     * Default constructor of the <c>PersonNotExistsException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($legalEntityId) {
        parent::__construct('LegalEntity with id '.$legalEntityId.' does not exist.', 0);
        $this->legalEntityId = $legalEntityId;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
}//PersonNotExistsException 

class UploadFailedException extends Exception {
    /**
     * The unique identifier of the derivate to which
     * a file or directory should be added.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $derivateId;
    
    /**
     * Default constructor of the <c>UploadFailedException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($derivateId) {
        parent::__construct('Upload to '.$derivateId.' failed.', 0);
        $this->derivateId = $derivateId;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
}//UploadFailedException

class ConfigException extends Exception {
    
    /**
     * The parameter in the configuration file (see <c>duepconfig.php</c>)
     * which is not configured properly.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $parameter;
    
    /**
     * Default constructor of the <c>ConfigException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameter, $code = 0) {
        parent::__construct($message, 0);
        $this->parameter= $parameter;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
}//ConfigException 


class GlobalProfilNotSetException extends Exception {
    
    /**
     * Default constructor of the <c>GlobalProfilNotSetException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct() {
        parent::__construct('The global author profile is not set.', 0);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
}//GlobalProfilNotSetException 

class DocumentNotFoundException extends Exception {
    
    /**
     * The unique identifier of the document instance that was
     * not found on the Miless/MyCoRe server.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $documentIdentifier;
    
    /**
     * Default constructor of the <c>DocumentNotFoundException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($documentIdentifier) {
        parent::__construct('A document identified by '.$documentIdentifier.' does not exists.', 0);
        $this->documentIdentifier = $documentIdentifier;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
}//DocumentNotFoundException 

class UploadFileException extends Exception {
    
    /**
     * The unique identifier of the derivate to which a file
     * should be uploaded; whereas the upload failed.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $derivateIdentifier;
    
    /**
     * Default constructor of the <c>UploadFileException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($derivateIdentifier) {
        parent::__construct('Upload of files to derivate identified by '.$derivateIdentifier.' failed.', 0);
        $this->derivateIdentifier = $derivateIdentifier;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": {$this->message} \n";
    }//__toString
    
}//UploadFileException 

class ValidationException extends Exception {
    
    /**
     * An <c>array</c> of parameters that are not valid.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $parameter;
    
    /**
     * Default constructor of the <c>ValidationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameter, $code = 0) {
        parent::__construct($message, $code);
        $this->parameter = $parameter;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . " [{$this->code}]: {$this->message}\n";
    }//__toString
    
}//Class: ValidationException

class WebServiceException extends Exception {

    /**
     * Name of the web service that reported an error.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $webservice;
    /**
     * Name of the web service's method that was called.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $method;
    /**
     * An <c>array</c> of parameters passed as arguments to the
     * web servic's method.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $parameters;
    
    /**
     * Default constructor of the <c>WebServiceException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($webservice, $method, $message, $parameters, $code = 0) {
        parent::__construct($message, $code);
        $this->webservice = $webservice;
        $this->method = $method;
        $this->parameters = $parameters;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": $this->webservice::$this->method [{$this->code}]: {$this->message}\n";
    }//__toString


}//Class: WebServiceException

class WebServiceReturnException extends WebServiceException {
    
    /**
     * Default constructor of the <c>WebServiceReturnException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($webservice, $method, $message, $code = 0) {
        parent::__construct($webservice, $method, $message, null, $code);
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": $this->webservice::$this->method [{$this->code}]: {$this->message}\n";
    }//__toString
    
}//WebServiceReturnException

class FileException extends Exception {
    
    /**
     * Absolute path of the file for which this exceptions was thrown.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $path;
   
   /**
     * Default constructor of the <c>CacheException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $path, $code = 0) {
        parent::__construct($message, $code);
        $this->path = $path;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . " [{$this->code}]: {$this->message}\n";
    }//__toString
   
}//Class: FileException

class CacheException extends Exception {
    
    /**
     * Type of the cache; possible values are: FileCache and MemoryCache.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $cacheType;
    
    /**
     * Default constructor of the <c>CacheException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $cacheType, $code = 0) {
        parent::__construct($message, $code);
        $this->cacheType = $cacheType;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": $this->cacheType: [{$this->code}]: {$this->message}\n";
    }//__toString
}//Class: CacheException

class FileCacheException extends CacheException {
    
    /**
     * Absolute path of the file for which this exception was thrown.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $path;
   
   /**
     * Default constructor of the <c>CacheException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $path, $code = 0) {
        parent::__construct($message, 'File', $code);
        $this->path = $path;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . ": $this->cacheType: [{$this->code}]: {$this->message}\n";
    }//__toString
   
}//Class: FileCacheException

class CreationException extends Exception {
    
    /**
     * Parameters of passed to the method that has thrown this exception.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $parameters;
    
    /**
     * Default constructor of the <c>CreationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameters, $code = 0) {
        parent::__construct($message, $code);
        $this->parameters = $parameters;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . " [{$this->code}]: {$this->message}\n";
    }//__toString
    
}//Class: CreationException

class DocumentCreationException extends CreationException {
    
    /**
     * An <c>array</c> of validation information.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $fails;
    
    /**
     * Default constructor of the <c>DocumentCreationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameters, $fails, $code = 0) {
        parent::__construct($message, $parameters, $code);
        $this->fails = $fails;
    }//Constructor
    
}//Class: DocumentCreationException

class QueryValidationException extends Exception {
    
    /**
     * An <c>array</c> of parameters which should be used for querying
     * the Miless/MyCoRe server.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $parameters;
    /**
     * Validation information of the parameters specified above.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $errors;
    
    /**
     * Default constructor of the <c>QueryValidationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameters, $errors, $code = 0) {
        parent::__construct($message, $code);
        $this->parameters = $parameters;
        $this->errors = $errors;
    }//Constructor    
}//Class: QueryValidationException

class NoSearchResultsException extends Exception {
    /**
     * Default constructor of the <c>NoSearchResultsException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }//Constructor
}//NoSearchResultsException

class LegalEntityCreationException extends CreationException {
    
    /**
     * Validation information of the parameters which should
     * be used to create a new legal entity on the Miless/MyCoRe
     * server.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $error;
    
    /**
     * Default constructor of the <c>LegalEntityCreationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $parameters, $error, $code = 0) {
        parent::__construct($message, $parameters, $code);
        $this->error = $error;
    }//Constructor
}//Class: LegalEntityCreationException

class CourseDocumentExistsException extends Exception {

    /**
     * The unqiue identifier of the Moodle course document
     * found on the Miless/MyCoRe server.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $documentId;
    
    /**
     * Default constructor of the <c>CreationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $documentId, $code = 0) {
        parent::__construct($message, $code);
        $this->documentId = $documentId;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . " [{$this->code}]: {$this->message}\n";
    }//__toString

}//Class: CourseDocumentExistsException

class HiddenParameterException extends Exception {

    /**
     * The key of the array.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $arrayKey;
    /**
     * The value of the <c>$key</c>.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $arrayValue;
    /**
     * Name of the method that has thrown this exception.
     * @access public
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public $method;
    
    /**
     * Default constructor of the <c>CreationException</c> class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct($message, $method, $key, $value, $code = 0) {
        parent::__construct($message, $code);
        $this->method = $method;
        $this->arrayValue = $value;
        $this->arrayKey = $key;
    }//Constructor

    /**
     * Returns a <c>string</c> representation of this exception.
     * @return string A <c>string</c> that contains the error information an
     *                exception instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __toString() {
        return __CLASS__ . " [{$this->code}]: {$this->message}\n";
    }//__toString
    
}//HiddenParameterException
?>
