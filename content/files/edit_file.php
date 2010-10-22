<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_file":
			saveUploadedFile("printUploadForm", "printUploadForm");
			break;
		default:
			printUploadForm();
	}

//////////////////////////////////////////////////////////////////////////


function printUploadForm($msg = "&nbsp;"){
	///////////////////////////////
	// editing or adding
	if(isset($_REQUEST['filId']) && $_REQUEST['filId']>0){
		$modeText = "Edit";
		$dbfile = new db_file($_REQUEST['filId']);
		$cab = new db_cabinate($dbfile->getCabId());
	}
	else{
		$adError = adError::getInstance();
		$adError->addUserError("no file specified");
		$adError->printErrorPage();
	}
	
	
	$uploadForm = getUploadFileForm("/".ADMIN_PATH."/files/edit_file/save_file", $dbfile, $msg);
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Complete the form below to update the file.
		</p>
	</div>
	<div class='halfWidth left'>
		{$uploadForm}
		<p>
			<a href='/{$adminPath}/files/view_cabinate?cab_id={$cab->getId()}' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle($modeText." file");
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files' title='Files'>files</a>", "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>".stripslashes($cab->getTitle())."</a>", $modeText." file"));
	$p->printPage();
}

?>