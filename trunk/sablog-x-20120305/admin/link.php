<?php
// ========================== 文件说明 ==========================//
// 本文件说明：友情链接管理
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

$linkid = (int)$linkid;

if ($message) {
	$messages = array(
		1 => '该链接已经存在',
		2 => '添加链接成功',
		3 => '编辑链接成功',
		4 => '至少要填写名称和地址',
		5 => '选定项目已删除',
		6 => '选定项目已启用',
		7 => '选定项目已禁用',
		8 => '选定项目已设置首页显示',
		9 => '选定项目已取消首页显示',
		10 => '没有选择任何项目',
		11 => '链接不存在'
	);
}

!$action && $action = 'list';
$location = '';
$doit = in_array($doit,array('home','page','delete','enable','disable')) ? $doit : '';

if ($action == 'addlink' || $action == 'modlink') {
	$new_name = char_cv(trim($_POST['new_name']));
	$new_url = char_cv(trim($_POST['new_url']));
	$new_note = char_cv(trim($_POST['new_note']));
	if($new_name && $new_url) {
		if ($action == 'addlink') {
			$query = $DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links WHERE name='$new_name' AND url='$new_url'");
		} else {
			$query = $DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links WHERE name='$new_name' AND url='$new_url' AND linkid!='$linkid'");
		}
		if($DB->result($query, 0)) {
			$location = getlink('link', 'list', array('message'=>1));
		} else {
			if ($action == 'addlink') {
				$DB->query("INSERT INTO	{$db_prefix}links (name,url,note,visible) VALUES ('$new_name','$new_url','$new_note','1')");
				$location = getlink('link', 'list', array('message'=>2));
			} else {
				$DB->query("UPDATE {$db_prefix}links SET name='$new_name', url='$new_url', note='$new_note' WHERE linkid='$linkid'");
				$location = getlink('link', 'list', array('message'=>3));
			}
			links_recache();
		}
	} else {
		$location = getlink('link', str_replace('link', '', $action), array('message'=>4));
	}
	header("Location: {$location}");
	exit;
}

//批量处理
if($action == 'domorelink') {
	if($doit && $ids = implode_ids($_POST['selectall'])) {
		if ($doit == 'delete') {
			$DB->query("DELETE FROM	{$db_prefix}links WHERE linkid IN ($ids)");
			$location = getlink('link', 'list', array('message'=>5));
		} elseif ($doit == 'enable') {
			$DB->query("UPDATE {$db_prefix}links SET visible='1' WHERE linkid IN ($ids)");
			$location = getlink('link', 'list', array('message'=>6));
		} elseif ($doit == 'disable') {
			$DB->query("UPDATE {$db_prefix}links SET visible='0' WHERE linkid IN ($ids)");
			$location = getlink('link', 'list', array('message'=>7));
		} elseif ($doit == 'home') {
			$DB->query("UPDATE {$db_prefix}links SET home='1' WHERE linkid IN ($ids)");
			$location = getlink('link', 'list', array('message'=>8));
		} elseif ($doit == 'page') {
			$DB->query("UPDATE {$db_prefix}links SET home='0' WHERE linkid IN ($ids)");
			$location = getlink('link', 'list', array('message'=>9));
		}
		links_recache();
	} else {
		$location = getlink('link', 'list', array('message'=>10));
	}
	header("Location: {$location}");
	exit;
}

if ($linkid) {
	$do = 'modlink';
	$doname = '修改链接';
	$linkinfo = $DB->fetch_one_array("SELECT * FROM {$db_prefix}links WHERE linkid='$linkid'");
	if (!$linkinfo) {
		$do = 'addlink';
		$doname = '添加链接';
	}
} else {
	$do = 'addlink';
	$doname = '添加链接';
}

$sql_query = ' WHERE 1=1 ';

$alltotal		= $DB->result($DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links"), 0);
$displaytotal	= $DB->result($DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links WHERE visible='1'"), 0);
$hiddentotal	= $alltotal - $displaytotal;
$hometotal		= $DB->result($DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links WHERE home='1'"), 0);

$view = in_array($view, array('display','hidden','home')) ? $view : '';
if ($view == 'display') {
	$sql_query .= " AND visible='1'";
	$pagelink   = '&view=display';
	$subnav     = '显示的链接';
} elseif ($view == 'hidden') {
	$sql_query .= " AND visible='0'";
	$pagelink   = '&view=hidden';
	$subnav     = '隐藏的链接';
} elseif ($view == 'home') {
	$sql_query .= " AND home='1'";
	$pagelink   = '&view=home';
	$subnav     = '首页显示的链接';
}

if($page) {
	$start_limit = ($page - 1) * 20;
} else {
	$start_limit = 0;
	$page = 1;
}
$total = $DB->result($DB->query("SELECT COUNT(linkid) FROM {$db_prefix}links $sql_query"), 0);
if ($total) {
	$multipage = multi($total, 20, $page, 'cp.php?job=link&amp;action=list'.$pagelink);

	$query = $DB->query("SELECT * FROM {$db_prefix}links $sql_query ORDER BY linkid DESC LIMIT $start_limit, 20");
	$linkdb = array();
	while ($link = $DB->fetch_array($query)) {
		$linkdb[$link['linkid']] = $link;
	}
	unset($link);
	$DB->free_result($query);
}

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('link');
?>