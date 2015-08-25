<?php
// ========================== 文件说明 ==========================//
// 本文件说明：文章管理
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

permission(array(1,2));

// 加载附件相关函数
require_once(SABLOG_ROOT.'include/func/attachment.func.php');
$max_upload_size = max_upload_size();
$max_upload_size_unit = sizecount($max_upload_size);

$article = array();
if ($articleid) {
	$article = $DB->fetch_one_array("SELECT a.*,u.username FROM {$db_prefix}articles a
		LEFT JOIN {$db_prefix}users u ON u.userid=a.uid
		WHERE articleid='$articleid'");
	if (!$article) {
		redirect('日志不存在');
	}
}

if ($message) {
	$messages = array(
		1 => '标题不能为空并且不能超过120个字符<br />',
		2 => '你还没有选择分类<br />',
		3 => '内容不能为空并且不能少于4个字符<br />',
		4 => '关键词不能超过10个<br />',
		5 => '每个关键字不能超过30个字符<br />',
		6 => '自定义URL只允许大小写字母、数字、下划线和减号<br />',
		7 => '自定义URL名称已经存在<br />',
		8 => '数据库中已存在一样的标题了,建议您换一个.<br />',
		9 => '添加日志成功',
		10 => sprintf('添加日志成功, <a href="'.$options['url'].'index.php?action=show&amp;id=%d" target="_blank">查看刚才发布的文章</a>', $articleid),
		11 => '您不能修改或删除不是您写的日志',
		12 => '修改日志成功',
		13 => sprintf('修改日志成功, <a href="'.$options['url'].'index.php?action=show&amp;id=%d" target="_blank">查看刚才修改的文章</a>', $articleid),
		14 => '日志不存在',
		15 => '删除日志成功',
		16 => '已经把《'.$article['title'].'》设置为隐藏状态',
		17 => '已经把《'.$article['title'].'》设置为显示状态',
		18 => '所选项目已隐藏',
		19 => '所选项目已显示',
		20 => '所选项目已置顶',
		21 => '所选项目已取消置顶',
		22 => '没有选择具体操作',
		23 => '未选择任何项目',
	);
}

$uquery = '';
if ($sax_group != 1) {
	$uquery = " AND uid='$sax_uid'";
}

$mid = (int)$mid;
!$action && $action = 'list';
$location = '';

$catedb = array();
$query = $DB->query("SELECT mid,name FROM {$db_prefix}metas WHERE type='category' ORDER BY displayorder");
while ($cate = $DB->fetch_array($query)) {
	$catedb[$cate['mid']] = $cate['name'];
}
unset($cate);
$DB->free_result($query);

if($action == 'addarticle') {
	// 取值并过滤部分
	$title        = trim($_POST['title']);
	$description  = sax_addslashes($_POST['description']);
	$content      = sax_addslashes($_POST['content']);
	$readpassword = sax_addslashes($_POST['readpassword']);
	$alias        = trim($_POST['alias']);
	$mids        = $_POST['mids'];
	$keywords     = strtolower(sax_addslashes(trim($_POST['keywords'])));
	$closecomment   = (int)$_POST['closecomment'];
	$closetrackback = (int)$_POST['closetrackback'];
	$visible     = (int)$_POST['visible'];
	$stick       = (int)$_POST['stick'];
	// 时间变量
	$edittime    = (int)$_POST['edittime'];
	$newyear     = (int)$_POST['newyear'];
	$newmonth    = (int)$_POST['newmonth'];
	$newday      = (int)$_POST['newday'];
	$newhour     = (int)$_POST['newhour'];
	$newmin      = (int)$_POST['newmin'];
	$pingurl	 = $_POST['pingurl'];

	$keywords    = str_replace('，', ',', $keywords);
	$keywords    = str_replace(',,', ',', $keywords);
	if (substr($keywords, -1) == ',') {
		$keywords = substr($keywords, 0, getstrlen($keywords)-1);
	}
	// 检查变量
	if(!$title || getstrlen($title) > 120) {
		$location = getlink('article', 'add', array('message'=>1));
	}
	if(!$mids) {
		$location = getlink('article', 'add', array('message'=>2));
	}
	if(!$content || getstrlen($content) < 4) {
		$location = getlink('article', 'add', array('message'=>3));
	}
	if ($keywords) {
		$v = explode(',', $keywords);
		$v_num = count($v);
		if ($v_num > 10) {
			$location = getlink('article', 'add', array('message'=>4));
		} else {
			for($i=0; $i<$v_num; $i++) {
				if(getstrlen($v[$i]) > 30) {
					$location = getlink('article', 'add', array('message'=>5));
					break;
				}
			}
		}
	}
	if ($alias) {
		if (!checkalias($alias)) {
			$location = getlink('article', 'add', array('message'=>6));
		}
		$alias = char_cv($alias);
		$r = $DB->fetch_one_array("SELECT articleid FROM {$db_prefix}articles WHERE alias='$alias' LIMIT 1");
		if ($r) {
			$location = getlink('article', 'add', array('message'=>7));
		}
	}

	$title = char_cv($title);
	$r = $DB->fetch_one_array("SELECT articleid FROM {$db_prefix}articles WHERE title='$title' LIMIT 1");
	if ($r) {
		$location = getlink('article', 'add', array('message'=>8));
	}
	$attachmentids = '';
	$attach_total = 0;
	$attachids = $_POST['attachids'];
	if ($attachids) {
		$attachmentids = implode(',', $attachids);
		$attach_total = count($attachids);
	}
	/*
	// 上传附件
	$uploadmode = 'html';
	$searcharray = array();
	$replacearray = array();
	require_once(SABLOG_ROOT.'admin/uploadfiles.php');
	// 上传结束
	*/
	// 修改时间
	if ($edittime) {
		if (checkdate($newmonth, $newday, $newyear)) {
			if (substr(PHP_OS, 0, 3) == 'WIN' && $newyear < 1970) {
				$posttime = $timestamp;
			} else {
				$posttime = gmmktime($newhour, $newmin, 00, $newmonth, $newday, $newyear) - $timeoffset * 3600;
			}
		} else {
			$posttime = $timestamp;
		}
	} else {
		$posttime = $timestamp;
	}
	$action = 'add';
	if (!$location) {
		// 插入数据部分
		$DB->query("INSERT INTO {$db_prefix}articles (uid, title, description, content, dateline, attachments, closecomment, closetrackback, visible, stick, readpassword, alias, pingurl) VALUES ('$sax_uid', '$title', '$description', '$content', '$posttime', '$attach_total', '$closecomment', '$closetrackback', '$visible', '$stick', '$readpassword', '$alias', '$pingurl')");
		$articleid = $DB->insert_id();
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles+1 WHERE userid='$sax_uid'");
		if($attachmentids){
			$DB->unbuffered_query("UPDATE {$db_prefix}attachments SET articleid='$articleid' WHERE attachmentid IN($attachmentids)");
		}
		if ($searcharray && $replacearray) {
			$content = str_replace($searcharray, $replacearray, $content);
			$DB->query("UPDATE {$db_prefix}articles SET content='$content' WHERE articleid='$articleid'");
		}
		// 关联文章分类
		foreach ($mids as $mid) {
			if ($visible) {
				$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='$mid' AND type='category'");
			}
			$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$mid')");
		}
		// 插入/更新Tags
		if ($keywords) {
			$tagdb = explode(',', $keywords);
			foreach($tagdb as $tag) {
				if ($tag) {
					$tag = sax_addslashes($tag);
					$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE name='$tag' AND type='tag' LIMIT 1");
					if(!$r) {
						if ($visible) {
							$new_mid = insert_meta($tag,$slug,'tag',1); //如果是显示的发布,容器显示内容数量1
						} else {
							$new_mid = insert_meta($tag,$slug,'tag');
						}
						$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$new_mid')");
						$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count+1");
					} else {
						$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '".$r['mid']."')");
						if ($visible) {
							$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='".$r['mid']."' AND type='tag'");
						}
					}
				}
			}			
		}
		if ($pingurl) {
			$pingurldb = explode("\n", $pingurl);
			foreach($pingurldb as $pingurl) {
				$pingurl = trim($pingurl);
				if($pingurl) {
					$url = str_replace('show&id','show&amp;id',getpermalink($articleid, $alias));
					$data = 'url='.rawurlencode($url).'&title='.rawurlencode($title).'&blog_name='.rawurlencode($options['name']).'&excerpt='.rawurlencode($content);
					$result = sendpacket($pingurl, $data);
					/*
					if (strpos($result, 'error>0</error')) {
						//succ
					} else {
						//fa
					}*/
				}
			}
		}
		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count+1");
		hottags_recache();
		archives_recache();
		categories_recache();
		statistics_recache();
		newarticles_recache();
		stick_recache();

		// 清除临时数据
		if (@include_once(SABLOG_ROOT.'data/cache/cache_autosave.php')){
			autosave_recache();
		}
		if ($visible) {
			$location = getlink('article', 'mod', array('message'=>10, 'articleid'=>$articleid));
		} else {
			$location = getlink('article', 'list', array('message'=>9));
		}
	}
	header("Location: {$location}");
	exit;
}

