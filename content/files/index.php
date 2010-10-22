<?php
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_file":
			saveUploadedFile("printCabinateList", "printCabinateList");
			break;
		default:
			printCabinateList();
	}

//////////////////////////////////////////////////////////////////////////


function printCabinateList($msg = "&nbsp;"){
	$cabinateList = getCabinateList();
	$adminPath = ADMIN_PATH;
	$uploadForm = getUploadFileForm("/".ADMIN_PATH."/files/index/save_file", new db_file(), $msg);
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Below are groups of files. To upload a file complete the form on the right.
		</p>
	</div>
	<div class='halfWidth left'>
		<a href='/{$adminPath}/files/edit_cabinate' title='add group'><img src='adImages/plus.png' alt='add group'/> Add group</a>
		<br/><br/>
		{$cabinateList}
	</div>
	<div class='halfWidth right'>
		{$uploadForm}
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("files");
	$p->addContent($html);
	$p->setBreadcrumb(array("files"));
	$p->printPage();
}

function getCabinateList(){
	$dblink = dbLink::getInstance();
	$cabs = $dblink->getObjects("cabinate");
	
	if(count($cabs)<1)
		return "";
	
	$i = 0;
	$html = "<table class='generic'>";
	$html .= "<tr><th style='width: 190px'>Title</th><th style='width: 90px'>Num files</th><th>Options</th></tr>";
	foreach($cabs as $cab){
		$html .= "<tr class='row".($i%2)."'>";
			$html .= "<td>";
				$html .= "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='View ".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>".stripslashes($cab->getTitle())."</a>";
			$html .= "</td>";
			$html .= "<td class='last'>";
				$html .= count($cab->getFiles());
				if($cab->getLimit()>0)
					$html .= " (".$cab->getLimit()." max)";
				else
					$html .= " (no limit)";
			$html .= "</td>";
			$html .= "<td  class='last'>";
				$html .= "<a href='/".ADMIN_PATH."/files/view_cabinate?cab_id=".$cab->getId()."' title='View ".htmlentities(stripslashes($cab->getTitle()), ENT_QUOTES)."'>files</a>";
				$html .= " | ";
				$html .= "<a href='/".ADMIN_PATH."/files/edit_cabinate?cab_id=".$cab->getId()."' title='edit group'>edit</a>";
				$html .= " | ";
				$html .= "<a href='/".ADMIN_PATH."/files/delete_cabinate?cab_id=".$cab->getId()."' title='delete gallery'>delete</a>";
			$html .= "</td>";
		$html .= "</tr>";
		$i++;
	}
	$html .= "</table>";
	
	return $html;
}
?>