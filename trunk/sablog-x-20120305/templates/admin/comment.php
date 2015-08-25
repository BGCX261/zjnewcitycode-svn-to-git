<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				<ul class="submenu">
					<!--{if $action == 'list'}-->
						<li><a href="cp.php?job=comment&amp;action=list"{if !$view && !$type && !$articleid} class="current"{/if}>全部<span class="count">($alltotal)</span></a></li>
						<li>|</li>
						<li><a href="cp.php?job=comment&amp;action=list&amp;view=hidden"{if $view == 'hidden'} class="current"{/if}>隐藏<span class="count">($hiddentotal)</span></a></li>
						<li><a href="cp.php?job=comment&amp;action=list&amp;view=display"{if $view == 'display'} class="current"{/if}>显示<span class="count">($displaytotal)</span></a></li>
						<li>|</li>
						<li><a href="cp.php?job=comment&amp;action=list&amp;type=comment"{if $type == 'comment'} class="current"{/if}>评论<span class="count">($ctotal)</span></a></li>
						<li><a href="cp.php?job=comment&amp;action=list&amp;type=trackback"{if $type == 'trackback'} class="current"{/if}>引用<span class="count">($ttotal)</span></a></li>
					<!--{else}-->
						<li><a href="cp.php?job=article">评论管理</a></li>
					<!--{/if}-->
				</ul>

				<h2><a href="cp.php?job=comment">评论管理</a>$navlink_L</h2>
				<div id="main">

			<!--{if $action == 'list'}-->

				<form action="cp.php?job=comment" method="post" name="form1" id="form1">
					<input type="hidden" name="formhash" value="$formhash" />

					<div class="pageinfo">
						<div class="multipage-submit">
							<input type="button" onclick="javascript:setdoit('hidden');" value="隐藏" />
							<input type="button" onclick="javascript:setdoit('display');" value="显示" />
							<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:setdoit('del');}else{return;}" value="删除" />
							<a class="buttons chkall" href="###">全选</a>,
							<a class="buttons dechkall" href="###">取消</a>
						</div>
						<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
						<div class="clear"></div>
					</div>

					<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<thead>
							<tr>
								<th width="4%">&nbsp;</th>
								<th width="20%">作者</th>
								<th width="60%">内容</th>
								<th width="16%">日期 / 状态</th>
							</tr>
						</thead>
						<tbody>
						<!--{if $total}-->
							<!--{loop $commentdb $comment}-->
							<tr>
								<td class="check-column"><input type="checkbox" name="selectall[]" value="$comment[commentid]" /></td>
								<td>
									<!--{if $comment[avatardb]}-->
										<img alt="" class="comment-avatar" src="$comment[avatardb][src]" width="$comment[avatardb][size]" height="$comment[avatardb][size]" />
									<!--{/if}-->
									<strong>$comment[author]</strong><br />

									<a href="cp.php?job=comment&amp;action=list&amp;ip=$comment[ipaddress]" title="查看此IP同一C段发表的评论">$comment[ipaddress]</a>
								
									<!--{if $comment['email']}-->
										<br /><a href="mailto:{$comment[email]}" target="_blank">$comment[email]</a>
									<!--{/if}-->

									<!--{if $comment['url']}-->
										<br /><a href="$comment[url]" target="_blank">$comment[url]</a>
									<!--{/if}-->
								</td>
								<td class="break">
									<div class="comment-title">发表在 : <a href="{$options[url]}?action=show&amp;id=$comment[articleid]" target="_blank">$comment[title]</a></div>
									<div>$comment[content]</div>
									<div class="row-actions">
										<a href="cp.php?job=comment&amp;action=visible&amp;commentid=$comment[commentid]&amp;articleid=$articleid"><!--{if $comment['visible']}-->隐藏<!--{else}-->显示<!--{/if}--></a> | 
										<a href="cp.php?job=comment&amp;action=mod&amp;commentid=$comment[commentid]&amp;articleid=$articleid">编辑</a> | 
										<a href="cp.php?job=comment&action=del&amp;commentid=$comment[commentid]&amp;articleid=$articleid" onclick="return confirm('你确定要删除此条评论吗?');">删除</a>
									</div>
								</td>

								<td>$comment[dateline]<br /><a href="cp.php?job=comment&amp;action=visible&amp;commentid=$comment[commentid]&amp;articleid=$articleid"><!--{if $comment['visible']}--><span class="yes">显示</span><!--{else}--><span class="no">隐藏</span><!--{/if}--></a></td>
							</tr>
							<!--{/loop}-->
						<!--{else}-->
							<tr>
								<td colspan="4">没有找到任何评论</td>
							</tr>
						<!--{/if}-->
						</tbody>
					</table>

					<input type="hidden" name="articleid" value="$articleid" />
					<input type="hidden" id="doit" name="doit" value=""/>
					<input type="hidden" name="action" value="domore{$action}" />

					<div class="pageinfo">
						<div class="multipage-submit">
							<input type="button" onclick="javascript:setdoit('hidden');" value="隐藏" />
							<input type="button" onclick="javascript:setdoit('display');" value="显示" />
							<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:setdoit('del');}else{return;}" value="删除" />
							<a class="buttons chkall" href="###">全选</a>,
							<a class="buttons dechkall" href="###">取消</a>
						</div>
						<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
						<div class="clear"></div>
					</div>
				</form>
			<!--{elseif $action == 'mod'}-->
				<form action="cp.php?job=comment" method="post" name="form1" id="form1">
				<input type="hidden" name="formhash" value="$formhash" />
					<h3>所在文章: <a href="cp.php?job=article&amp;action=mod&amp;articleid=$comment[articleid]">$comment[title]</a></h3>
					<div style="width:430px;float:left;">
						<fieldset>
							<legend>评论作者</legend>
							<input class="text-medium" type="text" name="author" size="50" value="$comment[author]" />
						</fieldset>
					</div>
					<div style="width:430px;float:right;">
						<fieldset>
							<legend>评论作者E-mail</legend>
							<input class="text-medium" type="text" name="email" size="50" value="$comment[email]" />
						</fieldset>
					</div>
					<div class="clear"></div>
					<fieldset>
						<legend>评论作者主页</legend>
						<input class="text-long" type="text" name="url" size="50" value="$comment[url]" />
					</fieldset>
					<fieldset>
						<legend>评论内容</legend>
						<textarea class="textarea" type="text" name="content" style="width:500px;height:250px;">$comment[content]</textarea>
					</fieldset>
					<p class="button-submit">
						<input type="hidden" name="commentid" value="$comment[commentid]" />
						<input type="hidden" name="articleid" value="$comment[articleid]" />
						<input type="hidden" name="action" value="domod" />
						<input type="submit" value="确定" />
					</p>
				</form>
			<!--{/if}-->

			</div>
			<!-- // #main -->
		</div>
		<!-- // #container -->