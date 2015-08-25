<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="utf-8" />
<meta http-equiv="Pragma" content="no-cache" />
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

<script type="text/javascript">
var postminchars = parseInt("$options[comment_min_len]");
var postmaxchars = parseInt("$options[comment_max_len]");
var blogurl = '$options[url]';
</script>
<script type="text/javascript" src="$options[url]include/jscript/jquery.js?ver=1.7.1"></script>
<script type="text/javascript" src="$options[url]include/jscript/common.js"></script>
<title>$options[title] $options[title_keywords] - Powered by Sablog-X</title>
</head>
<body>
<div id="outmain">

	<div id="header">
		<div class="blog-title"><a href="$options[url]">$options[name]</a></div>
		<p class="description">$options[description]</p>
		<div id="searchbar">
			<form class="searchform clearfix" action="$options[url]post.php" method="post">
				<!--
				隐藏表单,可有可无
				<select name="mids[]" multiple="multiple" style="width:100%" size="8">
					<option value="" selected="selected">搜索所有分类</option>
					<!--{loop $catecache $data}-->
						<option value="$data[mid]">&nbsp;&nbsp;&#0124;&#45;&#45; $data[name]</option>
					<!--{/loop}-->
				</select>
				<select name="searchin">
					<option value="title" selected="selected">标题搜索</option>
					<option value="content">全文搜索</option>
				</select>
				-->
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="action" value="search" />
				<input class="searchinput" type="text" onfocus="this.value=''" name="keywords" value="Search...">
				<input class="searchsubmit" type="submit" value="Search">
			</form>
		</div>
	</div>

	<div id="pagemenu">
		
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

		<ul class="clearfix">
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
		</ul>
	</div>