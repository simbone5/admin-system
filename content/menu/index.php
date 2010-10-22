<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_structure":
			saveStructure();
			break;
		case "save_menu_item":
			saveMenuItem();
			break;
		case "delete_menu_item":
			deleteMenuItem();
			break;
		default:
			printManageMenu();
	}
	
///////////////////////////////////////////////////////////////////////
function printManageMenu($msg = ""){
	$htmlTree = getManageMenuTree();
	$htmlForm = getMenuForm($msg);
	
	$maxLevelNotice = "";
	if(MENU_MAX_DEPTH>0)
		$maxLevelNotice = "<p>Moving some items will be restricted because you are limited to only ".MENU_MAX_DEPTH." levels.</p>";
	
	$adminPath = ADMIN_PATH;
	$html = <<<CONTENT
	<div class='fullWidth'>
		<p>
			You can use the left/up/down/right buttons (<img src='adImages/arrows/left.png' alt='left' /> <img src='adImages/arrows/up.png' alt='up' /> <img src='adImages/arrows/down.png' alt='down' /> <img src='adImages/arrows/right.png' alt='right' />) to restructure your menus. 
			This heirarchy represents how your menus are displayed on your website.
		</p>
		{$maxLevelNotice}
	</div>
	<div class="halfWidth left">
		<a href="javascript:showPopup('newMenuItemSelectParent')" title="add item"><img src="adImages/plus.png" alt="add menu"/> Add menu</a>
		{$htmlTree}
	</div>
	<div class='halfWidth right'>
		{$htmlForm}
	</div>
CONTENT;
	
	$p = adPage::getInstance();
	$p->setTitle("menu");
	$p->setBreadcrumb(array("menu"));
	$p->addJavascript("adJavascripts/tree.js");
	$p->addJavascript("adJavascripts/menu/index.js");
	$p->setInternalJavascript("var maxLevels = ".MENU_MAX_DEPTH);
	$p->addOnload("cleanUp()");
	$p->addCss("adCss/tree-screen.css");
	$p->addCss("adCss/menu/index-screen.css");
	$p->addPopup("newMenuItemSelectParent", getNewMenuSelectParentPopup());
	$p->addPopup("menuWebsiteHelp", getMenuWebsiteHelpPopupHTML());
	$p->addPopup("confirmDeletePopup", getDeleteMenuItemPopupHTML());
	printEditMenuPopup(new menuItem());//<-adds a popup to dbpage
	$p->addContent($html);
	$p->printPage();
}


function saveMenuItem(){
	$p = adPage::getInstance();
	$valueIndex = $_GET['type']."Value";
	
	if(!isset($_GET['title']) || $_GET['title']==""){
		$p->addOnload('showPopup("editMenuItem'.$_GET['menuId'].'")');
		////////////////////////////
		// Cheeky global error msg for editMenuPopups
		$GLOBALS["MENU_ITEM_".$_GET['menuId']."_MSG"] = "error - title required";
		printManageMenu();
		return false;
	}
	
	if(MENU_MAX_STRING_LENGTH>0 && strlen($_GET['title'])>MENU_MAX_STRING_LENGTH){
		$p->addOnload('showPopup("editMenuItem'.$_GET['menuId'].'")');
		////////////////////////////
		// Cheeky global error msg for editMenuPopups
		$GLOBALS["MENU_ITEM_".$_GET['menuId']."_MSG"] = "error - title restricted to ".MENU_MAX_STRING_LENGTH." characters";
		printManageMenu();
		return false;
	}
	
	if(!isset($_GET[$valueIndex]) || $_GET[$valueIndex]==""){
		$p->addOnload('showPopup("editMenuItem'.$_GET['menuId'].'")');
		////////////////////////////
		// Cheeky global error msg for editMenuPopups
		$GLOBALS["MENU_ITEM_".$_GET['menuId']."_MSG"] = "error - ".$_GET['type']." required";
		printManageMenu();
		return false;
	}
	
	$menuItem = new menuItem($_GET['menuId']);
	$menuItem->setTitle(stripslashes($_GET['title']));
	if(isset($_GET['url']) && $_GET['url']!="")
		$menuItem->setUrl(stripslashes($_GET['url']));
	else
		$menuItem->setUrl(stripslashes($_GET['title']));
		
	if(isset($_GET['parentMenuId'])){
		$menuItem->setParentId($_GET['parentMenuId']);
	}
	
	$menuItem->setType($_GET['type']);
	$menuItem->setValue(stripslashes($_GET[$valueIndex]));
	$menuItem->save();
	
	printManageMenu("changes saved");
}

