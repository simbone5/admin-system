<?php

class dbLink extends _baseClass{
	static private $instance;
	static private $sqlDebug = false;
	
	private function __construct($user = DB_USER, $password = DB_PASSWORD, $host = DB_HOST, $port = DB_PORT, $database = DB_NAME){
		$this->setIsConnected(false);
		$this->setConnection($this->_getConnection($user, $password, $host, $port, $database));
	}
	   	
	private function printError($function, $err){
		$adError = adError::getInstance();
		$adError->addClassError("dbLink", $function, $err);
		$adError->printErrorPage();
	}
	
	public static function getInstance(){
		if(self::$instance==NULL){
			self::$instance = new dbLink();
		}
        return self::$instance;
	}
	
	
	private function _getConnection($user, $password, $host, $port, $database){
        $connection = @new mysqli($host, $user, $password, $database, $port);
        if (@$connection->connect_errno===NULL || @$connection->connect_errno){
            $this->printError("_getConnection", "could not connect to database. " . @$connection->connect_errno .": " . @$connection->connect_error);
			return false;
        }
		$this->setIsConnected(true);
        return $connection;
    }
	
	public function getObjects($type, $where = null, $argParamTypes = null, $argParamArray = null, $order = null, $from = array()){
		$where = $where==null ? "" : " WHERE ".$where;
		$order = $order==null ? "" : " ORDER BY ".$order;
		$from[] = DB_TABLE_PREFIX.$type.DB_TABLE_SUFFIX;
		$sql = "SELECT DISTINCT ".DB_TABLE_PREFIX.$type.DB_TABLE_SUFFIX.".* FROM ".(implode(", ", $from)).$where.$order;
		return $this->performQuery($sql, $argParamTypes, $argParamArray, TRUE);
	}	
	
	public function performQuery($argQuery, $argParamTypes = null, $argParamArray = null, $returnObjects = FALSE){
		
				
		
		$this->setStatement($this->getConnection()->prepare($argQuery));

		if(!$this->getStatement()){
			$this->_printDebug($argQuery, $argParamTypes, $argParamArray);
		 	$this->printError("performQuery", "could not prepare statement.");
			return array();
        }
		
        // bind the params
        if ($argParamTypes != null){
            if(strlen($argParamTypes)>0 && func_num_args()>2 && is_array($argParamArray) && count($argParamArray)==strlen($argParamTypes)){
                $this->_bindParams($argParamTypes, $argParamArray);
            }
            else{
                $this->printError("performQuery", "could not bind parameters.");
			    $this->close();
                return array();
            }
        }
		
        $this->getStatement()->execute();
		$this->_printDebug($argQuery, $argParamTypes, $argParamArray);
		$res = $this->_getResults();
        $this->setRawRecords($res);


		if($returnObjects && count($this->getRawRecords()))
			$returnArray = $this->getObjectsFromRecords($argQuery);
		else
			$returnArray = $this->getRawRecords();



		return $returnArray;
    }	
	
	public function performUpdate($argQuery, $argParamTypes = null, $argParamArray = null){
		
		
		$this->closeStatement();
		
       
	    $rowCount = 0;

        $this->setStatement($this->getConnection()->prepare($argQuery));
		if (!$this->getStatement()){
		 	$this->printError("performUpdate", "could not prepare statement. " . $this->getConnection()->connect_errno .": " . $this->getConnection()->connect_error);
            return false;
        }
		
        // bind the params
        if ($argParamTypes != null){
            if(strlen($argParamTypes)>0 && func_num_args()>2 && is_array($argParamArray) && count($argParamArray)==strlen($argParamTypes)){
                $this->_bindParams($argParamTypes, $argParamArray);
            }
            else{
                $this->printError("performUpdate", "could not bind parameters. ");
                $this->close();
                return false;
            }
        }
        
        // execute query and buffer the result
        if(!$this->getStatement()->execute()){
			$this->_printDebug($argQuery, $argParamTypes, $argParamArray);
			$this->printError("performUpdate", "statement execute failed. ");
			$this->close();
			return false;
        }
		   
		$this->getStatement()->store_result();
    
        // 'rows affected count' to be returned
        $this->setRowsAffected($this->getStatement()->affected_rows);
		
        $lastInsertId = $this->getStatement()->insert_id;
		$this->_printDebug($argQuery, $argParamTypes, $argParamArray);
        // return the results
        return $lastInsertId;
    }
	
