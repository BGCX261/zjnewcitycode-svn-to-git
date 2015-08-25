<?
// ========================== 文件说明 ==========================//
// 本文件说明：WAP文章附件缩略图输出
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

define('SABLOG_ROOT', substr(dirname(__FILE__), 0, -3));


// 加载数据库配置信息
require_once(SABLOG_ROOT.'config.php');
// 加载数据库类
require_once(SABLOG_ROOT.'include/class/mysql.class.php');
// 初始化数据库类
$DB = new DB_MySQL;
$DB->connect($servername, $dbusername, $dbpassword, $dbname, $usepconnect);
unset($servername, $dbusername, $dbpassword, $dbname, $usepconnect);

require_once('global.php');
require_once(SABLOG_ROOT.'data/cache/cache_settings.php');

$attachid = (int)$_GET['attachid'];

$attachinfo = $DB->fetch_one_array("SELECT at.* FROM {$db_prefix}attachments at LEFT JOIN {$db_prefix}articles ar ON (ar.articleid=at.articleid) WHERE ar.visible='1' AND at.attachmentid='$attachid'");

$filepath = SABLOG_ROOT.$options['attachments_dir'].$attachinfo['filepath'];

if (file_exists($filepath)) {
	Thumb_GD($filepath, 200, 200);
}

?>