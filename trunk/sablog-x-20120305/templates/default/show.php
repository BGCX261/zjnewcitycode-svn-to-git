<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<div id="page">
		<div id="wrap">
			<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>
			<script type="text/javascript" src="$options[url]include/jscript/show.js"></script>
			<script type="text/javascript">
			window.onload=function(){
				fiximage('$options[attachments_thumbs_size]');
			}
			</script>
			<h1 class="title"><a href="$article[url]">$article[title]</a></h1>

			<p class="post-date"><a href="$article[userurl]">$article[username]</a> 发表于 $article[dateline]. 
				发表在:
				<!--{if $metadb[$article['articleid']]['category']}-->
					<!--{eval $comma = ''}-->
					<!--{loop $metadb[$article['articleid']]['category'] $cate}-->								
						$comma <a href="$cate[url]">$cate[name]</a>								
						<!--{eval $comma = ', '}-->
					<!--{/loop}-->
				<!--{/if}-->
			</p>

			<!--{if !$article['allowread']}-->

				<div class="needpwd"><form action="$article[url]" method="post">这篇日志被加密了。请输入密码后查看。<br /><input class="formfield" type="password" name="readpassword" style="margin-right:5px;" /> <button type="submit">提交</button></form></div>

			<!--{else}-->

				<div class="post-body">$article[content]</div>

				<!--{if $article['image']}-->
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

				<!--{if $article['file']}-->
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

			<!--{/if}-->

			<!--{if $options['show_n_p_title']}-->
				<p id="article-other-title">
				<!--{if $article['previous_id']}-->
					上一篇:
					<a href="$article[previous_url]">$article[previous_title]</a>
				<!--{/if}-->
				<br />
				<!--{if $article['next_id']}-->
					下一篇:
					<a href="$article[next_url]">$article[next_title]</a>
				<!--{/if}-->
				</p>
			<!--{/if}-->

			<!--{if $article['allowread']}-->

				<!--{if $options['related_shownum'] && $related_total > 1 && $relids != $article['articleid']}-->
					<div class="clear-height"></div>
					<div class="title">相关文章</div>
					<div class="lesscontent">
						<ul class="related-title">
						<!--{loop $titledb $title}-->
							<li><a href="$title[url]">$title[title]</a></li>
						<!--{/loop}-->
						</ul>
					</div>
				<!--{/if}-->

				<a id="comments"></a>
				<div class="clear-height"></div>
				<div class="title">访客评论
					<!--{if $options['enable_trackback'] && !$article['closetrackback']}-->
						<a class="trackbackurl" href="javascript:void(0);" onclick="showajaxdiv('$options[url]getxml.php?action=trackback&amp;id=$article[articleid]');">点击获得Trackback地址</a>
					<!--{/if}-->
				</div>
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

				<!--{if !$article['closecomment'] && !$options['close_comment']}-->
					<div class="clear-height"></div>
					<div id="comment-place">
						<div id="comment-post">
							<a id="addcomment"></a>
							<div class="title">发表评论 <a rel="nofollow" id="cancel-reply" href="javascript:void(0);" style="display:none;" onclick="cancelReply()">取消回复</a></div>
							<form method="post" name="form" id="form" action="$options[url]post.php" onsubmit="return checkform();">
							<input type="hidden" name="articleid" value="$article[articleid]" />
							<input type="hidden" name="formhash" value="$formhash" />
							<input type='hidden' name="comment_parent" id="comment_parent" value="0" />
							<div class="formbox">
							<!--{if $sax_uid}-->

								<p>已经登陆为 <strong>$sax_user</strong> [<a href="$options[url]cp.php?action=logout">注销</a>]</p>

							<!--{else}-->
								<p><label for="username">
									名字 (必填,如果已是注册用户请先登陆):<br /><input name="username" id="username" type="text" value="$_COOKIE[comment_username]" tabindex="1" class="formfield" style="width:400px;" />
									</label></p>
								<p><label for="email">
									E-mail (必填,不会被显示在前台,仅为方便联系):<br /><input name="email" id="email" type="text" value="$_COOKIE[comment_email]" tabindex="2" class="formfield" style="width: 400px;" />
								</label></p>
								<p><label for="url">
									网址 (选填, 要包含http://):<br /><input type="text" name="url" id="url" value="$_COOKIE[comment_url]" tabindex="3" class="formfield" style="width: 400px;" />
								</label></p>
							<!--{/if}-->

							<p id="reply_desc" style="display:none;color:#f00;">是回复别人的评论系统将会发送邮件通知给被评论人</p>

							<p>评论内容 (必填):
							
							<br /><textarea name="content" id="content" cols="70" rows="10" tabindex="4" onkeydown="ctlent(event);" class="formfield">$_COOKIE[cmcontent]</textarea>
						
							<!--{if $options['seccode'] && $sax_group != 1 && $sax_group !=2}-->
								<p><label for="clientcode">
									验证码(*):<br />
									<input onfocus="updateseccode();this.onfocus = null;" name="clientcode" id="clientcode" value="" tabindex="3" class="formfield" size="6" maxlength="6" />
									<div id="seccodeimage"></div>
								</label></p>
							<!--{/if}-->

							<p>
								<input type="hidden" name="action" value="addcomment" />
								<button type="submit" id="submit" name="submit">发表评论</button>
							</p>

							</div>
							</form>
						</div><!--/comment-post-->
					</div><!--/comment-place-->
				<!--{/if}-->

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

			<!--{if $options['recentarticle_num']}-->
				<div class="stitle">最新文章</div>
				<ul>
					<!--{if !$newarticlecache}-->
						<li>没有任何文章</li>
					<!--{else}-->
						<!--{loop $newarticlecache $data}-->
						<li><a href="$data[article_url]" title="$data[title]&#13;$data[dateline]">$data[trimmed_title]</a></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			<!--{/if}-->

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

		</div>
	</div>