<?php
	if(!isset($_GET['gal_id'])){
		$adError = adError::getInstance();
		$adError->addUserError("No gallery specified");
		$adError->printErrorPage();
	}
	
	$gal = new db_gallery($_GET['gal_id']);
	if($gal->getId()<1){
		$adError = adError::getInstance();
		$adError->addUserError("Invalid gallery specified");
		$adError->printErrorPage();
	}
	
	printGalleryImages($gal);
	

//////////////////////////////////////////////////////////////////////////

function printGalleryImages($gal){
	
	//////////////////////////////////////
	// Initialise variables, including the size of image we want
	$dbImages = $gal->getImages();
	$imagesPerRow = 5;
	$dim = new db_dimension();
	$dim->setWidth(DIMENSION_ADMIN_WIDTH);
	$dim->setHeight(DIMENSION_ADMIN_HEIGHT);
	$image = new image($dim);
	
	//////////////////////////////////////
	// Build table of images. If there are no images this html will be replaced with a message.
	$html = "<form action='#' method='get' id='imageForm' class='images'>";
		$html .= "<fieldset>";
			$html .= "<p>There are ".count($dbImages)." images in this gallery. To edit image details click on it. Use the form on the <a href='/".ADMIN_PATH."/images' title='gallery page'>gallery page</a> to add images.</a><br/><b>With selected:</b> ";
				$html .= "<input type='button' onclick='submitImageForm(\"/".ADMIN_PATH."/images/move_images\")' value='change gallery' class='button' />";
				$html .= " <input type='button' onclick='submitImageForm(\"/".ADMIN_PATH."/images/delete_images\")' value='delete' class='button' />";
			$html .= "</p>";
			
			$html .= "<table>";
			$i=1;
			foreach($dbImages as $dbImage){
				$image->setDbImage($dbImage);
			
				if($i==1)
					$html .= "\n<tr>";
				
				$html .= "\n<td>";
					$html .= "\n<div>";
						$html .= "<a href='/".ADMIN_PATH."/images/edit_image?imgId=".$dbImage->getId()."' title='edit this image'>";
							$html .= "<img src='".$image->getSrc()."' alt='".$dbImage->getName()."' />";
						$html .= "</a>";
					$html .= "</div>";
					$html .= "<input type='checkbox' name='imgs[]' id='img".$dbImage->getId()."' value='".$dbImage->getId()."' /> ";
					$html .= "<label for='img".$dbImage->getId()."'>".stripslashes($dbImage->getName())."</label>";
				$html .= "\n</td>";
				
				if($i==$imagesPerRow){
					$i = 0;
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
		$html = "<p>This gallery is empty. Use the form on the <a href='/".ADMIN_PATH."/images' title='gallery page'>gallery page</a> to add images.</a></p>";
	}
	
	$html = "<div class='fullWidth'>".$html."</div>";
	$p = adPage::getInstance();
	$p->addContent($html);
	$p->setTitle("view gallery");
	$p->setBreadcrumb(array('<a href="/'.ADMIN_PATH.'/images" title="images">images</a>', stripslashes($gal->getTitle())));
	$p->addCss("adCss/images/view_gallery-screen.css");
	$p->addJavascript("adJavascripts/images/view_gallery.js");
	$p->printPage();
}

?>