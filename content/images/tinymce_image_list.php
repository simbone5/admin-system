<?php
header('Content-type: text/javascript'); // Make output a real JavaScript file!

/*///////////////////////
// EXAMPLE
var tinyMCEImageList = new Array(
	// Name, URL
	["Logo 1", "logo.jpg"],
	["Logo 2 Overfew", "logo_over.jpg"]
);
*/


$dblink = dblink::getInstance();
$gals = $dblink->getObjects("gallery");

$options = array();
$origSizeDim = new db_dimension();
$origSizeDim->setWidth(0);
$origSizeDim->setHeight(0);
$origSizeDim->setQuality(100);

foreach($gals as $gal){
	$dbImages = $gal->getImages();
	$dims = array_merge(array($origSizeDim), $gal->getDimensions());
	$image = new image($dims);
	foreach($dbImages as $dbImage){
		$image->setDbImage($dbImage);
		foreach($dims as $dimNum => $dim){
			$options[] = '["'.$image->getNameWithDimensions($dimNum).'", "'.htmlentities(stripslashes($image->getSrc($dimNum)), ENT_QUOTES).'"]';
		}
	}
}
?>
var tinyMCEImageList = new Array(
	<?php echo implode(",", $options); ?>
);