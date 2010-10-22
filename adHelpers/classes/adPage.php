<?php
////////////////////////////////////////
// All content that is output uses this class.
// THIS IS A SINGLETON CLASS. Use $p = adPage::getInstance();
class adPage{
	static private $instance;
	
	private function __construct(){
		$this->initialise();
		return;	
	}
	
	public function initialise(){
		$this->content = array(); // an array of content 
		$this->javascripts = array();
		$this->css = array();
		$this->onloads = array();// scripts that will go into the <body onload=""> attribute
		$this->headers = array();// headers such as image/jpeg
		$this->popups = array();// content for popup windows
		$this->internalCss = "";
		$this->title = "";
		$this->tinyMce = FALSE;
		$this->jQuery = FALSE;
		$this->ajaxPage = FALSE;// when TRUE <html>, <head> etc aren't printed
		$this->structured = TRUE;// when TRUE the menu and page divs are printed
		$this->internalJavascript = "";
		$this->breadcrumb = array();// the breadcrumb is taken from an array and printed.
		return;	
	}
	
	public function getInstance(){	
		if( self::$instance == NULL ){
			self::$instance = new adPage();
		}
	
		return( self::$instance );
	}
	
	public function addJavascript($filename){
		//setting the key to $filename removes duplicates
		$this->javascripts[$filename] = $filename;
	}
	
	public function getJavascripts(){
		return $this->javascripts;
	}
	
	public function addCss($filename, $media = "screen"){
		//setting the key to $filename removes duplicates
		$this->css[$filename] = $media;
	}	
	
	public function getCss(){
		return $this->css;
	}	
	
	public function addOnload($onload){
		$this->onloads[] = $onload;
	}	
	
	public function getOnloads(){
		return $this->onloads;
	}	
	
	public function addHeader($header){
		$this->headers[] = $header;
	}	
	
	public function getHeaders(){
		return $this->headers;
	}	
	
	public function addPopup($id, $popupContent){
		$this->popups[$id] = $popupContent;
	}	
	
	public function getPopups(){
		return $this->popups;
	}

	////////////////////////////////////////
	// place the content for the popup windows into a popup div
	public function getPopupsHTML(){
		$html = "";
		$i = 0;
		foreach($this->getPopups() as $id => $popupContent){
			$i++;
			$html .= "\n<div class='popupWrapper' id='".$id."'>\n";
				$html .= $popupContent;
			$html .= "\n</div>\n";
			$html .= '<iframe class="popupIFrame" id="popupIFrame'.$i.'" height="100%">&nbsp;</iframe>';
		}
		
		return $html;
	}
	
	public function setTitle($title){
		$this->title = $title;
	}	
	
	private function getTitle(){
		return $this->title;
	}
	
	public function setTinyMce($v){
		$this->tinyMce = $v;
	}	
	
	private function getTinyMce(){
		return $this->tinyMce;
	}
	
	public function setJQuery($v){
		$this->jQuery = $v;
	}	
	
	private function getJQuery(){
		return $this->jQuery;
	}
	
	public function addContent($v){
		$this->content[] = $v;
	}
	
	public function getContent(){
		return $this->content;
	}
	
	public function clear(){
		$this->initialise();
	}
	
	public function setInternalCss($css){
		$this->internalCss = $css;
	}
	
	public function getInternalCss(){
		return $this->internalCss;
	}
	
	public function setInternalJavascript($js){
		$this->internalJavascript = $js;
	}
	
	public function getInternalJavascript(){
		return $this->internalJavascript;
	}
	
	public function setStructured($v){
		$this->structured = $v;
	}
	
	public function getStructured(){
		return $this->structured;
	}
	
	public function setAjaxPage($v){
		$this->ajaxPage = $v;
	}
	
	public function getAjaxPage(){
		return $this->ajaxPage;
	}
	
