<?php
require_once (dirname(__FILE__).'/duep_XMLExceptions.php');
require_once (dirname(__FILE__).'/duep_Attribute.php');
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
 * @abstract This class implements a convenient structure to handle Xml elements
 *           within php. It has at least one advantage over libries like SimpleXML:
 *           It can handle element attributes which are quite common in the world
 *           of web services. Furthermore, it implements some more advanced features
 *           like getting children by using XPath statements.
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
 * 
 * @todo Add difference between name, namespace, and namespace prefix/Qualified name is namespaceprefix:name
 * @todo Add a docuemnt class that holds the reference to the document and the comments
 * @todo Add attributes to Xpath statements "[@"
 * @todo Rewrite the __toString method
 */
class XMLElement {
	
	/**
	 * The name of this element
	 * @access private
	 */
	private $name;
	/**
	 * Namespace of this element
	 * @access private
	 */
	private $namespaceValue;
	/**
	 * An <c>array</c> with content elements assigned to this instance.
	 * @access private
	 */
	private $content;
	/**
	 * An <c>array</c> with this instance's attributes
	 * @access private
	 */
	private $attributes;
	/**
	 * An <c>array</c> of this instance's children
	 * @access private
	 */
	private $children;
	/**
	 * The number of children this instance has
	 * @access private
	 */
	private $numberOfChildren;
	/**
	 * A reference to the child that is the current child during interation
	 * @access private
	 */
	private $currentChild;
	/**
     * Holds the parent element reference.
     * @access private
	 */
    private $parentElement;
    /**
     * Flag that indicates if this instance is a root elemen <c>true</c> or not <c>false</c>.
     * {@link isRoot To enquire the flag from outside the class scope}
     * {@link setRoot Makes this instance the root element of a hierachy}
     * @access private
     */
    private $isRoot;
    
