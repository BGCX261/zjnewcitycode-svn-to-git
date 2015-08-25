<?php
// ========================== 文件说明 ==========================//
// 本文件说明：系统设置
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

//权限检查
permission(1);

if ($message) {
	$messages = array(
		1 => '更新系统配置成功'
	);
}

$settingsmenu = array(
	'all' => '全部',
	'basic' => '基本设置',
	'display' => '显示设置',
	'comment' => '评论设置',
	'attach' => '附件设置',
	'dateline' => '时间设置',
	'seo' => 'SEO设置',
	'wap' => 'WAP设置',
	'ban' => '安全设置',
	'rss' => 'RSS设置',
	'permalink' => '伪静态设置',
);

!$action && $action = 'basic';

// 更新配置以及配置文件
if($_POST['action'] == 'updatesetting') {
	//$DB->query("TRUNCATE TABLE {$db_prefix}settings");
	foreach($_POST['setting'] as $key => $val) {
		$DB->query("REPLACE INTO {$db_prefix}settings VALUES ('".sax_addslashes($key)."', '".sax_addslashes($val)."')");
	}
	if ($oldaction == 'all' || $oldaction == 'display') {
		newarticles_recache();
		archives_recache();
		hottags_recache();
	}
	if ($oldaction == 'all' || $oldaction == 'comment') {
		newcomments_recache();
	}
	settings_recache();
	$location = getlink('configurate', $oldaction, array('message'=>1));
	header("Location: {$location}");
	exit;
} //end update

$query = $DB->query("SELECT * FROM {$db_prefix}settings");
while($setting = $DB->fetch_array($query)) {
	$settings[$setting['title']] = htmlspecialchars($setting['value']);
}

ifchecked($settings['show_calendar'],'show_calendar');
ifchecked($settings['show_statistics'],'show_statistics');
ifchecked($settings['show_debug'],'show_debug');
ifchecked($settings['show_n_p_title'],'show_n_p_title');
ifchecked($settings['rss_all_output'],'rss_all_output');
ifchecked($settings['close_comment'],'close_comment');
ifchecked($settings['audit_comment'],'audit_comment');
ifchecked($settings['comment_order'],'comment_order');
ifchecked($settings['show_avatar'],'show_avatar');
ifchecked($settings['attachments_thumbs'],'attachments_thumbs');
ifchecked($settings['display_attach'],'display_attach');
ifchecked($settings['remote_open'],'remote_open');
ifchecked($settings['close'],'close');
ifchecked($settings['closereg'],'closereg');
ifchecked($settings['gzipcompress'],'gzipcompress');
ifchecked($settings['showmsg'],'showmsg');
ifchecked($settings['enable_trackback'],'enable_trackback');
ifchecked($settings['trackback_life'],'trackback_life');
ifchecked($settings['audit_trackback'],'audit_trackback');
ifchecked($settings['sitemap'],'sitemap');
ifchecked($settings['watermark'],'watermark');
ifchecked($settings['wap_enable'],'wap_enable');
ifchecked($settings['banip_enable'],'banip_enable');
ifchecked($settings['spam_enable'],'spam_enable');
ifchecked($settings['rss_enable'],'rss_enable');
ifchecked($settings['use_html'],'use_html');
ifchecked($settings['permalink'],'permalink');
ifchecked($settings['jumpwww'],'jumpwww');
ifchecked($settings['comment_email_reply'],'comment_email_reply');

ifchecked($settings['seccode'],'seccode');
ifchecked($settings['seccode_adulterate'],'seccode_adulterate');
ifchecked($settings['seccode_ttf'],'seccode_ttf');
ifchecked($settings['seccode_angle'],'seccode_angle');
ifchecked($settings['seccode_color'],'seccode_color');
ifchecked($settings['seccode_size'],'seccode_size');
ifchecked($settings['seccode_shadow'],'seccode_shadow');

ifchecked($settings['dateconvert'],'dateconvert');

$waterpos = $tb_spam_level = array();;

$settings['server_timezone'] < 0 ? ${'zone_0'.str_replace('.','_',abs($settings['server_timezone']))}='checked' : ${'zone_'.str_replace('.','_',$settings['server_timezone'])}='selected';

$waterpos[$settings['waterpos']] = 'checked';
$tb_spam_level[$settings['tb_spam_level']] = 'checked';
$avatar_level[$settings['avatar_level']] = 'checked';

$gd_version = gd_version();
$gd_version = $gd_version ? '服务器GD版本:'.$gd_version : '服务器不支持GD,因此该功能无法正常使用.';

if (in_array($action, array_flip($settingsmenu))) {
	$subnav = $settingsmenu[$action];
}

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('configurate');
?>