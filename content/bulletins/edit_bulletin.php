<?php
	if(isset($_REQUEST['bul_id']) && $_REQUEST['bul_id']>0)
		$bul = new db_bulletin($_REQUEST['bul_id']);
	else
		$bul = new db_bulletin();
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save":
			saveBulletin($bul);
			break;
		default:
			printBulletinForm($bul);
	}

//////////////////////////////////////////////////////////////////////////
function saveBulletin($bul){
	if(!isset($_POST['title']) || trim($_POST['title'])==""){
		printBulletinForm($bul, "error - title is required");
		return false;
	}
	
	if(getDateFromCombo($_POST['visibleFrom'])>getDateFromCombo($_POST['visibleTo'])){
		printBulletinForm($bul, "error - 'visible from' must be a date that is before 'visible to'");
		return false;
	}
	#printARray($_POST);
	$bul->setTitle($_POST['title']);
	$bul->setDescription(nl2br($_POST['description']));
	$bul->setContent(nl2br($_POST['content']));
	$bul->setBoaId((int)$_POST['boaId']);
	$bul->setDate(getDateFromCombo($_POST['date']));
	$bul->setVisibleFrom(getDateFromCombo($_POST['visibleFrom']));
	$bul->setVisibleTo(getDateFromCombo($_POST['visibleTo']));
	$bul->setImaId(NULL);
	$bul->setFilId(NULL);
	$bul->save();
	
	printBulletinForm($bul, "bulletin saved");
}

