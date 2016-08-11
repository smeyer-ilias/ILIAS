<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_DocumentService.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_FileService.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_PermissionService.php");

/**
* Application class for MUVIN repository object.
*
* @author Jan Rocho <jan@rocho.eu>
*
* $Id$
*/
class ilObjMuvin extends ilObjectPlugin
{
	
	/**
     *  Document Service
     *
     * @var String
     */
	
	private $documentService;
	private $fileService;
	private $permissionService;
	
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xmvn");
	}
	
	/**
	* Create object
	*/
	function doCreate()
	{
		global $ilDB, $ilUser;
				


		$ilDB->manipulate("INSERT INTO rep_robj_xmvn_data ".
		"(id, is_online, muvin_id, muvin_keywords, muvin_type, muvin_format, muvin_source, muvin_aspect) VALUES (".
		$ilDB->quote($this->getId(), "integer").",".
		$ilDB->quote(0, "integer").",".
		$ilDB->quote(0, "integer").",".
		$ilDB->quote(" ", "text").",".
		$ilDB->quote("d.9","text").",".
		$ilDB->quote("3","text").",".
		$ilDB->quote("FHDO","text").",".
		$ilDB->quote(0, "integer").
					")");

	}
	
	/**
	* Read data from db
	*/
	function doRead()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xmvn_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setOnline($rec["is_online"]);
			$this->setMuvinID($rec["muvin_id"]);
			$this->setKeywords($rec["muvin_keywords"]);
            $this->setMuvinType($rec["muvin_type"]);
            $this->setMuvinSource($rec["muvin_source"]);
            $this->setMuvinFormat($rec["muvin_format"]);
            $this->setMuvinAspect($rec["muvin_aspect"]);
		}
	}
	
	/**
	* Update data
	*/
	function doUpdate()
	{
		global $ilDB;
		
		// find the MUVIN Object ID
		/*
		$set = $ilDB->query("SELECT * FROM rep_robj_xmvn_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$muvinDocID = $rec["muvin_id"];
		}
		*/
		
		$this->documentService = new DocumentService ();
        
        
			$ilDB->manipulate($up = "UPDATE rep_robj_xmvn_data SET ".
				" is_online = ".$ilDB->quote($this->getOnline(), "integer").",".
				" muvin_id = ".$ilDB->quote($this->getMuvinID(), "integer").",".
				" muvin_keywords = ".$ilDB->quote($this->getKeywords(), "text").",".
				" muvin_type = ".$ilDB->quote($this->getMuvinType(), "text").",".
				" muvin_format = ".$ilDB->quote($this->getMuvinFormat(), "text").",".
				" muvin_aspect = ".$ilDB->quote($this->getMuvinAspect(), "integer").
				" WHERE id = ".$ilDB->quote($this->getId(), "integer")
				);
		
		// If MUVIN ID is 0 we have to create the MUVIN object, otherwise we update it
		
		$muvinID = $this->getMuvinID();
		if($muvinID == 0)
		{
			$this->fileService = new FileService();
			$this->permissionService = new PermissionService();
	
			$paramsDocCreation['language']        = 'de';
			$paramsDocCreation['title']           = $this->getTitle();
			$paramsDocCreation['title2']          = '';
			$paramsDocCreation['authorId']        = '320';
			$paramsDocCreation['role']            = 'author';
			$paramsDocCreation['description']     = $this->getDescription();
			$paramsDocCreation['keyword']         = '';
			$paramsDocCreation['erstelltDatum']   = date ('d.m.Y');
			$paramsDocCreation['geaendertDatum']  = 'tt.mm.jjjj';
			$paramsDocCreation['gueltigVonDatum'] = 'tt.mm.jjjj';
			$paramsDocCreation['gueltigBisDatum'] = 'tt.mm.jjjj';
			$paramsDocCreation['typeid']          = 'd.9';
			$paramsDocCreation['formatid']        = '3';
			$paramsDocCreation['originid']        = 'FHDO';
	
			$newDocID = $this->documentService->CreateDocument($paramsDocCreation);
	
			$ilDB->manipulate($up = "UPDATE rep_robj_xmvn_data SET ".
				" muvin_id = ".$ilDB->quote($newDocID, "integer").
				" WHERE id = ".$ilDB->quote($this->getId(), "integer")
				);
		
			// add an empty file derivate
			$newDerivateID = $this->fileService->CreateFileDerivate($newDocID);
	
			// set permissions to prevent the world from seeing content
			$permissions = array(
				'r' => 'fhdoread',
				'w' => 'jrocho'
			);
	
			if(!$this->permissionService->SetPermissions($newDocID,$permissions))
			{
				return false;
			}
		
		}
		else
		{
			$paramsDocCreation['title']           = $this->getTitle();
			$paramsDocCreation['title2']          = '';
			$paramsDocCreation['authorId']        = '320';
			$paramsDocCreation['role']            = 'author';
			$paramsDocCreation['description']     = $this->getDescription();
			$paramsDocCreation['keyword']         = $this->getKeywords();
			$paramsDocCreation['erstelltDatum']   = date ('d.m.Y');
			$paramsDocCreation['geaendertDatum']  = date ('d.m.Y');
			$paramsDocCreation['gueltigVonDatum'] = 'tt.mm.jjjj'; 
			$paramsDocCreation['gueltigBisDatum'] = 'tt.mm.jjjj';
			$paramsDocCreation['typeid']          = $this->getMuvinType();
			$paramsDocCreation['formatid']        = $this->getMuvinFormat();
			$paramsDocCreation['originid']        = $this->getMuvinSource();
			
			
			$newDocID = $this->documentService->UpdateDocument($paramsDocCreation,$muvinID);
		}
		
		
		
		
	}
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB;
		
		$this->documentService = new DocumentService ();
		
		// find the MUVIN Object ID
		$set = $ilDB->query("SELECT * FROM rep_robj_xmvn_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$muvinDocID = $rec["muvin_id"];
		}
				
		if($this->documentService->DeleteDocument($muvinDocID))
		{		
			$ilDB->manipulate("DELETE FROM rep_robj_xmvn_data WHERE ".
				" id = ".$ilDB->quote($this->getId(), "integer")
				);
		}
		else
		{
			return false;
		}
		
		
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		global $ilDB;
		
		$new_obj->setOnline($this->getOnline());
		$new_obj->setMuvinID($this->getMuvinID());
		$new_obj->setKeywords($this->getKeywords());
        $new_obj->setMuvinType($this->getMuvinType());
        $new_obj->setMuvinFormat($this->getMuvinFormat());
        $new_obj->setMuvinSource($this->getMuvinSource());
        $new_obj->setMuvinAspect($this->getMuvinAspect());
		$new_obj->update();
	}
	
