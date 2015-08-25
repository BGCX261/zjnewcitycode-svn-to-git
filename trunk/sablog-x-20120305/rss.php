<?php
// ========================== 文件说明 ==========================//
// 本文件说明：RSS输出
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once('global.php');

if (!$options['rss_enable']) {
	exit('RSS Disabled');
}

$query_add = '';

$cid = (int)$_GET['cid'];
$url = sax_addslashes($_GET['url']);

$rss_title = $options['name'];
$rss_url = $options['url'];

$fileext = md5('all');

if ($cid || $url) {
	if ($cid) {
		$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='category' AND mid='$cid'");
	} else {
		$r = $DB->fetch_one_array("SELECT mid, name, slug, count FROM {$db_prefix}metas WHERE type='category' AND slug='$url' LIMIT 1");
	}
	if (!$r) {
		exit('Record does not exist.');
	}
	$rss_title = $r['name'];
	$rss_url = getcatelink($r['mid'], $r['slug']);
	$aids = get_cids($r['mid']);
	$query_add .= " AND a.articleid IN ($aids)";
	$fileext = md5($cid.$url);
}

//读取缓存文件
$cachefile = SABLOG_ROOT.'data/cache/cache_rss_'.$fileext.'.php';

//如果读取失败或缓存过期则从新读取数据库
if((@!include($cachefile)) || $expiration < $timestamp || !$option['rss_ttl']) {
	require_once(SABLOG_ROOT.'include/func/attachment.func.php');
	$articledb = array();
	$query = $DB->query("SELECT a.articleid,a.uid,a.dateline,a.title,a.description,a.content,a.readpassword,a.attachments,a.comments,a.alias,u.username,u.email FROM {$db_prefix}articles a LEFT JOIN {$db_prefix}users u ON a.uid=u.userid WHERE a.visible='1' $query_add ORDER BY a.dateline DESC LIMIT ".($options['rss_num'] ? intval($options['rss_num']) : 20));
	$aids = $comma = '';
	$haveattach = 0;
	while ($article = $DB->fetch_array($query)) {
		$aids .= $comma.$article['articleid'];
		$comma = ',';
		$article['url'] = getpermalink($article['articleid'], $article['alias']);
		if (!$options['rss_all_output'] && $article['description']) {
			$article['content'] = $article['description'].'<p><a href="'.$article['url'].'" target="_blank">阅读全文</a></p>';
		} else {
			//附件
			if ($article['attachments']) {
				$haveattach = 1;
			}
		}

		$article['content'] = $options['name'].' ( '.$options['url'].' ) : '.$article['content'];

		$article['dateline'] = sadate('r',$article['dateline']);
		$articledb[$article['articleid']] = $article;
	}//end while

	unset($article);
	$DB->free_result($query);

	$metadb = array();
	if ($aids) {
		$query = $DB->query("SELECT m.mid, m.name, m.slug, m.type, r.cid FROM {$db_prefix}metas m
			INNER JOIN {$db_prefix}relationships r ON r.mid = m.mid
			WHERE m.type IN ('category', 'tag') AND r.cid IN ($aids)
			ORDER BY m.displayorder ASC, m.mid DESC");
		while ($meta = $DB->fetch_array($query)) {
			if ($meta['type'] == 'tag') {
				$meta['url'] = gettaglink($meta['slug']);
				//RSS里a标签内不能含有onclick
				//$articledb[$meta['cid']]['content'] = highlight_tag($articledb[$meta['cid']]['content'], $meta['name']);
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
			$query = $DB->query("SELECT attachmentid, articleid, dateline, filename, filetype, filesize, downloads, filepath, thumb_filepath, thumb_width, thumb_height, isimage FROM {$db_prefix}attachments WHERE articleid IN ($aids) ORDER BY attachmentid DESC");
			$size = explode('x', strtolower($options['attachments_thumbs_size']));
			while ($attach = $DB->fetch_array($query)) {
				$attach['filesize'] = sizecount($attach['filesize']);
				$attach['dateline'] = sadate('r', $attach['dateline']);
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
			foreach ($aids as $articleid) {
				$extracontent = '';
				if (($options['rss_all_output'] || !$articledb[$articleid]['description']) && $articledb[$articleid]['image']) {
					foreach ($articledb[$articleid]['image'] as $image) {
						if($image['thumbs']){
							$extracontent .= "<p><a href=\"$options[url]attachment.php?id=$image[attachmentid]\" target=\"_blank\"><img src=\"{$options[url]}{$image[thumb_filepath]}\" border=\"0\" alt=\"$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图\" width=\"$image[thumb_width]\" height=\"$image[thumb_height]\" /></a></p>";
						} else {
							$extracontent .= "<p><a href=\"$options[url]attachment.php?id=$image[attachmentid]\" target=\"_blank\"><img src=\"{$options[url]}{$image[filepath]}\" border=\"0\" alt=\"$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图\" width=\"$image[thumb_width]\" height=\"$image[thumb_height]\" /></a></p>";
						}
					}
				}
				if(($options['rss_all_output'] || !$articledb[$articleid]['description']) && $articledb[$articleid]['file']){
					foreach($articledb[$articleid]['file'] as $file){
						if($file){
							$extracontent .= "<p><strong><a title=\"$file[filename]\" href=\"$options[url]attachment.php?id=$file[attachmentid]\" target=\"_blank\">$file[filename]</a></strong> ($file[filesize], 下载次数:$file[downloads], 上传时间:$file[dateline])</p>";
						}
					}
				}
				if ($extracontent) {
					$articledb[$articleid]['content'] = $articledb[$articleid]['content'].$extracontent;
				}
			}
		}
	}
	
	$cachedata = "<?php\r\nif(!defined('SABLOG_ROOT')) exit('Access Denied');\r\n\$expiration='".($timestamp + $options['rss_ttl'] * 60)."';\r\n\$articledb = unserialize('".addcslashes(serialize($articledb), '\\\'')."');\r\n?>";
	
	if(!writefile($cachefile, $cachedata)) {
		exit('Can not write to cache files, please check directory ./data/cache/ .');
	}
}
//pr($articledb);
//exit;

header("Content-Type: application/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<rss version=\"2.0\">\n";
echo "\t<channel>\n";
echo "\t\t<title>".htmlspecialchars($rss_title)."</title>\n";
echo "\t\t<link>".$rss_url."</link>\n";
echo "\t\t<description>".htmlspecialchars($options['description'])."</description>\n";
echo "\t\t<copyright>Powered by SaBlog-X. Copyright (C) 2003-2012.</copyright>\n";
echo "\t\t<generator>SaBlog-X Version $SABLOG_VERSION Build $SABLOG_RELEASE</generator>\n";
echo "\t\t<lastBuildDate>".sadate('r', $timestamp)."</lastBuildDate>\n";
echo "\t\t<ttl>".$options['rss_ttl']."</ttl>\n";

if ($articledb && is_array($articledb)) {
	foreach ($articledb as $article) {
		echo "\t\t<item>\n";
		echo "\t\t\t<link>".$article['url']."</link>\n";
		echo "\t\t\t<guid>".$article['url']."</guid>\n";
		echo "\t\t\t<title>".$article['title']."</title>\n";
		echo "\t\t\t<author>".$article['email']."(".$article['username'].")</author>\n";
		if ($article['readpassword']) {
			echo "\t\t\t<description>文章需要输入密码才能浏览.</description>\n";
		} else {
			echo "\t\t\t<description><![CDATA[".$article['content']."]]></description>\n";
		}
		echo "\t\t\t<link>".$article['url']."</link>\n";
		if ($metadb[$article['articleid']]['category'] && is_array($metadb[$article['articleid']]['category'])) {
			foreach ($metadb[$article['articleid']]['category'] as $meta) {
				echo "\t\t\t<category domain=\"".$meta['url']."\">".$meta['name']."</category>\n";
			}
		}
		if ($metadb[$article['articleid']]['tag'] && is_array($metadb[$article['articleid']]['tag'])) {
			foreach ($metadb[$article['articleid']]['tag'] as $meta) {
				echo "\t\t\t<category domain=\"".$meta['url']."\">".$meta['name']."</category>\n";
			}
		}
		if ($article['comments']) {
			echo "\t\t\t<comments>".$article['url']."#comments</comments>\n";
		}
		echo "\t\t\t<pubDate>".$article['dateline']."</pubDate>\n";
		echo "\t\t</item>\n";
	}
}

echo "\t</channel>\n";
echo "</rss>\n";
exit;

?>