<?php
// ========================== 文件说明 ==========================//
// 本文件说明：附件上传
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

$max_upload_size = max_upload_size();
$max_upload_size_unit = sizecount($max_upload_size);

$attachments = $attach_data = array();

if ($uploadmode == 'swf') {

	if(isset($_FILES["Filedata"]) && is_array($_FILES["Filedata"])) {
		$attach = $_FILES["Filedata"];
	}

	$gd_version = gd_version();

	if(disuploadedfile($attach['tmp_name']) || !($attach['tmp_name'] != 'none' && $attach['tmp_name'] && $attach['name'])) {

		$attach['name'] = strtolower($attach['name']);
		$attach['ext']  = getextension($attach['name']);
		$attach['type'] = mime_content_type($attach['name']);

		$fnamehash = md5(uniqid(microtime()));
		$attachsubdir = '/date_'.sadate('Ym').'/';
		// 取得附件目录的绝对路径
		$attach_dir = SABLOG_ROOT.$options['attachments_dir'].$attachsubdir;
		if(!is_dir($attach_dir)) {
			mkdir($attach_dir, 0777);
			@chmod($attach_dir, 0777);
			fclose(fopen($attach_dir.'index.htm', 'w'));
		}
		// 判断上传的类型
		// path变量为管理目录相对路径,后台操作用
		// filepath变量为跟目录相对路径,前台读取用
		// fnamehash变量为当前时间的MD5散列,重命名附件名
		if (!in_array($attach['ext'], array('gif', 'jpg', 'jpeg', 'png'))) {
			$path     = $attach_dir.$fnamehash.'.file';
			$filepath = $attachsubdir.$fnamehash.'.file';
		} else {
			$path     = $attach_dir.$fnamehash.'.'.$attach['ext'];
			$filepath = $attachsubdir.$fnamehash.'.'.$attach['ext'];
		}
		$attachment = upfile($attach['tmp_name'], $path);
		// 如果一种函数上传失败，还可以用其他函数上传
		if (!$attachment) {
			uploaderrormsg('上传附件发生意外错误!上传失败');
		}

		@unlink($attach['tmp_name']);

		$tmp_filesize = @filesize($attachment);
		if ($tmp_filesize != $attach['size']) {
			@unlink($attachment);
			uploaderrormsg('上传附件发生意外错误!文件大小出错!');
		}
		if ($tmp_filesize > $max_upload_size || $attach['size'] > $max_upload_size) {
			@unlink($attachment);
			uploaderrormsg('上传附件发生意外错误!文件大小超过系统限制!');
		}
		// 判断是否为图片格式
		if (in_array($attach['ext'], array('gif', 'jpg', 'jpeg', 'png'))) {
			if ($imginfo=@getimagesize($attachment)) {
				if (!$imginfo[2] || !$imginfo['bits']) {
					@unlink($attachment);
					uploaderrormsg('上传的文件不是一个有效的GIF或者JPG文件!');
				} else {
					$isimage = '1';
				}
			}
			// 判断是否使用缩略图
			if ($options['attachments_thumbs'] && $gd_version && $options['attachments_thumbs_size']) {
				$size = explode('x', strtolower($options['attachments_thumbs_size']));
				if (($imginfo[0] > $size[0] || $imginfo[1] > $size[1]) && $attach['size'] < 2048000) {
					$attach_thumb = array(
						'filepath'     => $attachment,
						'filename'     => $fnamehash,
						'extension'    => $attach['ext'],
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
			//水印
			$watermark_size = explode('x', strtolower($options['watermark_size']));
			if($isimage && $options['watermark'] && $imginfo[0] > $watermark_size[0] && $imginfo[1] > $watermark_size[1] && $attach['size'] < 2048000) {
				require_once(SABLOG_ROOT.'include/func/image.func.php');
				create_watermark($path);
				$attach['size'] = filesize($path);
			}
		}
		// 把文件信息插入数据库
		$DB->query("INSERT INTO {$db_prefix}attachments (filename,filesize,filetype,filepath,dateline,downloads,isimage,thumb_filepath,thumb_width,thumb_height) VALUES ('".sax_addslashes($attach['name'])."', '".$attach['size']."', '".sax_addslashes($attach['type'])."', '".sax_addslashes($filepath)."', '$timestamp', '0', '$isimage', '".$attach_data['thumbfilepath']."', '".$attach_data['thumbwidth']."','".$attach_data['thumbheight']."')");
		$new_attachid = $DB->insert_id();
		unset($isimage);
		unset($attach_data);
	}

}
/*
else {

	if(isset($_FILES['attach']) && is_array($_FILES['attach'])) {
		foreach($_FILES['attach'] as $key => $var) {
			foreach($var as $id => $val) {
				$attachments[$id][$key] = $val;
			}
		}
	}

	$comma = '';

	if($attachments) {
		$gd_version = gd_version();
		foreach($attachments as $key => $attach) {
			if(!disuploadedfile($attach['tmp_name']) || !($attach['tmp_name'] != 'none' && $attach['tmp_name'] && $attach['name'])) {
				continue;
			}

			$attach['name'] = strtolower($attach['name']);
			$attach['ext']  = getextension($attach['name']);

			$fnamehash = md5(uniqid(microtime()));
			$attachsubdir = '/date_'.sadate('Ym').'/';
			// 取得附件目录的绝对路径
			$attach_dir = SABLOG_ROOT.$options['attachments_dir'].$attachsubdir;
			if(!is_dir($attach_dir)) {
				mkdir($attach_dir, 0777);
				@chmod($attach_dir, 0777);
				fclose(fopen($attach_dir.'index.htm', 'w'));
			}
			// 判断上传的类型
			// path变量为管理目录相对路径,后台操作用
			// filepath变量为跟目录相对路径,前台读取用
			// fnamehash变量为当前时间的MD5散列,重命名附件名
			if (!in_array($attach['ext'], array('gif', 'jpg', 'jpeg', 'png'))) {
				$path     = $attach_dir.$fnamehash.'.file';
				$filepath = $attachsubdir.$fnamehash.'.file';
			} else {
				$path     = $attach_dir.$fnamehash.'.'.$attach['ext'];
				$filepath = $attachsubdir.$fnamehash.'.'.$attach['ext'];
			}
			$attachment = upfile($attach['tmp_name'], $path);
			// 如果一种函数上传失败，还可以用其他函数上传
			if (!$attachment) {
				redirect('上传附件发生意外错误!上传失败');
			}

			@unlink($attach['tmp_name']);

			$tmp_filesize = @filesize($attachment);
			if ($tmp_filesize != $attach['size']) {
				@unlink($attachment);
				redirect('上传附件发生意外错误!文件大小出错!');
			}
			if ($tmp_filesize > $max_upload_size || $attach['size'] > $max_upload_size) {
				@unlink($attachment);
				redirect('上传附件发生意外错误!文件大小超过系统限制!');
			}
			// 判断是否为图片格式
			if (in_array($attach['ext'], array('gif', 'jpg', 'jpeg', 'png'))) {
				if ($imginfo=@getimagesize($attachment)) {
					if (!$imginfo[2] || !$imginfo['bits']) {
						@unlink($attachment);
						redirect('上传的文件不是一个有效的GIF或者JPG文件!');
					} else {
						$isimage = '1';
					}
				}
				// 判断是否使用缩略图
				if ($options['attachments_thumbs'] && $gd_version && $options['attachments_thumbs_size']) {
					$size = explode('x', strtolower($options['attachments_thumbs_size']));
					if (($imginfo[0] > $size[0] || $imginfo[1] > $size[1]) && $attach['size'] < 2048000) {
						$attach_thumb = array(
							'filepath'     => $attachment,
							'filename'     => $fnamehash,
							'extension'    => $attach['ext'],
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
				//水印
				$watermark_size = explode('x', strtolower($options['watermark_size']));
				if($isimage && $options['watermark'] && $imginfo[0] > $watermark_size[0] && $imginfo[1] > $watermark_size[1] && $attach['size'] < 2048000) {
					require_once(SABLOG_ROOT.'include/func/image.func.php');
					create_watermark($path);
					$attach['size'] = filesize($path);
				}
			}
			// 把文件信息插入数据库
			$DB->query("INSERT INTO {$db_prefix}attachments (filename,filesize,filetype,filepath,dateline,downloads,isimage,thumb_filepath,thumb_width,thumb_height) VALUES ('".sax_addslashes($attach['name'])."', '".$attach['size']."', '".sax_addslashes($attach['type'])."', '".sax_addslashes($filepath)."', '$timestamp', '0', '$isimage', '".$attach_data['thumbfilepath']."', '".$attach_data['thumbwidth']."','".$attach_data['thumbheight']."')");
			$new_attachid = $DB->insert_id();
			$attachmentids .= $comma.$new_attachid;
			$comma = ',';
			$attach_total++;
			unset($isimage);
			unset($attach_data);
			$searcharray[] = '[localfile='.$localid[$key].']';
			$replacearray[] = '[attach='.$new_attachid.']';
		}
	}
} //end uploadmode

*/
?>