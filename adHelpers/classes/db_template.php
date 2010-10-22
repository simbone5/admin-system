<?php
	class db_template extends _db_object{
	
		public function printTemplate(){
			$templateFile = "../".SITE_FOLDER."/templates/".$this->getFilename();
			if(file_exists($templateFile))
				require $templateFile;
			else
				printFrontEndError( "db_template::printTemplate error - template file not found" );
		}
		
	}
?>