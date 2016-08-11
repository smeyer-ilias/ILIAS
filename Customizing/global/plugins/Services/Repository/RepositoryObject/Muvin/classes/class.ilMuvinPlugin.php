<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* MUVIN repository object plugin
*
* @author Jan Rocho <jan@rocho.eu>
* @version $Id$
*
*/
class ilMuvinPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "Muvin";
	}


protected function uninstallCustom() {
                global $ilDB;
				// removes plugin tables if they exist                
                if($ilDB->tableExists('rep_robj_xmvn_data'))
                	$ilDB->dropTable('rep_robj_xmvn_data');
                	
    }

}

?>
