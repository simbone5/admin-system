<?php
	
	$GLOBALS['ADMIN_LOGGED_IN'] = FALSE;
	
	define("YOURSITE_VERSION", 1.13);
	
	if(!defined("DB_NAME"))
		define("DB_NAME", "");
	if(!defined("DB_PASSWORD"))
		define("DB_PASSWORD", "");
	if(!defined("DB_USER"))
		define("DB_USER", "");
	if(!defined("DB_HOST"))
		define("DB_HOST", "localhost");
	if(!defined("DB_PORT"))
		define("DB_PORT", "3306");
	if(!defined("SITE_ID"))
		define("SITE_ID", "");
	
	if(!defined("DB_TABLE_PREFIX"))
		define("DB_TABLE_PREFIX", "");
	if(!defined("DB_TABLE_SUFFIX"))
		define("DB_TABLE_SUFFIX", "");
	if(!defined("DB_COLUMN_PREFIX"))
		define("DB_COLUMN_PREFIX", "");
	if(!defined("DB_COLUMN_SUFFIX"))
		define("DB_COLUMN_SUFFIX", "");
	
	define("PAGE_XML_PATH", "../".SITE_FOLDER."/xmls/pages.xml");
	define("MENU_XML_PATH", "../".SITE_FOLDER."/xmls/menu.xml");
	define("SITE_IMAGE_PATH", "../".SITE_FOLDER."/savedImages/");
	define("SITE_FILE_PATH", "../".SITE_FOLDER."/savedFiles/");
	define("SITE_TRASH_PATH", "../".SITE_FOLDER."/deletedFiles/");
	
	
	define("ADMIN_PATH", "admin");
	
	define("DIMENSION_ADMIN_HEIGHT", 140);
	define("DIMENSION_ADMIN_WIDTH", 140);
	define("DIMENSION_ADMIN_QUALITY", 100);
	
	define("DATE_FORMAT_DATE", "d/m/Y");
	define("DATE_FORMAT_DATE_TIME", "d/m/Y H:i");
	/*define("SECONDS_IN_HOUR", 3600);
	define("SECONDS_IN_DAY", 86400);
	define("SECONDS_IN_YEAR", 31536000);
	*/
	
?>