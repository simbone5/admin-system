<?php
class field_single extends db_field{

	protected function getContentRead(){
		return htmlspecialchars(stripslashes($this->getContent()));
	}	
	
	public function getContentEdit(){
		$html = "\n<input type='text' style='width: 95%;padding: 0px' name='fields[field_".$this->getId()."][fieContent]' id='fieContent_".$this->getId()."' value='".htmlspecialchars($this->getContent(), ENT_QUOTES)."' />\n";
		return $html;
	}	
	
	protected function getContentExample(){
		return "Example content";
	}	
	
	protected function getContentPreview(){
		return "Preview mode";
	}
	
	protected function getType(){
		return FIELD_TYPE_SINGLE;
	}
}

?>