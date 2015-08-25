<?php
// ========================== 文件说明 ==========================//
// 本文件说明：SwfUpload用的
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

permission(array(1,2));

// 加载附件相关函数
require_once(SABLOG_ROOT.'include/func/attachment.func.php');

//删除临时上传的附件
if ($action == 'delattach') {
	$attachid = intval($_GET['attachid'] ? $_GET['attachid'] : $_POST['attachid']);
	$attachids = $_GET['attachids'] ? $_GET['attachids'] : $_POST['attachids'];
	if ($attachid) {
		remove_one_attachment($attachid);
	}
	if ($attachids) {
		$iddb = explode(',', $attachids);
		foreach ($iddb as $attachid) {
			remove_one_attachment($attachid);
		}
	}
	exit;
}

$uploadmode = 'swf';
// 上传附件
$searcharray = array();
$replacearray = array();
require_once(SABLOG_ROOT.'admin/uploadfiles.php');

echo $new_attachid;
exit;
// 上传结束

function uploaderrormsg($msg) {
	echo 'upload-error:'.$msg;
	exit;
}

?>