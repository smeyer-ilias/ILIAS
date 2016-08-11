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

class UserService extends WebService {

    /**
     * Creates a new instance of the UserService web service client
     * implementation.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function __construct () {
        parent::__construct ('UserService');
    }//Constructor

     /**
     * Gets a download ticket for downloading restricted files. The ticket is
     * attached to he url where the file is stored on the DuEPublico server:
     * <example>
     *     [URL]?miless.ticket=XXX
     * </example>
     * @param string $uname Username
     * @param string $pword Password
     * @return string Ticket that will be attached to the URL in order to download 
     *                restricted files
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */   
    public function GetTicket($uname, $pword) {
        if ($this->CheckPassword($uname, $pword)) {
        $xml = $this->PerformRequest('getTicket', array ('userID' => array ('content' => $uname, 'type' => 'string'), 
                                                         'password' => array ('content' => $pword, 'type' => 'string')));
            return $xml->GetElementText ();
        } else {
            return false;
        }//else
    }//GetTicket
    
    /**
     * Checks if there exists a user account with the passed username.
     * @param string $uname The username which should be checked for existance.
     * @return bool <c>True</c> if the username is allready assigned; otherwise 
     *              <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function ExistsUser ($uname) {
        $xml = $this->PerformRequest ('userExists', array ('userName' => array ('content' => $uname, 'type' => 'string')));
        if ($xml->GetElementText() == 'true') {
            return true;
        } else {
            return false;
        }//else
    }//UserExists
    
    /**
     * Creates the a new user on the DuEPublico server, specified by
     * <c>$uname</c> and <c>$pword</c> and associates them with the
     * passed <c>$authorId</c>.
     * @param string $uanme The username of the newly created user account.
     * @param string $pword The password of the newly created user account.
     * @param int $authorId The unique identifier of the author with which 
     *                      the user account will be associated.
     * @return bool <c>True</c> if the user account was created sucesfully; 
     *              otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CreateUser ($uname, $pword, $authorId) {
        if (!$this->ExistsUser($uname)) {
            $params = array ('userName' => array ('content' => $uname, 'type' => 'string'), 
                                                  'password' => array ('content' => $pword, 'type' => 'string'), 
                                                  'personID' => array ('content' => $authorId, 'type' => 'int'));
            $this->PerformRequest ('createUser', $params, 'no');
            return true;
        } else {
            return false;
        }//else
    }//CreateUser
    
    /**
     * Deletes the DuEPublico user specified by the passed <c>$uanme</c>.
     * @param string $uname A valid DuEPublico username.
     * @return bool <c>True</c> if the username existed and was deleted 
     *              succesfully, otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function DeleteUser ($uname) {
        if ($this->ExistsUser($uname)) {
            $params = array ('userName' => array ('content' => $uname, 'type' => 'string'));
            $this->PerformRequest ('deleteUser', $params, 'no');
            return true;
        } else {
            return false;
        }//else
    }//DeleteUser
    
    /**
     * Returns an <c>array</c> of groups to which the passed username 
     * is assigned to on the DuEPublico Server. Available groups are:
     * <ul>
     *  <li>admins</li>
     *  <li>creators</li>
     *  <li>disshab</li>
     *  <li>osap</li>
     *  <li>submitters</li>
     * </ul>
     * @param string $uname The name of a user, registered at the DuEPublico server.
     * @return array The groups the user is assigned to.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function GetGroups ($uname) {
        if ($this->ExistsUser($uname)) {
            $params = array ('userName' => array ('content' => $uname, 'type' => 'string'));
            $xml = $this->PerformRequest ('getGroups', $params);
            $groups = $xml->GetChild ('getGroupsReturn');
            $ret = array ();
            if ($groups instanceof XMLElement) {
                $ret[] = $groups->GetElementText();
            } else if (is_array ($groups)) {
                foreach ($groups as $group) {
                    if ($group instanceof XMLElement) {
                        $ret[] = $group->GetElementText();  
                    } else {
                        throw new WebServiceReturnException ('UserService', 'getGroups', 'Error while parsing web service response.');
                    }//else
                }//foreach
            }//else
            return $ret;
        } else {
            return false;
        }//else
    }//GetGroups
    
    /**
     * Adds the user, specified by the passed username, to the
     * groups with in the passed <c>$groups array</c>. Possible
     * group values are:
     * <ul>
     *  <li>admins</li>
     *  <li>creators</li>
     *  <li>disshab</li>
     *  <li>osap</li>
     *  <li>submitters</li>
     * </ul>
     * The group memebership does not influence the access rights of
     * a user; even for a user account that is a member of the admins
     * group, read and write access must be granted for this useraccount.
     * @param string $uname A DuEPublico username.
     * @param array $groups An <c>array</c> of <c>string</c>s as specified above.
     * @return bool <c>True</c> if the user was added to the specified groups; 
     *              otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function SetGroups ($uname, $groups) {
        if ($this->ExistsUser($uname)) {
            $params = array ('userName' => array ('content' => $uname, 'type' => 'string'),
                             'groupNames' => array ('content'=> $this->CreateGroupRequest($groups), 'type' => 'ArrayOf_xsd_string'));
            $this->PerformRequest ('setGroups', $params, 'no');
            return true;
        } else {
            return false;
        }//else
    }//SetGroups
    
    /**
     * Creates a Xml <c>string</c> out of the passed group <c>array</c>.
     * Possible values within the <c>array</c> are the following:
     * <ul>
     *  <li>admins</li>
     *  <li>creators</li>
     *  <li>disshab</li>
     *  <li>osap</li>
     *  <li>submitters</li>
     * </ul>
     * @param array $groups An <c>array</c> of <c>string</c>s as specified above.
     * @return string A <c>Xml</c> representation of the groups passed to this method.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    private function CreateGroupRequest ($groups) {
        $request = '';
        if (is_array($groups)) {
            foreach ($groups as $group){
                $request .= '<item xsi:type="xsd:string">'.$group.'</item>';
            }//while
        } else {
            $request .= '<item xsi:type="xsd:string">'.$groups.'</item>';
        }//else
        return $request;   
    }//CreateGroupRequest
    
    /**
     * Uses the function {@link CheckPassword CheckPassword} to validate
     * if <c>$uname</c> and <c>$pword</c> are valid credentials. If they
     * are correct the coresponding legal entity identifier is returned; 
     * otherwise <c>false</c> is returned.
     * @param string $uname The username
     * @param string $pword The password
     * @return string The legal entity id if credetials are correct otherwise 
     *                an empty string is returned
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function UserLogin($uname, $pword) {
        $uname = $this->MakeUnicode ($uname);
        $pword = $this->MakeUnicode ($pword);
        if ($this->CheckPassword ($uname, $pword)) {
            $xml = $this->PerformRequest ('getPersonID', array ('userName' => array ('content' => $uname, 'type' => 'string')));
            return $xml->GetElementText();
        } else {
            return false;
        }//else
    }//UserLogin
     
    /**
     * Checks if <c>$uname</c> and <c>$pword</c> are a valid
     * credential pair.
     * @param string $uname The username
     * @param string $pword The password
     * @return bool <c>True</c> if <c>$uname</c> and <c>$pword</c> are valid, 
     *              otherwise <c>false</c> is returned.
     * @author Marcel Heusinger <marcel.heusinger [at] uni-essen.de>
     */
    public function CheckPassword ($uname, $pword) {
        $xml = $this->PerformRequest('checkPassword', array ('userName' => array ('content' => $uname, 'type' => 'string'), 
                                                             'password' => array ('content' => $pword, 'type' => 'string')));
        
        if ($xml->GetElementText () == 'false') {
            return false;
        } else {
            return true;
        }//else
    }//ChekPassword   

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
    
}//Class: UserService  
?>
