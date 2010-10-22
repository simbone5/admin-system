<?php
	class db_board extends _db_object{
		
		public function openFromTitle($title){
			return $this->openFromCol("boaTitle", $title);
		}
		
		public function getLatestBulletin(){
			$buls = $this->getBulletins("bulDate");
			
			if(count($buls)>0)
				$bul = array_shift($buls);
			else
				$bul = false;
			
			return $bul;
		}
		
		public function getBulletins($order = "bulDate"){
			$dblink = dblink::getInstance();
			$buls = $dblink->getObjects("bulletin", "bulBoaID=?", "i", array($this->getId()), $order);
			
			return $buls;
		}
		
		public function getVisibleBulletins($order = "bulDate"){
			$dblink = dblink::getInstance();
			$buls = $dblink->getObjects("bulletin", "bulBoaID=? AND bulVisibleFrom<=NOW() AND bulVisibleTo>=Now()", "i", array($this->getId()), $order);
			
			return $buls;
		}
		
		public function getIsFull(){
			if($this->getLimit()==0)
				return false;
			else{
				$buls = $this->getBulletins();
				return count($buls)>=$this->getLimit();
			}
		}

	}
?>