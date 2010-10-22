<?php
function printVersionUpgrade(){
	$html = <<<CONTENT
	<b>Version upgrade required</b>
	<hr />
	<p>Multiple dates for bulletins, file description field</p>
	
	
	<pre><xmp>
CREATE TABLE `bulletindate` (
`bdaID` INT NOT NULL AUTO_INCREMENT ,
`bdaBulID` INT NOT NULL ,
`bdaDate` DATETIME NOT NULL ,
PRIMARY KEY ( `bdaID` )
) ENGINE = InnoDB;

ALTER TABLE `bulletindate` ADD INDEX ( `bdaBulID` ) 

ALTER TABLE `bulletindate` ADD FOREIGN KEY ( `bdaBulID` ) REFERENCES `treasurehunt`.`bulletin` (
`bulID`
) ON DELETE CASCADE ON UPDATE CASCADE ;

INSERT INTO bulletindate SELECT NULL, bulBoaID, bulDate FROM bulletin

ALTER TABLE `file` ADD `filDescription` TEXT NOT NULL 
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