	private function getTinyMceJs(){
		$extraButtons = $_SESSION['ADMIN_USER']->getSuperAdmin() ? 'code' : '';
		$adminPath = ADMIN_PATH;
		$js = <<<JAVASCRIPT
		<script language='javascript' type='text/javascript'>
			tinyMCE.init({
					mode : "textareas",
					editor_selector : "tinymce",
					theme : "advanced",
					theme_advanced_buttons1 : "link,unlink,separator,bold,italic,underline,separator,bullist,numlist,separator,image,separator,undo,redo,{$extraButtons}",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					relative_urls : false,
					paste_remove_styles : true,
					paste_auto_cleanup_on_paste : true,
					paste_remove_spans : true,
					plugins : "paste",
					document_base_url : "/",
					external_image_list_url : "/{$adminPath}/images/tinymce_image_list",
					external_link_list_url 	: "/{$adminPath}/pages/tinymce_page_list",
					paste_preprocess : function(pl, o) {
						//o.content = o.content.replace(/<\S[^><]*>/g, "");
						//strip_tags(str, allowed_tags)
						
						//alert(o.content);
						
						////////////////////////////
						// Remove all formatting
						o.content = strip_tags(o.content, "<p><br/>");
						
						//alert(o.content);
					}
				});	
		</script>
JAVASCRIPT;
	return $js;
	}
	
	////////////////////////////////////////
	// breadcrumb should be an array of links
	public function setBreadcrumb($arr){
		if(!is_array($arr)){
			$e = adError::getInstance();
			$e->addClassError("adPage", "setBreadcrumb", "argument 1 must be array, '<xmp>".$arr."</xmp>' given.");
			return false;
		}
		return $this->breadcrumb = $arr;
	}
	
	public function getBreadcrumb(){
		return $this->breadcrumb;
	}
	
	public function getFormattedBreadcrumb(){
		$i = 0;
		$html = "";
		foreach($this->getBreadcrumb() as $item){
			$i++;
			if($i!=1)
				$html .= "<span> &gt; </span>";
			if($i==count($this->getBreadcrumb()))
				$html .= "<span class='last'>".$item."</span>";
			else
				$html .= $item;
		}
		$html = strtolower($html);
		return $html;
	}
	
