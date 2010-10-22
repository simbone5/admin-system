<?php
	class db_file extends _db_object{
		protected $cabinate;
		
		public function uploadFile($fileArr){
			if($this->getId()<1){
				$adError = adError::getInstance();
				$adError->addClassError("db_file", "uploadFile", "File must be saved before file uploaded");
				$adError->printErrorPage();
			}
			
			return fileUtility::moveUploadedFile($fileArr, SITE_FILE_PATH, $this->getFilename(0, 0, false));
	
		}
		
		private function getCabinate($force = FALSE){
			if($this->cabinate==null || $force){
				$this->cabinate = new db_cabinate($this->getCabId());
			}
			return $this->cabinate;
		}
		
		public function getFilenameWithPath($incExtension = TRUE){
			return SITE_FILE_PATH.$this->getFilename($incExtension);
		}
		
		private function getFilename($incExtension){
			$filename = "fil_".(sprintf("%04d", $this->getId()));
			if($incExtension)
				$filename .= ".".fileUtility::getExtension($this->getUploadedFileName());
			return $filename;
		}
		
		public function getExists(){
			return fileUtility::exists($this->getFilenameWithPath());
		}
		
		public function deleteFile(){
			return fileUtility::delete($this->getFilenameWithPath());
		}
		
		public function download(){
			if(!$this->getExists())
				return false;
				
			header("Content-type: ".stripslashes($this->getMimeType()));
			header("Content-length: ".fileUtility::size($this->getFilenameWithPath()));
			header('Content-disposition: attachment; filename="'.stripslashes($this->getUploadedFileName()).'"');
			readfile($this->getFilenameWithPath()); 
			return true;
		}
		
		public function delete(){
			
			fileUtility::delete($this->getFilenameWithPath());
			parent::delete();
		}
	}
?>