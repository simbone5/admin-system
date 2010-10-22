<?php
class contentLoader{
	private static $instance;
	private $page; 
	private $item;
	
	private function __construct(){
		$this->setItem(new menuItem());
	}
	
	public function getInstance(){
		if(self::$instance==NULL){
			self::$instance = new contentLoader();
		}
		
		return self::$instance;
	}
	
	public function loadContent($parts){
		if(count($parts)==0){
			$item = new db_page(HOMEPAGE_PAG_ID);
		}
		else{
			//////////////////////////
			// e.g. /ad_template_preview/12
			$item = $this->getPageFromUrlPagId($parts);
			if(!$item){
				//////////////////////////
				// e.g. /menu1/sub-menu1
				$item = $this->getMenuItemFromUrlPath($parts);
			}
			if(!$item){
				//////////////////////////
				// e.g. /page_title
				$item = $this->getPageFromUrlPath(getUrlFormat(current($parts)));
			}
			if(!$item){
				//////////////////////////
				// e.g. /download-file/12
				$item = $this->getFileFromUrlFilId($parts);
			}
		}
		
		$this->setItem($item); //<-make it available for the menu class
		return true;
	}
	
	
	public function printContent(){
		//////////////////////////////
		//	Loads content depending on passed variable
		//	$item will be either a menuItem object or a db_page object
		$item = $this->getItem();
		switch(strtolower(get_class($item))){
			case "db_page":
				$this->printFromPageObject($item);
				break;
			case "menuitem":
				$this->printFromMenuItemObject($item);
				break;
			case "db_template":
				$this->printFromTemplateObject($item);
				break;
			case "db_file":
				$this->printFromFileObject($item);
				break;
			default:
				$this->printPageNotFound();
		}
	}
	
	private function printFromFileObject($file){
		if(!$file->download())	
			$this->printPageNotFound();
	}
	
	private function printFromTemplateObject($template){
		if($template->getId()<1){
			$this->printPageNotFound();
			return false;
		}
		
		$template->printTemplate();
	}
	
	private function printFromMenuItemObject($menuItem){
		if(strtolower($menuItem->getType())=="page"){
			$page = new db_page($menuItem->getValue());
			return $this->printFromPageObject($page);
		}
		
		if(strtolower($menuItem->getType())=="url"){
			return $this->headerRedirect($menuItem->getValue());
		}
		
		$this->printPageNotFound();
	}
	
	private function headerRedirect($loc){
		$loc = trim($loc);
		if($loc==""){
			$this->printPageNotFound();
			return false;
		}
		
		header("Location:".$loc);
		exit();
	}
	
	private function printFromPageObject($page){
		if($page->getId()<1 || (!$page->getLive() && db_field::getMode()==FIELD_MODE_READ)){
			$this->printPageNotFound();
			return false;
		}
		$this->setPage($page);
		$page->printPage();
	}
	
	private function printPageNotFound(){
		$page = new db_page(PAGE_NOT_FOUND_PAG_ID);
		if($page->getId()<1){
			echo "error - could not find 404 page";
			return;
		}
		
		$this->printFromPageObject($page);
	}
	
	private function setPage($page){
		$this->page = $page;
	}
	
	public function getPage(){
		if(!$this->page)
			$this->page = new db_page();
		return $this->page;
	}
	
	private function setItem($item){
		$this->item = $item;
	}
	
	public function getItem(){
		return $this->item;
	}
	
	
	private function getPageFromUrlPagId($urlParts){
		//////////////////////////////////
		// checks for format /mode/pagId (e.g. /ad_template_preview/12) and return db_page obj

		if(!isset($urlParts[0]) || !isset($urlParts[1]))
			return false;
		
		$item = false;
		switch(strtolower($urlParts[0])){
			case "ad_template_preview":
				if($GLOBALS['ADMIN_LOGGED_IN']){
					db_field::setMode(FIELD_MODE_EXAMPLE);
					$item = new db_template($urlParts[1]);
				}
				break;
			case "ad_page_edit":
				if($GLOBALS['ADMIN_LOGGED_IN']){
					db_field::setMode(FIELD_MODE_EDIT);
					$item = new db_page($urlParts[1]);
				}
				break;
		}
		
		if($item && $item->getId()>0)
			return $item;
		else
			return false;
	}
	
	private function getFileFromUrlFilId($urlParts){
		//////////////////////////////////
		// checks for format /download-file/filId (e.g. /download-file/12) and return db_file obj

		if(!isset($urlParts[0]) || !isset($urlParts[1]) || $urlParts[1]<1)
			return false;
		
		if(strtolower($urlParts[0])!="download-file")
			return fales;
			
		$item = new db_file($urlParts[1]);
		if($item && $item->getId()>0 && $item->getExists())
			return $item;
		else
			return false;
	}

	private function getPageFromUrlPath($urlPart, $pageXmlParentNode = NULL){
		//////////////////////////////////
		// RECURSIVE FUNCTION
		// Loops through the passed node's children, ending when it finds a match to the urlPart

		if($pageXmlParentNode==NULL){	
			$dom = new DomDocument();
			$dom->load(PAGE_XML_PATH);
			$pageXmlParentNode = $dom->documentElement;
		}
		
		foreach($pageXmlParentNode->childNodes as $node){
			if($node->nodeType!=XML_ELEMENT_NODE)
				continue;
			
			$page = new db_page($node->getAttribute("pagID"));
			if($urlPart==getUrlFormat($page->getExternalName()) && $page->getId()>0 && $page->getLive()){
				return $page;
			}
			unset($page);
			
			
			if($node->hasChildNodes()){
				$page = $this->getPageFromUrlPath($urlPart, $node);
				if($page)
					return $page;
			}
			//else carry on through loop
		}
	}

	private function getMenuItemFromUrlPath($urlParts, $menuXmlParentNode = NULL){
		//////////////////////////////////
		// RECURSIVE FUNCTION
		// Loops through the passed node's children, filtering down the url path

		if($menuXmlParentNode==NULL){	
			$dom = new DomDocument();
			$dom->load(MENU_XML_PATH);
			$menuXmlParentNode = $dom->documentElement;
		}
		
		$urlPart = getUrlFormat(array_shift($urlParts));
		foreach($menuXmlParentNode->childNodes as $node){
			if($node->nodeType!=XML_ELEMENT_NODE)
				continue;
			//echo $urlPart."==".getUrlFormat($node->getAttribute("url"))."<br/>";
			if($urlPart==getUrlFormat($node->getAttribute("url"))){
				if(count($urlParts)>0 && $node->hasChildNodes())
					return $this->getMenuItemFromUrlPath($urlParts, $node);
				else{
					$menuId = (int)$node->getAttribute("menuID");
					if($menuId>0){
						$menuItem = new menuItem();
						$menuItem->openFromNode($node);
						return $menuItem;
					}
					else
						return false;
				}
				break;
			}
		}
	}
	}