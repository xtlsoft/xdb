<?php
	/** 
	  * 
	  * $index.php @2016/10/23 12:09@
	  * Powered by XDB
	  * Author : xtl
	  * SITE   : zqjs.tk
	  * 
	  */
	
	define("xdb",true);
	
	$configDir = dirname(__FILE__).'/';
	include("{$configDir}xdb.ini.php");
	include("{$configDir}users.ini.php");
	
	class xdb{
		protected $ulogin = array();
		protected $temp = array( "where" => array(null,null) );
		protected $dbs = array();
		protected $user = array();
		protected $uDB;
		protected $select1;
		protected $select2;
		protected $select3;
		public function __construct($passcode,$password,$username="root"){
			if(substr(md5($passcode),8,16) != $GLOBALS['INI']['XDBCFG']['PASS']) $this->ulogin = array(false,"PASSCODE WRONG");
			$this->temp['ufunction'] = "_xdb_user_".$username;
			if(!function_exists($this->temp['ufunction'])) $this->ulogin = array(false,"USER NOT FOUND");
			$this->temp['uinfo'] = $this->temp['ufunction']();
			$this->temp['password'] = $this->md5PWD($password);
			if($this->temp['password'] != $this->temp['uinfo']['PWD']) $this->ulogin = array(false,"PASSWORD WRONG");
			if($this->ulogin == false){
				$this->user = array("username" => $username, "PER" => $this->temp['uinfo']['PER']);
				$this->ulogin = array(true,$this->user);
				$this->loadDBs();
			}
		}
		public function getLogin(){
			return $this->ulogin;
		}
		protected function md5PWD($PWD){
			return md5(md5(md5(md5($PWD)."X")."D")."B");
		}
		protected function loadDBs(){
			$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA']."/xdb.php");
			$this->dbs = explode("\n",$this->temp['dbsfile']);
			$this->dbs[0] = "system";
		}
		public function showDB(){
			if(($this->user['PER']>=2)){
				return $this->dbs;
			}else{
				return null;
			}
		}
		public function showTBL(){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($this->uDB,$this->dbs)){
					return null;
				}else{
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/db.php");
					$this->temp['tables'] = explode("\n",$this->temp['dbsfile']);
					$this->temp['tables'][0] = "system";
					return $this->temp['tables'];
				}
			}else{
				return null;
			}
		}
		public function DB($database){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $database == $this->user['username'])){
				if(!in_array($database,$this->dbs)){
					return false;
				}else{
					$this->uDB = $database;
					$this->showTBL();
					return $this;
				}
			}else{
				return false;
			}
		}
		public function where($need="%",$string="%"){
			$this->temp['where'] = array($need,$string);
			return $this;
		}
		public function select($table){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($table,$this->temp['tables'])){
					return false;
				}else{
					$this->temp['selected'] = array();
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php");
					$this->temp['dbsfile'] = str_replace("<?php /*XDB*/ if(!defined(\"XDB\")) die(\"Access denied.\"); ?>,","",$this->temp['dbsfile']);
					$this->temp['select'] = explode("/",$this->temp['dbsfile']);
					$this->temp['stemp']['count'] = count(explode(",",$this->temp['select'][0]))-2;
					$this->temp['stemp']['head'] = explode(",",$this->temp['select'][0]);
					$this->temp['int1']=1;
					foreach($this->temp['select'] as $this->temp['sfor']){
						$this->temp['sexpf'] = explode(",",$this->temp['sfor']);
						if(!($this->temp['sexpf'] == $this->temp['stemp']['head'])){
							$this->temp['int'] = 0;
							$this->temp['selected'][$this->temp['int1']]['id'] = $this->temp['int1'];
							while($this->temp['int'] <= $this->temp['stemp']['count']){
								$this->select1 = $this->temp['int'];
								$this->select2 = $this->temp['stemp']['head'][$this->select1];
								$this->temp['selected'][$this->temp['int1']][$this->select2] = urldecode(str_replace("<br>","\n",$this->temp['sexpf'][$this->select1]));
								++$this->temp['int'];
							}
							++$this->temp['int1'];
						}
					}
					if($this->temp['where'][0]!=null){
						if($this->temp['where'][0] == "id"){
							@$this->temp['return'] = $this->temp['selected'][$this->temp['where'][1]];
							return array($this->temp['return']);
						}else{
							$this->temp['int1']=1;
							$this->temp['return'] = array();
							$this->temp['where2'] = false;
							foreach($this->temp['selected'] as $this->temp['whereFor']){
								foreach($this->temp['whereFor'] as $this->temp['whereForKey'] => $this->temp['whereFor1']){
									if(preg_match("/^".str_replace("%","[\s\S]*",$this->temp['where'][1])."$/",$this->temp['whereFor1'])){
										if(preg_match("/^".str_replace("%","[\s\S]*",$this->temp['where'][0])."$/",$this->temp['whereForKey'])){
											$this->temp['where2'] = true;
										}
									}
								}
								if($this->temp['where2']){
									$this->temp['return'][] = $this->temp['selected'][$this->temp['int1']];
								}
								$this->temp['where2'] = false;
								++$this->temp['int1'];
							}
							if($this->temp['return']){
								return $this->temp['return'];
							}else{
								return false;
							}
						}
					}else{
						return $this->temp['selected'];
					}
					unset($this->temp['where']);
				}
			}else{
				return null;
			}
		}
		public function insert($table,$dataArr){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($table,$this->temp['tables'])){
					return false;
				}else{
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php");
					$this->temp['int'] = 0;
					foreach($dataArr as $this->temp['rubbishNum1']){
						$dataArr[$this->temp['int']] = str_replace("\n","<br>",urlencode($dataArr[$this->temp['int']]));
						++$this->temp['int'];
					}
					$this->temp['dbsfile'].="/".implode(",",$dataArr).',';
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php",$this->temp['dbsfile']);
					return true;
				}
			}else{
				return false;
			}
		}
		public function update($table,$key,$value){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($table,$this->temp['tables'])){
					return false;
				}else{
					$this->temp['updselect'] = $this->select($table);
					$this->temp['updselect1'] = $this->where($key[0],$key[1])->select($table);
					$this->temp['count'] = count($this->temp['updselect1']);
					$this->temp['int2'] = 0;
					while($this->temp['int2']+1 <= $this->temp['count']){
						$this->temp['updid'] = $this->temp['updselect1'][$this->temp['int2']]['id'];
						$this->temp['updselect'][$this->temp['updid']][$value[0]] = $value[1];
						++$this->temp['int2'];
					}
					$this->temp['dbsfile'] = "<?php /*XDB*/ if(!defined(\"XDB\")) die(\"Access denied.\"); ?>,";
					$this->temp['updkeys'] = array();
					foreach($this->temp['updselect'][1] as $this->temp['updfor1'] => $this->temp['rubbishNum1']){
						if($this->temp['updfor1'] != "id"){
							$this->temp['updkeys'][] = $this->temp['updfor1'];
							$this->temp['dbsfile'] .= $this->temp['updfor1'].",";
						}
					}
					foreach($this->temp['updselect'] as $this->temp['updfor']){
						$this->temp['int'] = 0;
						$this->temp['dbsfile'].="/";
						foreach($this->temp['updfor'] as $this->temp['rubbishNum2'] => $this->temp['rubbishNum1']){
							if($this->temp['rubbishNum2'] != "id"){
								$this->temp['updfor'][$this->temp['updkeys'][$this->temp['int']]] = str_replace("\n","<br>",urlencode($this->temp['updfor'][$this->temp['updkeys'][$this->temp['int']]]));
								$this->temp['dbsfile'].=$this->temp['updfor'][$this->temp['updkeys'][$this->temp['int']]].',';
								++$this->temp['int'];
							}
						}
					}
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php",$this->temp['dbsfile']);
					return true;
				}
			}else{
				return false;
			}
		}
		private $i=0;
		public function delete($table,$key){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($table,$this->temp['tables'])){
					return false;
				}else{
					$this->temp['dltselect1'] = $this->where($key[0],$key[1])->select($table);
					if($this->temp['dltselect1'] == false){
						return false;
					}
					foreach($this->temp['dltselect1'][0] as $this->temp['dltfor1'] => $this->temp['rubbishNum1']){
						if($this->temp['dltfor1'] != "id"){
							$this->temp['dltkeys'][] = $this->temp['dltfor1'];
							$this->temp['dbsfile'] .= $this->temp['dltfor1'].",";
						}
					}
					$this->i=0;
					$this->temp['dlttext']="";
					foreach($this->temp['dltselect1'][0] as $this->temp['dltsltfor']){
						if($this->temp['dltsltfor'] != $this->temp['dltselect1'][0]['id']){
							$this->temp['dlttext'] .= urlencode($this->temp['dltsltfor']).',';
							++$this->i;
						}
					}
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php");
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.".db/".$table.".tbl/data.php",str_replace("/".$this->temp['dlttext'],"",$this->temp['dbsfile']));
					
					return true;
				}
			}else{
				return false;
			}
		}
		public function makeDB($DBname){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $DBname == $this->user['username'])){
				if(!in_array($DBname,$this->dbs)){
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/xdb.php');
					$this->temp['dbsfile'] .= "\n".$DBname;
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/xdb.php',$this->temp['dbsfile']);
					mkdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db');
					$this->temp['mkfile'] = fopen($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/db.php',"x+");
					fwrite($this->temp['mkfile'],"<?php /*XDB*/ if(!defined(\"XDB\")) die(\"Access denied.\"); ?>");
					fclose($this->temp['mkfile']);
					mkdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/'.'system.tbl');
					$this->temp['mkfile'] = fopen($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/system.tbl/data.php',"x+");
					fwrite($this->temp['mkfile'],"<?php /*XDB*/ if(!defined(\"XDB\")) die(\"Access denied.\"); ?>,");
					fclose($this->temp['mkfile']);
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		public function makeTBL($TBLname,$dataArr){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(!in_array($TBLname,$this->showTBL())){
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/db.php');
					$this->temp['dbsfile'] .= "\n".$TBLname;
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/db.php',$this->temp['dbsfile']);
					mkdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/'.$TBLname.'.tbl');
					$this->temp['mkfile'] = fopen($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/'.$TBLname.'.tbl/data.php',"x+");
					$this->temp['mkTBLstr'] = implode(',',$dataArr).',';
					fwrite($this->temp['mkfile'],"<?php /*XDB*/ if(!defined(\"XDB\")) die(\"Access denied.\"); ?>,".$this->temp['mkTBLstr']);
					fclose($this->temp['mkfile']);
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		public function dropDB($DBname){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $DBname == $this->user['username'])){
				if(in_array($DBname,$this->dbs)){
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/xdb.php');
					$this->temp['dbsfile'] = str_replace("\n".$DBname,"",$this->temp['dbsfile']);
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/xdb.php',$this->temp['dbsfile']);
					$this->temp['dropDBtbl'] = $this->DB($DBname)->showTBL();
					unlink($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/db.php');
					foreach($this->temp['dropDBtbl'] as $this->temp['dropDBfor']){
						unlink($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/'.$this->temp['dropDBfor'].'.tbl/data.php');
						rmdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/'.$this->temp['dropDBfor'].'.tbl/');
					}
					rmdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$DBname.'.db/');
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		public function dropTBL($TBLname){
			if($this->user['PER']>=2 || ($this->user['PER']>0 && $this->uDB == $this->user['username'])){
				if(in_array($TBLname,$this->showTBL())){
					$this->temp['dbsfile'] = file_get_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/db.php');
					$this->temp['dbsfile'] = str_replace("\n".$TBLname,"",$this->temp['dbsfile']);
					file_put_contents($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/db.php',$this->temp['dbsfile']);
					unlink($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/'.$TBLname.'.tbl/data.php');
					rmdir($GLOBALS['INI']['XDBCFG']['DATA'].'/'.$this->uDB.'.db/'.$TBLname.'.tbl/');
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	}