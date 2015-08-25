<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				
				<ul class="submenu">
					<li><a href="cp.php?job=category&amp;action=catelist"{if $action == 'catelist'} class="current"{/if}>分类管理</a></li>
					<li>|</li>
					<li><a href="cp.php?job=category&amp;action=taglist"{if $action == 'taglist'} class="current"{/if}>标签管理</a></li>
				</ul>

				<!-- h2 stays for breadcrumbs -->
				<h2><a href="cp.php?job=category">$catenav</a>$navlink_L</h2>

				<div id="main">

				<!--{if $action == 'catelist' || $action == 'taglist'}-->
					<div style="float:left;width:615px;">

						<form action="cp.php?job=category" method="post" id="form1" name="form1">
						<input type="hidden" name="formhash" value="$formhash" />

							<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
								<thead>
									<tr>
										<th width="4%">&nbsp;</th>
										<th width="10%">排序</th>
										<th>名称</th>
										<th>URL</th>
										<th>文章数</th>
									</tr>
								</thead>
								<tbody>
								<!--{if $total}-->
									<!--{loop $metadb $meta}-->
									<tr>
										<td class="check-column"><input type="checkbox" name="selectall[]" value="$meta[mid]" /></td>
										<td><input class="text-medium" style="text-align: center;font-size: 11px;" type="text" value="$meta[displayorder]" name="displayorder[{$meta[mid]}]" size="1" /></td>
										<td><a href="cp.php?job=category&action={$action}&mid=$meta[mid]">$meta[name]</a></td>
										<td>$meta[slug]</td>
										<td nowrap="nowrap"><a href="cp.php?job=article&mid=$meta[mid]">$meta[count] 篇</a></td>
									</tr>
									<!--{/loop}-->
								<!--{else}-->
									<tr>
										<td colspan="5">没有找到任何{$name}</td>
									</tr>
								<!--{/if}-->
								</tbody>
							</table>

							<input type="hidden" name="action" id="action" value="update" />
							<input type="hidden" name="type" value="$type" />

							<div class="pageinfo">
								<div class="multipage-submit">
									<input type="button" onclick="javascript:doaction('update');" value="更新排序" />
									<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:doaction('delete');}else{return;}" value="删除" />
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
							<form action="cp.php?job=category" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<p>
								<label for="new_name">名称</label>
								<input class="text-medium" type="text" name="new_name" id="new_name" size="35" maxlength="50" value="$new_name" />
							</p>
							<p>
								<label for="new_url">URL</label>
								<input class="text-medium" type="text" name="new_url" id="new_url" size="35" maxlength="50" value="$new_url" /><br />
								<span>半角字母、数字、下划线和减号.</span>
							</p>
							<p class="button-submit">
								<input type="hidden" name="action" value="$do" />
								<input type="hidden" name="type" value="$type" />
								<input type="hidden" name="mid" value="$mid" />
								<input type="submit" value="确定" />
							</p>
							</form>
						</fieldset>
					</div>
					<div class="clear"></div>
				<!--{/if}-->
				</div>
				<!-- // #main -->
			</div>