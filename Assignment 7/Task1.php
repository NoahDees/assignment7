<?php
class jsonhelper{
	private static $obfuscator='<?php die() ?>';

	static function read($jsonfile,$offset=null,$limit=null,$skipblanks=false){
		if(!file_exists($jsonfile)){
			return [];
		}
		if(!isset(PATHINFO($jsonfile)['extension'])){
			return [];
		}
		$rows=json_decode(strtolower(PATHINFO($jsonfile)['extension'])=='php' ? trim(str_replace(self::$obfuscator,'',file_get_contents($jsonfile))) : file_get_contents($jsonfile),true);
		if(json_last_error()!=JSON_ERROR_NONE){
			self::reset($jsonfile);
			return [];
		}
		if(!isset($offset)){
			return $rows;
		}
		$count=0;
		$started=false;
		$out=[];
		$is_assoc=self::is_assoc($rows); 
		foreach($rows as $k=>$v){
			if($k==$offset){
				$started=true;
			}
			if($started){
				$out[$k]=$v;
				$count++;
				if($count==$limit){
					break;
				}
			}
		}
		return $out;
	}

	static function write($jsonfile,$input,$assoc=false,$overwrite=false){
		if(!isset($input)){
			return false;
		}
		$rows = [];
		if(!$overwrite && file_exists($jsonfile)){
			$rows=json_decode(strtolower(PATHINFO($jsonfile)['extension'])=='php' ? trim(preg_replace('/^'.preg_quote(self::$obfuscator).'/','',file_get_contents($jsonfile))) : file_get_contents($jsonfile),true);
		}
		if(json_last_error()!=JSON_ERROR_NONE){
			self::reset($file);
			return false;
		}
		if(!$assoc){
			if(isset($input[0])){
				foreach($input as $row){
					$rows[] = $row;
				}
			}
			else{
				$rows[] = $input;
			}
		}
		else{
			foreach($input as $k=>$v){
				$rows[$k]=$v;
			}
		}
		$file = fopen($jsonfile,'w+');
		if(!flock($file,LOCK_EX|LOCK_NB)){
			return false;
		}
		if(strtolower(PATHINFO($jsonfile)['extension'])=='php'){
			fwrite($file,self::$obfuscator."\n");
		}
		fwrite($file,json_encode($rows));
		fclose($file);
		return true;
	}

	static function update($jsonfile,$index,$replace,$overwrite=true){
		if(!file_exists($jsonfile) || !isset($replace) || !isset($index)){
			return false;
		}
		$rows = json_decode(strtolower(PATHINFO($jsonfile)['extension'])=='php' ? trim(preg_replace('/^'.preg_quote(self::$obfuscator).'/','',file_get_contents($jsonfile))) : file_get_contents($jsonfile),true);
		if(json_last_error()!=JSON_ERROR_NONE){
			self::reset($jsonfile);
			return false;
		}
		if(!isset($rows[$index])){
			return false;
		}
		$rows[$index] = $overwrite ? $replace : array_merge($rows[$index],$replace);
		if(!flock($file = fopen($jsonfile,'w+'),LOCK_EX|LOCK_NB)){
			return false;
		}
		if(strtolower(PATHINFO($jsonfile)['extension'])=='php'){
			fwrite($file,self::$obfuscator."\n");
		}
		fwrite($file,json_encode($rows));
		fclose($file);
		return true;
	}

