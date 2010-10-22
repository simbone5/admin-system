<?php
	class db_gallery extends _db_object{
		protected $dimensions;
		protected $type;
		protected $images;
		
		public function openFromTitle($title){
			return $this->openFromCol("galTitle", $title);
		}
		
		public function getDimensions($force = FALSE){
			if($this->dimensions==null || $force){
				$this->dimensions = $this->getType($force)->getDimensions($force);
			}
			return $this->dimensions;
		}
		
		private function getType($force = FALSE){
			if($this->type==null || $force){
				$this->type = new db_type($this->getTypId());
			}
			return $this->type;
		}
		
		public function getIsFull(){
			if($this->getLimit()==0)
				return false;
			else{
				$dbImages = $this->getImages();
				return count($dbImages)>=$this->getLimit();
			}
		}
		
		public function getImages($force = FALSE, $orderBy = "imgDateUploaded"){
			if($this->images==null || $force){
				$dblink = dbLink::getInstance();
				$images = $dblink->getObjects("image", "imgGalId=?", "i", array($this->getId()), $orderBy);
				$this->setImages($images);
			}
			
			return $this->images;
		}
		
		private function setImages($images){
			$this->images = $images;
		}
		
		public function delete(){
			foreach($this->getImages() as $img){
				$img->delete();
			}
			parent::delete();
		}
	}
?>