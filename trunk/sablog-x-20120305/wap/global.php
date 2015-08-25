<?php
// ========================== 文件说明 ==========================//
// 本文件说明：WAP公共函数
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

// 清除HTML代码
function wap_html_clean($content) {
	$content = htmlspecialchars($content);
	$content = str_replace("\n", "<br />", $content);
	$content = str_replace("  ", "&nbsp;&nbsp;", $content);
	$content = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $content);
	$content = str_replace("$", "$$", $content);
	$content = preg_replace("/\[quote=(.*?)\]\s*(.+?)\s*\[\/quote\]/is", "<div style=\"font-weight: bold\">引用 \\1 说过的话:</div><div class=\"quote\">\\2</div>", $content);
	return $content;
}

// 转换&#39
function cvurl($content) {
	$content = str_replace("&", "&amp;", $content);
	return $content;
}

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
	$content = preg_replace("/\&nbsp\;/i", " ", $content);
	$content = preg_replace("/\&nbsp/i", " ", $content); 
	$content = strip_tags($content);
	$content = preg_replace("/\&\#.*?\;/i", "", $content);
	return $content;
}

// 后台管理记录
function getlog() {
	global $timestamp, $onlineip, $action, $sax_user;
	if ($action) {
		writefile(SABLOG_ROOT.'data/log/adminlog.php', "<?PHP exit('Access Denied'); ?>\t$timestamp\t$sax_user\t$onlineip\t".htmlspecialchars(trim($action))."\twap\n", 'a');
	}
}
// 检查用户提交内容合法性
function checkcontent($content) {
	global $options;
    if(empty($content)) {
        $result .= '内容不能为空.<br />';
        return $result;
	}
    if(getstrlen($content) < $options['comment_min_len']) {
        $result .= '内容不能少于'.$options['comment_min_len'].'字节.<br />';
        return $result;
	}
	if(getstrlen($content) > $options['comment_max_len']) {
        $result .= '内容不能超过'.$options['comment_max_len'].'字节.<br />';
        return $result;
	}
}

// 系统消息
function wap_message($msg,$link = array()) {
	echo '<p>'.$msg.'</p>';
	if ($link) {
		echo '<p><a href="'.$link['link'].'">'.$link['title'].'</a></p>';
	}
	wap_footer();
}

function wap_norun($title,$msg='') {
	$msg = $msg ? $msg : $title;
	wap_header($title);
	echo '<p>'.$msg.'</p>';
	wap_footer();
}

