<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台主程序
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('global.php');
//require_once(SABLOG_ROOT.'include/query.inc.php');

!$action  && $action = 'article';

//清除浏览文章记录
if ($_GET['action'] == 'clearalready') {
	if(is_array($_COOKIE['articleids'])){
		foreach($_COOKIE['articleids'] as $key => $value){
			scookie("articleids[".$key."]", '');
		}
	}
	message('已经删除浏览过的文章记录', $referer);
}

$page = $maxpages && $page > $maxpages ? 1 : $page;

$moduledb = array(
	'article',
	'show',
	'tagslist',
	'archives',
	'links'
);

if (in_array($action, $moduledb)) {
	
	$archivenum = count($archivecache);
	//前台显示12个归档就可以了.显示那么多干嘛?
	if ($archivenum > 12) {
		$archivecache = array_slice($archivecache, 0, 12);
	}
	$module = loadmodule($action);
} else {
	message('未知模块');
}

include $module;
include template($pagefile);

updatesession();
footer();

?>