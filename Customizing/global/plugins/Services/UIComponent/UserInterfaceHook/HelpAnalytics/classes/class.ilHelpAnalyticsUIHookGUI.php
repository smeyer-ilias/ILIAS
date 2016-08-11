<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * UIHook HelpAnalytics
 *
 * @author Jan Rocho <jan.rocho@fh-dortmund.de>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilHelpAnalyticsUIHookGUI extends ilUIHookPluginGUI
{

	/**
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		// do not show the search part of the main menu
		// $a_par["main_menu_gui"]
		if ($a_comp == "Services/MainMenu" && $a_part == "main_menu_search")
		{
			// $a_par["main_menu_gui"] is ilMainMenu object
			// $a_par["main_menu_search_gui"] is ilMainMenuSearchGUI object
			
			//return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => "");
		}
		
		// add something to the main menu entries
		if ($a_comp == "Services/MainMenu" && $a_part == "main_menu_list_entries")
		{
			// $a_par["main_menu_gui"] is ilMainMenu object
			
			//return array("mode" => ilUIHookPluginGUI::APPEND, "html" =>
			//	" <a class='MMInactive' href='http://www.ilias.de' target='_blank'>ILIAS Home Page</a>");
		}
		
		// add something to locator
		if ($a_comp == "Services/Locator" && $a_part == "main_locator")
		{
			// $a_par["locator_gui"] is ilLocatorGUI object
			
			//return array("mode" => ilUIHookPluginGUI::APPEND,
			//	"html" => " » My Locator");
		}

		// add things to the personal desktop overview
		if ($a_comp == "Services/PersonalDesktop" && $a_part == "left_column")
		{
			// $a_par["personal_desktop_gui"] is ilPersonalDesktopGUI object
			
			//return array("mode" => ilUIHookPluginGUI::PREPEND,
			//	"html" => "Prepend to left");
		}

		// add things to the personal desktop overview
		if ($a_comp == "Services/PersonalDesktop" && $a_part == "right_column")
		{
			// $a_par["personal_desktop_gui"] is ilPersonalDesktopGUI object
			
			//return array("mode" => ilUIHookPluginGUI::APPEND,
			//	"html" => "Append to right");
		}

		// add things to the personal desktop overview
		if ($a_comp == "Services/PersonalDesktop" && $a_part == "center_column")
		{
			// $a_par["personal_desktop_gui"] is ilPersonalDesktopGUI object
			
			//return array("mode" => ilUIHookPluginGUI::PREPEND,
			//	"html" => "Prepend to center");
		}

		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}
	
	/**
	 * Modify GUI objects, before they generate ouput
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
		// currently only implemented for $ilTabsGUI
		
		// tabs hook
		// note that you currently do not get information in $a_comp
		// here. So you need to use general GET/POST information
		// like $_GET["baseClass"], $ilCtrl->getCmdClass/getCmd
		// to determine the context.
		if ($a_part == "tabs")
		{
			// $a_par["tabs"] is ilTabsGUI object
			
			// add a tab (always)
			//$a_par["tabs"]->addTab("test", "test", "test");
		}
	}

}
?>
