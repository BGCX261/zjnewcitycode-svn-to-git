<?php
// ========================== 文件说明 ==========================//
// 本文件说明：后台公共函数
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

// 控制面板各页面页眉
function cpheader($suvtitle = '') {
	global $title,$options,$adminitem,$job,$action,$SABLOG_VERSION,$SABLOG_RELEASE,$navlink_L,$sax_uid,$sax_user,$sax_group,$messages,$message;
	$title = isset($adminitem) ? $adminitem[$job]['name'] : '登陆';
	include template('header');
}

// 后台登陆入口页面
function loginpage(){
	global $sax_uid, $sax_user, $sax_group, $options, $referer, $formhash;
	include template('login');
	PageEnd();
}

// 检查权限

function permission($groupid) {
	global $sax_group;
	if (is_array($groupid)) {
		if (!in_array($sax_group, $groupid)) {
			redirect('你没有此功能的管理权限','cp.php');
		}
	} else {
		if ($sax_group != intval($groupid)) {
			redirect('你没有此功能的管理权限','cp.php');
		}
	}
}

function getlink($job, $action='', $arr=array()) {
	$tmp = '';
	if ($arr) {
		foreach ($arr as $name => $val) {
			$tmp .= '&'.$name.'='.$val;
		}
	}
	$link = 'cp.php?job='.$job.($action ? '&action='.$action : '').$tmp;
	return $link;
}

// 操作提示页面
function redirect($msg, $url = 'javascript:history.go(-1);', $min='2') {
	global $options;
	include template('redirect');
	PageEnd();
}

// 当页消息提示
function page_message($arr = array()) {
	if ($arr) {
		foreach ($arr as $name => $val) {
			global $$name;
			$$name = $val;
		}
	}
}

// 控制面板各页面页脚
function cpfooter() {
	global $options,$adminitem,$action,$starttime,$DB,$SABLOG_VERSION,$SABLOG_RELEASE;
	$mtime		= explode(' ', microtime());
	$totaltime	= number_format(($mtime[1] + $mtime[0] - $starttime), 6);
	$gzip		= $options['gzipcompress'] ? 'enabled' : 'disabled';
	$debuginfo	= 'Processed in '.$totaltime.' second(s), '.$DB->querycount.' queries, Gzip '.$gzip;
	include template('footer');
	PageEnd();
}

// 返回GD函数版本号
function gd_version() {
	if (function_exists('gd_info')) {
		$GDArray = gd_info();
		$gd_version_number = $GDArray['GD Version'] ? $GDArray['GD Version'] : 0;
		unset($GDArray);
	} else {
		$gd_version_number = 0;
	}
	return $gd_version_number;
}

//目录的实际大小
function dirsize($dir) {
	$dh = @opendir($dir);
	$size = 0;
	while($file = @readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$path = $dir.'/'.$file;
			if (@is_dir($path)) {
				$size += dirsize($path);
			} else {
				$size += @filesize($path);
			}
		}
	}
	@closedir($dh);
	return $size;
}

//目录个数
function dircount($dir) {
	$dh = @opendir($dir);
	$count = 0;
	while($file = @readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$path = $dir.'/'.$file;
			if (@is_dir($path)) {
				$count++;
			}
		}
	}
	@closedir($dh);
	return $count;
}

// 获取数据库大小单位
function get_real_size($size) {
	$kb = 1024;         // Kilobyte
	$mb = 1024 * $kb;   // Megabyte
	$gb = 1024 * $mb;   // Gigabyte
	$tb = 1024 * $gb;   // Terabyte

	if ($size < $kb) {
		return $size.' Byte';
	} elseif ($size < $mb) {
		return round($size/$kb,2).' KB';
	} elseif ($size < $gb) {
		return round($size/$mb,2).' MB';
	} elseif ($size < $tb) {
		return round($size/$gb,2).' GB';
	} else {
		return round($size/$tb,2).' TB';
	}
}

// 后台管理记录
function getlog() {
	global $timestamp, $onlineip, $sax_user;
	$action = $_POST['action'];
	if ($action && $action != 'autosave') {
		$script = str_replace('job=', '', $_SERVER['QUERY_STRING']);
		writefile(SABLOG_ROOT.'data/log/adminlog.php', "<?PHP exit('Access Denied'); ?>\t$timestamp\t".htmlspecialchars($sax_user)."\t$onlineip\t".htmlspecialchars(trim($action))."\t".htmlspecialchars(trim($script))."\n", 'a');
	}
}

