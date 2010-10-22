<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_structure":
			saveStructure();
			break;
		default:
			printManagePages();
	}
	
///////////////////////////////////////////////////////////////////////
function saveStructure(){

	$xml = stripslashes($_POST['structure']);
	$header = file_get_contents("adHelpers/xmlTemplates/pagesXmlHeaderDeclaration.txt");
	//p("XML:<xmp>".$xml."</xmp>");
	
	$dom = new DomDocument();
	
	if(!@$dom->loadXML($header.$xml)){
		printManagePages("error - could not save structure");
		return false;
	}
	$dom->formatOutput = true;
	$dom->preserveWhiteSpace = false;
	$items = $dom->getElementsByTagName("item");
	
	foreach($items as $item){
		$pag = new db_page($item->getAttribute("pagID"));
		
		//////////////////////////////////
		// Create a pagName attribute, create a textNode for the attribute's value
		// Append them to the $item
		$pagNameAttribute = $dom->createAttribute("pagName");
		$pagNameAttributeValue = $dom->createTextNode($pag->getInternalName());
		$pagNameAttribute->appendChild($pagNameAttributeValue);
		$item->appendChild($pagNameAttribute);
		
		//////////////////////////////////
		// Create a hidden attribute, create a textNode for the attribute's value
		// Append them to the $item
		$hiddenAttribute = $dom->createAttribute("hidden");
		$hiddenAttributeValue = $dom->createTextNode($pag->getHidden() ? "true" : "false");
		$hiddenAttribute->appendChild($hiddenAttributeValue);
		$item->appendChild($hiddenAttribute);
	}	
		
	
	$hiddenPages = $dblink->getObjects("page", "pagCanManage=FALSE", "", array());
	foreach($hiddenPages as $hiddenPage){
		$item = $dom->documentElement->appendChild($dom->createElement("item"));
	   
		//////////////////////////////////
		// Create a pagName attribute, create a textNode for the attribute's value
		$item->setAttribute("pagID", $hiddenPage->getId());
	   
		//////////////////////////////////
		// Create a pagName attribute, create a textNode for the attribute's value
		// Append them to the $item
		$pagNameAttribute = $dom->createAttribute("pagName");
		$pagNameAttributeValue = $dom->createTextNode($hiddenPage->getInternalName());
		$pagNameAttribute->appendChild($pagNameAttributeValue);
		$item->appendChild($pagNameAttribute);
	   
		//////////////////////////////////
		// Create a hidden attribute, create a textNode for the attribute's value
		// Append them to the $item
		$hiddenAttribute = $dom->createAttribute("hidden");
		$hiddenAttributeValue = $dom->createTextNode($hiddenPage->getHidden() ? "true" : "false");
		$hiddenAttribute->appendChild($hiddenAttributeValue);
		$item->appendChild($hiddenAttribute);
		
	}
	
	//p("<xmp>".$dom->saveXML()."</xmp>");
	$dom->save(PAGE_XML_PATH);
	
	printManagePages("structure saved");
}


function printManagePages($msg = ""){
	$htmlTree = printPageTree();
	$htmlForm = printPageForm($msg);
	
	$maxLevelNotice = "";
	if(PAGE_MAX_DEPTH>0)
		$maxLevelNotice = "<p>Moving some pages will be restricted because you are limited to only ".PAGE_MAX_DEPTH." levels.</p>";
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			You can use the left/up/down/right buttons (<img src='adImages/arrows/left.png' alt='left' /> <img src='adImages/arrows/up.png' alt='up' /> <img src='adImages/arrows/down.png' alt='down' /> <img src='adImages/arrows/right.png' alt='right' />) to move pages around the structure. 
			This heirarchy simply allows you to group pages, and does not reflect the layout of your menus.
		</p>
		{$maxLevelNotice}
	</div>
	<div class='halfWidth left'>
		<a href='/{$adminPath}/pages/add' title='add page'><img src='adImages/plus.png' alt='add page'/> Add page</a>
		{$htmlTree}
	</div>
	<div class='halfWidth right'>
		{$htmlForm}
	</div>
HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("pages");
	$p->setBreadcrumb(array("pages"));
	$p->addJavascript("adJavascripts/tree.js");
	$p->addJavascript("adJavascripts/pages/index.js");
	$p->setInternalJavascript("var maxLevels = ".PAGE_MAX_DEPTH);
	$p->addOnload("cleanUp()");
	$p->addCss("adCss/tree-screen.css");
	$p->addContent($html);
	$p->printPage();
}

function printPageForm($msg){
	$adminPath = ADMIN_PATH;
	$html = <<<FORM
		<form class='generic' action='/{$adminPath}/pages/index/save_structure' method='post' onsubmit='return saveStructure()'>
			<fieldset>
				<legend>Options</legend>
				<label for='undoButton'>Undo your last change</label>
				<input type='button' class='button disabled' onclick='restoreBackup()' value='undo' id='undoButton' disabled='disabled' />
				
				<textarea name='structure' id='structure' style='display:none;' cols='20' rows='20'></textarea>
				
				<label for='save'>Save this structure</label>
				<input type='submit' class='submit'  value='save' id='save' />
			</fieldset>
		</form>
		<p class='alert' id='alert'>{$msg}</p>
FORM;
	return $html;
}

function printPageTree(){
	$p = adPage::getInstance();
	$dom = new DomDocument();
	$dom->load(PAGE_XML_PATH);
	$html = printItems($dom->documentElement);
	return $html;
}

function printItems($parentNode, $level=1){
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
			$idAttribute = "pagID_".$node->getAttribute("pagID");
			$page = new db_page($node->getAttribute("pagID"));
			
			if($page->getHidden() || !$page->getCanManage())
				continue;
			
			//////////////////////////////////
			// Print <li>, the page name and buttons
			$html .= "\n\t<li id='".$idAttribute."'>";
				if($page->getId()>0){
					$pagName = stripslashes($page->getInternalName());
					if($page->getEditable() || $_SESSION['ADMIN_USER']->getSuperAdmin())
						$html .= "<span><a href='/".ADMIN_PATH."/pages/edit?pag_id=".$node->getAttribute("pagID")."' title='edit ".addslashes($pagName)."'>".$pagName."</a>".($page->getEditable() ? "" : " <em>uneditable</em>")."</span>";
					else
						$html .= "<span>".$pagName." <em>uneditable</em></span>";
					$html .= printTreeButtons($idAttribute, $level);
				}
				else{
					$html .= "<span>err - could not open page [".$node->getAttribute("pagID")."]</span>";
				}
				$html .= printItems($node, $level+1);
			$html .= "</li>";
		}
		$html .= "\n</ul>\n";
	}
	return $html;
}

function printTreeButtons($liId, $level){
	$adminPath = ADMIN_PATH;
	$html = <<<BUTTONS
		<a href='javascript:move(left, "{$liId}", true)' class='first img' id='{$liId}_left' title='left'><img src='adImages/arrows/left.png' alt='left'/></a>
		<a href='javascript:move(up, "{$liId}")' title='up' class='img' id='{$liId}_up'><img src='adImages/arrows/up.png' alt='up'/></a>
		<a href='javascript:move(down, "{$liId}")' title='down' class='img' id='{$liId}_down'><img src='adImages/arrows/down.png' alt='down'/></a>
		<a href='javascript:move(right, "{$liId}", true)' title='right' class='img' id='{$liId}_right'><img src='adImages/arrows/right.png' alt='right'/></a>
BUTTONS;
	return $html;
}
?>