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


include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
//require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/datalayer/duep_transformationservice.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_DocumentService.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_FileService.php");
require_once ("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/duepublico/webservice/duep_UserService.php");


/**
* User Interface class for MUVIN repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Jan Rocho <jan@rocho.eu>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjMuvinGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjMuvinGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjMuvinGUI: ilCommonActionDispatcherGUI
*
*/
class ilObjMuvinGUI extends ilObjectPluginGUI
{
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - example: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		
		global $ilTabs, $ilAccess;

        //$this->form = new ilPropertyFormGUI();

        $this->tabs = $ilTabs;
        //$this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        
		
	}
	
	/**
	* Get type.
	*/
	final function getType()
	{
		return "xmvn";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
			case "editDocumentFiles":
			//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		return "showContent";
	}
	
//
// DISPLAY TABS
//
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}
		
		// tab for the "edit files" command
        if ($this->access->checkAccess("write", "", $this->object->getRefId()))
		{
			// $this->txt("documentfiles")
			$this->tabs->addTab("documentfiles", $this->txt("files"), $this->ctrl->getLinkTarget($this, "editDocumentFiles"));
		}

		

		// standard epermission tab
		$this->addPermissionTab();
	}
//
// Edit properties form
//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);
		
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$this->form->addItem($cb);
		
		// keywords
		$ti = new ilTextInputGUI($this->txt("muvin_keywords"), "muvin_keywords");
		$ti->setMaxLength(400);
		$ti->setSize(40);
		$this->form->addItem($ti);
        
        // Muvin Classification
        
        $options = array(
            "d.6" => $this->txt("type_d6"),
            "e.1" => $this->txt("type_e1"),
            "d.9" => $this->txt("type_d9")
        );
        
        $mc = new ilSelectInputGUI($this->txt("muvin_type"), "muvin_type");
        
        $mc->setOptions($options);
        $this->form->addItem($mc);
        $mc->setRequired(true);
        
    	// Muvin Classification
        
        $optionsAspect = array(
            "1" => "480×320 (4:3)",
            "0" => "576×384 (4:3)",
            "2" => "720×480 (4:3)",
            "3" => "512x288 (16:9)",
            "4" => "640x360 (16:9)",
            "5" => "768x432 (16:9)"
        );
        
        $ma = new ilSelectInputGUI($this->txt("muvin_aspect"), "muvin_aspect");
        
        $ma->setOptions($optionsAspect);
        $this->form->addItem($ma);
        $ma->setRequired(true);
        
		$format_input = new ilHiddenInputGUI("muvin_format");
		//$format_input->setValue("muvin_format");
		$this->form->addItem($format_input);
		
		// Source (Fachbereich)
        
        $source_input = new ilHiddenInputGUI("muvin_source");
        //$source_input = new ilTextInputGUI($this->txt("muvin_keywords"), "muvin_source");
		//$source_input->setValue("muvin_source");
		$this->form->addItem($source_input);
        
        
		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["online"] = $this->object->getOnline();
		$values["muvin_keywords"] = $this->object->getKeywords();
        $values["muvin_type"] = $this->object->getMuvinType();
        $values["muvin_format"] = $this->object->getMuvinFormat();
        $values["muvin_source"] = $this->object->getMuvinSource();
        $values["muvin_aspect"] = $this->object->getMuvinAspect();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			
			$this->object->setKeywords($this->form->getInput("muvin_keywords"));
            $this->object->setMuvinType($this->form->getInput("muvin_type"));
            $this->object->setMuvinFormat($this->form->getInput("muvin_format"));
            $this->object->setMuvinSource($this->form->getInput("muvin_source"));
            $this->object->setMuvinAspect($this->form->getInput("muvin_aspect"));
			$this->object->setOnline($this->form->getInput("online"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
//
// Show content
//

	/**
	* Show content
	*/
	function showContent()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilErr;
		
		//echo '<pre>';
		//print_r(get_defined_vars());
		//echo '</pre>';
		
		// Test Setup
		
		//$runTest = TRUE;
		$runTest = FALSE;
		$useDerivate = 1;

		$ilTabs->activateTab("content");
		$muvinID = $this->object->getMuvinID();
		//echo $muvinID;
		if($muvinID == 0)
		{
			$ilErr->raiseError($this->txt('not_saved_no_upload'),$ilErr->MESSAGE);
		}
		
		$file = getCwd().'/Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/setup.xml';
        $xml = simplexml_load_file($file);


        $server = $xml->server.'/services/';
        $client = new SoapClient($server.'DocumentService?wsdl');



		$this->DocumentService = new DocumentService();
		
		$resultSmallest = $this->DocumentService->SmallestDerivateId($muvinID);
		$resultDetails = $this->DocumentService->getDocumentData($muvinID);
		
		
		
		
		if($runTest) {
			
			echo $titleContent[0];
			echo $resultSmallest;
			
		
		
			echo "<pre>";
					print_r($resultDetails);
			echo "</pre>";
		}		
		
		
			
		    if($runTest) {
		    	echo "<pre>";
		    	echo $data;
		    	echo "</pre>";
		    }
			//$resultXML = xml2array($data);
			
			$path = array_reverse($this->getkeypath($resultXML,'document'));
			
			$videoInfo = array();
			
			// get document title
			$titlePath = $resultDetails->GetChildXpath('/document/titles/title');
			$titleTemp = $titlePath->getContent(); // [0]
			$videoInfo['title'] = $titleTemp[0];
			
			// get derivate
			$derivates = $resultDetails->GetChildXpath('/document/derivates/derivate');
			$videoInfo['derivate'] = $derivates->GetAttributeValue ('ID');
			
			// get file
			$files = $resultDetails->GetChildXpath('/document/derivates/derivate/files');
			//echo $file;
			//echo $file->path;
			$videoInfo['num'] = $files->GetAttributeValue ('num');
			
			if($videoInfo['num'] > 0) {
			
				$videoInfo['file'] = $files->GetAttributeValue ('main');
			
				if(strlen($videoInfo['file']) < 2) {
					$videoInfo['file'] = FALSE;
				}
			
				// get file
				if($videoInfo['file']) {
					$file = $resultDetails->GetChildXpath('/document/derivates/derivate/files/file');
					$videoInfo['type'] = $file->GetAttributeValue ('contenttype');
					$fileType = $videoInfo['type'];
				}
			}			
		
			
			// get a ticket so we can access the file
            $muvinTicket = '';
            
            $this->UserService = new UserService();
            $muvinTicket = $this->UserService->getTicket($xml->user,$xml->password);
            if(strlen($muvinTicket) > 2)
            {
                $muvinTicket = '?miless.ticket='.$muvinTicket;
            }
            
			$buildURL = $xml->server."servlets/DerivateServlet/Derivate-".$videoInfo['derivate']."/".$videoInfo['file'].$muvinTicket;			

			if($runTest)
			{
				echo "<pre>";
				print_r($resultXML);
				echo "</pre>";
			}	
			
			//$smallestDerivate = $this->documentService->SmallestDerivateId($muvinDocID);
			
			//$testContent = '<h3>'.$this->txt("watch_video").'</h3>';
			$testContent = '';
            
			
                
			$allowedFiles = array('flv' => array(
													'extension' => 'flv', 
													'provider' => 'rtmp'
												),
								  'mpeg4' => array(
													'extension' => 'mp4',
													'provider' => 'http'
												)
								  ); 
    
                if(array_key_exists($videoInfo['type'],$allowedFiles))
				{
				    // add SWFObject
                    $tpl->addJavaScript("http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js");
                    $tpl->addJavaScript("./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/jwplayer.js");
                    
                    
					$flvFile = $videoInfo['file'];

                    $this->fileService = new FileService();
                    
                    $streamingURL = $this->fileService->GetStreamingURL ($xml->user,$xml->password,$videoInfo['derivate'],$flvFile,$allowedFiles[$fileType]['provider']);
                    
					// display flash player
					
					//$flvFile = $xml->server."servlets/DerivateServlet/Derivate-".$derivateID."/".$flvFile;
					
                    $streamer = substr($streamingURL,0,45);
                    $streamerFile = substr($streamingURL,45);
                    //$streamer = substr($streamingURL,0,33);
                    //$streamerFile = substr($streamingURL,33);
                    
					$testContent .= '<p id="player1">';
    				
					$player = array();
					
					switch($this->object->getMuvinAspect())
					{
						case 1:
								$player['w'] = 480;
								$player['h'] = 320;
								break;
						case 2:
								$player['w'] = 720;
								$player['h'] = 480;
								break;
						case 3:
								$player['w'] = 512;
								$player['h'] = 288;
								break;
						case 4:
								$player['w'] = 640;
								$player['h'] = 360;
								break;
						case 5:
								$player['w'] = 768;
								$player['h'] = 432;
								break;
						default:
								$player['w'] = 576;
								$player['h'] = 384;
							
					}
					
			   require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/Browser.php');
			   $browser = new Browser();
			   
			   
			   if($fileType == "mpeg4" && $browser->getBrowser() != Browser::BROWSER_IE)
			   {
						
					$testContent .= '
					
								<div id="container">
									<p>Loading Video Player</p>
								</div>
								
								<script type="text/javascript">
								jwplayer("container").setup({
									autostart: true,
									id: \'jwplayer\',
									width: '.$player['w'].',
									height: '.$player['h'].',
									modes: [
									  {
										type: \'flash\', 
										//src: \'./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/player.swf\',
										src: \'./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/player.swf\',
										config: {
										  file: \''.$streamerFile.'\',
										  streamer: \''.str_replace(array("http","4887"),array("rtmp","1935"),$streamer).'\',
										  provider: \'rtmp\',
										  bufferlength: 5
										}
									  },
									  {
										type: \'html5\', 
										config: {
										  file: \''.$streamingURL.'\',
										  provider: \'video\'
										}
									  },
									  {
										type: \'download\', 
										config: {
										  file: \''.$streamingURL.'\',
										  provider: \'video\'
										}
									  }
									]
									
								});
							</script>			
					';                   
                    }
                    elseif($fileType == "mpeg4" && $browser->getBrowser() == Browser::BROWSER_IE)
                    {
           									
    									$testContent .= '
                            
                            			<div id="container">
                            				<p>Loading Video Player</p>
                            			</div>
                            			
                            			<script type="text/javascript">
										jwplayer("container").setup({
											autostart: true,
											id: \'jwplayer\',
                                            width: '.$player['w'].',
                                            height: '.$player['h'].',
                                            modes: [
											  {
												type: \'flash\', 
												src: \'./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/player.swf\',
												config: {
												  file: \''.$streamerFile.'\',
												  streamer: \''.str_replace(array("http","4887"),array("rtmp","1935"),$streamer).'\',
												  provider: \'rtmp\',
												  bufferlength: 5
												}
											  },
											  {
												type: \'html5\', 
												config: {
												  file: \''.$streamingURL.'\',
												  provider: \'video\'
												}
											  },
											  {
												type: \'download\', 
												config: {
												  file: \''.$streamingURL.'\',
												  provider: \'video\'
												}
											  }
											]
                                            
										});
									</script>			
                            
                            
                            ';
                    }
                    else
                    {
                    					
    					$testContent .= '
                            
                            			<div id="container">
                            				<p>Loading Video Player</p>
                            			</div>
                            			
                            			<script type="text/javascript">
										jwplayer("container").setup({
											autostart: true,
											id: \'jwplayer\',
                                            width: '.$player['w'].',
                                            height: '.$player['h'].',
                                            modes: [
											  {
												type: \'flash\', 
												//src: \'./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/player.swf\',
												src: \'./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/mediaplayer/jwplayer-5.10/player.swf\',
												config: {
												  file: \''.$streamerFile.'\',
												  streamer: \''.str_replace(array("http","4887"),array("rtmp","1935"),$streamer).'\',
												  provider: \'rtmp\',
												  bufferlength: 5
												}
											  },
											  {
												type: \'html5\', 
												config: {
												  file: \''.$streamingURL.'\',
												  provider: \'video\'
												}
											  },
											  {
												type: \'download\', 
												config: {
												  file: \''.$streamingURL.'\',
												  provider: \'video\'
												}
											  }
											]
                                            
										});
									</script>			
                            ';
                    }

                    
				}
            
			if($videoInfo['file'])
			{
				if(!array_key_exists($fileType,$allowedFiles))
                {
                    $testContent .= "<p>Link: <a href=\"".$buildURL."\" target=\"_blank\">".$videoInfo['file']."</a></p>";
                }
			}
			else
			{
				$testContent .= "<p>".$this->txt("no_video_uploaded")."</p>";
			}

			


			


        	$tpl->setContent($testContent);		
            
            // Add Permalink
       		include_once './Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
            $permalink = new ilPermanentLinkGUI('xmvn', $this->object->getRefId());
		    $this->tpl->setVariable('PRMLINK', $permalink->getHTML());	  
        	
		/* } */
        

		
	}
	
	/**
     *  Edit/Upload Files
     *
     */
    public function editDocumentFiles()
    {
    
    	global $ilErr;
    	
        //include_once("./Services/Table/classes/class.ilTableGUI.php");
        
        $muvinID = $this->object->getMuvinID();

        $this->tabs->activateTab('documentfiles');
		
		
		if($muvinID == 0)
		{
			$ilErr->raiseError($this->txt('not_saved_no_upload'),$ilErr->MESSAGE);
		}
		
		
		$file = getCwd().'/Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/setup.xml';
        $xml = simplexml_load_file($file);


        $server = $xml->server;
        $genContent = "";
        
		
		
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/xml2array.php');
    
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $xml->server."services/DocumentService?method=retrieveDocumentDetailsForOutput&id=".$muvinID);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
			
			$data = curl_exec($ch);
			curl_close($ch); 
		
			//echo $data;
			$resultXML = xml2array($data);
			$path = array_reverse($this->getkeypath($resultXML,'document'));
			
            if(isset($resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']))
            {
                $multiRef = TRUE;
            }
            else
            {
                $multiRef = FALSE;
                $resultXML = $resultXML[$path[0]][$path[1]][$path[2]][$path[3]];
            }
            
			
            if($multiRef)
            {
    			$document = array();
    			$document['title'] = $derivateID = $resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['titles']['title']['value'];
    			$countDer = count($resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']);
    			$iDer = 0;
    			//echo $countDer;

    			$derivateID = $resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']['attr']['ID'];			
			}
            else
            {
                $document = array();
    			$document['title'] = $derivateID = $resultXML['document']['titles']['title']['value'];
    			$countDer = count($resultXML['document']['derivates']['derivate']);
    			$iDer = 0;
    			//echo $countDer;

    			$derivateID = $resultXML['document']['derivates']['derivate']['attr']['ID'];        
            }
            
            #['retrieveDocumentDetailsForOutputResponse']['retrieveDocumentDetailsForOutputReturn']
            
            /* DEBUG 
            echo '<pre>';
            echo $data;
            print_r($resultXML);
            echo '</pre>';
            
            echo "der:".$derivateID;
            */
            
			$this->fileService = new FileService();
			
			$pageURL = 'http';
			 //if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			 $pageURL .= "://";
			 
             /*
             if ($_SERVER["SERVER_PORT"] != "80") {
			  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			 } else {
			  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			 }
             */
             $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			 //echo $pageURL;
			 /*
			 echo $countDer;
			*/
			
			if($multiRef)
            {
                $countFiles = $resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']['files']['attr']['num'];
			}
            else
            {
                $countFiles = $resultXML['document']['derivates']['derivate']['files']['attr']['num'];
            }

			if($countFiles < 1)
			{
			
				$uploadToken = $this->fileService->GetUploadSessionkey($muvinID,$derivateID,$pageURL);
                
				$javaObject = '<object height="130" width="380" codebase="http://muvin.uaruhr.de/plugins/download/j2re-1_4_0_01-windows-i586-i.exe" classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"><param value="'.$uploadToken.'" name="uploadId"><param value="./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/" name="codebase"><param value="org.mycore.frontend.fileupload.MCRUploadApplet.class" name="code"><param value="Plugin" name="cache_option"><param value="upload.jar" name="cache_archive"><param value="true" name="progressbar"><param value="#ff6600" name="progresscolor"><param value="#CAD9E0" name="background-color"><param value="http://muvin.uaruhr.de/servlets/MCRUploadServlet?method=redirecturl&amp;uploadId='.$uploadToken.'" name="url"><param value="http://muvin.uaruhr.de/servlets/" name="ServletsBase"><param value="true" name="selectMultiple"><param value="flv,mp4" name="acceptFileTypes"> '.
							  '<noembed>'.$this->txt("no_java").'</noembed>'.
							  '<comment>'.
							  '<embed type="application/x-java-applet;version=1.3.1" codebase="./Customizing/global/plugins/Services/Repository/RepositoryObject/Muvin/applet" code="org.mycore.fileupload.MCRUploadApplet.class" archive="upload.jar" cache_option="Plugin" cache_archive="upload.jar" width="380" height="130" pluginspage="http://muvin.uaruhr.de/authoring/einrichten.xml" progressbar="true" progresscolor="#FF6600" uploadId="'.$uploadToken.'" background-color="#CAD9E0" url="http://muvin.uaruhr.de/servlets/MCRUploadServlet?method=redirecturl&amp;uploadId='.$uploadToken.'" ServletsBase="http://muvin.uaruhr.de/servlets/" selectMultiple="true" acceptFileTypes="flv,mp4"> '.
							  '<noembed>'.$this->txt("no_java").'</noembed> '.
							  '</embed>'.
							  '</comment> '.
							  '</object>';

				$genContent .= "<h3>".$this->txt("add_new_file")."</h3>";

				$genContent .= $javaObject;
                
                $genContent .= "<p>".$this->txt("info_file_format")."</p>";
				
			}
			/* $countFiles = count($resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']['files']['file']); */
			
			$countFilesI = 0;

			
			if($countFiles > 0 && $countFiles != 1)
			{
				while($countFilesI < $countFiles)
				{
					if($multiRef)
                    {
                        $fileList .= $resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']['files']['file'][$countFilesI]['path']['value'].'<br />';
                    }
                    else
                    {
                        $fileList .= $resultXML['document']['derivates']['derivate']['files']['file'][$countFilesI]['path']['value'].'<br />';                     
                    }
					$countFilesI++;
				}
			}
			else
			{
				if($countFiles == 1)
				{
					if($multiRef)
                    {
                        $fileList .= $resultXML['soapenv:Envelope']['soapenv:Body']['multiRef']['document']['derivates']['derivate']['files']['file']['path']['value'].'<br />';
                    }
                    else
                    {
                        $fileList .= $resultXML['document']['derivates']['derivate']['files']['file']['path']['value'].'<br />';
                    }
				}
				else
				{
					$fileList = $this->txt("no_files");
				}
			}
			
			$genContent .= "<h3>".$this->txt("uploaded_files")."</h3>";
			$genContent .= $fileList;
			
			// add files
			$genContent .= "<p>".$derList."</p>";
        

       	$this->tpl->setContent($genContent);

    }
    
	
	function getkeypath($arr, $lookup)
	{
		if (array_key_exists($lookup, $arr))
		{
			return array($lookup);
		}
		else
		{
			foreach ($arr as $key => $subarr)
			{
				if (is_array($subarr))
				{
					$ret = $this->getkeypath($subarr, $lookup);
	
					if ($ret)
					{
						$ret[] = $key;
						return $ret;
					}
				}
			}
		}
	
		return null;
	}
	

}
?>
