<?php
function saveUploadedImage($failFunc, $successFunc){
	
	////////////////////////////////////////////////
	// Save gallery selection to session
	$_SESSION['IMAGE_UPLOAD_GALLERY_SELECTION'] = $_POST['imgGalId'];
	
	////////////////////////////////////////////////
	// Ensure name is present
	if(!isset($_POST['imgName']) || trim($_POST['imgName'])==""){
		$failFunc("error - image name is required");
		return false;
	}
	
	////////////////////////////////////////////////
	// If new image then there must be file provided
	if($_POST['imgId']<1 && $_FILES['file']['tmp_name']==""){
		$failFunc("error - no image selected");
		return false;
	}
	
	////////////////////////////////////////////////
	// Validate file upload
	if($_FILES['file']['name']!=""){
		$result = validateFileUpload($_FILES['file']);
		if($result!==TRUE){
			$failFunc($result);
			return false;
		}
	}
	
	//////////////////////////////
	//	ensure we've been given an image or zip
	$fileType = $_FILES['file']['type'];
	$zipFile = false;
	$acceptedImages = array("image/jpeg", "image/pjpeg", "image/gif", "image/png");
	if(!in_array($fileType, $acceptedImages)){
		/////////////////////////////////////
		// Work out whether it is a zip. 
		if(strpos($_FILES['file']['type'], "zip")===FALSE && strpos(getFileType($_FILES['file']['tmp_name']), "zip")===FALSE){
			$failFunc("error - invalid file uploaded");
			return false;
		}
		$zipFile = true;
	}
	
	
	//////////////////////////////
	//	Can't upload zip if updating single image
	if($_POST['imgId']>0 && $zipFile){
		$failFunc("error - zip files not permitted");
		return false;
	}
	
	
	
	////////////////////////////////////////////////
	// Extract zip
	if($zipFile){
		$tempUnzipDir = sys_get_temp_dir()."unzipped";
		$images = fileUtility::unzip($_FILES['file']['tmp_name'], $tempUnzipDir);
		if($images===FALSE){
			$failFunc("error - could not extract files");
			return false;
		}elseif(count($images)==0){
			$failFunc("error - 0 files found in zip");
			return false;
		}
	}
	else{
		$image = $_FILES['file']['tmp_name'];
	}
	
	
	////////////////////////////////////////////////
	// Check gallery isn't full (when dealing with new images)
	$gallery = new db_gallery($_POST['imgGalId']);
	$freeSpace = $gallery->getLimit()-count($gallery->getImages());
	$numImagesToUpload = $zipFile ? count($images) : 1;
	if($_POST['imgId']<1 && $gallery->getLimit()>0 && $numImagesToUpload>$freeSpace){
		$failFunc("error - insufficient room in gallery");
		return false;
	}
	
	
	
	if(!$zipFile){
		////////////////////////////////////////////////
		// Open and update
		if($_POST['imgId']>0)
			$dbImage = new db_image($_POST['imgId']);
		else
			$dbImage = new db_image();
		$dbImage->setName($_POST['imgName']);
		$dbImage->setGalId($_POST['imgGalId']);
		$dbImage->save();
		
		////////////////////////////////////////////////
		// Delete old images if gallery changed
		// This should not involve zips as gallery change must be for existing image
		if($dbImage->getGalId()>0 && $dbImage->getGalId()!=$_POST['imgGalId']){
			$dbImage = updateGalleryChange($dbImage);
		}
	}
	else{
		$i = 0;
		$numWidth = strlen(count($images));
		$dbImages = array();
		$skips = array();
		foreach($images as $image){
			$tmpFileType = getFileType($tempUnzipDir."/".$image);
			if(!in_array($tmpFileType, $acceptedImages)){
				$skips[] = $image;
				continue;
			}
			$dbImages[$i] = new db_image();
			$dbImages[$i]->setName($_POST['imgName']." (".sprintf("%0".$numWidth."d", $i+1).")");
			$dbImages[$i]->setGalId($_POST['imgGalId']);
			$dbImages[$i]->setMimeType($tmpFileType);
			$dbImages[$i]->setUploadedFileName($image);
			$dbImages[$i]->setDateUploaded(date("U"));
			$dbImages[$i]->save();
			if(!$dbImages[$i]->uploadFile($tempUnzipDir."/".$image)){
				$failFunc("error - could not upload image from zip");
				return false;
			}
			$i++;
		}
		if(count($dbImages)==0){
			$msg = "no images saved";
			$msg .= count($skips)==0 ? "" : " - skipped files:<br/>".implode("<br/>", $skips);
			$failFunc($msg);
			return false;
		}
	}
	
	
	
	////////////////////////////////////////////////
	// Save uploaded image (individual - not zips as they are saved above)
	if(!$zipFile && $_FILES['file']['name']!=""){	
		////////////////////////////////////////////////
		// Remove old images including original size
		// (if file extension is the same then code below
		// would just replace them, but if diff type of file we need to get rid 
		// of old stuff)
		if($dbImage->getId()>0){
			$origDim = new db_dimension();
			$origDim->setWidth(0);
			$origDim->setHeight(0);
			$image = new image($dbImage->getDimensions()+array($origDim));
			$image->setDbImage($dbImage);
			$image->deleteImages();
		}
		
		////////////////////////////////////////////////
		// If file provided set file stuff
		$dbImage->setMimeType($_FILES['file']['type']);
		$dbImage->setUploadedFileName($_FILES['file']['name']);
		if($_POST['imgId']<1)
			$dbImage->setDateUploaded(date("U"));
		
		/////////////////////////////////
		// Save so we have an ID to put into filename
		$dbImage->save();
		
		/////////////////////////////////
		// Upload file. If upload failed and this was new image then remove all trace
		if(!$dbImage->uploadFile($_FILES['file'])){
			if($_POST['imgId']<1)
				$dbImage->delete();
			$failFunc("error - could not upload image");
			return false;
		}
		
	}
	
	
	////////////////////////////
	// clear values so they don't show in form
	unset($_POST['imgName']);
	unset($_POST['imgGalId']);
	
	if($zipFile && count($dbImages)>1){
		$msg = count($dbImages)." images saved";
		$msg .= count($skips)==0 ? "" : " - skipped files:<br/>".implode("<br/>", $skips);
		$successFunc($msg);
	}
	else
		$successFunc("image saved");
}

