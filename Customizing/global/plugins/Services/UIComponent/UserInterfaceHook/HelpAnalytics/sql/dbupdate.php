<#1>
<?php

/**
 * @var $ilDB ilDB
 */

	if(!$ilDB->tableExists('ui_uihk_helpanalytics'))
	{
		$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'access_time' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => false
		),
		'access_query' => array(
			'type' => 'text',
			'length' => 200,
			'notnull' => false
		),
		'session_id' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => false
		)

	);

		
		$ilDB->createTable("ui_uihk_helpanalytics", $fields);
		$ilDB->addPrimaryKey("ui_uihk_helpanalytics", array("id"));
		$ilDB->query("ALTER TABLE `ui_uihk_helpanalytics` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT"); 
		
	}
	
?>