//修改日志
if($action == 'modarticle') {
	if ($sax_group != 1 && $sax_uid != $article['uid']) {
		$location = getlink('article', 'mod', array('message'=>11, 'articleid'=>$articleid));
	}
	$title        = trim($_POST['title']);
	$description  = sax_addslashes($_POST['description']);
	$content      = sax_addslashes($_POST['content']);
	$readpassword = sax_addslashes($_POST['readpassword']);
	$alias        = trim($_POST['alias']);
	$mids         = $_POST['mids'];

	$keywords     = strtolower(sax_addslashes(trim($_POST['keywords'])));
	$closecomment   = (int)$_POST['closecomment'];
	$closetrackback = (int)$_POST['closetrackback'];
	$visible     = (int)$_POST['visible'];
	$stick       = (int)$_POST['stick'];
	// 时间变量
	$edittime    = (int)$_POST['edittime'];
	$newyear     = (int)$_POST['newyear'];
	$newmonth    = (int)$_POST['newmonth'];
	$newday      = (int)$_POST['newday'];
	$newhour     = (int)$_POST['newhour'];
	$newmin      = (int)$_POST['newmin'];

	$pingurl	 = $_POST['pingurl'];
	$pingagain   = (int)$_POST['pingagain'];

	$keywords    = str_replace('，', ',', $keywords);
	$keywords    = str_replace(',,', ',', $keywords);
	if (substr($keywords, -1) == ',') {
		$keywords = substr($keywords, 0, getstrlen($keywords)-1);
	}

	// 检查变量
	if(!$title || getstrlen($title) > 120) {
		$location = getlink('article', 'mod', array('message'=>1, 'articleid'=>$articleid));
	}
	if(!$mids) {
		$location = getlink('article', 'mod', array('message'=>2, 'articleid'=>$articleid));
	}
	if(!$content || getstrlen($content) < 4) {
		$location = getlink('article', 'mod', array('message'=>3, 'articleid'=>$articleid));
	}
	if ($keywords) {
		$v = explode(',', $keywords);
		$v_num = count($v);
		if ($v_num > 10) {
			$location = getlink('article', 'mod', array('message'=>4, 'articleid'=>$articleid));
		} else {
			for($i=0; $i<$v_num; $i++) {
				if(getstrlen($v[$i]) > 30) {
					$location = getlink('article', 'mod', array('message'=>5, 'articleid'=>$articleid));
					break;
				}
			}
		}
	}
	if ($alias) {
		if (!checkalias($alias)) {
			$location = getlink('article', 'mod', array('message'=>6, 'articleid'=>$articleid));
		}
		$alias = char_cv($alias);
		$r = $DB->fetch_one_array("SELECT articleid FROM {$db_prefix}articles WHERE alias='$alias' AND articleid!='$articleid' LIMIT 1");
		if($r) {
			$location = getlink('article', 'mod', array('message'=>7, 'articleid'=>$articleid));
		}
	}

	$title = char_cv($title);
	$r = $DB->fetch_one_array("SELECT articleid FROM {$db_prefix}articles WHERE title='$title' AND articleid!='$articleid' LIMIT 1");
	if ($r) {
		$location = getlink('article', 'mod', array('message'=>8, 'articleid'=>$articleid));
	}

	// 修改附件
	if ($delattachids = implode_ids($_POST['delattach'])) {
		$delattachs = array();
		//删除附件
		$query = $DB->query("SELECT articleid,attachmentid,filepath,thumb_filepath FROM {$db_prefix}attachments WHERE attachmentid IN ($delattachids)");
		while($attach = $DB->fetch_array($query)) {
			$delattachs[$attach['attachmentid']] = $attach;
		}
		removeattachment($delattachs);
	}

	$attachmentids = '';
	//$attach_total = 0;
	$attachids = $_POST['attachids'];
	if ($attachids) {
		$attachmentids = implode(',', $attachids);
		$attach_total = count($attachids);
	}
	/*
	$uploadmode = 'html';
	$searcharray = array();
	$replacearray = array();
	require_once(SABLOG_ROOT.'admin/uploadfiles.php');
	*/
	if($attachmentids){
		$DB->unbuffered_query("UPDATE {$db_prefix}attachments SET articleid='$articleid' WHERE attachmentid IN($attachmentids)");
	}
	if ($searcharray && $replacearray) {
		$content = str_replace($searcharray, $replacearray, $content);
	}
	// 修改附件结束

	// 修改时间
	$edittimesql = '';
	if ($edittime) {
		if (checkdate($newmonth, $newday, $newyear)) {
			if (substr(PHP_OS, 0, 3) == 'WIN' && $newyear < 1970) {
				$edittimesql = '';
			} else {
				$posttime = gmmktime($newhour, $newmin, 00, $newmonth, $newday, $newyear) - $timeoffset * 3600;
				$edittimesql = ", dateline='$posttime'";
			}
		}
	}
	$oldtags = $_POST['oldtags'] ? strtolower(sax_addslashes($_POST['oldtags'])) : '';

	if (!$location) {
		$attach_total = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments WHERE articleid='$articleid'"), 0);
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET title='$title', description='$description', content='$content', attachments='$attach_total', closecomment='$closecomment', closetrackback='$closetrackback', visible='$visible', stick='$stick', readpassword='$readpassword', alias='$alias', pingurl='$pingurl' $edittimesql WHERE articleid='$articleid'");
		$allmids = get_mids($articleid);
		$dostat = 0;
		// 操。这部分好乱。我都不知道自己写什么。
		//更新修改前后的标签以及容器内的数量
		if ($keywords != $oldtags) {
			$dostat = 1;
			$arrtag		= explode(',', $keywords);
			$arrold		= explode(',', $oldtags);
			$arrtag_num	= count($arrtag);
			$arrold_num	= count($arrold);

			for($i=0; $i<$arrtag_num; $i++) {
				if (!in_array($arrtag[$i], $arrold)) {
					$arrtag[$i] = trim($arrtag[$i]);
					if ($arrtag[$i]) {
						$tag = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE name='$arrtag[$i]' AND type='tag' LIMIT 1");
						if(!$tag['mid']) {
							if ((!$article['visible'] && $visible) || ($article['visible'] && $visible)) {
								$new_mid = insert_meta($arrtag[$i], $slug, 'tag', 1);
							} elseif (($article['visible'] && !$visible) || (!$article['visible'] && !$visible)) {
								$new_mid = insert_meta($arrtag[$i], $slug, 'tag');
							}
							$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$new_mid')");
							$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count+1");
						} else {
							$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}relationships WHERE cid='$articleid' AND mid='".$tag['mid']."'");
							if(!$r['mid']) {
								$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '".$tag['mid']."')");
							}
						}
					}
				}
			}
			for($i=0; $i<$arrold_num; $i++) {
				if ($arrold[$i] && !in_array($arrold[$i], $arrtag)) {
					$tag = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}metas WHERE name='$arrold[$i]' AND type='tag' LIMIT 1");
					if ($tag['mid']) {
						$DB->unbuffered_query("DELETE FROM {$db_prefix}relationships WHERE cid='$articleid' AND mid='".$tag['mid']."'");
					}
				}
			}
			//处理标签结束
		}

		//更新修改前后的标签以及容器内的数量
		$oldmids = get_mids($articleid, 'category');
		if ($oldmids != implode(',', $mids)) {
			$dostat = 1;
			// 关联文章分类
			// 添加新的归属分类
			$oldmiddb = explode(',', $oldmids);
			$mids_num = count($mids);
			$oldmids_num = count($oldmiddb);

			for($i=0; $i<$mids_num; $i++) {
				if (!in_array($mids[$i], $oldmiddb)) {
					$mids[$i] = (int)$mids[$i];
					if ($mids[$i]) {
						$r = $DB->fetch_one_array("SELECT mid FROM {$db_prefix}relationships WHERE cid='$articleid' AND mid='".$mids[$i]."'");
						if(!$r['mid']) {
							$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '".$mids[$i]."')");
						}
					}
				}
			}
			for($i=0; $i<$oldmids_num; $i++) {
				$oldmiddb[$i] = (int)$oldmiddb[$i];
				if ($oldmiddb[$i] && !in_array($oldmiddb[$i], $mids)) {
					$DB->unbuffered_query("DELETE FROM {$db_prefix}relationships WHERE cid='$articleid' AND mid='".$oldmiddb[$i]."'");
				}
			}
		}

		if ($dostat) {
			//把修改前和修改后的容器都统计数量
			$mids = get_mids($articleid);
			$allmids = $allmids . ',' . $mids;
			$allmiddb = explode(',', $allmids);
			$allmiddb = array_unique($allmiddb);
			$query = $DB->query("SELECT mid FROM {$db_prefix}metas WHERE mid IN (".implode(',',$allmiddb).")");
			while ($meta = $DB->fetch_array($query)) {
				$ctotal = get_meta_article_count($meta['mid']);
				update_meta_count($meta['mid'], $ctotal);
			}
		}

		if ($article['visible'] != $visible) {
			if ($article['visible']) {
				$visible = 0;
				$query = '-';
			} else {
				$visible = 1;
				$query = '+';
			}
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count".$query."1");
			$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles".$query."1 WHERE userid='$sax_uid'");
		}

		archives_recache();
		hottags_recache();
		categories_recache();
		statistics_recache();
		newarticles_recache();

		if ($pingurl && $pingagain) {
			$pingurldb = explode("\n", $pingurl);
			foreach($pingurldb as $pingurl) {
				$pingurl = trim($pingurl);
				if($pingurl) {
					$url = str_replace('show&id','show&amp;id',getpermalink($article['articleid'], $article['alias']));
					$data = 'url='.rawurlencode($url).'&title='.rawurlencode($article['title']).'&blog_name='.rawurlencode($options['name']).'&excerpt='.rawurlencode($article['content']);
					$result = sendpacket($pingurl, $data);
					/*
					if (strpos($result, 'error>0</error')) {
						//succ
					} else {
						//fa
					}*/
				}
			}
		}

		if ($article['stick'] != $stick) {
			stick_recache();
		}
		$location = getlink('article', 'mod', array('message'=>13, 'articleid'=>$articleid));
	}
	header("Location: {$location}");
	exit;
}

