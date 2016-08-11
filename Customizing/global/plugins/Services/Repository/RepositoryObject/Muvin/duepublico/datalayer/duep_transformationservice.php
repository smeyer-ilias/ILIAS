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
 * @abstract This class contains methods that transform data from one format 
 *           to another. One common transformation performed by this class
 *           is the transformation between <c>XMLElement</c> and HTML code.
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

require_once ($CFG->dirroot.'/lib/moodlelib.php');
require_once ($CFG->dirroot.'/lib/duepublico/XmlParser/duep_XMLExceptions.php');

class TransformationService {

    /**
     * The path of the DuEPublico pictures that was added during the installation.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $pixPath;
    /**
     * Type of the result table. See duepconfig.php for more details.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private $resultTableType;
    /**
     * Holds a reference to the singelton of this class after it
     * was initialized.
     * @access private
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private static $service = null;
    
    private function __construct ($resultTableType = 'full') {
        global $DUEP, $CFG;
        if (isset($CFG->pixpath)) {
            $this->pixPath = $CFG->pixpath.'/'.$DUEP->dueppics;
        } else {
            $this->pixPath = $CFG->dirroot.'/pix/'.$DUEP->dueppics;
        }//else
        $this->resultTableType = $resultTableType;
    }//Constructor

    public static function GetTransformationService () {
        if (null == self::$service) {
            self::$service = new TransformationService ();
        }//if
        return self::$service;
    }//GetTransformationService

    /**
     * Creates a <c>string</c> that displays the permissions
     * of the document specified by the passed id.
     * @param bool $freeRead <c>Bool</c> value that indicates if the document, specified by the passed identifier can be read freely (<c>true</c>) or if such an access is restricted (<c>false</c>).
     * @param bool $freeWrite <c>Bool</c> value that indicates if the document, specified by the passed identifier can be modified freely (<c>true</c>) or if such an access is restricted (<c>false</c>).
     * @return string <c>String</c> that displays the permissions, read and write, for the specific document
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CreatePermissionLabel ($freeRead, $freeWrite) {
        
        $free   = '<img alt="Ok" src="'.$this->pixPath.'/ok.png" />';
        $closed = '<img alt="Locked" src="'.$this->pixPath.'/locked.png" />';
        
        //Write permission
        $label = ' ('.get_string ('write', 'duepublicolang').' ';
        if ($freeWrite) {
            $label .= $free.' / ';
        }  else {
            $label .= $closed.' / ';
        }
        
        //Read permission
        $label .= get_string ('read', 'duepublicolang').' ';
        if ($freeRead) {
            $label .= $free.' )';
        } else {
            $label .= $closed.' )';
        }//else
        
        return $label;
    }//CreatePermissionLabel

    /**
     * Checks if the passed derviate is an empty one or not.
     * @param XMLElement $element The derivate child of a document that should be checked
     * @return bool <c>True</c> if the derivate contains at least one link or 
     *              file, otherwise <c>false</c> is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */      
    function isDerivateEmpty ($element) {
        $url   = true;
        $urls  = true;
        $file  = true;
        $files = true;
        
        try {
            $element->GetChild('url');
            $url = false;
        } catch (ChildNotFoundException $e) {}
        
        try {
            $elements = $element->GetChild('urls');  
            if ($elements->GetAttributeValue('num') != 0) {
                $urls  = false;
            }//if
        } catch (ChildNotFoundException $e) {}
          
        try {
            $element->GetChild('file');
            $file = false;
        } catch (ChildNotFoundException $e) {}
        
        try { 
            $elements = $element->GetChild('files');  
            if ($elements->GetAttributeValue('num') != 0) {
                $files  = false;
            }//if
        } catch (ChildNotFoundException $e) {}
        
        return ($url and $urls and $file and $files);
    }//isDerivateEmpty