	/**
	 * Default constructor of this class. Except <c>$name</c> all
	 * parameters are optional parameters.
	 * @param string $name Name of this instance
	 * @param XMLElement $parentElement The parent element of this element.
     * @param string $namespace Namespace of this instance. Deault value is an empty string.
	 * @param string $content Content of this instance. Default value is an empty string.
	 * @param array $attributes An <c>array</c> that contains the attributes, if exists, of this instance. Default value is <c>null</c>.
	 * @param array $children An <c>array</c> that contains the children, if exists, of this instance. Default value is <c>null</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	function __construct ($name, $parentElement = null, $namespace = '', $content = array(), $attributes = array(), $children = null) {
		if (($name == null) or ($name == '')) {
            throw new InvalidArgumentException ('While initializing XMLElement, name must not be null or an empty string.');
        }//if
        
        if ($children == null) {
			$this->numberOfChildren = 0;
		} else {
			$this->numberOfChildren = count ($children);
		}//else
		$this->ParseName ($name, $namespace);
		$this->content    = $content;
		$this->attributes = $attributes;
		$this->children   = $children;
        $this->parentElement = $parentElement;
        $this->isRoot = false;
	}//Constructor
	
	/**
	 * Returns the attribute with the passed name.
	 * @param string $name The name of the attribute
	 * @return mixed The attribute in form on a <c>Attribute</c> element or an empty string if there is no such attribute.
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetAttribute ($name) {
		foreach ($this->attributes as $attribute) {
			if ($attribute->GetName() == $name) {
				return $attribute;
			}//if
		}//foreach
		return '';
	}//GetAttribute
	
    /**
     * Checks if this instance is the root element of
     * a <c>XMLElement</c> hierarchy.
     * @return bool <c>True</c> if this instance is the root element; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function isRoot () {
        return $this->isRoot;
    }//isRoot
    
    /**
     * Sets this instance as root element of the hierarchy.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function setAsRoot () {
        $this->isRoot = true;
    }//setAsRoot
    
	/**
	 * Removes the specified attribute from the attributes' list.
	 * @param string $name Name of the attibute that should be removed from the list
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function RemoveAttribute ($name) {
		$i = 0;
		foreach ($this->attributes as $attribute) {
			if ($attribute->GetName() == $name) {
				break;
			}//if
			$i++;
		}//foreach
        unset($this->attributes[$i]);
	}//RemoveAttribute
	
	/**
	 * Returns the attributes of this instance.
	 * @return array Attributes of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetAttributes () {
		return $this->attributes;
	}//GetAttributes
	
	/**
	 * Returns the value of the attribute with the passed name. If such
	 * an attribute doesn't exists <c>null</c> is returned.
	 * @param string $attributeName Name of the attribute which value should be returned
	 * @return mixed A <c>string</c> with the attribute's value, or <c>null</c> if such an attribute doesn't exists.
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetAttributeValue ($name) {
		foreach ($this->attributes as $attribute) {
            if ($attribute instanceof Attribute) {
                if ($attribute->GetName() == $name) {
    				return $attribute->GetValue();
    			}//if
            } else {
                throw new AttributeNotFoundException ('Could not fine attribute '.$name);
            }//else
		}//foreach
		throw new AttributeNotFoundException ('Could not fine attribute '.$name);
	}//GetAttributeValue
	
	/**
	 * Returns first child that has the passed name.
	 * @param string $name The name of the child that should be returned
	 * @return mixed A <c>XMLElement</c> if there is just one child with that name otherwise an array with all children that have this name or null if there is no child
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetChild ($name) {
		if (isset($this->children)) {
            foreach ($this->children as $child) {
    			if ($child instanceof XMLElement) {
    				if ($child->GetName() == $name) {
    					return $child;
    				}//if
    			} else if (is_array ($child)){
    				if ($child[0]->GetName() == $name) {
    					return $child;
    				}//if
    			}//else if
    		}//foreach
        }//if
		throw new ChildNotFoundException ('There is no child with '.$name.' as name');
	}//GetChild
	
    /**
     * Returns first child that was found under the passed path.
     * @param string $xpath The xpath to find the child to be returned
     * @return mixed A <c>XMLElement</c> if there is just one child with that name otherwise an array with all children that have this name or null if there is no child
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetChildXpath ($xpath) {
        $children = explode ('/', $xpath);
        $count = count ($children);
        if ($count >= 2) { 
            $element = $this;
            for ($i = 1; $i < $count; $i++) {
                if (isset($element->children)) {
                    $element = $element->GetChild ($children[$i]);
                }//if
            }//for
            return $element;
        } else {
            throw new ChildNotFoundException ('Path '.$xpath.' does not lead to a child.');
        }//else
    }//GetChildXpath
    
    /**
     * Returns the last child of this element.
     * @return XMLElement The last child of this element
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetLastChild () {
        if ($this->numberOfChildren == 0) {
            throw new NoChildrenException ('Element '.$this->name.' has no children.');
        } else if ($this->numberOfChildren == 1) {
            return $this->children[0];
        } else {
            return $this->children [$this->numberOfChildren - 1];
        }//else
    }//GetLastChild
    
    /**
     * Returns the parent element of this instance, if such an element
     * was assigned to this element. If there was no element assigned
     * then an <c>NoParentElementException</c> is thrown.
     * @return XMLElement The parent element of this instance.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetParentElement () {
        if ($this->parentElement != null) {
            return $this->parentElement;
        } else {
            throw new NoParentElementException ('There is not parent element assigned to '.$this->name);
        }//else
    }//GetParentElement
	
    /**
	 * Returns the children, which are also instances of this class,
	 * of this instance.
	 * @return array Children of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetChildren () {
		return $this->children;
	}//GetChildren
	
	/**
	 * Removes the child with the passed name from the 
	 * list of children.
	 * @param string $name Name of the child that should be removed
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function RemoveChild ($name) {
		unset($this->children[$name]);
	}//RemoveChild
	
	/**
	 * Removes all children from this element by resetting the
	 * array that contains the children.
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function RemoveChildren () {
		$this->children = array ();
	}//RemoveChildren
	
	/**
	 * Returns the name of this instance.
	 * @return string Name of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetName () {
		return $this->name;
	}//GetName
	
	/**
	 * Returns the namespace of this instance.
	 * @return string Namespace of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetNamespace () {
		return $this->namespaceValue;
	}//GetNamespace
	
	/**
	 * Returns the number of children this element has.
	 * @return int The number of children this element has
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetNumberOfChildren () {
		return $this->numberOfChildren;
	}//GetNumberOfChildren

	/**
	 * Returns the quilified name of this element.
	 * @return string The element's qualified name.
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetQualifiedName () {
		throw new NotImplementedException ('Called  get_qualified_name method on '.$this->name.' but this method as no implementation.');
	}//GetQualifiedName
	
	/**
	 * Sets the namespace property of this instance to the passed value.
	 * @param string $namespace The new namespace property of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function SetNamespace ($namespace) {
		$this->namespaceValue = $namespace;
	}//SetNamespace
	
	/**
	 * Returns the name of this instance.
	 * @return string Name of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function __toString () {
		$string = '['.$this->name.']';
        $children = $this->children;
        if (isset($children)) {
            $string .= '<br/>';
            if ($children instanceof XMLElement) {
                $string .= '&#8195;'.$children->__toString().'<br/>';
            } else if (is_array($children)) {
                foreach ($children as $child) {
                    if ($child instanceof XMLElement) {
                        $string .= '&#8195;'.$child->__toString().'<br/>';
                    } else if (is_array($child)) {
                        foreach ($child as $c) {
                            if ($c instanceof XMLElement) {
                                $string .= '&#8195;&#8195;'.$c->__toString().'<br/>';
                            } else {
                                print_r($c);
                            }//else
                        }//foreach
                    }//else
                }//foreach
            }//else    
        }//if
        $string .= $this->GetElementText();
        $string .= '[/'.$this->name.']<br/>';
        return $string;
	}//__toString
	
	/**
	 * Returns the content elements in form of a <c>string</c>
	 * @return string The concatenated content elements
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetElementText () {
		$return = '';
		foreach ($this->content as $cont) {
			$return .= $cont;
		}//foreach
		return $return;
	}//get_text
	
	/**
	 * Returns the content of this instance.
	 * @return array Content of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function GetContent () {
		return $this->content;
	}//GetContent
		
	/**
	 * Sets the content property of this instance to the passed value.
	 * @param string $content The new content property of this instance
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function SetContent ($content) {
		if (!is_array($content)) {
			$this->content = array ();
			$this->content[] = $content;
		} else {
			$this->content = $content;
		}//else
	}//SetContent
	
	/**
	 * Adds the passed content to the content list
	 * of this instance.
	 * @param string $content The content that should be added to the list
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function AddContent ($content) {
		if ($this->content == null) {
			$this->content = array ();
		}//if
		$this->content[] = $content;
	}//AddContent
	
	/**
	 * Adds an attribute to this instance. The third parameter <c>$namespace</c>
	 * is an optional parameter that is set to a empty string by default.
	 * @param string $name Name of the attribute
	 * @param string $value Value of the attribute
	 * @param string $namespace Namespace of the attribute (optional)
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function AddAttribute ($name, $value, $type = '', $namespace = '') {
		if ($this->attributes == null) {
			$this->attributes = array ();	
		}//if 
		$this->attributes[] = new Attribute ($name, $value, $type, $namespace);
	}//AddAttribute
	
    /**
     * Tries to add the passed <c>XMLElement</c> as parent element
     * of this instance. If this element already has an assigned parenet
     * element, an <c>JustOneParentElementException</c> is thrown.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function AddParentElement ($parentElement) {
        if ($this->parentElement != null) {
            throw new JustOneParentElementException ('Element '.$this->name.' has already an assigned parent element. Could not add'.$parentElement->get_name().' as parent element.');
        } else {
            $this->parentElement = $parentElement;
        }//else
    }//AddParentElement
    
	/**
	 * Adds the passed instance of this class as a child to the
	 * internal list of children. There are three cases how this
	 * could happened:
	 * <ul>
	 * <li>There are at least two children with that name, so this child should be added to the array</li>
	 * <li>There is one child that has that name, which is currently accessable under this name. So both children will be put in a newly created array.</li>
	 * <li>Currently, there is no child with that name, so this one should be accessable under its name</li>
	 * </ul>
	 * @param XMLElement $child The child that should be added to the internal list of children
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	public function AddChild ($child) {
		if ($child instanceof XMLElement) {
			//If this child is the first one, create a new array
			if ($this->children == null) {
				$this->children = array ();	
			}//if 
			
			//There are three cases what could happened next
			if (isset($this->children[$child->GetName()])) {
				if (is_array($this->children[$child->GetName()])) {
					//See case 1
					$this->children[$child->GetName()][] = $child;
				} else {
					//See case 2
					//Get former added child
					$formerChild = $this->children[$child->GetName()];
					//Reset array because there is more than one child that should be added under this name
					$this->children[$child->GetName()] = array ();
					//Add both children
					$this->children[$child->GetName()][] = $child;
					$this->children[$child->GetName()][] = $formerChild;
				}//else
			} else {
				//See case 3
				$this->children[$child->GetName()] = $child;
			}//else
			$this->numberOfChildren++;
		} else {
			throw new ChildNotValidException ('Tried to add a child to element '.$this->name.' but it is not a valid XMLElement.', $child);
		}//else
	}//AddChild
	
    /**
     * Checks if the current instance has any child elements or not.
     * @return bool <c>True</c> if this element has any children; otherwise <c>false</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function hasChildren () {
        if ($this->numberOfChildren > 0) {
            return true;
        } else {
            return false;
        }//else
    }//hasChildren
	
    /**
     * Checks if there is a parent <c>XMLElement</c> assigned to this instance
     * @return bool <c>True</c> if there is a parent element assigned to this instance; otherwise <c>false</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function hasParentElement () {
        if ($this->parentElement != null) {
            return true;
        } else {
            return false;
        }//else
    }//hasParentElement
    
	/**
	 * Sets the name and the namespace properties of this instance. Regarding the
	 * namespace properties two cases have to be attended to: First, the namespace
	 * is passed, so add this one; secondly, if the namespace is not passed, then
	 * check the name property if it contains a colon that separates the name and
	 * the namespace.
	 * @param string $name Name or namespace and name, separated by colon, of this instance
	 * @param string $namespace The namespace of this instance or an empty string
	 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
	 */
	private function ParseName($name, $namespace) {
		if (($namespace != '') or //There is allready a namespace
		    (($pos = strpos($name, ':')) === false)) { //Neither the namespace is set nor the name contains one
			$this->name      = $name;
			$this->namespaceValue = $namespace; //is empty string
		} else {
		    $this->name      = substr($name, $pos + 1, strlen($name));//add +1 to exculde the colon
			$this->namespaceValue = substr($name, 0,        $pos);
		}//else
	}//ParseName
	
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
    
}//class 
?>
