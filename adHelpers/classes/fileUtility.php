<?php
	class fileUtility{
		
		public static function moveUploadedFile($fileArr, $newLoc, $newNameWithoutExt){
			$ext = fileUtility::getExtension($fileArr['name']);
			$to = $newLoc.$newNameWithoutExt.".".$ext;
			return (@move_uploaded_file($fileArr['tmp_name'], $to));
			
		}
		
		public static function getExtension($name){
			return @substr(strrchr($name, '.'), 1);
		}
		
		public static function copy($from, $to){
			//echo "<br/>copying ".$from." to ".$to;
			return @copy($from, $to);
		}
		
		public static function move($from, $to){
			//echo "<br/>moving ".$from." to ".$to;
			
			///////////////////////////////////////////
			// make sure destination dir doesn't already have file of that name by deleting without backup
			self::delete($to, FALSE);
			return @rename($from, $to);
		}
		
		public static function dirExists($folderWithPath){
			return @is_dir($folderWithPath);
		}
		
		public static function exists($file){
			return @file_exists($file);
		}
		
		public static function size($fileWithPath){
			return @filesize($fileWithPath);
		}
		
		public static function delete($fileWithPath, $backup = TRUE){
			if($backup){
				return self::move($fileWithPath, SITE_TRASH_PATH.date("Ymd")."-".self::getNameFromPath($fileWithPath));
			}
			else{
				@unlink($fileWithPath);
			}
		}
		
		public static function getNameFromPath($fileWithPath){
			$parts = explode("/", $fileWithPath);
			if(count($parts)<2)
				$parts = explode("\\", $fileWithPath);
			
			return end($parts);
		}
		
		public static function unzip($zipWithPath, $saveLoc){
			self::checkDir($saveLoc, true);
			$zip = zip_open($zipWithPath);
			$extractedFiles = array();
			while ($zippedFile = zip_read($zip)){
				$file = basename(zip_entry_name($zippedFile));
				
				$unZippedFile = fopen($saveLoc."/".$file, "w+");//set the file ($file) to read/write access. If file doesn't exist (as in this case), create it. We have now created an empty file with same name as $zippedFile
				
				if (zip_entry_open($zip, $zippedFile, "r")) {//open file ($zippedFile) that is in a zip folder ($zip) for read access
					$copyOfZippedFile = zip_entry_read($zippedFile, zip_entry_filesize($zippedFile)); //copy zippedFile from start to end (which is the file's uncompressed size) into copyOfZippedFile
					zip_entry_close($zippedFile); //close zippedFile
				}
				else{
					return false;
				}
				fwrite($unZippedFile, $copyOfZippedFile);//write the copy of the zipped file into the empty unzipped file
				$extractedFiles[] = $file;
				fclose($unZippedFile);
			}
			return $extractedFiles;
		}
		
		public static function checkDir($path, $forceCreate = false){
			$exists = fileUtility::dirExists($path);
			if(!$exists && $forceCreate){
				$exists = fileUtility::createDir($path);
			}
			
			return $exists;
		}
			
		
		public static function createDir($folderWithPath){
			return @mkdir($folderWithPath, 0777, true);
		}
	}
?>