	private function _bindParams($argParamTypes, $argParamArray){
		
			
        // first two args will be the statement and the param types
        $bindParamFuncArgs[] = $argParamTypes;
		
        
        // then add each bind param to the end of the array
        for($i = 0; $i < count($argParamArray); $i++){
            $bindParamFuncArgs[] = (substr($argParamTypes, $i ,1) == "b") ? 
                                    null :
                                    $argParamArray[$i];
        }
		
        // call the function - this is where the params actually get bound
		$statement = $this->getStatement() ;
		call_user_func_array(array($statement, 'bind_param'), $bindParamFuncArgs);

		
		
        if ($this->getStatement()->errno){
			$this->printError("_bindParams", "could not bind parameters. " . $this->getConnection()->connect_errno .": " . $this->getConnection()->connect_error);
			$this->close();
			return false;
        }

        // deal with blobs differntly
        for($i = 0; ($argParamTypes != null) && ($i < strlen($argParamTypes)); $i++){
            if(substr($argParamTypes, $i ,1) == "b"){
                if(is_resource($argParamArray[$i]) && (get_resource_type($argParamArray[$i]) == 'stream'  )){
                    // we have been passed a filehandle, not the actual blob
                    while (!feof($argParamArray[$i])){
                        // send to the ith parameter, the file data in 8192 chunks
                        $this->getStatement()->send_long_data($i, fread($argParamArray[$i], 8192));
                    }   
                }
                else{
                    // it's a BLOB so send it in its own special way
                    $this->getStatement()->send_long_data($i, $argParamArray[$i]);
                }
            }
        }
    }

	private function _getResults(){
	
			
        // this will be the array of rows in the result
        $returnArray = array();

        // find how many columns there are
        $columnsInResult = $this->getStatement()->field_count;
		
        // find the names to use in the associative array returned to the caller
        $colNameArray = $this->getColumnNames();
        
        // bind the results	using our function below	
        $resultBindArray = $this->_bindResults();
		
		// move to the next row (going beyond the last row returns false)
        while($this->getStatement()->fetch()){
            //print_r($resultBindArray);
            // iterate across the columns of data for this row
            for($i = 0; $i < $columnsInResult; $i++){
                // copy the data from the bind variable into an associative array
                $row[$colNameArray[$i]] = $resultBindArray[$i];
            }
            // put the array representing this row in the array representing the whole result set
            $returnArray[] = $row;
        }
        return $returnArray;
    }

    private function _bindResults(){  
	
        // find how many columns there are
        $columnsInResult = $this->getStatement()->field_count;

        // build a string of the code that will execute - first the fixed part
        for($i = 0; $i < $columnsInResult; $i++){
            // make the bind array one element bigger
            $resultBindArray[] = null;
            
            // - then add an arg to represent each array element - NB pass by ref
            $bindResultFuncArgs[] = &$resultBindArray[$i];
        }
        
        // call the function - this is where the params actually get bound 
		$statement = $this->getStatement() ;
		call_user_func_array(array($statement, 'bind_result'), $bindResultFuncArgs);

        return $resultBindArray;
    }
	
	public function getColumnNames(){
	
			
        $colNameArray = null;
        $resultMetadata = $this->getStatement()->result_metadata();
        
        // get the column names by iterating across the metadata
        while($columnMetadata = $resultMetadata->fetch_field()){
            // copy the name property of the $columnMetadata Object into an ordinary array
            $colNameArray[] = $columnMetadata -> name;
        }

		
        // release the metadata
        $this->getStatement()->result_metadata()->free();
        //print_r($colNameArray);
        return $colNameArray;
    }
	
	private function closeStatement(){
		// release resources
        if($this->getStatement()!=null){
			@$this->getStatement()->free_result();
			@$this->getStatement()->close();
		}
	}
	
