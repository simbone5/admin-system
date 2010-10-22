<?php
////////////////////////////////////////
// Any errors should be handled by this class, including 404 errors.
// THIS IS A SINGLETON CLASS. Use $e = adError::getInstance();
class adError{
	static private $instance;
	
	private function __construct(){
		$this->errors = array();
		$this->userErrors = array();
		return;
	}
	
	public function getInstance(){	
		if( self::$instance == NULL ){
			self::$instance = new adError();
		}
	
		return( self::$instance );
	}
	
	////////////////////////////////////////
	// add an error reported by a class
	public function addClassError($klass, $function, $error){
		$arr = array("class" => $klass, "function" => $function, "error" => $error);
		$this->errors[] = $arr;
		return true;
	}

	////////////////////////////////////////
	// add a general error
	public function addError($function, $error){
		$arr = array("class" => "", "function" => $function, "error" => $error);
		$this->errors[] = $arr;
		return true;
	}
	
	public function getErrors(){
		return $this->errors;
	}

	////////////////////////////////////////
	// add a user error
	public function addUserError($error){
		$this->userErrors[] = $error;
		return true;
	}
	
	public function getUserErrors(){
		return $this->userErrors;
	}
	
	public function getNumberErrors(){
		return count($this->errors) + count($this->userErrors);
	}
	
	public function areErrors(){
		return $this->getNumberErrors()>0;
	}
	
	////////////////////////////////////////
	// format errors so that they all appear in a standard way
	public function getFormattedErrors(){
		$html = "\n<ol>";
			foreach($this->getErrors() as $err){
				$html .= "\n<li>";
					$html .= "\n<b>";
						$html .= ($err['class']=="" ? "" : $err['class']."::");
						$html .= $err['function'];
						$html .= " error - ";
					$html .= "\n</b>";
					$html .= $err['error'];
				$html .= "\n</li>";
			}
			foreach($this->getUserErrors() as $error){
				$html .= "\n<li>";
					$html .= $error;
				$html .= "\n</li>";
			}
		$html .= "\n</ol>";
		return $html;
	}
	
	////////////////////////////////////////
	// print error. We use the adPage class, but clear out any content so that only the error is printed.
	public function printErrorPage(){
		$p = adPage::getInstance();
		$ajax = $p->getAjaxPage();
		$p->clear();
		$p->setAjaxPage($ajax);
		$p->setBreadcrumb(array("Errors"));
		p("<p class='alert'>The following errors have occured</p>");
		p($this->getFormattedErrors());
		$p->printPage(TRUE, TRUE);
		exit; //We exit here because if an error has occured we don't want the scripts to continue.
	}
}
?>