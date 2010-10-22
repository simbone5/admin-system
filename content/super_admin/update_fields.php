<?php
	checkSuperAdmin();
	switch($GLOBALS['NAV_PATH']['mode']){
		case "update":
			updateFields();
			break;
		default:
			printTemplateList();
	}
	
///////////////////////////////////////////////////////////////////////
function updateFields(){
	$dbLink = dbLink::getInstance();
	
	if(!isset($_GET['temId']) || $_GET['temId']<1){
		$adError = adError::getInstance();
		$adError->addUserError("No template specified");
		return false;
	}
	
	$temp = new db_template($_GET['temId']);
	if($temp->getId()<1){
		$adError = adError::getInstance();
		$adError->addUserError("Invalid template id specified [".$_GET['temId']."]");
		return false;
	}
	
	$pages = $dbLink->getObjects("page", "pagTemId=?", "i", array($_GET['temId']));
	foreach($pages as $page)
		$page->updateFields();
}

function printTemplateList(){
	$html = "<div class='halfWidth'>";
		$html .= "<p>This is a list of templates on the site. Only those in use can have their fields updated.</p>";
			
		$templates = getTemplates();
		$templatesInUse = getTemplatesInUse();
		if(count($templates)>0){
			$html .= "<ul class='bulleted'>";
				foreach($templates as $tempId => $template){
					$name = $template->getFilename().' ['.$tempId.']';
					$html .= "<li>";
						if(isset($templatesInUse[$tempId]))
							$html .= '<a href="/'.ADMIN_PATH.'/super_admin/update_fields/update?temId='.$tempId.'" title="update '.$template->getFilename().' fields">'.$name.'</a>';
						else
							$html .= $name;
					$html .= "</li>";
				}
			$html .= "</ul>";
		}
	$html .= "</div>";
	

	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/super_admin' title='Super Admin'>super admin</a>", "update fields"));
	$p->printPage();
}

function getTemplates(){
	$db = dbLink::getInstance();
	return  $db->getObjects("template");
}

function getTemplatesInUse(){
	$db = dbLink::getInstance();
	return  $db->getObjects("template", "temID=pagTemID", null, null, null, array("page"));
}

?>