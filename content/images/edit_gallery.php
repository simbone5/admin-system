<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_gallery":
			saveGallery();
			break;
		default:
			printEditForm();
	}
	
//////////////////////////////////////////////////////////////////////////
function saveGallery(){
	if(!isset($_GET['galTitle']) || trim($_GET['galTitle'])==""){
		printEditForm("error - title is required");
		return false;
	}
	
	if($_GET['gal_id']>0)
		$gal = new db_gallery($_GET['gal_id']);
	else
		$gal = new db_gallery();
	$gal->setTitle(trim($_GET['galTitle']));
	$gal->setLimit((isset($_GET['galLimit']) && $_GET['galLimit']!="") ? $_GET['galLimit'] : 0 );
	$gal->setTypId($_GET['galTypId']);
	$gal->save();
	printEditForm("gallery saved");
}

function printEditForm($msg = "&nbsp;"){
	///////////////////////////////
	// editing or adding
	if(isset($_GET['gal_id'])){
		$modeText = "Edit";
		$gal = new db_gallery($_GET['gal_id']);
	}
	else{
		$modeText = "Add";
		$gal = new db_gallery();
	}
	
	///////////////////////////////
	// default values
	if(isset($_GET['galTitle']))
		$galTitle = $_GET['galTitle'];
	else
		$galTitle = stripslashes($gal->getTitle());
	if(isset($_GET['galLimit']))
		$galLimit = $_GET['galLimit'];
	else
		$galLimit = $gal->getLimit();
	if(isset($_GET['galTypId']))
		$galTypId = $_GET['galTypId'];
	else
		$galTypId = $gal->getTypId();
	
	
	$adminPath = ADMIN_PATH;
	$galTypeCombo = getGalleryTypesCombo($galTypId);
	$html = <<<HTML
	<div class='halfWidth left'>
		<form class="generic" action="/{$adminPath}/images/edit_gallery/save_gallery" method="get">
			<fieldset>
				<legend>{$modeText} gallery</legend>
				<input type="hidden" name="gal_id" value="{$gal->getId()}" />
				
				<label for="galTitle">Title</label>
				<input type="text" class="text" name="galTitle" id="galTitle" value="{$galTitle}" />
				
				<label for="galTypId">Type</label>
				{$galTypeCombo}
HTML;
				if($_SESSION['ADMIN_USER']->getSuperAdmin()){
					$html .= '<label for="galLimit" class="superAdmin">Limit</label>';
					$html .= '<input type="text" class="text" name="galLimit" id="galLimit" value="'.$galLimit.'" />';
				}
				
				$html .= <<<HTML
				<label for="saveBt">Create gallery</label>
				<input type="submit" class="submit" value="save" />
			</fieldset>
		</form>
		<p class='alert'>{$msg}</p>	
		<p>
			<a href='/{$adminPath}/images' title='back'>&lt; back</a>
		</p>
	</div>
HTML;
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle($modeText." gallery");
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/images' title='images'>images</a>", $modeText." gallery"));
	$p->printPage();
}

function getGalleryTypesCombo($galTypId){
	$dblink = dbLink::getInstance();
	$types = $dblink->getObjects("type");
	
	if(count($types)<1)
		return "";
	
	$html = "<select name='galTypId' id='galTypId'>";
	foreach($types as $type){
		$selected = $galTypId==$type->getId() ? "selected='selected'" : "";
		$html .= "<option value='".$type->getId()."' ".$selected.">";
			$html .= stripslashes($type->getTitle());
		$html .= "</option>";
	}
	$html .= "</select>";
	
	return $html;
}
?>