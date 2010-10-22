<?php
/////////////////////////////////////////////////////////////////////////
//	WORK OUT WHAT THE SITE'S FOLDER IS
define("BASE_HREF", "http://".$_SERVER['HTTP_HOST']."/");
$urlParts = explode(".", $_SERVER['HTTP_HOST']);
if(count($urlParts)==0)
	echo "error:". BASE_HREF." detected as url. Could not determine site folder.";
	
if($urlParts[0]=="www")
	$folder = $urlParts[1];
else
	$folder = $urlParts[0];


//$toBeRemoved = array("http:", "/", "www.", ".yoursite.loc", ".ramski.co.uk", ".katierandon", ".katierandon", ".org.uk", ".com", ".co.uk");
//$folder = str_replace($toBeRemoved, "", BASE_HREF);

define("SITE_FOLDER", $folder);

/////////////////////////////////////////////////////////////////////////
//	LOAD UP SITE SPECIFIC SETTINGS.PHP
if(@file_exists("../../../".SITE_FOLDER."/settings.php")){
	require("../../../".SITE_FOLDER."/settings.php");
} else {
	if(@file_exists("../../".SITE_FOLDER."/settings.php")) {
		require("../../".SITE_FOLDER."/settings.php");
	} else {
		if(@file_exists("../".SITE_FOLDER."/settings.php")) {
			require("../".SITE_FOLDER."/settings.php");
		} else {
			echo "error:". BASE_HREF." detected as url. Site folder determined as '".$folder."'. Folder or site specific settings file not found.";
		}
	}
}


?>