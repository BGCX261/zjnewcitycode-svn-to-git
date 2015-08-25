<?php
// ========================== 文件说明 ==========================//
// 本文件说明：检查刷新&代理
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}

if($attackevasive == 1 || $attackevasive == 3) {
	if ($_COOKIE['lastrequest']) {
		list($lastrequest,$lastpath) = explode("\t",$_COOKIE['lastrequest']);
		$onlinetime = $timestamp - $lastrequest;
	} else {
		$lastrequest = $lastpath = '';
	}
	$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	if ($REQUEST_URI == $lastpath && $onlinetime < 2) {
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Refresh" content="2;url=<?php echo $REQUEST_URI;?>">
<title>Refresh Limitation Enabled</title>
</head>
<body style="table-layout:fixed; word-break:break-all">
<center>
<div style="margin-top:100px;background-color:#f1f1f1;text-align:center;width:600px;padding:20px;margin-right: auto;margin-left: auto;font-family: Verdana, Tahoma; color: #666666; font-size: 12px">
  <p><strong>Refresh Limitation Enabled</strong></p>
  <p>The time between your two requests is smaller than 2 seconds, please do NOT refresh and wait for automatical forwarding ...</p>
</div>
</center>
</body>
</html>
<?
		exit;
	}
	scookie('lastrequest',$timestamp."\t".$REQUEST_URI);
}

if(($attackevasive == 2 || $attackevasive == 3) && ($_SERVER['HTTP_X_FORWARDED_FOR'] || $_SERVER['HTTP_VIA'] || $_SERVER['HTTP_PROXY_CONNECTION'] || $_SERVER['HTTP_USER_AGENT_VIA'])) {
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Proxy Connection Denied</title>
</head>
<body style="table-layout:fixed; word-break:break-all">
<center>
<div style="margin-top:100px;background-color:#f1f1f1;text-align:center;width:600px;padding:20px;margin-right: auto;margin-left: auto;font-family: Verdana, Tahoma; color: #666666; font-size: 12px">
  <p><strong>Proxy Connection Denied</strong></p>
  <p>Your request was forbidden due to the administrator has set to deny all proxy connection.</p>
</div>
</center>
</body>
</html>
<?
	exit;
}
?>