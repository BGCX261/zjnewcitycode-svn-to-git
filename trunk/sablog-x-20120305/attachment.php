<?php
// ========================== 文件说明 ==========================//
// 本文件说明：附件输出
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

// 加载前台常用函数
require_once('global.php');

if (IS_ROBOT && !checkr_ua()) {
	exit(header("HTTP/1.1 403 Forbidden"));
}

if ($options['remote_open']) {
	$uinfo = parse_url($options['url']);
	$ourhost = str_replace('www.', '', $uinfo['host']);
	//如果包含端口信息,去掉端口,否则都会禁止下载
	if (strpos($ourhost,':')){
		$ourhost = substr($ourhost, 0, strrpos($ourhost, ':'));
	}
	$uinfo = parse_url($_SERVER['HTTP_REFERER']);
	$remotehost = str_replace('www.', '', $uinfo['host']);
	if (strpos($remotehost,':')){
		$remotehost = substr($remotehost, 0, strrpos($remotehost, ':'));
	}
	unset($uinfo);
	if ($ourhost != $remotehost) {
		message('附件禁止从地址栏直接输入或从其他站点链接访问', './');
	}
}

// 查询文章
$attachmentid = (int)$_GET['id'];
if (!$attachmentid){
	message('缺少参数', './');
} else {
	$attachinfo = $DB->fetch_one_array("SELECT at.*, ar.visible, ar.articleid as artid FROM {$db_prefix}attachments at LEFT JOIN {$db_prefix}articles ar ON (ar.articleid=at.articleid) WHERE ar.visible='1' AND at.attachmentid='$attachmentid'");
	if (!$attachinfo) {
		message('附件无效', './');
	}
	$DB->unbuffered_query("UPDATE {$db_prefix}attachments SET downloads=downloads+1 WHERE attachmentid='$attachmentid'");
}
$DB->close();

$filepath = SABLOG_ROOT.$options['attachments_dir'].$attachinfo['filepath'];

$isimage = 0;
if (stristr($attachinfo['filetype'], 'image')){ 
	$imginfo = @getimagesize($filepath); 
	if ($imginfo[2] && $imginfo['bits']) {
		$isimage = 1;
	}
	unset($imginfo);
}

$attachment = $isimage ? ($options['display_attach'] ? 'inline' : 'attachment') : 'attachment';
$attachinfo['filetype'] = $attachinfo['filetype'] ? $attachinfo['filetype'] : 'unknown/unknown';


if(is_readable($filepath)) {
	ob_end_clean();
	header('Cache-control: max-age=31536000');
	header('Expires: ' . gmdate('D, d M Y H:i:s',$timestamp+31536000) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s',$attachinfo['dateline']) . ' GMT');
	header('Content-Encoding: none');
	header('Content-type: '.$attachinfo['filetype']);
	header('Content-Disposition: '.$attachment.'; filename='.urlencode($attachinfo['filename']));
	header('Content-Length: '.filesize($filepath));
	error_reporting(0);
	readfile($filepath);
	flush();
	ob_flush();
	exit;
} else {
	message('读取附件失败', './');
}

?>