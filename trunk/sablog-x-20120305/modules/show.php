<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台文章显示模块
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

$articleid = (int)$_GET['id'];
$alias = sax_addslashes($_GET['alias']);
$where = '';
if ($articleid) {
	$where = " AND articleid='$articleid'";
} elseif ($alias) {
	$where = " AND alias='$alias' LIMIT 1";
} else {
	message('缺少参数', $options['url']);
}

// 获取文章信息
$article = $DB->fetch_one_array("SELECT a.*, u.username, u.email
	FROM {$db_prefix}articles a
	LEFT JOIN {$db_prefix}users u ON a.uid=u.userid
	WHERE a.visible='1'".$where);
if (!$article) {
	message('记录不存在', $options['url']);
}

$goto = $_GET['goto'];
//显示上下篇文章标题
if ($options['show_n_p_title']) {
	///////////////////////////////////
	$next = $DB->fetch_one_array("SELECT articleid,alias,title FROM {$db_prefix}articles WHERE dateline > '".$article['dateline']."' AND visible='1' ORDER BY dateline ASC LIMIT 1");
	if($next) {
		$article['next_url'] = redirect_permalink($next['articleid'], $next['alias']);
		$article['next_id'] = $next['articleid'];
		$article['next_title'] = $next['title'];
	}
	
	$previous = $DB->fetch_one_array("SELECT articleid,alias,title FROM {$db_prefix}articles WHERE dateline < '".$article['dateline']."' AND visible='1' ORDER BY dateline DESC LIMIT 1");
	if($previous) {
		$article['previous_url'] = redirect_permalink($previous['articleid'], $previous['alias']);
		$article['previous_id'] = $previous['articleid'];
		$article['previous_title'] = $previous['title'];
	}
}

// 跳转
if ($goto == 'next') {
	//跳转到下一篇文章
	$row = $DB->fetch_one_array("SELECT articleid,alias FROM {$db_prefix}articles WHERE dateline > '".$article['dateline']."' AND visible='1' ORDER BY dateline ASC LIMIT 1");
	if($row) {
		if ($options['showmsg']) {
			message('正在读取.请稍侯.', redirect_permalink($row['articleid'], $row['alias']));
		} else {
			@header('Location: '.redirect_permalink($row['articleid'], $row['alias']));
			exit;
		}
	} else {
		message('没有比当前更新的文章', redirect_permalink($article['articleid'], $row['alias']));
	}
} elseif ($goto == 'previous') {
	//跳转到上一篇文章
	$row = $DB->fetch_one_array("SELECT articleid,alias FROM {$db_prefix}articles WHERE dateline < '".$article['dateline']."' AND visible='1' ORDER BY dateline DESC LIMIT 1");
	if($row) {
		if ($options['showmsg']) {
			message('正在读取.请稍侯.', redirect_permalink($row['articleid'], $row['alias']));
		} else {
			@header('Location: '.redirect_permalink($row['articleid'], $row['alias']));
			exit;
		}
	} else {
		message('没有比当前更早的文章', redirect_permalink($article['articleid'], $row['alias']));
	}
}
//跳转结束

//记录浏览过的文章
if(!$_COOKIE['articleids'][$article['articleid']]){
	scookie('articleids['.$article['articleid'].']', $article['articleid']);
}

$article['avatardb'] = get_avatar($article['email']);

//设置连接
$article['url'] = getpermalink($article['articleid'], $article['alias']);
$article['userurl'] = getuserlink($article['username']);

//隐藏变量,默认模板用不着,方便那些做模板可以单独显示月份和号数的的朋友.
$article['month'] = sadate('M', $article['dateline']);
$article['day'] = sadate('d', $article['dateline']);
$article['dateline'] = sadate($options['article_timeformat'], $article['dateline'],1);

// 获取文章的关联信息
$metadb = array();
$query = $DB->query("SELECT m.mid, m.name, m.slug, m.type, r.cid FROM {$db_prefix}metas m
	INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
	WHERE m.type IN ('category', 'tag') AND r.cid='".$article['articleid']."'
	ORDER BY m.displayorder ASC, m.mid DESC");
$article['keywords'] = $comma = '';
while ($meta = $DB->fetch_array($query)) {
	if ($meta['type'] == 'tag') {
		$meta['url'] = gettaglink($meta['slug']);
		$article['content'] = highlight_tag($article['content'], $meta['name']);
	} else {
		$meta['url'] = getcatelink($meta['mid'], $meta['slug']);
	}
	$article['keywords'] .= $comma.$meta['name'];
	$metadb[$article['articleid']][$meta['type']][] = $meta;
	$comma = ',';
}
$DB->free_result($query);

if ($_POST['readpassword'] && ($article['readpassword'] == sax_addslashes($_POST['readpassword']))) {
	scookie('readpassword_'.$article['articleid'], sax_addslashes($_POST['readpassword']), 2592000); //一个月
}

//设置文章的分类名、作者、TAG、标题成为meta\title信息
if (!$article['keywords']) {
	$tmp = $comma = '';
	if (is_array($catecache) && count($catecache)){
		foreach($catecache as $data) {
			$tmp .= $comma.$data['name'];
			$comma = ',';
		}
		$options['meta_keywords'] = $tmp;
	} else {
		$options['meta_keywords'] = '';
	}
} else {
	$options['meta_keywords'] = $article['keywords'];
}
$options['meta_description'] = $article['title'] . ($options['meta_description'] ? ' - '.$options['meta_description'] : '');
$options['title_keywords'] = $options['title_keywords'] ? ' - '.$options['title_keywords'] : '';



if ($article['readpassword'] && ($_COOKIE['readpassword_'.$article['articleid']] != $article['readpassword']) && $sax_group != 1 && $sax_group != 2) {
	$article['allowread'] = 0;
} else {
	$article['allowread'] = 1;
	$DB->unbuffered_query("UPDATE {$db_prefix}articles SET views=views+1 WHERE articleid='".$article['articleid']."'");

	//一篇文章的每页评论显示数量
	$article_comment_num = (int)$options['article_comment_num'];

	//附件
	if ($article['attachments']) {
		require_once(SABLOG_ROOT.'include/func/attachment.func.php');		
		$attachdb = array();
		$query = $DB->query("SELECT attachmentid, dateline, filename, filetype, filesize, downloads, filepath, thumb_filepath, thumb_width, thumb_height, isimage FROM {$db_prefix}attachments WHERE articleid='".$article['articleid']."' ORDER BY attachmentid");
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
				$article['image'][$attach['attachmentid']] = $attach;
			} else {
				$article['file'][$attach['attachmentid']] = $attach;
			}
			//插入附件到文章中用的
			$attachdb[$attach['attachmentid']] = $attach;
		}
		unset($attach);
		$DB->free_result($query);
		
		$attachmentids = array();
		$article['content'] = preg_replace("/\[attach=(\d+)\]/ie", "upload('\\1')", $article['content']);
		unset($attachdb);
		if ($attachmentids && is_array($attachmentids)) {
			foreach($attachmentids as $attachid => $articleid){
				if($article['image'][$attachid]){
					unset($article['image'][$attachid]);
				}
				if($article['file'][$attachid]){
					unset($article['file'][$attachid]);
				}
			}
		}
	}
	// 获取附件结束

	// 显示相关文章
	if ($options['related_shownum'] && $metadb[$article['articleid']]['tag']) {
		$mids = $comma = '';
		foreach($metadb[$article['articleid']]['tag'] as $tag) {
			$mids .= $comma.$tag['mid'];
			$comma = ',';
		}
		if ($mids) {
			$query = $DB->query("SELECT cid FROM {$db_prefix}relationships WHERE mid IN ($mids)");
			$relaids = $comma = '';
			while ($meta = $DB->fetch_array($query)) {
				$relaids .= $comma.$meta['cid'];
				$comma = ',';
			}
			unset($meta);
			$relids = explode(',', $relaids);
			// 清除重复值的单元并删除当前ID
			$relids = array_unique($relids);
			$relids = array_flip($relids);
			unset($relids[$article['articleid']]);
			$relids = array_flip($relids);
			////////
			$related_total = count($relids);
			$relids = implode(',',$relids);
			if ($related_total > 1 && $relids != $article['articleid']) {
				$query = $DB->query("SELECT articleid,title,alias,comments FROM {$db_prefix}articles WHERE visible='1' AND articleid IN ($relids) ORDER BY dateline DESC LIMIT ".intval($options['related_shownum']));
				$titledb = array();
				while ($title = $DB->fetch_array($query)) {
					$title['url'] = getpermalink($title['articleid'], $title['alias']);
					$title['title'] = trimmed_title($title['title'], $options['related_title_limit']);
					$titledb[$title['articleid']] = $title;
				}
				unset($title);
				$DB->free_result($query);
			}
		}
	}

	// 评论
	if ($article['comments']) {
		$commentsql = '';
		/*
		改成评论嵌套就不用LIMIT了。直接查询全部，再用array_slice分割数组分页。
		if($article_comment_num) {
			if($page) {
				$cmtorderid = ($page - 1) * $article_comment_num;
				$start_limit = ($page - 1) * $article_comment_num;
			} else {
				$cmtorderid = 0;
				$start_limit = 0;
				$page = 1;
			}
			$multipage = multi($article['comments'], $article_comment_num, $page, $article['url']);
			//$commentsql = " LIMIT $start_limit, $article_comment_num";
		}
		*/
		$cmtorderid = 0;
		$cmtorder = $options['comment_order'] ? 'ASC' : 'DESC';
		$query = $DB->query("SELECT commentid,comment_parent, author,email,url,dateline,content FROM {$db_prefix}comments WHERE articleid='".$article['articleid']."' AND visible='1' ORDER BY dateline $cmtorder $commentsql");
		$commentdb=array();
		while ($comment=$DB->fetch_array($query)) {
			$comment['quoteuser'] = $comment['author'];
			$comment['avatardb'] = get_avatar($comment['email']);
			$comment['content'] = html_clean($comment['content']);
			$comment['dateline'] = sadate($options['comment_timeformat'], $comment['dateline'],1);
			$comment['children'] = array();
			$comment['level'] = isset($commentdb[$comment['comment_parent']]) ? $commentdb[$comment['comment_parent']]['level'] + 1 : 0;
			if (!$comment['comment_parent']) {
				$cmtorderid++;
			}
			$comment['cmtorderid'] = $cmtorderid;
			$commentdb[$comment['commentid']]=$comment;
		}
		unset($comment);
		$DB->free_result($query);
		

		$commentStacks = array();
		$multipage = '';
		foreach($commentdb as $commentid => $comment) {
			$comment_parent = $comment['comment_parent'];
			if($comment_parent == 0) $commentStacks[] = $commentid;
			if($comment_parent != 0 && isset($commentdb[$comment_parent])) {
				if($commentdb[$commentid]['level'] > 5) {
					$commentdb[$commentid]['comment_parent'] = $comment_parent = $commentdb[$comment_parent]['comment_parent'];
				}
				$commentdb[$comment_parent]['children'][] = $commentid;
			}
		}
		if(!$options['comment_order']) {
			$commentdb = array_reverse($commentdb, true);
			$commentStacks = array_reverse($commentStacks);
		}
		if($article_comment_num) {
			//翻页的话如果请求页面比最大页大，则取最大页。否则会取不到评论内容
			$all_comment_num = count($commentStacks);
			$maxpage = @ceil($all_comment_num / $article_comment_num);
			if ($page > $maxpage) {
				$page = $maxpage;
			}
			$multipage = multi($all_comment_num, $article_comment_num, $page, $article['url']);
			$commentStacks = array_slice($commentStacks, ($page - 1) * $article_comment_num, $article_comment_num);
		}
		//$commentdb = compact('commentdb','commentStacks','multipage');
	}
	if ($options['seccode'] && $sax_group != 1 && $sax_group !=2) {
		$seccode = random(6, 1);
	}
}

$options['title'] = settitle($article['title']);
$pagefile = 'show';

?>