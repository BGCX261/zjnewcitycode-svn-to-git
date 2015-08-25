<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> 
<meta name="Description" content="$options[meta_description]" />
<meta name="Keywords" content="$options[meta_keywords]" />
<link rel="stylesheet" type="text/css" media="all" href="$options[url]templates/$options[templatename]/style.css" />
<title>$options[title] $options[title_keywords] - Powered by Sablog-X</title>
<link rel="alternate" type="application/rss+xml" title="$options[name]" href="$options[url]rss.php" />
<meta name="generator" content="SaBlog-X" />
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
<script type="text/javascript" src="$options[url]include/jscript/jquery.js?ver=1.6.2"></script>
<script type="text/javascript" src="$options[url]include/jscript/common.js"></script>
</head>
<body>
<!--Header-->
<div{if $action == 'article' && $indexpage}id="header"{else}id="header1"{/if}>
	<div id="top">
		<div id="nav">
			<!--<a onfocus="this.blur()" title="$options[name] - $options[description]" href="$options[url]" id="sitename">$options[name]</a>-->
			<a onfocus="this.blur()" title="$options[name] - $options[description]" href="$options[url]" id="logo"><cite>$options[name] - $options[description]</cite></a>
			<div id="menu">
				<!--{eval $arcss = $licss = $cocss = $tacss = $trcss = $hocss = '';}-->
				<!--{if $action == 'archives' || $action == 'list'}-->
					<!--{eval $arcss = ' id="on"';}-->
				<!--{elseif $action == 'links'}-->
					<!--{eval $licss = ' id="on"';}-->
				<!--{elseif $action == 'tagslist'}-->
					<!--{eval $tacss = ' id="on"';}-->
				<!--{else}-->
					<!--{eval $hocss = ' id="on"';}-->
				<!--{/if}-->
				<ul>
					<li{$hocss}><a onfocus="this.blur()" href="$options[url]">博客</a></li>
					<li{$arcss}><a onfocus="this.blur()" href="$archives_url">归档</a></li>
					<li{$tacss}><a onfocus="this.blur()" href="$tagslist_url">标签</a></li>
					<li{$licss}><a onfocus="this.blur()" href="$links_url">链接</a></li>
					<!--{if $sax_uid}-->
						<li><a onfocus="this.blur()" href="$options[url]cp.php?action=logout">注销</a></li>
						<li><a onfocus="this.blur()" href="$options[url]cp.php"><!--{if $sax_group == 1 || $sax_group == 2}-->管理<!--{else}-->档案<!--{/if}--></a></li>
					<!--{else}-->
						<li><a onfocus="this.blur()" href="$options[url]cp.php?action=login">登陆</a></li>
					<!--{/if}-->
					<li id="rss"><a onfocus="this.blur()" href="$options[url]rss.php" class="rss" target="_blank"><cite>RSS</cite></a></li>
					<li id="feed"><a onfocus="this.blur()" href="javascript:doFav(0);" class="feed"><cite>分享</cite></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!--Header End-->

<!--分享到-->
<script type="text/javascript">
var postArray = [];
function doFav(postid){
	stitle = postArray[postid][0];
	surl = postArray[postid][1];
	
	if (postid != 0) {
		stitle = postArray[0][0] + ' - ' + stitle;
	}
	
	if (document.all){
		window.external.addFavorite(surl,stitle);
	}
	else if (window.sidebar){
		window.sidebar.addPanel(stitle,surl, "");
	}
	else{
		alert("抱歉,您的浏览器不支持添加到收藏夹,换个浏览器试试?");
	}
}
postArray[0]=[];
postArray[0][0]="$options[name]";
postArray[0][1]="$options[url]";
</script>
<!--分享到 End-->