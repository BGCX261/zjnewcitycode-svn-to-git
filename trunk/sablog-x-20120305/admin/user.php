<?php
// ========================== 文件说明 ==========================//
// 本文件说明：用户管理
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

permission(array(1,2,3));

if ($sax_group == 1) {
	!$action && $action = 'list';
} else {
	!$action && $action = 'profile';
}

$userid = (int)$userid;
$groupid = (int)$groupid;
$location = '';

if ($message) {
	$messages = array(
		1 => '登陆名不能为空并且不能超过20个字符',
		2 => '用户名包含不可接受字符',
		3 => 'E-mail地址错误',
		4 => '网站URL错误',
		5 => '密码不能为空并且密码长度不能小于8位',
		6 => '请确认输入的密码一致',
		7 => '密码包含不可接受字符',
		8 => '该用户名已被注册',
		9 => '该E-mail已被注册',
		10 => '添加新用户成功',
		11 => '新密码长度不能小于8位',
		12 => '资料已修改成功',
		13 => '删除用户成功',
		14 => '未选择任何用户',
		15 => '出错,请尝试重新登陆再进行此操作',
		16 => '旧密码错误'
	);
}

//权限检查
if (!in_array($action, array('profile','modprofile'))) {
	permission(1);
}

//添加用户
if($_POST['action'] == 'adduser') {
	$username       = trim($_POST['username']);
	$newpassword    = trim($_POST['newpassword']);
	$comfirpassword = trim($_POST['comfirpassword']);
	$email          = trim($_POST['email']);
	$url            = trim($_POST['url']);

	if (!$username || strlen($username) > 20) {
		$location = getlink('user', 'add', array('message'=>1));
    }
	$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
	foreach($name_key as $value){
		if (strpos($username,$value) !== false){
			$location = getlink('user', 'add', array('message'=>2));
			break;
		}
	}
	if (!isemail($email)) {
		$location = getlink('user', 'add', array('message'=>3));
	}
	if (!isurl($url)) {
		$location = getlink('user', 'add', array('message'=>4));
	}
    if ($newpassword == '' || strlen($newpassword) < 8) {
		$location = getlink('user', 'add', array('message'=>5));
	}
    if ($newpassword != $comfirpassword) {
		$location = getlink('user', 'add', array('message'=>6));
    }
	if (strpos($newpassword,"\n") !== false || strpos($newpassword,"\r") !== false || strpos($newpassword,"\t") !== false) {
		$location = getlink('user', 'add', array('message'=>7));
	}
	$url = char_cv($url);
	$email = char_cv($email);
	$username = char_cv($username);
	$newpassword = md5($newpassword);

    $r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE username='$username' LIMIT 1");
    if($r['userid']) {
		$location = getlink('user', 'add', array('message'=>8));
    }
    $r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE email='$email' LIMIT 1");
    if($r['userid']) {
		$location = getlink('user', 'add', array('message'=>9));
    }

	if (!$location) {
		$DB->query("INSERT INTO {$db_prefix}users (username, password, email, url, regdateline, regip, groupid) VALUES ('$username', '$newpassword', '$email', '$url', '$timestamp', '$onlineip', '$groupid')");
		$location = getlink('user', 'list', array('message'=>10));
	}
	header("Location: {$location}");
	exit;
}

//修改用户
if($_POST['action'] == 'moduser') {
	$username       = trim($_POST['username']);
	$newpassword    = trim($_POST['newpassword']);
	$comfirpassword = trim($_POST['comfirpassword']);
	$email          = trim($_POST['email']);
	$url            = trim($_POST['url']);

	if (!$username || getstrlen($username) > 20) {
		$location = getlink('user', 'mod', array('message'=>1, 'userid'=>$userid));
    }
	if (!isemail($email)) {
		$location = getlink('user', 'mod', array('message'=>3, 'userid'=>$userid));
	}
	if (!isurl($url)) {
		$location = getlink('user', 'mod', array('message'=>4, 'userid'=>$userid));
	}
	$password_sql = '';
	if ($newpassword) {
		if(getstrlen($newpassword) < 8) {
			$location = getlink('user', 'mod', array('message'=>11, 'userid'=>$userid));
		}
		if ($newpassword != $comfirpassword) {
			$location = getlink('user', 'mod', array('message'=>4, 'userid'=>$userid));
		}
		if (strpos($newpassword,"\n") !== false || strpos($newpassword,"\r") !== false || strpos($newpassword,"\t") !== false) {
			$location = getlink('user', 'mod', array('message'=>7, 'userid'=>$userid));
		}
		$password_sql = ", password='".md5($newpassword)."'";
	}
	$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
	foreach($name_key as $value){
		if (strpos($username,$value) !== false){
			$location = getlink('user', 'mod', array('message'=>2, 'userid'=>$userid));
			break;
		}
	}
	$url = char_cv($url);
	$username = char_cv($username);
    $r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE username='$username' AND userid!='$userid' LIMIT 1");
    if($r) {
		$location = getlink('user', 'mod', array('message'=>8, 'userid'=>$userid));
    }

	if (!$location) {
		$usernamesql = $username ? "username='$username'," : '';
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET $usernamesql url='$url', email='$email', groupid='$groupid' $password_sql WHERE userid='$userid'");
		$location = getlink('user', 'mod', array('message'=>12, 'userid'=>$userid));
	}
	header("Location: {$location}");
	exit;
}