function printBulletinForm($bul, $msg = "&nbsp;"){
	$mode = $bul->getId()>0 ? "Edit" : "Add";
	$board = isset($_POST['boaId']) ? new db_board($_POST['boaId']) : new db_board($bul->getBoaId());
	$date_now = date("U");
	$date_selectFromYear = date("Y", $date_now) - 5;
	$date_selectToYear = date("Y", $date_now) + 5;
	
	////////////////////////////
	// Set defaults
	if(isset($_POST['title'])){
		$title = $_POST['title'];
		$description = $_POST['description'];
		$content = $_POST['content'];
		$dateSelect = getDateCombo("date", "date", $date_selectFromYear, $date_selectToYear, $_POST['date']);
		$visibleFromSelect = getDateCombo("visibleFrom", "visibleFrom", $date_selectFromYear, $date_selectToYear, $_POST['visibleFrom']);
		$visibleToSelect = getDateCombo("visibleTo", "visibleTo", $date_selectFromYear, $date_selectToYear, $_POST['visibleTo']);
		$boardCombo = getBoardCombo($_POST['boaId']);
		$selectedDatesHtml = "";
	}
	else{
		$title = stripslashes($bul->getTitle());
		$description = br2nl(stripslashes($bul->getDescription()));
		$content = br2nl(stripslashes($bul->getContent()));
		$selectedDates = $bul->getBulletinDatesEpochs();
		$selectedDatesHtml = "";#getSelectedDatesHtml($selectedDates);
		$dateSelect = getDateCombo("date", "date", $date_selectFromYear, $date_selectToYear, $bul->getDate());
		$visibleFromSelect = getDateCombo("visibleFrom", "visibleFrom", $date_selectFromYear, $date_selectToYear, $bul->getVisibleFrom());
		$visibleToSelect = getDateCombo("visibleTo", "visibleTo", $date_selectFromYear, $date_selectToYear, $bul->getVisibleTo());
		$boardCombo = getBoardCombo($bul->getBoaId());
	}
	
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='halfWidth left'>
		<form action="/{$adminPath}/bulletins/edit_bulletin/save" method="post" class="generic">
			<fieldset>
				<legend>{$mode} Bulletin</legend>
				<input type="hidden" name="bul_id" value="{$bul->getId()}" />
				
				<label for="title">Title</label>
				<input type="text" class="text" name="title" id="title" value="{$title}" />
				
				<label for="description" class="textarea tinymce">Description</label>
				<textarea id="description" class="tinymce" name="description" cols="25" rows="5">{$description}</textarea>
				
				<label for="content" class="textarea tinymce">Content</label>
				<textarea id="content" class="tinymce" name="content" cols="25" rows="5">{$content}</textarea>
				
				<!-- label for="date" class='dateList'>Dates</label>
				<div class='dateListWrapper'>
					<div class='dateList' id='date_list'>
						<a href='#' title='select dates' id='date_link'>select</a>
						{$selectedDatesHtml}
					</div>
				</div --> 
				
				<label for="date">Date</label>
				{$dateSelect}
				
				<label for="visibleFrom">Visible from (<a href="javascript:showPopup('bulletinVisibleDateHelp')" title="help - bulletin visible dates">?</a>)</label>
				{$visibleFromSelect}
				
				<label for="visibleTo">Visible to (<a href="javascript:showPopup('bulletinVisibleDateHelp')" title="help - bulletin visible dates">?</a>)</label>
				{$visibleToSelect}
				
				<label for="boaId">Bulletin type</label>
				{$boardCombo}
				
				<label for="save">Save bulletin</label>
				<input type="submit" class="submit" id="save" value="save" />
			</fieldset>
		</form>
		<p class='alert'>{$msg}</p>
		<p>
			<a href='/{$adminPath}/bulletins/view_bulletins?boa_id={$board->getId()}' title='back'>&lt; back</a>
		</p>
	</div>

HTML;
	/*$datePickerConfig = "\n\n$(function(){\n";
	foreach($selectedDates as $epoch)
		$datePickerConfig .= "$('#date_link').dpSetSelected('".date("d/m/Y", (int)$epoch)."');\n";
		$datePickerConfig .= <<<ADD_EVENT
		$('.selectedDate').click(function(event){
			var selDate = $(this).html().split("/");
			$('#date_link').dpSetDisplayedMonth(selDate[1]-1, selDate[2]);
			$('#date_link').dpDisplay();
			event.preventDefault();
		});
ADD_EVENT;
	$datePickerConfig .= "});\n";
	*/
	$p = adPage::getInstance();
	$p->setTitle($mode." bulletin");
	$p->addContent($html);
	$p->setTinyMce(TRUE);
	$p->setJQuery(TRUE);
	#$p->addJavascript("adJavascripts/bulletins/edit_bulletin.js");
	#$p->setInternalJavascript($p->getInternalJavascript().$datePickerConfig);
	$p->addPopup("bulletinVisibleDateHelp", getBulletinVisibleDateHelpPopupHTML());
	$p->addCss("adCss/bulletins/edit_bulletin.css");
	$p->setBreadcrumb(array("<a href='/".ADMIN_PATH."/bulletins/' title='Boards'>boards</a>", "<a href='/".ADMIN_PATH."/bulletins/view_bulletins?boa_id=".$board->getId()."' title='View ".htmlentities(stripslashes($board->getTitle()), ENT_QUOTES)."'>".stripslashes($board->getTitle())."</a>", $mode." bulletin"));
	$p->printPage();
}

function getSelectedDatesHtml($epochs){
	$parts = array();
	foreach($epochs as $epoch){
		$parts[] = "<a href='#' class='selectedDate' title='view'>".date(DATE_FORMAT_DATE, $epoch)."<br/></a>";
	}
	return implode("\n", $parts);
}

function getBulletinVisibleDateHelpPopupHTML(){
	$html = <<<HTML
		<div class='help'>
			<h1>Help - Bulletin Visible Dates</h1>
			<p>These fields may not be used on your website, but if they are then they determine when the article can be seen on your site. The article will only be displayed during the dates selected. A good use of this is for news articles, where old news automatically becomes hidden after its 'visible to' date.</p>
			<input type="button" class="submit" onclick="hidePopup('bulletinVisibleDateHelp')" value="close" />
		</div>
HTML;
	return $html;
}

function getBoardCombo($curBoaId){
	$dblink = dbLink::getInstance();
	$boards = $dblink->getObjects("board");
	
	$options = array();
	foreach($boards as $board){
		$selected = $curBoaId==$board->getId() ? "selected='selected'" : "";
		$options[] = "<option value='".$board->getId()."' ".$selected.">".stripslashes($board->getTitle())."</option>";
	}
	
	$html = "<select name='boaId' id='boaId'>";
	$html .= implode("\n", $options);
	$html .= "</select>";
	
	return $html;
}
?>