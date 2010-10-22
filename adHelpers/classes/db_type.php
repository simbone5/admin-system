<?php
	class db_type extends _db_object{
		protected $dimensions;
	
		public function getDimensions($force = FALSE){
			if($this->dimensions==NULL || $force){
				$dblink = dblink::getInstance();
				$dims = $dblink->getObjects("dimension", "dimTypId=?", "i", array($this->getId()));
				$this->setDimensions($dims);
			}
			
			return $this->dimensions;
		}
		
		public function openFromTitle($title){
			$dblink = dblink::getInstance();
			$where = " where typTitle=?";
			$sql = "SELECT ".$this->getTable().".* FROM ".$this->getTable().$where;
			$values = array($title);
			$records = $dblink->performQuery($sql, "s", $values, FALSE);
			if(count($records)==1){
				$record = array_pop($records);
				$this->setPropertiesFromRecord($record);
				return true;
			}
			else{
				return false;
			}
		}

	}
?>