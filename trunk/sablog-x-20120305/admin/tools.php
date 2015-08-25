<?php
// ========================== 文件说明 ==========================//
// 本文件说明：程序维护管理操作
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

//权限检查
permission(1);

!$action && $action = 'mysqlinfo';

$backupdir = SABLOG_ROOT.'data/backupdata';
$location = '';

$tables = array(
	$db_prefix.'articles',
	$db_prefix.'attachments',
	$db_prefix.'comments',
	$db_prefix.'links',
	$db_prefix.'metas',
	$db_prefix.'relationships',
	$db_prefix.'searchindex',
	$db_prefix.'settings',
	$db_prefix.'statistics',
	$db_prefix.'stylevars',
	$db_prefix.'users'
);

if ($message) {
	$messages = array(
		1 => '只能恢复SQL文件',
		2 => '分卷数据成功导入数据库',
		3 => '分卷数据导入数据库失败',
		4 => '数据成功导入',
		5 => '数据文件非 Sablog-X 格式或与程序当前版本信息不符,无法导入.',
		6 => '不允许在后台恢复数据',
		7 => '您没有输入备份文件名或文件名中使用了敏感的扩展名',
		8 => '数据文件无法备份到服务器,请检查目录属性.',
		9 => '数据成功备份至服务器指定文件中',
		10 => '请选择目标分类',
		11 => '请选择文章作者',
		12 => '上传XML文件发生意外错误',
		13 => sprintf('导入RSS数据成功, <a href="cp.php?job=article&amp;mid=%d">查看刚才导入的文章</a>', $mid),
		14 => '只允许上传XML格式的文件',
		15 => '请选择要上传的XML文件',
		16 => '所有缓存已经更新',
		17 => '首页统计已经更新',
		18 => '成功重建所有分类文章数量',
		19 => '成功重建所有文章数据',
		20 => '成功重建所有后台用户数据',
		21 => '成功重建所有附件缩略图',
		22 => sprintf('删除选择的 %d 个数据文件,成功 %d 个,失败 %d 个.', $selected, $succ, $fail),
		23 => 'SQL文件有可能是当前程序的老版本备份的,为了程序程序正常运作,不允许导入.确实要导入,请通过其他MYSQL管理程序导入.',
		24 => '为了程序正常运作,只允许导入卷号为1的SQL文件.',
		25 => 'SQL文件版本信息和当前程序版本不匹配,为了程序程序正常运作,不允许导入.确实要导入,请通过其他MYSQL管理程序导入.',
		26 => sprintf('多余的 %s 已成功删除', $opname),
		27 => '记录少于100条不允许删除',
	);
}

if (in_array($action, array('resume','checkresume')) && !$dbimport) {
	$location = getlink('tools', 'filelist', array('message'=>6));
	header("Location: {$location}");
	exit;
}

