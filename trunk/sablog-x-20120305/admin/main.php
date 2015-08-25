<?php
// ========================== 文件说明 ==========================//
// 本文件说明：后台首页
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

if(!defined('SABLOG_ROOT') || !isset($php_self) || !preg_match("/[\/\\\\]cp\.php$/", $php_self)) {
	exit('Access Denied');
}

function getphpcfg($varname) {
	switch($result = @get_cfg_var($varname)) {
		case 0:
			return '关闭';
			break;
		case 1:
			return '打开';
			break;
		default:
			return $result;
			break;
	}
}

function getfun($funName) {
	return (function_exists($funName)) ? '支持' : '不支持';
}

if (@ini_get('file_uploads')) {
	$fileupload = '允许 '.ini_get('upload_max_filesize');
} else {
	$fileupload = '<font color="red">禁止</font>';
}

$globals  = getphpcfg('register_globals');
$safemode = getphpcfg('safe_mode');
$gd_version = gd_version();
$gd_version = $gd_version ? '版本:'.$gd_version : '不支持';

//查询数据信息
$hiddenarttotal = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE visible='0'"), 0);
$hiddencomtotal = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE visible='0'"), 0);
$tagtotal = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type='tag'"), 0);
$linktotal = $DB->result($DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links"), 0);

$server['datetime'] = sadate('Y-m-d H:i:s');
$server['software'] = $_SERVER['SERVER_SOFTWARE'];
if (function_exists('memory_get_usage')) {
	$server['memory_info'] = get_real_size(memory_get_usage());
}


$mysql_version = mysql_get_server_info();
$mysql_runtime = '';
$query = $DB->query("SHOW STATUS");
while ($r = $DB->fetch_array($query)) {
	if (eregi("^uptime", $r['Variable_name'])){
		$mysql_runtime = $r['Value'];
	}
}
$mysql_runtime = format_timespan($mysql_runtime);


require_once(SABLOG_ROOT.'include/func/attachment.func.php');
$attachdir = SABLOG_ROOT . $options['attachments_dir'];
$attachsize = dirsize($attachdir);
$dircount = dircount($attachdir);
$realattachsize = (is_numeric($attachsize)) ? sizecount($attachsize) : '不详';
$stats = $DB->fetch_one_array("SELECT count(attachmentid) as count, sum(filesize) as sum FROM {$db_prefix}attachments");
$stats['count'] = ($stats['count'] != 0) ? $stats['count'] : 0;
$stats['sum'] = ($stats['count'] == 0) ? '0 KB' : sizecount($stats['sum']);

$now_version = rawurlencode($SABLOG_VERSION);
$now_release = rawurlencode($SABLOG_RELEASE);
$now_hostname = rawurlencode($_SERVER['HTTP_HOST']);
$now_url = rawurlencode($options['url']);

cpheader();
include template('main');
?>