<?php
	if(isset($_GET['cab_id']) && $_GET['cab_id']>0)
		$cab = new db_cabinate($_GET['cab_id']);
	else{
		$adError = adError::getInstance();
		$adError->addUserError("No group specified");
		$adError->printErrorPage();
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "delete":
			deleteCabinate($cab);
			break;
		default:
			printConfirmDelete($cab);
	}

//////////////////////////////////////////////////////////////////////////
function deleteCabinate($cab){
	$title = stripslashes($cab->getTitle());
	$numFiles = count($cab->getFiles());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<p>
			The group has been deleted.
		</p>
		<p>
			Group: {$title}<br/>
			Number of files: {$numFiles}
		</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/files' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$cab->delete();
	
	$p = adPage::getInstance();
	$p->setTitle("delete group");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "delete group"));
	$p->printPage();
	
}

function printConfirmDelete($cab){
	////////////////////////////////////////////
	// Setup variables
	$title = stripslashes($cab->getTitle());
	$numFiles = count($cab->getFiles());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Please confirm you would like to delete this group, <b>and all files inside it</b>. Any pages that include these files will no longer display be able them.
		</p>
		<p>
			Group: {$title}<br/>
			Number of files: {$numFiles}
		</p>
		<p>
			<input type="button" class="button" value="delete" onclick="window.location='/{$adminPath}/files/delete_cabinate/delete?cab_id={$cab->getId()}'" />
			<br/><br/>
			<a href='/{$adminPath}/files' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("delete group");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "delete group"));
	$p->printPage();
}
?>