// 检查标题是否符合逻辑
function checktitle($title) {
	if(!$title || getstrlen($title) > 120) {
		redirect('标题不能为空并且不能超过120个字符');
	}
}

// 检查分类是否已选择
function checkcate($cid) {
	if(!$cid) {
		redirect('你还没有选择分类');
	}
}

// 检查提交内容是否符合逻辑
function checkcontent($content) {
	if(!$content || getstrlen($content) < 4) {
		redirect('内容不能为空并且不能少于4个字符');
	}
}

function checkalias($content) {
	if (!preg_match("/^[a-z0-9_\-]+$/i", $content)) {
		return 0;
	} else {
		return 1;
	}
}

// 检查提交关键字是否符合逻辑
function checkkeywords($keywords) {
	if ($keywords) {
		$v = explode(',', $keywords);
		$v_num = count($v);
		if ($v_num > 10) {
			redirect('标签(Tags)的关键字不能超过10个');
		} else {
			for($i=0; $i<$v_num; $i++) {
				if(getstrlen($v[$i]) > 30) {
					redirect('标签(Tags)的每个关键字不能超过30个字符, '.htmlspecialchars($v[$i]).' 超过了30个字符');
				}
			}
		}
	}
}

// 检查链接名字是否符合逻辑
function checksitename($sitename) {
	if(!$sitename || getstrlen($sitename) > 30) {
		redirect('站点名不能空并不能大于30个字符');
	}
	elseif(eregi("[<>{}(),%#|^&!`$]",$sitename)) {
		redirect('站点名中不能含有特殊字符');
	}
}

// 检查链接描述是否符合逻辑
function checknote($note = '') {
	if($note && getstrlen($note) > 200) {
		redirect('站点描述不能大于200个字符');
	}
}

// 链接缩短
function cuturl($url) {
	$length = 45;
	$urllink = '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? "http://$url" : $url).'" target="_blank">';
	if(getstrlen($url) > $length) {
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	$urllink .= $url.'</a>';
	return $urllink;
}

// 分页函数
function multi($num, $perpage, $curpage, $mpurl) {
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	if($num > $perpage) {
		$page = 7;
		$offset = 3;
		$pages = @ceil($num / $perpage);
		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $curpage + $page - $offset - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $curpage - $pages + $to;
				$to = $pages;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$from = $pages - $page + 1;
				}
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1">第一页</a> ' : '').($curpage > 1 ? '<a href="'.$mpurl.'page='.($curpage - 1).'">上一页</a> ' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? $i.' ' : '<a href="'.$mpurl.'page='.$i.'">['.$i.']</a> ';
		}
		$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.'page='.($curpage + 1).'">下一页</a>' : '').($to < $pages ? ' <a href="'.$mpurl.'page='.$pages.'">最后一页</a>' : '');
		$multipage = $multipage ? $multipage : '';
	}
	return $multipage;
}

// 重建所有缓存
function restats() {
	allarticleids_recache();
	links_recache();
	newarticles_recache();
	newcomments_recache();
	settings_recache();
	categories_recache();
	statistics_recache();
	archives_recache();
	hottags_recache();
	stylevars_recache();
	stick_recache();
}

