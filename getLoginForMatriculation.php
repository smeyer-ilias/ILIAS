<?php


#TODO pfad zu richtiger installations-ini anpassen

//Ilias Konfiguration lesen und parsen
$config=file("data/ilias-fhdo/client.ini.php") or die ("Datei nicht gefunden");
$keys=array("name","host","user","pass","csesecret");
$settings=array();
foreach($config as $val){
	$val=trim($val);
	foreach($keys as $current){
		$ca=getConfig($val,$current);
		if($ca!="")
			$settings[$current]=$ca;
	}

}

//sicherheitsabfrage
//wenn wirklich jemand die url und die parameter erraten sollte muss er noch dieses passwort erraten..
if($_GET['password']!=$settings["csesecret"]){
	echo "nein";
	exit;
}

//mit den gefundenen Werten zur DB verbinden
mysql_connect($settings["host"],$settings["user"],$settings["pass"]);
mysql_select_db($settings["name"]);

$lookup=$_GET['lookup'];

$res=mysql_query("SELECT DISTINCT usr_id from usr_data where active=1 AND matriculation='".mysql_real_escape_string($lookup)."'");
if(mysql_num_rows($res)>0){
	$z=mysql_fetch_row($res);
	echo $z[0];
}
else{
	echo "-1";
}

//hilfsfunktionen
function getConfig($line,$key){
        if(startsWith($line,$key." =")){
                $ret=substr($line,strpos($line,"\"")+1);
                return substr($ret,0,strlen($ret)-1);
        }
	else
		return "";

}
function startsWith($Haystack, $Needle){
        return strpos($Haystack, $Needle) === 0;
}
?>