// WML文档头
function wap_header($title = '') {
	global $options;
	!$title && $title = $options['name'];

	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<!DOCTYPE html PUBLIC \"-//WAPFORUM//DTD XHTML Mobile 1.0//EN\" \"http://www.wapforum.org/DTD/xhtml-mobile10.dtd\">\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
	echo "<head>\n";
	echo "<meta http-equiv=\"Content-Type\" content=\"application/xhtml+xml; charset=UTF-8\" />\n";
	echo "<meta name=\"generator\" content=\"Sablog-X 2.0\" />\n";
	echo "<title>".$title."</title>\n";
?>
<style type="text/css">
body,ul,ol,form{margin:0 0;padding:0 0}
ul,ol{list-style:none}
h1,h2,h3,div,li,p{margin:0 0;padding:2px 2px;font-size:medium}
h1{background:#7acdea}
h2{background:#d2edf6}
h3{background:#fffcaa;}
</style>
<?php
	echo "</head>\n";
	echo "<body>\n";
	echo "<h1 id=\"sitename\"><a href=\"".$options['url']."\" accesskey=\"0\">".$options['name']."</a></h1>\n";
	wap_menu();
	wap_loginstats();
	echo "<h2>".$title."</h2>";
}

function wap_menu() {
	echo "<div><p>\n";
	echo "<a href=\"index.php?action=article\">日志</a>\n";
	echo " | <a href=\"index.php?action=category\">分类</a>\n";
	echo " | <a href=\"index.php?action=archives\">归档</a>\n";
	echo " | <a href=\"index.php?action=tag\">标签</a>\n";
	echo " | <a href=\"index.php?action=search\">搜索</a>\n";
	echo "</p></div>\n";
}

function wap_loginstats() {
	global $DB, $db_prefix, $sax_uid, $sax_user, $sax_group;
	echo "<div><p>\n";
	if ($sax_uid && $sax_user) {
		echo "您好:".$sax_user." <a href=\"index.php?action=logout\">注销</a>";
		if ($sax_group == 1 || $sax_group == 2) {
			echo " | <a href=\"index.php?action=add\">写文章</a>";
			echo " | <a href=\"index.php?action=comments\">评论</a>\n";
			$hiddencomtotal = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE visible='0'"), 0);
			if ($hiddencomtotal) {
				echo " | <a href=\"index.php?action=auditcm\">有{$hiddencomtotal}条隐藏评论</a>";
			}
		}
	} else {
		echo "<a href=\"index.php?action=login\">登陆</a>\n";
	}
	echo "</p></div>\n";
}

// WML文档尾
function wap_footer() {
	echo "</body>\n";
	echo "</html>\n";
	wap_output();
	updatesession();
	exit;
}

function transhash($url, $tag = '') {
	global $sax_hash;
	$tag = sax_stripslashes($tag);
	if(!$tag || (!preg_match("/^(http:\/\/|mailto:|#|javascript)/i", $url) && !strpos($url, 'sax_hash='))) {
		if($pos = strpos($url, '#')) {
			$urlret = substr($url, $pos);
			$url = substr($url, 0, $pos);
		} else {
			$urlret = '';
		}
		$url .= (strpos($url, '?') ? '&amp;' : '?').'sax_hash='.$sax_hash.$urlret;
	}
	return $tag.$url;
}

function wap_output() {
	/*
	$content = ob_get_contents();
	$content = preg_replace("/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'\s]+)/ies", "transhash('\\3','<a\\1href=\\2')", $content);
	ob_end_clean();
	//要做内容编码转换将来对这个content就可以了
	echo $content;
	*/
}

// 分页函数
function multi($num, $perpage, $curpage, $mpurl) {
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	if($num > $perpage) {
		$page = 5;
		$offset = 2;
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

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1">首页</a> ' : '').($curpage > 1 ? '<a href="'.$mpurl.'page='.($curpage - 1).'">上页</a> ' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? $i.' ' : '<a href="'.$mpurl.'page='.$i.'">['.$i.']</a> ';
		}
		$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.'page='.($curpage + 1).'">下页</a>' : '').($to < $pages ? ' <a href="'.$mpurl.'page='.$pages.'">末页</a>' : '');
		$multipage = $multipage ? '<p>页: '.$multipage."</p>\n" : '';
	}
	return $multipage;
}


//改进harry的HTML截取函数
function htmlSubString($content,$maxlen=300,$offset=0){
	//把字符按HTML标签变成数组。
	$content = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows=0;	//中英字数
	$outstr="";		//生成的字串
	$wordend=false;	//是否符合最大的长度
	$beginTags=0;	//除<img><br><hr>这些短标签外，其它计算开始标签，如<div*>
	$endTags=0;		//计算结尾标签，如</div>，如果$beginTags==$endTags表示标签数目相对称，可以退出循环。
	//print_r($content);
	foreach($content as $value){
		if (trim($value)=="") continue;	//如果该值为空，则继续下一个值

		if (strpos(";$value","<")>0){
			//如果与要载取的标签相同，则到处结束截取。
			if (trim($value)==$maxlen) {
				$wordend=true;
				continue;
			}

			if ($wordend==false){
				$outstr.=$value;
				if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
					$beginTags++; //除img,br,hr外的标签都加1
				}
			}else if (preg_match("/<\/([^>]+?)>/is",$value,$matches)){
				$endTags++;
				$outstr.=$value;
				if ($beginTags==$endTags && $wordend==true) break;	//字已载完了，并且标签数相称，就可以退出循环。
			}else{
				if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<br([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)) {
					$beginTags++; //除img,br,hr外的标签都加1
					$outstr.=$value;
				}
			}
		}else{
			if (is_numeric($maxlen)){	//截取字数
				$curLength=getstrlen($value);
				$maxLength=$curLength+$wordrows;
				if ($wordend==false){
					if ($maxLength>$maxlen){	//总字数大于要截取的字数，要在该行要截取
						$outstr.=trimmed_title($value,$maxlen-$wordrows,$offset);
						$wordend=true;
					}else{
						$wordrows=$maxLength;
						$outstr.=$value;
					}
				}
			}else{
				if ($wordend==false) $outstr.=$value;
			}
		}
	}
	//循环替换掉多余的标签，如<p></p>这一类
	while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)){
		$outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
	}
	//把误换的标签换回来
	if (strpos(";".$outstr,"[html_")>0){
		$outstr=str_replace("[html_&lt;]","<",$outstr);
		$outstr=str_replace("[html_&gt;]",">",$outstr);
	}

	//echo htmlspecialchars($outstr);
	return $outstr;
}

//去掉多余的空标签
function strip_empty_html($matches){
	$arr_tags1=explode(" ",$matches[1]);
	if ($arr_tags1[0]==$matches[2]){	//如果前后标签相同，则替换为空。
		return "";
	}else{
		$matches[0]=str_replace("<","[html_&lt;]",$matches[0]);
		$matches[0]=str_replace(">","[html_&gt;]",$matches[0]);
		return $matches[0];
	}
}


function Thumb_GD($targetfile, $thumbwidth, $thumbheight) {
	$attachinfo = @getimagesize($targetfile);

	list($img_w, $img_h) = $attachinfo;

	header('Content-type: '.$attachinfo['mime']);

	if($img_w >= $thumbwidth || $img_h >= $thumbheight) {

		if(function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') && function_exists('imagejpeg')) {

			switch($attachinfo['mime']) {
				case 'image/jpeg':
					$imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
					$imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
					break;
				case 'image/gif':
					$imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
					$imagefunc = function_exists('imagegif') ? 'imagegif' : '';
					break;
				case 'image/png':
					$imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
					$imagefunc = function_exists('imagepng') ? 'imagepng' : '';
					break;
			}

			$imagefunc = $thumbstatus == 1 ? 'imagejpeg' : $imagefunc;

			$attach_photo = $imagecreatefromfunc($targetfile);

			$x_ratio = $thumbwidth / $img_w;
			$y_ratio = $thumbheight / $img_h;

			if(($x_ratio * $img_h) < $thumbheight) {
				$thumb['height'] = ceil($x_ratio * $img_h);
				$thumb['width'] = $thumbwidth;
			} else {
				$thumb['width'] = ceil($y_ratio * $img_w);
				$thumb['height'] = $thumbheight;
			}
			
			$cx = $img_w;
			$cy = $img_h;

			$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);

			imageCopyreSampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
			clearstatcache();

			if($attachinfo['mime'] == 'image/jpeg') {
				$imagefunc($thumb_photo, null, 90);
			} else {
				$imagefunc($thumb_photo);
			}
		}
	} else {
		readfile($targetfile);
		exit;
	}
}

?>