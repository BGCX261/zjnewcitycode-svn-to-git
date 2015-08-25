<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台日志归档模块
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

if ($stats['article_count']) {
	$articledb = array();
	$query = $DB->query("SELECT articleid,title,dateline,comments,readpassword,alias FROM {$db_prefix}articles WHERE visible='1' ORDER BY dateline DESC");
	while ($article = $DB->fetch_array($query)) {
		$date = sadate('Y-m', $article['dateline']);
		$article['dateline'] = sadate('m/d', $article['dateline']);
		$article['url'] = getpermalink($article['articleid'], $article['alias']);
		$articledb[$date][] = $article;
	}
}

if (!$options['meta_keywords']) {
	$tmp = $comma = '';
	if (is_array($catecache) && count($catecache)){
		foreach($catecache as $data) {
			$tmp .= $comma.$data['name'];
			$comma = ',';
		}
		$options['meta_keywords'] = $tmp;
	}
}
$options['meta_keywords'] = '文章归档,'.$options['meta_keywords'];
$options['meta_description'] = $options['meta_description'] ? $options['meta_description'] : $options['description'];
$options['title_keywords'] = $options['title_keywords'] ? ' - '.$options['title_keywords'] : '';

$pagefile = 'archives';
$options['title'] = settitle('文章归档');

?>