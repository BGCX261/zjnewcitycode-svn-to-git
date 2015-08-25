<?php
// ========================== 文件说明 ==========================//
// 本文件说明：站点地图输出
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('global.php');

if (!$options['sitemap']) {
	exit('Sitemap is not available.');
}

//读取缓存文件
$cachefile = SABLOG_ROOT.'data/cache/cache_sitemap.php';

//如果读取失败或缓存过期则从新读取数据库
if((@!include($cachefile)) || $expiration < $timestamp) {
	$mapdb = array();
	$query = $DB->query("SELECT articleid,dateline,alias FROM {$db_prefix}articles WHERE visible='1' ORDER BY dateline DESC LIMIT 1000");
	while ($article = $DB->fetch_array($query)) {
		$article['url'] = getpermalink($article['articleid'], $article['alias']);
		$article['dateline'] = sadate('Y-m-d\TH:i:s\Z',$article['dateline']);
		$mapdb[$article['articleid']] = $article;
	}//end while

	unset($article);
	$DB->free_result($query);
	
	$cachedata = "<?php\r\nif(!defined('SABLOG_ROOT')) exit('Access Denied');\r\n\$expiration='".($timestamp + 86400)."';\r\n\$mapdb = unserialize('".addcslashes(serialize($mapdb), '\\\'')."');\r\n?>";
	
	if(!writefile($cachefile, $cachedata)) {
		exit('Can not write to cache files, please check directory ./data/cache/ .');
	}
}


header("Content-Type: application/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
if (is_array($mapdb)) {
	foreach ($mapdb as $article) {
		echo "\t<url>\n";
		echo "\t\t<loc>".$article['url']."</loc>\n";
		echo "\t\t<lastmod>".$article['dateline']."</lastmod>\n";
		echo "\t\t<changefreq>always</changefreq>\n";
		echo "\t\t<priority>0.5</priority>\n";
		echo "\t</url>\n";
	}
}
echo "</urlset>\n";
exit;

?>