<?php
	checkSuperAdmin();
	if(isset($_GET['filename']))
		saveTemplate();
	
	printTemplateList();
	
///////////////////////////////////////////////////////////////////////
function saveTemplate(){
	$temp = new db_template();
	$temp->setName($_GET['name']);
	$temp->setFilename($_GET['filename']);
	$temp->setHidden(false);
	$temp->save();
}

function printTemplateList(){
	$html = "<div class='halfWidth'>";
		$html .= "<p>Drop a template into the templates directory and refresh this page.</p>";
			
		$templateFiles = getTemplateFiles();
		$templateRecords = getTemplateRecords();
		if(count($templateRecords)>0){
			$html .= "<ul class='bulleted'>";
				foreach($templateRecords as $tempFilename => $tempId){
					$html .= "<li>".$tempFilename." [".$tempId."]</li>";
				}
			$html .= "</ul>";
		}
	$html .= "</div>";
	
	$html .= "<div class='halfWidth'>";
		$html .= "<form class='generic' method='get' action='/".ADMIN_PATH."/super_admin/add_templates'>";
			$html .= "<fieldset>";
				$html .= "<legend>Add template</legend>";
				
				$html .= "<label for='name'>Name</label>";
				$html .= "<input type='text' class='text' name='name' id='name' />";
				
				$html .= "<label for='filename'>Unregistered templates</label>";
				$html .= "<select name='filename' id='filename'>";
					foreach($templateFiles as $file){
						if(!isset($templateRecords[$file]))
							$html .= "<option value='".$file."'>".$file."</option>";
					}
				$html .= "</select>";
				
				$html .= "<label for='save'>Save this template</label>";
				$html .= "<input type='submit' class='submit' value='save' id='save' />";
			$html .= "</fieldset>";
		$html .= "</form>";
	$html .= "</div>";
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/super_admin' title='Super Admin'>super admin</a>", "add templates"));
	$p->printPage();
}

function getTemplateRecords(){
	$db = dbLink::getInstance();
	$tempRecords = $db->getObjects("template");
	$temps = array();
	foreach($tempRecords as $temp){
		$temps[stripslashes($temp->getFilename())] = $temp->getId();
	}
	return $temps;
}

function getTemplateFiles(){
	$files = array();
	$templateDir = opendir("../".SITE_FOLDER."/templates");
	while($subfile = readdir($templateDir)){
		$subfile = $subfile;
		if($subfile!='.' && $subfile!='..'){
			$files[$subfile] = $subfile;
		}
	}
	return $files;
}
?>