<?php

///////////////////////////////////
// Modes determine what the constructor does
define("FIELD_MODE_READ", "read");
define("FIELD_MODE_EDIT", "edit");
define("FIELD_MODE_EXAMPLE", "example");
define("FIELD_MODE_PREVIEW", "preview");

///////////////////////////////////
// These are used to save the type to the db 
define("FIELD_TYPE_MULTILINE", "multiline");
define("FIELD_TYPE_SINGLE", "single");
define("FIELD_TYPE_HTML", "html");
define("FIELD_TYPE_IMAGE", "image");
define("FIELD_TYPE_CABINATE", "cabinate");

///////////////////////////////////
// This class is a singleton. It is used to control all fields' settings,
// as the field types extend from this class.Therefore, this class extends _db_object,
// so that it can load the table details.
abstract class db_field extends _db_object{

	abstract protected function getContentRead();
	abstract protected function getContentEdit();
	abstract protected function getContentExample();
	abstract protected function getContentPreview();
	abstract protected function getType();
	

	protected static $pagid;
	protected $params; //String version of the params to be saved to db
	protected $paramsArray;// Aray version of params
	protected static $mode = FIELD_MODE_READ;

	
	public function __construct($fieldId = "", $params = array()){
		/////////////////////////
		// set table (i.e. "field", rather than _db_object's mthod of get_class($this) which may return field_text)
		$this->setTable("field");
		
		
		//////////////////////////////////////////////////////////////////////
		//
		
		// NEED TO UPDATE THE openFromPagIdAndFieldId method so that it checks
		// the params here against params in db.
		// if mismatch then update is required (like the part that checks the type)
		$this->setParams($params);
		
		
		
		
		
		
		
		
		
		
		///////////////////////////////////////////////
		parent::__construct();
		
		////////////////////////
		// No $fieldId will be provided if openFromId is going to be used
		if($fieldId=="")
			return;
		
		
		$this->setFieldId($fieldId);
		$this->openFromPagIdAndFieldId();
		
		//$this->printContent();// <-determines what mode we're in a calls appropriate method
		
	}
	
	////////////////////////
	// This is used because _db_object::setPropertiesFromRecord tries to set 
	// properties directly e.g. $field->pagid = $value
	// However, pagid is static and must be set via setPagId method
	public function __set($name, $value) {
		if($name=="pagid")
			self::setPagId($value);
		else
			$this->$name = $value;
    }
	
	public function openFromId($id){
		///////////////////////////////////////
		// this function is required cus the constructor is busy doing other things!
		$this->setTable("field");
		parent::__construct($id);
	}
	
	public function printContent(){
		switch($this->getMode()){
			case FIELD_MODE_READ:
				$content = $this->printContentRead();
				break;
			case FIELD_MODE_EDIT:
				$content = $this->printContentEdit();
				break;
			case FIELD_MODE_EXAMPLE:
				$content = $this->printContentExample();
				break;
			case FIELD_MODE_PREVIEW:
				$content = $this->printContentPreview();
				break;
			default:
				$content = "";
		}
		return $content;
	}
	
	private function printContentRead(){
		return $this->getContentRead();
	}
	
	private function printContentEdit(){
		$html = "\n<div id='field_".$this->getId()."'>";
			$html .= $this->getContentEdit();
		$html .= "\n</div>\n";
		return $html;
	}
	
	private function printContentExample(){
		return $this->getContentExample();
	}
	
	private function printContentPreview(){
		return $this->getContentPreview();
	}
	
	public function getInformationFields(){
		///////////////////////////
		// Used in the hidden form on pages/edit.php
		$html = "\n<input type='hidden' name='fields[field_".$this->getId()."][fieType]' id='fieType_".$this->getId()."' value='".$this->getType()."' />\n";
		$html .= "\n<input type='hidden' name='fields[field_".$this->getId()."][fieID]' id='fieID_".$this->getId()."' value='".$this->getId()."' />\n";
		return $html;
	}
	
	///////////////////////////
	// Note: it is PagId not PageId, because the db field is fiePagId
	private function openFromPagIdAndFieldId(){		
		if($this->getFieldId()=="" || $this->getPagId()=="")
			return false;
			
		$db = dblink::getInstance();
		$where = " WHERE fiePagId = ? AND fieFieldId = ?";
		$sql = "SELECT ".$this->getTable().".* FROM ".$this->getTable().$where;
		$values = array($this->getPagId(), $this->getFieldId());
		$records = $db->performQuery($sql, "is", $values, FALSE);
		if(count($records)==1){
			$record = array_pop($records);
			$requireUpdate = false;
			
			///////////////////////////////////////
			// Populate object's properties from record
			foreach($record as $col => $value){
				$field = $this->getFieldFromColumn($col);
			
				///////////////////////////////////////
				// Do some checking to ensure db record matches field's definition in template
				if(strtolower($col)=="fietype" && $this->getType()!=$value){
					$requireUpdate = true;
					$this->$field = $this->getType();
				}
				elseif(strtolower($col)=="fieparams" && $this->getParams(FALSE)!=$value){
					$requireUpdate = true;
					
					$this->$field = $this->getParams();
				}
				else{
					$this->$field = $value;
				}
			}
			
			if($requireUpdate)
				$this->save();
			
			$this->setId($record[$this->getPKCol()]);
			
			return true;
		}
		else{
			///////////////////////////////////////
			// This db_field isn't in db yet, so save it to create it.
			$this->save();
			
			
			return false;
		}
	}
	
	public function getParams($asArray = TRUE){
		if(isset($this->paramsArray) && $this->paramsArray!="")
			$arr = $this->paramsArray;
		elseif(isset($this->params) && $this->params!="")
			$arr = explode("|", $this->params);
		else
			$arr = array();
			
		if($asArray)
			return $arr;
		else
			return implode("|", $arr);
	}
	
	public function setParams($v){
		if(!is_array($v))
			$v = explode("|", $v);
		$this->params = implode("|", $v);
		$this->paramsArray = $v;
	}
	
	public static function setMode($mode){
		self::$mode = $mode;
	}
	
	public static function getMode(){
		return self::$mode;
	}
	
	
	public static function setPagId($pagId){
		self::$pagid = $pagId;
	}
	
	public static function getPagId(){
		return self::$pagid;
	}
	
	
}
?>