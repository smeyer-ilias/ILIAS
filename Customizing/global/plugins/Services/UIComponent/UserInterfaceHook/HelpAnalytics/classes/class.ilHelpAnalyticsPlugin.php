<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
 
/**
 * Help Analytics
 *
 * @author Jan Rocho <jan.rocho@fh-dortmund.de>
 * @version $Id$
 *
 */
class ilHelpAnalyticsPlugin extends ilUserInterfaceHookPlugin
{
	function getPluginName()
	{
		return "HelpAnalytics";
	}
}

?>
