<?php
require_once("classes/baseclass.php");
require_once("classes/contentLoader.php");
require_once("classes/menu.php");
require_once("classes/menuItem.php");
require_once("classes/fileUtility.php");
require_once("classes/report.php");
require_once("classes/adPage.php");
require_once("classes/adError.php");
require_once("classes/dblink.php");
require_once("classes/_db_object.php");
require_once("settings.php");//note: settings determines the site we're on, and then includes that site's settings file.
require_once("definitions.php");
require_once("loginFunctions.php");
require_once("uploadImageFunctions.php");
require_once("uploadFileFunctions.php");
require_once("versionUpgradeTo".str_replace(".", "-", YOURSITE_VERSION).".php");
if(!function_exists("zip_open"))
	require_once "zipFunctions.php";
	
///////////////////////////
//	If a class that doesn't exist is called then
//	1. try to include a file that will have it defined
//	2. create the class (using eval!!!!), and have the class inherit from _db_object	
function __autoload($className){
	$className = strtolower($className);
	$classFilePath = "adHelpers/classes/".$className.".php";
	$customClassFilePath = "../".SITE_FOLDER."/customClasses/".$className.".php";
	if(file_exists($classFilePath)){
		require_once($classFilePath);
	}
	elseif(file_exists($customClassFilePath)){
		require_once($customClassFilePath);
	}
	elseif(strpos($className, "db_")===0){
		eval('
			class '.$className.' extends _db_object{
				
			}
		');
	}
}

session_start();
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR."./../".SITE_FOLDER);






////////////////////////////////////////////////////////////////////////////////////////////////


function getUrlFormat($str){
	$str = strtolower($str);
   
	////////////////////////////
	// Note - order is important
	$str = str_replace(", ", "-", $str);
	$str = str_replace(" ", "-", $str);
	$str = str_replace("/", "-", $str);
	$str = str_replace(".", "-", $str);
	$str = str_replace(",", "-", $str);
	$str = str_replace("&", "and", $str);
	$str = str_replace("'", "", $str);
	$str = urlencode($str);
	return $str;
}

function getDateCombo($name, $id, $selectFromYear, $selectToYear, $selectedDate){
	$options = new stdClass();
	if($selectedDate=="")
		$selectedDate = date("U");
	elseif(is_array($selectedDate))
		$selectedDate = getDateFromCombo($selectedDate);
	

	
	if($selectFromYear>$selectToYear){
		$adError = adError::getInstance();
		$adError->adError("getDateCombo", "selectFromYear is greater than selectToYear (".$selectFromYear."&gt;".$selectToYear.")");
		$adError->printErrorPage();
	}

	$options->days = array();
	for($i=1; $i<=31;$i++){
		$selected = date("d", $selectedDate)==$i ? "selected='selected'" : "";
		$options->days[] = "<option value='".$i."' ".$selected.">".sprintf("%02d", $i)."</option>";
	}
		
	$options->months = array();
	for($i=1; $i<=12;$i++){
		$selected = date("m", $selectedDate)==$i ? "selected='selected'" : "";
		$options->months[] = "<option value='".$i."' ".$selected.">".sprintf("%02d", $i)."</option>";
	}
		
	$options->years = array();
	for($i=$selectFromYear;$i<=$selectToYear;$i++){
		$selected = date("Y", $selectedDate)==$i ? "selected='selected'" : "";
		$options->years[] = "<option value='".$i."' ".$selected.">".$i."</option>";
	}
	
	$html = "<span class='dateSelect' id='".$id."'>\n";
		$html .= "<select name='".$name."[day]' id='".$name."_day' class='day'>\n";
			$html .= implode("\n", $options->days);
		$html .= "</select>";
		
		$html .= " / ";
		
		$html .= "<select name='".$name."[month]' id='".$name."_month' class='month'>\n";
			$html .= implode("\n", $options->months);
		$html .= "</select>";
		
		$html .= " / ";
		
		$html .= "<select name='".$name."[year]' id='".$name."_year' class='year'>\n";
			$html .= implode("\n", $options->years);
		$html .= "</select>";
		$html .= "<a href='#' class='date-pick' id='".$id."_link' title='select date'>se</a>";
	$html .= "</span>";
	
	
	$datePickerConfig = "\n\n$(function(){\n";
	$datePickerConfig .= "$('#".$id."_link').dpSetSelected('".date("d/m/Y", (int)$selectedDate)."');\n";
	$datePickerConfig .= "});\n";
	
	$p = adPage::getInstance();
	$p->addJavascript("adJavascripts/datePicker/date.js");
	$p->addJavascript("adJavascripts/datePicker/jquery.datePicker.js");
	$p->addJavascript("adJavascripts/datePicker/initialise.js");
	$p->addCss("adCss/datePicker/datePicker.css");
	$p->setInternalJavascript($p->getInternalJavascript().$datePickerConfig);
	
	
	return $html;
}

function getDateFromCombo($array){
	return strtotime($array['year']."-".$array['month']."-".$array['day']);
}

