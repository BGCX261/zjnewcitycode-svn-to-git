<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<div id="page">
		<div id="wrap">
			<!--{if $navtext}-->
				<div class="title">$navtext</div>
			<!--{/if}-->
			<!--{if $total}-->
				<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>
				<script type="text/javascript" src="$options[url]include/jscript/show.js"></script>
				<script type="text/javascript">
				window.onload=function(){
					fiximage('$options[attachments_thumbs_size]');
				}
				</script>
				<!--{loop $articledb $article}-->
					<div class="post-main">
					<!--{if $article['stick']}-->
						<h1 class="post-stick-title">
							[置顶] <a href="$article[url]">$article[title]</a>
						</h1>
					<!--{else}-->
						<h1 class="post-title">
							<a href="$article[url]">$article[title]</a>
						</h1>
						<p class="post-date"><a href="$article[userurl]">$article[username]</a> 发表于 $article[dateline]</p>
						<!--{if !$article['allowread']}-->

							<div class="needpwd">
								<form action="$article[url]" method="post">
								这篇日志被加密了，请输入密码后查看。<br />
								<input class="formfield" type="password" name="readpassword" style="margin-right:5px;" />
								<button type="submit">提交</button>
								</form>
							</div>

						<!--{else}-->

							<div class="post-body">$article[content]
							<!--{if $article['description']}-->
								<p>&raquo; <a href="$article[url]">阅读全文</a></p>
							<!--{/if}-->
							</div>

							<!--{if !$article['description'] && $article['image']}-->
								<!--{loop $article['image'] $image}-->
									<!--{if $image['thumbs']}-->
										<div class="attach">
											<p>$image[filename](缩略图)</p>
											<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[thumb_filepath]}" border="0" alt="$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
										</div>
									<!--{else}-->
										<div class="attach">
											<p>$image[filename]</p>
											<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[filepath]}" border="0" alt="$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
										</div>
									<!--{/if}-->
								<!--{/loop}-->
							<!--{/if}-->

							<!--{if !$article['description'] && $article['file']}-->
								<!--{loop $article['file'] $file}-->
									<div class="attach">
										<a title="$file[filename]" href="$options[url]attachment.php?id=$file[attachmentid]" target="_blank">$file[filename]</a>
										($file[filesize], 下载次数:$file[downloads], 上传时间:$file[dateline])
									</div>
								<!--{/loop}-->
							<!--{/if}-->

							<!--{if $metadb[$article['articleid']]['tag']}-->
								<h2 class="art-tag">关键词: 
								<!--{eval $comma = ''}-->
								<!--{loop $metadb[$article['articleid']]['tag'] $tag}-->								
									$comma <a href="$tag[url]">$tag[name]</a>								
									<!--{eval $comma = ', '}-->
								<!--{/loop}-->
								</h2>
							<!--{/if}-->

							<div class="post-meta">
								<span class="cat-links">发表在:
								<!--{if $metadb[$article['articleid']]['category']}-->
									<!--{eval $comma = ''}-->
									<!--{loop $metadb[$article['articleid']]['category'] $meta}-->								
										$comma <a href="$meta[url]">$meta[name]</a>								
										<!--{eval $comma = ', '}-->
									<!--{/loop}-->
								<!--{/if}-->
								</span>
								<span class="post-meta-link"><a href="$article[url]#comments">$article[comments] 条评论</a></span>
								<span class="post-meta-link"><a href="$article[url]">阅读 $article[views] 次</a></span>
							</div>

						<!--{/if}-->
					
					<!--{/if}-->
					</div>
				<!--{/loop}-->

				$multipage

			<!--{else}-->

				<p>没有任何文章</p>

			<!--{/if}-->

		</div>
		<div id="sidebar">

			<!--{if $options['show_calendar']}-->
				<div id="showcalendar">
					<table cellpadding="0" cellspacing="1" style="width:100%">
						<tr align="center">
							<td colspan="7" class="curdate"><a href="$prevmonth">&laquo;</a> $calendar[cur_date] <a href="$nextmonth">&raquo;</a></td>
						</tr>
						<tr>
							<th class="week"><span style="color:#833">日</span></th>
							<th class="week">一</th>
							<th class="week">二</th>
							<th class="week">三</th>
							<th class="week">四</th>
							<th class="week">五</th>
							<th class="week"><font color="#53A300">六</font></th>
						</tr>
						$calendar[html]
					</table>
				</div>
			<!--{/if}-->

			{template sidecomm}

			<!--{if $archivecache}-->
				<div class="stitle">归档</div>
				<ul>
					<!--{loop $archivecache $key $data}-->
						<li><a href="$data[url]">$key</a> <span>($data[num])</span></li>
					<!--{/loop}-->
					<!--{if $archivenum > 12}-->
						<li><a href="$archives_url">更多...</a></li>
					<!--{/if}-->
				</ul>
			<!--{/if}-->

			<!--{if $options['hottags_shownum']}-->
				<div class="stitle">热门标签</div>
				<!--{if !$tagcache}-->
					<ul>
						<li>没有任何标签</li>
					</ul>
				<!--{else}-->
					<h2 class="tags">
					<!--{loop $tagcache $data}-->
						<span style="line-height:160%;font-size:$data[fontsize]px;margin-right:10px;"><a href="$data[url]" title="使用次数: $data[count]">$data[name]</a></span>
					<!--{/loop}-->
					</h2>
				<!--{/if}-->
			<!--{/if}-->

			<!--{if $options['recentcomment_num']}-->
				<div class="stitle">最新评论</div>
				<ul>
					<!--{if !$newcommentcache}-->
						<li>没有任何评论</li>
					<!--{else}-->
						<!--{loop $newcommentcache $data}-->
						<li><a title="$data[title]" href="$data[article_url]">$data[content]</a><br /><span>$data[dateline] - $data[author]</span></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			<!--{/if}-->

			<!--{if $options['show_statistics']}-->
				<div class="stitle">统计</div>
				<ul>
					<li>文章: <span class="num">$stats[article_count]</span> 篇</li>
					<li>评论: <span class="num">$stats[comment_count]</span> 条</li>
					<li>标签: <span class="num">$stats[tag_count]</span> 个</li>
				</ul>
			<!--{/if}-->

			<!--{if $linkcache}-->
				<div class="stitle">友情链接</div>
				<ul>
					<!--{loop $linkcache $data}-->
						<li><a href="$data[url]" target="_blank" title="$data[note]">$data[name]</a></li>
					<!--{/loop}-->
					<li><a href="$links_url">更多...</a></li>
				</ul>
			<!--{/if}-->

			<div class="stitle">其他</div>
			<ul>
				<!--{if $options['rss_enable']}-->
				<li><a href="$options[url]rss.php">RSS 2.0</a></li>
				<!--{/if}-->
				<!--{if $options['wap_enable']}-->
				<li><a href="$options[url]wap/">WAP</a></li>
				<!--{/if}-->
				<li><a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML 1.0</a></li>
				<!--{if $options['icp']}-->
				<li><a href="http://www.miibeian.gov.cn/" target="_blank">$options[icp]</a></li>
				<!--{/if}-->
			</ul>

		</div>
	</div>