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
 * @abstract This is the abstract base class for all web service client 
 *           implemententations that provides some mutual methods.
 * @modified Jan Rocho <jan.rocho@fh-dortmund.de>
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




 abstract class WebService {
 
    /**
     * Miless/MyCoRe web service base uri.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $serviceBase;
    /**
     * Name of the derived web service client implementation.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $webservice;
    /**
     * Moodle root directory.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $dirroot;
    
    /**
     * Default constructor of this abstract base class and its derived
     * classes.
     * @param string $webservice The name of the webservice
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     * @author Jan Rocho <jan.rocho@fh-dortmund.de>
     */
    public function __construct ($webservice) {
        $setupFile = getCwd().'/Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/setup.xml';
		$setupXML = simplexml_load_file($setupFile);
        
        $this->serviceBase = $setupXML->server.'/services/';
        //echo "Servicebase: ".$this->serviceBase;
        $this->dirroot = './Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin';
        $this->webservice = $webservice;
    }//Constructor
 
    /**
     * Opens a socket connection to call a web service and returns
     * its SOAP response.
     * @param string $method The web service's method that should be called.
     * @param array $params The parameters required for calling the web service's method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    protected function CallWS ($method, $params) {
        $header = $this->CreateHeader ($method, $params);
        $urlParts = parse_url ($this->serviceBase);
        
        if (!isset($urlParts['port'])) {
            $urlParts['port'] = 80;
        }//if
        $fp = fsockopen($urlParts['host'], $urlParts['port'], $errno, $errstr, 120);
        
        if(!$fp) {
            throw new WebServiceReturnException ('WebService', 'ClassWS', "Web service $this->serviceBase not reachable: $errstr ($errno)");
        } else {
            fputs($fp, $header, strlen($header));
            $result = '';
            $i = 0;
            $end = false;
            while((!feof($fp)) and (!$end)) { 
                $result .= fgets($fp); 
                if (($test = stripos($result, '</soapenv:Envelope>')) !== false) {
                    $end = true;
                }//if
            }//while
            
            fclose($fp);
            $strPos1 = strpos ($result, '<soapenv:Envelope');
            $strPos2 = strpos ($result, '</soapenv:Envelope>');
            $xml = substr ($result, $strPos1, $strPos2 - $strPos1 + 19);
            $startPosFault = strpos ($xml, '<faultstring>');
            if ($startPosFault !== false) {
                $endPosFault = strpos ($xml, '</faultstring>');
                throw new WebServiceReturnException ('WebService', 'ClassWS', 
                    substr ($xml, $startPosFault + 13, $endPosFault - $startPosFault - 13).'<br/>'
                    .$header.'<br/>'
                    .$this->serviceBase);
            } else {
                return $xml;
            }//else
        }//else
    }//CallWS
 
     /**
     * This function opens the passed website and returns the read conent
     * in the specified form. Possible forms are:
     * <ul>
     *  <li>string: The function returns a <c>string</c></li>
     *  <li>xml: The function returns a <c>XMLElement</c> Element</li>
     * </ul>
     * @param string $link The website that should be read
     * @param bool $showdoc Prints the conent before leaving the function for debugging purpose
     * @param string $returntype Controlls the form of the returned content
     * @return XMLElement The root element of the read XML file
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     *
     * @modified Jan Rocho <jan.rocho@fh-dortmund.de>
     */
    protected function PerformRequest ($method, $params, $return = 'xml') {
        $doc = $this->CallWS($method, $params);
        
        if (strstr($doc, 'soapenv:Fault') !== false) {
            throw new WebServiceReturnException ('WebService', 'PerformRequest', 'SOAP Fault: '.$doc);
        }//if
        
        if ($return == 'xml') {
            require_once($this->dirroot.'/duepublico/XmlParser/duep_XMLParser.php');
            $parser = new XMLParser ($doc);
            $response = $parser->GetXmlArray();
            $response = $response->GetChildXpath("/Body/{$method}Response/{$method}Return");
            return $response;
        } else if ($return == 'string') {
            return $doc;
        } else if ($return == 'no') {
            return true;
        }//else
    }//PerformRequest
 
    /**
     * Returns the HTML header required for calling a DuEPublico web service.
     * @param string $method The web service's method that should be called.
     * @param array $params The parameter required for calling the web service's method.
     * @return string The created header.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreateHeader ($method, $params) {
        $requestPart1 = '<?xml version="1.0" encoding="UTF-8"?>' .
                        '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'.
                        '<soapenv:Body><'.$method.'>';
        $requestPart2 = '</'.$method.'></soapenv:Body></soapenv:Envelope>';
        
        
        $postdata = '';
        if (!empty($params)) {
            foreach ($params as $k=>$v) {
                $postdata[$k] ='<'.$k.' xsi:type="'.$v['type'].'">'.$v['content'].'</'.$k.'>';
            }//foreach
            $postdata = implode('', $postdata);
        }//if
        
        //Create post request with header and the content after an empty line
        $eol = "\r\n";
        $header =  'POST '.$this->serviceBase.$this->webservice.' HTTP/1.0'.$eol.
                   'Content-Type: text/xml;charset=UTF-8'.$eol.
                   'Content-Length: '.strlen($requestPart1.$postdata.$requestPart2).$eol.
                   'SOAPAction: '.$eol.
                   $eol.
                   $requestPart1.$postdata.$requestPart2;
        return $header;
    }//CreateHeader
 
    /**
     * This function replaces different representations (PHP, ASCII, and XML) 
     * of German umlaute with their Unicode representation. 
     * @param string $string XML string in which the umlaute should be replaced
     * @return string XML string where all the umlaute are replaced
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    protected function MakeUnicode ($string) {
        $before  = array ('&',     '\'',     '<',    '>',    '\"');
        $after  = array ('&#38;', '&#39;', '&#60;', '&#62;', '&#34;');
        $string = str_replace ($before, $after, $string);   
        $before  = array ('&amp;', '&apos;', '&lt;', '&gt;', '&quote;');
        $string = str_replace ($before, $after, $string);
        
        $before = array   ('Ä' ,     'ä',      'Ö',      'ö',      'Ü',      'ü',      'ß'  );
        $after = array   ('&#xC4;', '&#xE4;', '&#xD6;', '&#xF6;', '&#xDC;', '&#xFC;', '&#xDF;');  
        $string = str_replace ($before, $after, $string);
        $before = array   ('Ã„',     'Ã¤',     'Ã–',     'Ã¶',     'Ãœ',     'Ã¼',     'ÃŸ');
        $string = str_replace ($before, $after, $string);
        $before = array  ('&Auml;', '&auml;', '&Ouml;', '&ouml;', '&Uuml;', '&uuml;', '&szlig;');
        $string = str_replace ($before, $after, $string);
        $before = array   ('&#xC4;', '&#xE4;', '&#xD6;', '&#xF6;', '&#xDC;', '&#xFC;', '&#xDF;');  
        return str_replace ($before, $after, $string);
    }//MakeUnicode
    
    protected function Amps ($string) {
        $before  = array ('&');
        $after  = array ('&amp;');
        return str_replace ($before, $after, $string);
    }//ReplaceSpecialChars
    
    /**
     * Checks if the passed <c>$value</c> still is an
     * initialized one or not.
     * @param string $value The value that should be checked
     * @return bool <c>True</c> if the <c>string</c> is neither 
     *              empty nor a default value, otherwise <c>false</c> is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    protected function isValueInitialized ($value) {
        if (($value == '')           or
            ($value == 'noChoose')   or
            ($value == 'tt.mm.jjjj')    ){
            return false;
        } else {
            return true;
        }//else
    }//isValueInitialized
 
    /**
     * Creates a uri for uploading a file to the DuEPublico server.
     * The DuEPublico server will download the file from 
     * <c>$moodlePublish/$filename</c>. 
     * So <c>$moodlePublish</c> must be a public server directory 
     * accessable from the internet.
     * <example>
     *  $moodlePublish: www.myMoodleServer.org/public
     *  $filename: myFile.ext
     *  So the DuEPublico server will try to download the file from:
     *  http://www.myMoodleServer.org/public/myFile.ext
     * </example>
     * @param string $moodlePublish A Moodle server directory accessable from the internet.
     * @param string $filename The name of the file that should be 
     *                          transfered to the DuEPublico server.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateUploadUri ($moodlePublish, $filename) {
        if (!$this->EndsWith($moodlePublish, '/')) {
            $moodlePublish = $moodlePublish.'/';
        }//if
        return $this->CheckUrl($moodlePublish.$filename);
    }//CreateUploadUri
 
    /**
     * Checks if the passed <c>$url</c> starts with "http://"; if
     * not it will be added.
     * @param string $url The <c>string</c> to be checked.
     * @return string The modified <c>string</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckUrl ($url) {
       if (substr($url, 0, 7) != 'http://') {   
           $url = 'http://'.$url;
       }//if
       return $url;
    }//CheckUrl
     
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
     * Checks if the passed <c>$string</c> ends with
     * one of the <c>string<c>s in the passed 
     * <c>array</c> <c>$endings</c>.
     * @param string $string The <c>string</c> to be check.
     * @param string $endings An <c>array</c> of <c>string</c>s used for the comparison.
     * @return bool <c>True</c> if <c>$string</c> ends with one of 
     *              the <c>string</c>s in <c>$endings</c>; oterhwise 
     *              <c>false</c> is returned.
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
     * Returns the name of the derived web service client implementation.
     * @return string The name of the derived class.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetName () {
        return $this->webservice;
    }//GetName
    
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
    
 }//Class: WebService
  
?>
