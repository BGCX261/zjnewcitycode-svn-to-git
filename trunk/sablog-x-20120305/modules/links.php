<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台友情链接模块
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

$query = $DB->query("SELECT name,url,note FROM {$db_prefix}links WHERE visible = '1' ORDER BY home DESC, name ASC");
$linkdb = array();
while ($link = $DB->fetch_array($query)) {
	$link['note'] = $link['note'] ? $link['note'] : $link['url'];
	$linkdb[] = $link;
}
unset($link);
$DB->free_result($query);

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
$options['meta_keywords'] = '友情链接,'.$options['meta_keywords'];
$options['meta_description'] = $options['meta_description'] ? $options['meta_description'] : $options['description'];
$options['title_keywords'] = $options['title_keywords'] ? ' - '.$options['title_keywords'] : '';

$pagefile = 'links';
$options['title'] = settitle('友情链接');

?>