//修改个人资料
if ($_POST['action'] == 'modprofile') {
	$username        = trim($_POST['username']);
	$old_password    = md5($_POST['old_password']);
	$newpassword        = $_POST['newpassword'];
	$comfirpassword = $_POST['comfirpassword'];
	$email             = trim($_POST['email']);
	$url             = trim($_POST['url']);
	$referer         = trim($_POST['referer']);

	if (!isemail($email)) {
		$location = getlink('user', 'profile', array('message'=>3, 'userid'=>$sax_uid));
	}
	if (!isurl($url)) {
		$location = getlink('user', 'profile', array('message'=>4, 'userid'=>$sax_uid));
	}
	//修改资料
	$password_sql = '';
	if ($newpassword) {
		$user = $DB->fetch_one_array("SELECT password FROM {$db_prefix}users WHERE userid='$sax_uid'");
		if (!$user) {
			$location = getlink('user', 'profile', array('message'=>15, 'userid'=>$sax_uid));
		}
		if ($old_password != $user['password']) {
			$location = getlink('user', 'profile', array('message'=>16, 'userid'=>$sax_uid));
		}
		if(getstrlen($newpassword) < 8) {
			$location = getlink('user', 'profile', array('message'=>11, 'userid'=>$sax_uid));
		}
		if ($newpassword != $comfirpassword) {
			$location = getlink('user', 'profile', array('message'=>6, 'userid'=>$sax_uid));
		}
		if (strpos($newpassword,"\n") !== false || strpos($newpassword,"\r") !== false || strpos($newpassword,"\t") !== false) {
			$location = getlink('user', 'profile', array('message'=>7, 'userid'=>$sax_uid));
		}
		$password_sql = ", password='".md5($newpassword)."'";
	}
	
	if (!$location) {
		$email = char_cv($email);
		$url = char_cv($url);
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET url='$url', email='$email' $password_sql WHERE userid='$sax_uid'");
		if ($newpassword) {
			dcookies();
			redirect('资料已修改成功,您修改了密码,需要重新登陆.', $options['url'].'cp.php?action=login');
		} else {
			$location = getlink('user', 'profile', array('message'=>12, 'userid'=>$sax_uid));
		}
	}
	header("Location: {$location}");
	exit;
}

//删除用户
if($_POST['action'] == 'delusers') {
	if ($uids = implode_ids($_POST['selectall'])) {

		$aids = $comma = '';
		$comment_count = $a_total = $a_stick = 0;
		// 删除该用户发表的文章以及相关数据
		require_once(SABLOG_ROOT.'include/func/attachment.func.php');
		$query = $DB->query("SELECT articleid,visible,uid,comments,stick FROM {$db_prefix}articles WHERE uid IN ($uids)");
		while ($article = $DB->fetch_array($query)) {
			$aids .= $comma.$article['articleid'];
			$comma = ',';
			if ($article['visible']) {
				$a_total++;
				$mids = get_mids($article['articleid']);
				$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count-1 WHERE mid IN ($mids)");
				$comment_count = $comment_count + $article['comments'];
			}
			if ($article['stick']) {
				$a_stick = 1;
			}
		}//end while

		if ($aids) {
			// 删除该用户的文章中的附件
			$query  = $DB->query("SELECT attachmentid,filepath,thumb_filepath FROM {$db_prefix}attachments WHERE articleid IN ($aids)");
			$nokeep = array();
			while($attach = $DB->fetch_array($query)) {
				$nokeep[$attach['attachmentid']] = $attach;
			}
			removeattachment($nokeep);

			$DB->unbuffered_query("DELETE FROM {$db_prefix}comments WHERE articleid IN ($aids)");
			$DB->unbuffered_query("DELETE FROM {$db_prefix}articles WHERE uid IN ($uids)");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count-".$a_total.", comment_count=comment_count-$comment_count");

			archives_recache();
			categories_recache();
			statistics_recache();
			if ($a_stick) {
				stick_recache();
			}
		}

		// 删除用户
		$DB->unbuffered_query("DELETE FROM {$db_prefix}users WHERE userid IN ($uids)");

		$location = getlink('user', 'list', array('message'=>13, 'userid'=>$sax_uid));
	} else {
		$location = getlink('user', 'list', array('message'=>14, 'userid'=>$sax_uid));
	}
	header("Location: {$location}");
	exit;
}

