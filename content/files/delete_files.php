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
		case "delete":
			deleteFiles($files);
			break;
		default:
			printConfirmDelete($files);
	}

//////////////////////////////////////////////////////////////////////////
function deleteFiles($files){
	$cab = new db_cabinate(current($files)->getCabId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML_START
	<div class='halfWidth left'>
		<p>
			The files below have been deleted.
		</p>
			<p>
HTML_START;
			foreach($files as $fil){
				$html .= stripslashes($fil->getName())."<br/>";
				$fil->delete();
			}
	$html .= <<<HTML_END
			</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/files/view_cabinate?cab_id={$cab->getId()}' title='back'>&lt; back</a>
		</p>
	</div>
HTML_END;
	
	
	
	$p = adPage::getInstance();
	$p->setTitle("delete files");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>".stripslashes($cab->getTitle())."</a>", "delete files"));
	$p->printPage();
	
}

function printConfirmDelete($files){
	////////////////////////////////////////////
	// Setup variables
	$cab = new db_cabinate(current($files)->getCabId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<FORM_START
	<form action='/{$adminPath}/files/delete_files/delete' method='get' class='fullWidth'>
		<fieldset>
			<p>
				Please confirm you would like to delete these files.
			</p>
			<p>
FORM_START;
			foreach($files as $fil){
				$html .= "<input type='hidden' value='".$fil->getId()."' name='fils[]' />";
				$html .= stripslashes($fil->getName())."<br/>";
			}
	$html .= <<<FORM_END
			</p>
			<p>
				<input type="submit" class="button" value="delete"  />
				<br/><br/>
				<a href='/{$adminPath}/images/view_cabinate?cab_id={$cab->getId()}' title='back'>&lt; back</a>
			</p>
		</fieldset>
	</form>
FORM_END;
	
	
	$p = adPage::getInstance();
	$p->setTitle("delete files");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files/' title='Files'>files</a>", "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>".stripslashes($cab->getTitle())."</a>", "delete files"));
	$p->printPage();
}
?>