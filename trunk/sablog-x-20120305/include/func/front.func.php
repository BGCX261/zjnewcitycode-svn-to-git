<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台公共函数
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

// 日历获取日历部分
function calendar($y,$m){
	global $DB,$db_prefix,$options,$timestamp,$timeoffset;
	!$y && $y = sadate('Y');
	!$m && $m = sadate('m');

	//当前月等于1
	if ($m == 1) {
		$lastyear = $y-1;
		$lastmonth = 12;
		$nextmonth = $m+1;
		$nextyear = $y;
	} elseif ($m == 12) {
		$lastyear = $y;
		$lastmonth = $m - 1;
		$nextyear = $y + 1;
		$nextmonth = 1;
	} else {
		$lastmonth = $m - 1;
		$nextmonth = $m + 1;
		$lastyear = $nextyear = $y;
	}
	if ($nextmonth < 10) $nextmonth = '0'.$nextmonth;
	if ($lastmonth < 10) $lastmonth = '0'.$lastmonth;

	$weekday   = sadate('w',mktime(0,0,0,$m,1,$y));
	$totalday  = sadate('t',mktime(0,0,0,$m,1,$y));
	list($start, $end) = explode('-', gettimestamp($y,$m));
	// 动态缓存
	$expiration	= 0;
	$cachefile = SABLOG_ROOT.'data/cache/cache_calendar.php';

	if (($m != sadate('m')) || ($y != sadate('Y')) || (!@include($cachefile)) || $expiration < $timestamp) {
		$query = $DB->query("SELECT dateline FROM {$db_prefix}articles WHERE visible='1' AND dateline >= '".correcttime($start)."' AND dateline < '".correcttime($end)."'");
		$datelines = array();
		$articledb = array();
		while($article = $DB->fetch_array($query)) {
			$datelines[] = sadate('Y-m-j',$article['dateline']);
			$day = sadate('j', $article['dateline']);
			if (!isset($articledb[$day])) {
				$articledb[$day]['num'] = 1;
			} else {
				$articledb[$day]['num']++;
			}
		}
		$br = 0;
		$ret['html'] = "<tr>\n";
		for ($i=1; $i<=$weekday; $i++) {
			$ret['html'] .= "<td class=\"cal_day1\"></td>\n";
			$br++;
		}

		for($i=1; $i<=$totalday; $i++){
			$br++;
			if (in_array($y.'-'.$m.'-'.$i, $datelines)) {
				
				$td = '<a title="'.$i.'日内发表了'.$articledb[$i]['num'].'篇文章" href="'.getdaylink($y.$m, $i).'">'.$i.'</a>';
			} else{
				$td = $i;
			}
			if ($i == sadate('d') && $m == sadate('m') && $y == sadate('Y')) {
				$class = 'cal_day2';
			} else {
				$class = 'cal_day1';
			}
			$ret['html'] .= "<td class=\"".$class."\">".$td."</td>\n";
			if ($br >= 7) {
				$ret['html'] .= "</tr>\n<tr>\n";
				$br = 0;
			}
		}
		if ($br != 0) {
			for($i=$br; $i<7;$i++){
				 $ret['html'] .= "<td class=\"cal_day1\"></td>\n";
			}
		}
		$ret['html'] .= "</tr>\n";
		if ($y.$m == sadate('Ym')) {
			$cachedata = "<?php\r\nif(!defined('SABLOG_ROOT')) exit('Access Denied');\r\n\$expiration='".($timestamp + 300)."';\r\n\$ret = unserialize('".addcslashes(serialize($ret), '\\\'')."');\r\n?>";
			if(!writefile($cachefile, $cachedata)) {
				exit('Can not write to calendar cache files, please check directory ./data/cache/ .');
			}
		}
	}
	$ret['prevmonth'] = $lastyear.$lastmonth;
	$ret['nextmonth'] = $nextyear.$nextmonth;
	$ret['cur_month'] = $m;
	$e_month = ($m < 10) ? str_replace('0', '', $m) : $m;
	$ret['cur_date'] = $y.'年'.$m.'月';
	return $ret;
}

// 高亮标签
function highlight_tag($content, $tag) {
	global $options;
	$tag = trim($tag);
	if(preg_match('/<a[^>]+?'.preg_quote($tag).'[^>]+?>/i',$content)) return $content;
	if(preg_match('/<img[^>]+?'.preg_quote($tag).'[^>]+?>/i',$content)) return $content;

	//有次数的替换
	$content = preg_replace('/'.$tag.'/i','<a href="'.gettaglink(urlencode($tag)).'" onclick="javascript:tagshow(\''.$tag.'\');return false;">'.htmlspecialchars($tag).'</a>', $content, 1);
	/*
	替换所有
	if(function_exists('eregi_replace')) {	
		$content = eregi_replace($tag, '<a href="'.$tagurl.'" onclick="tagshow(\''.$tag.'\');return false;" class="tagshow">'.htmlspecialchars($tag).'</a>', $content);	
	} else {
		$content = str_replace($tag, '<a href="'.$tagurl.'" onclick="tagshow(\''.$tag.'\');return false;" class="tagshow">'.htmlspecialchars($tag).'</a>', $content);	
	}
	*/
	return $content;
}

