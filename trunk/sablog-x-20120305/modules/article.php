<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台文章列表模块
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

$pagefile = 'article';
$indexpage = 1;
$pagenum = (int)$options['article_shownum'];
$timeformat = $options['article_timeformat'];

$query_sql = "SELECT a.articleid,a.uid,a.stick,a.dateline,a.title,a.description,a.content,a.views,a.comments,a.attachments,a.readpassword,a.alias,u.username,u.email
	FROM {$db_prefix}articles a
	LEFT JOIN {$db_prefix}users u ON u.userid=a.uid
	WHERE a.visible='1'";

$extra = '';
if($page) {
	$start_limit = ($page - 1) * $pagenum;
} else {
	$start_limit = 0;
	$page = 1;
}

$user = sax_addslashes($_GET['user']);
$uid = (int)$_GET['uid'];
$tag = sax_addslashes($_GET['tag']);
$searchid = (int)$_GET['searchid'];

// 查看用户发表的文章
if ($user || $uid) {
	if ($user) {
		$usr = $DB->fetch_one_array("SELECT userid, username, articles FROM {$db_prefix}users WHERE username='$user' LIMIT 1");
		$pageurl = getuserlink($user);
	} else {
		$usr = $DB->fetch_one_array("SELECT userid, username, articles FROM {$db_prefix}users WHERE userid='$uid'");
		$pageurl = getuserlink($uid, false);
	}
	if (!$usr) {
		message('用户不存在', './');
	}
	$total = $usr['articles'];
	$query_sql .= " AND a.uid='".intval($usr['userid'])."' ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
	$navtext = $usr['username'].' 发表的文章';
	$options['title'] = settitle($navtext);
	$indexpage = 0;
// 查看tags的相关文章
} elseif ($tag) {
	$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='tag' AND name='$tag' LIMIT 1");
	if (!$r) {
		message('记录不存在.', './');
	}
	$aids = get_cids($r['mid']);
	$total = $r['count'];
	if ($total && $aids) {
		$query_sql .= " AND a.articleid IN ($aids) ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
	}
	$pageurl = gettaglink($r['slug']);
	$navtext = $r['name'];
	$options['title'] = settitle($r['name']);
	$indexpage = 0;
// 查看搜索结果的文章
} elseif ($searchid) {
	$search = $DB->fetch_one_array("SELECT * FROM {$db_prefix}searchindex WHERE searchid='".$searchid."' AND expiration>'$timestamp'");
	if (!$search) {
		$DB->unbuffered_query("DELETE FROM {$db_prefix}searchindex WHERE expiration < '$timestamp'");
		message('您指定的搜索不存在或已过期,请返回.', './');
	}
	$total = $search['totals'];
	$query_sql .= " AND a.articleid IN (".$search['ids'].") ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
	
	$pageurl = getsearchlink($searchid);
	$navtext = '搜索“<strong>'.$search['keywords'].'</strong>”的结果';
	$indexpage = 0;
// 查看首页文章
} else {
	if ($options['permalink']) {
		$pageurl = $options['url'].'page/';
	} else {
		$pageurl = $options['url'].'?action=article';
	}

	$navtext = '';
	$total = $stats['article_count'];

	// 检查是否设置分类参数
	$cid = (int)$_GET['cid'];
	$curl = sax_addslashes($_GET['curl']);
	if ($cid || $curl) {
		if ($cid) {
			$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='category' AND mid='$cid'");
		} else {
			$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='category' AND slug='$curl' LIMIT 1");
		}
		if (!$r) {
			message('记录不存在.', './');
		}
		$aids = get_cids($r['mid']);
		$query_sql .= " AND a.articleid IN ($aids)";
		$navtext = $r['name'];
		$total = $r['count'];
		$pageurl = getcatelink($cid, $r['slug']);
		$options['title'] = settitle($r['name']);
		$indexpage = 0;
	}

	//不用再计算记录数量直接从缓存读取
	$getnum = false;
	// 检查是否设置$setdate参数
	if ($setdate && getstrlen($setdate) == 6) {
		$extra = 'page/';
		$navtext = $setyear.'年'.$setmonth.'月的文章';
		$pageurl = getdatelink($setdate);
		if ($archivesdb[$setdate]) {
			$total = (int)$archivesdb[$setdate];
		} else {
			$getnum = true;
		}
		// 检查是否设置$setday参数
		$setday = (int)$_GET['setday'];
		if ($setday && is_numeric($setday)) {
			$getnum = true;
			if ($setday > 31 || $setday < 1) {
				$setday = sadate('d');
			}
			$navtext = $setyear.'年'.$setmonth.'月'.$setday.'日的文章';
			$start = strtotime($setyear.'-'.$setmonth.'-'.$setday);
			$end = $start + 86400;
			$pageurl = getdaylink($setdate, $setday);
		}
		$options['title'] = settitle($navtext);
		$indexpage = 0;
	}
	//*******************************//
	$startadd = $start ? " AND a.dateline >= '".correcttime($start)."' " : '';
	$endadd   = $end ? " AND a.dateline < '".correcttime($end)."' " : '';
	//*******************************//
	if($getnum) {
		$query = $DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles a WHERE a.visible='1' ".$startadd.$endadd);
		$total = $DB->result($query, 0);
	}
	
	//置顶文章
	if (!$startadd && !$endadd && !$r['cid'] && !$stickycount) {
		$stickycount = $stickids['count'];		
		$stickyaids = $stickids['aids'];
	}

	if(($start_limit && $start_limit > $stickycount) || !$stickycount) {
		$query_sql .= $startadd.$endadd.((!$startadd && !$endadd) ? " AND a.stick='0'" : '')." ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
	} else {
		$querystick = $DB->query("SELECT articleid, dateline, title, views, comments, alias, stick FROM {$db_prefix}articles WHERE articleid IN ($stickyaids) AND visible='1' ORDER BY dateline DESC LIMIT $start_limit, ".($stickycount - $start_limit < $pagenum ? $stickycount - $start_limit : $pagenum));
		/*	
		if($pagenum - $stickycount + $start_limit > 0) {
			$query_sql .= $startadd.$endadd." AND a.articleid NOT IN ($stickyaids) ORDER BY a.dateline DESC LIMIT ".($pagenum - $stickycount + $start_limit);
		} else {
			$query_sql .= $startadd.$endadd." AND a.articleid NOT IN ($stickyaids) ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
		}
		这段是根据置顶判断第一页显示多少条
		*/
		$query_sql .= $startadd.$endadd." AND a.articleid NOT IN ($stickyaids) ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
	}

}

