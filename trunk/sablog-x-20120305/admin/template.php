<?php
// ========================== 文件说明 ==========================//
// 本文件说明：模板管理
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

!$action && $action = 'template';

$stylevarid = (int)$stylevarid;
if ($stylevarid) {
	$stylevar = $DB->fetch_one_array("SELECT * FROM {$db_prefix}stylevars WHERE stylevarid='$stylevarid'");
	if (!$stylevar) {
		redirect('模板变量不存在');
	}
}

if ($message) {
	$messages = array(
		1 => '不允许在后台修改模板',
		2 => '模板无效',
		3 => sprintf('模板已经更新,当前使用 %s 模板', $name),
		4 => sprintf('%s 模板不存在', $name),
		5 => sprintf('%s 修改成功', $name),
		6 => sprintf('%s 不存在', $name),
		7 => '已经把《'.$stylevar['title'].'》设置为禁用状态',
		8 => '已经把《'.$stylevar['title'].'》设置为启用状态',
		9 => '变量名称不合法',
		10 => '变量名已经存在',
		11 => '添加模板变量成功',
		12 => '编辑模板变量成功',
		13 => '请填写变量名',
		14 => '删除模板变量成功',
		15 => '未选择任何项目',
		16 => '更新模板缓存完毕',
	);
}

if (in_array($action, array('savefile','donewtemplate','filelist','newtemplate')) && !$tpledit) {
	$location = getlink('template', 'template', array('message'=>1));
	header("Location: {$location}");
	exit;
}

//读取模板套系(目录)
$template_dir = 'templates/';

$opened = @opendir(SABLOG_ROOT.$template_dir);
$dirdb = array();
while($dir = @readdir($opened)){
	if(($dir != '.') && ($dir != '..') && $dir != 'admin') {
		if (@is_dir($template_dir.$dir)){
			$dirdb[] = $dir;
		}
	}
}
asort($dirdb);
unset($dir);
@closedir($opened);

$path = in_array($path, $dirdb) ? $path : 'default';
if (strstr($file,'.') || strstr($path,'.')) {
	$location = getlink('template', 'filelist', array('message'=>2));
	header("Location: {$location}");
	exit;
}

//模板用途
$desc = array(
	'archives' => '归档列表',
	'article' => '文章列表',
	'comments' => '评论列表',
	'footer' => '页面底部',
	'header' => '页面头部',
	'links' => '友情链接列表',
	'message' => '消息提示',
	'sidecomm' => '侧栏公共模板',
	'search' => '搜索表单',
	'show' => '文章显示',
	'style' => '风格样式',
	'tag' => '标签列表'
);

//设置模板
if($action == 'settemplate') {
	if (file_exists(SABLOG_ROOT.$template_dir.$name) && strpos($name,'..')===false) {
		buildtemplate($name);
		$DB->query("REPLACE INTO {$db_prefix}settings VALUES ('templatename', '".sax_addslashes($name)."')");
		settings_recache();
		$location = getlink('template', 'template', array('message'=>3, 'name'=>$name));
		$options['templatename'] = $name;
	} else {
		$location = getlink('template', 'template', array('message'=>4, 'name'=>$name));
	}
	header("Location: {$location}");
	exit;
}

//保存文件
if($action == 'savefile' && $tpledit){
	$ext = in_array($ext,array('php','css')) ? $ext : 'php';
	$filepath = SABLOG_ROOT.$template_dir.$path.'/'.$file.'.'.$ext;

	if (file_exists($filepath)) {
		$content = sax_stripslashes(trim($_POST['content']));
		writefile($filepath, $content);
		$location = getlink('template', 'filelist', array('message'=>5, 'name'=>$desc[$file]));
	} else {
		$location = getlink('template', 'filelist', array('message'=>6, 'name'=>$desc[$file]));
	}
	header("Location: {$location}");
	exit;
}

//设置状态
if($action == 'visible') {
	if ($stylevar['visible']) {
		$visible = 0;
		$state = '禁用';
		$location = getlink('template', 'stylevar', array('message'=>7, 'stylevarid'=>$stylevarid));
	} else {
		$visible = 1;
		$state = '启用';
		$location = getlink('template', 'stylevar', array('message'=>8, 'stylevarid'=>$stylevarid));
	}
	$DB->unbuffered_query("UPDATE {$db_prefix}stylevars SET visible='$visible' WHERE stylevarid='$stylevarid'");
	stylevars_recache();
	header("Location: {$location}");
	exit;
}

if ($action == 'addstylevar' || $action == 'modstylevar') {
	$new_title = strtolower(sax_addslashes($_POST['new_title']));
	$new_value = sax_addslashes($_POST['new_value']);
	$new_description = char_cv($_POST['new_description']);
	$goaction = str_replace('stylevar', '', $action);
	if($new_title) {
		if(!preg_match("/^[a-z]+[a-z0-9_]*$/i", $new_title)) {
			$location = getlink('template', $goaction, array('message'=>9, 'stylevarid'=>$stylevarid));
		}
		if ($action == 'addstylevar') {
			$query = $DB->query("SELECT COUNT(stylevarid) FROM {$db_prefix}stylevars WHERE title='$new_title'");
		} else {
			$query = $DB->query("SELECT COUNT(stylevarid) FROM {$db_prefix}stylevars WHERE title='$new_title' AND stylevarid!='$stylevarid'");
		}
		if($DB->result($query, 0)) {
			$location = getlink('template', $goaction, array('message'=>10, 'stylevarid'=>$stylevarid));
		} else {
			if ($action == 'addstylevar') {
				$DB->query("INSERT INTO {$db_prefix}stylevars (title, value, description) VALUES ('$new_title', '$new_value', '$new_description')");
				$stylevarid = $DB->insert_id();
				$location = getlink('template', 'mod', array('message'=>11, 'stylevarid'=>$stylevarid));
			} else {
				$DB->query("UPDATE {$db_prefix}stylevars SET title='$new_title', value='$new_value', description='$new_description' WHERE stylevarid='$stylevarid'");
				$location = getlink('template', 'mod', array('message'=>12, 'stylevarid'=>$stylevarid));
			}
			stylevars_recache();
		}
	} else {
		$location = getlink('template', 'mod', array('message'=>13, 'stylevarid'=>$stylevarid));
	}
	header("Location: {$location}");
	exit;
}

