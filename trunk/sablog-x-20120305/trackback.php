<?php
// ========================== 文件说明 ==========================//
// 本文件说明：Trackback接收
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//


// 加载前台常用函数
require_once('global.php');

header('Content-type: text/xml');	
// 加载编码转换函数
if (!$options['enable_trackback']) {
	showxml('Trackback功能没有开启');
}
$code = $_GET['code'] ? $_GET['code'] : $_POST['code'];
$code = authcode($code, 'DECODE');
$carr = explode("\t", $code);
if(count($carr) != 2) {
	showxml('参数不正确');
}
$articleid = (int)$carr[0];

//检查失效时间
if($options['trackback_life'] && ($timestamp-intval($carr[1])>(3600*24))) {
	showxml('已经超过本文允许Trackback的时间');
}

$article = $DB->fetch_one_array("SELECT dateline,closetrackback FROM {$db_prefix}articles WHERE articleid='$articleid'");
if (!$article) {
	showxml('文章不存在');
} elseif ($article['closetrackback']) {
	showxml('本文此时不允许引用');
} elseif ($article['dateline'] != intval($carr[1])) {
	showxml('文章时间验证失败');
}

$url = sax_addslashes(trim($_POST['url']));
if ($url) {
	$title		= sax_addslashes(html_excerpt($_POST['title']));
	$excerpt	= sax_addslashes(trimmed_title(html_excerpt($_POST['excerpt'])), 200);
	$blog_name	= sax_addslashes(html_excerpt($_POST['blog_name']));
}

if (!$title || !$excerpt || !$url || !$blog_name) {
	showxml('参数不正确');
} elseif(substr($url, 0, 7) != 'http://') {
	showxml('参数不正确');
}

// 检查Spam
// 定义发送来的此条Trackback初始分数
$point = 0;
$options['tb_spam_level'] = in_array($options['tb_spam_level'], array('strong', 'weak', 'never')) ? $options['tb_spam_level'] : 'weak';

if ($options['audit_trackback']) {
	//如果人工审核
	$visible = '0';
} elseif ($options['tb_spam_level'] != 'never') {
	$source_content = '';
	$source_content = fopen_url($url);
	$this_server = str_replace(array('www.', 'http://'), '', $_SERVER['HTTP_HOST']);
	//获取接受来的url原代码和本服务器的hostname

	if (empty($source_content)) {
		//没有获得原代码就-1分
		$point -= 1;
	} else {
		if (strpos(strtolower($source_content), strtolower($this_server)) !== FALSE) {
			//对比链接，如果原代码中包含本站的hostname就+1分，这个未必成立
			$point += 1;
		}
		if (strpos(strtolower($source_content), strtolower($title)) !== FALSE) {
			//对比标题，如果原代码中包含发送来的title就+1分，这个基本可以成立
			$point += 1;
		}
		if (strpos(strtolower($source_content), strtolower($excerpt)) !== FALSE) {
			//对比内容，如果原代码中包含发送来的excerpt就+1分，这个由于标签或者其他原因，未必成立
			$point += 1;
		}
	}
	$interval = $options['tb_spam_level'] == 'strong' ? 300 : 600;
	//根据防范强度设置时间间隔，强的话在5分钟内发现有同一IP发送。弱的话就是10分钟内发现有同一IP发送.
	$r = $DB->fetch_one_array("SELECT commentid FROM {$db_prefix}comments WHERE ipaddress='$onlineip' AND dateline+".$interval.">='$timestamp' LIMIT 1");
	//在单位时间内发送的次数
	if ($r) {
		//如果发现在单位时间内同一IP发送次数大于0就扣一分，人工有这么快发送trackback的吗？
		$point -= 1;
	}

	$r = $DB->fetch_one_array("SELECT commentid FROM {$db_prefix}comments WHERE REPLACE(LCASE(url),'www.','')='".str_replace('www.','',strtolower($url))."'");
	//对比数据库中的url和接收来的
	if ($r) {
		//如果发现有相同，扣一分。
		$point -= 1;
	}

	//禁止词语
	if ($options['spam_enable'] && $options['spam_words']) {
		$options['spam_words'] = str_replace('，', ',', $options['spam_words']);
		$badwords = explode(',', $options['spam_words']);
		if (is_array($badwords) && count($badwords) ) {
			foreach ($badwords AS $n) {
				if ($n) {
					if (preg_match( "/".preg_quote($n, '/' )."/i", $title.$excerpt.$url.$blog_name)) {
						$point -= 1;
					}
				}
			}
		}
	}

	if ($options['tb_spam_level'] == 'strong') {
		//高强度防范
		$query = $DB->query("SELECT ipaddress,articleid FROM {$db_prefix}comments WHERE (ipaddress='$onlineip' OR articleid='$articleid') AND dateline+86400>='$timestamp'");
		//搜索数据库里一天内发送此trackback来本站的IP和文章ID
		while ($trackback = $DB->fetch_array($query)) {
			if ($trackback['ipaddress'] == $onlineip && $trackback['articleid'] == $articleid) {
				//如果数据库内的articleid和接收来的articleid一样，IP也一样，视为重复发送。就减1分。
				$point -= 1;
			}
		}
		// 防范强:最终分数少于1分就CUT！
		$visible = ($point < 1) ? '0' : '1';
	} else {
		// 防范弱:最终分数少于0分才CUT！
		$visible = ($point < 0) ? '0' : '1';
	}
} else {
	$visible = '1';
}
// 检查Spam完毕

