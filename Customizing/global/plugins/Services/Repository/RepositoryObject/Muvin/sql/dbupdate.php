<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'muvin_id' => array(
		'type' => 'integer',
		'length' => 8,
		'notnull' => false
	),
	'muvin_keywords' => array(
		'type' => 'text',
		'length' => 400,
		'fixed' => false,
		'notnull' => false
	),
    'muvin_type' => array(
		'type' => 'text',
		'length' => 20,
		'fixed' => false,
		'notnull' => true
	),
    'muvin_format' => array(
		'type' => 'text',
		'length' => 20,
		'fixed' => false,
		'notnull' => true
	),
    'muvin_source' => array(
		'type' => 'text',
		'length' => 40,
		'fixed' => false,
		'notnull' => true
	),
);

$ilDB->createTable("rep_robj_xmvn_data", $fields);
$ilDB->addPrimaryKey("rep_robj_xmvn_data", array("id"));
?>
<#2>
<?php
if(!$ilDB->tableColumnExists("rep_robj_xmvn_data", "muvin_aspect"))
{
    $query = "ALTER TABLE  `rep_robj_xmvn_data` ADD  `muvin_aspect` INT( 9 ) NULL DEFAULT NULL";
    $res = $ilDB->query($query);
}
?>
<#3>
<?php
if($ilDB->tableColumnExists("rep_robj_xmvn_data", "muvin_aspect"))
{
    $query = "UPDATE `rep_robj_xmvn_data` SET muvin_aspect = 0 where `muvin_aspect` IS NULL";
    $res = $ilDB->query($query);
}
?>

