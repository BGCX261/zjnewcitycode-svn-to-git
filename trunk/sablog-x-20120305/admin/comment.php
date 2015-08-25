<?php
// ========================== 文件说明 ==========================//
// 本文件说明：评论管理
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

!$action && $action = 'list';

$commentid = (int)$commentid;
$doit = in_array($doit,array('hidden','display','del')) ? $doit : '';
$location = '';

if ($message) {
	$messages = array(
		1 => '记录不存在',
		2 => '删除成功',
		3 => '用户名为空或用户名太长',
		4 => '此用户名包含不可接受字符或被管理员屏蔽',
		5 => 'E-mail地址错误',
		6 => '网站URL错误',
		7 => '修改评论成功',
		8 => '选定项目已显示',
		9 => '选定项目已隐藏',
		10 => '选定项目已删除',
		11 => '没有选择任何项目'
	);
}

//设置状态
if($action == 'visible') {
	$comment = $DB->fetch_one_array("SELECT visible,articleid FROM {$db_prefix}comments WHERE commentid='$commentid'");
	if (!$comment) {
		$location = getlink('comment', 'list', array('message'=>1));
	} else {
		if ($comment['visible']) {
			$visible = '0';
			$query = '-';
			$location = getlink('comment', 'list', array('message'=>9));
		} else {
			$visible = '1';
			$query = '+';
			$location = getlink('comment', 'list', array('message'=>8));
		}
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments".$query."1 WHERE articleid='".$comment['articleid']."'");
		$DB->unbuffered_query("UPDATE {$db_prefix}comments SET visible='$visible' WHERE commentid='$commentid'");
		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count".$query."1");
		newcomments_recache();
		statistics_recache();
	}

	header("Location: {$location}");
	exit;
}

//删除单条评论
if($action == 'del') {
	$comment = $DB->fetch_one_array("SELECT visible,articleid FROM {$db_prefix}comments WHERE commentid='$commentid'");
	if (!$comment) {
		$location = getlink('comment', 'list', array('message'=>1));
	} else {
		if ($comment['visible']) {
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments-1 WHERE articleid='".$comment['articleid']."'");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count-1");
		}
		$DB->unbuffered_query("DELETE FROM {$db_prefix}comments WHERE commentid='$commentid'");
		newcomments_recache();
		statistics_recache();
		$location = getlink('comment', 'list', array('message'=>2));
	}
	header("Location: {$location}");
	exit;
}

// 修改评论
if($action == 'domod') {
	$author = trim($_POST['author']);
	$url = trim($_POST['url']);
	$email = trim($_POST['email']);
	if(!$author || getstrlen($author) > 30) {
		$location = getlink('comment', 'mod', array('message'=>3, 'commentid'=>$commentid));
	}
	$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
	foreach($name_key as $value){
		if (strpos($author,$value) !== false){
			$location = getlink('comment', 'mod', array('message'=>4, 'commentid'=>$commentid));
			break;
		}
	}
	$author = char_cv($author);
	
	if (!isemail($email)) {
		$location = getlink('comment', 'mod', array('message'=>5, 'commentid'=>$commentid));
	}

	if ($url) {
		if (!preg_match("#^(http|news|https|ftp|ed2k|rtsp|mms)://#", $url)) {
			$location = getlink('comment', 'mod', array('message'=>6, 'commentid'=>$commentid));
		}
		$key = array("\\",' ',"'",'"','*',',','<','>',"\r","\t","\n",'(',')','+',';');
		foreach($key as $value){
			if (strpos($url,$value) !== false){
				$location = getlink('comment', 'mod', array('message'=>6, 'commentid'=>$commentid));
				break;
			}
		}
		$url = char_cv($url);
	}
	if (!$location) {
		$DB->unbuffered_query("UPDATE {$db_prefix}comments SET author='$author', email='$email', url='$url', content='".sax_addslashes($_POST['content'])."' WHERE commentid='$commentid'");
		newcomments_recache();
		$location = getlink('comment', 'list', array('message'=>7));
	}
	header("Location: {$location}");
	exit;
}