// 恢复数据库文件
if ($action == 'resume' && $dbimport) {

	$file = $backupdir.'/'.$sqlfile;

	$path_parts = pathinfo($file);
	if (strtolower($path_parts['extension']) != 'sql') {
		$location = getlink('tools', 'filelist', array('message'=>1));
	} else {
		if(@$fp = fopen($file, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$sqldump .= fread($fp, filesize($file));
			fclose($fp);
			$continue = 1;
		} else {
			$continue = 0;
			if($autoimport) {
				restats();
				$location = getlink('tools', 'filelist', array('message'=>2));
			} else {
				$location = getlink('tools', 'filelist', array('message'=>3));
			}
		}
	
		if ($continue) {
			if($identify[0] && $identify[1] == $SABLOG_VERSION && $identify[2]) {
				$sqlquery = splitsql($sqldump);
				unset($sqldump);
				foreach($sqlquery as $sql) {
					if(trim($sql) != '') {
						$DB->query($sql, 'SILENT');
					}
				}

				$file_next = basename(preg_replace("/_($identify[2])(\..+)$/", "_".($identify[2] + 1)."\\2", $file));

				if($identify[2] == 1) {
					redirect('分卷数据成功导入数据库,程序将自动导入本次其他的备份.','cp.php?job=tools&action=resume&sqlfile='.rawurlencode($file_next).'&autoimport=yes');
				} elseif($autoimport) {
					redirect('数据文件卷号 '.$identify[2].' 成功导入，程序将自动继续。', 'cp.php?job=tools&action=resume&sqlfile='.rawurlencode($file_next).'&autoimport=yes');
				} else {
					restats();
					$location = getlink('tools', 'filelist', array('message'=>4));
				}
			} else {
				$location = getlink('tools', 'filelist', array('message'=>5));
			}
		}
	}
	header("Location: {$location}");
	exit;
}

// 备份操作
if ($action == 'dobackup') {
	$volume = intval($volume) + 1;
	$sqlfilename = $backupdir.'/'.$filename.'_'.$volume.'.sql';

	if(!$filename || preg_match("/(\.)(exe|jsp|asp|asa|htr|stm|shtml|php3|aspx|cgi|fcgi|pl|php|bat)(\.|$)/i", $sqlfilename)) {
		$location = getlink('tools', 'backup', array('message'=>7));
	} else {

		$idstring = '# Identify: '.base64_encode("$timestamp,$SABLOG_VERSION,$volume")."\n";

		//清除表内临时的数据
		$DB->unbuffered_query("TRUNCATE TABLE {$db_prefix}searchindex");

		$sqlcompat = in_array($sqlcompat, array('MYSQL40', 'MYSQL41')) ? $sqlcompat : '';
		$setnames = intval($addsetnames) || ($DB->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary;\n\n" : '';

		if($DB->version() > '4.1') {
			$DB->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary;");
			if($sqlcompat == 'MYSQL40') {
				$DB->query("SET SQL_MODE='MYSQL40'");
			}
		}

		$sqldump = '';
		$tableid = $tableid ? $tableid - 1 : 0;
		$startfrom = (int)$startfrom;
		for($i = $tableid; $i < count($tables) && getstrlen($sqldump) < $sizelimit * 1000; $i++) {
			$sqldump .= sqldumptable($tables[$i], $startfrom, getstrlen($sqldump));
			$startfrom = 0;
		}
		$tableid = $i;
		if(trim($sqldump)) {
			$sqldump = "$idstring".
				"# <?exit();?>\n".
				"# Sablog-X bakfile Multi-Volume Data Dump Vol.$volume\n".
				"# Version: $SABLOG_VERSION\n".
				"# Time: ".sadate('Y-m-d H:i')."\n".
				"# Sablog-X: http://www.sablog.net\n".
				"# --------------------------------------------------------\n\n\n".$setnames.$sqldump;

			if(!writefile($sqlfilename, $sqldump)) {
				$location = getlink('tools', 'backup', array('message'=>8));
			} else {
				redirect('分卷备份:数据文件 '.$volume.' 成功创建,程序将自动继续.', "cp.php?job=tools&action=dobackup&filename=".rawurlencode($filename)."&sizelimit=".rawurlencode($sizelimit)."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&sqlcompat=".rawurlencode($sqlcompat));
			}
		} else {
			$location = getlink('tools', 'filelist', array('message'=>9));
		}
	}
	header("Location: {$location}");
	exit;
}
// 备份操作结束

// 导入RSS
if ($action == 'importrss') {
	$mid = (int)$mid;
	$uid = (int)$uid;
	$us[$uid] = 'selected';
	$ms[$mid] = 'selected';

	if (!$mid) {
		$location = getlink('tools', 'rssimport', array('message'=>10));
	} elseif (!$uid) {
		$location = getlink('tools', 'rssimport', array('message'=>11));
	} else {
		$xmlfile = $_FILES['xmlfile'];
		if (is_array($xmlfile)) {
			$attachment      = $xmlfile['tmp_name'];
			$attachment_name = $xmlfile['name'];
			$attachment_size = $xmlfile['size'];
			$attachment_type = $xmlfile['type'];
		}
		if (trim($attachment) != 'none' && trim($attachment) != '' && trim($attachment_name) != '') {
			$rssinfo = pathinfo($attachment_name);
			if ($rssinfo['extension'] == 'xml') {
				$attachment = upfile($attachment, SABLOG_ROOT.'data/cache/rss_xml_tmp.xml');
				// 如果一种函数上传失败，还可以用其他函数上传
				if (!$attachment) {
					$message = '上传XML文件发生意外错误';
					$location = getlink('tools', 'rssimport', array('message'=>12));
				} else {
					$filecontent = loadfile($attachment);

					$rssdata = getrssdata($filecontent);
					$i = 0;
					if (is_array($rssdata)) {
						foreach ($rssdata as $rss) {
							if ($rss['title'] && $rss['dateline'] && $rss['content']) {
								$i++;
								$DB->query("INSERT INTO {$db_prefix}articles (uid, title, content, dateline) VALUES ('$uid', '".$rss['title']."', '".$rss['content']."', '".$rss['dateline']."')");
								$articleid = $DB->insert_id();
								$DB->query("INSERT INTO {$db_prefix}relationships (cid, mid) VALUES ('$articleid', '$mid')");
							}
						}
					}
					@unlink($attachment);

					$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles=articles+$i WHERE userid='$uid'");
					$DB->unbuffered_query("UPDATE {$db_prefix}metas SET count=count+$i WHERE mid='$mid' AND type='category'");
					$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count=article_count+$i");
					archives_recache();
					categories_recache();
					statistics_recache();
					$location = getlink('tools', 'rssimport', array('message'=>13, 'mid'=>$mid));
				}
			} else {
				$location = getlink('tools', 'rssimport', array('message'=>14));
			}
		} else {
			$location = getlink('tools', 'rssimport', array('message'=>15));
		}
	}

	header("Location: {$location}");
	exit;

}

if ($action == 'updateall') {
	restats();
	$location = getlink('tools', 'cache', array('message'=>16));
	header("Location: {$location}");
	exit;
}

// 更新首页统计
if ($action == 'dostatsdata') {
	// 更新首页显示的文章数
	$article_count = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE visible = '1'"), 0);
	// 更新首页显示的评论数
	$comment_count = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments c LEFT JOIN {$db_prefix}articles a ON (a.articleid=c.articleid) WHERE a.visible='1' AND c.visible='1'"), 0);
	// 更新首页显示的标签(Tags)数
	$tag_count = $DB->result($DB->query("SELECT COUNT(mid) FROM {$db_prefix}metas WHERE type='tag'"), 0);

	$DB->unbuffered_query("UPDATE {$db_prefix}statistics SET article_count='$article_count',comment_count='$comment_count',tag_count='$tag_count'");
	
	//$DB->unbuffered_query("DELETE FROM {$db_prefix}metas WHERE type='tag' AND count=0");

	statistics_recache();
	$location = getlink('tools', 'rebuild', array('message'=>17));
	header("Location: {$location}");
	exit;
}
// 更新所有分类的文章数
if ($action == 'dometadata') {
	$step = (!$step) ? 1 : $step;
	$percount = ($percount <= 0) ? 100 : $percount;
	$start    = ($step - 1) * $percount;
	$next     = $start + $percount;
	$step++;
	$jumpurl  = 'cp.php?job=tools&action=dometadata&step='.$step.'&percount='.$percount;
	$goon     = 0;
	$query = $DB->query("SELECT mid,count FROM {$db_prefix}metas LIMIT $start, $percount");
	while ($meta = $DB->fetch_array($query)) {
		$goon = 1;
		$ctotal = get_meta_article_count($meta['mid']);
		if ($meta['count'] != $ctotal) {
			update_meta_count($meta['mid'], $ctotal);
		}
	}
	if($goon){
		redirect('正在更新 '.$start.' 到 '.$next.' 项', $jumpurl, '2');
	} else{
		$DB->unbuffered_query("DELETE FROM {$db_prefix}relationships WHERE mid='0'");
		categories_recache();
		$location = getlink('tools', 'rebuild', array('message'=>18));
		header("Location: {$location}");
		exit;
	}
}
// 重建文章数据
if ($action == 'doarticledata') {
	$step = (!$step) ? 1 : $step;
	$percount = ($percount <= 0) ? 100 : $percount;
	$start    = ($step - 1) * $percount;
	$next     = $start + $percount;
	$step++;
	$jumpurl  = 'cp.php?job=tools&action=doarticledata&step='.$step.'&percount='.$percount;
	$goon     = 0;
	$query = $DB->query("SELECT articleid,comments,attachments FROM {$db_prefix}articles LIMIT $start, $percount");
	while ($article = $DB->fetch_array($query)) {
		$goon = 1;
		// 更新所有文章的评论数
		$ctotal = $DB->result($DB->query("SELECT COUNT(commentid) FROM {$db_prefix}comments WHERE articleid='".$article['articleid']."' AND visible='1'"), 0);
		$atotal = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments WHERE articleid='".$article['articleid']."'"), 0);
		if ($article['comments'] != $ctotal || $article['attachments'] != $atotal) {
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET comments='$ctotal', attachments='$atotal' WHERE articleid='".$article['articleid']."'");
		}
	}
	if($goon){
		redirect('正在更新 '.$start.' 到 '.$next.' 项', $jumpurl, '2');
	} else{
		$location = getlink('tools', 'rebuild', array('message'=>19));
		header("Location: {$location}");
		exit;
	}
}
// 重建后台用户文章数量
if ($action == 'doadmindata') {
	$query = $DB->query("SELECT userid,articles FROM {$db_prefix}users WHERE groupid='1' OR groupid='2'");
	while ($user = $DB->fetch_array($query)) {
		$total = $DB->result($DB->query("SELECT COUNT(articleid) FROM {$db_prefix}articles WHERE visible='1' AND uid='".$user['userid']."'"), 0);
		if ($user['articles'] != $total) {
			$DB->unbuffered_query("UPDATE {$db_prefix}users SET articles='$total' WHERE userid='".$user['userid']."'");
		}
	}
	$location = getlink('tools', 'rebuild', array('message'=>20));
	header("Location: {$location}");
	exit;
}

// 重建附件缩略图
if ($action == 'dothumbdata') {
	require_once(SABLOG_ROOT.'include/func/attachment.func.php');
	$step = (!$step) ? 1 : $step;
	$percount = ($percount <= 0) ? 100 : $percount;
	$start    = ($step - 1) * $percount;
	$next     = $start + $percount;
	$step++;
	$jumpurl  = 'cp.php?job=tools&action=dothumbdata&step='.$step.'&percount='.$percount;
	$goon     = 0;
	$size = explode('x', strtolower($options['attachments_thumbs_size']));

	$attachquery = $DB->query("SELECT * FROM {$db_prefix}attachments WHERE isimage='1' AND thumb_filepath <> '' LIMIT $start, $percount");
	while($attach = $DB->fetch_array($attachquery)) {
		$goon = 1;
		if (file_exists(SABLOG_ROOT.$options['attachments_dir'].$attach['thumb_filepath'])) {
			@unlink(SABLOG_ROOT.$options['attachments_dir'].$attach['thumb_filepath']);
			$DB->unbuffered_query("UPDATE {$db_prefix}attachments SET thumb_filepath='', thumb_width='', thumb_height='' WHERE attachmentid='".$attach['attachmentid']."'");
		}
		if (!$options['attachments_thumbs']) {
			$attach_data['thumbwidth']    = '';
			$attach_data['thumbheight']   = '';
			$attach_data['thumbfilepath'] = '';
		} else {
			$extension = getextension($attach['filepath']);
			$attachsubdir = '/date_'.sadate('Ym', $attach['dateline']).'/';
			$thumbname = substr($attach['filepath'],getstrlen($attachsubdir),32);
			if ($imginfo=@getimagesize(SABLOG_ROOT.$options['attachments_dir'].$attach['filepath'])) {
				if ($imginfo[2]) {
					if (($imginfo[0] > $size[0]) || ($imginfo[1] > $size[1])) {
						$attach_thumb = array(
							'filepath'     => SABLOG_ROOT.$options['attachments_dir'].$attach['filepath'],
							'filename'     => $thumbname,
							'extension'    => $extension,
							'attachsubdir' => $attachsubdir,
							'thumbswidth'  => $size[0],
							'thumbsheight' => $size[1],
						);
						$thumb_data = generate_thumbnail($attach_thumb);
						$attach_data['thumbwidth']    = $thumb_data['thumbwidth'];
						$attach_data['thumbheight']   = $thumb_data['thumbheight'];
						$attach_data['thumbfilepath'] = $attachsubdir.$thumb_data['thumbfilepath'];
					}
				}
			}
		}
		$DB->unbuffered_query("UPDATE {$db_prefix}attachments SET thumb_filepath='".$attach_data['thumbfilepath']."', thumb_width='".$attach_data['thumbwidth']."', thumb_height='".$attach_data['thumbheight']."' WHERE attachmentid='".$attach['attachmentid']."'");

		unset($attach_data);
	}
	if($goon){
		redirect('正在更新 '.$start.' 到 '.$next.' 项', $jumpurl, '2');
	} else{
		$location = getlink('tools', 'rebuild', array('message'=>21));
		header("Location: {$location}");
		exit;
	}
}

//批量删除备份文件
if($action == 'deldbfile') {
	$sqlfile = $_POST['selectall'];
    if (!$sqlfile || !is_array($sqlfile)) {
		$message = '未选择任何文件';
    } else {
		$selected = count($sqlfile);
		$succ = $fail = 0;
		foreach ($sqlfile as $file) {
			if (file_exists($file)) {
				@chmod($file, 0777);
				if (@unlink($file)) {
					$succ++;
				} else {
					$fail++;
				}
			} else {
				$fail++;
			}
		}
		$location = getlink('tools', 'filelist', array('message'=>22, 'selected'=>$selected, 'succ'=>$succ, 'fail'=>$fail));
		header("Location: {$location}");
		exit;
	}
}

// 数据库维护操作
if($action == 'dotools') {
	$doname = array(
		'check' => '检查',
		'repair' => '修复',
		'analyze' => '分析',
		'optimize' => '优化'
	);
	$dodb = $tabledb = array();
	foreach ($do as $value) {
		$dodb[] = array('do'=>$value,'name'=>$doname[$value]);
		foreach ($tables AS $table) {
			if ($DB->query($value.' TABLE '.$table)) {
				$result = '<span class="yes">成功</span>';
			} else {
				$result = '<span class="no">失败</span>';
			}
			$tabledb[] = array('do'=>$value,'table'=>$table,'result'=>$result);
		}
	}
	$subnav = '数据库维护';
}// 数据库维护操作结束

if (in_array($action, array('backup', 'tools'))) {
	if ($action == 'backup') {
		$backuppath = sadate('Y-m-d').'_'.random(8);
		$subnav = '备份数据库';
		$act = 'dobackup';
	} else {
		$subnav = '数据库维护';
		$act = 'dotools';
	}
}//backup

// 数据库信息
if ($action == 'mysqlinfo') {
	$query = $DB->query("SHOW TABLE STATUS");
	$sablog_table_num = $sablog_table_rows = $sablog_data_size = $sablog_index_size = $sablog_free_size = 0;
	$other_table_num = $other_table_rows = $other_data_size = $other_index_size = $other_free_size = 0;
	$sablog_table = $other_table = array();
	while($table = $DB->fetch_array($query)) {
		if(in_array($table['Name'],$tables)) {
			$sablog_data_size = $sablog_data_size + $table['Data_length'];
			$sablog_index_size = $sablog_index_size + $table['Index_length'];
			$sablog_table_rows = $sablog_table_rows + $table['Rows'];
			$sablog_free_size = $sablog_free_size + $table['Data_free'];
			$table['Create_time'] = $table['Create_time'] ? $table['Create_time'] : 'Unknow';
			$table['Update_time'] = $table['Update_time'] ? $table['Update_time'] : 'Unknow';
			$table['Data_length'] = get_real_size($table['Data_length']);
			$table['Index_length'] = get_real_size($table['Index_length']);
			$table['Data_free'] = get_real_size($table['Data_free']);
			$sablog_table_num++;
			$sablog_table[] = $table;
		} else {
			$other_data_size = $other_data_size + $table['Data_length'];
			$other_index_size = $other_index_size + $table['Index_length'];
			$other_table_rows = $other_table_rows + $table['Rows'];
			$other_free_size = $other_free_size + $table['Data_free'];
			$table['Create_time'] = $table['Create_time'] ? $table['Create_time'] : 'Unknow';
			$table['Update_time'] = $table['Update_time'] ? $table['Update_time'] : 'Unknow';
			$table['Data_length'] = get_real_size($table['Data_length']);
			$table['Index_length'] = get_real_size($table['Index_length']);
			$table['Data_free'] = get_real_size($table['Data_free']);
			$other_table_num++;
			$other_table[] = $table;
		}
	}
	$sablog_data_size = get_real_size($sablog_data_size);
	$sablog_index_size = get_real_size($sablog_index_size);
	$sablog_free_size = get_real_size($sablog_free_size);
	$other_data_size = get_real_size($other_data_size);
	$other_index_size = get_real_size($other_index_size);
	$other_free_size = get_real_size($other_free_size);
	unset($table);
	$subnav = '数据库信息';
}

if ($action == 'checkresume' && $dbimport) {
	$subnav = '恢复数据';
	$sqlfile = htmlspecialchars($sqlfile);
	$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", loadfile($backupdir.'/'.$sqlfile, 200))));

	if (count($identify) != 3) {
		$location = getlink('tools', 'filelist', array('message'=>23));
	} elseif ($identify[2] != 1) {
		$location = getlink('tools', 'filelist', array('message'=>24));
	} elseif ($identify[1] != $SABLOG_VERSION) {
		$location = getlink('tools', 'filelist', array('message'=>25));
	}
	if ($location) {
		header("Location: {$location}");
		exit;
	}
}//backup

// 管理数据文件
if ($action == 'filelist') {
	$file_i = 0;
	if(is_dir($backupdir)) {
		$dirs = dir($backupdir);
		$dbfiles = array();
		$today = @sadate('Y-m-d');
		require_once(SABLOG_ROOT.'include/func/attachment.func.php');
		while ($file = $dirs->read()) {
			$filepath = $backupdir.'/'.$file;
			$pathinfo = pathinfo($file);
			if(is_file($filepath) && $pathinfo['extension'] == 'sql') {
				$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", loadfile($filepath, 200))));
				$moday = @sadate('Y-m-d',@filemtime($filepath),1);
				$mtime = @sadate('Y-m-d H:i',@filemtime($filepath),1);
				$dbfile = array(
					'filename' => htmlspecialchars($file),
					'filesize' => sizecount(filesize($filepath)),
					'mtime' => ($moday == $today) ? '<font color="#FF0000">'.$mtime.'</font>' : $mtime,
					'bktime' => $identify[0] ? @sadate('Y-m-d H:i',$identify[0]) : '未知',
					'version' => $identify[1] ? $identify[1] : '未知',
					'volume' => $identify[2] ? $identify[2] : '未知',
					'filepath' => urlencode($file),
				);
				$file_i++;
				$dbfiles[] = $dbfile;
			}
		}
		@sort($dbfiles);
		unset($dbfile);
		$dirs->close();
		$noexists = 0;
	} else {
		$noexists = 1;
	}
	$subnav = '数据文件管理';
} // end filelist

if ($action == 'rssimport') {
	$subnav = '导入RSS数据';
	$catedb = array();
	$query = $DB->query("SELECT mid,name FROM {$db_prefix}metas WHERE type='category' ORDER BY displayorder");
	while ($cate = $DB->fetch_array($query)) {
		$catedb[$cate['mid']] = $cate;
	}
	unset($cate);

	$query = $DB->query("SELECT userid,username FROM {$db_prefix}users WHERE groupid='1' OR groupid='2'");
	$userdb = array();
	while ($user = $DB->fetch_array($query)) {
		$userdb[$user['userid']] = $user;
	}
	unset($user);
	$DB->free_result($query);
}//backup

if($action == 'cache') {
	require_once(SABLOG_ROOT.'include/func/attachment.func.php');
	$cachedesc = array(
		'allarticleids' => '所有文章ID(供随机文章用)',
		'archives'		=> '日志归档',
		'categories'	=> '日志分类',
		'hottags'		=> '热门标签',
		'links'			=> '友情链接',
		'stick'			=> '置顶文章',
		'newarticles'	=> '最新文章',
		'newcomments'	=> '最新评论',
		'settings'		=> '系统参数',
		'statistics'	=> '统计信息',
		'stylevars'		=> '模板变量'
	);
	$cachedb = array();
	foreach ($cachedesc AS $name => $desc)	{
		$filepath = SABLOG_ROOT.'data/cache/cache_'.$name.'.php';
		if(is_file($filepath)) {
			$cachefile['name'] = $name;
			$cachefile['desc'] = $desc;
			$cachefile['size'] = sizecount(filesize($filepath));
			$cachefile['mtime'] = @sadate('Y-m-d H:i',@filemtime($filepath),1);
			$bakinfo = loadfile($filepath, 200);
			$detail=explode("\n",$bakinfo);
			$cachefile['ctime'] = (getstrlen($detail[2]) == 33) ? substr($detail[2],13,16) : '未知';
			$cachedb[] = $cachefile;
		}
	}
	unset($cachefile);
	$subnav = '缓存管理';
}

// 重建数据
if($action == 'rebuild') {
	$subnav = '重建数据';
}//rebuild

// 运行记录
if (in_array($action, array('adminlog', 'loginlog', 'deladminlog', 'delloginlog', 'dberrorlog', 'deldberrorlog'))) {
	if (in_array($action, array('adminlog', 'deladminlog'))) {
		$logsfile = 'adminlog';
		$opname = '后台操作记录';
	} elseif (in_array($action, array('loginlog', 'delloginlog'))) {
		$logsfile = 'loginlog';
		$opname = '后台登陆记录';
	} elseif (in_array($action, array('dberrorlog', 'deldberrorlog'))) {
		$logsfile = 'dberrorlog';
		$opname = '数据库出错记录';
	}
	if (in_array($action, array('deladminlog', 'delloginlog', 'deldberrorlog'))) {
		$logfilename = SABLOG_ROOT.'data/log/'.$logsfile.'.php';
		if(file_exists($logfilename)){
			$logfile = @file($logfilename);
		} else{
			$logfile=array();
		}
		$logs = array();
		if(is_array($logfile)) {
			foreach($logfile as $log) {
				$logs[] = $log;
			}
		}
		$logs = @array_reverse($logs);
		$total = count($logs);
		if ($total>100) {
			$output=@array_slice($logs,0,100);
			$output=@array_reverse($output);
			$output=@implode("",$output);

			@touch($logfilename);
			@$fp=fopen($logfilename,'rb+');
			@flock($fp,LOCK_EX);
			@fwrite($fp,$output);
			@ftruncate($fp,getstrlen($output));
			@fclose($fp);
			@chmod($filename,0777);
			$location = getlink('tools', $logsfile, array('message'=>26, 'opname'=>$opname));
		} else {
			$location = getlink('tools', $logsfile, array('message'=>27));
		}
		header("Location: {$location}");
		exit;
	}//removelog

	//管理日志页面
	if (in_array($action, array('adminlog', 'loginlog', 'dberrorlog'))) {

		@$logfile = file(SABLOG_ROOT.'data/log/'.$logsfile.'.php');
		$logs = $logdb = array();
		if(is_array($logfile)) {
			foreach($logfile as $log) {
				$logs[] = $log;
			}
		}
		$logs = @array_reverse($logs);
		$pagenum = 20;
		if($page) {
			$start_limit = ($page - 1) * $pagenum;
		} else {
			$start_limit = 0;
			$page = 1;
		}
		$total = count($logs);
		if ($total) {
			$multipage = multi($total, $pagenum, $page, 'cp.php?job=tools&amp;action='.$logsfile);
			for($i = 0; $i < $start_limit; $i++) {
				unset($logs[$i]);
			}
			for($i = $start_limit + $pagenum; $i < $total; $i++) {
				unset($logs[$i]);
			}
			if ($action == 'adminlog') {
				foreach($logs as $logrow) {
					$logrow = explode("\t", $logrow);
					$logrow[1] = sadate('Y-m-d H:i:s', $logrow[1], 1);
					$logdb[] = $logrow;
				}
			} elseif ($action == 'loginlog') {
				foreach($logs as $logrow) {
					$logrow = explode("\t", $logrow);
					$logrow[1] = $logrow[1] ? htmlspecialchars($logrow[1]) : '<span class="no">Null</span>';
					$logrow[2] = sadate('Y-m-d H:i:s', $logrow[2], 1);
					$logrow[4] = trim($logrow[4]) == 'Succeed' ? '<span class="yes">Succeed</span>' : '<span class="no">Failed</span>';
					$logdb[] = $logrow;
				}
			} else {
				foreach($logs as $logrow) {
					$logrow = explode("\t", $logrow);
					$logrow[1] = sadate('Y-m-d H:i:s', $logrow[1], 1);
					$logdb[] = $logrow;
				}
			}
		}
		$subnav = $opname;
		unset($logrow);
	}//end
}

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('tools');
?>