<?php
	if(!isset($_GET['fils']) || count($_GET['fils'])<1){
		$adError = adError::getInstance();
		$adError->addUserError("No files specified");
		$adError->printErrorPage();
	}
	
	$files = array();
	foreach($_GET['fils'] as $filId){
		$fil = new db_file($filId);
		if($fil->getId()>0)
			$files[] = $fil;
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "move":
			moveFiles($files);
			break;
		default:
			printSelectCabinate($files);
	}

//////////////////////////////////////////////////////////////////////////
function moveFiles($files){
	if(!isset($_GET['filCabId'])){
		printSelectCabinate($files, "error - no cabinate selected");
		return false;
	}

	$oldCab = new db_cabinate(current($files)->getCabId());
	$newCab = new db_cabinate($_GET['filCabId']);
		
	////////////////////////////////////////////////
	// Check cabinate isn't full
	if($newCab->getIsFull()){
		printSelectCabinate($files, "error - file group is full");
		return false;
	}	
	
	////////////////////////////////////////////////
	// Check cabinate has room
	if($newCab->getLimit()>0){
		$freeSpace = $newCab->getLimit() - count($newCab->getFiles());
		if(count($files)>$freeSpace){
			printSelectCabinate($files, "error - cannot move ".count($files)." files as selected file group has only ".$freeSpace." spaces available");
			return false;
		}
	}
	
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML_START
	<div class='halfWidth left'>
		<p>
			The files below have been moved.
		</p>
			<p>
HTML_START;
			foreach($files as $dbFile){
				$html .= stripslashes($dbFile->getName())."<br/>";

				////////////////////////////////////////////////
				// Set new cabinate
				$dbFile->setCabId($newCab->getId());
				$dbFile->save();
			}
	$html .= <<<HTML_END
			</p>
		<p>
			<b>Moved</b>
			<br/><br/>
			<a href='/{$adminPath}/files/view_cabinate?cab_id={$oldCab->getId()}' title='back'>&lt; back</a>
		</p>
	</div>
HTML_END;
	
	
	
	$p = adPage::getInstance();
	$p->setTitle("moves files");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$oldCab->getId()."' title='".htmlentities(stripslashes($oldCab->getTitle()), ENT_QUOTES)."'>".stripslashes($oldCab->getTitle())."</a>", "move files"));
	$p->printPage();
	
}

function printSelectCabinate($files, $msg = "&nbsp;"){
	////////////////////////////////////////////
	// Setup variables
	$cab = new db_cabinate(current($files)->getCabId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<FORM_START
	<div class='fullWidth'>
		<form action='/{$adminPath}/files/move_files/move' method='get' class='generic'>
			<fieldset>
				<p>
					Please select the file group you would like to move these files to.
				</p>
				<p>
FORM_START;
				//////////////////////////////////////////
				// List files
				foreach($files as $fil){
					$html .= "<input type='hidden' value='".$fil->getId()."' name='fils[]' />";
					$html .= stripslashes($fil->getName())."<br/>";
				}
				
				//////////////////////////////////////////
				// List cabinate dropdown
				$html .= "<label for='filCabId'>File group to move to</label>";
				$html .= getCabinateCombo(-1, $cab->getId());
				
		$html .= <<<FORM_END
					<br/><br/>
				</p>
				<p>
					<input type="submit" class="button" value="move"  />
					</p>
			</fieldset>
		</form>
		<p class='alert'>{$msg}</p>
		<p><a href='/{$adminPath}/files/view_cabinate?cab_id={$cab->getId()}' title='back'>&lt; back</a></p>
				
	</div>
FORM_END;
	
	
	$p = adPage::getInstance();
	$p->setTitle("move files");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>".stripslashes($cab->getTitle())."</a>", "move files"));
	$p->printPage();
}
?>