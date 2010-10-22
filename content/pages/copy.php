<?php
	
	if(!isset($_GET['pag_id']) || $_GET['pag_id']<1){
		$adError = adError::getInstance();
		$adError->addUserError("no page specified");
		$adError->printErrorPage();
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		default:
			savePage();
	}
	
/////////////////////////////////////////////////////////////////
function savePage(){
	$page = new db_page($_GET['pag_id']);
	$oldFields = $page->getFields();
	
	///////////////////////////////////
	// Set Id to -1 to force _db_object to recognise it as a new record
	// Get parent Id so we can then set it again.
	// Change page names
	$parId = $page->getParentId(); 
	if($parId=="")
		$parId = -1;
	$page->setId(-1);
	$page->setInternalName(stripslashes($page->getInternalName())." - copy");
	$page->setExternalName("");
	$page->setParentId($parId);
	
	/////////////////////////////////
	// Save
	$page->save();
	$page->createFields();
	
	/////////////////////////////////
	// Populate field data
	foreach($page->getFields() as $newField){
		foreach($oldFields as $oldKey=>$oldField){
			//echo $oldField->getFieldID()."==".$newField->getFieldID()."<br/>";
			if($oldField->getFieldID()==$newField->getFieldID()){
				$newField->setContent($oldField->getContent());
				$newField->save();
				unset($oldFields[$oldKey]);
				break;
			}
		}
	}
	
	//////////////////////////////////
	// change mode so that we can load the edit page.
	// Add an onload to the page so that user is presented with page-details popup
	$GLOBALS['NAV_PATH']['mode'] = "edit";
	$p = adPage::getInstance();
	$p->addOnload('showPopup("editPageDetails")');
	require_once("edit.php");	
	
	/*$newPage = new db_page();
	
	
	$newPage->setTemId($_GET['templateId']);
	$newPage->setInternalName($_GET['name']);
	$newPage->setLive(TRUE);
	$newPage->setEditable(TRUE);
	$newPage->setDeletable(TRUE);
	$newPage->setHidden(FALSE);
	$newPage->setCanManage(TRUE);
	$newPage->setKeywords("");
	$newPage->setDescription("");
	$newPage->setParentId($_GET['parentPagId']);/*

	$page->save();
	
	$page->createFields();
	
	//////////////////////////////////
	// change mode so that we can load the edit page.
	// Add an onload to the page so that user is presented with page-details popup
	$GLOBALS['NAV_PATH']['mode'] = "edit";
	$p = adPage::getInstance();
	$p->addOnload('showPopup("editPageDetails")');
	require_once("edit.php");*/
}

?>