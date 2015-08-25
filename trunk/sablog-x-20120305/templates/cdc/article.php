<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>
<script type="text/javascript" src="$options[url]include/jscript/show.js"></script>
<script type="text/javascript">
window.onload=function(){
	fiximage('$options[attachments_thumbs_size]');
}
</script>

<!--当前位置-->
<!--{if $navtext}-->
<div class="crumb">
	<a href="$options[url]">首页</a> &gt; $navtext
</div>
<!--{/if}-->
<!--当前位置 End-->

<div id="container">
	<!--Content-->
	<div id="content">
		<!--{if $total}-->
			<!--{loop $articledb $article}-->
				<!--content_text-->
				<div class="content_text">
					<div class="title">
						<a href="$article[userurl]" class="avatar"><img alt="" class="avatar" src="$article[avatardb][src]" width="43" height="43" /></a>
						<i class="line_h"></i>

						<h3><!--{if $article['stick']}-->[置顶]<!--{/if}--> <a href="$article[url]">$article[title]</a></h3>
						<p>
							<a href="$article[userurl]">$article[username]</a> / 
							<!--{if $metadb[$article['articleid']]['category']}-->
								<!--{eval $comma = ''}-->
								<!--{loop $metadb[$article['articleid']]['category'] $cate}-->								
									$comma <a href="$cate[url]">$cate[name]</a>								
									<!--{eval $comma = ', '}-->
								<!--{/loop}-->
							<!--{/if}-->
							/ $article[dateline]
							<!--{if $metadb[$article['articleid']]['tag']}-->
								 / 关键词: 
								<!--{eval $comma = ''}-->
								<!--{loop $metadb[$article['articleid']]['tag'] $tag}-->								
									$comma <a href="$tag[url]">$tag[name]</a>								
									<!--{eval $comma = ', '}-->
								<!--{/loop}-->
							<!--{/if}--></p>
							<a onfocus="this.blur()" class="up" href="$article[url]#comments" title="{$article[comments]}条评论">$article[comments]</a>

					</div>
					<!--content_banner-->
					<div class="content_banner">
						<!--{if !$article['allowread']}-->
							<div class="text">
								<p>这篇日志被加密了。请输入密码后查看。</p>
								<div class="search">
									<form method="post" action="$article[url]">
										<input type="hidden" name="formhash" value="$formhash" />
										<input name="readpassword" type="password" value="" class="search-input" title="请输入密码" />
										<input type="submit" value="" class="so" onmouseout="this.className='so'" onmouseover="this.className='soHover'" />
									</form>
								</div>
							</div>

						<!--{else}-->

							<div class="text">
								$article[content]
							</div>

							<!--{if !$article['description'] && $article['image']}-->
								<!--{loop $article['image'] $image}-->
									<!--{if $image['thumbs']}-->
										<div class="attach">
											<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[thumb_filepath]}" border="0" alt="$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
										</div>
									<!--{else}-->
										<div class="attach">
											<a href="$options[url]attachment.php?id=$image[attachmentid]" target="_blank"><img src="{$options[url]}{$image[filepath]}" border="0" alt="$image[filename]&#13;&#13;大小: $image[filesize]&#13;尺寸: $image[thumb_width] x $image[thumb_height]&#13;浏览: $image[downloads] 次&#13;点击打开新窗口浏览全图" width="$image[thumb_width]" height="$image[thumb_height]" /></a>
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


						<!--{/if}-->
				
						<!--toolBar-->
						<script type="text/javascript">
						postArray[{$article[articleid]}]=[];
						postArray[{$article[articleid]}][0] = '$article[title]';
						postArray[{$article[articleid]}][1] = '$article[url]';
						</script>
						<div class="toolBar">
							<ul>
								<li class="browse">浏览: $article[views]</li>
								<li class="share"><a href="javascript:doFav($article[articleid]);">收藏</a></li>
							</ul>
							<a href="$article[url]" class="more">阅读全文</a>
						</div>
					</div>		 
				</div>
				<!--content_text End-->

			<!--{/loop}-->

			$multipage

		<!--{else}-->

			<p>没有任何文章</p>

		<!--{/if}-->

	</div>
	<!--Content End-->

	<!--Sidebar-->

	<div id="sidebar">

		{template sidecomm}

		<!--{if $options['recentarticle_num']}-->
			<h1>最新文章</h1>
			<div class="classify">
				<ul>
					<!--{if !$newarticlecache}-->
						<li class="none">没有任何文章</li>
					<!--{else}-->
						<!--{eval $i=0}-->
						<!--{loop $newarticlecache $data}-->
						<!--{eval $i++}-->
						<li{if count($newarticlecache) == $i} class="none"{/if}><a href="$data[article_url]" title="$data[title]&#13;$data[dateline]">$data[trimmed_title]</a></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			</div>
		<!--{/if}-->

		<!--{if $archivecache}-->
			<h1>归档</h1>
			<div class="classify">
				<ul>
					<!--{loop $archivecache $key $data}-->
						<li><a href="$data[url]">$key</a> <span>($data[num])</span></li>
					<!--{/loop}-->
					<!--{if $archivenum > 12}-->
						<li class="none"><a href="$archives_url">更多...</a></li>
					<!--{/if}-->
				</ul>
			</div>
		<!--{/if}-->

		<!--{if $options['recentcomment_num']}-->
			<h1>最新评论</h1>
			<div class="newcm">
				<!--{if !$newcommentcache}-->
					<p>没有任何评论</p>
				<!--{else}-->
					<!--{eval $i=0}-->
					<!--{loop $newcommentcache $data}-->
					<!--{eval $i++}-->
					<dl{if count($newcommentcache) == $i} class="none"{/if}>
						<dt>
							<b><!--{if $data['url']}--><a href="$data[url]">$data[author]</a><!--{else}-->$data[author]<!--{/if}--></b>
							<em>$data[dateline]</em>
						</dt>
						<dd>$data[content] <a title="$data[title]" href="$data[article_url]">»</a></dd>    
					</dl>
					<!--{/loop}-->
				<!--{/if}-->
			</div>
		<!--{/if}-->

		<!--{if $options['hottags_shownum']}-->
			<h1>热门标签</h1>
			<div class="classify">
				<ul>
					<!--{if !$tagcache}-->
						<li>没有任何标签</li>
					<!--{else}-->
						<!--{eval $i=0}-->
						<!--{loop $tagcache $data}-->
						<!--{eval $i++}-->
						<li{if count($tagcache) == $i} class="none"{/if}><a href="$data[url]">$data[name]</a> <span>($data[count])</span></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			</div>
		<!--{/if}-->


		<!--友情链接-->
		<!--{if $linkcache && !$page}-->
			<h1>友情链接</h1>
			<div class="classify">
				<ul>
					<!--{loop $linkcache $data}-->
						<li><a href="$data[url]" target="_blank" title="$data[note]">$data[name]</a></li>
					<!--{/loop}-->
					<li class="none"><a href="$links_url">更多...</a></li>
				</ul>
			</div>
		<!--{/if}-->
		<!--友情链接 End-->

		<!--{if $options['show_statistics']}-->
			<h1>统计</h1>
			<div class="classify">
				<ul>
					<li>文章: <span class="num">$stats[article_count]</span> 篇</li>
					<li>评论: <span class="num">$stats[comment_count]</span> 条</li>
					<li class="none">标签: <span class="num">$stats[tag_count]</span> 个</li>
				</ul>
			</div>
		<!--{/if}-->
	</div>
	<!--Sidebar End-->
</div>
<!--Container End-->