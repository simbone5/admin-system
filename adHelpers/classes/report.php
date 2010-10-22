<?php
///////////////////////////
// a class to generate custom reports. 
// An array of objects is passed to it and an array of functions/column headings
// This class then control the data that is output and controls ordering of the data

define("REPORT_TYPE_HTML", "html");
define("REPORT_TYPE_XML", "xml");
define("REPORT_TYPE_EXCEL", "excel");

class report{
	
	private $currentSortCol;
	private $currentSortDir;
	private $tableClass;
	private $headings;
	private $nonDataHeadings;
	private $data;
	private $pageUrl;
	
	
	public function __construct(){
		$this->currentSortCol = "";
		$this->currentSortDir = "";
		$this->tableClass = "";
		$this->pageUrl = "";
		$this->headings = array();
		$this->data = array();
		return;
	}
	
	////////////////////////////////////////
	// returns column headings in <th> tags
	private function getHtmlHeaders(){
		$html = "<tr>";
			foreach($this->getHeadings() as $h => $func){
				$html .= "<th>";
					$html .= $this->getHeaderLink($h);
				$html .= "</th>";
			}
			foreach($this->getNonDataHeadings() as $h => $func){
				$html .= "<th>";
					$html .= $h;
				$html .= "</th>";
			}
		$html .= "</tr>";
		
		return $html;
	}
	
	////////////////////////////////////////
	// returns the column heading as a link
	// The link allows the user to sort the data by the heading
	private function getHeaderLink($heading){
		if($this->getPageUrl()=="")
			return ucwords($heading);
			
		if($this->getUrlEncodedHeading($heading)==$this->getCurrentSortCol()){
			$dir = $this->getCurrentSortDir()=="sortUp" ? "sortDown" : "sortUp";
			}
		else
			$dir = "sortDown";	
		
		$class = "";
		if($this->getUrlEncodedHeading($heading)==$this->getCurrentSortCol())
			$class = $this->getCurrentSortDir();
		
		
		$href = $this->getPageUrl().(strpos($this->getPageUrl(), "?")===0 ? "&amp;" : "?")."sortCol=".$this->getUrlEncodedHeading($heading)."&amp;sortDir=".$dir;
		return "<a href='".$href."' title='Sort by ".$heading."' class='".$class."'>".ucwords($heading)."</a>";
	}

	////////////////////////////////////////
	// calls each function on each object using two loops
	// Each result returned by the functions are printed in <td> tags
	private function getHtmlBody(){
		$html = "";
		$data = $this->getSortedData();
		if(count($data)<1){
			$html = "<tr><td colspan='".count($this->getHeadings())."'>No results to display</td></tr>";
			return $html;
		}
		
		foreach($data as $obj){
			$html .= "<tr>";
				foreach($this->getHeadings() as $h => $func){
					$html .= "<td>";
						$html .= $obj->$func();
					$html .= "</td>";
				}
				foreach($this->getNonDataHeadings() as $h => $func){
					$html .= "<td>";
						$html .= $obj->$func();
					$html .= "</td>";
				}
			$html .= "</tr>";
		}
		return $html;
	}
	
		
	////////////////////////////////////////
	// clean the heading so that any dodgy characters aren't put in url string
	// also used to create valid xml tags
	private function getUrlEncodedHeading($h){
		$h = str_replace(array(" ", "&nbsp;"), "_", $h);
		$h = strtolower($h);
		return $h;
	}	
	

	////////////////////////////////////////
	// public function to get the entire report
	public function getReport($type = REPORT_TYPE_HTML){
		$returnValue = "";
		switch($type){
			case REPORT_TYPE_HTML:
				$returnValue = $this->getHtmlReport();
				break;
			case REPORT_TYPE_XML:
				$returnValue = $this->getXmlReport();
				break;
			case REPORT_TYPE_EXCEL:
				$returnValue = $this->getExcelReport();
				break;
		}
		return $returnValue;
	}
	
	private function getHtmlReport(){
		$html = "<table class='".$this->getTableClass()."'>";
			$html .= $this->getHtmlHeaders();
			$html .= $this->getHtmlBody();
		$html .= "</table>";
		return $html;
	}
	
	private function getXmlReport(){
		$xml = "<?xml version=\"1.0\" ?>";
		$xml .= "<xml>";		
			foreach($this->getSortedData() as $obj){
				$xml .= "<row>";
					foreach($this->getHeadings() as $h => $func){
						$tag = $this->getUrlEncodedHeading($h);
						$xml .= "<".$tag.">";
							$xml .= strip_tags($obj->$func());
						$xml .= "</".$tag.">";
					}
				$xml .= "</row>";
			}
		$xml .= "</xml>";
		return $xml;
	}
	
