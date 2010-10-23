<?php
class field_cabinate extends db_field{

	private $cabinate;
	
	
	protected function getContentRead(){
		$cabId = -1;
		if($this->getContent()!=""){
			$cabId = $this->getContent();
		}
		
		return $cabId;
	}	
	
	public function getContentEdit(){
		$cabinateName = "<i>unset</i>";
		if($this->getContent()!="" && $this->getContent()>0){
			$cabinateName = $this->getCabinate()->getTitle();
		}
		
		
		$html = $cabinateName;
		$html .= $this->getCabinatesCombo();
		
		
		return $html;
	}	
	
	protected function getContentExample(){
		return -1;
	}	
	
	protected function getContentPreview(){
		return -1;
	}
	
	protected function getType(){
		return FIELD_TYPE_CABINATE;
	}
		
	private function getCabinate(){
		if(!isset($this->cabinate) || $this->cabinate==""){
			$parts = explode("|", $this->getContent());
			$cabId = isset($parts[0]) ? $parts[0] : 0; //currently there is only one part
			if($cabId>0)
				$this->cabinate = new db_cabinate($cabId);
			else
				$this->cabinate = new db_cabinate();
		}
		
		return $this->cabinate;
	}
	
	private function getCabinatesCombo(){
		$dblink = dblink::getinstance();
		$cabs = $dblink->getObjects("cabinate");
		$options = "<option value='-1'>none</option>\n";
		foreach($cabs as $cabId => $cab){
			$selected = "";
			if($this->getContent()==$cabId)
				$selected = "selected='selected'";
			$options .= "<option value='".$cabId."' ".$selected.">".stripslashes($cab->getTitle())."</option>\n";
		}
		
		$selectHtml = <<<CONTENT
			<select name='fields[field_{$this->getId()}][fieContent]' id='fieContent_{$this->getId()}'>
				{$options}
			</select>
CONTENT;
		
		return $selectHtml;
	}
}

?>