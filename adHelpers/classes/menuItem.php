<?php
/////////////////////////////////////////////
//
//	Attributes available on a menuItem:
//	menuID
//	type (page|url)
//	title
//	url
//	value
//
/////////////////////////////////////////////
class menuItem extends _baseclass{
	protected $id;
	protected $type;
	protected $title;
	protected $value;
	protected $node;
	protected $dom;
	
	public function __construct($id = -1){
		
		if($id<1)
			return;
		
		$dom = $this->getDom();
		$node = $dom->getElementById($id);
		if($node==NULL){
			$this->setId(-1);
			return false;
		}
		
		$this->openFromNode($node);
	}
	
	public function openFromNode($node){
		$this->setId($node->getAttribute("menuID"));
		$this->setType($node->getAttribute("type"));
		$this->setTitle($node->getAttribute("title"));
		$this->setUrl($node->getAttribute("url"));
		$this->setValue($node->getAttribute("value"));
		$this->setNode($node);
		return true;
	}
	
	public function getParent(){
		$parentItem = new menuItem();
		$node = $this->getNode();
		
		if($node==null)
			return $parentItem;
			
		$parentNode = $node->parentNode;
		if($parentNode!=null)
			$parentItem->openFromNode($parentNode);
		
		return $parentItem;
	}
	
	public function getChildren(){
		$childNodes = $this->getNode()->childNodes;
		$childMenuItems = array();
		foreach($childNodes as $childNode){
			if($childNode->nodeType==XML_ELEMENT_NODE){
				$menuItem = new menuItem();
				$menuItem->openFromNode($childNode);
				$childMenuItems[] = $menuItem;
			}
		}
		
		return $childMenuItems;
	}
	
	public function getMenuId(){
		return $this->getId();
	}
	
	private function setMenuId($id){
		return $this->setId($id);
	}
	
	public function getTitle($validate = TRUE){
		$mode = db_field::getMode();
		if($validate && ($mode==FIELD_MODE_EDIT || $mode==FIELD_MODE_PREVIEW)){
			if(!$this->getIsValidURL()){
				$js = "window.parent.selectBrokenLink(\"".htmlspecialchars(stripslashes($this->title), ENT_QUOTES)."\")";
				return "<span onclick='javascript:".$js." ;return false;' title='Error'>".stripslashes($this->title)."</span>";
			}
		}
		
		return $this->title;
	}
		
	private function getIsValidUrl(){
		if($this->getType()=="page"){
			$page = new db_page($this->getValue());
			if($page->getId()==-1)
				return false;
		}
		
		return true;
	}
	
	public function getUrlPath(){
		$pathParts[] = getUrlFormat($this->getUrl());
		$node = $this->getNode();
		while($node->parentNode->nodeType==XML_ELEMENT_NODE && $node->parentNode->getAttribute("menuID")!=null){
			$pathParts[] = getUrlFormat($node->parentNode->getAttribute("url"));
			$node = $node->parentNode;
		}
		return "/".implode("/", array_reverse($pathParts));
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
	
	public function save(){
		if($this->getId()>0)
			$this->updateNode();
		else
			$this->_insertNode();
	}
	
	private function updateNode(){

		if($this->getNode()==NULL){
			$adError = adError::getInstance();
			$adError->addClassError("menuItem", "_updateNode", "node not set");
			return false;
		}
		
		$node = $this->getNode();
		$node->setAttribute("title", $this->getTitle());
		$node->setAttribute("url", $this->getUrl());
		$node->setAttribute("type", $this->getType());
		$node->setAttribute("value", $this->getValue());
		$this->getDom()->save(MENU_XML_PATH);
	}
	
	private function _insertNode(){
		$dom = $this->getDom();
		
		$newItem = $dom->createElement("item");
		
		//////////////////////////////////
		// Create a type attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$typeAttribute = $dom->createAttribute("type");
		$typeAttributeValue = $dom->createTextNode($this->getType());
		$typeAttribute->appendChild($typeAttributeValue);
		$newItem->appendChild($typeAttribute);
		
		//////////////////////////////////
		// Create a title attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$titleAttribute = $dom->createAttribute("title");
		$titleAttributeValue = $dom->createTextNode($this->getTitle());
		$titleAttribute->appendChild($titleAttributeValue);
		$newItem->appendChild($titleAttribute);
		
		//////////////////////////////////
		// Create a url attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$urlAttribute = $dom->createAttribute("url");
		$urlAttributeValue = $dom->createTextNode($this->getUrl());
		$urlAttribute->appendChild($urlAttributeValue);
		$newItem->appendChild($urlAttribute);
		
		//////////////////////////////////
		// Create a 'value' attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$valueAttribute = $dom->createAttribute("value");
		$valueAttributeValue = $dom->createTextNode($this->getValue());
		$valueAttribute->appendChild($valueAttributeValue);
		$newItem->appendChild($valueAttribute);
		
		//////////////////////////////////
		// Create a 'menuID' attribute, create a textNode for the attribute's value
		// Append them to the $newItem
		$valueAttribute = $dom->createAttribute("menuID");
		$valueAttributeValue = $dom->createTextNode($this->getNewMenuId());
		$valueAttribute->appendChild($valueAttributeValue);
		$newItem->appendChild($valueAttribute);
		
		
		
		if($this->getParentId()>0)
			$parent = $dom->getElementById($this->getParentId());
		else
			$parent = $dom->documentElement;
			
		$parent->appendChild($newItem);
		
		//p("<xmp>".$dom->saveXML()."</xmp>");
		$dom->save(MENU_XML_PATH);
	}
	
	private function getNewMenuId(){
		$dom = $this->getDom();
		$items = $dom->getElementsByTagName("item");
		$newId = 1;
		foreach($items as $item){
			if($item->getAttribute("menuID")>=$newId){
				$newId = $item->getAttribute("menuID")+1;
			}
		}
		return $newId;
	}
	
	public function delete(){
		$node = $this->getNode();
		if(!$node){
			$adE = adError::getInstance();
			$adE->addClassError("menuItem", "delete", "could not load node");
			$adE->printErrorPage();
		}
		$node->parentNode->removeChild($node);
		$this->getDom()->save(MENU_XML_PATH);
	}
}
?>