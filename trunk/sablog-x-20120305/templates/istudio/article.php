<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<script type="text/javascript">
	var blogurl = '$options[url]';
	</script>
	<section id="content">
		<section class="postlist">
			<!--{if $navtext}-->
				<div class="archive">
					<h3 class="title">$navtext</h3>
				</div>
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
					<article id="article-{$article[articleid]}">
					<!--{if $article['stick']}-->
						<section class="title">
							<h3>$article[dateline]</h3>
							<h2>[置顶] <a title="$article[title]" href="$article[url]">$article[title]</a></h2>
						</section>
					<!--{else}-->
						<section class="title">
							<h2><a title="$article[title]" href="$article[url]">$article[title]</a></h2>
							<small>
								<a href="$article[url]#comment"><!--{if $article['comments'] > 1}-->$article[comments] Comments<!--{elseif $article['comments'] == 1}-->$article[comments] Comment<!--{else}-->No Comments<!--{/if}--></a> | 
								<!--{if $metadb[$article['articleid']]['category']}-->
									<!--{eval $comma = ''}-->
									<!--{loop $metadb[$article['articleid']]['category'] $meta}-->								
										$comma <a title="$meta[name]" href="$meta[url]">$meta[name]</a>								
										<!--{eval $comma = ', '}-->
									<!--{/loop}-->
								<!--{/if}-->
								 | by <a href="$article[userurl]">$article[username]</a> | <a href="$article[url]">$article[views] Views.</a>
								 | $article[dateline]
							</small>
						</section>
						<!--{if !$article['allowread']}-->

							<section class="needpwd">
								<form action="$article[url]" method="post">
								这篇日志被加密了，请输入密码后查看。<br />
								<input class="text" type="password" name="readpassword" style="margin-right:5px;" />
								<button type="submit">提交</button>
								</form>
							</section>

						<!--{else}-->

							<section class="entry">

								$article[content]

								<!--{if $article['description']}-->
									<p>&raquo; <a title="$article[title]" href="$article[url]">阅读全文</a></p>
								<!--{/if}-->

								<!--{if !$article['description'] && $article['image']}-->
									<!--{loop $article['image'] $image}-->
										<!--{if $image['thumbs']}-->
											<div class="attach">
												<p>$image[filename](缩略图)</p>
												<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[thumb_filepath]}" alt="$image[filename] - 大小: $image[filesize] - 尺寸: $image[thumb_width] x $image[thumb_height] - 点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
											</div>
										<!--{else}-->
											<div class="attach">
												<p>$image[filename]</p>
												<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[filepath]}" alt="$image[filename] - 大小: $image[filesize] - 尺寸: $image[thumb_width] x $image[thumb_height] - 点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
											</div>
										<!--{/if}-->
									<!--{/loop}-->
								<!--{/if}-->

								<!--{if !$article['description'] && $article['file']}-->
									<!--{loop $article['file'] $file}-->
										<div class="but_down">
											<a title="$file[filesize] - 下载次数:$file[downloads] - 上传时间:$file[dateline]" href="$options[url]attachment.php?id=$file[attachmentid]" target="_blank"><span>$file[filename]</span></a>
											<div class="clear"></div>
										</div>
									<!--{/loop}-->
								<!--{/if}-->

								<p><a class="more-link" href="$article[url]">阅读全文</a></p>

							</section><!--entry-->

							<!--{if $metadb[$article['articleid']]['tag']}-->
								<p class="postmeta">关键词: 
								<!--{eval $comma = ''}-->
								<!--{loop $metadb[$article['articleid']]['tag'] $tag}-->								
									$comma <a title="Tag:$tag[name]" href="$tag[url]">$tag[name]</a>								
									<!--{eval $comma = ', '}-->
								<!--{/loop}-->
								</p>
							<!--{/if}-->

						<!--{/if}-->
					
					<!--{/if}-->
					</article>
				<!--{/loop}-->

				$multipage

			<!--{else}-->
				<div class="post">
					<p>没有任何文章</p>
				</div>
			<!--{/if}-->

		</section><!--postlist-->
		<aside>

			{template sidecomm}

			<!--{if $archivecache}-->
				<ul>
					<li>
						<h3>归档</h3>
						<ul>
						<!--{loop $archivecache $key $data}-->
							<li><a title="$key" href="$data[url]">$key</a> <span>($data[num])</span></li>
						<!--{/loop}-->
						<!--{if $archivenum > 12}-->
							<li><a href="$archives_url">更多...</a></li>
						<!--{/if}-->
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<!--{if $options['hottags_shownum']}-->
				<ul>
					<li>
						<h3>热门标签</h3>
						<!--{if !$tagcache}-->
							<ul>
								<li>没有任何标签</li>
							</ul>
						<!--{else}-->
							<div class="tags">
							<!--{loop $tagcache $data}-->
								<span style="line-height:160%;font-size:$data[fontsize]px;margin-right:5px;"><a href="$data[url]" title="使用次数: $data[count]">$data[name]</a></span>
							<!--{/loop}-->
							</div>
						<!--{/if}-->
					</li>
				</ul>
			<!--{/if}-->

			<!--{if $options['recentcomment_num']}-->
				<ul>
					<li>
						<h3>最新评论</h3>
						<ul>
							<!--{if !$newcommentcache}-->
								<li>没有任何评论</li>
							<!--{else}-->
								<!--{loop $newcommentcache $data}-->
								<li class="rc_item">
									<div class="rc_avatar rc_left"><img alt="" class="index_avatar" src="$data[avatardb][src]" width="$data[avatardb][size]" height="$data[avatardb][size]" /></div>
									<div class="rc_info"><span class="author_name"><a href="$data[article_url]">$data[author]</a></span></div>
									<div class="rc_excerpt">$data[content]<span class="rc_expand"><a title="$data[title]" href="$data[article_url]">»</a></span></div>
								</li>
								<!--{/loop}-->
							<!--{/if}-->
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<!--{if $options['show_statistics']}-->
				<ul>
					<li>
						<h3>统计</h3>
						<ul>
							<li>文章: <span class="num">$stats[article_count]</span> 篇</li>
							<li>评论: <span class="num">$stats[comment_count]</span> 条</li>
							<li>标签: <span class="num">$stats[tag_count]</span> 个</li>
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<!--{if $linkcache}-->
				<ul>
					<li>
						<h3>友情链接</h3>
						<ul>
							<!--{if $linkcache}-->
								<!--{loop $linkcache $data}-->
								<li><a href="$data[url]" target="_blank" title="$data[note]">$data[name]</a></li>
								<!--{/loop}-->
							<!--{/if}-->
							<li><a href="$links_url">更多...</a></li>
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<ul>
				<li>
					<h3>其他</h3>
					<ul>
						<!--{if $options['rss_enable']}-->
						<li><a href="$options[url]rss.php">RSS 2.0</a></li>
						<!--{/if}-->
						<!--{if $options['wap_enable']}-->
						<li><a href="$options[url]wap/">WAP</a></li>
						<!--{/if}-->
						<li><a href="http://validator.w3.org/check?uri=referer" target="_blank">HTML 5</a></li>
						<!--{if $options['icp']}-->
						<li><a href="http://www.miibeian.gov.cn/" target="_blank">$options[icp]</a></li>
						<!--{/if}-->
					</ul>
				</li>
			</ul>

		</aside>
		<section class="clear"></section>
	</section><!--content-->