if (in_array($action, array('add', 'mod', 'profile'))) {
	$info = array();
	if ($action == 'add') {
		$subnav = '添加用户';
		$do = 'adduser';
		if ($groupid) {
			$groupselect[$groupid] = 'selected';
		} else {
			$groupselect[3] = 'selected';
		}
	} else {
		$subnav = '修改资料';
		if ($action == 'profile') {
			$do = 'modprofile';
			$info = $DB->fetch_one_array("SELECT * FROM {$db_prefix}users WHERE userid='$sax_uid'");
		} else {
			$do = 'moduser';
			$info = $DB->fetch_one_array("SELECT * FROM {$db_prefix}users WHERE userid='$userid'");
			$groupselect[$info['groupid']] = 'selected';
		}
		$username = $info['username'];
		$email = $info['email'];
		$url = $info['url'];

		$info['regdateline'] = sadate('Y-m-d H:i', $info['regdateline'],1);
		$info['lastpost'] = $info['lastpost'] ? sadate('Y-m-d H:i', $info['lastpost'],1) : '从未';
		$info['lastvisit'] = $info['lastvisit'] ? sadate('Y-m-d H:i', $info['lastvisit'],1) : '从未';
		$info['lastactivity'] = $info['lastactivity'] ? sadate('Y-m-d H:i', $info['lastactivity'],1) : '从未';
	}
} //end mod

if($action == 'list') {
	$pagenum = 20;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	$sqladd = ' WHERE 1 ';
	$pagelink = '';

	//察看用户组
	if ($groupid && in_array($groupid, array(1,2,3))) {
		$sqladd .= " AND groupid='$groupid'";
		$pagelink .= '&groupid='.$groupid;
		$subnav = $groupdb[$groupid];
	}

	//搜索用户
	$srhname = char_cv($srhname);
	if ($srhname) {
		$sqladd .= " AND (BINARY username LIKE '%".str_replace('_', '\_', $srhname)."%' OR username='$srhname')";
		$pagelink .= '&srhname='.$srhname;
	}

	$usertotal		= $DB->result($DB->query("SELECT COUNT(userid) FROM {$db_prefix}users"), 0);
	$admintotal		= $DB->result($DB->query("SELECT COUNT(userid) FROM {$db_prefix}users WHERE groupid='1'"), 0);
	$editortotal	= $DB->result($DB->query("SELECT COUNT(userid) FROM {$db_prefix}users WHERE groupid='2'"), 0);
	$publictotal	= $usertotal - $admintotal - $editortotal;

	$total     = $DB->result($DB->query("SELECT COUNT(userid) FROM {$db_prefix}users ".$sqladd), 0);
	$multipage = multi($total, $pagenum, $page, 'cp.php?job=user&amp;action=list'.$pagelink);
	$query = $DB->query("SELECT userid,email,username,url,regdateline,groupid,lastvisit FROM {$db_prefix}users $sqladd ORDER BY userid DESC LIMIT $start_limit, $pagenum");
	$userdb = array();
	while ($user = $DB->fetch_array($query)) {
		$user['regdateline']	= sadate('Y-m-d H:i',$user['regdateline'],1);
		$user['lastvisit']		= $user['lastvisit'] ? sadate('Y-m-d H:i',$user['lastvisit'],1) : '从未';
		$user['group']			= $groupdb[$user['groupid']];
		$user['disabled']		= ($user['groupid'] == 1 || $user['userid'] == 1) ? 'disabled' : '';
		$userdb[$user['userid']] = $user;
	}
	unset($user);
	$DB->free_result($query);
} //end list

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('user');
?>