// 发送数据包
function sendpacket($url, $data) {
	$uinfo = parse_url($url);
	if ($uinfo['query']) {
		$data .= '&'.$uinfo['query'];
	}
	if (!$fp = fsockopen($uinfo['host'], ($uinfo['port'] ? $uinfo['port'] : '80'), $errno, $errstr, 3)) {
		return false;
	}
	fputs($fp, "POST ".$uinfo['path']." HTTP/1.1\r\n");
	fputs($fp, "Host: ".$uinfo['host']."\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-length: ".getstrlen($data)."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $data);
	$http_response = '';
	while(!feof($fp)) {
		$http_response .= fgets($fp, 128);
	}
	fclose($fp);
	list($http_headers, $http_content) = explode('\r\n\r\n', $http_response);
	return $http_response;
}

function ifchecked($var, $out) {
	global ${$out.'_Y'},${$out.'_N'};
	if($var) {
		${$out.'_Y'} = 'checked';
	} else {
		${$out.'_N'} = 'checked';
	}
}

// 转换时间单位:秒 to XXX
function format_timespan($seconds = '') {
	if ($seconds == '') $seconds = 1;
	$str = '';
	$years = floor($seconds / 31536000);
	if ($years > 0) {
		$str .= $years.' 年, ';
	}
	$seconds -= $years * 31536000;
	$months = floor($seconds / 2628000);
	if ($years > 0 || $months > 0) {
		if ($months > 0) {
			$str .= $months.' 月, ';
		}
		$seconds -= $months * 2628000;
	}
	$weeks = floor($seconds / 604800);
	if ($years > 0 || $months > 0 || $weeks > 0) {
		if ($weeks > 0)	{
			$str .= $weeks.' 周, ';
		}
		$seconds -= $weeks * 604800;
	}
	$days = floor($seconds / 86400);
	if ($months > 0 || $weeks > 0 || $days > 0) {
		if ($days > 0) {
			$str .= $days.' 天, ';
		}
		$seconds -= $days * 86400;
	}
	$hours = floor($seconds / 3600);
	if ($days > 0 || $hours > 0) {
		if ($hours > 0) {
			$str .= $hours.' 小时, ';
		}
		$seconds -= $hours * 3600;
	}
	$minutes = floor($seconds / 60);
	if ($days > 0 || $hours > 0 || $minutes > 0) {
		if ($minutes > 0) {
			$str .= $minutes.' 分钟, ';
		}
		$seconds -= $minutes * 60;
	}
	if ($str == '') {
		$str .= $seconds.' 秒, ';
	}
	$str = substr(trim($str), 0, -1);
	return $str;
}

// 上传文件
function upfile($source, $target) {
	// 如果一种函数上传失败，还可以用其他函数上传
	if (function_exists('move_uploaded_file') && move_uploaded_file($source, $target)) {
		chmod($target, 0666);
		return $target;
	} elseif (copy($source, $target)) {
		chmod($target, 0666);
		return $target;
	} elseif (is_readable($source)) {
		$filedata = loadfile($source);
		if(writefile($target, $filedata, 'wb', 0)) {
			return $target;
		} else {
			return false;
		}
	}
}

// 判断文件是否是通过 HTTP POST 上传的
function disuploadedfile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

// 连接多个ID
function implode_ids($array){
	$ids = $comma = '';
	if (is_array($array) && count($array)){
		foreach($array as $id) {
			$ids .= $comma.intval($id);
			$comma = ',';
		}
	}
	return $ids;
}

function buildtemplate($name) {
	$t_dir = SABLOG_ROOT.'templates/'.$name.'/';
	if(is_dir($t_dir)) {
		$dirs = dir($t_dir);
		$t_files = array();
		$i=0;
		while ($file = $dirs->read()) {
			$filepath = $t_dir.$file;
			$pathinfo = pathinfo($file);
			if(is_file($filepath) && $pathinfo['extension'] == 'php') {
				$i++;
				$t_files[$i]['tplfile'] = $filepath;
				$t_files[$i]['objfile'] = SABLOG_ROOT.'data/template/'.$name.'_'.str_replace('.php','',$file).'.tpl.php';
			}
		}
		$dirs->close();

		require_once SABLOG_ROOT.'include/func/template.func.php';

		foreach($t_files as $data) {
			parse_template($data['tplfile'], $data['objfile']);
		}
	} else {
		redirect('模板不存在');
	}
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $DB, $sizelimit, $startrow, $sqlcompat;

	$offset = 300;
	$tabledump = '';

	if(!$startfrom) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
		$createtable = $DB->query("SHOW CREATE TABLE $table");
		$create = $DB->fetch_row($createtable);
		$tabledump .= $create[1];

		if($sqlcompat == 'MYSQL41' && $DB->version() < '4.1') {
			$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $tabledump);
		}
		if($DB->version() > '4.1') {
			$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=utf8", $tabledump);
		}

		$query = $DB->query("SHOW TABLE STATUS LIKE '$table'");
		$tablestatus = $DB->fetch_array($query);
		$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
		if($sqlcompat == 'MYSQL40' && $DB->version() >= '4.1') {
			if($tablestatus['Auto_increment'] <> '') {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}
		}
	}

	$tabledumped = 0;
	$numrows = $offset;

	while($currsize + getstrlen($tabledump) < $sizelimit * 1000 && $numrows == $offset) {
		$tabledumped = 1;
		$rows = $DB->query("SELECT * FROM $table LIMIT $startfrom, $offset");
		$numfields = $DB->num_fields($rows);
		$numrows = $DB->num_rows($rows);
		while($row = $DB->fetch_row($rows)) {
			$comma = '';
			$tabledump .= "INSERT INTO $table VALUES (";
			for($i = 0; $i < $numfields; $i++) {
				$tabledump .= $comma.'\''.mysql_escape_string($row[$i]).'\'';
				$comma = ',';
			}
			$tabledump .= ");\n";
		}
		$startfrom += $offset;
	}

	$startrow = $startfrom;
	$tabledump .= "\n";
	return $tabledump;
}