function validateFileUpload($fileArr){
	//////////////////////////////////
	// returns error string or TRUE, so do a validateFileUpload($_FILES['file'])===TRUE check for success
	$error = "";
	
	if($fileArr['error']>0){
		switch($fileArr['error']){
			case 1:	
				$error = "error - file too large";//server limit
				echo "too big server";
				break;
			case 2: 
				$error = "error - file too large";//MAX_FILE_SIZE set in form
				echo "too big form";
				break;
			case 3:	
				$error = "error - file upload failed";//partial upload
				break;
			case 4:	
				$error = "error - file upload failed";//no uploaded file
				break;
		}
	}	
	elseif($fileArr['size']==0){
		$error = "error - empty or blank file";
	}
	elseif(!is_uploaded_file($fileArr['tmp_name'])){
		$error = "error - file upload failed";
	}
	
	return $error=="" ? TRUE : $error;
}

function br2nl($string){
	$string = preg_replace('/\<br\s*\/?\>/i', "", $string);
	return $string;
}

function deleteDirectory($dirname) { 
	if (is_dir($dirname)) 
		$dir_handle = opendir($dirname); 
	if (!$dir_handle) 
		return false; 
	while($file = readdir($dir_handle)) { 
		if ($file != "." && $file != "..") { 
			if (!is_dir($dirname."/".$file)) 
				unlink($dirname."/".$file); 
		else 
			delete_directory($dirname.'/'.$file); 
		} 
	} 
	closedir($dir_handle); 
	rmdir($dirname); 
	return true; 
} 


function printArray($arr, $echo = TRUE){
	/////////////////////////////////////
	// useful function to print arrays in readable format
	if($echo){
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
	else{
		p("<pre>");
		p(print_r($arr, TRUE));
		p("</pre>");
	}	
}


/////////////////////////////////////
// useful function to add content to the adPage class
function p($v){
	$adPage = adPage::getInstance();
	$adPage->addContent($v);
}

/////////////////////////////////////
// only used when viewing front end (admin has its own error system)
function printFrontEndError($error){
	$page = <<<HTML
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>Internal error</title></head><body><div style='font-type:verdana;text-align:center;'><p>Sorry, an error has occured.</p><p>{$error}</p></div></body></html>
HTML;
	exit($page);
}

function getFileType($path){
	if (function_exists('finfo_file')){
		$finfo = finfo_open(FILEINFO_MIME);#, 'C:\wamp\bin\php\php5.2.9-2\extras\magic');

		$fileType = finfo_file($finfo, realpath($path));
		finfo_close($finfo);
	}
	elseif(function_exists('mime_content_type')){
		$fileType = mime_content_type(realpath($path));
		}
	elseif(strstr($_SERVER['HTTP_USER_AGENT'], "Windows")){
		$fileType = returnMIMEType($path);
		if($fileType==-1)
			exit("error - neither finfo_file or mime_content_type exists. On Windows there is no alternative");
	}
	elseif(strstr($_SERVER['HTTP_USER_AGENT'], "Macintosh")) # Correct output on macs
		$fileType = trim(exec('file -b --mime '.escapeshellarg($path)));
	else  # Regular unix systems
	   $fileType = trim(exec('file -bi '.escapeshellarg($path)));
   
	#exit("error - neither finfo_file  or mime_content_type exists");

	return $fileType;
}


function returnMIMEType($filename){
	/////////////////////////////////
	//	Stolen from PHP.net http://uk3.php.net/mime_content_type :: lukas v @ 10-Jul-2008 01:57 
	preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

	switch(strtolower($fileSuffix[1])){
		case "js" :
			return "application/x-javascript";

		case "json" :
			return "application/json";

		case "jpg" :
		case "jpeg" :
		case "jpe" :
			return "image/jpg";

		case "png" :
		case "gif" :
		case "bmp" :
		case "tiff" :
			return "image/".strtolower($fileSuffix[1]);

		case "css" :
			return "text/css";

		case "xml" :
			return "application/xml";

		case "doc" :
		case "docx" :
			return "application/msword";

		case "xls" :
		case "xlt" :
		case "xlm" :
		case "xld" :
		case "xla" :
		case "xlc" :
		case "xlw" :
		case "xll" :
			return "application/vnd.ms-excel";

		case "ppt" :
		case "pps" :
			return "application/vnd.ms-powerpoint";

		case "rtf" :
			return "application/rtf";

		case "pdf" :
			return "application/pdf";

		case "html" :
		case "htm" :
		case "php" :
			return "text/html";

		case "txt" :
			return "text/plain";

		case "mpeg" :
		case "mpg" :
		case "mpe" :
			return "video/mpeg";

		case "mp3" :
			return "audio/mpeg3";

		case "wav" :
			return "audio/wav";

		case "aiff" :
		case "aif" :
			return "audio/aiff";

		case "avi" :
			return "video/msvideo";

		case "wmv" :
			return "video/x-ms-wmv";

		case "mov" :
			return "video/quicktime";

		case "zip" :
			return "application/zip";

		case "tar" :
			return "application/x-tar";

		case "swf" :
			return "application/x-shockwave-flash";

		default :
		if(function_exists("mime_content_type"))
		{
			$fileSuffix = mime_content_type($filename);
		}

		return -1;
	}
}
