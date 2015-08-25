<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前后台公共操作
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

error_reporting(7);
set_magic_quotes_runtime(0);
header("content-Type: text/html; charset=UTF-8");

session_start();
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('SABLOG_ROOT', substr(dirname(__FILE__), 0, -7));
define('MODULE_DIR', SABLOG_ROOT.'modules/');

$SABLOG_VERSION = '2.0';
$SABLOG_RELEASE = '20120305';

// 防止一些低级的XSS
if($_SERVER['REQUEST_URI']) {
	$temp = urldecode($_SERVER['REQUEST_URI']);
	if(strpos($temp, '<') !== false || strpos($temp, '>') !== false || strpos($temp, '(') !== false || strpos($temp, '"') !== false) {
		exit('Request Bad url');
	}
}

// 加载核心函数
require_once(SABLOG_ROOT.'include/func/global.func.php');

$action = addslashes($_POST['action'] ? $_POST['action'] : $_GET['action']);
$php_self = char_cv($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
$timestamp = time();
//登陆存活期一个月
$login_life = 2592000;

// 防止 PHP 5.1.x 使用时间函数报错
if(PHP_VERSION > '5.1') {
	@date_default_timezone_set('UTC');
}

define('IS_ROBOT', isrobot());
$referer = getreferer();

// 加载数据库配置信息
require_once(SABLOG_ROOT.'config.php');

// 检查防刷新或代理访问
if($attackevasive) {
	require_once(SABLOG_ROOT.'include/fense.inc.php');
}
// 加载数据库类
require_once(SABLOG_ROOT.'include/class/mysql.class.php');
// 初始化数据库类
$DB = new DB_MySQL;
$DB->connect($servername, $dbusername, $dbpassword, $dbname, $usepconnect);
unset($servername, $dbusername, $dbpassword, $dbname, $usepconnect);

// 获得IP地址
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
$onlineip = char_cv($onlineip);
@preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);

require_once(SABLOG_ROOT.'include/func/permalink.func.php');
require_once(SABLOG_ROOT.'include/func/cache.func.php');

// 读取缓存
if (!@include(SABLOG_ROOT.'data/cache/cache_settings.php')) {
	rethestats('settings');
	exit('<p>Settings caches successfully created, please refresh.</p>');
}

if($options['jumpwww'] && $_SERVER['HTTP_HOST'] != 'localhost' && substr($_SERVER['HTTP_HOST'],0,4)!='www.') {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location:http://www.'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	exit();
}

// 检查gzip加速支持情况
if ($options['gzipcompress'] && extension_loaded('zlib')) {
	@ob_start('ob_gzhandler');
} else {
	$options['gzipcompress'] = 0;
	ob_start();
}
!$options['templatename'] && $options['templatename'] = 'default';
$maxpages = $options['maxpages'] ? $options['maxpages'] : 1000;
$options['title'] = $options['name'];
$timeoffset = (!$options['server_timezone'] || $options['server_timezone'] == '111') ? 0 : $options['server_timezone'];

// 系统URL
if (!$options['url']) {
	//HTTP_HOST已经包含端口信息,不必加SERVER_PORT了.
	$options['url'] = 'http://'.char_cv($_SERVER['HTTP_HOST']).preg_replace("/\/+(admin|archiver|tools|wap)?\/*$/i", '', substr($php_self, 0, strrpos($php_self, '/'))).'/';
} else {
	$options['url'] = str_replace(array('{host}','index.php'), array(char_cv($_SERVER['HTTP_HOST']),''), $options['url']);
	if (substr($options['url'], -1) != '/') {
		$options['url'] = $options['url'].'/';
	}
}

$cachelost  = '';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_statistics.php') ? '' : 'statistics,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_newarticles.php') ? '' : 'newarticles,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_stick.php') ? '' : 'stick,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_newcomments.php') ? '' : 'newcomments,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_categories.php') ? '' : 'categories,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_archives.php') ? '' : 'archives,';
if ($options['randarticle_num']) {
	$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_allarticleids.php') ? '' : 'allarticleids,';
}
if ($options['hottags_shownum']) {
	$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_hottags.php') ? '' : 'hottags,';
}
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_links.php') ? '' : 'links,';
$cachelost .= (@include SABLOG_ROOT.'data/cache/cache_stylevars.php') ? '' : 'stylevars,';

