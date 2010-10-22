<?php
	if(isset($_GET['gal_id']) && $_GET['gal_id']>0)
		$gal = new db_gallery($_GET['gal_id']);
	else{
		$adError = adError::getInstance();
		$adError->addUserError("No gallery specified");
		$adError->printErrorPage();
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "delete":
			deleteGallery($gal);
			break;
		default:
			printConfirmDelete($gal);
	}

//////////////////////////////////////////////////////////////////////////
function deleteGallery($gal){
	$title = stripslashes($gal->getTitle());
	$numImages = count($gal->getImages());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<p>
			The gallery has been deleted.
		</p>
		<p>
			Gallery: {$title}<br/>
			Number of images: {$numImages}
		</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/images' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$gal->delete();
	
	$p = adPage::getInstance();
	$p->setTitle("delete gallery");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "delete gallery"));
	$p->printPage();
	
}

function printConfirmDelete($gal){
	////////////////////////////////////////////
	// Setup variables
	$title = stripslashes($gal->getTitle());
	$numImages = count($gal->getImages());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Please confirm you would like to delete this gallery, <b>and all images inside it</b>. Any pages that include these images will no longer display be able them.
		</p>
		<p>
			Gallery: {$title}<br/>
			Number of images: {$numImages}
		</p>
		<p>
			<input type="button" class="button" value="delete" onclick="window.location='/{$adminPath}/images/delete_gallery/delete?gal_id={$gal->getId()}'" />
			<br/><br/>
			<a href='/{$adminPath}/images' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("delete gallery");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "delete gallery"));
	$p->printPage();
}
?>