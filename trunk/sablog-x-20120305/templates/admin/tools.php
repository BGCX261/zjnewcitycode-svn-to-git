<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container">
        		<div id="sidebar">
                	<ul class="sideNav">
					<!--{loop $adminitem['tools']['submenu'] $item}-->
						<li><a href="cp.php?job=tools&amp;action=$item[action]"{if $item[action] == $action} class="active"{/if}>$item[name]</a></li>
					<!--{/loop}-->
                    </ul>
                    <!-- // .sideNav -->
                </div>    
                <!-- // #sidebar -->

                <h2 class="maintitle"><a href="cp.php?job=tools">系统维护</a>$navlink_L</h2>
                
                <div id="main-700">
					<form action="cp.php?job=tools" enctype="multipart/form-data" method="post" name="form1" id="form1">
					<input type="hidden" name="formhash" value="$formhash" />
					<!--{if in_array($action, array('backup', 'tools'))}-->
						<!--{if $action == 'backup'}-->
							<fieldset>
								<legend>建表语句格式</legend>
								<input type="radio" name="sqlcompat" value="" checked /> 默认<br />
								<input type="radio" name="sqlcompat" value="MYSQL40" /> MySQL 4.0.x<br />
								<input type="radio" name="sqlcompat" value="MYSQL41" /> MySQL 4.1.x/5.x
							</fieldset>
							<fieldset>
								<legend>字符集限定</legend>
								<input type="radio" name="addsetnames" value="1" /> 是
								<input type="radio" name="addsetnames" value="0" checked /> 否
							</fieldset>
							<fieldset>
								<legend>分卷备份大小</legend>
								<input class="text-medium" type="text" name="sizelimit" size="20" maxlength="20" value="2048" /> KB
							</fieldset>
							<fieldset>
								<legend>备份文件名</legend>
								<input class="text-medium" type="text" name="filename" size="40" maxlength="40" value="$backuppath" />
							</fieldset>
						<!--{else}-->
							<h3>选择操作</h3>
							<ul>
								<li><input type="checkbox" name="do[]" value="check" checked /> 检查表</li>
								<li><input type="checkbox" name="do[]" value="repair" checked /> 修复表</li>
								<li><input type="checkbox" name="do[]" value="analyze" checked /> 分析表</li>
								<li><input type="checkbox" name="do[]" value="optimize" checked /> 优化表</li>
							</ul>
						<!--{/if}-->
						<div class="button-submit">
							<input type="hidden" name="action" value="$act" />
							<input type="submit" value="确定" />
						</div>
					<!--{elseif $action == 'filelist'}-->

						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<thead>
								<tr>
									<th width="4%">&nbsp;</th>
									<th width="32%">文件名</th>
									<th width="18%">备份时间</th>
									<th width="14%">版本</th>
									<th width="10%">卷号</th>
									<th width="12%">文件大小</th>
									<th width="14%">操作</th>
								</tr>
							</thead>
							<tbody>
							<!--{if $noexists}-->
								<tr>
									<td colspan="8">目录不存在或无法访问, 请检查 $backupdir 目录.</td>
								</tr>
							<!--{elseif $file_i}-->
								<!--{loop $dbfiles $dbfile}-->
									<tr>
										<td class="check-column"><input type="checkbox" name="selectall[]" value="{$backupdir}/{$dbfile[filename]}" /></td>
										<td><a href="{$option[url]}data/backupdata/$dbfile[filename]" title="右键另存为保存该文件">$dbfile[filename]</a></td>
										<td>$dbfile[bktime]</td>
										<td>$dbfile[version]</td>
										<td>$dbfile[volume]</td>
										<td>$dbfile[filesize]</td>
										<td><!--{if $dbfile['volume'] == '1'}--><a href="cp.php?job=tools&action=checkresume&sqlfile=$dbfile[filepath]">导入</a><!--{else}-->无<!--{/if}--></td>
									</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="7">没有找到任何备份文件</td>
								</tr>
							<!--{/if}-->
							</tbody>
						</table>

						<input type="hidden" name="action" value="deldbfile" />

						<div class="pageinfo">
							<div class="multipage-submit">
								<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:submit('form1');}else{return;}" value="删除" />
								<a class="buttons chkall" href="###">全选</a>,
								<a class="buttons dechkall" href="###">取消</a>
							</div>
							<div class="multipage">共有{$file_i}个备份文件</div>
							<div class="clear"></div>
						</div>

				<!--{elseif $action == 'mysqlinfo'}-->
						<h3>Sablog-X数据表</h3>
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<thead>
								<tr>
									<th width="22%">名称</th>
									<th width="21%">最后更新时间</th>
									<th width="12%">记录数</th>
									<th width="15%">数据</th>
									<th width="15%">索引</th>
									<th width="15%">碎片</th>
								</tr>
							</thead>
							<tbody>
							<!--{loop $sablog_table $sablog}-->
								<tr>
									<td>$sablog[Name]</td>
									<td>$sablog[Update_time]</td>
									<td>$sablog[Rows]</td>
									<td>$sablog[Data_length]</td>
									<td>$sablog[Index_length]</td>
									<td>$sablog[Data_free]</td>
								</tr>
							<!--{/loop}-->
								<tr>
									<td colspan="2"><strong>共计:{$sablog_table_num}个数据表</strong></td>
									<td><strong>$sablog_table_rows</strong></td>
									<td><strong>$sablog_data_size</strong></td>
									<td><strong>$sablog_index_size</strong></td>
									<td><strong>$sablog_free_size</strong></td>
								</tr>
							</tbody>
						</table>
						<!--{if $other_table_num && $other_table}-->
							<h3>其他数据表</h3>
							<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
								<thead>
									<tr>
										<th width="22%">名称</th>
										<th width="21%">最后更新时间</th>
										<th width="12%">记录数</th>
										<th width="15%">数据</th>
										<th width="15%">索引</th>
										<th width="15%">碎片</th>
									</tr>
								</thead>
								<tbody>
								<!--{loop $other_table $other}-->
									<tr>
										<td>$other[Name]</td>
										<td>$other[Update_time]</td>
										<td>$other[Rows]</td>
										<td>$other[Data_length]</td>
										<td>$other[Index_length]</td>
										<td>$other[Data_free]</td>
									</tr>
								<!--{/loop}-->
									<tr>
										<td colspan="2"><strong>共计:{$other_table_num}个数据表</strong></td>
										<td><strong>$other_table_rows</strong></td>
										<td><strong>$other_data_size</strong></td>
										<td><strong>$other_index_size</strong></td>
										<td><strong>$other_free_size</strong></td>
									</tr>
								</tbody>
							</table>
						<!--{/if}-->

					<!--{elseif $action == 'dotools'}-->
						<!--{loop $dodb $do}-->
							<div style="width:48%;margin-right:10px;float:left;">
								<fieldset>
									<legend>$do[name]表</legend>
									<ul class="info">
										<!--{loop $tabledb $table}-->
											<!--{if $table['do'] == $do['do']}-->
												<li>$table[table]: $table[result]</li>
											<!--{/if}-->
										<!--{/loop}-->
									</ul>
								</fieldset>
							</div>
						<!--{/loop}-->
						<div class="clear"></div>
					<!--{elseif $action == 'checkresume' && $dbimport}-->
						<p>$sqlfile</p>
						<p>恢复功能将覆盖原来的数据,您确认要导入备份数据?</p>
						<div class="button-submit">
							<input type="hidden" name="action" value="resume" />
							<input type="hidden" name="sqlfile" value="$sqlfile" />
							<input type="submit" value="确定" />
						</div>
					<!--{elseif $action == 'rssimport'}-->
						<fieldset>
							<legend>选择目标分类</legend>
							<select name="mid" id="mid">
								<option value="" selected>== 选择分类 ==</option>
								<!--{loop $catedb $cate}-->
								<option value="$cate[mid]" $ms[$cate[mid]]>$cate[name]</option>
								<!--{/loop}-->
							</select>
						</fieldset>
						<fieldset>
							<legend>选择文章作者</legend>
							<select name="uid" id="uid">
								<option value="" selected>== 选择作者 ==</option>
								<!--{loop $userdb $user}-->
								<option value="$user[userid]" $us[$user[userid]]>$user[username]</option>
								<!--{/loop}-->
							</select>
						</fieldset>
						<fieldset>
							<legend>选择XML文件</legend>
							<input class="formfield" type="file" name="xmlfile" /><br />
							<span class="describe">允许文件类型:xml</span>
						</fieldset>
						<div class="button-submit">
							<input type="hidden" name="action" value="importrss" />
							<input type="submit" value="确定" />
						</div>
					<!--{elseif $action == 'cache'}-->
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<thead>
								<tr>
									<th>缓存名称</th>
									<th>生成时间</th>
									<th>修改时间</th>
									<th>缓存大小</th>
								</tr>
							</thead>
							<tbody>
								<!--{loop $cachedb $cache}-->
								<tr>
									<td>$cache[desc]</td>
									<td>$cache[ctime]</td>
									<td>$cache[mtime]</td>
									<td>$cache[size]</td>
								</tr>
								<!--{/loop}-->
							</tbody>
						</table>

						<input type="hidden" name="action" value="updateall" />

						<div class="button-submit">
							<input type="button" onclick="javascript:submit('form1');" value="更新所有缓存" />
						</div>

					<!--{elseif $action == 'rebuild'}-->
							<fieldset>
								<legend>更新全站统计数据</legend>
								<input type="hidden" name="action" value="dostatsdata" />
								<input type="submit" value="确定" />
							</fieldset>
						</form>
						<form action="cp.php?job=tools" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<fieldset>
								<legend>更新所有分类和标签内的文章数</legend>
								<input type="hidden" name="action" value="dometadata" />
								<input type="submit" value="确定" />
							</fieldset>
						</form>
						<form action="cp.php?job=tools" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<fieldset>
								<legend>更新后台用户发表数量</legend>
								<input type="hidden" name="action" value="doadmindata" />
								<input type="submit" value="确定" />
							</fieldset>
						</form>
						<form action="cp.php?job=tools" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<fieldset>
								<legend>更新所有文章数据</legend>
								<input type="hidden" name="action" value="doarticledata" />
								循环更新数量: <input class="text-small" type="text" name="percount" value="200" size="5" /> <input type="submit" value="确定" /><br />
								更新所有文章中的评论数、引用数及附件信息. 建议经常定期执行, 配合附件管理中的附件修复操作, 可以提高数据准确性和程序的执行效率.
							</fieldset>
						</form>
						<form action="cp.php?job=tools" method="post">
							<input type="hidden" name="formhash" value="$formhash" />
							<fieldset>
								<legend>重建附件缩略图</legend>
								<input type="hidden" name="action" value="dothumbdata" />
								循环更新数量: <input class="text-small" type="text" name="percount" value="200" size="5" /> <input type="submit" value="确定" /><br />
								重新按照现在设定的缩略图尺寸重建所有附件图像的缩略图。通常用于你更改了缩略图尺寸并希望更新全部附件的情况下。这个操作会耗费一定服务器资源。
							</fieldset>
					<!--{elseif in_array($action, array('adminlog', 'loginlog', 'deladminlog', 'delloginlog', 'dberrorlog', 'deldberrorlog'))}-->

						<!--{if $action == 'adminlog'}-->

							<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
								<thead>
									<tr>
										<th>用户</th>
										<th>IP地址</th>
										<th>访问时间</th>
										<th>访问模块</th>
										<th>操作</th>
									</tr>
								</thead>
								<tbody>
							<!--{if $total}-->
								<!--{loop $logdb $log}-->
									<tr>
										<td>$log[2]</td>
										<td>$log[3]</td>
										<td>$log[1]</td>
										<td><a href="cp.php?job=$log[5]">$log[5]</a></td>
										<td>$log[4]</td>
									</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="5">没有操作记录</td>
								</tr>
							<!--{/if}-->
								</tbody>
							</table>

						<!--{elseif $action == 'loginlog'}-->

							<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
								<thead>
									<tr>
										<th>用户名</th>
										<th>登陆时间</th>
										<th>IP地址</th>
										<th>登陆结果</th>
									</tr>
								</thead>
								<tbody>
							<!--{if $total}-->
								<!--{loop $logdb $log}-->
									<tr>
										<td>$log[1]</td>
										<td>$log[2]</td>
										<td>$log[3]</td>
										<td>$log[4]</td>
									</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="5">没有登陆记录</td>
								</tr>
							<!--{/if}-->
								</tbody>
							</table>

						<!--{elseif $action == 'dberrorlog'}-->

							<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
								<thead>
									<tr>
										<th>信息</th>
										<th>错误描述 - SQL语句</th>
									</tr>
								</thead>
								<tbody>
							<!--{if $total}-->
								<!--{loop $logdb $log}-->
									<tr>
										<td>时间:$log[1]<br />IP:$log[2]<br />文件:$log[3]</td>
										<td>
											<div style="width:500px;height:auto;overflow:auto;font-weight:bold;color:#000;margin-bottom:5px;">$log[4]</div>
											<div style="border: 1px solid #ccc;width:500px;height:50px;overflow:auto;">$log[5]</div>
										</td>
									</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="5">没有出错记录</td>
								</tr>
							<!--{/if}-->
								</tbody>
							</table>
						<!--{/if}-->

						<input type="hidden" name="action" value="del{$action}" />

						<div class="pageinfo">
							<div class="multipage-submit">
								<input type="button" onclick="if (confirm('此操作会只保留最新的100条{$opname},而将其他更早的记录删除.确定吗?')) {javascript:submit('form1');}else{return;}" value="删除多余{$opname}" />
							</div>
							<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
							<div class="clear"></div>
						</div>

					<!--{/if}-->
					</form>
                </div>
                <!-- // #main -->
                <div class="clear"></div>
            </div>
            <!-- // #container -->