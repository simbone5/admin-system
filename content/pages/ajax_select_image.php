<?php
	$p = adPage::getInstance();
	$p->setAjaxPage(TRUE);
	
	if(!isset($_GET['gal_id']) || $_GET['gal_id']<0){
		$adError = adError::getInstance();
		$adError->addUserError("no gallery specified");
		$adError->printErrorPage();
	}
	
	$gal = new db_gallery($_GET['gal_id']);
	
	if($gal->getId()<1){
		$adError = adError::getInstance();
		$adError->addUserError("invalid gallery specified");
		$adError->printErrorPage();
	}
	
	$dbImages = $gal->getImages();
	$imagesPerRow = 5;
	$origSizeDim = new db_dimension();
	$origSizeDim->setWidth(DIMENSION_ADMIN_WIDTH);
	$origSizeDim->setHeight(DIMENSION_ADMIN_HEIGHT);
	$requestedDim = new db_dimension();
	$requestedDim->setWidth($_GET['width']);
	$requestedDim->setHeight($_GET['height']);
	$image = new image(array($origSizeDim, $requestedDim));
	
	$html = "<form action='#' method='get' id='imageForm' class='images'>";
		$html .= "<fieldset>";
			$html .= "<p>There are ".count($dbImages)." images in this gallery. Click on your chosen image to select it.</p>";
			$html .= "<p>To upload new images save any changes you have made here and then use the upload form on the gallery page.</p>";
			
			$html .= "<table>";
			$i=1;
			foreach($dbImages as $dbImage){
				$image->setDbImage($dbImage);
			
				if($i==1)
					$html .= "\n<tr>";
				
				$html .= "\n<td>";
					$html .= "\n<div>";
						$html .= "<a href='javascript:promptForImageAlt(\"".$image->getSrc(1)."\", ".$dbImage->getId().", \"".htmlspecialchars(stripslashes($dbImage->getName()), ENT_QUOTES)."\")' title='select this image'>";
							$html .= "<img src='".$image->getSrc(0)."' alt='".htmlspecialchars(stripslashes($dbImage->getName()), ENT_QUOTES)."' />";
						$html .= "</a>";
					$html .= "</div>";
					$html .= "<label>".stripslashes($dbImage->getName())."</label>";
				$html .= "\n</td>";
				
				if($i==$imagesPerRow){
					$i = 1;
					$html .= "\n</tr>\n";
				}
				$i++;
			}
			if($i!=1){
				for($i;$i<=$imagesPerRow;$i++){
					$html .= "<td></td>";
				}
			}
			$html .= "</tr>";
			$html .= "</table>";
		$html .= "</fieldset>";
	$html .= "</form>";
	
	if(count($dbImages)<1){
		$html = "<p>This gallery (".stripslashes($gal->getTitle()).") is empty. Use the form on the <a href='/".ADMIN_PATH."/images' title='gallery page'>gallery page</a> to add images.</a></p>";
	}
	
	$p->addContent($html);
	$p->printPage();
?>