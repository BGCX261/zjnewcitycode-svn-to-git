<?php
define('SABLOG_ROOT', substr(dirname(__FILE__), 0, -7));
$onoff = function_exists('ini_get') ? ini_get('register_globals') : get_cfg_var('register_globals');
if ($onoff != 1) {
	@extract($_POST, EXTR_SKIP);
	@extract($_GET, EXTR_SKIP);
}
$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

require_once(SABLOG_ROOT.'/include/func/global.func.php');

function runquery($sql) {
	global $db_prefix, $DB, $tablenum;

	$sql = str_replace("\r", "\n", str_replace(' sablog_', ' '.$db_prefix, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				echo '创建表 '.$name.' ... <font color="#0000EE">成功</font><br />';
				$DB->query(createtable($query));
				$tablenum++;
			} else {
				$DB->query($query);
			}
		}
	}
}
function createtable($sql) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=utf8" : " TYPE=$type");
}

if (in_array($action, array('first','second','three','four'))) {
	// 允许程序在 register_globals = off 的环境下工作
	// 加载数据库配置信息
	require_once(SABLOG_ROOT.'/config.php');
	// 加载数据库类
	require_once(SABLOG_ROOT.'/include/class/mysql.class.php');
	// 初始化数据库类
	$DB = new DB_MySQL;
	$DB->connect($servername, $dbusername, $dbpassword, $dbname, $usepconnect);
	unset($servername, $dbusername, $dbpassword, $dbname, $usepconnect);

	$step = (!$step) ? 1 : $step;
	$a = (!$a) ? 0 : $a;
	$percount = ($percount <= 0) ? 500 : $percount;
	$start    = ($step - 1) * $percount;
	$next     = $start + $percount;
	$step++;
	$jumpurl = $php_self.'?action='.$action.'&step='.$step.'&percount='.$percount;
	$goon = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SABLOG-X | Powered by 4ngel</title>
<link href="install.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="main">
<?php
if ($action == 'first') {
$add = <<<EOT
DROP TABLE IF EXISTS {$db_prefix}metas;
CREATE TABLE {$db_prefix}metas (
	mid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	name varchar(100) NOT NULL DEFAULT '',
	slug varchar(200) NOT NULL,
	type enum('category','tag','link_category') NOT NULL,
	description varchar(200) NOT NULL DEFAULT '',
	count smallint(6) unsigned NOT NULL DEFAULT '0',
	displayorder smallint(6) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (mid),
	KEY slug (slug),
	KEY displayorder (displayorder)
) ENGINE=MyISAM ;


DROP TABLE IF EXISTS {$db_prefix}relationships;
	CREATE TABLE {$db_prefix}relationships (
	cid mediumint(8) unsigned NOT NULL DEFAULT '0',
	mid mediumint(8) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (cid,mid)
) ENGINE=MyISAM ;
EOT;

	runquery($add);
	$query = $DB->query("SELECT cid, name, displayorder FROM {$db_prefix}categories");
	while ($cate = $DB->fetch_array($query)) {
		$DB->query("INSERT INTO {$db_prefix}metas (name, type, displayorder) VALUES ('".addslashes($cate['name'])."', 'category', '".addslashes($cate['displayorder'])."')");
		$mid = $DB->insert_id();

		$articles = $DB->query("SELECT articleid, visible FROM {$db_prefix}articles WHERE cid='".$cate['cid']."'");
		while ($article = $DB->fetch_array($articles)) {
			$r  = $DB->fetch_one_array("SELECT cid FROM {$db_prefix}relationships WHERE mid='$mid' LIMIT 1");
			if (!$r) {
				$DB->query("INSERT INTO {$db_prefix}relationships (cid,mid) VALUES ('".$article['articleid']."', '$mid')");
			} else {
				if ($article['articleid'] != $r['cid']) {
					$DB->query("INSERT INTO {$db_prefix}relationships (cid,mid) VALUES ('".$article['articleid']."', '$mid')");
				}
			}
			if ($article['visible']) {
				$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='$mid' AND type='category'");
			}
		}
	}

	echo '<div class="install_main">';
	echo '<p class="p2">成功重建所有分类数据</p><p class="p2"><a href="'.$php_self.'?action=second">程序将自动跳转.如果没有自动跳转,请点击这里.</a></p>';
	echo '<meta HTTP-EQUIV="REFRESH" content="2;URL='.$php_self.'?action=second">';
	echo '</div></body></html>';
	exit;
} elseif ($action == 'second') {
	$query = $DB->query("SELECT articleid, cid, keywords, visible FROM {$db_prefix}articles LIMIT $start, $percount");
	while($article = $DB->fetch_array($query)){
		$goon = 1;
		//关联标签
		if ($article['keywords']) {
			$tagdb = explode(',', $article['keywords']);
			foreach($tagdb as $tag) {
				$tag = sax_addslashes(trim($tag));
				if ($tag) {
					$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE name='$tag' AND type='tag' LIMIT 1");
					if(!$r) {
						$DB->query("INSERT INTO {$db_prefix}metas (name,slug,type) VALUES ('$tag', '$tag', 'tag')");
						$mid = $DB->insert_id();
						$DB->query("INSERT INTO {$db_prefix}relationships (cid,mid) VALUES ('".$article['articleid']."', '$mid')");
					} else {
						$mid = $r['mid'];
						$r2  = $DB->fetch_one_array("SELECT cid FROM {$db_prefix}relationships WHERE mid='$mid' LIMIT 1");
						if (!$r2) {
							$DB->query("INSERT INTO {$db_prefix}relationships (cid,mid) VALUES ('".$article['articleid']."', '$mid')");
						} else {
							if ($article['articleid'] != $r2['cid']) {
								$DB->query("INSERT INTO {$db_prefix}relationships (cid,mid) VALUES ('".$article['articleid']."', '$mid')");
							}
						}
					}					
					if ($article['visible']) {
						$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='$mid' AND type='tag'");
					}
				}
			}
		}		
		$attach_total = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments WHERE articleid='".$article['articleid']."'"), 0);
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET attachments='$attach_total' WHERE articleid='".$article['articleid']."'");
	}
	echo '<div class="install_main">';
	if($goon) {
		echo '<p class="p2">文章中的数据正在更新 '.$start.' 到 '.$next.' 项</p><p class="p2"><a href="'.$jumpurl.'">程序将自动跳转.如果没有自动跳转,请点击这里.</a></p>';
		echo '<meta HTTP-EQUIV="REFRESH" content="2;URL='.$jumpurl.'">';
	} else {
		echo '<p class="p2">成功重建所有文章内的数据</p><p class="p2"><a href="'.$php_self.'?action=second">程序将自动跳转.如果没有自动跳转,请点击这里.</a></p>';
		echo '<meta HTTP-EQUIV="REFRESH" content="2;URL='.$php_self.'?action=three">';
	}
	echo '</div></body></html>';
	exit;
} elseif ($action == 'three') {
	$query = $DB->query("SELECT * FROM {$db_prefix}trackbacks LIMIT $start, $percount");
	while ($trackback = $DB->fetch_array($query)) {
		$goon = 1;
		$DB->query("INSERT INTO {$db_prefix}comments (articleid, author, url, dateline, content, ipaddress, type, visible) VALUES ('".$trackback['articleid']."', '".addslashes($trackback['blog_name'])."', '".addslashes($trackback['url'])."', '".$trackback['dateline']."', '".addslashes($trackback['title'])."\n".addslashes($trackback['excerpt'])."', '".addslashes($trackback['ipaddress'])."', 'trackback', '".$trackback['visible']."')");
	}
	if($goon){
		echo '<p class="p2">正在更新 '.$start.' 到 '.$next.' 项</p><p class="p2"><a href="'.$jumpurl.'">程序将自动跳转.如果没有自动跳转,请点击这里.</a></p>';
		echo '<meta HTTP-EQUIV="REFRESH" content="2;URL='.$jumpurl.'">';
	} else{
		echo '<p class="p2">成功重建所有文章内的数据</p><p class="p2"><a href="'.$php_self.'?action=second">程序将自动跳转.如果没有自动跳转,请点击这里.</a></p>';
		echo '<meta HTTP-EQUIV="REFRESH" content="2;URL='.$php_self.'?action=four">';
	}
	echo '</div></body></html>';
	exit;
} elseif ($action == 'four') {
$add = <<<EOT
ALTER TABLE {$db_prefix}users ADD COLUMN email varchar(40) NOT NULL,
	ADD COLUMN lastactivity int(10) unsigned NOT NULL,
	ADD COLUMN lastip varchar(16) NOT NULL,
	ADD COLUMN lastvisit int(10) unsigned NOT NULL;
ALTER TABLE {$db_prefix}users MODIFY COLUMN articles int(11) unsigned NOT NULL,
	MODIFY COLUMN groupid smallint(4) unsigned NOT NULL,
	MODIFY COLUMN lastpost int(10) unsigned NOT NULL,
	MODIFY COLUMN logincount smallint(6) unsigned NOT NULL,
	MODIFY COLUMN logintime int(10) unsigned NOT NULL,
	MODIFY COLUMN password char(32) NOT NULL,
	MODIFY COLUMN regdateline int(10) unsigned NOT NULL,
	MODIFY COLUMN url varchar(75) NOT NULL;
ALTER TABLE {$db_prefix}stylevars MODIFY COLUMN stylevarid mediumint(9) unsigned NOT NULL auto_increment,
	ADD COLUMN description varchar(200) NOT NULL;
ALTER TABLE {$db_prefix}statistics MODIFY COLUMN article_count int(11) unsigned NOT NULL,
	MODIFY COLUMN comment_count int(11) unsigned NOT NULL,
	MODIFY COLUMN tag_count int(11) unsigned NOT NULL;
ALTER TABLE {$db_prefix}sessions ADD COLUMN auth_key varchar(32) NOT NULL,
	ADD COLUMN ip1 tinyint(3) unsigned NOT NULL,
	ADD COLUMN ip2 tinyint(3) unsigned NOT NULL,
	ADD COLUMN ip3 tinyint(3) unsigned NOT NULL,
	ADD COLUMN ip4 tinyint(3) unsigned NOT NULL,
	ADD COLUMN is_robot tinyint(1) NOT NULL,
	ADD COLUMN seccode varchar(6) NOT NULL;
ALTER TABLE {$db_prefix}sessions MODIFY COLUMN groupid smallint(4) unsigned NOT NULL,
	MODIFY COLUMN hash char(6) NOT NULL,
	MODIFY COLUMN lastactivity int(10) unsigned NOT NULL,
	MODIFY COLUMN uid mediumint(8) unsigned NOT NULL;
ALTER TABLE {$db_prefix}searchindex ADD COLUMN expiration int(10) unsigned NOT NULL,
	ADD COLUMN searchstring varchar(255) NOT NULL,
	ADD COLUMN totals smallint(6) unsigned NOT NULL,
	ADD COLUMN uid mediumint(8) unsigned NOT NULL;
ALTER TABLE {$db_prefix}searchindex MODIFY COLUMN dateline int(10) unsigned NOT NULL,
	MODIFY COLUMN searchid int(11) unsigned NOT NULL auto_increment;
ALTER TABLE {$db_prefix}links ADD COLUMN home tinyint(1) NOT NULL;
ALTER TABLE {$db_prefix}comments ADD COLUMN email varchar(40) NOT NULL,
	ADD COLUMN type enum('comment','trackback') NOT NULL default 'comment';
ALTER TABLE {$db_prefix}comments MODIFY COLUMN author varchar(40) NOT NULL,
	MODIFY COLUMN url varchar(75) NOT NULL;
ALTER TABLE {$db_prefix}attachments MODIFY COLUMN articleid mediumint(8) unsigned NOT NULL,
	MODIFY COLUMN downloads mediumint(8) unsigned NOT NULL,
	MODIFY COLUMN isimage tinyint(1) NOT NULL;
ALTER TABLE {$db_prefix}articles ADD COLUMN alias varchar(200) NOT NULL,
	ADD COLUMN pingurl tinytext NOT NULL;
ALTER TABLE {$db_prefix}articles MODIFY COLUMN attachments tinyint(4) NOT NULL,
	MODIFY COLUMN stick tinyint(1) NOT NULL;
INSERT INTO {$db_prefix}settings (title, value) VALUES ('use_html', '0');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_adulterate', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_angle', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_ttf', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('wap_article_limit', '1000');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('permalink', '0');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('close_comment', '0');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('rss_all_output', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('sitemap', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('recentarticle_num', '10');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('recentarticle_limit', '0');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('maxpages', '1000');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('show_n_p_title', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_color', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_size', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('seccode_shadow', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('article_shownum', '6');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('dateconvert', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('article_timeformat', 'Y-m-d, g:i A');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('show_avatar', '1');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('avatar_size', '36');
INSERT INTO {$db_prefix}settings (title, value) VALUES ('avatar_level', 'G');
ALTER TABLE {$db_prefix}articles 
	DROP cid, DROP keywords,
	DROP trackbacks;
ALTER TABLE {$db_prefix}links 
	DROP displayorder ;
ALTER TABLE {$db_prefix}searchindex
	DROP tatols,
	DROP searchfrom ;
ALTER TABLE {$db_prefix}sessions
	DROP ipaddress,
	DROP agent ;
ALTER TABLE {$db_prefix}statistics
	DROP cate_count,
	DROP attachment_count,
	DROP all_view_count,
	DROP today_view_count,
	DROP trackback_count,
	DROP user_count,
	DROP curdate;
DELETE FROM {$db_prefix}settings WHERE title = 'allow_search_comments' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'archives_num' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'artlink_ext' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'attachments_display' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'js_cache_life' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'js_enable' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'js_lock_url' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'listtime' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'list_shownum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'normaltime' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'normal_shownum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'random_links' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'related_order' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'rewrite_enable' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'rewrite_ext' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'search_keywords_min_len' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'search_post_space' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'seccode_enable' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'sidebarlinknum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'smarturl' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'tags_shownum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'title_limit' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'trackback_excerpt_limit' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'trackback_list_excerpt_limit' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'trackback_num' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'trackback_order' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'trackback_timeformat' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'viewmode' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'wap_article_pagenum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'wap_article_title_limit' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'wap_comment_pagenum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'wap_tags_pagenum' LIMIT 1;
DELETE FROM {$db_prefix}settings WHERE title = 'wap_trackback_pagenum' LIMIT 1;
EOT;
	runquery($add);
	$DB->query("DROP TABLE {$db_prefix}trackbacklog");
	$DB->query("DROP TABLE {$db_prefix}trackbacks");
	$DB->query("DROP TABLE {$db_prefix}categories");
	$DB->query("DROP TABLE {$db_prefix}tags");
	echo '<p class="p2">升级成功，请进入后台重建数据以及更新缓存并仔细检查系统设置以完成整个升级过程。</p>';
	echo '</div></body></html>';
	exit;
} else {
?>
<div class="install_main">
  <div id="install_innertext">
    <p class="p2">感谢您选择由 <a href="http://www.4ngel.net/" target="_blank">安全天使网络安全小组</a> 开发的 <a href="http://www.4ngel.net/">SaBlog-X</a> 博客程序!</p>
    <p class="p2">当前版本为 <u>SaBlog-X v 1.6</u></p>
    <p class="p2">目标版本为 <u>SaBlog-X v 2.0</u></p>
    <p class="p2">本程序仅适用于1.6升级到2.0，不适合任何2.0测试版本升级到2.0正式版。</p>
    <p class="p2">升级过程完全不用人工干预,请耐心等待成功带来的喜悦.</p>
	<p class="p2"><a href="<?=$php_self?>?action=first">升级数据</a></p>
  </div>
</div>
<?php
}
?>
<div class="copyright">Powered by SaBlog-X (C) 2003-2012 Security Angel Team</div>
</body>
</html>
