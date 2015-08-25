<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container">
        		<div id="sidebar">
                	<ul class="sideNav">
					<!--{loop $adminitem['template']['submenu'] $item}-->
						<li><a href="cp.php?job=template&amp;action=$item[action]"{if $item[action] == $action} class="active"{/if}>$item[name]</a></li>
					<!--{/loop}-->
                    </ul>
                    <!-- // .sideNav -->
                </div>    
                <!-- // #sidebar -->

                <h2 class="maintitle"><a href="cp.php?job=template">模板管理</a>$navlink_L</h2>
                
                <div id="main-700">

				<!--{if $action == 'template'}-->
					<div>
						<h3>当前模板</h3>
						<!--{if $current_template_info}-->
						<div class="template_now">
							<div class="pic"><img alt="$current_template_info[name]" src="$current_template_info[screenshot]" border="0" /></div>
							<div class="info">
								<ul>
									<li><strong>$current_template_info[name]</strong></li>
									<li>制作者:$current_template_info[author]</li>
									<li>适用版本:$current_template_info[version]</li>
									<li>$current_template_info[description]</li>
									<li><a href="cp.php?job=template&action=buildtemplate&path=$options[templatename]">更新缓存</a></li>
									<!--{if $tpledit}-->
										<li><a href="cp.php?job=template&action=filelist&path=$options[templatename]">编辑模板</a></li>
									<!--{/if}-->
								</ul>
							</div>
						</div>
						<!--{else}-->
							<p>没有当前主题的相关资料</p>
						<!--{/if}-->
						<div class="clear"></div>
					</div>

					<div>
						<h3>可用模板</h3>
					<!--{if $available_template_db}-->
							<!--{loop $available_template_db $template}-->
								<div class="template_list">
									<div>
										<a href="cp.php?job=template&action=settemplate&name=$template[dirurl]" class="screenshot"><img src="$template[screenshot]" border="0" alt="设置$template[name]主题为当前主题" align="left" height="180" /></a>
									</div>
									<div>
										<ul>
											<li><strong><a title="设置$template[name]主题为当前主题" href="cp.php?job=template&action=settemplate&name=$template[dirurl]">$template[name]</a></strong></li>
											<li><a href="cp.php?job=template&action=filelist&path=$template[dirurl]">编辑此模板</a></li>
											<li>适用版本: $template[version]</li>
										</ul>
									</div>
								</div>
							<!--{/loop}-->
					<!--{else}-->
						<p>没有可用模板</p>
					<!--{/if}-->
					</div>
					<div class="clear"></div>
				<!--{elseif $action == 'add' || $action == 'mod'}-->
					<form action="cp.php?job=template" method="post" name="form1" id="form1">
						<input type="hidden" name="formhash" value="$formhash" />
						<fieldset>
							<legend>变量名</legend>
							<input class="text-long" type="text" name="new_title" id="new_title" size="35" maxlength="50" value="$new_title" />
						</fieldset>
						<fieldset>
							<legend>变量内容</legend>
							<textarea id="varid_$stylevar[stylevarid]" class="textarea" name="new_value" style="width:400px;height:120px;">$new_value</textarea>
							<strong><a href="###" onClick="resizeup('varid_$stylevar[stylevarid]');">[+]</a> <a href="###" onClick="resizedown('varid_$stylevar[stylevarid]');">[-]</a></strong>
						</fieldset>
						<fieldset>
							<legend>描述</legend>
							<input class="text-long" type="text" name="new_description" id="new_description" size="35" maxlength="50" value="$new_description" />
						</fieldset>
						<p class="button-submit">
							<input type="hidden" name="action" value="{$action}stylevar" />
							<input type="hidden" name="stylevarid" value="$stylevar[stylevarid]" />
							<input type="submit" value="确定" />
						</p>
					</form>
				<!--{elseif $action == 'stylevar'}-->
					<form action="cp.php?job=template" method="post" name="form1" id="form1">
						<input type="hidden" name="formhash" value="$formhash" />

						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<thead>
								<tr>
									<th width="4%">&nbsp;</th>
									<th width="8%">状态</th>
									<th width="40%">变量名</th>
									<th>变量描述</th>
								</tr>
							</thead>
							<tbody>
							<!--{if $total}-->
								<!--{loop $stylevardb $stylevar}-->
									<tr>
										<td class="check-column"><input type="checkbox" name="selectall[]" value="$stylevar[stylevarid]" /></td>
										<td><!--{if $stylevar['visible']}--><span class="yes">启用</span><!--{else}--><span class="no">禁用</span><!--{/if}--></td>
										<td><strong><!--{eval echo '$stylevar['.{$stylevar[title]}.']';}--></strong>
											<div class="row-actions">
												<a href="cp.php?job=template&action=visible&stylevarid=$stylevar[stylevarid]"><!--{if $stylevar['visible']}-->禁用<!--{else}-->启用<!--{/if}--></a> | <a href="cp.php?job=template&action=mod&amp;stylevarid=$stylevar[stylevarid]">编辑</a> | 
												<a href="cp.php?job=template&action=delstylevar&amp;stylevarid=$stylevar[stylevarid]" onclick="return confirm('你确定要删除此变量吗?');">删除</a>
											</div>
										</td>
										<td>$stylevar[description]</td>
									</tr>
								<!--{/loop}-->
							<!--{else}-->
								<tr>
									<td colspan="4">没有找到任何模板变量</td>
								</tr>
							<!--{/if}-->
							</tbody>
						</table>

						<input type="hidden" name="action" value="domorestylevar" />
						<div class="pageinfo">
							<div class="multipage-submit">
								<input type="button" onclick="if (confirm('确定要删除所选并且更新不删除的项目么?')) {javascript:submit('form1');}else{return;}" value="删除" />
							</div>
							<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
							<div class="clear"></div>
						</div>
					</form>
				<!--{elseif $action == 'filelist' && $tpledit}-->
					<div>
						<div style="width:100px;float:left;">
							<fieldset>
								<legend>文件列表</legend>
								<ul>
								<!--{loop $filedb $fileinfo}-->
									<li class="arrow"><a href="cp.php?job=template&action=filelist&path=$path&file=$fileinfo[filename]&ext=$fileinfo[extension]">$fileinfo[filedesc]</a></li>
								<!--{/loop}-->
								</ul>
							</fieldset>
						</div>
						<div style="width:590px;float:right;">
							<form action="cp.php?job=template" method="post" name="form1" id="form1">
								<input type="hidden" name="formhash" value="$formhash" />
								<fieldset>
									<legend>$desc[$file]</legend>
									<!--{if !$writeable}-->
										<p align="center"><strong><span class="no">当前模板文件不可写入, 请设置为 0777 权限后再编辑此文件.</span></strong></p>
									<!--{/if}-->
									<textarea id="filecontent" name="content" style="width:99%;height:400px;font:12px 'Courier New';">$contents</textarea>
								</fieldset>
								<div class="button-submit">
									<input type="hidden" name="action" value="savefile">
									<input type="hidden" name="file" value="$file">
									<input type="hidden" name="ext" value="$ext">
									<input type="hidden" name="path" value="$path">
									<input type="submit" value="确定" />
									<input type="reset" value="重置" />
								</div>
							</form>
						</div>
						<div class="clear"></div>
					</div>
				<!--{/if}-->
                </div>
                <!-- // #main -->
                <div class="clear"></div>
            </div>
            <!-- // #container -->