<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		default:
			printBoardList();
	}

//////////////////////////////////////////////////////////////////////////


function printBoardList($msg = "&nbsp;"){
	$boardList = getBoardList();
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Below is a list of bulletin types. Select a type to edit/add new bulletins.
		</p>
	</div>
	<div class='fullWidth'>
		{$boardList}
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("boards");
	$p->addContent($html);
	$p->setBreadcrumb(array("boards"));
	$p->printPage();
}

function getBoardList(){
	$dblink = dbLink::getInstance();
	$boards = $dblink->getObjects("board");
	
	if(count($boards)<1)
		return "";
	
	$i = 0;
	$html = "<table class='generic'>";
	$html .= "<tr><th style='width: 200px'>Title</th><th style='width: 400px'>Latest bulletin</th><th>Options</th></tr>";
	foreach($boards as $board){
		$html .= "<tr class='row".($i%2)."'>";
			$html .= "<td>";
				$html .= "<a href='/".ADMIN_PATH."/bulletins/view_bulletins?boa_id=".$board->getId()."' title='View ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>".stripslashes($board->getTitle())."</a>";
			$html .= "</td>";
			$html .= "<td>";
				$latestBul = $board->getLatestBulletin();
				if($latestBul){
					$html .= date(DATE_FORMAT_DATE, $latestBul->getDate());
					$html .= " - ".stripslashes($latestBul->getTitle());
				}
			$html .= "</td>";
			$html .= "<td class='last'>";
				$html .= "<a href='/".ADMIN_PATH."/bulletins/view_bulletins?boa_id=".$board->getId()."' title='View ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>bulletins</a>";
			$html .= "</td>";
		$html .= "</tr>";
		$i++;
	}
	$html .= "</table>";
	
	return $html;
}
?>