// 设置title
function settitle($string) {
	global $options;
	$strings = $comma = '';
	if (is_array($string) && count($string)){
		foreach($string as $value) {
			$strings .= $comma.','.htmlspecialchars($value);
			$comma = ',';
		}
		$string = $strings.' - '.$options['title'];
	} else {
		$string = htmlspecialchars($string).' - '.$options['title'];
	}
	return $string;
}

// 分页函数
function multi($num, $perpage, $curpage, $mpurl, $extra='', $maxpages = 1000) {
	global $options;
	$multipage = '';	
	if (substr($mpurl, 0, 7) != 'http://') {
		$mpurl = $options['url'].$mpurl;
	}
	$realpages = 1;
	if($num > $perpage) {
		$page = 7;
		$offset = 3;
		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
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

		if ($options['permalink']) {
			$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'" class="p_redirect">&laquo; First</a>' : '').($curpage > 1 ? '<a href="'.$mpurl.$extra.($curpage - 1).'/" class="p_redirect">&#8249; Prev</a>' : '');
			for($i = $from; $i <= $to; $i++) {
				$multipage .= $i == $curpage ? '<span class="p_curpage">'.$i.'</span>' : '<a href="'.$mpurl.$extra.$i.'/" class="p_num">'.$i.'</a>';
			}
			$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.$extra.($curpage + 1).'/" class="p_redirect">Next &#8250;</a>' : '').($to < $pages ? '<a href="'.$mpurl.$extra.$pages.'/" class="p_redirect">Last &raquo;</a>' : '');
		} else {
			$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
			$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1" class="p_redirect">&laquo; First</a>' : '').($curpage > 1 ? '<a href="'.$mpurl.'page='.($curpage - 1).'" class="p_redirect">&#8249; Prev</a>' : '');
			for($i = $from; $i <= $to; $i++) {
				$multipage .= $i == $curpage ? '<span class="p_curpage">'.$i.'</span>' : '<a href="'.$mpurl.'page='.$i.'" class="p_num">'.$i.'</a>';
			}
			$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.'page='.($curpage + 1).'" class="p_redirect">Next &#8250;</a>' : '').($to < $pages ? '<a href="'.$mpurl.'page='.$pages.'" class="p_redirect">Last &raquo;</a>' : '');
		}

		//$multipage = $multipage ? '<div class="p_bar"><span class="p_info">Total: '.$num.'</span><span class="p_info">Page '.$curpage.' of '.$pages.'</span>'.$multipage.'</div>' : '';
		$multipage = $multipage ? '<div class="p_bar"><span class="p_info">Total: '.$num.'</span>'.$multipage.'</div>' : '';
	}
	return $multipage;
}

// 获取页面调试信息
function footer() {
	global $DB, $starttime, $options, $stylevar, $SABLOG_VERSION, $SABLOG_RELEASE;
	$mtime = explode(' ', microtime());
	$totaltime = number_format(($mtime[1] + $mtime[0] - $starttime), 6);
	//updatesession();
	$sa_debug = 'Processed in '.$totaltime.' second(s), '.$DB->querycount.' queries, Gzip '.($options['gzipcompress'] ? 'enabled' : 'disabled');
	include template('footer');
	PageEnd();
}

// 消息显示页面
function message($msg,$returnurl='javascript:history.go(-1);',$min='3') {
	global $options, $stylevar;
	include template('message');
	PageEnd();
}

function template($file) {
	global $options, $tplrefresh;
	$tplfile = SABLOG_ROOT.'templates/'.$options['templatename'].'/'.$file.'.php';
	$objfile = SABLOG_ROOT.'data/template/'.$options['templatename'].'_'.$file.'.tpl.php';
	if ($tplrefresh && @filemtime($tplfile) > @filemtime($objfile)) {
		if (!file_exists($tplfile)) {
			$tplfile = SABLOG_ROOT.'templates/default/'.$file.'.php';
		}
		require_once SABLOG_ROOT.'include/func/template.func.php';
		parse_template($tplfile, $objfile);
	}
	return $objfile;
}

function loadmodule($name) {
	$error = MODULE_DIR.'index.php';
	$module = MODULE_DIR.$name.'.php';
	if (file_exists($module) && strpos($name,'.')===false && strpos($name,'/')===false) {
		return $module;
	} else {
		return $error;
	}
}

