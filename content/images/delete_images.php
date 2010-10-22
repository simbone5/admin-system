<?php
	if(!isset($_GET['imgs']) || count($_GET['imgs'])<1){
		$adError = adError::getInstance();
		$adError->addUserError("No images specified");
		$adError->printErrorPage();
	}
	
	$images = array();
	foreach($_GET['imgs'] as $imgId){
		$img = new db_image($imgId);
		if($img->getId()>0)
			$images[] = $img;
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "delete":
			deleteImages($images);
			break;
		default:
			printConfirmDelete($images);
	}

//////////////////////////////////////////////////////////////////////////
function deleteImages($images){
	$gal = new db_gallery(current($images)->getGalId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML_START
	<div class='halfWidth left'>
		<p>
			The images below have been deleted.
		</p>
			<p>
HTML_START;
			foreach($images as $img){
				$html .= stripslashes($img->getName())."<br/>";
				$img->delete();
			}
	$html .= <<<HTML_END
			</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/images/view_gallery?gal_id={$gal->getId()}' title='back'>&lt; back</a>
		</p>
	</div>
HTML_END;
	
	
	
	$p = adPage::getInstance();
	$p->setTitle("delete images");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>".stripslashes($gal->getTitle())."</a>", "delete images"));
	$p->printPage();
	
}

function printConfirmDelete($images){
	////////////////////////////////////////////
	// Setup variables
	$gal = new db_gallery(current($images)->getGalId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<FORM_START
	<form action='/{$adminPath}/images/delete_images/delete' method='get' class='fullWidth'>
		<fieldset>
			<p>
				Please confirm you would like to delete these images.
			</p>
			<p>
FORM_START;
			foreach($images as $img){
				$html .= "<input type='hidden' value='".$img->getId()."' name='imgs[]' />";
				$html .= stripslashes($img->getName())."<br/>";
			}
	$html .= <<<FORM_END
			</p>
			<p>
				<input type="submit" class="button" value="delete"  />
				<br/><br/>
				<a href='/{$adminPath}/images/view_gallery?gal_id={$gal->getId()}' title='back'>&lt; back</a>
			</p>
		</fieldset>
	</form>
FORM_END;
	
	
	$p = adPage::getInstance();
	$p->setTitle("delete images");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>".stripslashes($gal->getTitle())."</a>", "delete images"));
	$p->printPage();
}
?>