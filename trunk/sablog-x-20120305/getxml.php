<?php
// ========================== 文件说明 ==========================//
// 本文件说明：获取Trackback地址&标签操作
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('global.php');

if ($_GET['action'] == 'tag') {
	$tag = sax_addslashes($_GET['tag']);
	$html = '<h2><a href="javascript:;" onclick="document.getElementById(\'ajax-div\').style.display=\'none\';">关闭</a>相关文章</h2><div>';
	if ($tag) {
		$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='tag' AND name='$tag' LIMIT 1");
		if (!$r) {
			$html .= 'TAG记录不存在';
		} else {
			$aids = get_cids($r['mid']);
			if ($aids) {
				$total = $r['count'];
				$query = $DB->query("SELECT articleid, title, alias FROM {$db_prefix}articles WHERE visible='1' AND articleid IN ($aids) ORDER BY dateline DESC LIMIT 10");
				$html .= '<ul>';
				while ($article = $DB->fetch_array($query)) {
					$html .= '<li><a href="'.getpermalink($article['articleid'], $article['alias']).'">'.$article['title'].'</a></li>';
				}
				$html .= '</ul>';
				if ($total > 10) {
					$html .= '<div style="padding-top:20px;text-align:right;"><a href="'.gettaglink($r['slug']).'">更多相关文章</a></p>';
				}
			} else {
				$html .= '没有相关文章';
			}
		}
	} else {
		$html .= '没有相关文章';
	}
	$html .= '</div>';
	xmlmsg($html);
}

if ($_GET['action'] == 'getalltag') {
	$html = '<h2><a href="javascript:;" onclick="document.getElementById(\'ajax-div\').style.display=\'none\';">关闭</a>插入已有的标签</h2>';

	$total = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type IN ('tag')"), 0);
	$html .= '<div class="alltag">';
	if ($total) {
		$query = $DB->query("SELECT mid, name, count FROM {$db_prefix}metas WHERE type IN ('tag') ORDER BY count DESC");
		while ($tag = $DB->fetch_array($query)) {
			$html .= '<a href="###" onclick="addTag(\''.$tag['name'].'\')" title="插入">'.$tag['name'].' ('.$tag['count'].')</a>';
		}
	} else {
		$html .= '没有任何标签';
	}
	$html .= '</div>';
	xmlmsg($html);
}

if ($_GET['action'] == 'trackback') {
	$id = (int)$_GET['id'];

	$article = $DB->fetch_one_array("SELECT dateline FROM {$db_prefix}articles WHERE articleid='$id' AND visible='1'");
	if (!$article) {
		message('文章不存在.', './');
	}
	$code = rawurlencode(authcode("$id\t$article[dateline]"));

	$html = '<h2><a href="javascript:;" onclick="document.getElementById(\'ajax-div\').style.display=\'none\';">关闭</a>Trackback</h2>
	<div>
		<a href="'.$options['url'].'trackback.php?code='.$code.'" onclick="setCopy(this.href);return false;" target="_self">点击复制Trackback链接到剪切板</a>
	</div>';
	xmlmsg($html);
}

function xmlmsg($html) {
	@header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<root><![CDATA[".$html."]]></root>\n";
}
?>