function saveStructure(){
	$xml = stripslashes($_POST['structure']);
	$header = file_get_contents("adHelpers/xmlTemplates/menuXmlHeaderDeclaration.txt");
	//p("XML:<xmp>".$xml."</xmp>");
	
	$newDom = new DomDocument();
	
	if(!@$newDom->loadXML($header.$xml)){
		printManageMenu("error - could not save structure");
		return false;
	}
	$newDomformatOutput = true;
	$newDom->preserveWhiteSpace = false;
	$newItems = $newDom->getElementsByTagName("item");
	
	$oldDom = new DomDocument();
	$oldDom->load(MENU_XML_PATH);
	foreach($newItems as $newItem){
		$oldItem = $oldDom->getElementById($newItem->getAttribute("menuID"));
		
		//////////////////////////////////
		// Create a type attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$typeAttribute = $newDom->createAttribute("type");
		$typeAttributeValue = $newDom->createTextNode(strtolower($oldItem->getAttribute("type")));
		$typeAttribute->appendChild($typeAttributeValue);
		$newItem->appendChild($typeAttribute);
		
		//////////////////////////////////
		// Create a title attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$titleAttribute = $newDom->createAttribute("title");
		$titleAttributeValue = $newDom->createTextNode($oldItem->getAttribute("title"));
		$titleAttribute->appendChild($titleAttributeValue);
		$newItem->appendChild($titleAttribute);
		
		//////////////////////////////////
		// Create a url attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$urlAttribute = $newDom->createAttribute("url");
		$urlAttributeValue = $newDom->createTextNode($oldItem->getAttribute("url"));
		$urlAttribute->appendChild($urlAttributeValue);
		$newItem->appendChild($urlAttribute);
		
		//////////////////////////////////
		// Create a 'value' attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$valueAttribute = $newDom->createAttribute("value");
		$valueAttributeValue = $newDom->createTextNode($oldItem->getAttribute("value"));
		$valueAttribute->appendChild($valueAttributeValue);
		$newItem->appendChild($valueAttribute);
		
	}	
	
	//p("<xmp>".$newDom->saveXML()."</xmp>");
	$newDom->save(MENU_XML_PATH);
	
	printManageMenu("structure saved");
}

function getMenuForm($msg){
	$adminPath = ADMIN_PATH;
	$html = <<<FORM
		<form class='generic' action='/{$adminPath}/menu/index/save_structure' method='post' onsubmit='return saveStructure()'>
			<fieldset>
				<legend>Options</legend>
				<label for='undoButton'>Undo your last change</label>
				<input type='button' class='button disabled' onclick='restoreBackup()' value='undo' id='undoButton' disabled='disabled' />
				
				<textarea name='structure' id='structure'  rows='20' cols='20' style='display:none'></textarea>
				
				<label for='save'>Save this structure</label>
				<input type='submit' class='submit'  value='save' id='save' />
			</fieldset>
		</form>
		<p class='alert' id='alert'>{$msg}</p>
FORM;
	return $html;
}

function getManageMenuTree(){
	$dom = new DomDocument();
	$dom->load(MENU_XML_PATH);
	$html = getMenuItems($dom->documentElement, 1, "getManageMenuButtons");
	return $html;
}

