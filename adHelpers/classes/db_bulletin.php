<?php
	class db_bulletin extends _db_object{
		public function getIsVisible($date = -1){
			$date = $date==-1 ? date("U") : $date;
			//echo "<br />".date("d/m/Y", $date) .">=". date("d/m/Y", $this->visibleFrom) ." && ". date("d/m/Y", $date) ."<=". date("d/m/Y", $this->visibleTo);
			return $date >= $this->getVisibleFrom() && $date <= $this->getVisibleTo();
		}
		
		private function getBulletinDates(){
			$dblink = dblink::getInstance();
			$bDates = $dblink->getObjects("bulletindate", "bdaBulID=?", "i", array($this->getId()), "bdaDate");
			
			return $bDates;
		}
		
		public function getBulletinDatesEpochs(){
			$epochs = array();
			foreach($this->getBulletinDates() as $bDate){
				$epochs[$bDate->getId()] = $bDate->getDate();
			}
			return $epochs;
		}
		
	}
?>