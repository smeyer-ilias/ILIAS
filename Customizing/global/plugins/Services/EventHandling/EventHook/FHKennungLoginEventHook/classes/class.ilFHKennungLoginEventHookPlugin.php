<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/classes/class.ilEventHookPlugin.php';
require_once 'Services/Component/classes/class.ilPluginAdmin.php';

/**
 * @author Jan Rocho <jan.rocho@fh-dortmund.de>
 */
class ilFHKennungLoginEventHookPlugin extends ilEventHookPlugin
{

        /**
         * @return string
         */
        final public function getPluginName()
        {
                return "FHKennungLoginEventHook";
        }

        public function handleEvent($a_component, $a_event, $a_params)
        {
        		
                switch($a_component)
                {
                        case 'Services/Authentication':
                                switch($a_event)
                                {
                                        case 'afterLogin':

                                                $this->fetchFHKennung($a_params);
                                                break;
                                }
                                break;
                }

                return true;
        }
	

	/**
	 * @param array $a_params
	 * @return bool
	 */
	public function fetchFHKennung(array $a_params)
	{
		global $ilLog, $ilDB, $ilIliasIniFile;
                
		$usr_id = ilObjUser::_loginExists($a_params['username']);

		if($usr_id) {
			$ilUser = new ilObjUser($usr_id);
			
		
			// check if login is valid and write FHKennung to ext_account
			$db =& $ilDB;
			$q = "SELECT usr_id, ext_account, matriculation FROM usr_data WHERE usr_id = ".$usr_id;
																																																										 
			$r = $db->query($q);
	   
			if ($r->numRows() > 0)
			{
				
				$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
				
				if(strlen($row->matriculation) > 3 && $row->matriculation != "000" && 
					strlen($row->ext_account) < 3)
				{
				
					
					require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/FHKennungLoginEventHook/xml2array.php');

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://ods.fh-dortmund.de/ods?Sicht=ilias&auth3="
						.$ilIliasIniFile->readVariable("fhdo","ods_auth3") .
						"&mtknr=" .$row->matriculation);
					curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

					// The usual - get the data and close the session
					$xmlData = curl_exec($ch);
					curl_close($ch);

					$result = xml2array($xmlData);

					if($result["Dias2Ilias"]["student"]["attr"]["errornum"] == 0) {
						$fhkennung = $result["Dias2Ilias"]["student"]["attr"]["fhkennung"];
				   		
				   
						$q = "UPDATE usr_data set ext_account = ".
							$ilDB->quote($fhkennung)." WHERE usr_id = ".$usr_id;
																																																				 
						$r = $db->query($q);
				
						$ilLog->write("FHKennung: added ".$fhkennung." for usr_id ".$usr_id);
					   
						
					}
				}
			}
		}
	}
}
