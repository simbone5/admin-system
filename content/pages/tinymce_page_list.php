<?php
header('Content-type: text/javascript'); // Make output a real JavaScript file!

/*///////////////////////
// EXAMPLE
var tinyMCELinkList = new Array(
	// Name, URL
	["Moxiecode", "http://www.moxiecode.com"],
	["Freshmeat", "http://www.freshmeat.com"],
	["Sourceforge", "http://www.sourceforge.com"]
);

*/

///////////////////////////////////
// vars
$dblink = dblink::getInstance();
$options = array();

///////////////////////////////////
// Pages
$pages = $dblink->getObjects("page");
foreach($pages as $page){
	if($page->getHidden())
		continue;
	
	$intName = htmlentities(stripslashes($page->getInternalName()), ENT_QUOTES);
	$extName = htmlentities(stripslashes($page->getExternalName()), ENT_QUOTES);
	$extName = "/".getUrlFormat($extName);
	$options[] = '["'.$intName.'", "'.$extName.'"]';
	
}


///////////////////////////////////
// Files
$files = $dblink->getObjects("file", null, null, null, "filDateUploaded, filName");
foreach($files as $file){
	$date = date(DATE_FORMAT_DATE, $file->getDateUploaded());
	$title = "FILE: (".$date.") ".htmlentities(stripslashes($file->getName()), ENT_QUOTES);
	$address = "/download-file/".$file->getId();
	$options[] = '["'.$title.'", "'.$address.'"]';
}

?>
var tinyMCELinkList = new Array(
	<?php echo implode(",", $options); ?>
);