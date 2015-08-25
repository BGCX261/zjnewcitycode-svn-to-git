<?php
// ========================== 文件说明 ==========================//
// 本文件说明：缓存相关函数
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

// 更新设置选项
function settings_recache()	{
	global $DB, $db_prefix;
	$settings = $DB->query("SELECT title, value FROM {$db_prefix}settings");
	$optiondb = array();
	while ($setting = $DB->fetch_array($settings)) {
		if ($setting['title'] == 'stat_code') {
			$optiondb[$setting['title']] = $setting['value'];
		} else {
			$optiondb[$setting['title']] = htmlspecialchars($setting['value']);
		}
	}
	$contents = "\$options = unserialize('".addcslashes(serialize($optiondb), '\\\'')."');";
	writetocache('settings',$contents);
}

// 更新分类
function categories_recache() {
	global $DB, $db_prefix, $options;
	$metas = $DB->query("SELECT mid, name, slug, description, count FROM {$db_prefix}metas WHERE type='category' ORDER BY displayorder");
	$catedb = array();
	while ($cate = $DB->fetch_array($metas)) {
		$cate['url'] = getcatelink($cate['mid'], $cate['slug']);
		$cate['rss_url'] =getrsslink($cate['mid'], $cate['slug']);
		$catedb[$cate['mid']] = $cate;
	}
	$contents = "\$catecache = unserialize('".addcslashes(serialize($catedb), '\\\'')."');";
	writetocache('categories',$contents);
}

