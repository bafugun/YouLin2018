<?php
require_once("code2Session.php");
require_once("customDbConnection.php");
require_once("queryOpenAndSessionID.php");

class YouLinAccount
{
	private $_arrData = null;
	
	public function __construct($arrData)
	{
		$this->_arrData = $arrData;
	}
	
	public function & add()
	{
		$arrRet = array();
		
		/*1. 首先查询code对应的*/
		$code = $this->_arrData["code"];
		if(empty($code))
		{
			$arrRet["code"] = -5;
			$arrRet["msg"] = "Code is Empty.";
			return $arrRet;
		}
		
		/*签名是否正确*/
		$recvSignature = $this->_arrData["signature"];
		if(empty($recvSignature))
		{
			$arrRet["code"] = -6;
			$arrRet["msg"] = "Signature is Empty.";
			return $arrRet;
		}
		
		/*加密数据是否正确*/
		$recvEncryptedData = $this->_arrData["encryptedData"];
		if(empty($recvSignature))
		{
			$arrRet["code"] = -7;
			$arrRet["msg"] = "encryptedData is Empty.";
			return $arrRet;
		}
		
		$db = new customDbConnection();
		$select = new queryOpenAndSessionID($db, $code);
		$dbSessionAndOpenID = $select->getOpenIDAndSession();
		/*用户code 过期或者未登录过*/
		if(empty($dbSessionAndOpenID))
		{
			/*2. 根据code获取Session*/
			$codeSession = new code2Session($code);
			$arrcodeSession = $codeSession->getSession();
			if(empty($arrcodeSession))
			{
				$arrRet["code"] = -8;
				$arrRet["msg"] = "Get Empty Session_key.";
				return $arrRet;
			}
	
			/*3. 判断是否合法*/
			if(!array_key_exists("session_key",$arrcodeSession) || $arrcodeSession["errcode"])
			{
				$arrRet["code"] = -8;
				$arrRet["msg"] = $arrcodeSession["errmsg"];
				return $arrRet;
			}
		
			/*获取OpenID的方法有两种，一种是通过Session获取，一种是解密encryptedData 字段，目前采用第一种方案*/
			$openID = $arrcodeSession["openid"];
			if(empty($openID))
			{
				$arrRet["code"] = -9;
				$arrRet["msg"] = "Signature is Failed.";
				return $arrRet;
			}
		
			/*获取会话KEY*/
			$sessionkey = $arrcodeSession["session_key"];
            /*后台保存code、Session_KEY,OPENID三者数据，用于后续API使用*/
            $sql = sprintf("insert into  codeSessionOpenID(openID,sessionID,code) values('%s','%s','%s') on  DUPLICATE key update sessionID=values(sessionID),code=values(code)",$openID,$sessionkey,$code);
            $db->exec($sql);
		}
		else
		{
			$openID =$dbSessionAndOpenID[0]["openID"];
			$sessionkey = $dbSessionAndOpenID[0]["sessionID"];
		}

		$recvRawData = $this->_arrData["rawData"];
		$currSignature = sha1(htmlspecialchars_decode($recvRawData).$sessionkey);
		
		//echo "recv:".$recvSignature."\nCurr:".$currSignature."\n";
		//echo "\nrawData:".$recvRawData."\n";
		//echo "\nsessionkey:".$sessionkey."\n";

		/*验证签名的正确性*/
		if($currSignature != $recvSignature)
		{
			$arrRet["code"] = -10;
			$arrRet["msg"] = "Signature is Failed.";
			return $arrRet;
		}
	
		$arrRet["code"] = 0;
		$arrRet["msg"] = "OK";
		$jsonRaw = json_decode($this->_arrData["rawData"],true);
		/*插入当前登录的用户*/
		$sql = sprintf("insert into  YouLinAccount(OpenID,nickName,gender,city,province,country,avatarUrl) values ('%s','%s',%s,'%s','%s','%s','%s') on duplicate key update nickName=values(nickName),gender=values(gender),city=values(city),province=values(province),country=values(country),avatarUrl=values(avatarUrl);"
		,$openID,$jsonRaw['nickName'],$jsonRaw['gender'],$jsonRaw['city'],$jsonRaw['province'],$jsonRaw['country'],$jsonRaw['avatarUrl']);
		if(!$db->exec($sql))
		{
			$arrRet["code"] = -11;
			$arrRet["msg"] = "DB Error.";
		}
		else
		{
			$arrRet["YouLin"]=$recvRawData;
		}
		return $arrRet;
	}
}
?>