//获得的全部RSS内容并列入数组
function getrssdata($data) {
	$data = str_replace(array("\r","\n",'<![CDATA[',']]>'),'',$data);
	preg_match_all("/<item>(.+?)<\/item>/is", $data, $article);

	$rssdb = $article[1];
	$articledb = array();
	if (!is_array($rssdb)) {
		$articledb[]=parserss($rssdb);
	} else {
		foreach ($rssdb as $rss) {
			$articledb[]=parserss($rss);
		}
	}
	return $articledb;
}

//分析出RSS的每篇文章
function parserss($rssdata) {
	global $options,$timeoffset;
	if (preg_match("/<title>(.+?)<\/title>/is", $rssdata, $match)) {
		$title = sax_addslashes($match[1]);
	}
	if (preg_match("/<pubDate>(.+?)<\/pubDate>/is", $rssdata, $match)) {
		$dateline = strtotime($match[1])-$timeoffset*3600;
	}
	if (preg_match("/<content:encoded>(.+?)<\/content:encoded>/is", $rssdata, $match)) {
	} else {
		preg_match("/<description>(.+?)<\/description>/is", $rssdata, $match);
	}
	$content = sax_addslashes($match[1]);
	return array('title'=>$title, 'dateline'=>$dateline, 'content'=>$content);
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function template($file) {
	global $options, $tplrefresh;
	$tplfile = SABLOG_ROOT.'templates/admin/'.$file.'.php';
	$objfile = SABLOG_ROOT.'data/template/admin_'.$file.'.tpl.php';
	if (@filemtime($tplfile) > @filemtime($objfile)) {
		require_once SABLOG_ROOT.'include/func/template.func.php';
		parse_template($tplfile, $objfile);
	}
	return $objfile;
}

//获取模板信息
function get_template_info($infofile) {
	global $template_dir;
	$infofile = str_replace(array('..',':/'),array('',''),$infofile);
	$template_info = @file(SABLOG_ROOT.$template_dir.$infofile);
	if ($template_info) {
		$cssdata = array();
		foreach ($template_info AS $data) {
			$data = str_replace('://','=//',$data);
			$info = explode(':', $data);
			$info[1] = trim(str_replace('=//','://',$info[1]));
			$cssdata[] = $info[1];
		}
		//判断制作者是否有网站
		if ($cssdata[4]) {
			$cssdata[3] = '<a href="'.trim($cssdata[4]).'" title="访问模板作者的网站" target="_blank">'.trim($cssdata[3]).'</a>';
		}
		//判断缩略图是否存在
		$templatedir = dirname($template_dir.$infofile);
		if (file_exists($templatedir.'/screenshot.png')) {
			$screenshot = $templatedir.'/screenshot.png';
		} else {
			$screenshot = dirname($template_dir.dirname($infofile)).'/no.png';
		}
		$info = array(
			'name' => $cssdata[0],
			'dirurl' => urlencode(dirname($infofile)),
			'version' => $cssdata[1],
			'description' => $cssdata[2],
			'author' => $cssdata[3],
			'screenshot' => $screenshot
		);
		return $info;
	} else {
		return false;
	}
}

?>