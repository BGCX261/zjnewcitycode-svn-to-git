<?php
// ========================== 文件说明 ==========================//
// 本文件说明：附件管理
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
permission(array(1,2));

!$action && $action = 'list';

$article = array();
if ($articleid) {
	$article = $DB->fetch_one_array("SELECT title,attachments,visible FROM {$db_prefix}articles WHERE articleid='$articleid'");
	if (!$article) {
		redirect('日志不存在');
	}
}

if ($message) {
	$messages = array(
		1 => sprintf('成功上传了 %d 个附件到《'.$article['title'].'》', $attach_total),
		2 => '成功删除所选附件',
		3 => '未选择任何附件',
		4 => '成功清理不存在文件的附件记录',
		5 => sprintf('附件清理结束,共删除了 %d 个冗余附件.', $deltotal),
	);
}

// 加载附件相关函数
require_once(SABLOG_ROOT.'include/func/attachment.func.php');
$attachdir = SABLOG_ROOT . $options['attachments_dir'];

//批量删除附件
if($action == 'delattachments') {
	if ($attachmentids = implode_ids($_POST['selectall'])) {
		$nokeep = $articledb = array();
		//删除附件
		$query  = $DB->query("SELECT articleid,attachmentid,filepath,thumb_filepath FROM {$db_prefix}attachments WHERE attachmentid IN ($attachmentids)");
		while($attach = $DB->fetch_array($query)) {
			$articledb[$attach['articleid']][] = $attach['attachmentid'];
			$nokeep[$attach['attachmentid']] = $attach;
		}

		removeattachment($nokeep);

		if ($articledb){
			foreach($articledb as $articleid => $attachid) {
				$attach_total = count($articledb[$article['articleid']]);
				$DB->unbuffered_query("UPDATE {$db_prefix}articles SET attachments=attachments-$attach_total WHERE articleid='".$article['articleid']."'");
			}
		}
		$location = getlink('attachment', 'list', array('message'=>2));
	} else {
		$location = getlink('attachment', 'list', array('message'=>3));
	}
	header("Location: {$location}");
	exit;
}

//附件修复
if($action == 'dorepair') {
	$query = $DB->query("SELECT attachmentid,articleid,filepath FROM {$db_prefix}attachments");
	while ($attach = $DB->fetch_array($query)) {
		if(!file_exists($attachdir.$attach['filepath'])){
			$DB->unbuffered_query("UPDATE {$db_prefix}articles SET attachments=attachments-1 WHERE articleid='".$attach['articleid']."'");
			$DB->unbuffered_query("DELETE FROM {$db_prefix}attachments WHERE attachmentid='".$attach['attachmentid']."'");
		}
	}
	$location = getlink('attachment', 'list', array('message'=>4));
	header("Location: {$location}");
	exit;
}

if ($action == 'doclear'){
	if (!$start){
		$start=0;
		$deltotal=0;
	}
	$num	= 0;
	$delnum	= 0;
	!$percount && $percount = 500;
	$dir1 = @opendir($attachdir);
	while($file1 = @readdir($dir1)){
		if ($file1 != '' && $file1 != '.' && $file1 != 'index.php' && $file1 != '..' && $file1 != 'index.htm'){
			if (@is_dir($attachdir.'/'.$file1)){
				$dir2 = @opendir($attachdir.'/'.$file1);
				while($file2 = @readdir($dir2)){
					if (@is_file($attachdir.'/'.$file1.'/'.$file2) && $file2 != '' && $file1 != 'index.php' && $file2 != '.' && $file2 != '..' && $file2 != 'index.htm'){
						$num++;
						if ($num > $start){
							$r = $DB->fetch_one_array("SELECT attachmentid FROM {$db_prefix}attachments WHERE filepath='/$file1/$file2' OR  thumb_filepath='/$file1/$file2'");
							if(!$r){
								$delnum++;
								$deltotal++;
								@unlink($attachdir.'/'.$file1.'/'.$file2);
							}
							if ($num-$start >= $percount){
								$start = $num-$delnum;
								$jumpurl="cp.php?job=attachment&action=doclear&start=$start&percount=$percount&deltotal=$deltotal";
								redirect('正在清理冗余附件，已经删除 '.$deltotal.' 个附件,程序将自动完成整个过程.页面跳转中..',$jumpurl);
							}
						}
					}
				}
			} elseif (is_file($attachdir.'/'.$file1)){
				$num++;
				if ($num > $start){
					$rt = $DB->fetch_one_array("SELECT attachmentid FROM {$db_prefix}attachments WHERE filepath='/$file1' OR  thumb_filepath='/$file1'");
					if(!$rt){
						$delnum++;
						$deltotal++;
						@unlink($attachdir.'/'.$file1);
					}
					if ($num-$start >= $percount){
						$start = $num-$delnum;

						$jumpurl = "cp.php?job=attachment&action=doclear&start=$start&percount=$percount&deltotal=$deltotal";
						redirect('正在清理冗余附件，已经删除 '.$deltotal.' 个附件,程序将自动完成整个过程.页面跳转中..',$jumpurl);
					}
				}
			}
		}
	}
	$location = getlink('attachment', 'list', array('message'=>5, 'deltotal'=>$deltotal));
	header("Location: {$location}");
	exit;
}

