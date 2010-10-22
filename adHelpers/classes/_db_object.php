<?php
define("MYSQL_DATE", "Y-m-d H:i:s");

class _db_object extends _baseClass{
	protected $table;
	protected $id;
	
	public function __construct($ID = -1){
		$this->_loadTableDetails();
		$this->id = -1;
		if($ID>0){
			$this->setId((int)$ID);
			$this->loadObjectDetails();
		}
	}
		
	private function printError($function, $err){
		$adError = adError::getInstance();
		$adError->addClassError("_db_object", $function, $err);
		$adError->printErrorPage();
	}
	
	public function getId(){
		/////////////////////////////////////
		// return $this->id if this->id is set OR if the fieldFromCol is "id".
		if((isset($this->id) && $this->id!="") || strtolower($this->getFieldFromColumn($this->getPkCol()))=="id")
			return $this->id;
		else{
			$getFunc = "get".$this->getFieldFromColumn($this->getPkCol());
			return $this->$getFunc();
		}
	}
	
	
	//////////////////////////////////////
	//	Set variable to hold PK column, column names, table name etc
	private function _loadTableDetails(){
		$db = dblink::getInstance();
		if(isset($GLOBALS['DB_TABLE_COLS'][$this->getTable()])){
			$this->setPKCol($GLOBALS['DB_TABLE_COLS'][$this->getTable()]['pkCol']);
			$this->setColumnDetails($GLOBALS['DB_TABLE_COLS'][$this->getTable()]['cols']);
		}
		else{
			$cols = $db->performQuery("describe ".$this->getTable(), null, null, FALSE);
			if(count($cols)<1)
				$this->printError("_loadTableDetails", "item columns could not be loaded - table identified as '".$this->getTable()."'");
			else{
				foreach($cols as $colDetails){
					$colDetails = array_change_key_case($colDetails);
					$this->_addCol($colDetails);
					if(strtolower($colDetails['key'])=="pri"){
						$this->setPKCol($colDetails['field']);
						$GLOBALS['DB_TABLE_COLS'][$this->getTable()]['pkCol'] = $this->getPKCol();
					}
				}
			}
		}
	}
	
	//////////////////////////////////////
	//	Add another column to the array of column details
	private function _addCol($colDetails){
		$field = $this->getFieldFromColumn($colDetails['field']);
		$type = $this->getTypeFromColumnType($colDetails['type']);
		$this->columndetails[$colDetails['field']] = array("field" => $field, "type" => $type);
		$GLOBALS['DB_TABLE_COLS'][$this->getTable()]['cols'][$colDetails['field']] = $this->columndetails[$colDetails['field']];
		return true;
	}

	//////////////////////////////////////
	//	Converts a column heading to user friendly call name e.g. dbUserName to Name	
	public function getFieldFromColumn($col){
		$field = substr(substr($col, 3, strlen($col)-strlen(DB_COLUMN_SUFFIX)), strlen(DB_COLUMN_PREFIX));
		#$field = substr($field, strlen(trim(get_class($this), "db_")));
		return strtolower($field);
	}

	//////////////////////////////////////
	//	When passed a db column type it converts it to a type for bind params e.g. 'varchar(50)' becomes 's', and 'int' becomes 'i'
	public function getTypeFromColumnType($colType){
		$type = "s"; //<-default is string
		if(strpos($colType, "int")!==FALSE){
			$type = "i";
		}
		if(strpos($colType, "timestamp")===0){
			$type = "null_field";
		}
		if(strpos($colType, "date")===0){
			$type = "date_field";
		}
		return $type;
	}
	
	//////////////////////////////////////
	//	if passed an ID in the constructor, then load details from the db
	public function loadObjectDetails(){
		$db = dblink::getInstance();
		
		$where = " WHERE ".$this->getPkCol()." = ?";
		$sql = "SELECT ".$this->getTable().".* FROM ".$this->getTable().$where;
		$records = $db->performQuery($sql, "i", array($this->getId()), FALSE);
		if(count($records)==1){
			$record = array_pop($records);
			$this->setPropertiesFromRecord($record);
		}
		else{
			$this->setId(-1);
		}
	}
	
	public function setPropertiesFromRecord($record){
		$colDetails = $this->getColumnDetails();
		foreach($record as $col => $value){
			if($colDetails[$col]["type"]=="date_field"){
				$value = strtotime($value);
				$zone = intval(date("O"))/100;
				$value += $zone*60*60;
			}
			$field = $this->getFieldFromColumn($col);
			$this->$field = $value;
		}
	}
	
