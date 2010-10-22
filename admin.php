<?php

/////////////////////////////////////////////////////////////////////////
//	LOAD UP ALL THE FILES 
require("adHelpers/generalFunctions.php");
if(file_exists("../".SITE_FOLDER."/helpers/allFunctions.php"))
	require("../".SITE_FOLDER."/helpers/allFunctions.php");//the site's own functions 

	
/////////////////////////////////////////////////////////////////////////
//	check version
if(SITE_YOURSITE_VERSION!=YOURSITE_VERSION){
	printVersionUpgrade();
}

/////////////////////////////////////
// check login
checkLogin();


///////////////////////////////////////////////////////////////
//	Split URL and then include the content
// 	URLs should be in the format site.com/admin/section/page/mode where section=folder, page=php script and mode=function
if(!isset($_REQUEST["path"]) || $_REQUEST["path"]=="")
	$GLOBALS['NAV_PATH']['url'] = "";
else
	$GLOBALS['NAV_PATH']['url'] = $_REQUEST["path"];
	

/////////////////////////////////////
//store requested pages in globals
$nav = array_filter(explode("/", strtolower($GLOBALS['NAV_PATH']['url'])));
if(count($nav)<1){
	$GLOBALS['NAV_PATH']['section'] = "home";
	$GLOBALS['NAV_PATH']['page'] = "index.php";
	$GLOBALS['NAV_PATH']['mode'] = "";
}
else{
	$GLOBALS['NAV_PATH']['section'] = $nav[0];
	$GLOBALS['NAV_PATH']['page'] = isset($nav[1]) ? $nav[1].".php" : "index.php";
	$GLOBALS['NAV_PATH']['mode'] = isset($nav[2]) ? $nav[2] : "";
}


/////////////////////////////////////
// if not logged in then override request path
if(!$GLOBALS['ADMIN_LOGGED_IN']){
	$GLOBALS['NAV_PATH']['section'] = "home";
	$GLOBALS['NAV_PATH']['page'] = "login_admin.php";
}
/////////////////////////////////////
// check page exists before load
$file = "content/".$GLOBALS['NAV_PATH']['section']."/".$GLOBALS['NAV_PATH']['page'];
$customContentFile = "../".SITE_FOLDER."/customAdmin/".$GLOBALS['NAV_PATH']['section']."/".$GLOBALS['NAV_PATH']['page'];
if(file_exists($file))
	require($file);
elseif(file_exists($customContentFile)){
	require($customContentFile);
}
else{
	$e = adError::getInstance();
	$e->addUserError("Page could not be found:<br />".$GLOBALS['NAV_PATH']['url']."<br/>path 1:".$file."<br/>path 2:".$customContentFile);
}
	


/////////////////////////////////////
// if there are errors then show error page
$e = adError::getInstance();
if($e->areErrors()){
	$e->printErrorPage();
}


?>