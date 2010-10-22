<?php
	checkSuperAdmin();
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_board":
			saveBoard();
			printBoardsList();
			break;
		default:
			printBoardsList();
	}
	
///////////////////////////////////////////////////////////////////////
function saveBoard(){
	if($_GET['boaTitle']=="")
		return false;
		
	$board = new db_board();
	
	$board->setTitle($_GET['boaTitle']);
	$board->setLimit($_GET['boaLimit']);
	$board->save();
	
}

function printBoardsList(){
	$dblink = dbLink::getInstance();
	$boards = $dblink->getObjects("board");
	
	$html = "<div class='halfWidth left'>";
		if(count($boards)>0){
			$html .= "<ul>";
			foreach($boards as $board){
				$html .= "<li>";
					$html .= stripslashes($board->getTitle());
					$html .= " (".$board->getLimit()." limit)";
				$html .= "</li>";
			}
			$html .= "</ul>";
		}
	$html .= "</div>";
	
	$adminPath = ADMIN_PATH;
	$html .= <<<FORM
		<div class="halfWidth right">
			<form action="/{$adminPath}/super_admin/manage_boards/save_board" method="get" class="generic">
				<fieldset>
					<legend>New board</legend>
					<label for="boaTitle">Title</label>
					<input type="text" class="text" name="boaTitle" id="boaTitle" value="" />
					
					<label for="boaLimit">Limit</label>
					<input type="text" class="text" name="boaLimit" id="boaLimit" value="0" />
					
					<label for="saveNewBoard">Save new board</label>
					<input type="submit" id="saveNewBoard" class="submit" value="save">
				</fieldset>
			</form>
		</div>
FORM;
	
	$p = adPage::getInstance();
	$p->setTitle("manage boards");
	$p->addContent($html);
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/super_admin' title='super admin'>super admin</a>", "manage boards"));
	$p->printPage();
}

?>