//设置状态
if($action == 'visible') {
	if ($sax_group != 1 && $sax_uid != $article['uid']) {
		$location = getlink('article', 'list', array('message'=>11));	
	} else {
		if ($article['visible']) {
			$visible = 0;
			$query = '-';
			$location = getlink('article', 'list', array('message'=>16, 'articleid'=>$articleid));	
		} else {
			$visible = 1;
			$query = '+';
			$location = getlink('article', 'list', array('message'=>17, 'articleid'=>$articleid));	
		}
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET visible='$visible' WHERE articleid='$articleid'");

		$mids = get_mids($articleid);
		$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count".$query."1 WHERE mid IN ($mids)");

		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count".$query."1");
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles".$query."1 WHERE userid='".$article['uid']."'");
		archives_recache();
		categories_recache();
		statistics_recache();
		newarticles_recache();
		newcomments_recache();
	}
	header("Location: {$location}");
	exit;
}

//批量操作日志
if($action == 'domore') {
	if ($aids = implode_ids($_POST['selectall'])) {

		if($doit == 'delete') {

			$comment_count = $a_total = $a_stick = 0;
			$vaids = $comma = '';
			$query  = $DB->query("SELECT articleid,visible,uid,comments,stick FROM {$db_prefix}articles WHERE articleid IN ($aids)".$uquery);
			while($article = $DB->fetch_array($query)) {
				if ($article['visible']) {
					$a_total++;
					$vaids .= $comma.$article['articleid'];
					$comma = ',';
					$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles-1 WHERE userid='".$article['uid']."'");
					$comment_count = $comment_count + $article['comments'];
				}
				if ($article['stick']) {
					$a_stick = 1;
				}
			}
			$mids = get_mids($vaids);
			$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count-1 WHERE mid IN ($mids)");

			//$DB->unbuffered_query("DELETE FROM {$db_prefix}metas WHERE count='0' AND type='tag'");

			$query  = $DB->query("SELECT attachmentid,filepath,thumb_filepath FROM {$db_prefix}attachments WHERE articleid IN ($aids)");
			if ($DB->num_rows($query)) {
				$nokeep = array();
				while($attach = $DB->fetch_array($query)) {
					$nokeep[$attach['attachmentid']] = $attach;
				}
				removeattachment($nokeep);
			}
			$DB->unbuffered_query("DELETE FROM {$db_prefix}articles WHERE articleid IN ($aids)".$uquery);
			$DB->unbuffered_query("DELETE FROM {$db_prefix}comments WHERE articleid IN ($aids)");
			$DB->unbuffered_query("DELETE FROM {$db_prefix}relationships WHERE cid IN ($aids)");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count-$a_total ,comment_count=comment_count-$comment_count");
			hottags_recache();
			archives_recache();
			categories_recache();
			newarticles_recache();
			statistics_recache();
			newcomments_recache();
			if ($a_stick) {
				stick_recache();
			}
			$location = getlink('article', 'list', array('message'=>15));	

		} elseif ($doit == 'hidden') {

			$a_total = 0;
			$vaids = $comma = '';
			$query  = $DB->query("SELECT articleid,visible,uid FROM {$db_prefix}articles WHERE articleid IN ($aids)".$uquery);
			while($article = $DB->fetch_array($query)) {
				if ($article['visible']) {
					$a_total++;
					$vaids .= $comma.$article['articleid'];
					$comma = ',';
					$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles-1 WHERE userid='".$article['uid']."'");
				}
			}
			$mids = get_mids($vaids);
			$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count-1 WHERE mid IN ($mids)");

			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET visible='0' WHERE articleid IN ($aids)");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count-".$a_total);
			archives_recache();
			categories_recache();
			newarticles_recache();
			newcomments_recache();
			statistics_recache();
			stick_recache();
			$location = getlink('article', 'list', array('message'=>18));

		} elseif ($doit == 'display') {

			$a_total = 0;
			$vaids = $comma = '';
			$query  = $DB->query("SELECT articleid,visible,uid FROM {$db_prefix}articles WHERE articleid IN ($aids)".$uquery);
			while($article = $DB->fetch_array($query)) {
				if (!$article['visible']) {
					$a_total++;
					$vaids .= $comma.$article['articleid'];
					$comma = ',';
					$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles+1 WHERE userid='".$article['uid']."'");
				}
			}
			$mids = get_mids($vaids);
			$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid IN ($mids)");

			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET visible='1' WHERE articleid IN ($aids)");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count+".$a_total);
			archives_recache();
			categories_recache();
			statistics_recache();
			newarticles_recache();
			newcomments_recache();
			stick_recache();
			$location = getlink('article', 'list', array('message'=>19));

		} elseif ($doit == 'stick') {

			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET stick='1' WHERE articleid IN ($aids)".$uquery);
			stick_recache();
			$location = getlink('article', 'list', array('message'=>20));

		} elseif ($doit == 'unstick') {

			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET stick='0' WHERE articleid IN ($aids)".$uquery);
			stick_recache();
			$location = getlink('article', 'list', array('message'=>21));

		} else {

			$location = getlink('article', 'list', array('message'=>22));

		}

	} else {

		$location = getlink('article', 'list', array('message'=>23));

	}	
	header("Location: {$location}");
	exit;
}

