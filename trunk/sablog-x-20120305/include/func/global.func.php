<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前后台公共函数
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}

//获得关联的内容ID
function get_cids($mid) {
	global $DB, $db_prefix;
	if ($mid) {
		$query = $DB->query("SELECT cid FROM {$db_prefix}relationships WHERE mid IN ($mid)");
		$cids = $comma = '';
		while ($relationship = $DB->fetch_array($query)) {
			$cids .= $comma.$relationship['cid'];
			$comma = ',';
		}
		!$cids && $cids = 0;
	} else {
		$cids = 0;
	}
	return $cids;
}
//获得关联的容器ID
function get_mids($cid, $type='') {
	global $DB, $db_prefix;
	if (!in_array($type, array('tag','category'))) {
		$type = '';
	}
	if ($cid) {
		if ($type) {
			$query = $DB->query("SELECT c.mid FROM {$db_prefix}relationships c LEFT JOIN {$db_prefix}metas m ON (m.mid=c.mid) WHERE m.type='$type' AND c.cid IN ($cid)");
		} else {
			$query = $DB->query("SELECT mid FROM {$db_prefix}relationships WHERE cid IN ($cid)");
		}
		$mids = $comma = '';
		while ($relationship = $DB->fetch_array($query)) {
			$mids .= $comma.$relationship['mid'];
			$comma = ',';
		}
		!$mids && $mids = 0;
	} else {
		$mids = 0;
	}
	return $mids;
}
//获得容器内的内容数
function get_meta_article_count($mid) {
	global $DB, $db_prefix;
	if ($mid) {
		$total = $DB->result($DB->query("SELECT COUNT(c.cid) FROM {$db_prefix}relationships c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.cid) WHERE a.visible='1' AND c.mid='$mid'"), 0);
	} else {
		$total = 0;
	}
	return $total;
}
//更新容器内的内容数
function update_meta_count($mid, $total=0) {
	global $DB, $db_prefix;
	$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count='$total' WHERE mid='$mid'");
}
//添加记录进入容器
function insert_meta($name, $slug, $type, $count=0) {
	global $DB, $db_prefix;
	!$slug && $slug = $name;
	$DB->query("INSERT INTO {$db_prefix}metas (name,slug,type,count) VALUES ('$name', '$slug', '$type', '$count')");
	return $DB->insert_id();
}
//更新标签容器
function insert_tag_in_article($name, $articleid=0, $slug='') {
	global $DB, $db_prefix;
	$name = sax_addslashes($name);
	$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE name='$name' AND type='tag' LIMIT 1");
	if(!$r) {
		$new_mid = insert_meta($name,$slug,'tag',1);
		$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$new_mid')");
		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count+1");
	} else {
		$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '".$r['mid']."')");
		$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='$mid' AND type='tag'");
	}
}

// 去除转义字符
function sax_stripslashes($array) {
	if (is_array($array)) {
		foreach ($array as $k => $v) {
			$array[$k] = sax_stripslashes($v);
		}
	} else if (is_string($array)) {
		$array = stripslashes($array);
	}
	return $array;
}

// 添加转义
function sax_addslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sax_addslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

//格式化时间
function sadate($format, $timestamp='', $convert=0){
	global $options, $timeoffset;
	!$timestamp && $timestamp = time();
	$s = gmdate($format, $timestamp + $timeoffset * 3600);
	
	if ($options['dateconvert'] && $convert) {
		$now = time();
		$interval = $now - $timestamp;

		//分钟内
		if ($interval < 60) {
			return '<span title="'.$s.'">'.$interval.'秒前</span>';
		}
		//小时内
		if ($interval < 3600) {
			return '<span title="'.$s.'">'.intval($interval / 60).'分钟前</span>';
		}
		//一天内
        if ($interval < 86400) {
			return '<span title="'.$s.'">'.intval($interval / 3600).'小时前</span>';
        }
		//两天内
        if ($interval < 172800) {
			return '<span title="'.$s.'">昨天 '.gmdate('H:i', $timestamp + $timeoffset * 3600).'</span>';
        }
		//一星期内
        if ($interval < 604800) {
			return '<span title="'.$s.'">'.intval($interval / 86400).'天前 '.gmdate('H:i', $timestamp + $timeoffset * 3600).'</span>';
        }
	}
	return $s;

}
// 获得散列
function formhash($specialadd = '') {
	global $sax_user, $sax_uid, $sax_pw, $timestamp;
	return substr(md5(substr($timestamp, 0, -7).$sax_user.$sax_uid.$sax_pw.$specialadd), 8, 8);
}

