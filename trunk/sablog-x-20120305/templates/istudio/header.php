<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8" />
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /><![endif]-->
<!--[if IE]><script type="text/javascript" src="$options[url]include/jscript/html5.js"></script><![endif]-->
<meta content="$options[meta_keywords]" name="keywords" />
<meta content="$options[meta_description]" name="description" />
<meta content="SaBlog" name="copyright" />
<meta content="angel,4ngel" name="author" />
<link rel="alternate" title="$options[name]" href="$options[url]rss.php" type="application/rss+xml" />
<link rel="stylesheet" href="$options[url]templates/$options[templatename]/style.css" type="text/css" media="all" />
<!-- SyntaxHihglighter Begin -->
<script type="text/javascript" src="$options[url]include/syntax/scripts/shCore.js"></script>
<script type="text/javascript" src="$options[url]include/syntax/scripts/shLegacy.js"></script> 
<script type="text/javascript" src="$options[url]include/syntax/scripts/shBrushAll.js"></script>
<link type="text/css" rel="stylesheet" href="$options[url]include/syntax/styles/shCore.css"/>
<link type="text/css" rel="stylesheet" href="$options[url]include/syntax/styles/shThemeDefault.css"/>
<script type="text/javascript">
	SyntaxHighlighter.config.clipboardSwf = '$options[url]include/syntax/scripts/clipboard.swf';
	SyntaxHighlighter.all();
	dp.SyntaxHighlighter.HighlightAll('code');
</script>
<!-- SyntaxHihglighter End -->
<script type="text/javascript" src="$options[url]include/jscript/jquery.js?ver=1.7.1"></script>
<script type="text/javascript" src="$options[url]include/jscript/common.js"></script>
<title>$options[title] $options[title_keywords] - Powered by Sablog-X</title>
</head>
<body>

<section id="wrapper">
	<header>
		<section class="hgroup">
			<h1><a href="$options[url]">$options[name]</a></h1>
			<div class="description">$options[description]</div>
		</section>

		<nav>
			<a class="feedrss" href="$options[url]rss.php" title="$options[name] RSS Feed">Feed Rss</a>
			<!--{eval $arcss = $licss = $cocss = $tacss = $trcss = $hocss = '';}-->
			<!--{if $action == 'archives' || $action == 'list'}-->
				<!--{eval $arcss = ' class="current_page_item"';}-->
			<!--{elseif $action == 'links'}-->
				<!--{eval $licss = ' class="current_page_item"';}-->
			<!--{elseif $action == 'tagslist'}-->
				<!--{eval $tacss = ' class="current_page_item"';}-->
			<!--{else}-->
				<!--{eval $hocss = ' class="current_page_item"';}-->
			<!--{/if}-->
			<menu id="menus">
				<li{$hocss}><a href="$options[url]">博客</a></li>
				<li{$arcss}><a href="$archives_url">归档</a></li>
				<li{$tacss}><a href="$tagslist_url">标签</a></li>
				<li{$licss}><a href="$links_url">链接</a></li>
				<!--{if $sax_uid}-->
					<li><a href="$options[url]cp.php?action=logout">注销</a></li>
					<li><a href="$options[url]cp.php"><!--{if $sax_group == 1 || $sax_group == 2}-->管理<!--{else}-->档案<!--{/if}--></a></li>
				<!--{else}-->
					<li><a href="$options[url]cp.php?action=login">登陆</a></li>
				<!--{/if}-->
			</menu>
		</nav>
	</header>
	<hr />