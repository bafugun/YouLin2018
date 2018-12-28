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
			$commitRet = $this->commit();
			/*执行失败的情况下*/
			if(!$retExec)
			{
				$errors =  join(";",$this->errorInfo());
			}
			return $retExec;
		}
		catch(PDOException $e)
		{
			$this->rollback();
			$retExec  = false;
			$errors = $e->getMessage();
		}
		
		if(!$retExec)
		{
			_error($sql,$errors);
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
?>