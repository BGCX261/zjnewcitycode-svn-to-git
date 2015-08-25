<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Pragma" content="no-cache" />
<title>管理 &raquo; $title<!--{if $suvtitle}--> &raquo; $suvtitle<!--{/if}--></title>

<!-- CSS -->
<link href="$options[url]templates/admin/transdmin.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="$options[url]templates/admin/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="$options[url]templates/admin/ie7.css" /><![endif]-->

<script type="text/javascript" src="$options[url]include/jscript/jquery.js?ver=1.7.1"></script>
<script type="text/javascript" src="$options[url]include/jscript/common.js"></script>
<script type="text/javascript" src="$options[url]include/jscript/admin.js"></script>
<script type="text/javascript">
var blogurl = '$options[url]';
</script>
</head>

<body>
	<div id="wrapper">
		<div id="user_info">
		<!--{if $sax_uid && $sax_user}-->
			<ul>
				<li>欢迎您, $sax_user &raquo; </li>
				<li><a href="cp.php?job=article&amp;action=add">添加文章</a></li>
				<li><a href="$options[url]" target="_blank">站点首页</a></li>
			</ul>
		<!--{/if}-->
		</div>

    	<h1><a href="cp.php"><span>Transdmin Light</span></a></h1>
        
		<!--{if isset($adminitem) && $adminitem && in_array($sax_group, array(1,2))}-->
        <ul id="mainNav">
			<!--{loop $adminitem $key $data}-->
        		<li><a href="cp.php?job=$key"{if $key == $job && $action != 'profile'} class="active"{/if}>$data[name]</a></li>
			<!--{/loop}-->
        	<li class="logout"><a href="cp.php?action=logout">注销</a></li>
        	<li class="logout"><a href="cp.php?job=user&amp;action=profile"{if $job == 'user' && $action == 'profile'} class="active"{/if}>资料</a></li>
        </ul>
		<!--{/if}-->        

		<!--{if $message}-->
			<div id="message" class="updated">$messages[$message]</div>
		<!--{/if}-->

        <div id="containerHolder">