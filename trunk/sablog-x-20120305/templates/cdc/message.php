<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8" />
<meta name="keywords" content="$options[meta_keywords]" />
<meta name="description" content="$options[meta_description]" />
<meta name="author" content="4ngel" />
<style type="text/css">
body, div {font-size: 12px;font-family: Arial, sans-serif;color: #333;line-height: 16px;background:#f1f1f1;}
a{color:#833;text-decoration:none;}
a:hover{color:#147;text-decoration:underline;}
#message {margin-top:100px;background:#fff;text-align:center;width:500px;margin-right:auto;margin-left:auto;padding:20px;}
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