	//////////////////////////////////////
	//	if the object is created using dblink::getObjects() then $this->ID wont be set. This function will load it.
	public function loadId(){
		if($this->getId()>0){
			return true;
		}
		
		$getFunc = "get".$this->getPkCol();
		if($getFunc()>0){
			$this->setId($getFunc());
			return true;
		}
		return false;
	}
	
	public function save(){
		if($this->getId()>0)
			return $this->_updateRecord();
		else
			return $this->_insertRecord();
	}

	protected function _updateRecord(){
		$db = dblink::getInstance();
		
		if(!$this->loadId()){
			$this->printError("_updateRecord", "item could not be updated in db - no ID set");
		}
		$paramTypes = "";
		$values = array();
		$colNames = array();
		foreach($this->getColumnDetails() as $col => $details){ //$col = actual col name.
			$getFunc = "get".$details['field'];
			/////////////////////
			//	Don't include PK in update fields
			if(strtolower($this->getFieldFromColumn($this->getPKCol()))!=strtolower($details['field'])){
				if($details['type']=="null_field" || $this->$getFunc()===NULL){
					$colNames[] = $col." = NULL";
				}
				elseif($details['type']=="date_field"){
					$values[] = gmdate(MYSQL_DATE, $this->$getFunc());
					$paramTypes .= "s";
					$colNames[] = $col." = ?";
				}
				else{
					$value = $this->$getFunc();
					if(is_array($value))
						$value = implode("|", $value);
					$values[] = $value;
					$paramTypes .= $details['type'];
					$colNames[] = $col." = ?";
				}
			}
		}
		
		$values[] = $this->getId();
		$paramTypes .= "i";
		$sql = "UPDATE ".$this->getTable()." SET ".implode(", ", $colNames)." WHERE ".$this->getPkCol()." = ?";
		$db->performUpdate($sql, $paramTypes, $values);
		return true;
	}
	
	
	protected function _insertRecord(){
		$db = dblink::getInstance();
		
		$paramTypes = "";
		$values = array();
		$colNames = array();
		$markers = array();//<- usually ?, but sometimes NULL
		foreach($this->getColumnDetails() as $col => $details){ //$col = actual col name. 
			$getFunc = "get".$details['field'];
			if(strtolower($this->getFieldFromColumn($this->getPKCol()))!=strtolower($details['field'])){
				if($details['type']=="null_field" || $this->$getFunc()===NULL){
					$markers[] = "NULL";
					$colNames[] = $col;
				}
				elseif($details['type']=="date_field"){
					$markers[] = "?";
					$values[] = gmdate(MYSQL_DATE, (int)$this->$getFunc());
					$paramTypes .= "s";
					$colNames[] = $col;
				}
				else{
					$value = $this->$getFunc();
					if(is_array($value))
						$value = implode("|", $value);
						
					$markers[] = "?";
					$values[] = $value;
					$paramTypes .= $details['type'];
					$colNames[] = $col;
				}
			}
		}
		$sql = "INSERT INTO ".$this->getTable()." (".$this->getPkCol().", ".implode(", ", $colNames).") VALUES (NULL, ".implode(", ", $markers).")";

		$id = $db->performUpdate($sql, $paramTypes, $values);
		$setFunc = "set".$this->getFieldFromColumn($this->getPKCol());
		$this->$setFunc($id);
		$this->setId($id); 
		return true;
	}
	
	public function delete(){
		$db = dblink::getInstance();
		
		$db->performUpdate("DELETE FROM ".$this->getTable()." WHERE ".$this->getPkCol()." = ?", "i", array($this->getId()));
		
		$this->setId(-1);
	}
	
	public function getTable(){
		////////////////////////////////////
		// This method provides a way to override the default assumption that get_class($this) gives table name
		// The assumption is wrong for cases where an object is created that extends the db_tableName object.
		if($this->table==null){
			$this->setTable( DB_TABLE_PREFIX.str_replace("db_", "", get_class($this)).DB_TABLE_SUFFIX );
		}
		return $this->table;
	}
	
	
	protected function openFromCol($col, $value){
		$db = dblink::getInstance();
		$where = " WHERE ".$col." = ?";
		$sql = "SELECT ".$this->getTable().".* FROM ".$this->getTable().$where;
		$values = array($value);
		$records = $db->performQuery($sql, "s", $values, FALSE);
		if(count($records)==1){
			$record = array_pop($records);
			foreach($record as $col => $value){
				$field = $this->getFieldFromColumn($col);
				$this->$field = $value;
			}
			$this->setId($record[$this->getPKCol()]);
			return true;
		}
		else{
			return false;
		}
	}
}

?>