//操作结束

if ($action == 'list') {
	$pagenum = 25;
	if($page) {
		$start_limit = ($page - 1) * $pagenum;
	} else {
		$start_limit = 0;
		$page = 1;
	}

	$atttotal = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments"), 0);
	$imgtotal = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments WHERE isimage='1'"), 0);
	$noarticletotal = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments WHERE articleid='0'"), 0);
	$filetotal = $atttotal - $imgtotal;

	$sql = 'WHERE 1';
	if ($view == 'image') {
		$sql .= " AND a.isimage='1'";
		$subnav = '图片附件';
	} elseif ($view == 'file') {
		$sql .= " AND a.isimage='0'";
		$subnav = '非图片附件';
	} elseif ($view == 'noarticle') {
		$sql .= " AND a.articleid='0'";
		$subnav = '未附加附件';
	}
	if ($articleid) {
		$article = $DB->fetch_one_array("SELECT title FROM {$db_prefix}articles WHERE articleid='$articleid'");
		$subnav = '《'.$article['title'].'》的附件';
		$sql .= " AND a.articleid='$articleid'";
	}
	$view = in_array($view, array('image', 'file')) ? $view : '';

	$total = $DB->result($DB->query("SELECT COUNT(attachmentid) FROM {$db_prefix}attachments a ".$sql), 0);

	if ($total) {

		$multipage = multi($total, $pagenum, $page, 'cp.php?job=attachment&amp;action=list&amp;view='.$view.'&amp;articleid='.$articleid);

		$query = $DB->query("SELECT a.*,ar.title as article,ar.visible FROM {$db_prefix}attachments a LEFT JOIN {$db_prefix}articles ar ON (ar.articleid=a.articleid) $sql ORDER BY a.attachmentid DESC LIMIT $start_limit, $pagenum");

		$attachdb = array();
		while ($attach = $DB->fetch_array($query)) {
			if ($attach['isimage']) {
				if (!$attach['thumb_filepath']) {
					$attach['thumb_filepath'] = $attach['filepath'];
				}
				$imsize = @getimagesize( $attachdir . $attach['filepath'] );
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
			$attach['filename'] = htmlspecialchars($attach['filename']);
			$attach['filepath'] = htmlspecialchars($attach['filepath']);
			$attach['filesize'] = sizecount($attach['filesize']);
			$attach['filetype'] = htmlspecialchars($attach['filetype']);
			$attach['dateline'] = date("Y-m-d H:i",$attach['dateline']);
			$pathdata = explode('/',$attach['filepath']);
			if (count($pathdata) == 2) {
				$attach['subdir'] = '根目录';
			} else {
				$attach['subdir'] = $pathdata[1];
			}
			$attachdb[$attach['attachmentid']] = $attach;
		}
		unset($attach);
		$DB->free_result($query);
	}
}

if($action == 'repair') {
	$subnav = '附件整理';
}

$navlink_L = $subnav ? ' &raquo; <span>'.$subnav.'</span>' : '';
cpheader($subnav);
include template('attachment');
?>