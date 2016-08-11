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
 * @author Jan Rocho <jan.rocho@fh-dortmund.de>
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
	
require_once (dirname(__FILE__).'/duep_XMLElement.php');
require_once (dirname(__FILE__).'/duep_XMLExceptions.php');

class XMLParser {
	
	/**
	 * An <c>array</c> that holds references for the namespace's subsitution
	 * @access private
	 */
	private $namespaces;
	/**
	 * An <c>array</c> that holds references to the default namespaces
	 * @access private
	 */
	private $defaultNamespaces;
	/**
     * The currently processed element.
     * @access private
     */
    private $currentElement;
    /**
     * Internal flag that is used to set the first processed element as root element.
     * @access private
     */
    private $isRoot;
    
    
    /**
	 * Default constructor of this class that takes an XML string which should be
	 * parsed as parameter.
	 * @param string $xmlString The XML document, which should be parsed, in form of a <c>string</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function __construct ($xmlString) {
        //Initialization
		$this->isRoot = true;
		$this->namespaces   = array ();
        
		//Remove comments from the xml file to avoid
		//conflicts with special characters that can
		//occur in comments
		$xmlString = $this->RemoveComments($xmlString);
		
		//Create parser and set handling functions
		$parser = xml_parser_create();
		xml_set_element_handler        ($parser, 'startElement', 'endElement');
		xml_set_character_data_handler ($parser, 'cdata');
		//Option for skipping content that consists only of whitespaces
		xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE,   true );
		//If case folding is true then all cases are converted to uppercases
		xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option ($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		//Tells the parser that it should call functions within this class 
		//instead of global functions. This neccesary if the parser is
		//implementet in its own class.
		xml_set_object($parser, $this);
		//Used for processing instructions
		//xml_set_processing_instruction_handler($p, 'piHandler');
		xml_parse($parser, $xmlString);
        //Dispose resources
		xml_parser_free($parser);
	}//duepublicoXMLParser
	
	/**
	 * Transforms the internal XMLElement stack to an <c>array</c> of
	 * <c>XMLElement</c>s.
	 * @return XMLElement The root element of the parsed XML document
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function GetXmlArray () {
        $xml = $this->currentElement;
		//Replace multiRefElement
		$this->ReplaceMultiref($xml);
		return $xml;
	}//get_xml_array
	
	/**
	 * Replaces the href attribute with the content of the multiRefElement
	 * @param XMLElement $xml The root element of the XML document
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 * @author Jan Rocho <jan.rocho [at] fh-dortmund.de>
	 */
	function ReplaceMultiref ($xml) {
		$body = $xml->GetChild('Body');
		$bodyChildren = $body->GetChildren ();
		
        //Get the MultiRef element and all the other children of the body element
		$multiRef = null;
        foreach ($bodyChildren as $bodyChild) {
			if ($bodyChild->GetName() == 'multiRef') {
				$multiRef = $bodyChild;
			} else {
				$childList[] = $bodyChild;
			}//else
		}//foreach
		
		if ($multiRef !== null) {
			//Get attributes and children of multiref element before
			//removing it from the children list
			$multiRefAttributes = $multiRef->GetAttributes ();
			$multiRefChildren   = $multiRef->GetChildren ();
			$multiRefContent    = $multiRef->GetContent ();
			
			//Find the element that contains the href attribute
			foreach ($childList as $elem) {
				$hrefElement = $this->FindHrefAttribute ($elem);
			}//foreach
			
			//Remove the href attribute, add attributes and children of multiref element
			$hrefElement->RemoveAttribute('href');
			$this->AddAttributes ($hrefElement, $multiRefAttributes);
			// only add children if they exist (Jan Rocho)
			if(isset($multiRefChildren))
			{
				$this->AddChildren ($hrefElement, $multiRefChildren);
			}
			$this->AddContent ($hrefElement, $multiRefContent);
			
			//remove all children from the body element and add the child list
			//which contains all children without the multiref element
			$body->RemoveChildren ();
			$this->AddChildren ($body, $childList);
		}//if 
	}//replace_multiref
	