	////////////////////////////////////////
	// This function returns everything that appears in the page from doctype declaration to breadcrumb,
	// including <head> tag, css, javascript, menu and page wrapper divs
	public function getHeaderHTML($includeStructure = TRUE){
		$this->addCss("adCss/main-screen.css");
		if(count($this->getPopups())>0){
			$this->addCss("adCss/popup-screen.css");
			$this->addJavascript("adJavascripts/popupFunctions.js");
		}
		if($this->getTinyMce()){	
			$this->addJavascript("adJavascripts/tiny_mce/tiny_mce.js");
			$this->addJavascript("adJavascripts/generalFunctions.js");
		}
		$menuItems = $this->getMenuItems();
		
		////////////////////////////////
		//	HTML headers & DTDS
		$html = <<<html
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
html;
		$html .= "\n<base href='http://".$_SERVER['HTTP_HOST']."/".ADMIN_PATH."/' />\n";
		$html .= "\n<title>Site Admin".($this->getTitle()=="" ? "" : " | ".ucWords($this->getTitle()))."</title>\n";

		////////////////////////////////
		//	Javascripts
		if($this->getJQuery())
			$html .= "\n<script type='text/javascript' src='adJavascripts/jquery.js'></script>\n";
		foreach($this->getJavascripts() as $script){
			$html .= "\n<script language='javascript' type='text/javascript' src='".$script."'></script>\n";
		}
		
		////////////////////////////////
		//	Internal Javascripts
		if($this->getInternalJavascript()!=""){
			$html .= "\n<script language='javascript' type='text/javascript'>\n";
				$html .= $this->getInternalJavascript();
			$html .= "\n</script>\n";	
		}
		if($this->getTinyMce()){
			$html .= $this->getTinyMceJs();
		}
		
		////////////////////////////////
		//	Internal CSS
		if($this->getInternalCss()!=""){
			$html .= "\n<style type='text/css'>\n";
				$html .= $this->getInternalCss();
			$html .= "\n</style>\n";	
		}
		

		////////////////////////////////
		//	CSS docs
		foreach($this->getCss() as $css => $media){
			$html .= "\n<link href='".$css."' rel='stylesheet' media='".$media."' type='text/css' />\n";
		}
		$html .= "\n</head>\n";


		////////////////////////////////
		//	Onload		
		$html .= "\n<body onload='".implode(";", $this->getOnloads())."' >\n";	
		if($this->getStructured()){
			////////////////////////////////
			//	Page html tags
			$html .= "<div class='pageWrapper'>";
				$html .= "<div class='header'>";
					$html .= "<ul>";
					foreach($menuItems as $href => $menu){
						$html .= "<li ".(isset($GLOBALS['NAV_PATH']['section']) && $GLOBALS['NAV_PATH']['section']==$href ? "class='selected'" : "").">";
							$html .= "<a href='/".ADMIN_PATH."/".$href."/' title='".ucwords($menu)."'>";
								$html .= ucwords($menu);
							$html .= "</a>";
						$html .= "</li>";
					}
					$html .= "</ul>";
					if($GLOBALS['ADMIN_LOGGED_IN']==TRUE)
						$html .= "<a href='?logout=1' title='Logout' class='logout'>Logout</a>";
					else
						$html .= "<span class='logout'></span>";
					$html .= "<div>Admin</div>";
				$html .= "</div>";
				$html .= "<div class='breadcrumb'>";
						$html .= "location: ".$this->getFormattedBreadcrumb();;
				$html .= "</div>";
				$html .= "<div class='contentWrapper'>";
		}
		return $html;
	}
	
	////////////////////////////////////////
	// Closes off any page wrapper divs and outputs popups (including search results panel)
	public function getFooterHTML(){
		$html = "";
		if($this->getStructured()){
				$html .= "\n</div>";//end div#contentWrapper
				$html .= "\n</div>";//end div#pageWrapper
		}
		if(count($this->getPopups())>0){
			$html .= $this->getPopupsHTML();
		}
		$html .= "\n\n</body>";
		$html .= "\n</html>";
		return $html;
	}
	
	////////////////////////////////////////
	// returns $this->content array as one long string
	public function getContentHTML($includeNewLines = TRUE){
		$newline = $includeNewLines ? "\n" : '';
		$html = $newline;
		$html .= implode($newline, $this->getContent());
		$html .= $newline;
		return $html;
	}
	
	public function getMenuItems(){
		$m = array_merge(array("home" => "home"), $GLOBALS['SECTIONS']);
		if($GLOBALS['ADMIN_LOGGED_IN'] && $_SESSION['ADMIN_USER']->getSuperAdmin())
			$m['super_admin'] = "Super Admin";
		return $m;
	}
	
	////////////////////////////////////////
	// outputs the page. For the majority of the site it is this function that outputs to the screen.
	// These should be the only echo statements in the code. 
	public function printPage($includeStructure = TRUE, $ignoreErrors = FALSE){
		if($this->getAjaxPage()){
			return $this->printBarePage();
		}
		
		$adError = adError::getInstance();
		if($ignoreErrors || !$adError->areErrors()){
			foreach($this->getHeaders() as $header){
				header($header);
			}
			echo $this->getHeaderHTML($includeStructure);
			echo $this->getContentHTML();
			echo $this->getFooterHTML();
			return true;
		}
		else
			return false;
	}
	
	////////////////////////////////////////
	// outputs the page without HTML headers (e.g. no <html><head>.... tags)
	public function printBarePage(){
		echo $this->getContentHTML();
		return true;
	}
	
}
?>