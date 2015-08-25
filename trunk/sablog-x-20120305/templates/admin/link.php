<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				
				<ul class="submenu">
					<li><a href="cp.php?job=link"{if !$view} class="current"{/if}>全部<span class="count">($alltotal)</span></a></li>
					<li><a href="cp.php?job=link&amp;view=hidden"{if $view == 'hidden'} class="current"{/if}>隐藏的<span class="count">($hiddentotal)</span></a></li>
					<li><a href="cp.php?job=link&amp;view=display"{if $view == 'display'} class="current"{/if}>显示的<span class="count">($displaytotal)</span></a></li>
					<li><a href="cp.php?job=link&amp;view=home"{if $view == 'home'} class="current"{/if}>显示在首页的<span class="count">($hometotal)</span></a></li>
				</ul>

				<!-- h2 stays for breadcrumbs -->
				<h2><a href="cp.php?job=link">链接管理</a>$navlink_L</h2>
				
				<div id="main">
					<div style="float:left;width:615px;">

						<form action="cp.php?job=link" method="post" name="form1" id="form1">
						<input type="hidden" name="formhash" value="$formhash" />
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<thead>
								<tr>
									<th width="4%">&nbsp;</th>
									<th width="10%">状态</th>
									<th width="10%">首页</th>
									<th width="30%">名称</th>
									<th width="46%">地址</th>
								</tr>
							</thead>
							<tbody>
							<!--{if $total}-->
								<!--{loop $linkdb $link}-->
								<tr>
									<td class="check-column"><input type="checkbox" name="selectall[]" value="$link[linkid]" /></td>
									<td><!--{if $link['visible']}--><span class="yes">启用</span><!--{else}--><span class="no">禁用</span><!--{/if}--></td>
									<td><!--{if $link['home']}--><span class="yes">是</span><!--{else}--><span class="no">否</span><!--{/if}--></td>
									<td><a href="cp.php?job=link&amp;linkid=$link[linkid]">$link[name]</a></td>
									<td><a href="$link[url]" target="_blank">$link[url]</a></td>
								</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="5">没有找到任何链接</td>
								</tr>
							<!--{/if}-->
							</tbody>
						</table>

						<input type="hidden" id="doit" name="doit" value=""/>
						<input type="hidden" name="action" id="action" value="domorelink" />

						<div class="pageinfo">
							<div class="multipage-submit">
								<input type="button" onclick="javascript:setdoit('enable');" value="启用" />
								<input type="button" onclick="javascript:setdoit('disable');" value="禁用" />
								<input type="button" onclick="javascript:setdoit('home');" value="首页显示" />
								<input type="button" onclick="javascript:setdoit('page');" value="取消首页显示" />
								<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:setdoit('delete');}else{return;}" value="删除" />
								<a class="buttons chkall" href="###">全选</a>,
								<a class="buttons dechkall" href="###">取消</a>
							</div>
							<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
							<div class="clear"></div>
						</div>
						</form>
					</div>
					<div style="float:left;width:240px;margin-left:20px;">
						<fieldset>
							<legend>{$doname}</legend>
							<form action="cp.php?job=link" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<p>
								<label for="new_name">名称</label>
								<input class="text-medium" type="text" name="new_name" id="new_name" size="20" maxlength="50" value="$linkinfo[name]" />
								
							</p>
							<p>
								<label for="new_url">网址</label>
								<input class="text-medium" type="text" name="new_url" id="new_url" size="20" maxlength="50" value="$linkinfo[url]" />
							</p>
							<p>
								<label for="new_note">链接描述</label>
								<textarea class="textarea" id="new_note" name="new_note" style="width:200px;height:100px;">$linkinfo[note]</textarea><br />
							</p>
							<p class="button-submit">
								<input type="hidden" name="action" value="$do" />
								<input type="hidden" name="linkid" value="$linkid" />
								<input type="submit" value="确定" />
							</p>
							</form>
						</fieldset>
					</div>
					<div class="clear"></div>
				</div>
				<!-- // #main -->
			</div>