//获得某年某月的时间戳
function gettimestamp($year, $month) {
	$start = strtotime($year.'-'.$month.'-1');
	if ($month == 12) {
		$endyear  = $year + 1;
		$endmonth = 1;
	} else {
		$endyear  = $year;
		$endmonth = $month+1;
	}
	$end = strtotime($endyear.'-'.$endmonth.'-1');
	return $start.'-'.$end;
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(1, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = getstrlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function tag_count_scale($count) {
	return round(log10($count + 1) * 100);
}

function correcttime($timestamp) {
	global $timeoffset;
	$z = date('Z');
	if ($z != '0') {
		$timestamp = $timestamp - ($z - $timeoffset * 3600);
	} else {
		$timestamp = $timestamp - $timeoffset * 3600;
	}
	return $timestamp;
}

//判断是否为邮件地址
function isemail($email) {
	return getstrlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

//截取字数
function trimmed_title($text, $limit=12, $start=0) {
	if ($limit) {
		$val = csubstr($text, $limit, $start);
		return $val[1] ? $val[0] . '...' : $val[0];
	} else {
		return $text;
	}
}

function csubstr($text, $limit=12, $start=0) {
	if (function_exists('mb_substr')) {
		$more = (mb_strlen($text,'UTF-8') > $limit) ? true : false;
		$text = mb_substr($text, $start, $limit, 'UTF-8');
		return array($text, $more);
	} elseif (function_exists('iconv_substr')) {
		$more = (iconv_strlen($text) > $limit) ? true : false;
		$text = iconv_substr($text, $start, $limit, 'UTF-8');
		return array($text, $more);
	} else {
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);   
		if(func_num_args() >= 3) {   
			if (count($ar[0])>$limit) {
				$more = true;
				$text = join('',array_slice($ar[0], $start, $limit)); 
			} else {
				$more = false;
				$text = join('',array_slice($ar[0], $start, $limit)); 
			}
		} else {
			$more = false;
			$text =  join('',array_slice($ar[0], $start)); 
		}
		return array($text, $more);
	} 
}

//取得字符串的长度，包括中英文。
function getstrlen($text){
	if (function_exists('mb_substr')) {
		$length=mb_strlen($text,'UTF-8');
	} elseif (function_exists('iconv_substr')) {
		$length=iconv_strlen($text,'UTF-8');
	} else {
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);   
		$length=count($ar[0]);
	}
	return $length;
}

//转换字符
function char_cv($string) {
	$string = htmlspecialchars(sax_addslashes($string));
	return $string;
}

//页面输出
function PageEnd() {
	global $options;
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	ob_end_clean();
	updatesession();
	$options['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	exit;
}

// base64编码函数
function authcode($string, $operation = 'ENCODE') {
	$string = $operation == 'DECODE' ? base64_decode($string) : base64_encode($string);
	return $string;
}

//获取请求来路
function getreferer() {
	global $options;
	if(!$referer && !$_SERVER['HTTP_REFERER']) {
		$referer = $options['url'];
	} elseif (!$referer && $_SERVER['HTTP_REFERER']) {
		$referer = $_SERVER['HTTP_REFERER'];
	} else {
		$referer = htmlspecialchars($referer);
	}
	if(strpos($referer, 'post.php')) {
		$referer = $options['url'];
	}
	return $referer;
}

function submitcheck($var, $cp = 0) {
	if(empty($GLOBALS[$var])) {
		return false;
	} else {
		if ($cp) {
			$msgfunc = 'redirect';
		} else {
			$msgfunc = 'message';
		}
		global $options, $seccode;
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || $GLOBALS['formhash'] != formhash() || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
			$msgfunc('您的请求来路不正确,无法提交.');
		} else {
        	if($options['seccode']) {
				$clientcode = $GLOBALS['clientcode'];
				if (!$clientcode || strtolower($clientcode) != strtolower($seccode)) {
					$seccode = random(6, 1);
					updatesession();
					$msgfunc('验证码错误,请返回重新输入.', $referer);
				}
        	}
			return true;
		}
	}
}

// 检查链接URL是否符合逻辑
function isurl($url) {
	if($url) {
		if (!preg_match("#^(http|news|https|ftp|ed2k|rtsp|mms)://#", $url)) {
			return false;
		}
		$key = array("\\",' ',"'",'"','*',',','<','>',"\r","\t","\n",'(',')','+',';');
		foreach($key as $value){
			if (strpos($url,$value) !== false){ 
				return false;
				break;
			}
		}
	}
	return true;
}

function updatesession() {
	global $DB, $db_prefix, $onlineip, $sax_uid, $sax_group, $timestamp, $seccode, $lastactivity;
	if($sax_uid && $timestamp - $lastactivity > 7200) {
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET lastip='$onlineip', lastvisit=lastactivity, lastactivity='$timestamp' WHERE userid='$sax_uid'");
	}
}

function isrobot() {
	$kw_spiders = 'Bot|Crawl|Spider|Slurp|sohu|Twiceler|lycos|robozilla|Google|baidu|msn|yahoo|sogou';
	$kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
	if(preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
		return 1;
	} elseif(preg_match("/($kw_browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
		return 0;
	} else {
		return 0;
	}
}

// 登录记录
function loginresult($username = '', $result) {
	global $timestamp,$onlineip;
	writefile(SABLOG_ROOT.'data/log/loginlog.php', "<?PHP exit('Access Denied'); ?>\t$username\t$timestamp\t$onlineip\t$result\n", 'a');
}

function scookie($key, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	$key = ($prefix ? $cookiepre : '').$key;
	$life = $life ? $timestamp + $life : 0;
	$useport = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	@setcookie($key, $value, $life, $cookiepath, $cookiedomain, $useport);
}

// 删除cookies
function dcookies($key = '') {
	global $sax_uid, $sax_user, $sax_pw;
	if ($key) {
		if(is_array($_COOKIE[$key])) {
			foreach ($_COOKIE[$key] as $k => $name) {
				scookie($key.'['.$k.']', '', -86400 * 365);
			}
		} else {
			scookie($key, '', -86400 * 365);
		}
	} else {
		if(is_array($_COOKIE)) {
			foreach ($_COOKIE as $key => $val) {
				scookie($key, '', -86400 * 365);
			}
		}
		$sax_uid = 0;
		$sax_user = $sax_pw = '';
	}
}

//读取文件内容
function loadfile($filename, $filesize = 0, $method='rb', $local = 1) {
	$filedata = false;
	if (strpos($filename, '..') !== false) {
		 exit('Load file failed');
	}
	if(function_exists('file_get_contents')) {
		$filedata = @file_get_contents($filename);
	} elseif($local && $fp = @fopen($filename, $method)) {
		flock($fp,LOCK_SH);
		$size = $filesize ? $filesize : filesize($filename);
		$filedata = @fread($fp, $size);
		fclose($fp);
	} elseif(!$local) {
		$filedata = @implode('', file($filename));
	}
	return $filedata;
}

//写入文件内容
function writefile($filename, $data, $method = 'wb', $chmod = 1) {
	$return = false;
	if (strpos($filename, '..') !== false) {
		 exit('Write file failed');
	}
	if($fp = @fopen($filename, $method )) {
		@flock($fp, LOCK_EX);
		$return = fwrite($fp, $data);
		fclose($fp);
		$chmod && @chmod($filename,0777);
	}
	return $return;
}

// 清除HTML代码
function html_clean($content) {
	$content = htmlspecialchars($content);
	$content = str_replace("\n", "<br />", $content);
	$content = str_replace("  ", "&nbsp;&nbsp;", $content);
	$content = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $content);
	$content = preg_replace("/\[quote=(.*?)\]\s*(.+?)\s*\[\/quote\]/is", "<div class=\"quote-comment\"><div style=\"font-weight: bold\">引用 \\1 说过的话:</div><div class=\"quote\">\\2</div></div>", $content);
	return $content;
}

function get_avatar($email, $size = 0) {
	global $options;
	if (!$options['show_avatar']) {
		$avatardb = array();
	} else {
		if (!$size) {
			if (!$options['avatar_size'] || !is_numeric($options['avatar_size'])) {
				$size = '36';
			} else {
				$size = $options['avatar_size'];
			}
		}

		$default = 'gravatar_default';

		$host = 'http://www.gravatar.com';

		if ( 'mystery' == $default ) {
			$default = $host.'/avatar/ad516503a11cd5ca435acc9bb6523536?s='.$size;
			// ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
		} elseif ( !empty($email) && 'gravatar_default' == $default ) {
			$default = '';
		} elseif ( 'gravatar_default' == $default ) {
			$default = "$host/avatar/s={$size}";
		} elseif ( empty($email) ) {
			$default = "$host/avatar/?d=$default&amp;s={$size}";
		}

		if ($email) {
			$src = $host.'/avatar/';
			$src .= md5(strtolower($email));
			$src .= '?s='.$size;
			$src .= '&amp;d='.urlencode($default);
			if ($options['avatar_level']) {
				$src .= '&amp;r='.$options['avatar_level'];
			}
		} else {
			$src = $default;
		}

		$avatardb = array(
			'size' => $size,
			'src' => $src
		);
	}
	return $avatardb;

}

?>