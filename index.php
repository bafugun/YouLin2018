<?php 

require_once("config.php");
require_once("YouLinAccount.php");

ob_clean();
/*读取接收到POST数据*/
$readData = file_get_contents("php://input");
$readData = substr(urldecode($readData),strlen(POSTKEYS));
/*echo $readData;*/
$arrRet = array();
if(empty($readData))
{
	$arrRet["code"] = -1;
	$arrRet["msg"] = "Recv Data Empty";
	echo json_encode($arrRet);
	exit(0);
}
$fp = fopen("recv.txt","w");
if(is_resource($fp) && $fp)
{
	fwrite($fp,$readData);
	fclose($fp);
}

$arrData = json_decode($readData,true);
//echo "\nRecv:".join(":",$arrData)."\n";

/*先决条件判断*/
if(empty($arrData) || !array_key_exists("token",$arrData) || !array_key_exists("type",$arrData))
{
	$arrRet["code"] = -2;
	$arrRet["msg"] = "token or type  is Invalid.";
	echo json_encode($arrRet);
	exit(0);
}

/*token 不合法*/
if(strcmp($arrData["token"],TOKENFILE))
{
	$arrRet["code"] = -3;
	$arrRet["msg"] = "token is Invalid.";
	echo json_encode($arrRet);
	exit(0);
}

$type = strtoupper($arrData["type"]);
/*判断操作类型是否合法*/
if(empty($type))
{
	$arrRet["code"] = -4;
	$arrRet["msg"] = "type is Invalid.";
	echo json_encode($arrRet);
	exit(0);
}

switch($type)
{
	/*新增用户*/
	case "YOULINACCOUNTADD":
	{
		$youlinadd = new YouLinAccount($arrData);
		$arrRet = $youlinadd->add();
	}
	break;
	
	default:
	{
		$arrRet["code"] = -1;
		$arrRet["msg"] = "type is Invalid.";
	}
	break;
}

echo json_encode($arrRet);
exit(0);
?>