	static function delete($jsonfile,$index=null,$assoc=false,$wipe=false){
		if(!file_exists($jsonfile)){
			return false;
		}
		if(!isset($index)){
			return unlink($jsonfile);
		}
		$rows=json_decode(strtolower(PATHINFO($jsonfile)['extension'])=='php' ? trim(preg_replace('/^'.preg_quote(self::$obfuscator).'/','',file_get_contents($jsonfile))) : file_get_contents($jsonfile),true);
		if(json_last_error()!=JSON_ERROR_NONE){
			self::reset($jsonfile);
			return false;
		}
		if(is_array($index)){
			foreach($index as $i){
				if($wipe){
					unset($rows[$i]);
				}
				else{
					$rows[$i] = [null];
				}
			}
		}
		else{
			if($wipe){
				unset($rows[$index]);
			}
			else{
				$rows[$index]=[null];
			}
		}
		if(!$assoc){
			$rows=array_values($rows);
		}
		if(!flock($file=fopen($jsonfile,'w+'),LOCK_EX|LOCK_NB)){
			return false;
		}
		if(strtolower(PATHINFO($jsonfile)['extension'])=='php'){
			fwrite($file,self::$obfuscator."\n");
		}
		fwrite($file,str_replace('[null]','{}',json_encode($rows)));
		fclose($file);
		return true;
	}

	private static function is_assoc($array){
		$keys=array_keys($array);
		return $keys!==array_keys($keys);
	}
	
	private static function reset($file){
		if(file_exists($file)) rename($file,str_replace('.json','_backup_'.date('Y-m-d_h_i_s').'.json',$file));
	}
}

class csvhelper{
	static function read($csvfile){
		$f = fopen($csvfile,"r");
		while ($record = fgetcsv($f)) {
			$arr[] = $record;
		}
	fclose($f);
	return $arr;
	}

	static function write($csvfile,$input){
		$f = fopen($csvfile,"a");
		fputcsv($f,$input);
		fclose($f);
	}

	static function update($csvfile,$searchindex,$replace){
	$infoarray = array($replace);
	$counter = 0;
	$f = fopen($csvfile,"r");
	while ($record = fgetcsv($f)){
		if ($counter == $searchindex){
			$arr[] = $replace;
		}
		else{
			$arr[] = $record;
		}
		$counter++;
	}
	fclose($f);

	$counter = 0;
	$fw = fopen($csvfile,"w");
	foreach($arr as $rows){
		fputcsv($fw,$rows);
	}
	fclose($fw);
	return $arr;
	}

	static function delete($csvfile,$searchindex,$wipe=false){
	$counter = 0;
	$f = fopen($csvfile,"r");
	while ($record = fgetcsv($f)){
		if ($counter != $searchindex){
			$arr[] = $record;
		}
		$counter++;
	}
	return $arr;
	fclose($f);

	$fw = fopen($csvfile,"w");
	foreach($arr as $rows){
		fputcsv($fw,$rows);
	}
	fclose($fw);
	return $arr;
	}
}

if(count($_POST)>0 && isset($_POST['action']{0})){
	if($_POST['action']=='signup') authhelper::signup();
	if($_POST['action']=='signin') authhelper::signin();
}

class authhelper{
	static function signin(){
		if(!file_exists('userslogin.csv')){
			die("The user isn't registered");
		}
		$file = fopen('userslogin.csv','r+');
		while(!feof($file)){
			$line = explode(':',trim(fgets($file)));
			if(count($line)<2){
				continue;
			}
			if($line[0]==$_POST['email']){
				if(!password_verify($_POST['password'],$line[1])){
					die("The password is incorrect");
				}
				else{
					session_start();
					header('location: test_logged_authhelper.php');
				}
			}
		}
		fclose($file);
		die('The email address isn\'t registered');
	}

	static function signup(){
		if(!file_exists('userslogin.csv')){
			$file = fopen('userslogin.csv','w+');
		}
		else{
			$file = fopen('userslogin.csv','r+');
		}
		while(!feof($file)){
			$line = explode(';',trim(fgets($file)));
			if(count($line)<2){
				continue;
			}
			if($line[0]==$_POST['email']){
				die('The email address is in our system');
			}
		}
		fclose($file);
		$file=fopen('userslogin.csv','a+');
		fwrite($file,$_POST['email'].':'.password_hash($_POST['password'],PASSWORD_DEFAULT).PHP_EOL);
		fclose($file);
		header('location: test_logged_authhelper.php');
	}

	static function signout(){
		session_start();
		session_destroy();
		header('location: test_authhelper.php');
	}
}

class entities{

}
?>