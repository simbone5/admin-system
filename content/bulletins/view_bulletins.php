<?php
	if(!isset($_GET['boa_id']) || $_GET['boa_id']<1){
		$adError = adError::getInstance();
		$adError->addUserError("No board specified");
		$adError->printErrorPage();
	}
	
	$board = new db_board($_GET['boa_id']);
	if($board->getId()<1){
		$adError = adError::getInstance();
		$adError->addUserError("Invalid board specified");
		$adError->printErrorPage();
	}
	
	switch($GLOBALS['NAV_PATH']['mode']){
		default:
			printBulletinList($board);
	}

//////////////////////////////////////////////////////////////////////////


function printBulletinList($board, $msg = "&nbsp;"){
	$bulletinList = getBulletinList($board);
	$addBulletinLink = $board->getIsFull() ? "Note: Cannot add new bulletins as the board is full" : "<a href='/".ADMIN_PATH."/bulletins/edit_bulletin?boaId={$board->getId()}' title='add bulletin'><img src='adImages/plus.png' alt='add bulletin'/> Add bulletin</a>";
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Below is a list of bulletins.
		</p>
		<p>{$addBulletinLink}</p>
		{$bulletinList}
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("bulletins");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/bulletins/' title='Boards'>boards</a>", stripslashes($board->getTitle())));
	$p->printPage();
}

function getBulletinList($board){
	$dblink = dbLink::getInstance();
	$buls = $board->getBulletins();
	
	if(count($buls)<1)
		return "";
		
	$i = 0;
	$now = date("U");
	$html = "<table class='generic'>";
	$html .= "<tr><th style='width: 200px'>Title</th><th style='width: 110px'>Date</th><th style='width: 110px'>Visible from</th><th style='width: 110px'>Visible to</th><th style='width: 110px'>State</th><th>Options</th></tr>";
	foreach($buls as $bul){
		$html .= "<tr class='row".($i%2)."'>";
			$html .= "<td>";
				$html .= "<a href='/".ADMIN_PATH."/bulletins/edit_bulletin?bul_id=".$bul->getId()."' title='Edit ".htmlentities(stripslashes($bul->getTitle()), ENT_QUOTES)."'>".stripslashes($bul->getTitle())."</a>";
			$html .= "</td>";
			$html .= "<td>";
				$html .= date(DATE_FORMAT_DATE, $bul->getDate());
			$html .= "</td>";
			$html .= "<td>";
				$html .= date(DATE_FORMAT_DATE, $bul->getVisibleFrom());
			$html .= "</td>";
			$html .= "<td>";			
				$html .= date(DATE_FORMAT_DATE, $bul->getVisibleTo());
			$html .= "</td>";
			$html .= "<td>";
				if($bul->getVisibleFrom()>=$now)
					$html .= "future";
				elseif($bul->getVisibleFrom()<$now &&$bul->getVisibleTo()>$now)
					$html .= "visible";
				else
					$html .= "past";
			$html .= "</td>";
			$html .= "<td class='last'>";
				$html .= "<a href='/".ADMIN_PATH."/bulletins/edit_bulletin?bul_id=".$bul->getId()."' title='Edit ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>edit</a>";
				$html .= " | ";
				$html .= "<a href='/".ADMIN_PATH."/bulletins/delete_bulletin?bul_id=".$bul->getId()."' title='Delete ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>delete</a>";
			$html .= "</td>";
		$html .= "</tr>";
		$i++;
	}
	$html .= "</table>";
	
	return $html;
}
?>