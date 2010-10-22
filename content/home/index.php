<?php
$dblink=dblink::getInstance();

	$html = getWelcomePage();
	$p = adPage::getInstance();
	$p->setTitle("home");
	$p->setBreadcrumb(array("home"));
	$p->addCss("adCss/home/index-screen.css");
	$p->addContent($html);
	$p->printPage();
	
///////////////////////////////////////////////////////////////////////

function getWelcomePage(){
	$username = isset($_GET['login_admin']['login']) ? $_GET['login_admin']['login'] : "";
	$version = $_SESSION['ADMIN_USER']->getSuperAdmin() ? "<p class='superAdmin'>Version: ".YOURSITE_VERSION."</p>" : "";
	$html = <<<HTML
	<div class='fullWidth'>
		<p>Welcome to your website's admin system. Access areas using the menu at the top of the screen.</p>
		<dl>
			<dt><b>Pages</b></dt>
			<dd>The pages section allows you to edit/add text and images on your website.</dd>
			<dt><b>Menu</b></dt>
			<dd>The menu section allows you to modify the links that appear in your menu and the order they appear in. Menus are generally linked to a page created in the Pages section.</dd>
			<dt><b>Images</b></dt>
			<dd>The images section allows you to upload images onto the site. These images can then be added to page content in the Pages section.</dd>
			<dt><b>Help</b></dt>
			<dd>When you see (?) you can click the question mark for a brief explanation about the task you're performing</dd>
		</dl>
	</div>
HTML;
	
	return $html;
}
?>