$options['meta_keywords'] = $navtext;
if (!$options['meta_keywords']) {
	$tmp = $comma = '';
	if (is_array($catecache) && count($catecache)){
		foreach($catecache as $data) {
			$tmp .= $comma.$data['name'];
			$comma = ',';
		}
		$options['meta_keywords'] = $tmp;
	}
}
$options['meta_description'] = $options['meta_description'] ? $options['meta_description'] : $options['description'];
$options['title_keywords'] = $options['title_keywords'] ? ' - '.$options['title_keywords'] : '';

// 执行查询
if ($total) {
	$query = $DB->query($query_sql);
	$multipage = multi($total, $pagenum, $page, $pageurl, $extra, $maxpages);
	$articledb = array();
	$haveattach = 0;
	$aids = $comma = '';
	while (($querystick && $article = $DB->fetch_array($querystick)) || ($query && $article = $DB->fetch_array($query))) {
		$aids .= $comma.$article['articleid'];
		$comma = ',';

		$article['avatardb'] = get_avatar($article['email']);
		$article['url'] = getpermalink($article['articleid'], $article['alias']);
		$article['userurl'] = getuserlink($article['username']);
		$article['cateurl'] = getcatelink($article['cid'], $article['curl']);

		//隐藏变量,默认模板用不着,方便那些做模板可以单独显示月份和号数的的朋友.
		$article['month'] = sadate('M', $article['dateline']);
		$article['day'] = sadate('d', $article['dateline']);

		$article['dateline'] = sadate($timeformat, $article['dateline'], 1);

		if ($article['readpassword'] && ($_COOKIE['readpassword_'.$article['articleid']] != $article['readpassword']) && $sax_group != 1 && $sax_group != 2) {
			$article['allowread'] = 0;
		} else {
			$article['allowread'] = 1;
			if ($article['attachments']) {
				$haveattach = 1;
			}
			if ($article['description']) {
				$article['content'] = $article['description'];
			}
		}

		$articledb[$article['articleid']] = $article;
	}
	unset($article);
	$DB->free_result($query);

	//设置一个时间戳,一定时间内该时间戳有效.用于COOKIE防盗链
	scookie('viewarticle', $timestamp);

	$metadb = array();
	if ($aids) {
		$query = $DB->query("SELECT m.mid, m.name, m.slug, m.type, r.cid FROM {$db_prefix}metas m
			INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
			WHERE m.type IN ('category', 'tag') AND r.cid IN ($aids)
			ORDER BY m.displayorder ASC, m.mid DESC");
		while ($meta = $DB->fetch_array($query)) {
			if ($meta['type'] == 'tag') {
				$meta['url'] = gettaglink($meta['slug']);
				$articledb[$meta['cid']]['content'] = highlight_tag($articledb[$meta['cid']]['content'], $meta['name']);
			} else {
				$meta['url'] = getcatelink($meta['mid'], $meta['slug']);
			}
			$metadb[$meta['cid']][$meta['type']][] = $meta;
		}
		unset($meta);
		$DB->free_result($query);

		if ($haveattach) {
			require_once(SABLOG_ROOT.'include/func/attachment.func.php');
			$attachdb = array();
			$query = $DB->query("SELECT attachmentid, articleid, dateline, filename, filetype, filesize, downloads, filepath, thumb_filepath, thumb_width, thumb_height, isimage FROM {$db_prefix}attachments WHERE articleid IN ($aids) ORDER BY attachmentid");
			$size = explode('x', strtolower($options['attachments_thumbs_size']));
			while ($attach = $DB->fetch_array($query)) {
				$attach['filesize'] = sizecount($attach['filesize']);
				$attach['dateline'] = sadate('Y-m-d H:i', $attach['dateline']);
				$attach['filepath'] = $options['attachments_dir'].$attach['filepath'];
				$attach['thumbs'] = 0;
				if ($attach['isimage']) {
					if ($attach['thumb_filepath'] && $options['attachments_thumbs'] && file_exists(SABLOG_ROOT.$options['attachments_dir'].$attach['thumb_filepath'])) {
						$attach['thumbs'] = 1;
						$attach['thumb_filepath'] = $options['attachments_dir'].$attach['thumb_filepath'];
					} else {
						$imagesize = @getimagesize(SABLOG_ROOT.$attach['filepath']);
						$im = scale_image( array(
							'max_width'  => $size[0],
							'max_height' => $size[1],
							'cur_width'  => $imagesize[0],
							'cur_height' => $imagesize[1]
						));
						$attach['thumb_width'] = $im['img_width'];
						$attach['thumb_height'] = $im['img_height'];
					}
					$articledb[$attach['articleid']]['image'][$attach['attachmentid']] = $attach;
				} else {
					$articledb[$attach['articleid']]['file'][$attach['attachmentid']] = $attach;
				}
				//插入附件到文章中用的
				$attachdb[$attach['attachmentid']] = $attach;
			}
			unset($attach);
			$DB->free_result($query);

			$attachmentids = array();
			$aids = explode(',', $aids);
			foreach ($aids as $articleid) {
				$articledb[$articleid]['content'] = preg_replace("/\[attach=(\d+)\]/ie", "upload('\\1')", $articledb[$articleid]['content']);
			}
			unset($attachdb);
			if ($attachmentids && is_array($attachmentids)) {
				foreach($attachmentids as $attachid => $articleid){
					if($articledb[$articleid]['image'][$attachid]){
						unset($articledb[$articleid]['image'][$attachid]);
					}
					if($articledb[$articleid]['file'][$attachid]){
						unset($articledb[$articleid]['file'][$attachid]);
					}
				}
			}
		}
	}
}

?>