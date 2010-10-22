<?php
function saveUploadedFile($failFunc, $successFunc){
	
	////////////////////////////////////////////////
	// Ensure cabinate selected
	if(!isset($_POST['filCabId']) || $_POST['filCabId']<1){
		$failFunc("error - no file group selected");
		return false;
	}
	
	////////////////////////////////////////////////
	// Ensure name is present
	if(!isset($_POST['filName']) || trim($_POST['filName'])==""){
		$failFunc("error - file name is required");
		return false;
	}
	
	////////////////////////////////////////////////
	// If new file then there must be file provided
	if($_POST['filId']<1 && $_FILES['file']['tmp_name']==""){
		$failFunc("error - no file selected");
		return false;
	}
	
	////////////////////////////////////////////////
	// Save cabinate selection to session
	$_SESSION['FILE_UPLOAD_CABINATE_SELECTION'] = $_POST['filCabId'];
	
	////////////////////////////////////////////////
	// Check cabinate isn't full
	$cabinate = new db_cabinate($_POST['filCabId']);
	if($cabinate->getIsFull()){
		$failFunc("error - file group is full");
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
	
	////////////////////////////////////////////////
	// Open file object
	if($_POST['filId']>0)
		$dbFile = new db_file($_POST['filId']);
	else
		$dbFile = new db_file();
	
	
	
	////////////////////////////////////////////////
	// Set new stuff
	$dbFile->setName($_POST['filName']);
	$dbFile->setCabId($_POST['filCabId']);
	$dbFile->setDescription($_POST['filDescription']);
	
	
	if($_FILES['file']['name']!=""){			
		////////////////////////////////////////////////
		// Delete old file
		$dbFile->deleteFile();
		
		////////////////////////////////////////////////
		// If file provided set file stuff
		$dbFile->setMimeType($_FILES['file']['type']);
		$dbFile->setUploadedFileName($_FILES['file']['name']);
		if($_POST['filId']<1)
			$dbFile->setDateUploaded(date("U"));
		
		/////////////////////////////////
		// Save so we have an ID to put into filename
		$dbFile->save();
		
		/////////////////////////////////
		// Upload file. If upload failed and this was new file then remove all trace
		if(!$dbFile->uploadFile($_FILES['file'])){
			if($_POST['filId']<1)
				$dbFile->delete();
			$failFunc("error - could not upload file");
			return false;
		}
	}
	
	$dbFile->save();
	
	////////////////////////////
	// clear values so they don't show in form
	unset($_POST['filName']);
	unset($_POST['filCabId']);
	
	$successFunc("file saved");
}

function getUploadFileForm($action, $file = null, $msg = "&nbsp;"){
	$file = $file==NULL ? new db_file() : $file;
	
	$filName = isset($_POST['filName']) ? $_POST['filName'] : stripslashes($file->getName());
	$filDescription = isset($_POST['filDescription']) ? $_POST['filDescription'] : stripslashes($file->getDescription());
	if($file)
		$selected = $file->getCabID();
	elseif(isset($_SESSION['FILE_UPLOAD_CABINATE_SELECTION']))
		$selected = $_SESSION['FILE_UPLOAD_CABINATE_SELECTION'];
	else
		$selected = -1;
	$cabinateCombo = getCabinateCombo($selected);
	
	$html = <<<HTMLFORM
		<form action="{$action}" method="post" enctype="multipart/form-data" class="generic">
			<fieldset>
				<legend>File details</legend>
				<input type="hidden" name="filId" value="{$file->getId()}" />
				
				<label for="filName">Name</label>
				<input type="text" name="filName" id="filName" value="{$filName}" class="text" />
				
				<label for="filDescription" class="textarea">Description</label>
				<textarea name="filDescription" id="filDescription">{$filDescription}</textarea>
				
				<label for="filCabId">Cabinate</label>
				{$cabinateCombo}
				
				<label for="file">File (<a href="javascript:showPopup('fileUploadHelp')" title="help - file upload">?</a>)</label>
				<input type="file" name="file" id="file" class="file" />
				
				<label for="filSubmit">Save file</label>
				<input type="submit" value="save" id="filSubmit" class="submit" />
			</fieldset>
		</form>
		<p class="alert">{$msg}</p>
HTMLFORM;

	$p = adPage::getInstance();
	$p->addPopup("fileUploadHelp", getFileUploadHelpPopupHTML());
	return $html;
}

function getCabinateCombo($filCabId, $excludeCabId = -1){
	$dblink = dbLink::getInstance();
	$cabs = $dblink->getObjects("cabinate");
	
	$html = "<select name='filCabId' id='filCabId'>";
	foreach($cabs as $cab){
		///////////////////////////////////
		// $excludeCabId is used by files/move_files
		if($cab->getId()==$excludeCabId)
			continue;
			
		$fullMsg = $cab->getIsFull() ? "(full)" : "";
		$selected = $cab->getId()==$filCabId ? "selected='selected'" : "";
		
		$html .= "<option value='".$cab->getId()."' ".$selected.">".stripslashes($cab->getTitle())." ".$fullMsg."</option>";
	}
	$html .= "</select>";
	
	return $html;
}

function getFileUploadHelpPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - File upload</h1>
			<p>Almost any file can be uploaded, although there are some that are restricted.</p>
			<p>There will be limits on the size of the file that can be uploaded. Usually files above 2MB cannot be uploaded.</p>
			<input type="button" class="submit" onclick="hidePopup('fileUploadHelp')" value="close" />
		</div>
HTML;
	return $html;
}
?>