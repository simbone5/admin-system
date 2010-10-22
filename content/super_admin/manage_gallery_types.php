<?php
	checkSuperAdmin();
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_type":
			saveType();
			printTypesList();
			break;
		case "save_dimension":
			saveDimension($_GET['dimWidth'], $_GET['dimHeight'], $_GET['dimQuality'], $_GET['dimTypId']);
			printTypesList();
			break;
		default:
			printTypesList();
	}
	
///////////////////////////////////////////////////////////////////////
function saveType(){
	if($_GET['typTitle']=="")
		return false;
		
	$type = new db_type();
	
	$type->setTitle($_GET['typTitle']);
	$type->save();
	
	saveDimension(DIMENSION_ADMIN_WIDTH, DIMENSION_ADMIN_HEIGHT, DIMENSION_ADMIN_QUALITY, $type->getId());
}

function saveDimension($w, $h, $q, $typeId){
	if($w==0 && $h==0){
		return false;
	}
	$dim = new db_dimension();
	$dim->setHeight((int)$h);
	$dim->setWidth((int)$w);
	$dim->setQuality($q<10 ? 10 : ($q>100 ? 100 : $q));//<- force $q to between 10 ad 100
	$dim->setTypId($typeId);
	$dim->save();
}

function printTypesList(){
	$dblink = dbLink::getInstance();
	$types = $dblink->getObjects("type");
	$typesComboOptions = "";
	
	$html = "<div class='halfWidth left'>";
		if(count($types)>0){
			$html .= "<ul>";
			foreach($types as $type){
				$html .= "<li>";
					$html .= stripslashes($type->getTitle());
					$html .= "<ul class='bulleted'>";
						foreach($type->getDimensions() as $dim){
							$html .= "<li>";
								$html .= $dim->getWidth()."x".$dim->getHeight()." qual: ".$dim->getQuality();
							$html .= "</li>";
						}
					$html .= "</ul>";
				$html .= "</li>";
				$typesComboOptions .= '<option value="'.$type->getId().'">'.$type->getTitle().' ['.$type->getId().']</option>';
			}
			$html .= "</ul>";
		}
	$html .= "</div>";
	
	$adminPath = ADMIN_PATH;
	$html .= <<<FORM
		<div class="halfWidth right">
			<form action="/{$adminPath}/super_admin/manage_gallery_types/save_type" method="get" class="generic">
				<fieldset>
					<legend>New gallery type</legend>
					<label for="typTitle">Title</label>
					<input type="text" class="text" name="typTitle" id="typTitle" value="" />
					
					<label for="saveNewType">Save new type</label>
					<input type="submit" id="saveNewType" class="submit" value="save">
				</fieldset>
			</form>
			
			<br/><br/>
			<form action="/{$adminPath}/super_admin/manage_gallery_types/save_dimension" method="get" class="generic">
				<fieldset>
					<legend>New dimension</legend>
					<label for="dimWidth">Width</label>
					<input type="text" class="text" name="dimWidth" id="dimWidth" value="" />
					
					<label for="dimHeight">Height</label>
					<input type="text" class="text" name="dimHeight" id="dimHeight" value="" />
					
					<label for="dimQuality">Quality</label>
					<input type="text" class="text" name="dimQuality" id="dimQuality" value="" />
					
					<label for="dimTypId">Type</label>
					<select name="dimTypId" id="dimTypId">
						{$typesComboOptions}
					</select>
					
					<label for="saveNewDim">Save new dimension</label>
					<input type="submit" id="saveNewDim" class="submit" value="save">
				</fieldset>
			</form>
		</div>
FORM;
	
	$p = adPage::getInstance();
	$p->setTitle("manage gallery types");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/super_admin' title='super admin'>super admin</a>", "manage gallery types"));
	$p->printPage();
}

?>