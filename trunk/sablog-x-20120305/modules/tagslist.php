<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台标签列表模块
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

if ($stats['tag_count']) {

	$pagenum = 400;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}

	$smallest = 14;
	$largest = 28;

	$multipage = multi($stats['tag_count'], $pagenum, $page, $tagslist_url, '', $maxpages);
	$query = $DB->query("SELECT mid, name, slug, type, count FROM {$db_prefix}metas WHERE type = 'tag' ORDER BY mid DESC LIMIT $start_limit, ".$pagenum);

	$tagdb = $counts = array();
	while ($tag = $DB->fetch_array($query)) {
		$tag['counts'] = tag_count_scale(($tag['count'] ? $tag['count'] : 1));
		$counts[$tag['mid']] = $tag['counts'];

		$tag['url'] = gettaglink($tag['slug']);
		$tagdb[$tag['mid']]=$tag;
	}
	unset($tag);
	$DB->free_result($query);

	//字体大小
	if ($counts) {
		$min_count = @min( $counts );
		$spread = @max( $counts ) - $min_count;
		if ( $spread <= 0 )
			$spread = 1;
		$font_spread = $largest - $smallest;
		if ( $font_spread < 0 )
			$font_spread = 1;
		$font_step = $font_spread / $spread;

		foreach ($tagdb as $mid => $tag) {
			$tagdb[$mid]['fontsize'] = $smallest + (($tag['counts'] - $min_count) * $font_step);
		}
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
$options['meta_keywords'] = '标签,'.$options['meta_keywords'];
$options['meta_description'] = $options['meta_description'] ? $options['meta_description'] : $options['description'];
$options['title_keywords'] = $options['title_keywords'] ? ' - '.$options['title_keywords'] : '';

$options['title'] = settitle('标签');
$pagefile = 'tag';

?>