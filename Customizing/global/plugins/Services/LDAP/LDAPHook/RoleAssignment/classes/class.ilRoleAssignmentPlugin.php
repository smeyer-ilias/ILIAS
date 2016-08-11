<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once './Services/LDAP/classes/class.ilLDAPPlugin.php';

/** 
* 
* 
* @author Jan Rocho <jan.rocho@fh-dortmund.de>
* @version $Id$
* 
*
*/
class ilRoleAssignmentPlugin extends ilLDAPPlugin implements ilLDAPRoleAssignmentPlugin
{
	private static $ldap_query = null;
	
	private static $assignments = null;

	
	/**
	 * Get name of plugin.
	 */
	public function getPluginName()
	{
		return 'RoleAssignment';
	}
	
    /**
     * @see ilLDAPRoleAssignmentPlugin::getAdditionalAttributeNames()
     */
	public function getAdditionalAttributeNames()
	{	
		return array('anAdditionalLDAPField');
	}


	/**
	 * check role assignment for a specific plugin id 
	 * (defined in the shibboleth role assignment administration).
	 * 
	 * @param int	$a_plugin_id	Unique plugin id
	 * @param array $a_user_data	Array with user data ($_SERVER)
	 * @return bool whether the condition is fullfilled or not	
	 */
	public function checkRoleAssignment($a_plugin_id,$a_user_data)
	{
		global $ilLog;
	
		$GLOBALS['ilLog']->write(__METHOD__.': Starting plugin assignments');
		//$GLOBALS['ilLog']->write(__METHOD__.': Received user data: '.print_r($a_user_data,true));

		/*
			3 - IuE
			4 - InfiniteIterator
			5 - Maschinenbau
			8 - Soz
			9 - Wirt
			*/
			
			
		/* old
		   100 => array('FB1'),
							101 => array('FB1-013-84','FB1-249-51'),
							102 => array('FB1-242-90'),
							200 => array('FB2'),
							201 => array('FB2-054-84','FB2-917-84'),
							202 => array('FB2-054-90','FB2-917-90'),
							203 => array('FB2-231-84','FB2-447-84', 'FB2-E71-84'),
							204 => array('FB2-450-84'),
							205 => array('FB2-670-84','FB2-670-51'),
							208 => array('FB2-C98-84'),
							209 => array('FB2-C99-90'),
							211 => array('FB2-E71-90'),
							300 => array('FB3'),
							301 => array('FB3-048-84','FB3-473-84','FB3-658-84'),
							302 => array('FB3-212-84'),
							303 => array('FB3-234-84'),
							304 => array('FB3-550-90'),
							305 => array('FB3-588-90'),
							306 => array('FB3-702-84','FB3-781-84'),
							307 => array('FB3-E84-84'),
							308 => array('FB3-G08-84','FB3-G09-84'),
							400 => array('FB4'),
							401 => array('FB4-079-84'),
							402 => array('FB4-079-90'),
							403 => array('FB4-118-84','FB4-721-84'),
							404 => array('FB4-721-90'),
							405 => array('FB4-456-84','FB4-555-84'),
							406 => array('FB4-555-90'),
							407 => array('FB4-573-84'),
							408 => array('FB4-278-84'),
							409 => array('FB4-278-90'),
							410 => array('FB4-408-84'),
							411	=> array('FB4-D30-84'),
							500 => array('FB5'),
							501 => array('FB5-104-84','FB5-970-84'),
							502 => array('FB5-104-90'),
							503 => array('FB5-645-84','FB5-E58-84'),
							505 => array('FB5-B94-90'),
							507 => array('FB5-E78-90'),
							800 => array('FB8'),
							801 => array('FB8-260-51','FB-C40-84'),
							802 => array('FB8-F17-90'),
							900 => array('FB9'),
							901 => array('FB9-285-84','FB9-021-84','FB9-21-84'),
							902 => array('FB9-A20-84'),
							903 => array('FB9-B63-84'),
							904 => array('FB9-C24-90','FB9-F25-90'),
							905 => array('FB9-E83-90'),
							906 => array('FB9-E85-90'),
							907 => array('FB9-C97-90'),
							908 => array('FB9-E86-84'),
							909 => array('FB9-F06-84')
							
							*/	
							
																		
							
		// Mapping Plugin-ID -> Keys aus LDAP
		$mapping = array(
							100 => array('FB1'),
							200 => array('FB2'),
							300 => array('FB3'),
							400 => array('FB4'),
							500 => array('FB5'),
							600 => array('FB6','V6'),
							800 => array('FB8'),
							900 => array('FB9')
						);
		// DEBUG
		//$a_user_data['description'] = array('Mitarbeiter@.FB2.fh-dortmund.de');

		
		/****************************
		 * fake the description field for certain FB
		 */
		  
		if(!is_array($a_user_data['edupersonscopedaffiliation'])) {
			$studiengang = $this->_sortUser($a_user_data['edupersonscopedaffiliation'],false);
			
			// add the fake fields
			$a_user_data['edupersonscopedaffiliation'] = $this->_addDescFields($studiengang);			
		}
		else
		{
			foreach($a_user_data['edupersonscopedaffiliation'] as $key2 => $value2) {
				$studiengang = $this->_sortUser($value2,$a_user_data['edupersonscopedaffiliation']);
			
				// add the fake fields
				$a_user_data['edupersonscopedaffiliation'] = $this->_addDescFields($studiengang);	
			} // end: foreach($a_user_data['description'] as $key => $value) 
		} // end:  !is_array($a_user_data['description'])
			


		/****************************
		 * do the assignment
		 */
		 
		if(!is_array($a_user_data['edupersonscopedaffiliation'])) {
	     	$studiengang = $this->_sortUser($a_user_data['edupersonscopedaffiliation'],false);
	     	
	     	// Wenn kein Studiengang dann nichts zuweisen
			if($studiengang == false)
				return false;
			
			if(in_array($studiengang['fb'],$mapping[$a_plugin_id]))
				return true;
					
		} else {
		
			foreach($a_user_data['edupersonscopedaffiliation'] as $key => $value) {
				$studiengang = $this->_sortUser($value,$a_user_data['edupersonscopedaffiliation']);
				
				// Wenn kein Studiengang dann nichts zuweisen
				if($studiengang == false)
					return false;
				
				if(in_array($studiengang['fb'],$mapping[$a_plugin_id]))
					return true;
					
				$GLOBALS['ilLog']->write(__METHOD__.': New User FB:'.$studiengang['fb']);
				
			}
			
		}
		

		return false;
	}
	