    /**
     * Modifies the content of the legal entity elements within the
     * passed <c>XmlElement</c> element.
     * @uses {{@link weblib.php Moodle weblib library} The Moodle weblib library and the link_to_popup_window.
     * @param XMLElement $element The <c>XMLElement</c> that will be modified
     * @param string $link A relative path or Url of the script that will be called when clicking the link. The passed Url will be extended by <c>?id=[DuEPublicoAuthorId]</c>, so the script should use this information for providing its content.
     * @param string $popupTitle The title of the popup window that shows up if the link is clicked.
     * @param bool $menubar Indicates if the popup window contains a menubar (<c>true</c>) or not (<c>false</c>).
     *                      Default value is <c>false</c> or 0 respectively.
     * @param int $location The location of the popup window.
     * @param int $height The height of the popup window.
     * @param int $width The width of the popup window.
     * @return XmlElement The modified <c>XmlElement</c> that contains a link as content now.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function ModifyParty ($element, $link, $popupTitle, $menubar = 0, $location = 0, $width = 600, $height = 500) {
       global $CFG;
       require_once ($CFG->dirroot.'/lib/weblib.php');
       $options = "menubar=$menubar,location=$location,scrollbars,resizable,width=$width,height=$height";
       $link    = $link.'?id=';
       //TODO: Use this as popupTitle in the call method $authorDetails = get_string ('authordetails', 'duepublicolang');
       if ($element instanceof XMLElement) {
           $element->SetContent(link_to_popup_window ($link.$element->GetAttributeValue('ID'), 
                                 $popupTitle, $element->GetAttributeValue('name'), '200', '400', 
                                 $popupTitle, $options, true));
       
       } else if (is_array ($element)){
           foreach ($element as $child) {
               $child->SetContent(link_to_popup_window ($link.$child->GetAttributeValue('ID'), 
                                 $popupTitle, $child->GetAttributeValue('name'), '200', '400', 
                                 $popupTitle, $options, true));
           }//foreach
       }//else if
       return $element;
    }//ModifyParty
 
    /**
     * This is a workaround for the subject category.
     * Normally all categories have two children: category and classification, 
     * but the subjects element has just one child, subject, which contains the 
     * two children mentioned before.
     * Purpose of this function is to remove to replace the the subject element
     * by its children to have a common format of all category elements.
     * TODO:Remove this workaround if possible
     */
    function ModifySubject ($element) {
       $subject = $element->GetChildXpath('/subjects/subject');
       if ($subject instanceof XMLElement) {
           $this->ModifySubjectChildren ($subject);
           $element->RemoveChild('subjects');
           $element->AddChild ($subject);
       } else if (is_array($subject)) {
           //TODO: Try to fix this case when more than one subject child occurs, forexample text number 16
           /*
           foreach ($subject as $subsubject) {
               modify_subject_children($subsubject, $subject);
               //$element->remove_child('subjects');
               $element->add_child ($subject);
           }//foreach
           $element->remove_child('subjects');
           $element->add_child ($subject);
           */
       }//else if
    }//ModifySubject
 
