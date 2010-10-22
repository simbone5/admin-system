<?php
	if(!isset($page)){//<-$page can be set in add.php which loads this script
		if(isset($_GET['pag_id'])){
			$page = new db_page($_GET['pag_id']);
			$pageToEdit = $page;
		}
	}
	else{
		$pageToEdit = $page;
	}
	
	if(isset($_REQUEST['editPageHref'])){
		$contentLoader = contentLoader::getInstance();
		$contentLoader->loadContent(array_filter(explode("/", $_REQUEST['editPageHref'])));
		$pageToEdit = $contentLoader->getItem();
		if(strtolower(get_class($pageToEdit))=="db_page"){
			$page = $pageToEdit;
		}
		elseif(strtolower(get_class($pageToEdit))=="menuitem"){
			if($pageToEdit->getType()=="page"){
				$pageToEdit = new db_page($pageToEdit->getValue());
				$page = $pageToEdit;
			}
		}
	}
	
	if((!isset($pageToEdit) || !$pageToEdit || $pageToEdit->getId()<1) && (!isset($page) || !$page || $page->getId()<1)){
		$adError = adError::getInstance();
		$adError->addUserError("invalid page id supplied");
		$adError->printErrorPage();
	}

	switch($GLOBALS['NAV_PATH']['mode']){
		case "delete":
			deletePage($page);
			break;
		case "savedetails":
			saveDetails($page);
			break;
		case "save":
			saveEditPage($page);
			break;
		case "update_fields":
			updateFields($page);
			break;
		case "edit":
		default:
			printPageEdit($pageToEdit);
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function printPageEdit($page, $editPageDetailsMsg = "", $editPageMsg = "&nbsp;"){
	
	$iframeSrc = "/ad_page_edit/".$page->getId();

	/////////////////////////////////////////
	// Get fields and put into hidden div
	$fields = $page->getFields();
	// If there are no fields force an update to create them
	if(count($fields)==0){
		$page->createFields();
		$fields = $page->getFields();
	}
	$fieldHtml = "";
	foreach($fields as $field){
		$fieldHtml .= $field->getContentEdit();
		$fieldHtml .= $field->getInformationFields();
	}
	
	
	$intName = stripslashes($page->getInternalName());
	$state = $page->getLive() ? "page live" : "page offline";
	$deleteBt = $page->getDeletable() ?  "<input type='button' class='submit' id='delete' onclick=\"showPopup('confirmDeletePage')\" value='delete' />" : "";
	$menu = getLeftMenu();
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div class='editOptions'>
			<p>
				<b>Name: </b>{$intName}<br/>
				<b>State: </b>{$state}<br/>
				<b>Options:</b>
			</p>
			<form action="/{$adminPath}/pages/edit/save?pag_id={$page->getId()}" method="post" id="fieldValuesForm" onsubmit="return loadFieldValues()" class="generic">
				<fieldset>
					<input type="hidden" name="editPageHref" id="editPageHref" value="--set-by-selectLink-function-when-link-clicked" />
					<input type="hidden" name="showDetailsPopup" id="showDetailsPopup" value="--set-to-true-when-the-option-to-save-before-details-popup-shown" />
					
					<div style='display:none;'>
						{$fieldHtml}
					</div>
					<input type='submit' id='save' class='submit' value='save' />
				</fieldset>
			</form>
			<input type='button' class='submit' id='details' onclick="showPopup('savePageBeforeDetails')" value='details' />
			{$deleteBt}
			<input type='button' class='submit' id='copy' onclick="window.location='/{$adminPath}/pages/copy?pag_id={$page->getId()}'" value='copy page' />
			<input type='button' class='submit' id='cancel' onclick="window.location='/{$adminPath}/pages'" value='cancel' />
			
			<span class='alert'>{$editPageMsg}</span>
			{$menu}
		</div>
		<iframe id="previewWrapper" src="{$iframeSrc}" height="100%;"></iframe>
HTML;

			
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->addCss("adCss/iFramePreview.css");
	$p->addCss("adCss/pages/edit-screen.css");
	$p->addCss("adCss/help-screen.css");
	$p->addJavascript("adJavascripts/pages/edit.js");
	$p->addJavascript("adJavascripts/generalFunctions.js");
	$p->addJavascript("adJavascripts/ajax.js");
	$p->setInternalJavascript(getInternalJavascript($page));
	$p->setStructured(FALSE);
	$p->addPopup("editPageDetails", getEditPageDetailsPopupHTML($page, $editPageDetailsMsg));
	$p->addPopup("selectEditImage", getSelectEditImagePopupHTML());
	$p->addPopup("promptForImageAlt", getPromptForImageAltPopupHTML());
	$p->addPopup("pageTitleHelp", getPageTitleHelpPopupHTML($page));
	$p->addPopup("pageInternalNameHelp", getPageInternalNameHelpPopupHTML());
	$p->addPopup("pageKeywordsDescHelp", getPageKeywordsDescPopupHTML());
	$p->addPopup("pageLiveHelp", getPageLivePopupHTML());
	$p->addPopup("confirmDeletePage", getConfirmDeletePagePopupHTML($page));
	$p->addPopup("brokenMenuItemWarning", getBrokenMenuItemWarningPopupHTML($page));
	$p->addPopup("confirmSave", getConfirmSavePagePopupHTML());
	$p->addPopup("savePageBeforeDetails", getSavePageBeforeDetailsPopupHTML());
	$p->addPopup("imageAltHelp", getImageAltPopupHTML());
	$p->setTitle("edit page");
	$p->addOnload('alterFrameNavigation("'.$_SERVER['SERVER_NAME'].'")');
	if(isset($_POST['showDetailsPopup']) && $_POST['showDetailsPopup']=="1")
		$p->addOnload('showPopup("editPageDetails")');
	$p->printPage();
}

function getleftMenu(){
	$p = adPage::getInstance();
	
	$html = "<p><br/><br/><br/><b>Full navigation: </b></p>";
	$html .= "<ul>";
	foreach($p->getMenuItems() as $href => $menu){
		$html .= "<li><a href='/".ADMIN_PATH."/".$href."' title='".$menu."'>".ucfirst($menu)."</a></li>";
	}
	$html .= "</ul>";
	return $html;
}

function saveEditPage($page){
	if(isset($_POST['fields'])){
		foreach($_POST['fields'] as $details){
			$class = "field_".$details['fieType'];
			$field = new $class();
			$field->openFromId($details['fieID']);
			$field->setContent(isset($details['fieContent']) ? $details['fieContent'] : "");
			$field->save();
		}
	}
	printPageEdit($page, "", "changes saved");
}

function saveDetails($page){
	if(!isset($_GET['pagExternalName']) || $_GET['pagExternalName']=="" || !isset($_GET['pagInternalName']) || $_GET['pagInternalName']==""){
		$p = adPage::getInstance();
		$p->addOnload('showPopup("editPageDetails")');
		printPageEdit($page, "error - page title and internal name are required");
		return false;
	}
	
	$page->setExternalName($_GET['pagExternalName']);
	$page->setKeywords($_GET['pagKeywords']);
	$page->setDescription($_GET['pagDescription']);
	$page->setInternalName($_GET['pagInternalName']);
	$page->setLive(isset($_GET['pagLive']));
	if($_SESSION['ADMIN_USER']->getSuperAdmin()){
		$page->setEditable(isset($_GET['pagEditable']));
		$page->setDeletable(isset($_GET['pagDeletable']));
		$page->setHidden(isset($_GET['pagHidden']));
		$page->setCanManage(isset($_GET['pagCanManage']));
	}
	$page->save();
	printPageEdit($page);
	return true;
}

function getEditPageDetailsPopupHTML($page, $msg){
	if($GLOBALS['NAV_PATH']['mode']=="savedetails"){
		$externalName = stripslashes($_GET['pagExternalName']);
		$keywords = stripslashes($_GET['pagKeywords']);
		$description = stripslashes($_GET['pagDescription']);
		$internalName = stripslashes($_GET['pagInternalName']);
		$pagLive = isset($_GET['pagLive']) ? "checked='checked'" : "";
		$pagEditable = isset($_GET['pagEditable']) ? "checked='checked'" : "";
		$pagDeletable = isset($_GET['pagDeletable']) ? "checked='checked'" : "";
		$pagHidden = isset($_GET['pagHidden']) ? "checked='checked'" : "";
		$pagCanManage = isset($_GET['pagCanManage']) ? "checked='checked'" : "";
	}
	else{
		$externalName = stripslashes($page->getExternalName());
		$keywords = stripslashes($page->getKeywords());
		$description = stripslashes($page->getDescription());
		$internalName = stripslashes($page->getInternalName());
		$pagLive = $page->getLive() ? "checked='checked'" : "";
		$pagEditable = $page->getEditable() ? "checked='checked'" : "";
		$pagDeletable = $page->getDeletable() ? "checked='checked'" : "";
		$pagHidden = $page->getHidden() ? "checked='checked'" : "";
		$pagCanManage = $page->getCanManage() ? "checked='checked'" : "";
	}
	
	$adminPath = ADMIN_PATH;
	if($externalName=="" || $internalName==""){
		$classDisabled = "disabled";
		$disabled = "disabled='disabled'";
		$externalName = $internalName;
	}
	else{
		$classDisabled = "";
		$disabled = "";
	}
	$html = <<<PART1
	<form action='/{$adminPath}/pages/edit/saveDetails' id='pageDetailsForm' method='get' class='generic'>
		<fieldset>
			<legend>Page details</legend>
			<input type='hidden' name='pag_id' value='{$page->getId()}' />
			
			<label for='pagExternalName'>Title (<a href="javascript:showPopup('pageTitleHelp')" title="help - page title">?</a>)</label>
			<input type='text' name='pagExternalName' maxlength='40' id='pagExternalName' value="{$externalName}" class='text'/>
			
			<label for='pagKeywords' class='textarea'>Keywords (<a href="javascript:showPopup('pageKeywordsDescHelp')" title="help - page keywords">?</a>)</label>
			<textarea name='pagKeywords' id='pagKeywords' cols='100' rows='100'>{$keywords}</textarea>
			
			<label for='pagDescription' class='textarea'>Description (<a href="javascript:showPopup('pageKeywordsDescHelp')" title="help - page description">?</a>)</label>
			<textarea name='pagDescription' id='pagDescription' cols='100' rows='100'>{$description}</textarea>
			
			<label for='pagInternalName'>Internal Name (<a href="javascript:showPopup('pageInternalNameHelp')" title="help - page internal name">?</a>)</label>
			<input type='text' name='pagInternalName' maxlength='40' id='pagInternalName' value="{$internalName}" class='text'/>
			
			<label for='pagLive'>Is the page live (<a href="#" onclick="showPopup('pageLiveHelp');return false;" title="help - page live">?</a>)</label>
			<input type='checkbox' class='checkbox' name='pagLive' id='pagLive' {$pagLive} />
PART1;
	if($_SESSION['ADMIN_USER']->getSuperAdmin()){
		$html .= <<<PART2
				<label for='pagEditable' class='superAdmin'>Is the page editable</label>
				<input type='checkbox' class='checkbox' name='pagEditable' id='pagEditable' {$pagEditable} />
				
				<label for='pagDeletable' class='superAdmin'>Is the page deletable</label>
				<input type='checkbox' class='checkbox' name='pagDeletable' id='pagDeletable' {$pagDeletable} />
				
				<label for='pagHidden' class='superAdmin'>Is the page hidden from menu</label>
				<input type='checkbox' class='checkbox' name='pagHidden' id='pagHidden' {$pagHidden} />
				
				<label for='pagCanManage' class='superAdmin'>Is the page hidden managable</label>
				<input type='checkbox' class='checkbox' name='pagCanManage' id='pagCanManage' {$pagCanManage} />
PART2;
	}
	$html .= <<<PART3
			<input type='submit' class='submit first' value='ok' />
			<input type='button' class='submit {$classDisabled}' {$disabled} onclick='hidePopup("editPageDetails")' value='cancel' />
		</fieldset>
	</form>
	<p class='alert'>{$msg}</p>
PART3;
	
	return $html;
}

function getConfirmDeletePagePopupHTML($page){
	$intName = stripslashes($page->getInternalName());
	$extName = stripslashes($page->getExternalName());
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div>
			<h1>Delete page</h1>
			<p>
				Please confirm that you wish to delete this page:<br/>
				Internal name: {$intName}<br/>
				Title: {$extName}
			</p>
			<p>
				The page will be permanently deleted, and will not be retrievable.
			</p>
			<form action="/{$adminPath}/pages/edit/delete" class='generic' method="get">
				<fieldset>
					<input type='hidden' name='pag_id' value='{$page->getId()}' />
					<input type="submit" class="submit first" value="delete" />
					<input type="button" class="submit" onclick="hidePopup('confirmDeletePage')" value="cancel" />
				</fieldset>
			</form>
		</div>
HTML;
	return $html;
}

function getPromptForImageAltPopupHTML(){
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div>
			<h1>Image</h1>
			<p>
				Please provide the "alternative text" for this image.
			</p>
			<form action="#" class='generic' method="get">
				<fieldset>
					<input type='hidden' name='imgSrc' id='imgSrc' value='' />
					
					<label for='imageAlt'>Alternative text (<a href="javascript:showPopup('imageAltHelp')" title="help - image 'alt' text">?</a>)</label>
					<input type='text' name='imageAlt' id='imageAlt' class='text' value='' />
					<input type="button" class="submit first" value="insert" onclick="insertSelectedEditImage()" />
					<input type="button" class="submit" onclick="hidePopup('promptForImageAlt')" value="back" />
				</fieldset>
			</form>
		</div>
HTML;
	return $html;
}


function getSelectEditImagePopupHTML(){

	$html = <<<HTML
		<div>
			<h1>Select Image</h1>
			<div id="galleryListWrapper">
				<img src="adImages/loader.gif" alt="loading" class="loading"/>
			</div>
			<input type="button" class="submit" onclick="hidePopup('selectEditImage')" value="cancel" />
		</div>
HTML;
	return $html;
}

function getConfirmSavePagePopupHTML(){
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div>
			<h1>Save changes</h1>
			<p>
				Save changes before moving to selected page?
			</p>
			<input type="button" class="submit first" onclick="loadFieldValues();document.getElementById('fieldValuesForm').submit()" value="save" />
			<input type="button" class="submit" onclick="window.location='/{$adminPath}/pages/edit?editPageHref='+document.getElementById('editPageHref').value" value="don't save" />

		</div>
HTML;
	return $html;
}


function getBrokenMenuItemWarningPopupHTML(){
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div>
			<h1>Broken menu item</h1>
			<p>
				Warning - this menu link (<span id='brokenMenuTitle'>--filled-in-by-selectBrokenLink-javascript-function</span>) links to a non-existant page.
			</p>
			<p>			
				It is advised you check this menu item in the Manage Menu section to ensure it is going to the correct page.
			</p>
			<input type="button" class="submit" onclick="hidePopup('brokenMenuItemWarning')" value="OK" />

		</div>
HTML;
	return $html;
}
function getSavePageBeforeDetailsPopupHTML(){
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
		<div>
			<h1>Save changes</h1>
			<p>
				Save any changes before showing page details?
			</p>
			<p>
				If you do not save and then click OK on the details window you will lose any changes you made to the page content.
			</p>
			<input type="button" class="submit first" onclick="loadFieldValues();document.getElementById('showDetailsPopup').value=1;document.getElementById('fieldValuesForm').submit()" value="save" />
			<input type="button" class="submit" onclick="hidePopup('savePageBeforeDetails');showPopup('editPageDetails')" value="don't save" />

		</div>
HTML;
	return $html;
}

function getImageAltPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Image 'Alt' Text</h1>
			<p>This is a description of what the image is showing. In most browsers, the text appears when users hover over the image or when the image cannot be loaded.</p>
			<input type="button" class="submit" onclick="hidePopup('imageAltHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getPageTitleHelpPopupHTML($page){
	if($page->getExternalName()!="")
		$pageUrl = "<p>Additionally, using this title allows visitors to access pages directly<br/>e.g. with the currently saved title the page's address is<br/>http://".$_SERVER['HTTP_HOST']."/".getUrlFormat($page->getExternalName())."</p>";
	else
		$pageUrl = "";
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Page Title</h1>
			<p>This is the title that (usually) appears in the browsers title bar when being viewed by a site visitor.</p>
			<p class='center'><img src='adImages/help/help-pageTitle.jpg' alt='page title example' /></p>
			{$pageUrl}
			<input type="button" class="submit" onclick="hidePopup('pageTitleHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getPageInternalNameHelpPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Page Internal Name</h1>
			<p>This name is used in this admin site. It is for your reference, and is never displayed to site visitors. Therefore you can have a descriptive and specific internal name.</p>
			<input type="button" class="submit" onclick="hidePopup('pageInternalNameHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getPageKeywordsDescPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Page Keywords and Description</h1>
			<p>Keywords and description are not normally viewed directly by visitors to your site. They are used by many search engines (such as Google) to determine what the pages is about. Some search engines then display the description within their search result listing.</p>
			<p><b>Keywords</b> - between 5 and 10 specific words, separated with commas (,)<br/>e.g. keyword1,keyword2,key word 3,keyword4, keyword5,keyword6,keyword7</p>
			<p><b>Description</b> - two or three short sentances about the page, summarising the content.</p>
			<input type="button" class="submit" onclick="hidePopup('pageKeywordsDescHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getPageLivePopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Page Live</h1>
			<p>When the checkbox is ticked the page is said to be live. This means that following any links to the page (e.g. menu links) will result in the page being displayed.</p>
			<p>Uncheck the box to take the page offline so that visitors cannot access the page.</p>
			<input type="button" class="submit" onclick="hidePopup('pageLiveHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getInternalJavascript($page){
	$db = dbLink::getInstance();
	
	
	$fields = $page->getFields();
	
	//////////////////////////////
	// Can't have objects cus db_field is abstract
	$where = "SELECT fieId FROM field WHERE fiePagId=".$page->getId();
	$fieldRecords = $db->performQuery($where);
	
	$fieldIds = array();
	foreach($fieldRecords as $fieldRecord){
		$fieldIds[] = $fieldRecord['fieId'];
	}
	$fieldIds = implode(",", $fieldIds);
	
	$js = "var fieIds = [".$fieldIds."];";

	return $js;
}

function deletePage($page){
	$intName = stripslashes($page->getInternalName());
	$extName = stripslashes($page->getExternalName());

	$page->delete();
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<p>
			The page has been deleted.
		</p>
		<p>
			Title: {$extName}<br/>
			Internal Name: {$intName}
		</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/pages' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/pages' title='Pages'>pages</a>", "delete"));
	$p->setTitle("page deleted");
	$p->printPage();
}

?>