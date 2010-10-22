<?php
	class db_cabinate extends _db_object{
		protected $files;
		
		public function openFromTitle($title){
			return $this->openFromCol("cabTitle", $title);
		}
		
		public function getIsFull(){
			if($this->getLimit()==0)
				return false;
			else{
				$dbFiles = $this->getFiles();
				return count($dbFiles)>=$this->getLimit();
			}
		}
		
		public function getFiles($force = FALSE, $orderBy = "filDateUploaded"){
			if($this->files==null || $force){
				$dblink = dbLink::getInstance();
				$files = $dblink->getObjects("file", "filCabId=?", "i", array($this->getId()), $orderBy);
				$this->setFiles($files);
			}
			return $this->files;
		}
		
		private function setFiles($files){
			$this->files = $files;
		}
		
		public function delete(){
			foreach($this->getFiles() as $fil){
				$fil->delete();
			}
			parent::delete();
		}
	}
?>