<?php
class field_image extends db_field{

	private $image;
	
	protected function getContentRead(){
		$imgSrc = "";
		$html = "";
		if($this->getContent()!=""){
			$imgSrc = $this->getImage()->getSrc();
			$html = "<img src='".$imgSrc."' id='fieContent_".$this->getId()."_image' alt='".$this->getImageAlt()."' title='".$this->getImageAlt()."' />";
		}
		
		return $html;
	}	
	
	public function getContentEdit(){
		$imgSrc = "";
		if($this->getContent()!=""){
			$imgSrc = $this->getImage()->getSrc();
		}
		
		$gal = $this->getGallery();
		
		$ajaxUrl = "/".ADMIN_PATH."/pages/ajax_select_image?gal_id=".$gal->getId()."&amp;width=".$this->getWidth()."&amp;height=".$this->getHeight();
		$editBt = "<a style='position:absolute;bottom:0px;right:0px;display:block;background:#ffffff;color:#000000;width:40px;' href='javascript:window.parent.selectEditImage(\"fieContent_".$this->getId()."\", \"".$ajaxUrl."\")' title='update image'>edit</a>";
		$html = "<div style='position:relative;'>";
			$html .= $editBt;
			$html .= "<img src='".$imgSrc."' id='fieContent_".$this->getId()."_image' alt='".$this->getImageAlt()."' />";
		$html .= "</div>";
		$html .= "<input type='hidden' name='fields[field_".$this->getId()."][fieContent]' id='fieContent_".$this->getId()."' value='".stripslashes($this->getContent())."'/>";
		
		
		return $html;
	}	
	
	protected function getContentExample(){
		return "Example content";
	}	
	
	protected function getContentPreview(){
		return "Preview mode";
	}
	
	protected function getType(){
		return FIELD_TYPE_IMAGE;
	}
		
	private function getImage(){
		if(!isset($this->image) || $this->image==""){
			$parts = explode("|", $this->getContent());
			$imgId = isset($parts[0]) ? $parts[0] : 0;
			if($imgId>0)
				$dbImage = new db_image($imgId);
			else
				$dbImage = new db_image();
			
			$dim = new db_dimension();
			$dim->setWidth($this->getWidth());
			$dim->setHeight($this->getHeight());
			$img = new image($dim);
			$img->setDbImage($dbImage);
			$this->image = $img;
		}
		
		return $this->image;
	}
	
	private function getImageAlt(){
		$parts = explode("|", $this->getContent());
		$alt = isset($parts[1]) ? $parts[1] : "";
		return $alt;
	}
	
	private function getGalleryTitle(){
		$params = $this->getParams();
		return isset($params[0]) ? $params[0] : "undefined";
	}
	
	private function getGallery(){
		$gal = new db_gallery();
		$gal->openFromTitle($this->getGalleryTitle());
		return $gal;
	}
	
	private function getWidth(){
		$params = $this->getParams();
		return isset($params[1]) ? $params[1] : 0;
	}
	
	private function getHeight(){
		$params = $this->getParams();
		return isset($params[2]) ? $params[2] : 0;
	}
}

?>