<?php
///////////////////////////
//	Most classes (especially object classes) will inherit from here
//	The only use of this class is the use the __call function to get/set variable
//	It allows for the use of setXxxx($value) and getXxxx() on an object where setXxxx() and getXxxx don't exist.
class _baseClass{
	 private function __construct(){
		//exit("<b>_baseClass::__call error</b> - class cannot be called directly. Use child classes.");
	}
	
	private function printError($function, $err){
		$adError = adError::getInstance();
		$adError->addClassError("_baseClass", $function, $err);
		$adError->printErrorPage();
	}
	
	public function __call($method, $params){
		$method = strtolower($method);
		if(strpos($method, "get")===0){
			return $this->_baseClassTranslateGetMethod($method);
		}		
		elseif(strpos($method, "set")===0){
			return $this->_baseClassTranslateSetMethod($method, $params);
		}
		elseif(strpos($method, "add")===0){
			return $this->_baseClassTranslateAddMethod($method, $params);
		}
		else
			$this->printError("__call", "'".$method."' called but not recognised as a class method. Parent class = ".get_class($this));
	}


	///////////////////////////
	//	If $method contains the word "get" then try to retrieve a value	
	private function _baseClassTranslateGetMethod($method){
		if(strlen($method)<4)
			$this->printError("_baseClassTranslateGetMethod", "'".$method."' method called. What am I getting? Parent class = ".get_class($this));
		$varName = substr($method, 3, strlen($method));
		
		if(isset($this->$varName)){
			return $this->$varName;
		}
		else{
			///////////////////////////////////////
			//I think it has to be null rather than empty string so that db doesn't 
			// complain about empty foreign keys
			return NULL;
		}
	}
	
	
	///////////////////////////
	//	If $method contains the word "set" then set a value - provided the param count is 1	
	private function _baseClassTranslateSetMethod($method, $params){
		if(strlen($method)<4)
			$this->printError("_baseClassTranslateSetMethod", "'".$method."' method called. What am I setting? Parent class = ".get_class($this));
		$varName = substr($method, 3, strlen($method));
		if(count($params)==1){
			$this->$varName = $params[0];
			return true;
		}
		else{
			if(count($params)<1)
				$this->printError("_baseClassTranslateSetMethod", "'".$method." called with no parameters. Parent class = ".get_class($this));
			else
				$this->printError("_baseClassTranslateSetMethod", "'".$method." called with too many parameters (".count($params)." instead of 1). Parent class = ".get_class($this));
		}
	}
	
	///////////////////////////
	//	If $method contains the word "add" then add a value an array - provided the param count is 1, and that is is an array	
	private function _baseClassTranslateAddMethod($method, $params){
		if(strlen($method)<4)
			$this->printError("_baseClassTranslateAddMethod", "'".$method."' method called. What am I adding to? Parent class = ".get_class($this));
		$varName = substr($method, 3, strlen($method));
		//if there are 1 or 2 params, and the variable isn't set or is an array
		if(!isset($this->$varName) || is_array($this->$varName)){
			if(count($params)==1){
				$this->$varName = array();
				array_push($this->$varName, $params[0]);// $obj->addSomething('value', 'key');
				return true;
			}
			elseif(count($params)==2){
				$this->$varName[$params[1]] = $params[0];// $obj->addSomething('value', 'key');
				return true;
			}
			elseif(count($params)<1)
				$this->printError("_baseClassTranslateAddMethod", "'".$method." called with no parameters. Parent class = ".get_class($this));
			else
				$this->printError("_baseClassTranslateAddMethod", "'".$method." called with too many parameters (".count($params)." instead of 1 or 2). Parent class = ".get_class($this));
		}
		else{
			$this->printError("_baseClassTranslateAddMethod", "'".$method."' method called. '".$varName."' is not an array. Parent class = ".get_class($this));
		}
	}
	
}

?>