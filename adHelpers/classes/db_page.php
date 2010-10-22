<?php
class db_page extends _db_object{
	private $fields;
	private $node;
	private $dom;
	
	public function printPage(){
		////////////////////////////////
		// Inform the field class what page we're viewing.
		// this is so that when the template runs and the field objects are created they
		// load the correct page data
		db_field::setPagId($this->getId());
		
		////////////////////////////////
		// Load the template and print it!
		$template = new db_template($this->getTemId());
		if($template->getId()>0)
			$template->printTemplate();
		else
			printFrontEndError( "db_page::printPage error - template not loaded" );
	}
	
	public function save(){
		////////////////////////////////
		// Override _db_object save function so that
		// we can save it to xml, then call _db_object::save
		if($this->getId()>0)
			return $this->_updateRecord();
		else
			return $this->_insertRecord();
	}
	
	protected function _updateRecord(){
		$this->updateNode();
		parent::_updateRecord();
	}
	
	protected function _insertRecord(){
		$adError = adError::getInstance();
		$parId = $this->getParentId();
		
		if($parId==""){
			$adError->addClassError("db_page", "_insertRecord", "parentId not set");
			return false;
		}
			
		////////////////////////////////
		// Determine parent in xml
		$dom = $this->getDom();
		if($parId==-1)
			$parent = $dom->documentElement;
		else
			$parent = $dom->getElementById($this->getParentId());
		if(!$parent){
			$adError->addClassError("db_page", "_insertRecord", "parentId [".$this->getParentId()."] not found in xml");
			return false;
		}
		
		////////////////////////////////
		// Save new page so that it is assigned an id
		parent::_insertRecord();
		if($this->getId()<1){
			$adError->addClassError("db_page", "_insertRecord", "page not saved to db so cannot save to xml");//<-prob wont ever get here
			return false;
		}

		////////////////////////////////
		// Create new node in xml
		$this->setNode($parent->appendChild($dom->createElement("item")));
		$this->updateNode();
	}
	
	private function updateNode(){
		$node = $this->getNode();
		$node->setAttribute("pagID", $this->getId());
		$node->setAttribute("pagName", $this->getExternalName());
		$node->setAttribute("hidden", $this->getHidden() ? "true" : "false");
		$this->getDom()->save(PAGE_XML_PATH);
	}
	
	private function getDom(){
		if($this->dom==""){
			$this->dom = new DomDocument();
			$this->dom->load(PAGE_XML_PATH);
			$this->dom->formatOutput = true;
		}
		return $this->dom;
	}
	
	private function getNode(){
		/////////////////////////////////////
		// If id is -1 then it is a new object, and therefore shouldn't have a node yet
		if($this->getId()<1){
			$this->node = "";
		}
			
		if($this->node==""){
			$node = $this->getDom()->getElementById($this->getId());
			$this->node = $node;
		}
		return $this->node;
	}
	
	public function getParentId(){
		$node = $this->getNode();
		if($node!=NULL && $node->getAttribute("pagID")>0 && $node->parentNode)
			return $node->parentNode->getAttribute("pagID");
		else{
			return -1;
		}
	}
	
	private function setNode($node){
		$this->node = $node;
	}
	
	public function getFields(){
		if(!is_array($this->fields))
			$this->fields = $this->_getFields();
			
		return $this->fields;
	}
	
	private function _getFields(){
		$db = dbLink::getInstance();
		
		////////////////////////////////////
		// Load field records
		$where = "SELECT * FROM field WHERE fiePagId=".$this->getId();
		$fieldRecords = $db->performQuery($where);
		
		////////////////////////////////////
		// We have to create objects by setting data as each one is a different sort of field
		$fields = array();
		foreach($fieldRecords as $i=>$fieldRecord){
			$class = "field_".$fieldRecord['fieType'];
			$field = new $class();
			
			$fieldRecord = array_change_key_case($fieldRecord);
			$field->setId($fieldRecord['fieid']);
			$field->setPagId($fieldRecord['fiepagid']);
			$field->setType($fieldRecord['fietype']);
			$field->setFieldId($fieldRecord['fiefieldid']);
			$field->setContent($fieldRecord['fiecontent']);
			$field->setParams($fieldRecord['fieparams']);
			$fields[] = $field;
		}
		
		return $fields;
	}
	
	public function createFields(){
		///////////////////////////////
		// Unset previous fields
		// This forces getFields() to reload fields from db
		$this->fields = NULL;
		
		///////////////////////////////
		// Unset previous fields
		db_field::setMode(FIELD_MODE_EDIT);
		db_field::setPagId($this->getId());
		ob_start();
		$this->printPage();
		ob_end_clean();
	}
	
	public function updateFields(){
		//////////////////////////////////
		// Forces fields that are new to the template to be created
		$this->createFields();
		
		//////////////////////////////////
		// Forces db to get updated e.g. param changes on a field
		/*dbLink::setDebug(1);
		foreach($this->getFields() as $field){
			$field->save();
		}*/
	}
	
	public function delete(){
		$node = $this->getNode();
		
		if($node){
			$children = $node->getElementsByTagName("item");
			foreach($children as $child)
				$node->parentNode->appendChild($child);
				
			$node->parentNode->removeChild($node);
			
			$this->getDom()->save(PAGE_XML_PATH);
		}
		foreach($this->getFields() as $f){
			
			$f->delete();
		}
		
		parent::delete();
	}
}
?>