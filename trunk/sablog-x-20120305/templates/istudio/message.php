<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8" />
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /><![endif]-->
<!--[if IE]><script type="text/javascript" src="$options[url]include/jscript/html5.js"></script><![endif]-->
<meta name="keywords" content="$options[meta_keywords]" />
<meta name="description" content="$options[meta_description]" />
<meta name="author" content="4ngel" />
<style type="text/css">
body, div {font-size: 12px;font-family: Arial, sans-serif;color: #333;line-height: 16px;background:#fff;}
a{color:#0ab2e6;text-decoration:none;}
a:hover{color:#147;text-decoration:underline;}
#message {border:5px solid #C2E4FF;margin-top:100px;background:#F7FCFF;text-align:center;width:500px;margin-right:auto;margin-left:auto;padding:20px;}
#message h3 {color:#000;font-size:16px;margin:20px auto;}
</style><!--{if $returnurl}-->
	<meta http-equiv="REFRESH" content="$min;URL=$returnurl" />
<!--{/if}-->
<title>系统消息 $options[title_keywords] - Powered by Sablog-X</title>
</head>
<body>
<div id="message">
	<h3>$options[name]</h3>
	<p style="margin-bottom:20px;">$msg</p>
	<!--{if $returnurl}-->
		<p><a href="$returnurl">如果不想等待或浏览器没有自动跳转请点击这里跳转</a></p>
	<!--{/if}-->
</div>
</body>
</html>