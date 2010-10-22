<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_image":
			saveUploadedImage("printUploadForm", "printUploadForm");
			break;
		default:
			printUploadForm();
	}

//////////////////////////////////////////////////////////////////////////


function printUploadForm($msg = "&nbsp;"){
	///////////////////////////////
	// editing or adding
	if(isset($_REQUEST['imgId']) && $_REQUEST['imgId']>0){
		$modeText = "Edit";
		$dbImage = new db_image($_REQUEST['imgId']);
		$gal = new db_gallery($dbImage->getGalId());
	}
	else{
		$adError = adError::getInstance();
		$adError->addUserError("no image specified");
		$adError->printErrorPage();
	}
	
	
	$uploadForm = getUploadImageForm("/".ADMIN_PATH."/images/edit_image/save_image", $dbImage, $msg);
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Complete the form below to update a new image.
		</p>
	</div>
	<div class='halfWidth left'>
		{$uploadForm}
		<p>
			<a href='/{$adminPath}/images/view_gallery?gal_id={$gal->getId()}' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle($modeText." image");
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images' title='images'>images</a>", "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>".stripslashes($gal->getTitle())."</a>", $modeText." image"));
	$p->printPage();
}

?>