if($action == 'delstylevar'){
	$DB->query("DELETE FROM	{$db_prefix}stylevars WHERE stylevarid = '$stylevarid'");
	stylevars_recache();
	$location = getlink('template', 'stylevar', array('message'=>14));
	header("Location: {$location}");
	exit;
}

//批量处理自定义模板变量
if($action == 'domorestylevar'){
	if($ids = implode_ids($_POST['selectall'])) {
		$DB->query("DELETE FROM	{$db_prefix}stylevars WHERE stylevarid IN ($ids)");
		stylevars_recache();
		$location = getlink('template', 'stylevar', array('message'=>14));
	} else {
		$location = getlink('template', 'stylevar', array('message'=>15));
	}
	header("Location: {$location}");
	exit;
}

// 生成模板缓存
if($action == 'buildtemplate') {
	buildtemplate($path);
	$location = getlink('template', 'template', array('message'=>16));
	header("Location: {$location}");
	exit;
}

if ($action == 'add') {
	$subnav = '添加模板变量';
}

if ($action == 'mod') {
	$new_title = $stylevar['title'];
	$new_value = $stylevar['value'];
	$new_description = $stylevar['description'];
	$subnav = '编辑模板变量';
}

//自定义模板变量
if($action == 'stylevar'){
	if($page) {
		$start_limit = ($page - 1) * 30;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	$total = $DB->result($DB->query("SELECT COUNT(stylevarid) FROM {$db_prefix}stylevars"), 0);
	if ($total) {
		$multipage = multi($total, 30, $page, 'cp.php?job=template&amp;action=stylevar');

		$query = $DB->query("SELECT * FROM {$db_prefix}stylevars ORDER BY stylevarid DESC LIMIT $start_limit, 30");

		$stylevardb = array();
		while ($stylevar = $DB->fetch_array($query)) {
			$stylevar['visible_check'] = $stylevar['visible'] ? 'checked' : '';
			$stylevardb[$stylevar['stylevarid']] = $stylevar;
		}
		unset($stylevar);
		$DB->free_result($query);
	}
	$subnav = '模板变量';
}

//选择模板
if($action == 'template') {
	$current_infofile = $options['templatename'].'/info.txt';
	if (file_exists(SABLOG_ROOT.$template_dir.$current_infofile)) {
		$current_template_info = get_template_info($current_infofile);
	} else {
		$current_template_info = '';
	}
	if (!file_exists(SABLOG_ROOT.$template_dir.$options['templatename'].'/screenshot.png')) {
		$current_template_info['screenshot'] = $template_dir.'no.png';
	} else {
		$current_template_info['screenshot'] = $template_dir.$options['templatename'].'/screenshot.png';
	}

	$dir1 = opendir(SABLOG_ROOT.$template_dir);
	$available_template_db = array();
	while($file1 = readdir($dir1)){
		if ($file1 != '' && $file1 != '.' && $file1 != '..' && $file1 != 'admin' && $file1 != $options['templatename']){
			if (is_dir($template_dir.'/'.$file1)){
				$dir2 = opendir($template_dir.'/'.$file1);
				while($file2 = readdir($dir2)){
					if (is_file(SABLOG_ROOT.$template_dir.'/'.$file1.'/'.$file2) && $file2 == 'info.txt'){
						$available_template_db[] = get_template_info($file1.'/'.$file2);
					}
				}
				closedir($dir2);
			}
		}
	}
	closedir($dir1);
	unset($file1);
	$subnav = '选择模板';
}

//模板套系中的文件列表
if($action == 'filelist') {
	require_once(SABLOG_ROOT.'include/func/attachment.func.php');
	$dir = $template_dir.$path;
	$fp = opendir($dir);
	$i = 0;
	$filedb = array();
	while ($fileinfo = readdir($fp)) {
		if ($fileinfo != '.' && $fileinfo != '..' && $fileinfo != 'index.php' && is_file(SABLOG_ROOT.$dir.'/'.$fileinfo)) {
			$extension = getextension($fileinfo);
			if ($extension == 'php' || $extension == 'css') {
				$i++;
				$filedb[$i]['filename'] = str_replace(array('.php','.css'), '', $fileinfo);
				$filedb[$i]['filedesc'] = $desc[$filedb[$i]['filename']] ? $desc[$filedb[$i]['filename']] : $filedb[$i]['filename'];
				$filedb[$i]['extension'] = $extension;
			}
		}
	}
	closedir($fp);
	asort($filedb);
	unset($fileinfo);

	!$file && $file = $filedb[$i]['filename'];
	!$ext && $ext = $filedb[$i]['extension'];
	$ext = in_array($ext,array('php','css')) ? $ext : 'php';
	$filepath = SABLOG_ROOT.$dir.'/'.$file.'.'.$ext;
	if (file_exists($filepath)) {
		$writeable = false;
		if(is_writeable($filepath)) {
			$writeable = true;
		}
		$contents = htmlspecialchars(loadfile($filepath));
	}
	$subnav = '编辑模板 - '.$path;
}

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('template');
?>