//操作结束

if (in_array($action, array('add', 'mod'))) {
	if ($action == 'mod') {
		$act = 'modarticle';
		$subnav = '修改日志';
		if ($sax_group != 1 && $sax_uid != $article['uid']) {
			$location = getlink('article', 'list', array('message'=>11));
			header("Location: {$location}");
			exit;
		}
		$article['keywords'] = htmlspecialchars($article['keywords']);
		//$article['description'] = str_replace('\r\n', '', $article['description']);
		//$article['content'] = str_replace('\r\n', '', $article['content']);
		$article['content'] = htmlspecialchars($article['content']);
		$article['description'] = htmlspecialchars($article['description']);

		//附件
		$query = $DB->query("SELECT attachmentid,articleid,dateline,filename,filesize,filetype,filepath,thumb_filepath,thumb_width,thumb_height,isimage FROM {$db_prefix}attachments WHERE articleid = '".$article['articleid']."'");
		$attachdb = array();
		while($attach = $DB->fetch_array($query)) {
			$attach['filename'] = htmlspecialchars($attach['filename']);
			$attach['dateline'] = sadate('Y-m-d H:i:s',$attach['dateline'],1);
			$attach['filesize'] = sizecount($attach['filesize']);
			if ($attach['isimage']) {
				if (!$attach['thumb_filepath']) {
					$attach['thumb_filepath'] = $attach['filepath'];
				}
				$imsize = @getimagesize( SABLOG_ROOT . $options['attachments_dir'] . $attach['filepath'] );
				$im = scale_image( array(
					'max_width'  => 320,
					'max_height' => 240,
					'cur_width'  => $imsize[0],
					'cur_height' => $imsize[1]
				));
				$attach['thumb_width'] = $im['img_width'];
				$attach['thumb_height'] = $im['img_height'];
				$attach['thumb_filepath'] = $options['url'] . $options['attachments_dir'] . $attach['thumb_filepath'];
			}
			$attachdb[$attach['attachmentid']] = $attach;
		}
		unset($attach);

		$closecomment_check = $article['closecomment'] ? 'checked' : '';
		$closetrackback_check = $article['closetrackback'] ? 'checked' : '';
		$visible_check = $article['visible'] ? 'checked' : '';
		$stick_check = $article['stick'] ? 'checked' : '';

		$metadb = array();
		$query = $DB->query("SELECT m.mid, m.name, m.type, r.cid FROM {$db_prefix}metas m
			INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
			WHERE m.type IN ('category','tag') AND r.cid IN ($articleid)
			ORDER BY m.displayorder ASC, m.mid DESC");
		while ($meta = $DB->fetch_array($query)) {
			$metadb[$meta['cid']][$meta['type']][$meta['mid']] = $meta;
		}
		unset($meta);
		$DB->free_result($query);
		if ($metadb[$article['articleid']]['tag']) {
			$comma = $article['keywords'] = '';
			foreach ($metadb[$article['articleid']]['tag'] as $tag) {
				$article['keywords'] .= $comma.$tag['name'];
				$comma = ',';
			}
		}
	} else {
		$article['description'] = $article['content'] = '';
		if (@include_once(SABLOG_ROOT.'data/cache/cache_autosave.php')) {
			$autosavedb = sax_stripslashes($autosavedb);
			$article['title'] = $autosavedb[$sax_uid]['title'];
			$article['description'] = $autosavedb[$sax_uid]['description'];
			$article['content'] = $autosavedb[$sax_uid]['content'];
		} else {
			$article['title'] = $title;
			$article['description'] = $description;
			$article['content'] = $content;
		}

		$act = 'addarticle';
		$subnav = '添加日志';
		$article['alias'] = $_COOKIE['alias'] ? $_COOKIE['alias'] : $alias;
		$article['mid'] = intval($_COOKIE['mid'] ? $_COOKIE['mid'] : $mid);
		$stick_check = $stick ? 'checked' : '';
		$closecomment_check = $closecomment ? 'checked' : '';
		$closetrackback_check = $closetrackback ? 'checked' : '';
		$visible_check = !isset($visible) ? 'checked' : '';
	}
	@list($newyear, $newmonth, $newday, $newhour, $newmin) = explode('-', sadate('Y-n-j-H-i', $timestamp));

	//载入编辑器
	//include(SABLOG_ROOT.'include/editor.inc.php');
}//end add or mod

if ($action == 'list') {
	$uid = (int)$uid;
	$m = sax_addslashes($m);

	$addquery = $pagelink = '';
	if ($sax_group != 1) {
		$subnav = '您发表的日志';
		$addquery .= " AND a.uid = '$sax_uid'";
	}

	$arttotal = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles a WHERE 1 $addquery"), 0);
	$hiddenarttotal = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles a WHERE visible='0' $addquery"), 0);
	$displayarttotal = $arttotal - $hiddenarttotal;
	$stickarttotal = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles a WHERE stick='1' $addquery"), 0);

	if ($mid) {
		$r = $DB->fetch_one_array("SELECT mid, name, slug, count, type FROM {$db_prefix}metas WHERE mid='$mid'");
		if (!$r) {
			redirect('记录不存在.', './');
		}
		$aids = get_cids($r['mid']);
		$total = $r['count'];
		if ($total && $aids) {
			$addquery .= " AND a.articleid IN ($aids)";
		}
		$pageurl = gettaglink($r['slug']);
		$pagelink .= '&amp;mid='.$mid;
		if ($r['type'] == 'category') {
			$subnav = '分类:'.$r['name'];
		} else {
			$subnav = 'Tags:'.$r['name'];
		}
	}
	if ($view == 'stick') {
		$addquery .= " AND a.stick='1'";
		$subnav = '置顶的日志';
		$pagelink .= '&amp;view=stick';
	} elseif ($view == 'hidden') {
		$addquery .= " AND a.visible='0'";
		$subnav = '隐藏的日志';
		$pagelink .= '&amp;view=hidden';
	} elseif ($view == 'display') {
		$addquery .= " AND a.visible='1'";
		$subnav = '显示的日志';
		$pagelink .= '&amp;view=display';
	}
	if ($uid) {
		$user = $DB->fetch_one_array("SELECT username FROM {$db_prefix}users WHERE userid='$uid'");
		$subnav = $user['username'].'发表的文章';
		$addquery .= " AND a.uid='$uid'";
		$pagelink .= '&amp;uid='.$uid;
	}
	if ($m) {
		$mdb = explode('-', $m);
		list($start, $end) = explode('-', gettimestamp($mdb[0],$mdb[1]));
		$pagelink .= '&amp;m='.$m;
		$subnav = '在'.$mdb[0].'年'.$mdb[1].'月里';
		//*******************************//
		$addquery .= " AND a.dateline >= '".correcttime($start)."' AND a.dateline < '".correcttime($end)."' ";
	}
	// 搜索部分
	$keywords = sax_addslashes(trim($keywords));
	if ($keywords) {
		$keywords = str_replace("_","\_",$keywords);
		$keywords = str_replace("%","\%",$keywords);
		if(preg_match("(AND|\+|&|\s)", $keywords) && !preg_match("(OR|\|)", $keywords)) {
			$andor = ' AND ';
			$sqltxtsrch = '1';
			$keywords = preg_replace("/( AND |&| )/is", "+", $keywords);
		} else {
			$andor = ' OR ';
			$sqltxtsrch = '0';
			$keywords = preg_replace("/( OR |\|)/is", "+", $keywords);
		}
		$keywords = str_replace('*', '%', addcslashes($keywords, '%_'));
		foreach(explode("+", $keywords) AS $text) {
			$text = trim($text);
			if($text) {
				$sqltxtsrch .= $andor;
				$sqltxtsrch .= "(a.content LIKE '%".$text."%' OR a.description LIKE '%".$text."%' OR a.title LIKE '%".$text."%')";
			}
		}
		$addquery .= " AND ($sqltxtsrch)";
		$subnav = '搜索结果';
		$pagelink .= '&amp;keywords='.urlencode($keywords);
	}

	$pagenum = 25;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	$total = $DB->result($DB->query("SELECT count(articleid) FROM {$db_prefix}articles a WHERE 1 $addquery $uquery"), 0);

	if ($total) {

		$multipage = multi($total, $pagenum, $page, 'cp.php?job=article&amp;action=list'.$pagelink);

		$query = $DB->query("SELECT a.dateline,a.articleid,a.title,a.uid,a.comments,a.attachments,a.visible,a.readpassword,a.stick,u.username
			FROM {$db_prefix}articles a
			LEFT JOIN {$db_prefix}users u ON u.userid=a.uid
			WHERE 1 $addquery $uquery ORDER BY dateline DESC LIMIT $start_limit, $pagenum");

		$aids = $comma = '';
		$articledb = array();
		while ($article = $DB->fetch_array($query)) {
			$aids .= $comma.$article['articleid'];
			$comma = ',';
			$article['dateline'] = sadate('Y-m-d H:i',$article['dateline'],1);
			$articledb[$article['articleid']] = $article;
		}
		unset($article);
		$DB->free_result($query);

		$metadb = array();
		if ($aids) {
			$query = $DB->query("SELECT m.mid, m.name, r.cid FROM {$db_prefix}metas m
				INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
				WHERE m.type IN ('category') AND r.cid IN ($aids)
				ORDER BY m.displayorder ASC, m.mid DESC");
			while ($meta = $DB->fetch_array($query)) {
				$metadb[$meta['cid']][] = $meta;
			}
			unset($meta);
			$DB->free_result($query);
		}
	}
} //end list

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('article');
?>