function updateGalleryChange($dbImage){
	$image = new image($dbImage->getDimensions());
	$image->setDbImage($dbImage);
	$image->deleteImages();

	////////////////////////////////////////////////
	// Set new gallery and force dimensions to be reloaded
	$dbImage->setGalId($_POST['imgGalId']);
	$dims = $dbImage->getDimensions(TRUE);
		
	////////////////////////////////////////////////
	// We've changed gallery so recreate images, but only if we've not uploaded new file
	if($_FILES['file']['name']==""){
		$image = new image($dims);
		$image->setDbImage($dbImage);
		$image->createImages();
	}
	return $dbImage;
}


function getUploadImageForm($action, $image = null, $msg = "&nbsp;"){
	$image = $image==NULL ? new db_image() : $image;
	
	$imgName = isset($_POST['imgName']) ? $_POST['imgName'] : stripslashes($image->getName());
	if($image->getGalId()>0)
		$selected = $image->getGalId();
	elseif(isset($_SESSION['IMAGE_UPLOAD_GALLERY_SELECTION']))
		$selected = $_SESSION['IMAGE_UPLOAD_GALLERY_SELECTION'];
	else
		$selected = -1;
		
	$galleryCombo = getGalleryCombo($selected);
	
	$html = <<<HTMLFORM
		<form action="{$action}" method="post" enctype="multipart/form-data" class="generic">
			<fieldset>
				<legend>Image details</legend>
				<input type="hidden" name="imgId" value="{$image->getId()}" />
				
				<label for="imgName">Name</label>
				<input type="text" name="imgName" id="imgName" value="{$imgName}" class="text" />
				
				<label for="imgGalId">Gallery</label>
				{$galleryCombo}
				
				<label for="file">Image (.jpg, .png, .gif) (<a href="javascript:showPopup('imageUploadHelp')" title="help - image upload">?</a>)</label>
				<input type="file" name="file" id="file" class="file" />
				
				<label for="imgSubmit">Save image</label>
				<input type="submit" value="save" id="imgSubmit" class="submit" />
			</fieldset>
		</form>
		<p class="alert">{$msg}</p>
HTMLFORM;

	$p = adPage::getInstance();
	$p->addPopup("imageUploadHelp", getImageUploadHelpPopupHTML());
	return $html;
}

function getGalleryCombo($imgGalId, $excludeGalId = -1){
	$dblink = dbLink::getInstance();
	$gals = $dblink->getObjects("gallery");
	
	$html = "<select name='imgGalId' id='imgGalId'>";
	foreach($gals as $gal){
		///////////////////////////////////
		// $excludeGalId is used by images/move_images
		if($gal->getId()==$excludeGalId)
			continue;
			
		$fullMsg = $gal->getIsFull() ? "(full)" : "";
		$selected = $gal->getId()==$imgGalId ? "selected='selected'" : "";
		
		$html .= "<option value='".$gal->getId()."' ".$selected.">".stripslashes($gal->getTitle())." ".$fullMsg."</option>";
	}
	$html .= "</select>";
	
	return $html;
}

function getImageUploadHelpPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Image upload</h1>
			<p>There are three types of image you can upload:</p>
			<ul class="bulleted">
				<li>jpegs - most common for photos. File extension .jpg or .jpeg</li>
				<li>pngs - good for graphics. File extension .png</li>
				<li>gif - good for simple graphics. File extension .gif</li>
			</ul>
			<p>Zip files can be uploaded. These can contain multiple images of the types listed above.</p>
			<p>Formats that are not allowed:</p>
			<ul class="bulleted">
				<li>bitmaps. File extension .bmp</li>
				<li>tiffs. File extension .tiff</li>
				<li>various others</li>
			</ul>
			<p>It is advised that any gifs or pngs that use transparency are not uploaded as they will be displayed with black backgrounds where the transparent parts are. Additionally, animated gifs will displayed as static images.</p>
			<p>There will be limits on the size of the file that can be uploaded. Usually files above 2MB cannot be uploaded.</p>
			<input type="button" class="submit" onclick="hidePopup('imageUploadHelp')" value="close" />
		</div>
HTML;
	return $html;
}
?>