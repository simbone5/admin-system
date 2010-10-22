<?php
	
	
	switch($GLOBALS['NAV_PATH']['mode']){
		case "save_image":
			saveUploadedImage("printGalleryList", "printGalleryList");
			break;
		default:
			printGalleryList();
	}

//////////////////////////////////////////////////////////////////////////


function printGalleryList($msg = "&nbsp;"){
	$galleryList = getGalleryList();
	$adminPath = ADMIN_PATH;
	$uploadForm = getUploadImageForm("/".ADMIN_PATH."/images/index/save_image", null, $msg);
	$html = <<<HTML
	<div class='fullWidth'>
		<p>
			Below are galleries of images. To upload an image complete the form on the right.
		</p>
	</div>
	<div class='halfWidth left'>
		<a href='/{$adminPath}/images/edit_gallery' title='add gallery'><img src='adImages/plus.png' alt='add gallery'/> Add gallery</a>
		<br/><br/>
		{$galleryList}
	</div>
	<div class='halfWidth right'>
		{$uploadForm}
	</div>

HTML;
	
	$p = adPage::getInstance();
	$p->setTitle("images");
	$p->addContent($html);
	$p->setBreadcrumb(array("images"));
	$p->printPage();
}

function getGalleryList(){
	$dblink = dbLink::getInstance();
	$gals = $dblink->getObjects("gallery");
	
	if(count($gals)<1)
		return "";
	
	$i = 0;
	$html = "<table class='generic'>";
	$html .= "<tr><th style='width: 190px'>Title</th><th style='width: 90px'>Num images</th><th>Options</th></tr>";
	foreach($gals as $gal){
		$html .= "<tr class='row".($i%2)."'>";
			$html .= "<td>";
				$html .= "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='View ".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>".stripslashes($gal->getTitle())."</a>";
			$html .= "</td>";
			$html .= "<td class='last'>";
				$html .= count($gal->getImages());
				if($gal->getLimit()>0)
					$html .= " (".$gal->getLimit()." max)";
				else
					$html .= " (no limit)";
			$html .= "</td>";
			$html .= "<td  class='last'>";
				$html .= "<a href='/".ADMIN_PATH."/images/view_gallery?gal_id=".$gal->getId()."' title='View ".htmlentities(stripslashes($gal->getTitle()), ENT_QUOTES)."'>images</a>";
				$html .= " | ";
				$html .= "<a href='/".ADMIN_PATH."/images/edit_gallery?gal_id=".$gal->getId()."' title='edit gallery'>edit</a>";
				$html .= " | ";
				$html .= "<a href='/".ADMIN_PATH."/images/delete_gallery?gal_id=".$gal->getId()."' title='delete gallery'>delete</a>";
			$html .= "</td>";
		$html .= "</tr>";
		$i++;
	}
	$html .= "</table>";
	
	return $html;
}
?>