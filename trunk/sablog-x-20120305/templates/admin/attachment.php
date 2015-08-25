<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				<ul class="submenu">
					<!--{if $action == 'list'}-->
						<li><a href="cp.php?job=attachment&amp;action=repair"{if $action == 'repair'} class="current"{/if}>附件整理</a></li>
						<li>|</li>
						<li><a href="cp.php?job=attachment&amp;type=$type"{if !$view} class="current"{/if}>全部<span class="count">($atttotal)</span></a></li>
						<li><a href="cp.php?job=attachment&amp;action=list&amp;type=$type&amp;view=image"{if $view == 'image'} class="current"{/if}>图片附件<span class="count">($imgtotal)</span></a></li>
						<li><a href="cp.php?job=attachment&amp;action=list&amp;type=$type&amp;view=file"{if $view == 'file'} class="current"{/if}>非图片附件<span class="count">($filetotal)</span></a></li>
						<li><a href="cp.php?job=attachment&amp;action=list&amp;type=$type&amp;view=noarticle"{if $view == 'noarticle'} class="current"{/if}>未附加附件<span class="count">($noarticletotal)</span></a></li>
					<!--{else}-->
						<li><a href="cp.php?job=attachment">附件管理</a></li>
					<!--{/if}-->
				</ul>
                
                <!-- h2 stays for breadcrumbs -->
                <h2><a href="cp.php?job=attachment">附件管理</a>$navlink_L</h2>
                <div id="main">

			<!--{if $action == 'list'}-->

				<script type="text/javascript">
				var attachurl = '{$options[url]}{$options[attachments_dir]}';
				var allowpostattach = 0;
				</script>
				<script type="text/javascript" src="$options[url]include/jscript/attachment.js"></script>

				<form action="cp.php?job=attachment" method="post" name="form1" id="form1">
					<input type="hidden" name="formhash" value="$formhash" />
		
					<div class="popupmenu_popup" id="imgpreview_menu" style="position:absolute;width:320px;display:none;"></div>

					<table width="100%" id="uploadlist" align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<thead>
							<tr>
								<th width="4%">&nbsp;</th>
								<th width="40">ID</th>
								<th width="230">文件</th>
								<th>大小</th>
								<th>上传时间</th>
								<th>下载次数</th>
								<th width="240">文章</th>
							</tr>
						</thead>
						<tbody>
						<!--{if $total}-->
							<!--{loop $attachdb $attach}-->
							
							<!--{eval $attach[articletitle] = trimmed_title($attach[article], 20)}-->
							<tr>
								<td class="check-column"><input type="checkbox" name="selectall[]" value="$attach[attachmentid]" /></td>
								<td class="check-column">$attach[attachmentid]</td>
								<td>
									<!--{if $attach['isimage']}-->
										<span onmouseover="showpreview(this, 'imgpreview_$attach[attachmentid]')">
											<a href="./attachment.php?id=$attach[attachmentid]" target="_blank" title="$attach[filepath]">$attach[filename]</a>
										</span>
										<div id="imgpreview_$attach[attachmentid]" style="display:none"><img id="preview_$attach[attachmentid]" width="$attach[thumb_width]" height="$attach[thumb_height]" src="$attach[thumb_filepath]" /></div>
									<!--{else}-->
										<a href="attachment.php?id=$attach[attachmentid]" target="_blank" title="$attach[filepath]">$attach[filename]</a>
									<!--{/if}-->
									<br />$attach[filetype]
								</td>
								<td><span title="所在目录: $attach[subdir]">$attach[filesize]</span></td>
								<td>$attach[dateline]</td>
								<td>$attach[downloads]</td>
								<td>
									<!--{if $attach['articleid']}-->
										<a title="$attach[article]" href="index.php?action=show&id=$attach[articleid]" target="_blank">$attach[articletitle]</a> [<!--{if $attach['visible']}--><span class="yes">显示</span><!--{else}--><span class="no">隐藏</span><!--{/if}-->]
									<!--{else}-->
										尚未附加到文章
									<!--{/if}-->
								</td>
							</tr>
							<!--{/loop}-->
						<!--{else}-->
							<tr>
								<td colspan="7">没有找到任何附件</td>
							</tr>
						<!--{/if}-->
						</tbody>
					</table>

					<input type="hidden" name="action" value="delattachments" />
					<input type="hidden" name="articleid" value="$articleid" />
					<div class="pageinfo">
						<div class="multipage-submit">
							<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:submit('form1');}else{return;}" value="删除" />
							<a class="buttons chkall" href="###">全选</a>,
							<a class="buttons dechkall" href="###">取消</a>
						</div>
						<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
						<div class="clear"></div>
					</div>
				</form>
			<!--{elseif $action == 'repair'}-->
				<h3>附件修复</h3>
				<form action="cp.php?job=attachment" method="post">
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="action" value="dorepair" />
				<p>本功能清除数据库那存在附件记录而没有附件文件的冗余数据，游戏中的附件记录也将同时更新。</p>
				<p>如果附件较多，过程会比较久，请耐心等候。</p>
				<p>建议定期执行。</p>
				<p class="button-submit"><input type="submit" value="确定" /></p>
				</form>
				<h3>附件清理</h3>
				<form action="cp.php?job=attachment" method="post">
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="action" value="doclear" />
				<p>本功能删除数据库中没有记录而实际存在的附件，可有效清理冗余附件。</p>
				<p>循环处理数量: <input class="formfield" type="text" name="percount" value="500" size="5" /></p>
				<p class="button-submit"><input type="submit" value="确定" /></p>
				</form>
			<!--{/if}-->
				</div>
				<!-- // #main -->
                <div class="clear"></div>
            </div>
            <!-- // #container -->