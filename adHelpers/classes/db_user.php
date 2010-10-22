<?php
	class db_user extends _db_object{
	
		
		public function openFromUsernameAndPassword($name, $password, $type="admin"){
			$db = dblink::getInstance();
			$where = " WHERE useUsername = ? AND usePassword = ?";
			if($type=="admin")
				$where .= " AND (useAdmin=TRUE OR useSuperAdmin=TRUE)";
			$sql = "SELECT ".$this->getTable().".* FROM ".$this->getTable().$where;
			$values = array($name, $password);
			$records = $db->performQuery($sql, "ss", $values, FALSE);
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