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

// 加载公用函数
require_once('include/common.inc.php');

// 加载后台常用函数
require_once(SABLOG_ROOT.'include/func/admin.func.php');

// 检查安装文件是否存在
if (file_exists('install')) {
	exit('Installation directory: install/ is still on your server. Please DELETE it or RENAME it now.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['formhash'] != $formhash)){
	//loginpage();
}

// 登陆验证
if($_POST['action'] == 'dologin') {
	/*
	if ($options['seccode']) {
		$clientcode = $_POST['clientcode'];
		include_once(SABLOG_ROOT.'include/class/seccode.class.php');
		$code = new seccode();
		$code->seccodeconvert($_SESSION['seccode']);
		if (!$clientcode || strtolower($clientcode) != strtolower($_SESSION['seccode'])) {
			$_SESSION['seccode'] = random(6, 1);
			redirect('验证码错误,请返回重新输入.', $referer);
		}
	}
	*/

	$username = char_cv(trim($_POST['username']));
	if (isemail($username)) {
		$account_field = 'email';
	} else {
		$account_field = 'username';
	}
	$password = md5($_POST['password']);
	$userinfo = $DB->fetch_one_array("SELECT userid,password,logincount FROM {$db_prefix}users WHERE $account_field='$username' LIMIT 1");

	if ($userinfo['userid'] && $userinfo['password'] == $password) {
		//更新登陆次数、登陆时间和登陆IP
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET logincount=logincount+1, logintime='$timestamp', loginip='$onlineip' WHERE userid='$userinfo[userid]'");
		$logincount = $userinfo['logincount']+1;
		$sax_uid = $userinfo['userid'];
		//保存COOKIE
		scookie('sax_auth', authcode("$sax_uid\t$password\t$logincount"), $login_life);
		//更新数据库中的登陆会话
		updatesession();
		loginresult($username, 'Succeed');
		@header('Location: '.$options['url'].'cp.php');
		exit;
	} else {
		loginresult($username, 'Failed');
		dcookies();
		redirect('登陆失败', $options['url'].'cp.php?action=login');
	}
}

//注销
if ($_GET['action'] == 'logout') {
	dcookies();
	$sax_group = 4;
	$sax_uid = 0;
	$sax_user = $sax_pw = '';
	redirect('注销成功', $options['url']);
}

//注册表单
if ($_GET['action'] == 'register') {
	if ($options['closereg']) {
		redirect('禁止注册新用户', $referer);
	} else {
		if ($sax_uid && $sax_pw && $sax_group) {
			redirect('您已经处于登陆状态', $referer);
		}
		include template('register');
		PageEnd();
	}
}

if ($_POST['action'] == 'doregister') {
	if($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || $GLOBALS['formhash'] != formhash() || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
		redirect('您的请求来路不正确,无法提交.');
	} else {
		if($options['seccode']) {
			$clientcode = $_POST['clientcode'];
			include_once(SABLOG_ROOT.'include/class/seccode.class.php');
			$code = new seccode();
			$code->seccodeconvert($_SESSION['seccode']);
			if (!$clientcode || strtolower($clientcode) != strtolower($_SESSION['seccode'])) {
				$_SESSION['seccode'] = random(6, 1);
				redirect('验证码错误,请返回重新输入.', $referer);
			}
		}
	}

	//取值
	$username		= trim($_POST['username']);
	$password		= $_POST['password'];
	$comfirpassword = $_POST['comfirpassword'];
	$email			= trim($_POST['email']);
	$url			= trim($_POST['url']);
	$referer		= trim($_POST['referer']);

	//检测网址
	if (!isurl($url)) {
		redirect('网站URL错误');
	}

	//检测用户名
	if(!$username || getstrlen($username) > 20) {
		redirect('用户名为空或者超过20字节.', $reg_url);
	}

	if ($options['censoruser']) {
		$options['censoruser'] = str_replace('，', ',', $options['censoruser']);
		$banname = explode(',',$options['censoruser']);
		foreach($banname as $value){
			if (strpos($username,$value) !== false){
				redirect('此用户名包含不可接受字符或被管理员屏蔽,请选择其它用户名.', $reg_url);
			}
		}
	}

	$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
	foreach($name_key as $value){
		if (strpos($username,$value) !== false){
			redirect('此用户名包含不可接受字符或被管理员屏蔽,请选择其它用户名.', $reg_url);
		}
	}

	//检测密码
	if (!$password || getstrlen($password) < 8) {
		redirect('密码不能为空并且密码长度不能小于8位.',$reg_url);
	}
	if ($password != $comfirpassword) {
		redirect('请确认输入的密码一致.', $reg_url);
	}
	if (strpos($password,"\n") !== false || strpos($password,"\r") !== false || strpos($password,"\t") !== false) {
		redirect('密码包含不可接受字符.', $reg_url);
	}

	$username = char_cv($username);
	$r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE username='$username' LIMIT 1");
	if($r['userid']) {
		redirect('该用户名已被注册.');
	}
	$email = char_cv($email);
	$r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE email='$email' LIMIT 1");
	if($r['userid']) {
		redirect('该E-mail已被注册.');
	}

	$password = md5($password);

	$DB->query("INSERT INTO {$db_prefix}users (username, password, logincount, loginip, logintime, email, url, regdateline, regip, groupid, lastip, lastvisit, lastactivity) VALUES ('$username', '$password', '1', '$onlineip', '$timestamp', '$email', '$url', '$timestamp', '$onlineip', '3', '$onlineip', '$timestamp', '$timestamp')");
	$sax_uid = $DB->insert_id();

	//保存COOKIE
	scookie('sax_auth', authcode("$sax_uid\t$password\t1"), $login_life);
	//更新数据库中的登陆会话
	updatesession();

	redirect('注册成功.', $options['url']);

}

//登陆状态检测
if (!$sax_uid || !$sax_pw || !$sax_logincount) {
	loginpage();
} else {
	$r = $DB->fetch_one_array("SELECT userid, password, logincount FROM {$db_prefix}users WHERE userid='$sax_uid'");
	if(!$r) {
		loginpage();
	}
	if($sax_pw != $r['password']) {
		loginpage();
	}
	if($sax_logincount != $r['logincount']) {
		loginpage();
	}
}

$job = sax_addslashes($_GET['job'] ? $_GET['job'] : $_POST['job']);

// 记录管理的一切操作
getlog();

if ($sax_group == 1) {
	$adminitem = array(
		'main' => array(
			'name'=>'首页',
			'start'=>1,
		),
		'article' => array(
			'name'=>'文章',
			'submenu' => array(
				array('name'=>'文章管理', 'action'=>'list', 'default'=>1),
				array('name'=>'添加文章', 'action'=>'add'),
			),
		),
		'comment' => array(
			'name'=>'评论',
			'submenu' => array(
				array('name'=>'评论管理', 'action'=>'list', 'default'=>1),
			),
		),
		'attachment' => array(
			'name'=>'附件',
			'submenu' => array(
				array('name'=>'附件管理', 'action'=>'list', 'default'=>1),
				array('name'=>'附件修复', 'action'=>'repair'),
				array('name'=>'附件清理', 'action'=>'clear'),
				array('name'=>'附件统计', 'action'=>'stats'),
			),
		),
		'category' => array(
			'name'=>'分类',
			'submenu' => array(
				array('name'=>'分类管理', 'action'=>'catelist', 'default'=>1),
				array('name'=>'标签管理', 'action'=>'taglist'),
			),
		),
		'user' => array(
			'name'=>'用户',
			'submenu' => array(
				array('name'=>'用户管理', 'action'=>'list', 'default'=>1),
				array('name'=>'添加用户', 'action'=>'add'),
			),
		),
		'link' => array(
			'name'=>'链接',
			'submenu' => array(
				array('name'=>'链接管理', 'action'=>'list', 'default'=>1),
				array('name'=>'添加连接', 'action'=>'add'),
			),
		),
		'template' => array(
			'name'=>'模板',
			'submenu' => array(
				array('name'=>'模板设置', 'action'=>'template', 'default'=>1),
				array('name'=>'模板变量', 'action'=>'stylevar'),
				array('name'=>'添加模板变量', 'action'=>'add'),
			),
		),
		'tools' => array(
			'name'=>'维护',
			'submenu' => array(
				array('name'=>'数据库信息', 'action'=>'mysqlinfo', 'default'=>1),
				array('name'=>'备份数据库', 'action'=>'backup'),
				array('name'=>'数据库维护', 'action'=>'tools'),
				array('name'=>'数据文件管理', 'action'=>'filelist'),
				array('name'=>'导入RSS数据', 'action'=>'rssimport'),
				array('name'=>'缓存管理', 'action'=>'cache'),
				array('name'=>'重建数据', 'action'=>'rebuild'),
				array('name'=>'后台操作记录', 'action'=>'adminlog'),
				array('name'=>'登陆记录', 'action'=>'loginlog'),
				array('name'=>'数据库出错记录', 'action'=>'dberrorlog'),
			),
		),

		'configurate' => array(
			'name'=>'设置',
			'end'=>1,
			'submenu' => array(
				array('name'=>'全部', 'action'=>'all'),
				array('name'=>'基本设置', 'action'=>'basic', 'default'=>1),
				array('name'=>'显示设置', 'action'=>'display'),
				array('name'=>'评论设置', 'action'=>'comment'),
				array('name'=>'附件设置', 'action'=>'attach'),
				array('name'=>'时间设置', 'action'=>'dateline'),
				array('name'=>'SEO设置', 'action'=>'seo'),
				array('name'=>'WAP设置', 'action'=>'wap'),
				array('name'=>'安全设置', 'action'=>'ban'),
				array('name'=>'RSS设置', 'action'=>'rss'),
				array('name'=>'伪静态设置', 'action'=>'permalink'),
			),
		),
	);
} elseif ($sax_group == 2) {
	$adminitem = array(
		'main' => array(
			'name'=>'首页',
			'start'=>1,
		),
		'article' => array(
			'name'=>'文章',
			'submenu' => array(
				array('name'=>'文章管理', 'action'=>'list', 'default'=>1),
				array('name'=>'添加文章', 'action'=>'add'),
			),
		),
		'user' => array(
			'name'=>'资料',
			'end'=>1,
		),
	);
	!$job && $job = 'article';
	if ($job == 'user') {
		$action = in_array($action, array('profile','modprofile')) ? $action : 'profile';
	}
	// 撰写组菜单
} else {
	$adminitem = array();
	$job = 'user';
	$action = in_array($action, array('profile','modprofile')) ? $action : 'profile';
	// 注册组菜单
}

$groupdb = array(
	1 => '管理者',
	2 => '撰写者',
	3 => '普通用户',
	4 => '游客',
);

if (!$job) {
	$job = 'main';
} else {
	if (getstrlen($job) > 20) {
		$job = 'main';
	}
	$job = str_replace(array('.','/','\\',"'",':','%'),'',$job);
	$job = basename($job);
	$job = in_array($job, array('main','misc','article','comment','attachment','category','user','link','template','tools','configurate','upload')) ? $job : 'main';
}

$articleid = intval($_POST['articleid'] ? $_POST['articleid'] : $_GET['articleid']);

$subnav = '';

if (file_exists(SABLOG_ROOT.'admin/'.$job.'.php')) {
	include(SABLOG_ROOT.'admin/'.$job.'.php');
} else {
	include(SABLOG_ROOT.'admin/main.php');
}

cpfooter();
?>