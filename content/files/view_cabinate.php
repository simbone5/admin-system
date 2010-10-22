<?php
	if(!isset($_GET['cab_id'])){
		$adError = adError::getInstance();
		$adError->addUserError("No file group specified");
		$adError->printErrorPage();
	}
	
	$cab = new db_cabinate($_GET['cab_id']);
	if($cab->getId()<1){
		$adError = adError::getInstance();
		$adError->addUserError("Invalid file group specified");
		$adError->printErrorPage();
	}
	
	printCabinateFiles($cab);
	

//////////////////////////////////////////////////////////////////////////

function printCabinateFiles($cab){
	
	//////////////////////////////////////
	// Initialise variables
	$dbFiles = $cab->getFiles();
	$filesPerRow = 5;
	
	//////////////////////////////////////
	// Build table of files. If there are no files this html will be replaced with a message.
	$html = "<form action='#' method='get' id='fileForm' class='files'>";
		$html .= "<fieldset>";
			$html .= "<p>There are ".count($dbFiles)." files in this file group. To edit file details click on its title, or click on the filename to download it. Use the form on the <a href='/".ADMIN_PATH."/files' title='file group page'>file group page</a> to add files.</a><br/><b>With selected:</b> ";
				$html .= "<input type='button' onclick='submitFileForm(\"/".ADMIN_PATH."/files/move_files\")' value='change group' class='button' />";
				$html .= " <input type='button' onclick='submitFileForm(\"/".ADMIN_PATH."/files/delete_files\")' value='delete' class='button' />";
			$html .= "</p>";
			
			$i = 0;	
			$html .= "<table class='generic'>";
			$html .= "<tr><th style='width: 200px'>Title</th><th style='width: 200px'>Filename</th><th style='width: 105px'>Date uploaded</th><th>Select</th></tr>";
			foreach($dbFiles as $dbFile){
	
				$html .= "\n<tr class='row".($i%2)."'>";
				
					$html .= "\n<td>";
						$html .= "<a href='/".ADMIN_PATH."/files/edit_file?filId=".$dbFile->getId()."' title='edit this file'>";
							$html .= stripslashes($dbFile->getName());
						$html .= "</a>";
					$html .= "\n</td>";
					$html .= "\n<td>";
						$html .= "<a href='/download-file/".$dbFile->getId()."' title='download ".htmlentities(stripslashes($dbFile->getUploadedFileName()), ENT_QUOTES)."'>";
							$html .= stripslashes($dbFile->getUploadedFileName());
						$html .= "</a>";
					$html .= "\n</td>";
					$html .= "\n<td>";
						$html .= date(DATE_FORMAT_DATE, $dbFile->getDateUploaded());
					$html .= "\n</td>";
					$html .= "\n<td>";
						$html .= "<input type='checkbox' name='fils[]' id='fil".$dbFile->getId()."' value='".$dbFile->getId()."' /> ";
					$html .= "\n</td>";
				

				$html .= "\n</tr>\n";
				
				$i++;
			}
			$html .= "</tr>";
			$html .= "</table>";
		$html .= "</fieldset>";
	$html .= "</form>";
	
	if(count($dbFiles)<1){
		$html = "<p>This file group is empty. Use the form on the <a href='/".ADMIN_PATH."/files' title='Files'>file group page</a> to add files.</a></p>";
	}
	
	$html = "<div class='fullWidth'>".$html."</div>";
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle("view files");
	$p->setBreadcrumb(array('<a href="/'.ADMIN_PATH.'/files" title="files">files</a>', stripslashes($cab->getTitle())));
	$p->addCss("adCss/files/view_cabinate-screen.css");
	$p->addJavascript("adJavascripts/files/view_cabinate.js");
	$p->printPage();
}

?>