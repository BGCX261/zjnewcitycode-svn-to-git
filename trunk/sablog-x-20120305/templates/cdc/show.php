<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>
<script type="text/javascript" src="$options[url]include/jscript/show.js"></script>
<script type="text/javascript" src="$options[url]include/jscript/comment.js"></script>
<script type="text/javascript">
window.onload=function(){
	fiximage('$options[attachments_thumbs_size]');
}
postArray[{$article[articleid]}]=[];
postArray[{$article[articleid]}][0] = '$article[title]';
postArray[{$article[articleid]}][1] = '$article[url]';
</script>

<div id="container">
	<!--Content-->
	<div id="content">

		<!--content_text-->
		<div class="content_text3">
			<div class="title">
				<a href="$article[userurl]" class="avatar"><img alt="" class="avatar" src="$article[avatardb][src]" width="43" height="43" /></a>
				<i class="line_h"></i>

				<h3>$article[title]</h3>
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

					<!--{if $article['image']}-->
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

					<!--{if $article['file']}-->
						<!--{loop $article['file'] $file}-->
							<div class="but_down">
								<a title="$file[filesize] - 下载次数:$file[downloads] - 上传时间:$file[dateline]" href="$options[url]attachment.php?id=$file[attachmentid]" target="_blank"><span>$file[filename]</span></a>
								<div class="clear"></div>
							</div>
						<!--{/loop}-->
					<!--{/if}-->


				<!--{/if}-->
		
				<!--appendInfo-->
				<div class="appendInfo">
					<ul>
						<li class="come_from">(<span>本文出自{$options[name]}，转载时请注明出处</span>)</li>
						<li class="share"><a href="javascript:doFav($article[articleid]);">收藏</a></li>
					</ul>
					<!--{if $options['show_n_p_title']}-->
						<!--{if $article['previous_id']}-->
							<p>上一篇:<a href="$article[previous_url]">$article[previous_title]</a></p>
						<!--{/if}-->
						<br />
						<!--{if $article['next_id']}-->
							<p>下一篇:<a href="$article[next_url]">$article[next_title]</a></p>
						<!--{/if}-->
					<!--{/if}-->
				</div>
			</div>


			<!--{if $article['allowread']}-->

				<!--{if $options['related_shownum'] && $related_total > 1 && $relids != $article['articleid']}-->
					<div class="relatearticle">
						<p><b class="left">相关文章</b></p>
						<ul class="related-title">
						<!--{eval $i=0}-->
						<!--{loop $titledb $title}-->
						<!--{eval $i++}-->
							<li{if count($titledb) == $i} class="none"{/if}><a href="$title[url]">$title[title]</a> (<span>$title[comments]</span> 条评论)</li>
						<!--{/loop}-->
						</ul>
					</div>
				<!--{/if}-->

				<div class="clear"></div>
				<!--Comment-->
				<div class="comment_t" id="comments">
					<p><b class="left">文章评论</b> <b class="right"> 共有{$article[comments]}条评论(<a href="#commentform">写评论</a><!--{if $options['enable_trackback'] && !$article['closetrackback']}-->, <a href="javascript:void(0);" onclick="showajaxdiv('$options[url]getxml.php?action=trackback&amp;id=$article[articleid]');">点击获得Trackback地址</a><!--{/if}-->)</b></p>
				</div>
				<div class="clear"></div>
				<!--{if $commentStacks}-->
					<!--{eval blog_comments($commentdb, $commentStacks, $multipage)}-->

					<script type="text/javascript">
					$('.p_bar a').each(function(i){
						this.href+="#comments";
					});  
					</script>
				<!--{else}-->
					<div class="nocomment">
						目前还没有人评论，您发表点看法？
					</div>
				<!--{/if}-->

				<!--Comment End-->

				<!--{if !$article['closecomment'] && !$options['close_comment']}-->
					<!--commentform-->
					<div class="commentform" id="respond">
						<script type="text/javascript">						
						function commentform_submit(){
							if ($('#username').length > 0 && $('#username').val() == "") {
								alert("请输入您的名字.");
								$('#username').focus();
								return;
							}
							if ($('#email').length > 0 && $('#email').val() == "") {
								alert("请输入您的电子邮件.");
								$('#email').focus();
								return;
							}
							if ($('#clientcode').length > 0 && $('#clientcode').val() == "")	{
								alert("请输入验证码.");
								return;
							}
							if ($('#cmcontent').val() == "")	{
								alert("请输入内容.");
								$('#cmcontent').focus();
								return;
							}
							if (((postminchars != 0 && $('#cmcontent').val().length < postminchars) || (postmaxchars != 0 && $('#cmcontent').val().length > postmaxchars))) {
								alert("您的评论内容长度不符合要求。\n\n当前长度: "+$('#cmcontent').val().length+" 字节\n系统限制: "+postminchars+" 到 "+postmaxchars+" 字节");
								return;
							}
							$('#commentform').submit();
						}
						function ctlent_form(event) {
							if((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83)) {
								commentform_submit();
							}
						}
						</script>
						<form method="post" name="commentform" id="commentform" action="$options[url]post.php">
						<input type="hidden" name="articleid" id='articleid' value="$article[articleid]" />
						<input type="hidden" name="formhash" value="$formhash" />
						<input type="hidden" name="action" value="addcomment" />
						<input type='hidden' name='comment_parent' id='comment_parent' value='0' />
						<h4>发表评论 <small><a rel="nofollow" id="cancel-comment-reply-link" href="$article[url]#respond" style="display:none;">取消回复</a></small> <a href="#top" class="back_top"></a></h4>
						<div class="inputul">
							<!--{if $sax_uid}-->

								<p class="logininfo">已经登陆为 <strong>$sax_user</strong> [ <a href="$options[url]cp.php?action=logout">注销</a> ]</p>

							<!--{else}-->

									<p>
										<input id="username" name="username" type="text" class="input" value="$_COOKIE[comment_username]" tabindex="1" />
										<span class="input_desc">名字 (必填,如果已是注册用户请先登陆)</span>
									</p>

									<p><input id="email" name="email" type="text" class="input" value="$_COOKIE[comment_email]" tabindex="2" /><span class="input_desc">邮箱 (不会被公开,必填)</span></p>

									<p><input id="url" name="url" type="text" class="input" value="$_COOKIE[comment_url]" tabindex="3" /><span class="input_desc">网站</span></p>

							<!--{/if}-->

							<p><textarea id="cmcontent" name="content" cols="" rows="" tabindex="4" onkeydown="ctlent_form(event);">$_COOKIE[cmcontent]</textarea></p>

							<!--{if $options['seccode'] && $sax_group != 1 && $sax_group !=2}-->
								<p>
									<input class="input seccode" onfocus="updateseccode();this.onfocus = null;" name="clientcode" id="clientcode" value="" tabindex="5" size="4" maxlength="4">
									<span class="input_desc">验证码 (必填)</span>
									<div id="seccodeimage"></div>
								</p>
							<!--{/if}-->

							<p class="bnt"><a onfocus="this.blur()" href="javascript:commentform_submit();">发&nbsp;表</a><p>
						</div>
						</form>
					</div>
					<!--commentform End-->
				<!--{/if}-->

			<!--{/if}-->
		 
		</div>
		<!--content_text End-->

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

	</div>
	<!--Sidebar End-->
</div>
<!--Container End-->