<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_cabinate":
			saveCabinate();
			break;
		default:
			printEditForm();
	}
	
//////////////////////////////////////////////////////////////////////////
function saveCabinate(){
	if(!isset($_GET['cabTitle']) || trim($_GET['cabTitle'])==""){
		printEditForm("error - title is required");
		return false;
	}
	
	if($_GET['cab_id']>0)
		$cab = new db_cabinate($_GET['cab_id']);
	else
		$cab = new db_cabinate();
		
	$cab->setTitle(trim($_GET['cabTitle']));
	$cab->setLimit((isset($_GET['cabLimit']) && $_GET['cabLimit']!="") ? $_GET['cabLimit'] : 0 );
	$cab->save();
	printEditForm("group saved");
}

function printEditForm($msg = "&nbsp;"){
	///////////////////////////////
	// editing or adding
	if(isset($_GET['cab_id'])){
		$modeText = "Edit";
		$cab = new db_cabinate($_GET['cab_id']);
	}
	else{
		$modeText = "Add";
		$cab = new db_cabinate();
	}
	
	///////////////////////////////
	// default values
	if(isset($_GET['cabTitle']))
		$cabTitle = $_GET['cabTitle'];
	else
		$cabTitle = stripslashes($cab->getTitle());
	if(isset($_GET['cabLimit']))
		$cabLimit = $_GET['cabLimit'];
	else
		$cabLimit = $cab->getLimit();

	
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<form class="generic" action="/{$adminPath}/files/edit_cabinate/save_cabinate" method="get">
			<fieldset>
				<legend>{$modeText} file group</legend>
				<input type="hidden" name="cab_id" value="{$cab->getId()}" />
				
				<label for="cabTitle">Title</label>
				<input type="text" class="text" name="cabTitle" id="cabTitle" value="{$cabTitle}" />
HTML;
				if($_SESSION['ADMIN_USER']->getSuperAdmin()){
					$html .= '<label for="cabLimit" class="superAdmin">Limit</label>';
					$html .= '<input type="text" class="text" name="cabLimit" id="cabLimit" value="'.$cabLimit.'" />';
				}
				
				$html .= <<<HTML
				<label for="saveBt">Create file group</label>
				<input type="submit" class="submit" value="save" />
			</fieldset>
		</form>
		<p class='alert'>{$msg}</p>	
		<p>
			<a href='/{$adminPath}/files' title='back'>&lt; back</a>
		</p>
	</div>
HTML;
	
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle($modeText." group");
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/files' title='files'>files</a>", $modeText." group"));
	$p->printPage();
}

?>