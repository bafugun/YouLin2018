<?php
require_once("config.php");

class customDbConnection extends PDO
{
	public function __construct($inifile = "db.ini")
	{
		if(!$settings = parse_ini_file($inifile, true))
		{
				throw new Exception("Could not find $inifile....",-1);
		}
		
		$dns = $settings["database"]["driver"].":host=".$settings["database"]["host"].((!empty($settings["database"]["port"])) ? (";port=".$settings["database"]["port"]):"").";dbname=".$settings["database"]["dbname"];
		parent::__construct($dns, $settings["database"]["user"], $settings["database"]["password"]);
		$this->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); 
	}
	
	/*用来执行非查询类的SQL*/
	public function exec($sql)
	{
		$errors = "Undefined Error";
		$retExec  = false;
		try
		{
			$this->beginTransaction();
			$retExec = parent::exec($sql);
			/*当SQL成功执行，且未影响数据的情况下，返回成功*/
			if(!(int)($this->errorCode()) and  !$retExec)
			{	
				$retExec = true;
			}
			
			/*执行失败的情况下*/
			if(!$retExec)
			{
				$errors =  join(";",$this->errorInfo());
			}
			
			$commitRet = $this->commit();
		}
		catch(PDOException $e)
		{
			$this->rollback();
			$retExec  = false;
			$errors = $e->getMessage();
		}
		
		if(!$retExec)
		{
			$this->_error($sql,$errors);
		}
		return $retExec;
	}
	
	/*执行查询语句的结果*/
	public function query($sql)
	{
		$stmt = parent::query($sql);
		$ret = array();
		if(!$stmt)
		{
			return $ret;
		}
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
		
	}
	
	/*记录出错的SQL*/
	private function _error($sql,$errors)
	{
		$fp = fopen(DBERROR,"a+");
		if(Sfp && is_resource($fp))
		{
			$formatError = sprintf("Sql:%s\t Error:%s\n",$sql,$errors);
			fwrite($formatError);
			fclose($fp);
		}
	}
}
/*
$db = new customDbConnection();
$db->exec("insert into  YouLinAccount(OpenID,nickName,gender,city,province,country,avatarUrl) values ('oGkPx5GtlKCgj9ZWUwvIxO_AEWKI','bafugun',1,'London','England','United Kingdom','https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eomoibdd14OEmG9KUu4W7yM6anupn8oJ3SpuXlpQUHYvQ9qibSnHEksoMNj1rLqrDCibhibojvnzTLSiaA/132') on duplicate key update nickName=values(nickName),gender=values(gender),city=values(city),province=values(province),country=values(country),avatarUrl=values(avatarUrl);");
*/

?>
