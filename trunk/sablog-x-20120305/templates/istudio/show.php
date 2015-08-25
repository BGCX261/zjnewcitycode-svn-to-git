<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<script type="text/javascript">
	var postminchars = parseInt("$options[comment_min_len]");
	var postmaxchars = parseInt("$options[comment_max_len]");
	var blogurl = '$options[url]';
	</script>
	<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>
	<script type="text/javascript" src="$options[url]include/jscript/show.js"></script>
	<script type="text/javascript">
	window.onload=function(){
		fiximage('$options[attachments_thumbs_size]');
	}
	</script>
	<section id="content">
		<section class="postlist">
			<article id="post-{$article[articleid]}">
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

					<div class="needpwd"><form action="$article[url]" method="post">这篇日志被加密了。请输入密码后查看。<br /><input class="text" type="password" name="readpassword" style="margin-right:5px;"> <button type="submit">提交</button></form></div>

				<!--{else}-->

					<div class="entry">
					
						$article[content]

						<!--{if $article['image']}-->
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

						<!--{if $article['file']}-->
							<!--{loop $article['file'] $file}-->
								<div class="but_down">
									<a title="$file[filesize] - 下载次数:$file[downloads] - 上传时间:$file[dateline]" href="$options[url]attachment.php?id=$file[attachmentid]" target="_blank"><span>$file[filename]</span></a>
									<div class="clear"></div>
								</div>
							<!--{/loop}-->
						<!--{/if}-->
					
					</div>

					<!--{if $metadb[$article['articleid']]['tag']}-->
						<h2 class="postmeta">关键词: 
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
			</article>

			<!--{if $article['allowread']}-->

				<!--{if $options['related_shownum'] && $related_total > 1 && $relids != $article['articleid']}-->
					<div class="post">
						<div class="title">
							<h3>相关文章</h3>
						</div>
						<div class="lesscontent">
							<ul class="related-title">
							<!--{loop $titledb $title}-->
								<li><a href="$title[url]">$title[title]</a></li>
							<!--{/loop}-->
							</ul>
						</div>
					</div>
				<!--{/if}-->

				<a id="comments"></a>

				<div class="clear-height"></div>
				<div class="postcomment">
					<div class="respond">访客评论
						<!--{if $options['enable_trackback'] && !$article['closetrackback']}-->
							<span><a class="trackbackurl" href="javascript:void(0);" onclick="showajaxdiv('$options[url]getxml.php?action=trackback&amp;id=$article[articleid]');">点击获得Trackback地址</a></span>
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
				</div>

				<!--{if !$article['closecomment'] && !$options['close_comment']}-->
					<div class="postcomment">
						<div id="comment-place">
							<div id="comment-post">
								<a id="addcomment"></a>
								<div class="respond">发表评论<span><a rel="nofollow" id="cancel-reply" href="javascript:void(0);" style="display:none;" onclick="cancelReply()">取消回复</a></span></div>
								<form method="post" name="form" id="form" class="reply" action="$options[url]post.php" onsubmit="return checkform();">
									<input type="hidden" name="articleid" value="$article[articleid]">
									<input type="hidden" name="formhash" value="$formhash">
									<input type='hidden' name="comment_parent" id="comment_parent" value="0" />
									<!--{if $sax_uid}-->

										<p>已经登陆为 <strong>$sax_user</strong> <a href="$options[url]cp.php?action=logout">注销</a></p>

									<!--{else}-->
										<p>
											<input type="text" class="text" tabindex="1" size="22" maxlength="30" value="$_COOKIE[comment_username]" id="username" name="username" required>
											<label for="username">名字 (必填, 如果是注册用户请先登陆)</label>
										</p>
										<p>
											<input type="email" class="text" tabindex="2" size="22" maxlength="50" value="$_COOKIE[comment_email]" id="email" name="email" required>
											<label for="email">E-mail (必填, 不会被透露)</label>
										</p>
										<p>
											<input type="url" class="text" tabindex="3" size="22" value="$_COOKIE[comment_url]" id="url" name="url">
											<label for="url">网址 (选填, 要包含http://)</label>
										</p>
									<!--{/if}-->

									<p>
										<textarea tabindex="4" style="width:90%;height:200px;" id="cmcontent" name="content" onkeydown="javascript:ctlent(event);" required>$_COOKIE[cmcontent]</textarea>						
									</p>


									<!--{if $options['seccode'] && $sax_group != 1 && $sax_group !=2}-->
										<p>
											<input class="seccode" onfocus="updateseccode();this.onfocus = null;" name="clientcode" id="clientcode" value="" tabindex="5" size="4" maxlength="4">
											<label for="clientcode">验证码 (必填)</label>
											<div id="seccodeimage"></div>
										</p>
									<!--{/if}-->

									<p>
										<input type="hidden" name="action" value="addcomment">
										<input type="submit" onmouseout="this.className='submit'" onmousemove="this.className='submit_move'" onmousedown="this.className='submit_down'" class="submit" value="发表评论" tabindex="5" id="submit" name="submit">
									</p>

								</form>
							</div>
						</div>
					</div>
				<!--{/if}-->

			<!--{/if}-->

		</section>
		<aside>

			{template sidecomm}

			<!--{if $options['recentarticle_num']}-->
				<ul>
					<li>
						<h3>最新文章</h3>
						<ul>
						<!--{if !$newarticlecache}-->
							<li>没有任何文章</li>
						<!--{else}-->
							<!--{loop $newarticlecache $data}-->
							<li><a title="$data[title]" href="$data[article_url]">$data[trimmed_title]</a></li>
							<!--{/loop}-->
						<!--{/if}-->
						</ul>
					</li>
				</ul>
			<!--{/if}-->

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
	</section>