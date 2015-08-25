<?
// ========================== 文件说明 ==========================//
// 本文件说明：WAP功能页面
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('../include/common.inc.php');

// 检查WAP开启状态
if (!$options['wap_enable']) {
	exit('Wap disabled');
}

require_once('global.php');

// 博客系统状态检查
if ($options['close']) {
	wap_norun('博客已经关闭', $options['close_note']);
}

if ($action == 'logout') {
	replacesession();
	dcookies();
	$sax_hash = '';
	$sax_uid = 0;
	wap_header('注销身份');
	wap_message('注销成功', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
}

// 首页
!$action && $action = 'article';

// 文章列表
if ($action == 'article') {
	$pagenum = 10;
	if($page) {
		$orderid = ($page - 1) * $pagenum;
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$orderid = 0;
		$start_limit = 0;
		$page = 1;
	}
	//定义相同的查询语句前部分
	$query_sql = "SELECT a.articleid,a.title,a.dateline FROM {$db_prefix}articles a WHERE a.visible='1'";

	$userid = (int)$_GET['userid'];
	$mid = sax_addslashes($_GET['mid']);
	$searchid = (int)$_GET['searchid'];

	if ($mid) {
		$r = $DB->fetch_one_array("SELECT mid, name, count, type FROM {$db_prefix}metas WHERE mid='$mid'");
		if (!$r) {
			wap_header('系统消息');
			wap_message('记录不存在', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
		}
		$aids = get_cids($r['mid']);
		$total = $r['count'];
		if ($total && $aids) {
			$query_sql .= " AND a.articleid IN ($aids) ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
		}
		$pageurl = 'index.php?action=article&amp;mid='.$mid;
		if ($r['type'] == 'category') {
			$catename = '分类:'.$r['name'];
		} else {
			$catename = 'Tag:'.$r['name'];
		}
	// 查看搜索结果的文章
	} elseif ($searchid) {
		$search = $DB->fetch_one_array("SELECT * FROM {$db_prefix}searchindex WHERE searchid='$searchid' AND expiration > '$timestamp'");
		if (!$search) {
			$DB->unbuffered_query("DELETE FROM {$db_prefix}searchindex WHERE expiration < '$timestamp'");
			wap_header('系统消息');
			wap_message('您指定的搜索不存在或已过期,请返回.', array('title' => '重新搜索', 'link' => 'index.php?action=search'));
		}
		$total = $search['totals'];
		$query_sql .= " AND a.articleid IN (".$search['ids'].") ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
		$pageurl = 'index.php?action=article&amp;searchid='.$searchid;
		$catename = '搜索:'.$search['keywords'];
	// 查看首页文章
	} else {
		$catename = '全部文章';
		$total = $stats['article_count'];
		$setdate = (int)$_GET['setdate'];
		if ($setdate && getstrlen($setdate) == 6) {
			$setyear = substr($setdate,0,4);
			if ($setyear >= 2038 || $setyear <= 1970) {
				$setyear = sadate('Y');
				$setmonth = sadate('m');
				$start = $end = 0;
			} else {
				$setmonth = substr($setdate,-2);
				list($start, $end) = explode('-', gettimestamp($setyear,$setmonth));
				$catename = $setyear.'年'.$setmonth.'月的文章';
			}
		} else {
			$setyear = sadate('Y');
			$setmonth = sadate('m');
			$start = $end = 0;
		}
		//*******************************//
		$startadd = $start ? " AND a.dateline >= '".correcttime($start)."' " : '';
		$endadd   = $end ? " AND a.dateline < '".correcttime($end)."' " : '';
		//*******************************//
		if($setdate) {
			$query = $DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles a WHERE a.visible='1' ".$startadd.$endadd);
			$total = $DB->result($query, 0);
		}
		//*******************************//
		$query_sql .= $startadd.$endadd." ORDER BY a.dateline DESC LIMIT $start_limit, ".$pagenum;
		$pageurl = 'index.php?action=article&amp;setdate='.$setdate;
	}
	// 执行查询

	wap_header($catename);

	if ($total) {
		$query = $DB->query($query_sql);
		$multipage = multi($total, $pagenum, $page, $pageurl, $maxpages);
		echo "<ul>\n";
		while ($article = $DB->fetch_array($query)) {
			$orderid++;
			echo "<li>{$orderid}. <a href=\"index.php?action=show&amp;id=".$article['articleid']."\">".$article['title']."</a> (".sadate('m-d',$article['dateline']).")</li>\n";
		}
		$DB->free_result($query);
		echo "</ul>\n";
		echo "<p>共有{$total}篇文章</p>\n";
		echo $multipage;
	} else {
		echo "<p>没有任何日志</p>\n";
	}
	wap_footer();
}

if ($action == 'category' || $action == 'tag') {
	if ($action == 'category') {
		$name = '分类';
		$total = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type = 'category'"), 0);
	} else {
		$name = '标签';
		$total = $stats['tag_count'];
	}
	wap_header($name);
	$pagenum = 15;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	$metadb = array();
	if ($total) {
		$multipage = multi($total, $pagenum, $page, 'index.php?action='.$action);
		$query = $DB->query("SELECT * FROM {$db_prefix}metas WHERE type='$action' ORDER BY displayorder LIMIT $start_limit, $pagenum");
		echo "<ul>\n";
		while($meta = $DB->fetch_array($query)){
			echo "<li><a href=\"index.php?action=article&amp;mid=".$meta['mid']."\">".$meta['name']."</a> (".$meta['count'].")</li>\n";
		}
		unset($meta);
		$DB->free_result($query);
		echo "</ul>\n";
		echo "<p>共有{$total}个{$name}</p>\n";
		echo $multipage;
	} else {
		echo '<p>没有任何'.$name.'</p>';
	}
	wap_footer();
}

// 日志归档
if ($action == 'archives') {
	wap_header('日志归档');
	if (empty($archivecache)) {
		echo '<p>没有任何归档</p>';
	} else {
		$monthname = array('','一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月');
		$pagenum = 15;
		if($page) {
			$start_limit = ($page - 1) * $pagenum;
		} else {
			$start_limit = 0;
			$page = 1;
		}
		echo "<ul>\n";
		$multipage = multi(count($archivecache), $pagenum, $page, 'index.php?action=archives');
		$archivecache = @array_slice($archivecache,$start_limit,$pagenum);
		foreach($archivecache as $key => $val){
			$v = explode('-', $key);
			$e_month = ($v[1] < 10) ? str_replace('0', '', $v[1]) : $v[1];
			echo "<li><a href=\"index.php?action=article&amp;setdate=".$v[0].$v[1]."\">".$monthname[$e_month].", ".$v[0]."</a> (".$val['num'].")</li>\n";
		}
		echo "</ul>\n";
		echo $multipage;
	}
	wap_footer();
}

// 搜索引擎
if ($action == 'search') {
	wap_header('搜索引擎');
	$keywords = sax_addslashes(trim($_POST['keywords'] ? $_POST['keywords'] : $_GET['keywords']));
	if (!$keywords || getstrlen($keywords) < 3) {
		echo "<p>注意:只搜索文章标题和内容,不对评论进行搜索.关键字不能少于3个字节.</p>\n";
		echo "<p>关键字中可使用通配符 &quot;*&quot;<br />匹配多个关键字全部, 可用空格或 &quot;AND&quot; 连接. 如: angel AND 4ngel<br />匹配多个关键字其中部分, 可用 &quot;|&quot; 或 &quot;OR&quot; 连接. 如: angel OR 4ngel</p>";
		echo "<form name=\"loginform\" id=\"loginform\" action=\"index.php?action=search&amp;sax_hash=".$sax_hash."\" method=\"post\">\n";
		echo "<p><label>关键字: <input type=\"text\" name=\"keywords\" value=\"\" /> <input type=\"submit\" value=\"搜索\" /></label></p>\n";
		echo "</form>\n";
	} else {

		$searchstring = sax_addslashes($keywords).'|content|wap_all';

		$searchindex = array('id' => 0, 'dateline' => '0');
		$query = $DB->query("SELECT searchid, dateline,
			(".($sax_uid ? "uid='$sax_uid'" : "ipaddress='$onlineip'")." AND $timestamp-dateline<20) AS flood, (searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
			FROM {$db_prefix}searchindex
			WHERE (".($sax_uid ? "uid='$sax_uid'" : "ipaddress='$onlineip'")." AND $timestamp-dateline<20) ORDER BY flood");

		while($index = $DB->fetch_array($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($index['flood'] && $sax_group != 1 &&  $sax_group != 2) {
				wap_message('对不起,您在 20 秒内只能进行一次搜索.', array('title' => '重新搜索', 'link' => 'index.php?action=search'));
			}
		}

		if($searchindex['id']) {
			$searchid = $searchindex['id'];
		} else {
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
			foreach(explode('+', $keywords) AS $text) {
				$text = trim($text);
				if($text) {
					$sqltxtsrch .= $andor;
					$sqltxtsrch .= "(content LIKE '%".$text."%' OR title LIKE '%".$text."%')";
				}
			}
			//搜索文章
			$totals = 0;
			$ids = $comma = '';
			$query = $DB->query("SELECT articleid FROM {$db_prefix}articles WHERE visible='1' AND ($sqltxtsrch) ORDER BY dateline desc");
			while($article = $DB->fetch_array($query)) {
				$ids .= $comma.$article['articleid'];
				$comma = ',';
				$totals++;
			}
			$DB->free_result($query);

			$DB->query("INSERT INTO {$db_prefix}searchindex (keywords, searchstring, dateline, expiration, totals, ids, ipaddress, uid) VALUES ('".char_cv($keywords)."', '$searchstring', '$timestamp', '".($timestamp+3600)."', '$totals', '$ids', '$onlineip', '$sax_uid')");

			$searchid = $DB->insert_id();
		}
		wap_message('搜索成功完成', array('title' => '查看搜索结果', 'link' => 'index.php?action=article&amp;searchid='.$searchid));
	}
	wap_footer();
}

// 最新评论
if ($action == 'comments') {
	$articleid = (int)$_GET['articleid'];
	$query_sql = "SELECT c.articleid,c.author,c.commentid,c.dateline,c.content, a.title FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.visible='1' AND c.visible='1'";
	if ($articleid) {
		$query_sql .= " AND c.articleid='$articleid'";
		$article = $DB->fetch_one_array("SELECT title,comments FROM {$db_prefix}articles WHERE articleid='$articleid'");
		$total = $article['comments'];
		$pageurl = 'index.php?action=comments&amp;articleid='.$articleid;
		$subtitle = "《".$article['title']."》的评论";
	} else {
		$total = $stats['comment_count'];
		$pageurl = 'index.php?action=comments';
		$subtitle = "全部评论";
	}
	wap_header($subtitle);
	if ($total) {
		$pagenum = 5;
		if($page) {
			$orderid = ($page - 1) * $pagenum;
			$start_limit = ($page - 1) * $pagenum;
		} else {
			$orderid = 0;
			$start_limit = 0;
			$page = 1;
		}
		$query_sql .= " ORDER BY commentid DESC LIMIT $start_limit, $pagenum";
		$multipage = multi($total, $pagenum, $page, $pageurl);
		$query = $DB->query($query_sql);

		echo "<ul>";
		while ($comment=$DB->fetch_array($query)) {
			$orderid++;
			echo "<li>";

			if (!$articleid) {
				echo "<p><strong>{$orderid}.</strong>文章:<a href=\"index.php?action=show&amp;id=".$comment['articleid']."\">".$comment['title']."</a></p>\n";
			}

			echo "<p>".($articleid ? "<strong>{$orderid}.</strong> " : "").$comment['author']." : ".sadate('Y-m-d H:i',$comment['dateline'],1);
			if ($sax_group == 1) {
				echo " [<a href=\"index.php?action=editcomment&amp;commentid=".$comment['commentid']."\">编辑</a>]";
			}
			echo "</p>\n";
			echo "<p>内容:".wap_html_clean($comment['content'])."</p>\n";
			echo "<br />\n";
			echo "</li>\n";
		}
		echo "</ul>";

		unset($comment);
		echo "<p>共有".$total."条评论</p>\n";
		echo $multipage;
		$DB->free_result($query);
	} else {
		echo "<p>没有任何评论</p>\n";
	}
	echo "<p>";
	if ($articleid) {
		if (!$sax_uid || !$sax_hash) {
			echo "<a href=\"index.php?action=login\">立即登陆发表评论</a><br />\n";
		} else {
			echo "<a href=\"index.php?action=addcomment&amp;articleid=".$articleid."\">发表评论</a><br />\n";
		}
		echo "<a href=\"index.php?action=show&amp;id=".$articleid."\">返回文章</a><br />";
	}
	wap_footer();
}

// 浏览日志
if ($action == 'show') {
	$offset = $_GET['offset'] ? (int)$_GET['offset'] : 0;
	$articleid = (int)$_GET['id'];
	// 获取文章信息
	if (!$articleid) {
		wap_header('系统消息');
		wap_message('缺少参数', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
	} else {
		$article = $DB->fetch_one_array("SELECT a.articleid,a.uid,a.title,a.content,a.dateline,a.views,a.comments,a.closecomment,a.readpassword,a.attachments,u.username
			FROM {$db_prefix}articles a
			LEFT JOIN {$db_prefix}users u ON a.uid=u.userid
			WHERE a.visible='1' AND articleid='$articleid'");
		if (!$article) {
			wap_header('系统消息');
			wap_message('记录不存在', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
		}
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET views=views+1 WHERE articleid='$articleid'");
		// 获取文章的关联信息
		$metadb = array();
		$query = $DB->query("SELECT m.mid, m.name, m.slug, m.type, r.cid FROM {$db_prefix}metas m
			INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
			WHERE m.type IN ('category', 'tag') AND r.cid='".$article['articleid']."'
			ORDER BY m.displayorder ASC, m.mid DESC");
		while ($meta = $DB->fetch_array($query)) {
			$metadb[$article['articleid']][$meta['type']][] = $meta;
		}
		$DB->free_result($query);
	}

	wap_header($article['title']);
	echo "<p><a href=\"index.php?action=showuser&amp;userid=".$article['uid']."\">".$article['username']."</a> 发表于 ".sadate('Y-m-d H:i',$article['dateline'],1)."</p>";
	echo "<p>分类:";	
	$comma = '';
	foreach ($metadb[$article['articleid']]['category'] as $meta) {
		echo $comma."<a href=\"index.php?action=article&amp;mid=".$meta['mid']."\">".$meta['name']."</a>\n";
		$comma = ', ';
	}
	echo "</p>\n";
	if ($article['readpassword']) {
		echo "<p>本文需要输入密码才能浏览,请通过HTTP浏览器浏览.</p>\n";
	} else {
		if ($metadb[$article['articleid']]['tag']) {
			echo "<p>标签:";	
			$comma = '';
			foreach ($metadb[$article['articleid']]['tag'] as $meta) {
				echo $comma."<a href=\"index.php?action=article&amp;mid=".$meta['mid']."\">".$meta['name']."</a>\n";
				$comma = ', ';
			}
			echo "</p>\n";
		}
		//附件
		if ($article['attachments']) {
			//单张显示附件图片
			$attach_offset = isset($_GET['attach_offset']) ? max(1, (int)$_GET['attach_offset']) : 1;
			if($attach_offset && $attach_offset <= $article['attachments']) {
				$start_limit = $attach_offset - 1;
			} else {
				$start_limit = 0;
				$attach_offset = 1;
			}
			$attach = $DB->fetch_one_array("SELECT attachmentid, articleid, dateline, filename, filetype, filesize, downloads, filepath, thumb_filepath, thumb_width, thumb_height, isimage FROM {$db_prefix}attachments WHERE articleid='$articleid' ORDER BY attachmentid DESC LIMIT $attach_offset, 1");
		
			if (file_exists(SABLOG_ROOT.$options['attachments_dir'].$attach['filepath'])) {
				echo "<p><img src=\"imgout.php?attachid=".$attach['attachmentid']."\" alt=\"\" border=\"0\"></p>\n";
			}

			echo "<p>共{$article[attachments]}张图片";
			if ($attach_offset > 1) {
				echo ', <a href="index.php?action=show&amp;id='.$articleid.'&amp;attach_offset='.($attach_offset-1).'">上张图片</a>';
			}
			if ($attach_offset < $article['attachments']) {
				echo ', <a href="index.php?action=show&amp;id='.$articleid.'&amp;attach_offset='.($attach_offset+1).'">下张图片</a>';
			}
			echo "</p>\n";
		}

		//htmlSubString
		//echo 'aaa:'.getstrlen($article['content']);

		//截取内容

		if($options['wap_article_limit'] && !$offset) {
			if(getstrlen($article['content']) < $options['wap_article_limit']) {
				$last = 0;
				$next = 0;
			} else {
				$article['content'] = trimmed_title($article['content'], $options['wap_article_limit']);
				$last = 0;
				$next = 1;
				$offset_next = $offset + $options['wap_article_limit'];
			}
		} elseif ($options['wap_article_limit'] && $offset > 0) {
			$article['content'] = trimmed_title($article['content'], $options['wap_article_limit'], $offset);
			$last = 1;
			$offset_last = $offset - $options['wap_article_limit'];
			if ($offset_last < 0) {
				$offset_last = 0;
			}
			if(getstrlen($article['content']) < $options['wap_article_limit']) {
				$next = 0;
			} else {
				$next = 1;
				$offset_next = $offset + $options['wap_article_limit'];
			}
		} else {
			$last = 0;
			$next = 0;
		}
		//截取内容结束

		echo "<p>".$article['content']."</p>\n";

		echo "<p>\n";
		echo ($last ? "<a href=\"index.php?action=show&amp;id=$articleid&amp;offset=$offset_last\">上页</a> " : '');
		echo ($next ? " <a href=\"index.php?action=show&amp;id=$articleid&amp;offset=$offset_next\">下页</a>" : '');
		echo "</p>\n";

		//上一篇
		$previous_one = $DB->fetch_one_array("SELECT articleid,alias,title FROM {$db_prefix}articles WHERE dateline < '".$article['dateline']."' AND visible='1' ORDER BY dateline DESC LIMIT 1");
		if($previous_one) {
			echo "<p>上一篇:<a href=\"index.php?action=show&amp;id=".$previous_one['articleid']."\">".$previous_one['title']."</a></p>\n";
		}
		//下一篇
		$next_one = $DB->fetch_one_array("SELECT articleid,alias,title FROM {$db_prefix}articles WHERE dateline > '".$article['dateline']."' AND visible='1' ORDER BY dateline ASC LIMIT 1");
		if($next_one) {
			echo "<p>下一篇:<a href=\"index.php?action=show&amp;id=".$next_one['articleid']."\">".$next_one['title']."</a></p>\n";
		}		

		echo "<p>\n";
		if ($article['comments']) {
			echo "<a href=\"index.php?action=comments&amp;articleid=".$article['articleid']."\">".$article['comments']."条评论</a><br />\n";
		}
		if (!$article['closecomment']) {
			if (!$sax_uid || !$sax_hash) {
				echo "<a href=\"index.php?action=login\">立即登陆发表评论</a><br />\n";
			} else {
				echo "<a href=\"index.php?action=addcomment&amp;articleid=".$article['articleid']."\">发表评论</a><br />\n";
			}
		}
		echo "</p>\n";

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
					echo "<h3>相关文章</h3>";
					echo '<ul>';
					$query = $DB->query("SELECT articleid,title,comments FROM {$db_prefix}articles WHERE visible='1' AND articleid IN ($relids) ORDER BY dateline DESC LIMIT ".intval($options['related_shownum']));
					$orderid = 0;
					while ($title = $DB->fetch_array($query)) {
						$orderid++;
						echo "<li>{$orderid}.<a href=\"index.php?action=show&amp;id=".$title['articleid']."\">".$title['title']."</a>(".$title['comments'].")</li>";
					}
					echo '</ul>';
					unset($title);
					$DB->free_result($query);
				}
			}
		}
	}
	wap_footer();
}

//登陆
if ($action == 'login') {
	wap_header('用户登陆');
	if (!$do || $do != 'login') {
		echo "<form name=\"loginform\" id=\"loginform\" action=\"\" method=\"post\">\n";
		echo "<p><label>用户名:<br /><input type=\"text\" name=\"username\" value=\"\" /></label></p>\n";
		echo "<p><label>密码:<br /><input type=\"password\" name=\"password\" value=\"\" /></label></p>\n";
		echo "<p><input type=\"submit\" value=\"登录\" /><input type=\"hidden\" name=\"do\" value=\"login\" /></p>\n";
		echo "</form>\n";
	} elseif ($do == 'login') {
		// 登陆验证
		$username = sax_addslashes(trim($_POST['username']));
		$password = md5($_POST['password']);
		$user = $DB->fetch_one_array("SELECT userid,username,logincount,groupid,password FROM {$db_prefix}users WHERE username='$username' LIMIT 1");
		if ($user['userid'] && $user['password'] == $password) {
			$sax_uid = $user['userid'];
			$logincount = $user['logincount']+1;
			$DB->unbuffered_query("UPDATE {$db_prefix}users SET logincount=logincount+1, logintime='$timestamp', loginip='$onlineip' WHERE userid='$sax_uid'");
			//保存COOKIE
			scookie('sax_auth', authcode("$sax_uid\t$password\t$logincount"), $login_life);
			//更新数据库中的登陆会话
			if ($user['groupid'] == 1 || $user['groupid'] == 2) {
				loginresult($username,'Succeed');
			}
			wap_message('登陆成功', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
		} else {
			replacesession();
			if ($user['groupid'] == 1 || $user['groupid'] == 2 || $sax_group == 1 || $sax_group == 2) {
				loginresult($username,'Failed');
			}
			$sax_hash = '';
			dcookies();
			wap_message('登陆失败', array('title' => '重新登陆', 'link' => 'index.php?action=login'));
		}
	}
	wap_footer();
}

// 添加评论
if ($action == 'addcomment') {
	wap_header('添加评论');
	if ($options['close_comment']) {
		wap_message('禁止发表评论', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
	}
	$articleid = (int)$articleid;
	if (!$articleid) {
		wap_message('缺少必要参数', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
	} else {
		$article = $DB->fetch_one_array("SELECT title, closecomment FROM {$db_prefix}articles WHERE articleid='$articleid'");
		if ($article['closecomment']) {
			wap_message('本文不允许发表评论', array('title' => '返回日志列表', 'link' => 'index.php?action=article'));
		}
	}
	if($do == 'addcomment') {
		if ($options['banip_enable']) {
			$options['ban_ip'] = str_replace('，', ',', $options['ban_ip']);
			$ban_ips = explode(',', $options['ban_ip']);
			if (is_array($ban_ips) && count($ban_ips)) {
				foreach ($ban_ips AS $ban_ip) {
					$ban_ip = str_replace( '\*', '.*', preg_quote($ban_ip, "/") );
					if (preg_match("/^$ban_ip/", $onlineip)) {
						wap_message('您的IP已经被系统禁止发表评论.');
					}
				}
			}
		}
		//如果没有登陆
		if (!$sax_uid || !$sax_hash) {
			wap_message('只有登陆后才能发表评论.', array('title' => '立即登陆', 'link' => 'index.php?action=login'));
		} else {
			$username = $sax_user;
			$email = char_cv($sax_email);
			$url = char_cv($sax_url);
		}
		$content = sax_addslashes(trim($_POST['content'] ? $_POST['content'] : $_GET['content']))." \n\n<自 WAP 发表>";

		$spam = false;
		// 不属于撰写组以上的成员需要做审核检查
		if ($sax_group != 1 && $sax_group != 2) {
			// 检查限制选项
			if ($options['audit_comment']) {
				$spam = true;
			} else {
				//禁止IP
				if ($options['banip_enable'] && $options['ban_ip']) {
					$options['ban_ip'] = str_replace('，', ',', $options['ban_ip']);
					$ban_ips = explode(',', $options['ban_ip']);
					if (is_array($ban_ips) && count($ban_ips)) {
						foreach ($ban_ips as $ban_ip) {
							$ban_ip = str_replace( '\*', '.*', preg_quote($ban_ip, "/") );
							if (preg_match("/^$ban_ip/", $onlineip)) {
								wap_message('您的IP已经被系统禁止发表评论');
							}
						}
					}
				}
				if ($options['spam_enable']) {
					//链接次数
					if (substr_count($content, 'http://') >= $options['spam_url_num']) {
						$spam = true;
					}
					//禁止词语
					if ($options['spam_words']) {
						$options['spam_words'] = str_replace('，', ',', $options['spam_words']);
						$badwords = explode(',', $options['spam_words']);
						if (is_array($badwords) && count($badwords) ) {
							foreach ($badwords AS $n) {
								if ($n) {
									if (preg_match( "/".preg_quote($n, '/' )."/i", $content)) {
										$spam = true;
										break;
									}
								}
							}
						}
					}
					//内容长度
					if ($options['spam_content_size'] && getstrlen($content) >= $options['spam_content_size']) {
						$spam = true;
					}
				}
			}
		}

		$visible = $spam ? '0' : '1';
		if ($sax_group != 1 && $sax_group != 2) {
			if ($options['comment_post_space'] && $timestamp - $user['lastpost'] <= $options['comment_post_space']){
				wap_message('为防止灌水,发表评论时间间隔为'.$options['comment_post_space'].'秒.', array('title' => '重新发表', 'link' => 'index.php?action=addcomment&amp;articleid='.$articleid));
			}
		}

		$checkcontent = checkcontent($content);
		if($checkcontent){
			wap_message($checkcontent, array('title' => '重新发表', 'link' => 'index.php?action=addcomment&amp;articleid='.$articleid));
		}

		$r = $DB->fetch_one_array("SELECT commentid FROM {$db_prefix}comments WHERE articleid='$articleid' AND author='$username' AND content='$content'");
		if($r['commentid']) {
			wap_message('该评论已存在', array('title' => '重新发表', 'link' => 'index.php?action=addcomment&amp;articleid='.$articleid));
		}
		unset($r);
		$msg = '添加评论成功, '.($spam ? '目前发表评论需要管理员审核才会显示,请耐心等待管理员审核...' : '');
		$DB->query("INSERT INTO {$db_prefix}comments (articleid, author, url, dateline, content, ipaddress, visible) VALUES ('$articleid', '$username', '$url', '$timestamp', '$content', '$onlineip', '$visible')");
		$cmid = $DB->insert_id();
		if ($sax_uid && $sax_hash) {
			$DB->unbuffered_query("UPDATE {$db_prefix}users SET lastpost='$timestamp' WHERE userid='$sax_uid'");
		}
		if (!$spam) {
			// 更新当前文章评论数
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments+1 WHERE articleid='$articleid'");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count+1");
			newcomments_recache();
			statistics_recache();
		}
		wap_message($msg, array('title' => '查看评论', 'link' => 'index.php?action=comments&amp;articleid='.$articleid));
	} else {
		echo "<p>文章:<a href=\"index.php?action=show&amp;id=".$articleid."\">".$article['title']."</a></p>\n";
		if ($sax_uid && $sax_hash) {
			echo "<form name=\"loginform\" id=\"loginform\" action=\"\" method=\"post\">\n";
			echo "<p><label>评论内容:<br /><textarea rows=\"5\" cols=\"20\" name=\"content\"></textarea></label></p>\n";
			echo "<p><input type=\"submit\" value=\"确定\" /></p>\n";
			echo "<input type=\"hidden\" name=\"do\" value=\"addcomment\" />\n";
			echo "<input type=\"hidden\" name=\"articleid\" value=\"$articleid\" />\n";
			echo "</form>\n";
		} else {
			echo "<p>只有登陆后才能发表评论</p>\n";
			echo "<p><a href=\"index.php?action=login\">立即登陆</a></p>";
		}
	}
	wap_footer();
}

// 添加文章
if ($action == 'add') {
	wap_header("写文章");
	if ($sax_group == 1 || $sax_group == 2 && $sax_hash) {
		// 添加文章
		if($do == 'add') {
			$title   = sax_addslashes(trim($_POST['title']));
			$content = sax_addslashes($_POST['content']);
			$mids    = $_POST['mids'];

			$keywords = strtolower(sax_addslashes(trim($_POST['keywords'])));
			if ($keywords) {
				$keywords	= str_replace('，', ',', $keywords);
				$keywords	= str_replace(',,', ',', $keywords);
				if (substr($keywords, -1) == ',') {
					$keywords = substr($keywords, 0, getstrlen($keywords)-1);
				}
				$v = explode(',', $keywords);
				$v_num = count($v);
				if ($v_num > 10) {
					wap_message('关键字不能超过10个', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
				} else {
					for($i=0; $i<$v_num; $i++) {
						if(getstrlen($v[$i]) > 30) {
							wap_message('每个关键字不能超过30个字符', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
						}
					}
				}
			}

			if($title == '' || getstrlen($title) > 120) {
				wap_message('标题不能为空并且不能多于120个字节', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
			}
			if(!$mids) {
				wap_message('你还没有选择分类', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
			}
			if(!$content) {
				wap_message('内容不能为空', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
			}
			$title = char_cv($title);
			$r = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE title='$title'"), 0);
			if($r) {
				wap_message('数据库中已存在一样的标题了,建议您换一个', array('title' => '重新发表', 'link' => 'index.php?action=addarticle'));
			}
			// 插入数据部分
			$DB->query("INSERT INTO {$db_prefix}articles (uid, title, content, dateline) VALUES ('$sax_uid', '$title', '$content <br /><br /><span style=\"font-weight:bold;color:#4685C4;background-color:#E9F1F8;\">自 WAP 发表</span>', '$timestamp')");
			$articleid = $DB->insert_id();
			// 关联文章分类
			foreach ($mids as $mid) {
				$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='$mid' AND type='category'");
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
							$new_mid = insert_meta($tag,$slug,'tag',1);
							$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$new_mid')");
							$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET tag_count=tag_count+1");
						} else {
							$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '".$r['mid']."')");
							$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+1 WHERE mid='".$r['mid']."' AND type='tag'");
						}
					}
				}
			}
			$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles+1 WHERE userid='$sax_uid'");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count+1");
			archives_recache();
			categories_recache();
			statistics_recache();
			newarticles_recache();
			getlog();
			wap_message('添加文章成功', array('title' => '查看文章', 'link' => 'index.php?action=show&amp;id='.$articleid));
		} else {
			echo "<form name=\"loginform\" id=\"loginform\" action=\"\" method=\"post\">\n";
			echo "<p><label>标题:<br /><input type=\"text\" name=\"title\" value=\"\" /></label></p>\n";
			echo "<p>分类:<br />\n";
			$query = $DB->query("SELECT mid,name FROM {$db_prefix}metas WHERE type='category' ORDER BY displayorder");
			while ($cate = $DB->fetch_array($query)) {
				echo "<input type=\"checkbox\" name=\"mids[]\" value=\"".$cate['mid']."\" /> ".$cate['name']."<br />\n";
			}
			echo "</p>\n";
			echo "<p><label>关键字(用,隔开):<br /><input type=\"text\" name=\"keywords\" value=\"\" /></label></p>\n";
			echo "<p><label>内容:<br /><textarea rows=\"5\" cols=\"20\" name=\"content\"></textarea></label></p>\n";
			echo "<p><input type=\"submit\" value=\"发表\" /></p>\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"add\" />\n";
			echo "<input type=\"hidden\" name=\"do\" value=\"add\" />\n";
			echo "</form>\n";
		}
	} else {
		wap_message('你没有权限进行此操作');
	}
	wap_footer();
}

// 管理评论
if ($action == 'editcomment') {
	wap_header("管理评论");
	if ($sax_group == 1 && $sax_hash) {
		$commentid = (int)$commentid;
		// 获取文章信息
		if (!$commentid) {
			wap_message('缺少参数');
		} else {
			$comment = $DB->fetch_one_array("SELECT author,articleid,content FROM {$db_prefix}comments WHERE commentid='$commentid'");
			if (!$comment) {
				wap_message('记录不存在');
			}
		}
		if($act == 'edit') {
			$do = in_array($do, array('hidden', 'delete')) ? $do : 'hidden';
			if($do == 'hidden') {
				$DB->query("UPDATE {$db_prefix}comments SET visible='0' WHERE commentid='$commentid'");
				$msg = '评论已隐藏';
			} else {
				$DB->query("DELETE FROM {$db_prefix}comments WHERE commentid='$commentid'");
				$msg = '评论已删除';
			}
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments-1 WHERE articleid='".$comment['articleid']."'");
			$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count-1");
			newcomments_recache();
			statistics_recache();
			getlog();
			wap_message($msg, array('title' => '返回评论列表', 'link' => "index.php?action=comments&amp;articleid=".$comment['articleid']));
		} else {
			echo "<form name=\"loginform\" id=\"loginform\" action=\"\" method=\"post\">\n";
			echo "<p>作者:".$comment['author']."</p>\n";
			echo "<p>内容:".wap_html_clean($comment['content'])."</p>\n";

			echo "<p><label>操作:<br /><select name=\"do\">\n";
			echo "<option value=\"hidden\">隐藏</option>\n";
			echo "<option value=\"delete\">删除</option>\n";
			echo "</select></label></p>\n";
			echo "<p><input type=\"submit\" value=\"确定\" /></p>\n";
			echo "<input type=\"hidden\" name=\"act\" value=\"edit\" />\n";
			echo "<input type=\"hidden\" name=\"commentid\" value=\"$commentid\" />\n";
			echo "</form>\n";
		}
	} else {
		wap_message('你没有权限进行此操作');
	}
	wap_footer();
}

// 审核评论列表
if ($action == 'auditcm' && ($sax_group == '1' || $sax_group == '2') && $sax_hash) {
	wap_header('审核评论');

	$total = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE visible='0'"), 0);

	if ($total) {
		$pagenum = 5;
		if($page) {
			$orderid = ($page - 1) * $pagenum;
			$start_limit = ($page - 1) * $pagenum;
		} else {
			$orderid = 0;
			$start_limit = 0;
			$page = 1;
		}
		$multipage = multi($total, $pagenum, $page, 'index.php?action=auditcm');
		$sqladd = '';
		if ($sax_group == '2') {
			$sqladd = " AND a.uid='$sax_uid'";
		}
		$query = $DB->query("SELECT c.articleid, c.author, c.commentid, c.dateline, c.content, a.title FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.visible='1' AND c.visible='0' $sqladd ORDER BY commentid DESC LIMIT $start_limit, $pagenum");

		echo "<ul>\n";
		while ($comment=$DB->fetch_array($query)) {
			$orderid++;
			echo "<li>\n";
			echo "<p><strong>{$orderid}.</strong>文章:<a href=\"index.php?action=show&amp;id=".$comment['articleid']."\">".$comment['title']."</a></p>\n";
			echo "<p>".$comment['author']." : ".sadate('Y-m-d H:i',$comment['dateline'],1)." [<a href=\"index.php?action=auditcm_ok&amp;commentid=".$comment['commentid']."\">显示</a>]</p>\n";
			echo "<p>".wap_html_clean($comment['content'])."</p>\n";
			echo "<br />\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
		unset($comment);
		echo "<p>共有".$total."条隐藏评论</p>\n";
		echo $multipage;
		$DB->free_result($query);
	} else {
		echo "<p>没有需要审核的评论</p>\n";
	}

	wap_footer();
}

// 审核评论操作
if ($action == 'auditcm_ok' && ($sax_group == '1' || $sax_group == '2') && $sax_hash) {
	wap_header('审核评论');
	$commentid = (int)$commentid;
	// 获取文章信息
	if (!$commentid) {
		wap_message('缺少参数');
	}

	$comment = $DB->fetch_one_array("SELECT c.articleid, a.uid FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE c.commentid='$commentid'");

	if (!$comment) {
		wap_message('记录不存在');
	}

	if ($sax_group == '2') {
		if ($comment['uid'] != $sax_uid) {
			wap_message('此评论不属于您发表的文章');
		}
	}

	$DB->query("UPDATE {$db_prefix}comments SET visible='1' WHERE commentid='$commentid'");

	$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments+1 WHERE articleid='".$comment['articleid']."'");
	$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count+1");
	newcomments_recache();
	statistics_recache();
	getlog();
	wap_message('评论已显示', array('title' => '返回隐藏评论列表', 'link' => 'index.php?action=auditcm'));
}

?>