if($cachelost) {
	$cachelost = explode(',',$cachelost);
	echo '<p>Cache List:</p><p>';
	foreach ($cachelost as $name) {
		if($name) {
			rethestats($name);
			echo $name.'<br />';
		}
	}
	exit('</p><p>Caches successfully created, please refresh.</p>');
}

// 将缓存中的转义字符去掉
$options = sax_stripslashes($options);
$linkcache = sax_stripslashes($linkcache);
$catecache = sax_stripslashes($catecache);
if ($stylevar) {
	$stylevar = sax_stripslashes($stylevar);
}
$newcommentcache = sax_stripslashes($newcommentcache);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// 允许程序在 register_globals = off 的环境下工作
$onoff = function_exists('ini_get') ? ini_get('register_globals') : get_cfg_var('register_globals');
if ($onoff != 1) {
	@extract($_COOKIE, EXTR_SKIP);
	@extract($_POST, EXTR_SKIP);
	@extract($_GET, EXTR_SKIP);
}

// 判断 magic_quotes_gpc 状态
if (@get_magic_quotes_gpc()) {
    $_GET = sax_stripslashes($_GET);
    $_POST = sax_stripslashes($_POST);
    $_COOKIE = sax_stripslashes($_COOKIE);
}


if ($_POST['sax_cookie_auth']) {
	list($sax_uid, $sax_pw, $sax_logincount) = explode("\t", authcode($_POST['sax_cookie_auth'], 'DECODE'));
} else {
	list($sax_uid, $sax_pw, $sax_logincount) = $_COOKIE['sax_auth'] ? explode("\t", authcode($_COOKIE['sax_auth'], 'DECODE')) : array('', '', 0);
}

$sax_uid = (int)$sax_uid;
$sax_pw = sax_addslashes($sax_pw);
$sax_logincount = (int)$sax_logincount;
$sax_group = 4;
$_EVO = array();

$seccode = 0;
if ($sax_uid) {
	$query = $DB->query("SELECT userid AS sax_uid, username AS sax_user, password AS sax_pw, groupid AS sax_group, logincount AS sax_logincount, email as sax_email, url as sax_url, lastpost, lastip, lastvisit, lastactivity
		FROM {$db_prefix}users
		WHERE userid='$sax_uid' AND password='$sax_pw' AND logincount='$sax_logincount'");
	$_EVO = $DB->fetch_array($query);

	if(!$_EVO) {
		dcookies();
	}
}
@extract($_EVO);

$lastvisit = !$lastvisit ? $timestamp : $lastvisit;
if(!$sax_uid || !$sax_user) {
	$sax_uid = $sax_logincount = 0;
	$sax_user = '';
	$sax_group = 6;

}
if ($sax_group == 1) {
	error_reporting(7);
}

$formhash = formhash();
/*
if ($options['active_plugins']) {
	//如果设置了插件
	$plugins = unserialize($options['active_plugins']);
	if (is_array($plugins)) {
		//遍历插件名字
		foreach ($plugins as $key => $plugin) {
			//存在并且不包含..才包含文件
			if ($plugin && file_exists(SABLOG_ROOT.'plugins/'.$plugin) && strpos($plugin,'..')===false) {
				include_once(SABLOG_ROOT.'plugins/'.$plugin);
			} else {
				//否则删除插件纪录
				unset($plugins[$key]);
				if ($plugins){
					$plugins = sax_addslashes(serialize($plugins));
				}
				require_once(SABLOG_ROOT.'include/func/cache.func.php');
				$DB->query("REPLACE INTO {$db_prefix}settings VALUES ('active_plugins', '$plugins')");
				settings_recache();
			}
		}
	}
}
print_r($_GET);
echo '<hr>';
print_r($_POST);
echo '<hr>';
print_r($_REQUEST);

*/

// 调试函数
function pr($a) {
	echo '<div style="text-align: left;border:1px solid #ddd;">';
	echo '<pre>';
	print_r($a);
	echo '</pre>';
	echo '</div>';
}
?>