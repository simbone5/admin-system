<?php
///////////////////////////////////////////////////////////////
//	Split URL and then include the content
// 	URLs should be in the format site.com/menu1/menu2/
if(!isset($_REQUEST["path"]) || $_REQUEST["path"]==""){
	$GLOBALS['NAV_PATH']['url'] = "";
	$GLOBALS['NAV_PATH']['parts'] = array();
}
else{
	$GLOBALS['NAV_PATH']['url'] = $_REQUEST["path"];
	$GLOBALS['NAV_PATH']['parts'] = array_filter(explode("/", strtolower($GLOBALS['NAV_PATH']['url'])));
}

/////////////////////////////////////////////////////////////////////////
//	LOAD UP ALL THE FILES 
require("adHelpers/generalFunctions.php");
if(file_exists("../".SITE_FOLDER."/helpers/allFunctions.php"))
	require("../".SITE_FOLDER."/helpers/allFunctions.php");//the site's own functions 


/////////////////////////////////////
// check login
checkLogin();


$contentLoader = contentLoader::getInstance();
$contentLoader->loadContent($GLOBALS['NAV_PATH']['parts']);
$contentLoader->printContent();




//////////////////////////////////////////////////////////////////////////////////////////////

?>