function getMenuItems($parentNode, $level=1, $buttonFunction){
	//////////////////////////////////
	// RECURSIVE FUNCTION
	// Loops through the passed node's children, printing the item tags as <li>
	
	$nodes = $parentNode->childNodes;
	$html = "";
	if($nodes->length>0){
		//////////////////////////////////
		// If level = 0 then we're at the top, so print class and id attributes
		if($level==1)
			$html = "\n<ul class='tree' id='treeRoot'>";
		else
			$html = "\n<ul>";
			
		//////////////////////////////////
		// Loop through nodes. Ignore non-elements
		foreach($nodes as $node){
			if($node->nodeType!=XML_ELEMENT_NODE)
				continue;
			$menuItem = new menuItem();
			$menuItem->openFromNode($node);
			
			$html .= $buttonFunction($menuItem, $level);
			
		}
		$html .= "\n</ul>\n";
	}
	return $html;
}

function getManageMenuButtons($menuItem, $level){
	$idAttribute = "menuID_".$menuItem->getId();
	$html = "";
	
	//////////////////////////////////
	// determine what the menu item links to
	if($menuItem->getType()=="page"){
		if($menuItem->getValue()>0){
			$page = new db_page($menuItem->getValue());
			if($page->getId()>0)
				$linksTo = htmlentities(stripslashes($page->getInternalName()), ENT_QUOTES);
			else{
				$linksTo = "<span class='alert'>assigned to non-existant page</span>";
				$menuItem->setValue(""); //<- unset value so it isn't passed through edit form
			}
		}
		else
			$linksTo = "<span class='alert'>unassigned</span>";
			
			
	}
	else{
		$linksTo = $menuItem->getValue();
	}
	
	//////////////////////////////////
	// Print <li>, the menu name and buttons
	$html .= "\n\t<li id='".$idAttribute."'>";
		$html .= "<span><a href='javascript:showPopup(\"editMenuItem".$menuItem->getId()."\")' title='edit ".$menuItem->getTitle()."'>".$menuItem->getTitle()."</a> - ".$linksTo."</span>";
		$html .= getTreeButtons($idAttribute);
		$html .= getMenuItems($menuItem->getNode(), $level+1, "getManageMenuButtons");
		printEditMenuPopup($menuItem);
	$html .= "</li>";
	return $html;
}

function getNewMenuSelectParentMenuButtons($menuItem, $level){
	if(MENU_MAX_DEPTH>0 && $level>=MENU_MAX_DEPTH)
		return "";
	$idAttribute = "menuID_parent_".$menuItem->getId();
	$html = "";
	
	//////////////////////////////////
	// Print <li>, the menu name
	$html .= "\n\t<li>";
		$html .= '<a id="'.$idAttribute.'" href="javascript:selectParentMenu('.$menuItem->getId().')" title="select '.$menuItem->getTitle().'">'.$menuItem->getTitle().'</a>';
		$html .= getMenuItems($menuItem->getNode(), $level+1, "getNewMenuSelectParentMenuButtons");
	$html .= "</li>";
	return $html;
}

