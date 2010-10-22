<?php
	class db_dimension extends _db_object{
			
		public function getString(){
			return $this->getWidth."x".$this->getHeight();
		}
		
		public function getQuality(){
			if($this->quality==null)
				$this->quality = 100;
			
			return $this->quality;	
		}
	}
?>