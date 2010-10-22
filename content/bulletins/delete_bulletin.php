<?php
	if(isset($_GET['bul_id']) && $_GET['bul_id']>0)
		$bul = new db_bulletin($_GET['bul_id']);
	else{
		$adError = adError::getInstance();
		$adError->addUserError("No bulletin specified");
		$adError->printErrorPage();
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "delete":
			deleteBulletin($bul);
			break;
		default:
			printConfirmDelete($bul);
	}

//////////////////////////////////////////////////////////////////////////
function deleteBulletin($bul){
	$title = stripslashes($bul->getTitle());
	$date = date(DATE_FORMAT_DATE, $bul->getDate());
	$board = new db_board($bul->getBoaId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<p>
			The bulletin has been deleted.
		</p>
		<p>
			Title: {$title}<br/>
			Date: {$date}
		</p>
		<p>
			<b>Deleted</b>
			<br/><br/>
			<a href='/{$adminPath}/bulletins/view_bulletins?boa_id={$board->getId()}' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$bul->delete();
	
	$p = adPage::getInstance();
	$p->setTitle("delete bulletin");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/bulletins/' title='Boards'>boards</a>", "<a href='/".ADMIN_PATH."/bulletins/view_bulletins?boa_id=".$board->getId()."' title='View ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>".stripslashes($board->getTitle())."</a>", "delete bulletin"));
	$p->printPage();
	
}

function printConfirmDelete($bul){
	////////////////////////////////////////////
	// Setup variables
	$title = stripslashes($bul->getTitle());
	$date = date(DATE_FORMAT_DATE, $bul->getDate());
	$board = new db_board($bul->getBoaId());
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<p>
			Please confirm you would like to delete this bulletin.
		</p>
		<p>
			Title: {$title}<br/>
			Date: {$date}
		</p>
		<p>
			<input type="button" class="button" value="delete" onclick="window.location='/{$adminPath}/bulletins/delete_bulletin/delete?bul_id={$bul->getId()}'" />
			<br/><br/>
			<a href='/{$adminPath}/bulletins/view_bulletins?boa_id={$bul->getId()}' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("delete bulletin");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/bulletins/' title='Boards'>boards</a>", "<a href='/".ADMIN_PATH."/bulletins/view_bulletins?boa_id=".$board->getId()."' title='View ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>".stripslashes($board->getTitle())."</a>", "delete bulletin"));
	$p->printPage();
}
?>