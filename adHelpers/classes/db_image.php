<?php
	class db_image extends _db_object{
		protected $dimensions;
		protected $gallery;
		
		public function uploadFile($fileRef){
			if($this->getId()<1){
				$adError = adError::getInstance();
				$adError->addClassError("db_image", "uploadFile", "Image must be saved before file uploaded");
				$adError->printErrorPage();
			}
			
			$dims = $this->getDimensions();
			if(count($dims)==0){
				$adError = adError::getInstance();
				$adError->addClassError("db_image", "uploadFile", "cannot upload file as there are no Dimensions to create images");
				$adError->printErrorPage();
			}
			
			if(is_array($fileRef))
				$moved = fileUtility::moveUploadedFile($fileRef, SITE_IMAGE_PATH, $this->getFilename(0, 0, false));
			else
				$moved = fileUtility::move($fileRef, SITE_IMAGE_PATH.$this->getFilename(0, 0, true));
			if(!$moved)				
				return false;
				
		
			$image = new image($dims);
			$image->setDbImage($this);
			return $image->createImages();
			
		}
		
		public function getDimensions($force = FALSE){
			if($this->dimensions==null || $force){
				$this->dimensions = $this->getGallery($force)->getDimensions($force);
			}
			return $this->dimensions;
		}
		
		private function getGallery($force = FALSE){
			if($this->gallery==null || $force){
				$this->gallery = new db_gallery($this->getGalId());
			}
			return $this->gallery;
		}
		
		public function getFilenameWithPath($w, $h, $incExtension){
			return SITE_IMAGE_PATH.$this->getFilename($w, $h, $incExtension);
		}
		
		private function getFilename($w, $h, $incExtension){
			$filename = "img_".(sprintf("%04d", $this->getId()))."_".$w."x".$h;
			if($incExtension)
				$filename .= ".".fileUtility::getExtension($this->getUploadedFileName());
			return $filename;
		}
		
		public function delete(){
			$dims = $this->getDimensions();
			
			//////////////////////////////////
			// We need to delete orig size too
			$origSizeDim = new db_dimension();
			$origSizeDim->setWidth(0);
			$origSizeDim->setHeight(0);
			$dims[] = $origSizeDim;
			
			$image = new image($dims);
			$image->setDbImage($this);
			$image->deleteImages();
			parent::delete();
		}
	}
?>