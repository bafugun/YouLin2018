<?php
require_once("config.php");

/*根据wx.login获取的CODE，换取用户唯一标识和session_key等用户信息*/
class code2Session
{
private $code="";

public function __construct($code)
{
	$this->code = $code;
}

public function getSession()
{
	
	$urlFormat="https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code";
	$url = sprintf($urlFormat,APPID,APPSECRET,$this->code);
	$ret = $this->_execRequest($url);
	$fp = fopen("codeurl","w");
	if(is_resource($fp))
	{
		fwrite($fp,$url);
		fwrite($fp,"\n");
		fwrite($fp,$ret);
	}
	return json_decode($ret,true);
}

/*获取指定URL的数据*/
private function _execRequest($url)
{
	/*
	$paramData = array(
			    "ssl"=>array(
        "verify_peer"=> false,
        "verify_peer_name"=> false,
    			),
			'http'=>array(
			"timeout"=>10,
			"method"=>"get")
		     );
	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx7c86392bd41b6f49&secret=0fa016c73454f61d53854635cd321131&code=071pncS32DyeCO0lg8P328ZZR32pncS6&grant_type=authorization_code";
	$body = file_get_contents($url,false, stream_context_create($paramData));
	echo "Body:".$body;
	return $body;*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_SAFE_UPLOAD,TRUE);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		$ret = curl_exec($ch);
		if($ret == FALSE)
		{
			echo "Exec Failed.";
		}

		curl_close($ch);
	//echo "ret".$ret;
	return $ret;
}

}

/*$codeToSession = new code2Session("mafujun");
$codeToSession->getSession();
*/

?>
