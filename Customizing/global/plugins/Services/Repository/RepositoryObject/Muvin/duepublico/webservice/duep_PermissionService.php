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

require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_WebService.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLElement.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/XmlParser/duep_XMLExceptions.php');
require_once ('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/exceptions/duep_Exceptions.php');

/**
 * Implementation of the DuEPublico PermissionService web service
 * client.
 * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
 */
class PermissionService extends WebService {

    /**
     * Creates a new instance of the DuEPublico PermissionService
     * web service client.
     * @param string $wwwduepublico The base url of the DuEPublico web services.
     * @param string $dirroot Path to the Moodle directory on the harddisk.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct () {
        parent::__construct ('PermissionService');
    }//Constructor

     /**
     * Calls the PermissionService webservice and grants the passed
     * permissions for the document spcified by the passed id. The
     * permissions <c>array</c> must have the following structure:
     * <ul>
     *   <li>key = The right that should be granted</li>
     *   <li>value = The user to which the right should be granted</li>
     * </ul>
     * If you want grant write access to a user account you can't grant
     * a read access at the same time, because that will override the
     * write access. In order to give write access to an user account
     * and restrict the reading access at the same time you must create
     * two user accounts: first an account with writing access that can 
     * read the document as well and secondly an account with reading 
     * access that protects your document by restricting the reading
     * access to this and the first account.
     * 
     * @param int $documentId The id of the document to which access should be granted
     * @param array $perms The permissions which should be granted
     * @return bool Returns <c>true</c> if the permissions were granted successfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function SetPermissions ($documentId, $perms) {  
        $parameters = array ('ID' => array ('content' => $documentId, 'type' => 'int'),  
                             'permissions' => array ('content' => $this->CreatePermissonRequest($perms), 'type' => 'apachesoap:Document'));
        
        return $this->PerformRequest('setPermissions', $parameters, 'no');
    }//SetPermissions

    /**
     * Removes all access restrictions from the document
     * specified by the passed unique identifier.
     * @param int $documentId The unique identifier of the document for which the access restrictions should be removed.
     * @return bool <c>True</c> if the access restrictions were removed sucesfully; otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function RemoveAllPermissions ($documentId) {
        return $this->PerformRequest('removeAllPermissions',  array ('ID' => array ('content' => $documentId, 'type' => 'int')), 'no');
    }//RemoveAllPermissions

    /**
     * Checks if the document, specified by the passed document id, has read
     * and/or write protections. The applied rights are returned in form of an
     * <c>array</c> that has the following structure:
     * <ul>
     *  <il>array['freeRead'] : <c>true</c> if the document can be access freely for reading, otherwise <c>false</c>.</il>
     *  <il>array['freeWrite'] : <c>true</c> if the document can be access freely for writting, otherwise <c>false</c>.</il>
     * </ul>
     * @param int $documentId The document that should be checked
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetPermissions ($documentId) {
        $permissions = array ();
        $permissions['freeRead'] = true;
        $permissions['freeWrite'] = true;
            
        $xml = $this->PerformRequest('getPermissions', array ('ID' => array ('content' => $documentId, 'type' => 'int')));
        try {
            $rights = $wsReturn = $xml->GetChildXpath('/permissions/permission');
                
            if ($rights instanceof XMLElement) {
                $permissions = $this->SetRights($rights, $permissions);
            } else if (is_array($rights)) {
                foreach ($rights as $right) {
                    $permissions = $this->SetRights($right, $permissions);
                }//foreach
            }//else if
        } catch (ChildNotFoundException $e) {}
        
        return $permissions;
    }//GtePermissions
    
    /**
     * Checks if the user has access to the document, specified by the passed
     * document id. 
     * @param int $documentId Unique identifier of the document that should be checked
     * @param string $uname The username which should be checked
     * @param bool $needWrite Function will return <c>true</c> if the user has write access, otherwise <code>false</code> is returned
     * @return bool <c>True</c> if the user has access and <c>false</code> if not
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    function GetUserRights ($documentId, $uname) {
        $rights = array ();
        
        $freeAccess = $this->GetPermissions($documentId);
        $rights['read'] = $freeAccess['freeRead'];
        $rights['write'] = $freeAccess['freeWrite'];
        
        $xml = $this->PerformRequest('getPermissions', array('ID' => array ('content' => $documentId, 'type' => 'int')));
        
        try {
            $permissions = $xml->GetChildXpath ('/permissions/permission');
            
            if ($permissions instanceof XMLElement) {
                try {
                    if ($permissions->GetAttributeValue ('user') == $uname) {
                        $rights = $this->GetRightsFromPermission ($permissions, $rights);
                    }//if
                } catch (AttributeNotFoundException $e) {}
            } else if (is_array($permissions)) {
                foreach ($permissions as $permission) {
                    try {
                        if ($permission->GetAttributeValue ('user') == $uname) {
                            $rights = $this->GetRightsFromPermission ($permission, $rights);
                        }//if
                    } catch (AttributeNotFoundException $e) {}   
                }//foreach
            }//else if
        } catch (ChildNotFoundException $e) {}
        
        return $rights;
    }//GetUserRights

    /**
     * Checks the passed <c>XmlElement</c> element for its 
     * rights and changes the read and changes the values of the
     * read and write keys of the passed right <c>array</c>.
     * @param XmlElement $permission The permission element returned from the Permission web service.
     * @param array $rights An <c>array</c> that contains two keys which represents the access rights of a specific user.
     * @return array The modified <c>$rights array</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function GetRightsFromPermission ($permission, $rights) {
        if ($permission->GetAttributeValue ('right') == 'w') {
            $rights['write'] = true;
            $rights['read'] = true;
        }//if
        if ($permission->GetAttributeValue ('right') == 'r')  {
            $rights['read'] = true;
        }//if
        return $rights;
    }//GetRightsFromPermission
    
    /**
     * Creates a Xml <c>string</c> of the passed <c>array</c> that is used
     * for setting document access rights on the DuEPulico server.
     * The passed <c>array</c> must have the following structure, otherwise
     * it could not processed correctly:
     * <ul>
     *  <li>key: 'w' for writing access or 'r' for reading access.</li>
     *  <li>value: the username for which the rights should be granted.</li>
     * </ul>
     * @param array $parameters The parameters required for setting access rights on the DuEPublico server.
     * @return string An Xml <c>string</c> that is required by the setPermissions method of the PermissionService web service.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreatePermissonRequest ($parameters) {
        $request = '<permissions>';
        while (list ($key, $val) = each ($parameters)) {
            if (is_array($val)) {
                while (list ($k, $v) = each ($val)) {
                    $request .= '<permission right="'.$k.'" type="user" user="'.$this->MakeUnicode ($v).'" />';
                }//while
            } else {
                $request .= '<permission right="'.$key.'" type="user" user="'.$this->MakeUnicode ($val).'" />';
            }//else
        }//while
        $request .= '</permissions>';
        return $request;    
    }//CreatPermissionRequest

    /**
     * Checks the permissions of the passed <c>XmlElement</c>
     * and addes them to the passed permission <c>array</c>.
     * @param XmlElement $rights The <c>XmlElement</c> that should be checked for the granted rights.
     * @param array $permissions The <c>array</c> that stores the documents restrictions.
     * @return array The modified {@link $permission} <c>array</c>.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de> 
     */
    private function SetRights ($rights, $permissions) {
        try {
            $right = $rights->GetAttributeValue ('right');
            if ($right == 'r') {
                $permissions['freeRead'] = false;
                $permissions['freeWrite'] = false;
            } else if ($right == 'w') {
                $permissions['freeWrite'] = false;
            }//else
        } catch (AttributeNotFoundException $e) {}
        return $permissions;
    }//SetRights
    
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
    
   
}//Class: PermissionService
?>