function checkr_ua() {
	if (!$_SERVER['HTTP_REFERER'] && !$_SERVER['HTTP_USER_AGENT']) {
		return false;
	} else {
		return true;
	}
}

function get_rand_article() {
	global $DB,$db_prefix,$options,$allarticleids;
	$articledb = array();
	$options['randarticle_num'] = (int)$options['randarticle_num'];
	if ($options['randarticle_num'] && $allarticleids) {
		$idnum = count($allarticleids);
		if ($options['randarticle_num'] > $idnum) {
			$options['randarticle_num'] = $idnum;
		}
		$allarticleids = array_rand($allarticleids, $options['randarticle_num']);
		$query = $DB->query("SELECT articleid, title, alias FROM {$db_prefix}articles WHERE visible='1' AND articleid IN (".implode(',', array_values($allarticleids)).")");
		while($article = $DB->fetch_array($query)) {
			$article['url'] = getpermalink($article['articleid'], $article['alias']);
			$articledb[$article['articleid']] = $article;
		}
	}
	return $articledb;
}

function blog_comments($commentdb, $commentStacks, $multipage){
	global $options, $article, $css;
    if ($commentStacks) {
		foreach ($commentStacks as $commentid) {
			$comment = $commentdb[$commentid];
			$css = ($css == 'rowa' ? 'rowb' : 'rowa');
	?>
	<div class="commentnum">#<?php echo $comment['cmtorderid'];?></div>
	<div class="comment <?php echo $css; ?>" id="comment-<?php echo $commentid;?>">
		<a name="cm<?php echo $commentid;?>"></a>
		<div class="comment_data">
			<a class="reply" href="javascript:void(0);" onclick="commentReply(<?php echo $commentid;?>,this)">回复</a>
			<?php if ($options['show_avatar'] && $comment['avatardb']) {?>
				<img alt="" class="avatar" src="<?php echo $comment['avatardb']['src'];?>" width="<?php echo $comment['avatardb']['size'];?>" height="<?php echo $comment['avatardb']['size'];?>" />
			<?php }?>
			<span class="author">
				<?php
				if ($comment['url']) {
				?>
					<a href="<?php echo $comment['url'];?>" rel="external nofollow" target="_blank"><?php echo $comment['author'];?></a>
				<?php
				} else {
					echo $comment['author'];
				}
				?>
			</span>
			<span><?php echo $comment['dateline'];?></span>
		</div>
		<div class="cmcontent" id="comm_<?php echo $commentid;?>"><?php echo $comment['content'];?></div>
		<?php blog_comments_children($commentdb, $comment['children']); ?>
	</div>
	<?php
		}
		if ($multipage) {
			echo $multipage;
		}
	}
}

//blog：博客子评论列表
function blog_comments_children($commentdb, $children){
	global $options, $article, $css;
	if ($children && is_array($children)) {
		foreach ($children as $child) {
			$comment = $commentdb[$child];
			$css = ($css == 'rowa' ? 'rowb' : 'rowa');
		?>
	<div class="comment comment-children <?php echo $css; ?>" id="comment-<?php echo $child;?>">
		<a name="cm<?php echo $child;?>"></a>
		<div class="comment_data">
			<?php if($comment['level'] < 5) { ?><a class="reply" href="javascript:void(0);" onclick="commentReply(<?php echo $child;?>,this)">回复</a><?php }; ?>
			<?php if ($options['show_avatar'] && $comment['avatardb']) {?>
				<img alt="" class="avatar" src="<?php echo $comment['avatardb']['src'];?>" width="<?php echo $comment['avatardb']['size'];?>" height="<?php echo $comment['avatardb']['size'];?>" />
			<?php }?>
			<span class="author">
				<?php
				if ($comment['url']) {
				?>
					<a href="<?php echo $comment['url'];?>" rel="external nofollow" target="_blank"><?php echo $comment['author'];?></a>
				<?php
				} else {
					echo $comment['author'];
				}
				?>
			</span>
			<span><?php echo $comment['dateline'];?></span>
		</div>
		<div class="cmcontent" id="comm_<?php echo $child;?>"><?php echo $comment['content'];?></div>
		<?php blog_comments_children($commentdb, $comment['children']); ?>
	</div>
		<?php
		}
	}
}

function get_comment_parent($commentid){
	global $DB, $db_prefix;
	$parent = $DB->result($DB->query("SELECT comment_parent FROM {$db_prefix}comments WHERE commentid='$commentid'"), 0);
	if ( $parent != 0 ) {
		return get_comment_parent( $parent );
	} else {
		return $commentid;
	}
}

?>