$DB->query("INSERT INTO {$db_prefix}comments (articleid, author, url, dateline, content, ipaddress, type, visible) VALUES ('$articleid', '$blog_name', '$url', '$timestamp', '{$title}\n{$excerpt}', '$onlineip', 'trackback', '$visible')");

//更新文章Trackback数量
if ($visible) {
	$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments+1 WHERE articleid='$articleid'");
	$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count+1");
}
showxml('Trackback 成功接收',0);

//发送消息页面
function showxml($message, $error = 1) {
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<response>\n";
	echo "\t<error>".$error."</error>\n";
	echo "\t<message>".$message."</message>\n";
	echo "</response>\n";
	exit;
}

//获取远程页面的内容
function fopen_url($url) {
	if (function_exists('file_get_contents')) {
		$file_content = @file_get_contents($url);
	} elseif (ini_get('allow_url_fopen') && ($file = @fopen($url, 'rb'))){
		$file_content = '';
		while (!feof($file)) {
			$file_content .= fgets($file);
		}
		fclose($file);
	} elseif (function_exists('curl_init')) {
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle, CURLOPT_FAILONERROR,1);
  		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'SaBlog-X Trackback Spam Check');
		$file_content = curl_exec($curl_handle);
		curl_close($curl_handle);
	} elseif (function_exists('fsockopen')) {
		$a_url = parse_url($url);
		$fp = fsockopen($a_url['host'], ($a_url['port'] ? $a_url['port'] : 80), $errno, $errstr, 8);
		if ($fp) {
			$out = "GET ".$a_url['path']." ".($a_url['query'] ? '?'.$a_url['query'] : '')." HTTP/1.1\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Referer: $options[url]\r\n";
			$out .= "Host: ".$a_url['path']."\r\n";
			$out .= "User-Agent: Sablog-X\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);
			while (!feof($fp)) {
				$file_content .= fgets($fp, 128);
			}
			fclose($fp);
		}
	} else {
		$file_content = '';
	}
	return $file_content;
}

/*
//转换到UTF-8编码
function iconv2utf($chs) {
	global $encode;
	if ($encode != 'utf-8') {
		if (function_exists('mb_convert_encoding')) {
			$chs = mb_convert_encoding($chs, 'UTF-8', $encode);
		} elseif (function_exists('iconv')) {
			$chs = iconv($encode, 'UTF-8', $chs);
		}
	}
	return $chs;
}
*/

function html_excerpt( $str, $count ) {
	$str = strip_tags( $str );
	$str = preg_replace( '/&[^;\s]{0,6}$/', '', $str );
	return $str;
}

/*
// HTML转换为纯文本
function html2text($content) {
	$content = preg_replace("/<style .*?<\/style>/is", "", $content);
	$content = preg_replace("/<script .*?<\/script>/is", "", $content);
	$content = preg_replace("/<br\s*\/?>/i", "\n", $content);
	$content = preg_replace("/<\/?p>/i", "\n", $content);
	$content = preg_replace("/<\/?td>/i", "\n", $content);
	$content = preg_replace("/<\/?div>/i", "\n", $content);
	$content = preg_replace("/<\/?blockquote>/i", "\n", $content);
	$content = preg_replace("/<\/?li>/i", "\n", $content);
	$content = strip_tags($content);
	$content = preg_replace("/\&\#.*?\;/i", "", $content);
	return $content;
}

*/
?>