//
// Set/Get Methods for our MUVIN properties
//

	/**
	* Set online
	*
	* @param	boolean		online
	*/
	function setOnline($a_val)
	{
		$this->online = $a_val;
	}
	
	/**
	* Get online
	*
	* @return	boolean		online
	*/
	function getOnline()
	{
		return $this->online;
	}
	
	
	
	
	/**
	* Set option one
	*
	* @param	string		option one
	*/
	function setMuvinID($a_val)
	{
		$this->muvin_id = $a_val;
	}
	
	/**
	* Get option one
	*
	* @return	string		option one
	*/
	function getMuvinID()
	{
		return $this->muvin_id;
	}
	
	/**
	* Set option two
	*
	* @param	string		option two
	*/
	function setKeywords($a_val)
	{
		$this->muvin_keywords = $a_val;
	}
	
	/**
	* Get option two
	*
	* @return	string		option two
	*/
	function getKeywords()
	{
		return $this->muvin_keywords;
	}

    	/**
	* Set option two
	*
	* @param	string		option two
	*/
	function setMuvinType($a_val)
	{
		$this->muvin_type = $a_val;
	}
	
	/**
	* Get option two
	*
	* @return	string		option two
	*/
	function getMuvinType()
	{
		return $this->muvin_type;
	}
    
    	/**
	* Set option two
	*
	* @param	string		option two
	*/
	function setMuvinFormat($a_val)
	{
		$this->muvin_format = $a_val;
	}
	
	/**
	* Get option two
	*
	* @return	string		option two
	*/
	function getMuvinFormat()
	{
		return $this->muvin_format;
	}
    
    /**
	* Set option two
	*
	* @param	string		option two
	*/
	function setMuvinSource($a_val)
	{
		$this->muvin_source = $a_val;
	}
	
	/**
	* Get option two
	*
	* @return	string		option two
	*/
	function getMuvinSource()
	{
		return $this->muvin_source;
	}
    
    /**
	* Set Aspect Ratio
	*
	* @param	string		option two
	*/
	function setMuvinAspect($a_val)
	{
		$this->muvin_aspect = $a_val;
	}
	
	/**
	* Get Aspect Ratio
	*
	* @return	string		option two
	*/
	function getMuvinAspect()
	{
		return $this->muvin_aspect;
	}
    
    

}
?>
