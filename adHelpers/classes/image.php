<?php
	class image{
		private $dimensions;
		private $dbImage;
		
		public function __construct($dims){
			$this->setDimensions($dims);
		}
		
		public function setDbImage($obj){
			$this->dbImage = $obj;
		}
		
		public function getSrc($dimNum = 0){
			$dims = $this->getDimensions();
			if(!isset($dims[$dimNum])){
				$adError = adError::getInstance();
				$adError->addClassError("image", "getSrc", "requested dimension '".$dimNum."', which is not set");
				$adError->printErrorPage();
			}
			
			$dim = $dims[$dimNum];
			$dbImage = $this->getDbImage();
			$path = $dbImage->getFilenameWithPath($dim->getWidth(), $dim->getHeight(), TRUE);
			if(fileUtility::exists($path))
				return $path;
			
			
			/////////////////////////////////////////// 
			// If here try to create an image of right size from original
			// CHANGED MY MIND: don't create a new size. Each image size must be present in the gallery's type's dimensions
			// This is so that when new image is uploaded all size variations can be determined and updated.
			/*$from = $dbImage->getFileNameWithPath(0, 0, true);
			$to = $dbImage->getFileNameWithPath($dim->getWidth(), $dim->getHeight(), true);
			if(!fileUtility::copy($from, $to)){
				return $this->getImageNotFoundSrc($dim);
			}
			$this->resizeImage($to, $dim);
			if(fileUtility::exists($path))
				return $path;
			*/
			
			
			/////////////////////////////////////////// 
			// If here we need to return "image not found" src cus trying to create image has failed
			return $this->getImageNotFoundSrc($dim);
		}
		
		private function getDbImage(){
			return $this->dbImage;
		}
		
		private function setDimensions($dims){
			if(!is_array($dims))
				$dims = array($dims);
			$this->dimensions = $dims;
		}
		
		private function getDimensions(){
			return $this->dimensions;
		}
		
		public function getNameWithDimensions($dimNum = 0){
			$name = htmlentities(stripslashes($this->getDbImage()->getName()), ENT_QUOTES);
			
			$dims = $this->getDimensions();
			if(!isset($dims[$dimNum])){
				$adError = adError::getInstance();
				$adError->addClassError("image", "getNameWithDimensions", "requested dimension '".$dimNum."', which is not set");
				$adError->printErrorPage();
			}
			
			$dim = $dims[$dimNum];
			if($dim->getWidth()==0 && $dim->getHeight()==0)
				$dimString = "original";
			elseif($dim->getWidth()==0)
				$dimString = "h: ".$dim->getHeight();
			elseif($dim->getHeight()==0)
				$dimString = "w: ".$dim->getWidth();
			else
				$dimString = $dim->getWidth()."x".$dim->getHeight();
				
			return $name." [".$dimString."]";
			
		}
		
		private function getImageNotFoundSrc($dim){
			$path = SITE_IMAGE_PATH."img_not_found_".$dim->getWidth()."x".$dim->getHeight().".jpg";
			if(fileUtility::exists($path))
				return $path;
				
				
			/////////////////////////////////////////// 
			// If here we need to resize the "image not found" image
			$from = SITE_IMAGE_PATH."img_not_found_0x0.jpg";
			$to = SITE_IMAGE_PATH."img_not_found_".$dim->getWidth()."x".$dim->getHeight().".jpg";
			if(!fileUtility::copy($from, $to)){
				return "cannot_copy_image-not-found-jpg";
			}
			$this->resizeImage($to, $dim);
			if(fileUtility::exists($path))
				return $path;
			
			/////////////////////////////////////////// 
			// If here we're screwed
			return "failed_to_resize_image-not-found-jpg";
		}
		
		public function createImages(){
			if($this->getDbImage()==null){
				$adError = adError::getInstance();
				$adError->addClassError("image", "createImages", "no db_image object set");
				$adError->printErrorPage();
				return false;
			}
			
			$dbImage = $this->getDbImage();
			$dims = $dbImage->getDimensions();
			///////////////////////////////
			//loop through the dimensions to create the different sizes of photos
			foreach($dims as $dim){
				
				///////////////////////////////
				//copy original
				$from = $dbImage->getFileNameWithPath(0, 0, true);
				$to = $dbImage->getFileNameWithPath($dim->getWidth(), $dim->getHeight(), true);
				if(!fileUtility::copy($from, $to)){
					return false;
				}
				
				///////////////////////////////
				//	resize
				$this->resizeImage($to, $dim);
				
			}
			
			return true;
		}
		
		private function resizeImage($toResize, $dim){
			list($width_orig, $height_orig) = getimagesize($toResize);
		
	
			////////////////////////////////////
			// Invalid image! 0x0
			if($width_orig==0 || $height_orig==0){
				return false;
			}
			
			////////////////////////////////////
			// Determine new width & height
			$width = $dim->getWidth();
			$height = $dim->getHeight();
			if(($height==0 && $width==0) || ($height==$height_orig && $width==$width_orig))
				return true;
			elseif($width==0 || (($width/$width_orig)==($height/$height_orig) && $height_orig>$width_orig) || (($width/$width_orig)>($height/$height_orig) && $height!=0))
				$width = ($height/$height_orig)*$width_orig;
			elseif($height==0 || (($width/$width_orig)==($height/$height_orig) && $height_orig<$width_orig) || (($width/$width_orig) < ($height/$height_orig) && $width!=0))
				$height = ($width/$width_orig)*$height_orig;
			
			
			////////////////////////////////////
			// Resample
			$image_p = @imagecreatetruecolor($width, $height);
			switch(strtolower($this->getDbImage()->getMimeType())){
				case "image/png":
					$func = "imagecreatefrompng";
					break;
				case "image/gif":
					$func = "imagecreatefromgif";
					break;
				default:
					$func = "imagecreatefromjpeg";
					break;
			}
			$image = @$func($toResize);
			@imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			
			////////////////////////////////////
			// Save output
			@imagejpeg($image_p, $toResize, $dim->getQuality());
			return true;
		}
		
		public function deleteImages(){
			if($this->getDbImage()==null){
				$adError = adError::getInstance();
				$adError->addClassError("image", "deleteImages", "no db_image object set");
				$adError->printErrorPage();
				return false;
			}
			
			$dbImage = $this->getDbImage();
			$dims = $this->getDimensions();
			///////////////////////////////
			//loop through the dimensions
			foreach($dims as $dim){
				
				///////////////////////////////
				//delete
				$path = $dbImage->getFileNameWithPath($dim->getWidth(), $dim->getHeight(), true);
				fileUtility::delete($path);
				
			}
			
			return true;
		}
	}
?>