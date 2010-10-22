<?php
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "preview":
			printTemplatePreview();
			break;
		case "save":
			savePage();
			break;
		default:
			printAddPageForm();
	}
	
/////////////////////////////////////////////////////////////////
function savePage(){
	if(!validateForm())
		return false;
	
	
	$page = new db_page();
	$page->setTemId($_GET['templateId']);
	$page->setInternalName($_GET['name']);
	$page->setLive(TRUE);
	$page->setEditable(TRUE);
	$page->setDeletable(TRUE);
	$page->setHidden(FALSE);
	$page->setCanManage(TRUE);
	$page->setKeywords("");
	$page->setDescription("");
	$page->setParentId($_GET['parentPagId']);

	$page->save();
	
	$page->createFields();
	
	//////////////////////////////////
	// change mode so that we can load the edit page.
	// Add an onload to the page so that user is presented with page-details popup
	$GLOBALS['NAV_PATH']['mode'] = "edit";
	$p = adPage::getInstance();
	$p->addOnload('showPopup("editPageDetails")');
	require_once("edit.php");
}

function printTemplatePreview(){
	if(!validateForm())
		return false;
		
	$templateOptions = getTemplateOptions();
	$adminPath = ADMIN_PATH;
	$html = <<<FORM
		<div class='previewOptions'>
			<p>
				<b>Template preview</b>
			</p>
				
			<form class='generic' method='get' action='/{$adminPath}/pages/add/save'>
				<fieldset>
					<input type='hidden' name='name' value='{$_GET['name']}' />
					<input type='hidden' name='parentPagId' value='{$_GET['parentPagId']}' />
					
					<label for='templateId'>Change template:</label>
					<select name='templateId' id='templateId' onchange='document.getElementById("previewWrapper").src = "/ad_template_preview/"+document.getElementById("templateId").value'>
						{$templateOptions}
					</select>
					
					<input type='submit' class='submit' value='create' />
				</fieldset>
			</form>
			<input type='button' class='submit' id='cancel' onclick="window.location='/{$adminPath}/pages'" value='cancel' />
		</div>
FORM;
	$html .= '<iframe id="previewWrapper" src="/ad_template_preview/'.$_GET['templateId'].'" height="100%;"></iframe>';
			
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle("preview page");
	$p->addCss("adCss/iFramePreview.css");
	$p->addCss("adCss/pages/add-screen.css");
	$p->setStructured(FALSE);
	$p->printPage();
}

function validateForm(){
	if(!isset($_GET['name']) || $_GET['name']==""){
		printAddPageForm("error - name is required");
		return false;
	}
	//////////////////////////////////
	// the below should never be true as the default is set to -1
	if(!isset($_GET['parentPagId']) || $_GET['parentPagId']==""){
		printAddPageForm("error - parent page selection is required");
		return false;
	}
	
	return true;
}

function printAddPageForm($msg = ""){
	
	$newPageLocation = getNewPageLocation();
	$templateOptions = getTemplateOptions();
	$adminPath = ADMIN_PATH;
	$name = isset($_GET['name']) ? stripslashes($_GET['name']) : "";
	$html = <<<FORM
		<div class='fullWidth'>
			<p>Enter the name of the page and select a template from the list below. You will be offered a preview of the template before your new page is created.</p>
		</div>
		<div class='halfWidth left'>
			<form class='generic' action='/{$adminPath}/pages/add/preview' method='get'>
				<fieldset>
					<legend>Page details</legend>
					<label for='name'>Name</label>
					<input type='text' class='text' name='name' id='name' value='{$name}' />
					
					<label for='templateId'>Template</label>
					<select name='templateId' id='templateId'>
					{$templateOptions}
					</select>
					
					<div class='clear'>&nbsp;</div>
					
					{$newPageLocation}
					
					<div class='clear'>&nbsp;</div>
					<label for='preview'>Preview this template</label>
					<input type='submit' id='preview' value='preview' class='submit'/>
				</fieldset>
			</form>
			<p class='alert'>{$msg}</p>
		</div>
FORM;
	
	$p = adPage::getInstance();
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/pages' title='Pages'>pages</a>", "add"));
	$p->setTitle("add page");
	$p->addCss("adCss/tree-screen.css");
	$p->addCss("adCss/pages/add-screen.css");
	$p->addJavascript("adJavascripts/pages/add.js");
	$p->addContent($html);
	$p->printPage();
}

function getNewPageLocation(){
	$dom = new DomDocument();
	$dom->load(PAGE_XML_PATH);
	
	$htmlInput = "<input type='hidden' name='parentPagId' id='parentPagId' value='-1' />";
	if(PAGE_MAX_DEPTH==1){
		return $htmlInput;
	}
	
	$html = $htmlInput;
	$html .= "<p>Select a parent for this new page to be stored under:</p>";
	$html .= printItems($dom->documentElement);
	return $html;
}

function getTemplateOptions(){
	$db = dbLink::getInstance();
	$templateOptions = "";
	foreach($db->getObjects("template", "temHidden=FALSE") as $temp){
		$templateOptions .= "<option value='".$temp->getId()."' ".(isset($_GET['templateId']) && $_GET['templateId']==$temp->getId() ? "selected='selected'" : "").">".stripslashes($temp->getName())."</option>";
	}
	return $templateOptions;
}

function printItems($parentNode, $level=1){
	//////////////////////////////////
	// RECURSIVE FUNCTION
	// Loops through the passed node's children, printing the item tags as <li>
	
	$nodes = $parentNode->childNodes;
	$html = "";
	if($nodes->length>0){
		if($level==1){
			$html = "\n<div class='treeWrapper'>";
			$html .= "\n<a href='javascript:setParentPageId(-1)' id='pagID_-1' title='top'>top level</a>";
			$html .= "\n<ul class='tree' id='treeRoot'>";
		}
		else
			$html = "\n<ul>";
			
		//////////////////////////////////
		// Loop through nodes. Ignore non-elements
		foreach($nodes as $node){
			if($node->nodeType!=XML_ELEMENT_NODE)
				continue;
			$idAttribute = $node->getAttribute("pagID");
			$pagName = htmlentities($node->getAttribute("pagName"), ENT_QUOTES);
			$page = new db_page($node->getAttribute("pagID"));
			
			if($page->getHidden() || !$page->getCanManage())
				continue;
				
			//////////////////////////////////
			// Print <li>, the page name
			$html .= "\n\t<li>";
				if(PAGE_MAX_DEPTH>0 && $level<PAGE_MAX_DEPTH)
					$html .= "<a href='javascript:setParentPageId(".$idAttribute.")' id='pagID_".$idAttribute."' title='under ".$pagName."'>".$pagName."</a>";
				else
					$html .= "<span>".$pagName."</span>";
				$html .= printItems($node, $level+1);
			$html .= "</li>";
		}
		$html .= "\n</ul>\n";
		if($level==1)
			$html .= "\n</div>";
	}
	return $html;
}
?>