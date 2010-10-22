<?php
class field_multiline extends db_field{

	protected function getContentRead(){
		return htmlspecialchars(stripslashes($this->getContent()));
	}	
	
	public function getContentEdit(){
		$html = "\n<textarea style='width: 100%;height: 200px' rows='20' cols='20' name='fields[field_".$this->getId()."][fieContent]' id='fieContent_".$this->getId()."'>".stripslashes($this->getContent())."</textarea>\n";
		return $html;
	}	
	
	protected function getContentExample(){
		return "Example content";
	}	
	
	protected function getContentPreview(){
		return "Preview mode";
	}
	
	protected function getType(){
		return FIELD_TYPE_MULTILINE;
	}
}

?>