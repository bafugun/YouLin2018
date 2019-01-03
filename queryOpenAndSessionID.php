<?php

/*从数据库根据CODE获取OpenID及其SessionKEY*/
class queryOpenAndSessionID
{
	/**/
	private $db = null;
	private $code = null;
	
	/*调用之前DB必须已经连接好了*/
	public function __construct(&$db, $code)
	{
		$this->db = $db;
		$this->code = $code;
	}
	
	public function getOpenIDAndSession()
	{
		$sql = sprintf("select sessionID,openID from codeSessionOpenID where code='%s'",$this->code);
		return $this->db.query($sql);
	}
}

?>