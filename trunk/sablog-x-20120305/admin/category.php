<?php
// ========================== 文件说明 ==========================//
// 本文件说明：分类&标签管理
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
		1 => '类型出错',
		2 => '名称不能为空并且不能超过30个字符',
		3 => '名称在数据库中已存在',
		4 => '自定义URL只允许大小写字母、数字、下划线和减号',
		5 => '自定义URL名称已经存在',
		6 => '添加成功',
		7 => '编辑成功',
		8 => '排序已更新',
		9 => '选定项目已删除',
		10 => '没有选择任何项目'
	);
}

$mid = (int)$mid;
!$action && $action = 'catelist';
$location = '';
if ($type == 'category') {
	$goaction = 'catelist';
} else {
	$goaction = 'taglist';
}

//添加/修改分类
if($action == 'add' || $action == 'mod') {
	$new_name = trim($_POST['new_name']);
	$new_url = trim($_POST['new_url']);
	$type = trim($_POST['type']);
	if (!in_array($type, array('category','tag'))) {
		$location = getlink('category', null, array('message'=>1));
	}
	if(!$new_name || getstrlen($new_name) > 30) {
		$location = getlink('category', $goaction, array('message'=>2));
	}

	$new_name = char_cv($new_name);
	if ($action == 'add') {
		$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE type='$type' AND name='$new_name' LIMIT 1");
	} else {
		$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE type='$type' AND mid!='$mid' AND name='$new_name' LIMIT 1");
	}
    if($r) {
		$location = getlink('category', $goaction, array('message'=>3));
    }

	if ($new_url) {
		if (!checkalias($new_url)) {
			$location = getlink('category', $goaction, array('message'=>4));
		} else {
			$new_url = char_cv($new_url);
			if ($action == 'add') {
				$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE type='$type' AND slug='$new_url' LIMIT 1");
			} else {
				$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE type='$type' AND slug='$new_url' AND mid!='$mid' LIMIT 1");
			}
			if($r) {
				$location = getlink('category', $goaction, array('message'=>5));
			}
		}
	} else {
		$new_url = $new_name;
	}

	if (!$location) {
		if ($action == 'add') {

			insert_meta($new_name,$new_url,$type);

			if ($type == 'tag') {
				$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count+1");
			}
			$location = getlink('category', $goaction, array('message'=>6));
		} else {
			$DB->unbuffered_query("UPDATE {$db_prefix}metas SET name='$new_name',slug='$new_url' WHERE mid='$mid' AND type='$type'");
			$location = getlink('category', $goaction, array('message'=>7));
		}
		categories_recache();
		statistics_recache();
		$new_url = $new_name = '';
	}
	header("Location: {$location}");
	exit;
}

// 更新分类排序
if ($action == 'update') {
	if(is_array($_POST['displayorder'])) {
		foreach($_POST['displayorder'] as $mid => $order) {
			$DB->unbuffered_query("UPDATE {$db_prefix}metas SET displayorder='".intval($order)."' WHERE mid='".intval($mid)."'");
		}
	}
	$location = getlink('category', $goaction, array('message'=>8));
	header("Location: {$location}");
	exit;
}

if ($action == 'delete') {
	if($mids = implode_ids($_POST['selectall'])) {
			
		$a_total = 0;
		// 删除分类
		$DB->query("DELETE FROM {$db_prefix}metas WHERE mid IN ($mids)");
		$aids = get_cids($mids);
		$DB->query("DELETE FROM {$db_prefix}relationships WHERE mid IN ($mids)");

		if ($aids) {
			$query = $DB->query("SELECT uid, visible FROM {$db_prefix}articles WHERE articleid IN ($aids)");
			while ($article = $DB->fetch_array($query)) {
				if ($article['visible']) {
					$a_total++;
					$DB->query("UPDATE {$db_prefix}users SET articles=articles-1 WHERE userid='".$article['uid']."'");
				}
			}//end while

			// 加载附件相关函数
			require_once(SABLOG_ROOT.'include/func/attachment.func.php');
			// 删除该分类下文章中的附件
			$query  = $DB->query("SELECT attachmentid,filepath,thumb_filepath FROM {$db_prefix}attachments WHERE articleid IN ($aids)");
			$nokeep = array();
			while($attach = $DB->fetch_array($query)) {
				$nokeep[$attach['attachmentid']] = $attach;
			}
			removeattachment($nokeep);

			$DB->unbuffered_query("DELETE FROM {$db_prefix}comments WHERE articleid IN ($aids)");
			// 删除分类下的文章
			$DB->unbuffered_query("DELETE FROM {$db_prefix}articles WHERE articleid IN ($aids)");
			hottags_recache();
			archives_recache();
			categories_recache();
			newarticles_recache();
			newcomments_recache();
		}
		if ($type == 'category') {
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count-".$a_total);
		} else {
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count-".count($_POST['selectall']).", article_count=article_count-".$a_total);
		}
		statistics_recache();

		$location = getlink('category', $goaction, array('message'=>9));

	} else {
		$location = getlink('category', $goaction, array('message'=>10));
	}
	header("Location: {$location}");
	exit;
}

// 列表
if ($action == 'catelist' || $action == 'taglist') {
	$pagenum = 20;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	if ($action == 'catelist') {
		$type = 'category';
		$name = '分类';
	} else {
		$type = 'tag';
		$name = '标签';
	}
	if ($mid) {
		$do = 'mod';
		$doname = '修改'.$name;
		$meta = $DB->fetch_one_array("SELECT * FROM {$db_prefix}metas WHERE mid='$mid'");
		if (!$meta) {
			$do = 'add';
			$doname = '添加'.$name;
		} else {
			$new_name = $meta['name'];
			$new_url = $meta['slug'];
		}
	} else {
		$do = 'add';
		$doname = '添加'.$name;
	}
	$metadb = array();
	$total = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type='$type'"), 0);
	if ($total) {
		$multipage = multi($total, $pagenum, $page, 'cp.php?job=category&amp;action='.$action);
		$query = $DB->query("SELECT * FROM {$db_prefix}metas WHERE type='$type' ORDER BY displayorder LIMIT $start_limit, $pagenum");
		while($meta = $DB->fetch_array($query)){
			$metadb[$meta['mid']] = $meta;
		}
		unset($meta);
		$DB->free_result($query);
	}
}

if (strstr($action, 'tag')) {
	$cateurl = 'taglist';
} else {
	$cateurl = 'catelist';
}
$catenav = $name.'管理';

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('category');
?>