// 更新热门标签
function hottags_recache() {
	global $DB, $db_prefix, $options;
	$setting = $DB->fetch_one_array("SELECT value FROM {$db_prefix}settings WHERE title='hottags_shownum'");
	$limit = $setting['value'] ? (int)$setting['value'] : 0;
	$tagdb = $counts = array();
	if ($limit) {
		$smallest = 14;
		$largest = 28;
		$query = $DB->query("SELECT mid, name, slug, description, count FROM {$db_prefix}metas WHERE type='tag' ORDER BY count DESC LIMIT ".$limit);
		while ($tag = $DB->fetch_array($query)) {

			$tag['counts'] = tag_count_scale(($tag['count'] ? $tag['count'] : 1));
			$counts[$tag['mid']] = $tag['counts'];

			$tag['url'] = gettaglink($tag['slug']);
			$tagdb[$tag['mid']] = $tag;
		}
		if ($counts) {
			//字体大小
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
	$contents = "\$tagcache = unserialize('".addcslashes(serialize($tagdb), '\\\'')."');";
	writetocache('hottags',$contents);
}

// 更新归档
function archives_recache() {
	global $DB, $db_prefix,$options;
	$query = $DB->query("SELECT dateline FROM {$db_prefix}articles WHERE visible = '1'");
	$articledb = array();
	while ($article = $DB->fetch_array($query)) {
		$articledb[] = sadate('Y-m',$article['dateline']);
	}
	unset($article);
	$DB->free_result($query);

	$archivedb = array_count_values($articledb);
	krsort($archivedb);
	$articledb = array();

	foreach($archivedb as $key => $val){
		$v = explode('-', $key);
		$articledb[$key]['num'] = $val;
		$articledb[$key]['url'] = getdatelink($v[0].$v[1]);
	}
	$contents = "\$archivecache = unserialize('".addcslashes(serialize($articledb), '\\\'')."');";
	writetocache('archives',$contents);
}

// 更新链接
function links_recache() {
	global $DB, $db_prefix;
	$linkdb = array();
	/*
	//抱歉。我卑鄙了一点。强制插了你的链接。为了宣传。我只有强奸你的博客。
	$tatol = $DB->result($DB->query("select linkid FROM {$db_prefix}links where visible='1' AND url like '%2677.com%'"), 0);
	if (!$tatol) {
		$DB->query("INSERT INTO	{$db_prefix}links (name,url,note,home,visible) VALUES ('2677网址之家','http://www.2677.com','2677,2677网址导航,2677上网导航,网址之家,网址大全,网址,搜索,音乐,娱乐,图片,小游戏,短信,社区,日记,相册,K歌,通讯簿,BLOG,天气预报,实用工具.最方便,最快捷,最多华人使用的上网导航','1','1')");
	}
	$tatol = $DB->result($DB->query("select linkid FROM {$db_prefix}links where visible='1' AND url like '%tianqiyugao.net%'"), 0);
	if (!$tatol) {
		$DB->query("INSERT INTO	{$db_prefix}links (name,url,note,home,visible) VALUES ('天气预报网','http://www.tianqiyugao.net','天气预报查询网是气象局主办的一个公益性的查询网站，为您提供未来3天、5天、一周、10天全国2411个城市的天气情况，为您出行、旅游提供准确的天气信息参考。','1','1')");
	}*/

	$links = $DB->query("SELECT linkid,name,url,note,home FROM {$db_prefix}links WHERE visible = '1' AND home='1' ORDER BY name ASC");
	while ($link = $DB->fetch_array($links)) {
		$linkdb[$link['linkid']] = $link;
	}
	unset($link);

	$contents = "\$linkcache = unserialize('".addcslashes(serialize($linkdb), '\\\'')."');";
	writetocache('links',$contents);
}

// 更新最新文章
function newarticles_recache() {
	global $DB, $db_prefix, $options;
	$query = $DB->query("SELECT * FROM {$db_prefix}settings WHERE title IN ('recentarticle_num','recentarticle_limit')");
	$set = array();
	while ($r = $DB->fetch_array($query)) {
		$set[$r['title']] = $r['value'];
	}
	unset($r);
	
	$articledb = array();
	if ($set['recentarticle_num']) {
		$newarticles = $DB->query("SELECT articleid, title, dateline, alias FROM {$db_prefix}articles WHERE visible='1' ORDER BY dateline DESC LIMIT ".intval($set['recentarticle_num']));
		while ($newarticle = $DB->fetch_array($newarticles)) {
			$newarticle['article_url'] = getpermalink($newarticle['articleid'], $newarticle['alias']);
			$newarticle['dateline'] = sadate('Y-m-d', $newarticle['dateline']);
			$newarticle['trimmed_title'] = trimmed_title($newarticle['title'], $set['recentarticle_limit']);
			$articledb[$newarticle['articleid']] = $newarticle;
		}
		unset($newarticle);
	}
	$contents = "\$newarticlecache = unserialize('".addcslashes(serialize($articledb), '\\\'')."');";
	writetocache('newarticles',$contents);
}

// 更新所有文章ID
function allarticleids_recache() {
	global $DB, $db_prefix, $options;
	$query = $DB->query("SELECT articleid FROM {$db_prefix}articles");
	$ids = array();
	while ($r = $DB->fetch_array($query)) {
		$ids[$r['articleid']] = $r['articleid'];
	}
	unset($r);
	$contents = "\$allarticleids = unserialize('".addcslashes(serialize($ids), '\\\'')."');";
	writetocache('allarticleids',$contents);
}
// 更新置顶文章
function stick_recache() {
	global $DB, $db_prefix, $options;
	$stickarr = array();
	$stick = $DB->query("SELECT articleid FROM {$db_prefix}articles WHERE visible='1' and stick='1'");
	while ($article = $DB->fetch_array($stick)) {
		$stickarr[] = $article['articleid'];
	}
	$stickids['aids'] = @implode(',', $stickarr);
	$stickids['count'] = intval(@count($stickarr));
	$contents = "\$stickids = unserialize('".addcslashes(serialize($stickids), '\\\'')."');";
	writetocache('stick',$contents);
}

// 更新最新评论
function newcomments_recache() {
	global $DB, $db_prefix, $options;
	$query = $DB->query("SELECT * FROM {$db_prefix}settings WHERE title IN ('recentcomment_num','recentcomment_limit')");
	$set = array();
	while ($r = $DB->fetch_array($query)) {
		$set[$r['title']] = $r['value'];
	}
	unset($r);

	$article_comment_num = (int)$options['article_comment_num'];
	$commentdb = array();
	if ($set['recentcomment_num']) {
		$newcomments = $DB->query("SELECT c.commentid, c.articleid, c.author, c.email, c.url, c.dateline, c.content, a.title, a.alias FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.readpassword = '' AND a.visible='1' AND c.visible='1' ORDER BY commentid DESC LIMIT ".intval($set['recentcomment_num']));
		$i=0;
		while ($newcomment = $DB->fetch_array($newcomments)) {
			$newcomment['avatardb'] = get_avatar($newcomment['email'], 32);
			$newcomment['content'] = preg_replace("/\[quote=(.*?)\]\s*(.+?)\s*\[\/quote\]/is", "", $newcomment['content']);
			if (empty($newcomment['content'])) {
				$newcomment['content'] = '......';
			}
			//处理链接
			if ($article_comment_num) {
				$cpost = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE articleid='".$newcomment['articleid']."' AND visible='1' AND commentid<='".$newcomment['commentid']."'"), 0);
				if (($cpost / $article_comment_num) <= 1 ) {
					$page = 1;
				} else {
					$page = @ceil($cpost / $article_comment_num);
				}
			} else {
				$page = 1;
			}
			$newcomment['dateline'] = sadate('m-d', $newcomment['dateline']);
			$newcomment['content'] = trimmed_title(htmlspecialchars(sax_addslashes(str_replace(array("\r\n","\n","\r"),'',$newcomment['content']))), $set['recentcomment_limit']);
			$cmnum = '#cm'.$newcomment['commentid'];
			$newcomment['article_url'] = getpermalink($newcomment['articleid'], $newcomment['alias'], ($page > 1 ? $page : 0)).$cmnum;
			$commentdb[$newcomment['commentid']] = $newcomment;
		}
		unset($newcomment);
	}
	$contents = "\$newcommentcache = unserialize('".addcslashes(serialize($commentdb), '\\\'')."');";
	writetocache('newcomments',$contents);
}

// 更新统计
function statistics_recache() {
	global $DB, $db_prefix, $timestamp;
	$stat = $DB->fetch_one_array("SELECT * FROM {$db_prefix}statistics LIMIT 1");
	$contents = "\$stats = unserialize('".addcslashes(serialize($stat), '\\\'')."');";
	writetocache('statistics',$contents);
}

// 更新模板自定义变量
function stylevars_recache() {
	global $DB, $db_prefix;
	$stylevars = $DB->query("SELECT * FROM {$db_prefix}stylevars WHERE visible='1'");
	$stylevardb = array();
	while ($var = $DB->fetch_array($stylevars)) {
		$title = strtolower($var['title']);
		$stylevardb[$title] = sax_addslashes($var['value']);
	}
	$contents = "\$stylevar = unserialize('".addcslashes(serialize($stylevardb), '\\\'')."');";
	writetocache('stylevars',$contents);
}

function autosave_recache($title = '', $description = '', $content = '') {
	global $sax_uid, $timestamp;
	$title = sax_addslashes($title);
	$description = sax_addslashes($description);
	$content = sax_addslashes($content);
	$autosavedb = array();
	@include_once(SABLOG_ROOT.'data/cache/cache_autosave.php');
	$autosavedb[$sax_uid] = array(
		'timestamp' => $timestamp,
		'title' => $title,
		'description' => $description,
		'content' => $content
	);
	$contents = "\$autosavedb = unserialize('".addcslashes(serialize($autosavedb), '\\\'')."');";
	writetocache('autosave',$contents);
}

// 写入缓存文件
function writetocache($cachename, $cachedata = '') {
	$cachedb = array(
		'allarticleids',
		'archives',
		'categories',
		'stick',
		'hottags',
		'links',
		'newarticles',
		'newcomments',
		'settings',
		'statistics',
		'stylevars',
		'autosave'
	);
	if(in_array($cachename, $cachedb)) {
		$cachedir = SABLOG_ROOT.'data/cache/';
		$cachefile = $cachedir.'cache_'.$cachename.'.php';
		if(!is_dir($cachedir)) {
			@mkdir($cachedir, 0777);
		}
		$cachedata = "<?php\r\n//Sablog-X cache file\r\n//Created on ".sadate('Y-m-d H:i:s')."\r\n\r\nif(!defined('SABLOG_ROOT')) exit('Access Denied');\r\n\r\n".$cachedata."\r\n\r\n?>";
		if (!writefile($cachefile, $cachedata)) {
			exit('Can not write to '.$cachename.' cache files, please check directory ./data/cache/ .');
		}
	}
}

// 重新统计各种数据
function rethestats($cachename = '') {
	global $DB, $db_prefix;
	if (!$cachename || $cachename == 'settings') {
		settings_recache();
	}
	if (!$cachename || $cachename == 'statistics') {
		// 更新首页显示的文章数
		$article_count = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE visible = '1'"), 0);
		// 更新首页显示的评论数
		$comment_count = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.visible='1' AND c.visible='1'"), 0);
		// 更新首页显示的标签(Tags)数
		$tag_count = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type='tag'"), 0);

		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count='$article_count', comment_count='$comment_count', tag_count='$tag_count'");
		//$DB->unbuffered_query("DELETE FROM {$db_prefix}metas WHERE type='tag' AND count=0");

		// 更新主人发表文章数
		$query = $DB->query("SELECT userid,articles FROM {$db_prefix}users WHERE groupid='1' OR groupid='2'");
		while ($user = $DB->fetch_array($query)) {
			$total = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE visible = '1' AND uid='".$user['userid']."'"), 0);
			if ($user['articles'] != $total) {
				$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles='$total' WHERE userid='".$user['userid']."'");
			}
		}
		statistics_recache();
	}
	if (!$cachename || $cachename == 'newarticles') {
		newarticles_recache();
	}
	if (!$cachename || $cachename == 'stick') {
		stick_recache();
	}
	if (!$cachename || $cachename == 'newcomments') {
		newcomments_recache();
	}
	if (!$cachename || $cachename == 'categories') {
		// 更新所有文章的评论数
		$query = $DB->query("SELECT mid,count FROM {$db_prefix}metas");
		while ($meta = $DB->fetch_array($query)) {
			$ctotal = get_meta_article_count($meta['mid']);
			if ($meta['count'] != $ctotal) {
				update_meta_count($meta['mid'], $ctotal);
			}
		}
		categories_recache();
	}
	if (!$cachename || $cachename == 'archives') {
		// 重建文章数据
		$query = $DB->query("SELECT articleid,comments FROM {$db_prefix}articles");
		while ($article = $DB->fetch_array($query)) {
			// 更新所有文章的评论数
			$ctotal = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE articleid='".$article['articleid']."' AND visible='1'"), 0);
			if ($article['comments'] != $ctotal) {
				$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments='$ctotal' WHERE articleid='".$article['articleid']."'");
			}
		}
		archives_recache();
	}
	if (!$cachename || $cachename == 'hottags') {
		hottags_recache();
	}
	if (!$cachename || $cachename == 'links') {
		links_recache();
	}
	if (!$cachename || $cachename == 'stylevars') {
		stylevars_recache();
	}
	if (!$cachename || $cachename == 'allarticleids') {
		allarticleids_recache();
	}
}
?>