    /**
     * This function is a workaround for the subject categorie. This is neccessary
     * because it is the only categorie that has a second element that encloses the
     * categorie elements.
     * @param mixed $subject The subject categorie that should be modified
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function ModifySubjectChildren ($subject) {
        $children = $subject->GetChildren ();
        $subject->RemoveChildren ();
        foreach ($children as $child) {
            if ($child instanceof XMLElement) {
                $subject->AddChild($child);
            } else if (is_array ($child)) {
                foreach ($child as $subchild) {
                    $subject->AddChild($subchild);
                }//foreach
            }//else
        }//foreach
    }//ModifySubjectChildren
 
    /**
     * Creates a <c>string</c> of the passed <c>array</c> that has 
     * the following structure:
     * <code><input type="hidden" name="[key]" value="[value]"/></code>
     * @param array $values Contains key value pairs as specified above
     * @return string A list of HTML hidden parameters in form of a <c>string</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CreateHiddenParams($values) {
        $ret = '';
        while (list ($key, $val) = each ($values)) {
            $ret .= '<input type="hidden" name="'.$key.'" value="'.$val.'"/>';
        }//while
        return $ret;
    }//CreateHiddenParams
    
    /**
     * Creates a popup field out of the passed parameters. The <c>array $popupFields</c>
     * ist composed of <c>array</c>s that have the following structure: 
     * <ul>
     *  <li>$entry['value']: The value of the popup entry</li>
     *  <li>$entry['label']: The label of the popup entry.</li>
     *  <li>$entry['title']: Optional parameter that is used as title of the popup entry</li>
     * </ul>
     * @param array $popupFields The <c>array</c> of <c>array</c>s described above.
     * @param string $popupName The name of the popup.
     * @param string $onChange Instructiosn what should be done after the selection is changed.
     * @param boolean $includeNoChoose If <c>true</c> then a 'Please Choose' field will be added to the popupfield
     * @param string $valueSelectedOption The value of the option that should be marked as selected
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CreatePopupFromArray ($popupFields, $popupName, $onChange, $firstSelected = false, $valueSelectedOption = '', $includeNoChoose = true) {
        $Popup = '<select name="'.$popupName.'" onChange="'.$onChange.'" value="GO">';
        
        if ($includeNoChoose) {
            $Popup .= '<option value="noChoose"';
            if (($valueSelectedOption != '') and (!$firstSelected)) {
                 $popup .= ' selected';
            }//if 
            $Popup .= '>'.$this->MakeUnicode(get_string('pleasechoose', 'duepublicolang')).'</option>';
        }//if
        
        $first = true;
        foreach ($popupFields as $field) {
            $value = $this->MakeUnicode($field['value']);
            $label = $this->MakeUnicode($field['label']);
            $title = $this->MakeUnicode($field['title']);
            if ($first) { 
                //Add the first element and mark it as selected if neccessary
                if (($firstSelected) or ($valueSelectedOption == $value)) {
                    $Popup .= '<option value="'.$value.'" title="'.$title.'" selected>'.$label.'</option>';
                } else {
                    $Popup .= '<option value="'.$value.'" title="'.$title.'">'.$label.'</option>';
                }//else
            } else {
                //Add all the other elements to the drop down list
                if ($value == $valueSelectedOption) {
                    $Popup .= '<option value="'.$value.'" title="'.$title.'" selected>'.$label.'</option>';     
                } else {
                    $Popup .= '<option value="'.$value.'" title="'.$title.'">'.$label.'</option>';  
                }//else
            }//else
            $first = false;
        }//while
        $Popup .= '</select>';
        
        return $Popup;
    }//CreatePopupFromArray
    
    /**
     * Replaces the selected attribute to the entry with the passed key. This
     * function is used because the dropdonwlist maybe stored in the cache or
     * a file and so the selected entry is not the currently selected.
     * @param string $dropDownList The drop down list that should be modified
     * @param string $keySelectedOption The key of the entry that should be marked as selected now
     * @return string The modified drop down list
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function ResetSelectedOption ($dropDownList, $keySelectedOption) {
        //Remove former selected entry
        $dropDownList = preg_replace ('/selected/', 
                                      '', 
                                      $dropDownList);
        //Mark selected entry
        $dropDownList = preg_replace ('/value="'.$keySelectedOption.'"/', 
                                      'value="'.$keySelectedOption.'" selected ', 
                                      $dropDownList);
        return $dropDownList;
    }//ResetSelectedOption
    
    /**
     * Recusive function that parses the sub-categories of a given 
     * <c>XMLElement</c> <c>$category</c>.
     * @param array $popupFields The <c>array</c> that contains the categories.
     * @param XMLElement $category The <c>XMLElement</c> that should be parsed
     * @param string $space The space that is used for shifting sub-categories '&#8195;'
     * @return array The modified <c>$popupFields array</c> passed to this method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function CheckCategories ($category, $popupFields = null, $space = '') {
        $categories = $category->GetChildren();
        if ($categories !== null) {
            foreach ($categories['category'] as $subcategory) {
                $value = (string)$subcategory->GetAttributeValue('id');
                $label = $space.(string)$subcategory->GetAttributeValue('label');
                
                //Get the comment child if it is there and add the content as title
                try {
                    $title = $subcategory->GetChild('comment')->GetAttributeValue('content');
                } catch (ChildNotFoundException $e) {
                    $title = '';
                } catch (AttributeNotFoundException $e) {
                    $title = '';
                }//catch 
                
                $popupFields[] = array ('value' => $value, 'label' => $label, 'title'=> $title);
                
                //Call this method again for the subcategories
                $popupFields = $this->CheckCategories ($subcategory, $popupFields, $space.'&#8195;');
            }//foreach
        }//if
        return $popupFields;
    }//CheckCategories
    
    /**
     * This function replaces different representations (PHP, ASCII, and XML) 
     * of German umlaute with their Unicode representation. 
     * @param string $string XML string in which the umlaute should be replaced
     * @return string XML string where all the umlaute are replaced
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function MakeUnicode ($string) {
        $befor = array   ('Ä' ,     'ä',      'Ö',      'ö',      'Ü',      'ü',      'ß'  );
        $after = array   ('&#xC4;', '&#xE4;', '&#xD6;', '&#xF6;', '&#xDC;', '&#xFC;', '&#xDF;');  
        $string = str_replace ($befor, $after, $string);
        $befor = array   ('Ã„',     'Ã¤',     'Ã–',     'Ã¶',     'Ãœ',     'Ã¼',     'ÃŸ');
        $string = str_replace ($befor, $after, $string);
        $before = array  ('&Auml;', '&auml;', '&Ouml;', '&ouml;', '&Uuml;', '&uuml;', '&szlig;');
        $string = str_replace ($befor, $after, $string);
        $befor = array   ('&#xC4;', '&#xE4;', '&#xD6;', '&#xF6;', '&#xDC;', '&#xFC;', '&#xDF;');  
        return str_replace ($befor, $after, $string);
    }//MakeUnicode
    
    /**
     * Replaces some special characters with their HTML code
     * representation.
     * @param string $string The <c>string</c> in which the special characters
     *                       should be replaced.
     * @return string The passed <c>string</c> without special characters.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ReplaceSpecialChars ($string) {
        $befor  = array ('&',     '\'',     '<',    '>',    '\"');
        $after  = array ('&#38;', '&#39;', '&#60;', '&#62;', '&#34;');
        $string = str_replace ($befor, $after, $string);   
        $before  = array ('&amp;', '&apos;', '&lt;', '&gt;', '&quote;');
        return str_replace ($befor, $after, $string);
    }//ReplaceSpecialChars
    
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
       
    /**
     * Modifies a operator drop down lists by adding a unique name to it.
     * @param string $popup The drop down list that should be modified
     * @param string $name The name that will be added to the drop down list
     * @param string $keySelectedOption The key of the option that should be marked as selected
     * @return string The modified drop down list
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function ModifyPopup ($popup, $name, $keySelectedOption) {
        //Replace the selected entry
        if ($keySelectedOption != '') {
            $popup = $this->ResetSelectedOption($popup, $keySelectedOption);
        }//if
        
        $before = 'name="OPERATOR"';
        $after  = 'name="operator'.$name.'"';
        return str_replace ($before, $after, $popup);
    }//ModifyPopup
    
    /**
     * Creates the date row of the table. This row contains one drop down list
     * with all possible date properties and one input box where the user
     * can enter a date.
     * @param array &$seachFieldArray The array that contains all search field rows of the search field table (in/out)
     * @param array $dateFieldArray An array that contains all the possible date properties of the search query
     * @param string $popup The operator popup for the date search field
     * @param string $label The label of this row that is used for chancing the name of the operator drop down list
     * @param string $fieldValue The value a user entered into the input box before validation
     * @param string $keySelectedOption The option's key which should be marked as selected
     * @param string $keyOperatorSelectedOption The option which should be marked as selected in the operator drop down list
     * @author Marcel Heusinger <marcel DOT heusinger[at]uni-essen DOT de>
     */
    function AddDateFields ($dateFieldArray, $popup, $fieldValue, $keySelectedOption, $keyOperatorSelectedOption) {
        $dateField = $this->CreatePopupFromArray($dateFieldArray, 'date', ON_CHANGE, false, $keySelectedOption);
        
        if ($fieldValue == '') {    
            $fieldValue = 'tt.mm.jjjj';
        }//if
        $dateField .= '</select>&#9;<input size="10" type="text" name="datum" value="'.$fieldValue.'"/>';
                
        return array ('name'     => 'date',
                      'label'    => 'Datum',
                      'type'     => 'date',
                      'value'    => 'date',
                      'sortable' => 'true',
                      'field'    => $dateField,
                      'popup'    => $this->ModifyPopup($popup, 'Datum', $keyOperatorSelectedOption));
    }//AddDateFields
    
}//Class: TransformationService
?>