	// Split LDAP description string
	private function _sortUser($description,$orig_description)
	{
		// split the string
		$type = explode('@',$description);
		$attr = explode(".",$type[1]);
		
		// set original description field for orig
		if($orig_description) {
			$orig_description = $orig_description;
		}
		else
		{
			$orig_description = $description;
		}
		
		// build the returned data array
		switch($type[0]) {		
			case 'Mitarbeiter':
			case 'affiliate':
				$data = array('fb' => $attr[1],
							  'status' => 'Mitarbeiter',
							  'orig' => $orig_description);
				break;
			case 'ExMA':			
				$data = array('fb' => 'ExMA',
							  'status' => 'Mitarbeiter',
							  'orig' => $orig_description);
				break;
				
			case 'Student':
				$data = array('qualification' => $attr[0],
						  'focus' => $attr[1],
						  'course' => $attr[2],
						  'fb' => $attr[3],
						  'status' => 'Student',
						  'orig' => $orig_description);
				break;
			case 'ExStud':
				$data = array('qualification' => '00',
						  'focus' => '000',
						  'course' => '000',
						  'fb' => 'ExStud',
						  'status' => 'Student',
						  'orig' => $orig_description);
				break;

		}
							  
		return $data;
	}
	
	// Add fake description fields
	private function _addDescFields($studiengang) {
	
		// connect different FB				
		$mappingMulti = array(
							'FB5' => array('FB3'),
							'FB3' => array('FB5')
							);
		
		if(array_key_exists($studiengang['fb'],$mappingMulti)) {
				
			// first set original description field
			if(!is_array($studiengang['orig'])) {
				$a_user_data['edupersonscopedaffiliation'] = array($studiengang['orig']);
			}
			else
			{
				$a_user_data['edupersonscopedaffiliation'] = array();
				foreach($studiengang['orig'] as $keyDesc => $valueDesc) {
					array_push($a_user_data['edupersonscopedaffiliation'],$valueDesc);
				}
			}


			// add all other FB roles
			$fb = $studiengang['fb'];				
			foreach($mappingMulti[$fb] as $key => $value) {
			
				if($studiengang['status'] == 'Mitarbeiter') 
					$add_description = 'Mitarbeiter@000.'.$value.'.fh-dortmund.de';
			
				if($studiengang['status'] == 'Student')
					$add_description = 'Student@00.000.000.'.$value.'.fh-dortmund.de';
			
				array_push($a_user_data['edupersonscopedaffiliation'], $add_description);	
			} // end: foreach $mappingMulti
			
			$newDesc = $a_user_data['edupersonscopedaffiliation'];	
		} // end: array_key_exists($studiengang['fb'],$mappingMulti
		else
		{
			$newDesc = $studiengang['orig'];
		}
		return $newDesc;
	}
}
?>