function printEditMenuPopup($menuItem){
	$dbLink = dbLink::getInstance();
	$itemId = $menuItem->getId();

	$adminPath = ADMIN_PATH;
	$legend = $menuItem->getId()>0 ? "Edit menu" : "Add menu";
	/////////////////////////////
	// Basic details
	$html = <<<FORM_PART_1
		<form action="/{$adminPath}/menu/index/save_menu_item" class="generic" method="get">
			<fieldset>
				<legend>{$legend}</legend>
				<input type="hidden" value="{$menuItem->getId()}" name="menuId" />
				
				<label for="title{$itemId}">Title</label>
				<input type="text" class="text" value="{$menuItem->getTitle()}" name="title" id="title{$itemId}" />
FORM_PART_1;


	/////////////////////////////
	// Only show URL option if enabled
	if(MENU_ADVANCED_URL){
		$html .= '<label for="url'.$itemId.'">URL</label>';
		$html .= '<input type="text" class="text" value="'.$menuItem->getUrl().'" name="url" id="url'.$itemId.'" />';
	}
	
	/////////////////////////////
	// What type of thing the menu item links to
	$html .= '<label for="type'.$itemId.'">What the menu item links to</label>';
	$html .= '<select name="type" id="type'.$itemId.'" onchange="changeMenuItemType(\''.$itemId.'\', this.value)" >';
	foreach(array("page" => "a page", "url" => "a website") as $value=>$option)
		$html .= '<option value="'.$value.'" '.($menuItem->getType()==$value ? 'selected="selected"' : '').'>'.$option.'</option>';
	$html .= '</select>';
	
	/////////////////////////////
	// Menu links to: a page
	$style = $menuItem->getType()!="page" && $menuItem->getType()!="" ? 'style="display: none;"' : '';
	$html .= '<label id="pageValueLabel'.$itemId.'" '.$style.'>Pages</label>';
	$pageDom = new DomDocument();
	$pageDom->load(PAGE_XML_PATH);
	$html .= '<input type="hidden" id="hiddenPageValue'.$itemId.'" value="'.($menuItem->getType()=="page" ? $menuItem->getValue() : '').'" name="pageValue" />';
	$html .= '<div class="treeWrapper" '.$style.' id="pageValue'.$itemId.'">';
		$html .= getPageItems($pageDom->documentElement, 1, $menuItem);
	$html .= "</div>";
	
	/////////////////////////////
	// Menu links to: a website		
	$style = $menuItem->getType()!="url" ? 'style="display: none;"' : '';
	$html .= '<label for="urlValue'.$itemId.'" id="urlValueLabel'.$itemId.'" '.$style.'>Website address (<a href="javascript:showPopup(\'menuWebsiteHelp\')" title="help - menu link to website">?</a>)</label>';
	$html .= '<input type="text" '.$style.' class="text" value="'.($menuItem->getType()=="url" ? $menuItem->getValue() : '').'" name="urlValue" id="urlValue'.$itemId.'" />';
	
	
	/////////////////////////////
	// If the form is for a new menu item then output hidden input for parent menu item id
	if($menuItem->getId()<1)
		$html .= '<input type="hidden" name="parentMenuId" id="parentMenuId" value="'.(isset($_GET['parentMenuId']) ? $_GET['parentMenuId'] : '').'"/>';
	
	/////////////////////////////
	// End of form with submit buttons		
	$html .= <<<FORM_PART_2
				<input type="submit" class="submit first" value="save" />
				<input type="button" class="submit" onclick="hidePopup('editMenuItem{$menuItem->getId()}')" value="cancel" />
			</fieldset>
		</form>
FORM_PART_2;
	$html .= '<p class="alert">'.(isset($GLOBALS["MENU_ITEM_".$menuItem->getId()."_MSG"]) ? $GLOBALS["MENU_ITEM_".$menuItem->getId()."_MSG"] : '').'&nbsp;</p>';
	
	
	if($menuItem->getId()>0){
		$html .= <<<EXTRA
			<div class="extraOptions">
				<label for="deleteBt{$itemId}">Delete this menu item</label>
				<input type="button" class="submit" onclick="selectDeleteMenuItem({$menuItem->getId()})" value="delete" id="deleteBt{$itemId}" />
			</div>
EXTRA;
	}
	$p = adPage::getInstance();
	$p->addPopup("editMenuItem".$itemId, $html);
}