	/**
	 * Adds the array of <c>XMLElement</c>s to the passed 
	 * <c>XMLElement</c> element.
	 * @param XMLElement $element The element that should be enriched with children
	 * @param array $children The children whcih should be added to the <c>XMLElement</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function AddChildren ($element, $children) {
		foreach ($children as $child) {
			$element->AddChild ($child);
		}//foreach
	}//add_children
	
	/**
	 * Adds the <c>string array</c> to the passed <c>XMLElement</c>.
	 * @param XMLElement $element The <c>XMLElement</c> that should be enriched with the content in the passed <c>array</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function AddContent ($element, $content) {
		foreach ($content as $cont) {
			$element->addContent ($cont);
		}//foreach
	}//add_content
		
	/**
	 * Adds the list of attributes to the passed element
	 * @param XMLElement $element The element that should be enriched with the attributes
	 * @param array $attributes The list with the attributes that should be added
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function AddAttributes ($element, $attributes) {
		foreach ($attributes as $attribute) {
			$element->AddAttribute ($attribute->GetName(), $attribute->GetValue(), $attribute->GetAttributeType(), $attribute->GetNamespace());
		}//foreach
	}//add_attributes
	
	/**
	 * This recursive function looks for the element that contains the
	 * href attribute, which is refered by the multiRef element in the
	 * web service response.
	 * @param XMLElement $xmlElement The root element of the xml document
	 * @return XMLElement The <c>XMLElement</c> that contains the href attribute
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function FindHrefAttribute ($xmlElement) {
		$attribute = $xmlElement->GetAttribute('href');
		if ($attribute != '') {
			return $xmlElement;
		} else {
			$children = $xmlElement->GetChildren ();
			foreach ($children as $child) {
				$return = $this->FindHrefAttribute ($child);
				if ($return !== null) {
					return $return;
				}//if 
			}//foreach
		}//else
	}//find_href_attribute
	
	/**
	 * Removes possible namespace identiferes from the element's name and
	 * sets the namespace property of this element to the identified
	 * namespace. If no namespace could be identified then the namespace
	 * will be empty and set to the default namespace if there is one. Therefore
	 * see {@link startElement() startElement function} as well as
	 * {@link endElement() endElement function} of this class.
	 * @param string $elementName Name of the current element in form of <c>string</c>
	 * @return array An <c>array</c> that contains two elements: name and namespace.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function SubstituteNamespaces ($elementName) {
		$ret = array ();
        
        $colonPos = strpos($elementName, ':');
		if ($colonPos === false) {
			//Element name doesn't contain a colon, so it belongs to the current 
			//default namespace which will be added in startElement or endElement
			//function of this class
			$ret['name'] = $elementName;
			$ret['namespace'] = ''; 
		} else {
			$namespaceIdentifier = substr($elementName, 0, $colonPos);
			$ret['name'] = substr($elementName, $colonPos + 1, strlen($elementName));
			$ret['namespace'] = $this->namespaces[$namespaceIdentifier];			
		}//else
        return $ret;
	}//substitute_namespaces

	/**
	 * This function removes all namespace defintitions (xmlns:[localPart]="[URI]") 
	 * from the passed <c>array</c> and adds this definitions to the class 
	 * variable <c>$namespaces</c>, which is used to subsititute namespaces
	 * while parsing the XML document.
	 * @param array $attributes All XML element's attributes
	 * @return array An <c>array</c> that contains all XML element's attributes that were not identified as namespace definitions
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function CheckNamespaceDefinitions ($attributes, $elementName) {
		$newAttributes = array ();
		while (list ($key, $value) = each($attributes)) {
			$namespaceDefinition1 = strtolower(substr($key, 0, 6));
			$namespaceDefinition2 = strtolower(substr($key, 0, 5));
			if ($namespaceDefinition1 == 'xmlns:') {
				$namespace = substr($key, 6, strlen($key));
				$this->namespaces[$namespace] = $value;
			} else if ($namespaceDefinition2 == 'xmlns'){
				//It is not neccessary to cut a substring because here the
				//namespace is a default namespace for every element without
				//a colon in its name
				$this->defaultNamespaces[] = array ('elementName' => $elementName, 'namespace' => $value);
			} else {
				$newAttributes[$key] = $value;
			}//else
		}//while
		return $newAttributes;
	}//check_namespace_definitions

	/**
	 * Is called if the parser finds an opening XML tag. This function
	 * creates an <c>array</c> with all important information of
	 * this XML element and puts it on top of the internal XML element stack.
	 * @param XMLParser $parser The XML parser that parses the XML document
	 * @param string $elementName The name of the XML element
	 * @param array $elementAttributes The attributes of the <c>XMLElement</c>
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function startElement($parser, $elementName, $elementAttributes) {
		if (isset($elementAttributes)) {
			$elementAttributes = $this->CheckNamespaceDefinitions ($elementAttributes, $elementName);
		}//if
		$ret = $this->SubstituteNamespaces ($elementName);
        $namespace = $ret['namespace'];
        $name = $ret['name'];
        if (($namespace == '') and (!empty($this->defaultNamespaces))) {
			$defaultNamespace = array_pop($this->defaultNamespaces);
			$namespace = $defaultNamespace['namespace'];
			$this->defaultNamespaces[] = $defaultNamespace;
		}//if
        
        $tempElement = new XMLElement ($name, $this->currentElement, $namespace, null);
        while (list ($key, $value) = each($elementAttributes)) {
            $tempElement->AddAttribute ($key, $value);
        }//while
        if ($this->currentElement != null) {
            $this->currentElement->AddChild($tempElement);
        } else if ($this->isRoot) {
            $this->isRoot = false;
            $tempElement->setAsRoot();
        } else {
            throw new XmlParserException ('Current element is null but '.$tempElement->GetName().' should not be the root element.');
        }//else
        $this->currentElement = $tempElement;
	}//startElement

	/**
	 * Is called if the parser finds a closing XML tag. This function
	 * creates an <c>array</c> with the element's name and puts
	 * it on top of the internal XML element stack.
	 * @param XMLParser $parser The XML parser that parses the XML document
	 * @param string $elementName The name of the XML element
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function endElement($parser, $elementName) {
		$ret = $this->SubstituteNamespaces ($elementName);
        $namespace = $ret['namespace'];
        $name = $ret['name'];
        
		//Check if the default namespace from the opening element should be removed
		if (!empty($this->defaultNamespaces)) {
			//Get default namespace from top of the default namespace stack
			$defaultNamespace = array_pop($this->defaultNamespaces);
			
			if ($namespace == '') {
				$namespace = $defaultNamespace['namespace'];
			}//if
			
			if ($elementName == $defaultNamespace['elementName']) {
				//Found the closing element so the top of the default stack
				//should be removed, or like now the element is not added again
			} else {
				//Closing element wasn't found so put it on top of the list again
				$this->defaultNamespaces[] = $defaultNamespace;
			}//else
		}//if
		
        if ($this->currentElement->hasParentElement()) {
            $tempElement = $this->currentElement; 
            $this->currentElement = $tempElement->GetParentElement();
        } else {
            if (!$this->currentElement->isRoot()) {
                throw new XmlParception ('There is no parent assigned to '.$this->currentElement->GetName().' but it is not the root element.');
            }//if
        }//else
	}//endElement
	
	/**
	 * Is called if the parser meets the content of a XML element. This
	 * function creates an <c>array</c> and puts it on top of the
	 * internal XML element stack.
	 * @param XMLParser $parser The XML parser that parses the XML document
	 * @param string $elementContent The content of a XML element
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function cdata($parser, $elementContent) {
		$elementContent = trim($elementContent);
		if ($elementContent != '') {
            $this->currentElement->AddContent($elementContent);
		}//if
	}//cdata
	
	/**
	 * Removes comments, surrounded by <c><!--</c> and
	 * <c>--></c>, because this comments can include
	 * special characters that frustrate the parsing. To remove
	 * comments is allowed according to the XML specification see:
	 * {@link http://www.w3.org/TR/REC-xml/#sec-comments XML specification: section comments}.
	 * @param string $string The xml document in form of a string
	 * @return string The xml document withut comments
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	private function RemoveComments ($string) {
		return preg_replace('/<!--[\s](.)*[\s]-->/', '', $string);
	}//RemoveComments
	
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
    
}//Class: XmlParser
?>
