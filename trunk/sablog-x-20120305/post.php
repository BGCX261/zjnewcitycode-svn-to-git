<?php
// ========================== 文件说明 ==========================//
// 本文件说明：提交数据操作
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('global.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || $_POST['formhash'] != formhash() || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
	message('您的请求来路不正确,无法提交');
}

//添加评论
if($_POST['action'] == 'addcomment') {
	if ($options['close_comment']) {
		message('禁止发表评论', $referer);
	}
	$comment_parent = (int)$_POST['comment_parent'];
	$articleid = (int)$_POST['articleid'];
	$username = trim($_POST['username']);
	$password = $_POST['password'];
	$email = trim($_POST['email']);
	$url = trim($_POST['url']);
	$content = sax_addslashes(trim($_POST['content']));
	//把评论内容保存到cookie里以免丢失
	scookie('cmcontent', $content, $login_life);

	if (!$articleid) {
		message('缺少必要参数', $options['url']);
	}

	$article = $DB->fetch_one_array("SELECT dateline,alias,closecomment FROM {$db_prefix}articles WHERE articleid='$articleid'");

	$article['url'] = redirect_permalink($articleid, $article['alias']);

	
	if($comment_parent != 0) {
		$comment = $DB->fetch_one_array("SELECT commentid, comment_parent, articleid, author, email FROM {$db_prefix}comments WHERE commentid='$comment_parent'");
		if (!$comment) {
			message('要回复的评论不存在', $options['url']);
		}
		$content = '@' . addslashes($comment['author']) . ': ' . $content;
		
		if ($options['url']) {
			//发送邮件通知
			include_once(SABLOG_ROOT.'include/class/mail.class.php');

			$fromname = $fromname ? $fromname : substr ($sax_email, 0, strpos ($sax_email, "@"));
			$fromdomain = strstr($sax_email, '@');

			$m = new MailSend();
			$m->setToaddr($comment['email']);
			$m->setSubject('有人在'.$options['name'].'回复了您的评论');
			$m->setContent('有人在'.$options['name'].'回复了您的评论, 查看内容链接: '.$article['url']);
			$m->setFromaddr($fromname.' <'.$sax_email.'>');
			$m->setDomain($fromdomain);
			$m->send();
		}
	}

	if ($article['closecomment']) {
		message('本文不允许发表评论', $article['url']);
	}

	if ($options['seccode'] && $sax_group != 1 && $sax_group !=2) {
		$clientcode = $_POST['clientcode'];
		include_once(SABLOG_ROOT.'include/class/seccode.class.php');
		$code = new seccode();
		$code->seccodeconvert($_SESSION['seccode']);
		if (!$clientcode || strtolower($clientcode) != strtolower($_SESSION['seccode'])) {
			$_SESSION['seccode'] = random(6, 1);
			message('验证码错误,请返回重新输入.', $options['url'].'cp.php?action=login');
		}
	}

	//如果没有登陆
	if (!$sax_uid || !$sax_pw || !$sax_logincount) {
		if(!$username || getstrlen($username) > 30) {
			message('用户名为空或用户名太长');
		}
		$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
		foreach($name_key as $value){
			if (strpos($username,$value) !== false){
				message('此用户名包含不可接受字符或被管理员屏蔽,请选择其它用户名');
			}
		}

		if (!isemail($email)) {
			message('E-mail地址错误');
		}
		if (!isurl($url)) {
			message('网站URL错误', $article['url'].'#addcomment');
		}

		$username = char_cv($username);

		if ($options['censoruser']) {
			$options['censoruser'] = str_replace('，', ',', $options['censoruser']);
			$banname=explode(',',$options['censoruser']);
			foreach($banname as $value){
				if (strpos($username,$value) !== false){
					message('此用户名包含不可接受字符或被管理员屏蔽.', $article['url'].'#addcomment');
				}
			}
		}

		$r = $DB->fetch_one_array("SELECT userid FROM {$db_prefix}users WHERE username='$username' LIMIT 1");
		if($r['userid']) {
			message('该用户名已存在,如果是您注册的,请先登陆.');
		}

		//把用户名和URL信息保存到cookie
		scookie('comment_username',$username,$login_life);
		scookie('comment_email',$email,$login_life);
		scookie('comment_url',$url,$login_life);
		$email = char_cv($email);
		$url = char_cv($url);

	} else {
		$username = $sax_user;
		$email = char_cv($sax_email);
		$url = char_cv($sax_url);
	}

	$spam = false;
	// 不属于撰写组以上的成员需要做审核检查
	if ($sax_group != 1 && $sax_group != 2) {
		// 检查限制选项
		if ($options['audit_comment']) {
			$spam = true;
		} elseif ($options['spam_enable']) {
			//链接次数
			if (substr_count($content, 'http://') >= $options['spam_url_num']) {
				$spam = true;
			}
			//禁止词语
			if ($options['spam_words']) {
				$options['spam_words'] = str_replace('，', ',', $options['spam_words']);
				$badwords = explode(',', $options['spam_words']);
				if (is_array($badwords) && count($badwords) ) {
					foreach ($badwords as $n) {
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
		} else {
			$spam = false;
		}

		//禁止IP
		if ($options['banip_enable'] && $options['ban_ip']) {
			$options['ban_ip'] = str_replace('，', ',', $options['ban_ip']);
			$ban_ips = explode(',', $options['ban_ip']);
			if (is_array($ban_ips) && count($ban_ips)) {
				foreach ($ban_ips AS $ban_ip) {
					$ban_ip = str_replace( '\*', '.*', preg_quote($ban_ip, "/") );
					if (preg_match("/^$ban_ip/", $onlineip)) {
						message('您的IP已经被系统禁止发表评论');
					}
				}
			}
		}

	}

	$visible = $spam ? '0' : '1';

	if ($sax_group != 1 && $sax_group != 2) {
		$lastposttime = $user['lastpost'] ? $user['lastpost'] : $_COOKIE['comment_post_time'];
		if ($options['comment_post_space'] && $timestamp - $lastposttime <= $options['comment_post_space'] && $sax_group != 1){
			message('为防止灌水,发表评论时间间隔为'.$options['comment_post_space'].'秒.', $article['url'].'#addcomment');
		}
	}

	$checkcontent = checkcontent($content);
	if ($checkcontent) {
		message($checkcontent, $article['url'].'#addcomment');
	}

    $r = $DB->fetch_one_array("SELECT commentid FROM {$db_prefix}comments WHERE articleid='$articleid' AND author='$username' AND content='$content' LIMIT 1");
    if($r['commentid']) {
		message('该评论已存在');
    }

    $DB->query("INSERT INTO {$db_prefix}comments (comment_parent, articleid, author, email, url, dateline, content, ipaddress, visible) VALUES ('$comment_parent', '$articleid', '$username', '$email', '$url', '$timestamp', '$content', '$onlineip', '$visible')");
	$cmid = $DB->insert_id();
	if ($sax_uid) {
		$DB->unbuffered_query("UPDATE {$db_prefix}users SET lastpost='$timestamp' WHERE userid='$sax_uid'");
		// 更新用户最后发表时间
	}
	if (!$spam) {
		// 如果不是垃圾则更新当前文章评论数
		$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments=comments+1 WHERE articleid='$articleid'");
		$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET comment_count=comment_count+1");
		newcomments_recache();
		statistics_recache();
	}
	scookie('comment_post_time',$timestamp);
	// 跳转到最新发表的评论
	if ($comment_parent) {
		$gocommentid = get_comment_parent($comment_parent);
	} else {
		$gocommentid = $cmid;
	}
	$cmnum = '#cm'.$gocommentid;
	$article_comment_num = (int)$options['article_comment_num'];
	if ($article_comment_num) {
		$cpost = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE articleid='$articleid' AND visible='1' AND commentid<='$gocommentid' AND comment_parent='0'"), 0);
		if (($cpost / $article_comment_num) <= 1 ) {
			$page = 1;
		} else {
			$page = @ceil($cpost / $article_comment_num);
			$article['url'] = redirect_permalink($articleid, $article['alias'], $page);
		}
	} else {
		$page = 1;
	}
	if ($spam) {
		message('添加评论成功,目前发表评论需要管理员审核才会显示,请耐心等待管理员审核.', $article['url']);
	}

	dcookies('comment_username');
	dcookies('comment_email');
	dcookies('comment_url');
	dcookies('cmcontent');

	if ($options['comment_order']) { //新评论靠后排序
		if ($options['showmsg']) {
			message('添加评论成功.', $article['url'].$cmnum);
		} else {
			@header('Location: '.$article['url'].$cmnum);
			exit;
		}
	} else {
		if ($options['showmsg']) {
			message('添加评论成功.', $article['url'].'#comment');
		} else {
			@header('Location: '.$article['url'].'#comment');
			exit;
		}
	}
}

//搜索
if ($_POST['action'] == 'search') {
	$keywords = sax_addslashes(trim($_POST['keywords'] ? $_POST['keywords'] : $_GET['keywords']));
	if (!$keywords) {
		message('您没有指定要搜索的关键字.');
	} else {
		if(getstrlen($keywords) < 2) {
			message('关键字不能少于2个字节.');
		}

		$mids = $_POST['mids'];
		//分类
		$catearray = array();
		if($mids) {
			foreach($mids as $mid) {
				if($mid = intval(trim($mid))) {
					$catearray[] = $mid;
				}
			}
		}
		$cids = $comma = '';
		foreach($catecache as $data) {
			if(!$catearray || in_array($data['mid'], $catearray)) {
				$cids .= $comma.intval($data['mid']);
				$comma = ',';
			}
		}

		$searchin = ($_POST['searchin'] == 'title') ? 'title' : 'content';

		$searchstring = sax_addslashes($keywords).'|'.sax_addslashes($searchin).'|'.sax_addslashes($cids);

		$searchindex = array('id' => 0, 'dateline' => '0');
		$query = $DB->query("SELECT searchid, dateline,
			(".($sax_uid ? "uid='$sax_uid'" : "ipaddress='$onlineip'")." AND $timestamp-dateline<20) AS flood, (searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
			FROM {$db_prefix}searchindex
			WHERE (".($sax_uid ? "uid='$sax_uid'" : "ipaddress='$onlineip'")." AND $timestamp-dateline<20) OR (searchstring='$searchstring' AND expiration>'$timestamp') ORDER BY flood");

		while($index = $DB->fetch_array($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($index['flood'] && $sax_group != 1 &&  $sax_group != 2) {
				message('对不起,您在 20 秒内只能进行一次搜索.');
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
			foreach(explode("+", $keywords) AS $text) {
				$text = trim($text);
				if($text) {
					$sqltxtsrch .= $andor;
					$sqltxtsrch .= ($_POST['searchin'] == 'content') ? "(content LIKE '%".$text."%' OR description LIKE '%".$text."%' OR title LIKE '%".$text."%')" : "title LIKE '%".$text."%'";
				}
			}

			$query_sql = "SELECT ".($_POST['searchin'] == 'content' ? 'DISTINCT' : '')." articleid FROM {$db_prefix}articles WHERE visible='1'";

			$aids = '';
			if ($cids) {
				$aids = get_cids($cids);
				$query_sql .= " AND articleid IN ($aids)";
			}

			$query_sql .= " AND ($sqltxtsrch) ORDER BY dateline DESC LIMIT 500"; //搜索500个出来足够了.保证效率.反正一般BLOG也没有多少数据.

			$totals = 0;
			$ids = $comma = '';
			$query = $DB->query($query_sql);
			while($article = $DB->fetch_array($query)) {
				$ids .= $comma.$article['articleid'];
				$comma = ',';
				$totals++;
			}

			$DB->free_result($query);

			$DB->query("INSERT INTO {$db_prefix}searchindex (keywords, searchstring, dateline, expiration, totals, ids, ipaddress, uid) VALUES ('".char_cv($keywords)."', '$searchstring', '$timestamp', '".($timestamp+3600)."', '$totals', '$ids', '$onlineip', '$sax_uid')");
			$searchid = $DB->insert_id();

		}

		$gourl = getsearchlink($searchid);

		if ($options['showmsg']) {
			message('搜索成功完成,现在将转入结果页面.', $gourl);
		} else {
			$gourl = str_replace("&amp;", "&", $gourl);
			@header("Location: ".$gourl);
			exit;
		}
	}
}

message('未定义操作', $referer);


// 检查用户提交内容合法性
function checkcontent($content) {
	global $options;
    if(empty($content)) {
        $result .= '内容不能为空.<br />';
        return $result;
	}
	if(getstrlen($content) < $options['comment_min_len'] || getstrlen($content) > $options['comment_max_len']) {
        $result .= '内容不能少于'.$options['comment_min_len'].'字节,并且不能超过'.$options['comment_max_len'].'字节.<br />';
        return $result;
	}
}

?>