<?php
function printVersionUpgrade(){
	$html = <<<CONTENT
	<b>Version upgrade required</b>
	<hr />
	<p>New field - field_cabinate. No db changes</p>
	
	
	<pre><xmp>

</xmp></pre>
	<hr />
CONTENT;
	
	$p = adPage::getInstance();
	$p->setStructured(FALSE);
	$p->addContent($html);
	$p->printPage();
	exit;
}
?>