function getPageItems($parentNode, $level, $menuItem){
	//////////////////////////////////
	// RECURSIVE FUNCTION
	// Loops through the passed node's children, printing the item tags as <li>
	
	$nodes = $parentNode->childNodes;
	$html = "";
	if($nodes->length>0){
		if($level==1){
			$html .= "\n<ul class='tree'>";
		}
		else
			$html = "\n<ul>";
			
		//////////////////////////////////
		// Loop through nodes. Ignore non-elements
		foreach($nodes as $node){
			if($node->nodeType!=XML_ELEMENT_NODE)
				continue;
			$idAttribute = $node->getAttribute("pagID");
			$page = new db_page($node->getAttribute("pagID"));
			$pagName = htmlentities($page->getInternalName(), ENT_QUOTES);
			$anchorId = "menuItem".$menuItem->getId()."_linkToPage".$page->getId();
			
			if($page->getHidden() || !$page->getLive())
				continue;
			
			//////////////////////////////////
			// Print <li>, the page name
			$html .= "\n\t<li>";
				$html .= '<a href="javascript:selectLinkToPage(\''.$menuItem->getId().'\', '.$page->getId().');" id="'.$anchorId.'" '.($menuItem->getValue()==$page->getId() ? 'class="selected"' : '').' title="select '.$pagName.'">'.$pagName.'</a>';
				$html .= getPageItems($node, $level+1, $menuItem);
			$html .= "</li>";
		}
		$html .= "\n</ul>\n";
	}
	return $html;
}

function getTreeButtons($liId){
	$adminPath = ADMIN_PATH;
	$html = <<<BUTTONS
		<a href='javascript:move(left, "{$liId}", true)' class='first img' id='{$liId}_left' title='left'><img src='adImages/arrows/left.png' alt='left'/></a>
		<a href='javascript:move(up, "{$liId}")' title='up' class='img' id='{$liId}_up'><img src='adImages/arrows/up.png' alt='up'/></a>
		<a href='javascript:move(down, "{$liId}")' title='down' class='img' id='{$liId}_down'><img src='adImages/arrows/down.png' alt='down'/></a>
		<a href='javascript:move(right, "{$liId}", true)' title='right' class='img' id='{$liId}_right'><img src='adImages/arrows/right.png' alt='right'/></a>
BUTTONS;
	return $html;
}

function getNewMenuSelectParentPopup(){
	$dom = new DomDocument();
	$dom->load(MENU_XML_PATH);
	
	$html = '<p>Select the item you wish to be the parent of the new menu item.</p>';
	$html .= '<a href="javascript:selectParentMenu(0);" id="menuID_parent_0" title="Add top level menu">top level</a>';
	$html .= getMenuItems($dom->documentElement, 1, "getNewMenuSelectParentMenuButtons");
	$html .= '<input type="button" class="submit first" value="ok" onclick="submitSelectedParentMenu()" />';
	$html .= '<input type="button" class="submit" value="cancel" onclick="hidePopup(\'newMenuItemSelectParent\')" />';
	
	return $html;
}

function getMenuWebsiteHelpPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Menu link to website</h1>
			<p>Instead of the menu taking a user to a page within your site, this allows you to send the user to a different website.</p>
			<p>You will need to include any prefix (e.g. http:// or https://) in the address you provide.</p>
			<input type="button" class="submit" onclick="hidePopup('menuWebsiteHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getDeleteMenuItemPopupHTML(){
	$adminPath = ADMIN_PATH;
	$html = <<<FORM
		<form action="/{$adminPath}/menu/index/delete_menu_item" class="generic" method="get">
			<fieldset>
				<legend>Delete menu item</legend>
				<p>Please confirm that you wish to remove this item from the menu.</p>
				<p class="alert">Note: This will remove the item and any sub-items that appear beneath it</p>
				<input type="hidden" id="deleteMenuItemId" name="menuItemId" value="--set by js function: selectDeleteMenuItem()--" />
				<input type="submit" class="submit first" value="delete" />
				<input type="button" class="submit" onclick="hidePopup('confirmDeletePopup')" value="cancel" />
			</fieldset>
		</form>
FORM;
	return $html;

}

function deleteMenuItem(){
	if(!isset($_GET['menuItemId'])){
		printManageMenu("error - cannot delete menu item with no id");
		return false;
	}
	
	$menuItem = new menuItem($_GET['menuItemId']);
	if($menuItem->getId()>0){
		$menuItem->delete();
		$msg = "item deleted";
	}
	else
		$msg = "error - could not delete item. Possibly already deleted";

	printManageMenu($msg);
}
?>