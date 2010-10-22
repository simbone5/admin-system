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
		case "move":
			moveImages($images);
			break;
		default:
			printSelectGallery($images);
	}

//////////////////////////////////////////////////////////////////////////
function moveImages($images){
	$oldGal = new db_gallery(current($images)->getGalId());
	$newGal = new db_gallery($_GET['imgGalId']);
		
	////////////////////////////////////////////////
	// Check gallery isn't full
	if($newGal->getIsFull()){
		printSelectGallery($images, "error - gallery is full");
		return false;
	}	
	
	////////////////////////////////////////////////
	// Check gallery has room
	if($newGal->getLimit()>0){
		$freeSpace = $newGal->getLimit() - count($newGal->getImages());
		if(count($images)>$freeSpace){
			printSelectGallery($images, "error - cannot move ".count($images)." images as selected gallery has only ".$freeSpace." spaces available");
			return false;
		}
	}
	
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML_START
	<div class='halfWidth left'>
		<p>
			The images below have been moved.
		</p>
			<p>
HTML_START;
			foreach($images as $dbImage){
				$html .= stripslashes($dbImage->getName())."<br/>";
				
				////////////////////////////////////////////////
				// Create image and delete existing files
				$image = new image($dbImage->getDimensions());
				$image->setDbImage($dbImage);
				$image->deleteImages();

				////////////////////////////////////////////////
				// Set new gallery and force dimensions to be reloaded
				$dbImage->setGalId($newGal->getId());
				$dims = $dbImage->getDimensions(TRUE);
					
				////////////////////////////////////////////////
				// We've changed gallery so recreate images
				$image = new image($dims);
				$image->setDbImage($dbImage);
				$image->createImages();
				
				$dbImage->save();
			}
	$html .= <<<HTML_END
			</p>
		<p>
			<b>Moved</b>
			<br/><br/>
			<a href='/{$adminPath}/images/view_gallery?gal_id={$oldGal->getId()}' title='back'>&lt; back</a>
		</p>
	</div>
HTML_END;
	
	
	
	$p = adPage::getInstance();
	$p->setTitle("moves images");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$oldGal->getId()."' title='".htmlentities(stripslashes($oldGal->getTitle()), ENT_QUOTES)."'>".stripslashes($oldGal->getTitle())."</a>", "move images"));
	$p->printPage();
	
}

function printSelectGallery($images, $msg = "&nbsp;"){
	////////////////////////////////////////////
	// Setup variables
	$gal = new db_gallery(current($images)->getGalId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<FORM_START
	<div class='fullWidth'>
		<form action='/{$adminPath}/images/move_images/move' method='get' class='generic'>
			<fieldset>
				<p>
					Please select the gallery you would like to move these images to.
				</p>
				<p>
FORM_START;
				//////////////////////////////////////////
				// List images
				foreach($images as $img){
					$html .= "<input type='hidden' value='".$img->getId()."' name='imgs[]' />";
					$html .= stripslashes($img->getName())."<br/>";
				}
				
				//////////////////////////////////////////
				// List gallery dropdown
				$html .= "<label for='imgGalId'>Gallery to move to</label>";
				$html .= getGalleryCombo(-1, $gal->getId());
				
		$html .= <<<FORM_END
					<br/><br/>
				</p>
				<p>
					<input type="submit" class="button" value="move"  />
					</p>
			</fieldset>
		</form>
		<p class='alert'>{$msg}</p>
		<p><a href='/{$adminPath}/images/view_gallery?gal_id={$gal->getId()}' title='back'>&lt; back</a></p>
				
	</div>
FORM_END;
	
	
	$p = adPage::getInstance();
	$p->setTitle("move images");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images/' title='Images'>images</a>", "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>".stripslashes($gal->getTitle())."</a>", "move images"));
	$p->printPage();
}
?>