//批量处理评论状态
if($action == 'domorelist') {
	$message = '';
	if ($doit == 'display') {
		$visible = '1';
		$location = getlink('comment', 'list', array('message'=>8));
		$del = false;
	} elseif ($doit == 'hidden') {
		$visible = '0';
		$location = getlink('comment', 'list', array('message'=>9));
		$del = false;
	} else {
		$location = getlink('comment', 'list', array('message'=>10));
		$del = true;
	}
	if ($doit && $cids = implode_ids($_POST['selectall'])) {
		if ($del) {
			$DB->unbuffered_query("DELETE FROM {$db_prefix}comments WHERE commentid IN ($cids)");
		} else {
			$DB->unbuffered_query("UPDATE {$db_prefix}comments SET visible='$visible' WHERE commentid IN ($cids)");
		}
		$comment_count = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.visible='1' AND c.visible='1'"), 0);

		$DB->query("UPDATE {$db_prefix}statistics SET comment_count='$comment_count'");
		$query = $DB->query("SELECT articleid FROM {$db_prefix}articles");
		while ($article = $DB->fetch_array($query)) {
			// 更新所有文章的评论数
			$total = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE articleid='".$article['articleid']."' AND visible='1'"), 0);
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments='$total' WHERE articleid='".$article['articleid']."'");
		}
		newcomments_recache();
		statistics_recache();
	} else {
		$location = getlink('comment', 'list', array('message'=>11));
	}
	header("Location: {$location}");
	exit;
}

if ($action == 'mod') {
	$comment = $DB->fetch_one_array("SELECT c.articleid,c.commentid,c.author,c.url,c.email,c.dateline,c.content, a.title FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE commentid='$commentid'");
	if (!$comment) {
		$message = '评论不存在';
		$action = 'list';
	} else {
		$comment['content'] = htmlspecialchars($comment['content']);
	}
	$subnav = '修改评论';
}//end mod

if ($action == 'list') {
	$sql_query = ' WHERE 1=1 ';
	$pagelink = '';
	$view = in_array($view, array('display','hidden')) ? $view : '';
	$type = in_array($type, array('comment','trackback')) ? $type : '';
	
	$alltotal		= $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments"), 0);
	$displaytotal	= $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE visible='1'"), 0);
	$hiddentotal	= $alltotal - $displaytotal;
	
	$ttotal	= $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE type='trackback'"), 0);
	$ctotal	= $alltotal - $ttotal;

	if ($view == 'display') {
		$sql_query .= " AND c.visible='1'";
		$pagelink  .= '&amp;view=display';
		$subnav     = '显示的评论';
	} elseif ($view == 'hidden') {
		$sql_query .= " AND c.visible='0'";
		$pagelink  .= '&amp;view=hidden';
		$subnav     = '隐藏的评论';
	}
	
	if ($type == 'comment') {
		$sql_query .= " AND c.type='comment'";
		$pagelink  .= '&amp;type=comment';
		$subnav     = '评论';
	} elseif ($type == 'trackback') {
		$sql_query .= " AND c.type='trackback'";
		$pagelink  .= '&amp;type=trackback';
		$subnav     = '引用';
	}

	if ($articleid) {
		$article = $DB->fetch_one_array("SELECT title FROM {$db_prefix}articles WHERE articleid='$articleid'");
		$sql_query .= " AND c.articleid='$articleid'";
		$pagelink  .= '&amp;articleid='.$articleid;
		$subnav     = '《'.$article['title'].'》的评论';
	}
	$ip = char_cv($ip);
	if ($ip) {
		$frontlen = strrpos($ip, '.');
		$ipc = substr($ip, 0, $frontlen);
		$sql_query .= " AND (c.ipaddress LIKE '%".$ipc."%')";
		$pagelink  .= '&amp;ip='.$ip;
		$subnav     = '与 '.$ip.' 同一C段提交的评论';
	}
	$pagenum = 15;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	$total     = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments c $sql_query"), 0);

	if ($total) {
		$multipage = multi($total, $pagenum, $page, 'cp.php?job=comment&amp;action=list'.$pagelink);
		$query = $DB->query("SELECT c.*,a.title
			FROM {$db_prefix}comments c
			LEFT JOIN {$db_prefix}articles a ON a.articleid = c.articleid
			$sql_query
			ORDER BY c.commentid DESC LIMIT $start_limit, $pagenum");

		$commentdb = array();
		while ($comment = $DB->fetch_array($query)) {
			$comment['content'] = preg_replace("/\[quote=(.*?)\]\s*(.+?)\s*\[\/quote\]/is", "", $comment['content']);
			if (empty($comment['content'])) {
				$comment['content'] = '......';
			}
			$comment['content'] = str_replace(array("\r\n\r\n","\n\n","\r\r"),array("\r\n","\n","\r"),$comment['content']);
			$comment['avatardb'] = get_avatar($comment['email'], 32);
			$comment['dateline'] = sadate('Y-m-d H:i',$comment['dateline'],1);
			$comment['content'] = html_clean($comment['content']);
			$commentdb[$comment['commentid']] = $comment;
		}
		unset($comment);
		$DB->free_result($query);
	}
}//end list

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('comment');
?>