	private function close(){
		// release resources
        $this->closeStatement();
		
        if($this->getConnection() != null)
			@$this->getConnection()->close();
	}
	
	
	public function getObjectsFromRecords($argQuery){ 
	
		
		if(!is_array($this->getRawRecords()) || count($this->getRawRecords())<1){
			$this->printError("getObjectsFromRecords", "raw results not set so cannot create objects");
			return false;
		}
		
		
		///////////////////////////
		//	Get Meta Data for record
		$recordMetaData = $this->getStatement()->result_metadata();
		$metaFields = $recordMetaData->fetch_fields();
		
		///////////////////////////
		//	Loop through records
		$i = -1;
		$returnObjects = array();
		foreach($this->getRawRecords() as $record){
			$objClass = "";
		
						
			///////////////////////////
			//	Loop through fields
			$j = -1;
			foreach($record as $col => $value){
				$j++;
				
				///////////////////////////
				//	Get metadata for this field
				//	The metadata->orgtable will change when we're looking at fields from a different table....
				//	... so that's when we create a new object
				$colMeta  = $metaFields[$j];
				
				///////////////////////////
				//	get object class name by removing the table prefix and suffix
				//	Note: at the moment this simpy removes the number of characters matching the prefix/suffix length!
				$tableName = substr(substr($colMeta->orgtable, 0, strlen($colMeta->orgtable)-strlen(DB_TABLE_SUFFIX)), strlen(DB_TABLE_PREFIX));
				$nextObjClass = "db_".$tableName;
				
				///////////////////////////
				//	If we've moved onto columns from another table create a new object			
				if($objClass!=$nextObjClass){
					$i++;
					$objClass = $nextObjClass;
					$tmpObj = new $objClass();
					$tmpObj->setPropertiesFromRecord($record);
					$returnObjects[$tmpObj->getId()] = $tmpObj;
				}
				if(isset($tmpObj))
					unset($tmpObj);
				
				///////////////////////////
				//	Set the objects properties
				//$field = $returnObjects[$i]->getFieldFromColumn($col);
				//$returnObjects[$i]->$field = $value;
				//
				// Now done in _db_object class using setPropertiesFromRecord() method above
				
			}
		}
		
		return $returnObjects;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	// TRANSACTIONS
	//////////////////////////////////////////////////////////////////////////////////////////////
		
	public function startTransaction(){
		
		
		if($this->getConnection()->autocommit(FALSE)){
			if($GLOBALS['SQL_DEBUG']==true)
				echo "<div style='background: #EEEEEE'><b>Transaction started</b></div>";
			return true;
			}
		else{
			$this->printError("startTransaction", "transaction could not be started. Ensure table types are transactional.");
			return false;
		}
	}
	
	public function commitTransaction(){
		if($GLOBALS['SQL_DEBUG']==true)
			echo "<div style='background: #EEEEEE'><b>Committing transaction</b></div>";
		$this->getConnection()->commit();
		$this->getConnection()->autocommit(TRUE);
		return true;
	}
	
	public function rollbackTransaction(){
		
		if($GLOBALS['SQL_DEBUG']==true)
			echo "<div style='background: #EEEEEE'><b>Rolling back transaction</b></div>";
		
		$this->getConnection()->rollback();
		$this->getConnection()->autocommit(TRUE);
		return true;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////
	// DEBUG
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	public function setDebug($v){
		self::$sqlDebug = (boolean)$v;
	}
	
	private function getDebug(){
		return self::$sqlDebug;
	}
	
	private function _printDebug($argQuery, $argParamTypes, $argParamArray){
		if($this->getDebug()){
			echo "<div style='background: #EEEEEE; border: solid 1px black;'><b>Query:</b>".$argQuery;
			echo "<br /><b>Param Types:</b>".$argParamTypes;
			echo "<br /><b>Param Values:</b>";
			if(is_array($argParamArray)){
				foreach($argParamArray as $arg){
					echo "<br />".$arg." (".gettype($arg).")";
				}
			}
			echo "<br /><b>Error:</b>".$this->getStatement()->error;
			echo "</div>";
		}
	}
	
}

?>