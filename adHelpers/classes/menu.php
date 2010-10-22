<?php
class menu extends _baseClass{
	protected $location;
	protected $dom;
	
	public function __construct(){
		
	}
	
	public function getLocation(){
		if($this->location==null)
			$this->loadLocation();
		
		return $this->location;
	}
	
	private function loadLocation($parentNode = null, $urlPath = null){
		$contentLoader = contentLoader::getInstance();
		$item = $contentLoader->getItem();
		
		switch(strtolower(get_class($item))){
			case "db_page":
				$this->setLocation($this->loadLocationFromPageObj($item));
				break;
			case "menuitem":
				$this->setLocation($item);
				break;
			case "db_template":
				break;
			default:
				$this->setLocation(new menuItem());
		}
		
		return false;
		
		/*///////////////////////////////
		// The code below is replaced by the above, which gets location from contentLoader
		//
		if($parentNode==null)
			$parentNode = $this->getDom()->documentElement;
			
		if($urlPath==null)
			$urlPath = $GLOBALS['NAV_PATH']['parts'];
		
		//////////////////////////////////////
		// default
		$this->setLocation(new menuItem());
		
		/////////////////////////
		// if url path is empty we're on top level
		if(count($urlPath)==0)
			return true;
		
		
		foreach($parentNode->childNodes as $node){
			if($node->nodeType==XML_ELEMENT_NODE){
				//echo getUrlFormat($node->getAttribute("url"))."==".strtolower($urlPath[0])."<br/>";
				if(getUrlFormat($node->getAttribute("url"))==strtolower($urlPath[0])){
					array_shift($urlPath);
					$loc = new menuItem();
					$loc->openFromNode($node);
					$this->setLocation($loc);
					if(isset($urlPath[0])){
						$this->loadLocation($node, $urlPath);
					}
					return true;
				}
			}
		}
		return true;*/
	}
	
	private function loadLocationFromPageObj($page){
		$dom = $this->getDom();
		
		//////////////////////////////////////////////
		// get ALL items, not just direct children of root. 
		$nodes = $dom->documentElement->getElementsByTagName("item");
		foreach($nodes as $node){
			if($node->getAttribute("type")=="page" && $node->getAttribute("value")==$page->getId()){
				$menuItem = new menuItem();
				$menuItem->openFromNode($node);
				return $menuItem;
			}
		}
		return new menuItem();
	}
	
	public function getChildren(){
		$root = $this->getDom()->documentElement;
		$children = $root->childNodes;
		
		$returnArray = array();
		foreach($children as $c){
			if($c->nodeType==XML_ELEMENT_NODE){
				$menuItem = new menuItem();
				$menuItem->openFromNode($c);
				$returnArray[] = $menuItem;
			}
		}
		return $returnArray;
	}
	
	private function getDom(){
		if($this->dom==NULL){
			$dom = new DomDocument();
			$dom->load(MENU_XML_PATH);
			$dom->validateOnParse = true;
			$this->setDom($dom);
		}
		
		return $this->dom;
	}
	
}

?>