	private function getExcelReport(){
		$excel = "";
		$quote = '"';
		$sep = "\t";
		$nl = "\n";
		$excel .= $quote.implode($quote.$sep.$quote, str_replace("&nbsp;", " ", array_keys($this->getHeadings()))).$quote;
		$excel .= $nl;
		foreach($this->getSortedData() as $obj){
			$values = array();
			foreach($this->getHeadings() as $h => $func){
				$values[] = strip_tags($obj->$func());
			}
			$excel .= $quote.implode($quote.$sep.$quote, $values).$quote.$nl;
		}
		return $excel;
	}
	
	////////////////////////////////////////
	// returns the array of objects sorted in the order specified by $this->currentSortcol
	private function getSortedData(){
		$col = $this->getCurrentSortCol();
		$data = $this->getData();
		usort($data, array($this, 'compareObjects'));
		return $data;
	}
	
	////////////////////////////////////////
	// compares two objects using the function specified by $this->currentSortcol 
	private function compareObjects($a, $b){
		$funcs = $this->getHeadings();
		$func = "";
		foreach($funcs as $heading => $func){
			if($this->getCurrentSortCol()==$this->getUrlEncodedHeading($heading)){
				$func = $funcs[$heading];
				break;
			}
		}
		
		if($func=="")
			return 0;
			
		$value_a = strip_tags(strtolower($a->$func()));
		$value_b = strip_tags(strtolower($b->$func()));
		
		////////////////////////////////
		// Dates: *should* be in format dd/mm/YYYY
		// therefore we check for that format and reverse it so that format becomes
		// YYYYmmdd, which we can then compare properly.
		if((strlen($value_a)==10 && substr_count($value_a, "/")==2) || (strlen($value_a)==19 && substr_count($value_a, "/")==2 && substr_count($value_a, ":")==2)){
			$value_a = $this->convertDateToSortable($value_a);
			$value_b = $this->convertDateToSortable($value_b);
		}
		
	    if ( $value_a==$value_b) {
	        return 0;
	    }
		if($this->getCurrentSortDir()=="sortUp")
			return ($value_a < $value_b) ? 1 : -1;
		else
			return ($value_a < $value_b) ? -1 : 1;
			
	}
	
	private function convertDateToSortable($d){
		if(strlen($d)==19 && substr_count($d, "/")==2 && substr_count($d, ":")==2){
			$parts = explode(" ", $d);
			$datePart = $parts[0];
			$timePart = $parts[1];
			$timePart = str_replace(":", "", $timePart);
		}
		else{
			$datePart = $d;
			$timePart = "";
		}
			
		$result = implode(array_reverse(explode("/", $datePart)));
		$result .= $timePart;
		return $result;
	}
	
	public function setData($v){
		$this->data = $v;
	}
	
	private function getData(){
		return $this->data;
	}
	
	public function setHeadings($v){
		$this->headings = array_change_key_case($v);
	}
	
	private function getHeadings(){
		return $this->headings;
	}
	
	public function setNonDataHeadings($v){
		$this->nonDataHeadings = $v;
	}
	
	private function getNonDataHeadings(){
		return $this->nonDataHeadings;
	}
	
	public function setCurrentSortCol($v){
		$this->currentSortCol = $this->getUrlEncodedHeading($v);
	}
	
	
	////////////////////////////////////////
	// sort column is $_GET['sortCol']. If not set then default the current sort column to the first column 
	public function getCurrentSortCol(){
		if($this->currentSortDir!="")
			return $this->getUrlEncodedHeading($this->currentSortCol);
		
		if(isset($_GET['sortCol']) && $_GET['sortCol']!="")
			return $this->getUrlEncodedHeading($_GET['sortCol']);
		
		$headings = array_keys($this->getHeadings());
		if(count($headings)>0)
			return $this->getUrlEncodedHeading(current($headings));
	}
	
	public function setCurrentSortDir($v){
		$this->currentSortDir = $v;
	}
	
	////////////////////////////////////////
	// direction of sort order (up or down). defaults to down.
	public function getCurrentSortDir(){
		if($this->currentSortDir!="")
			return $this->currentSortDir;
		
		if(isset($_GET['sortDir']) && $_GET['sortDir']!="")
			return $_GET['sortDir'];
		
		return "sortDown";
	}
	
	////////////////////////////////////////
	// css class used on the <table> tag
	public function setTableClass($v){
		$this->tableClass = $v;
	}
	
	////////////////////////////////////////
	// css class used on the <table> tag
	private function getTableClass(){
		return $this->tableClass;
	}
		
	////////////////////////////////////////
	// the url of the page has to be passed into the class so that the links in the headers return to the correct page
	public function setPageUrl($v){
		$this->pageUrl = $v;
	}
	
	////////////////////////////////////////
	// the url of the page has to be passed into the class so that the links in the headers return to the correct page
	private function getPageUrl(){
		return $this->pageUrl;
	}
	

}
?>