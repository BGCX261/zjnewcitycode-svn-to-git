<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				<ul class="submenu">
					<!--{if $action == 'list'}-->
						<li><a href="cp.php?job=article&amp;action=add"{if $action == 'add'} class="current"{/if}>添加文章</a></li>
						<li>|</li>
						<li><a href="cp.php?job=article"{if !$view && !$tag && !$mid && !$m && !$keywords} class="current"{/if}>全部<span class="count">($arttotal)</span></a></li>
						<li><a href="cp.php?job=article&amp;view=display"{if $view == 'display'} class="current"{/if}>显示的<span class="count">($displayarttotal)</span></a></li>
						<li><a href="cp.php?job=article&amp;view=hidden"{if $view == 'hidden'} class="current"{/if}>隐藏的<span class="count">($hiddenarttotal)</span></a></li>
						<li><a href="cp.php?job=article&amp;view=stick"{if $view == 'stick'} class="current"{/if}>置顶的<span class="count">($stickarttotal)</span></a></li>
					<!--{else}-->
						<li><a href="cp.php?job=article">文章管理</a></li>
					<!--{/if}-->
				</ul>

				<h2><a href="cp.php?job=article">文章管理</a>$navlink_L</h2>
				<div id="main">

			<!--{if $action == 'list'}-->

				<form method="post" action="cp.php?job=article&action=list">
				<input type="hidden" name="formhash" value="$formhash" />
					<table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:15px;">
					<tr>
						<td style="padding-right:8px;">
							<select name="mid">
								<option value="">显示全部分类文章</option>
								<!--{loop $catedb $cid $name}-->
								<option value="$cid"{if $cid == $mid} selected{/if}>$name</option>
								<!--{/loop}-->
							</select>
						</td>
						<td style="padding-right:8px;">
							<select name="m">
								<option value="">显示所有时期</option>
								<!--{loop $archivecache $key $data}-->
								<option value="$key"{if $key == $m} selected{/if}>$key</option>
								<!--{/loop}-->
							</select>
						</td>
						<td style="padding-right:8px;">
							<input title="请输入关键词搜索" type="text" name="keywords" size="20" maxlength="30" value="$keywords" />
						</td>
						<td>
							<input type="hidden" name="action" value="list" />
							<input type="hidden" name="do" value="search" />
							<input type="submit" class="buttons" value="过滤" />
						</td>
					</tr>
					</table>
				</form>

				<form method="post" action="cp.php?job=article" name="form1" id="form1">
					<input type="hidden" name="formhash" value="$formhash" />
					<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<thead>
							<tr>
								<th width="4%">&nbsp;</th>
								<th>状态</th>
								<th>标题</th>
								<th>分类</th>
								<th>作者</th>
								<th>评论</th>
								<th>时间</th>
							</tr>
						</thead>
						<tbody>
						<!--{if $total}-->
							<!--{loop $articledb $article}-->
							<tr>
								<td class="check-column"><!--{if $sax_group == 1 || $article[uid] == $sax_uid}--><input type="checkbox" name="selectall[]" value="$article[articleid]" /><!--{else}-->&nbsp;<!--{/if}--></td>
								<td><a href="cp.php?job=article&action=visible&articleid=$article[articleid]"><!--{if $article['visible']}--><span class="yes">显示</span><!--{else}--><span class="no">隐藏</span><!--{/if}--></a></td>
								<td>										
									<!--{if $article['stick']}-->
										<strong>置顶</strong>
									<!--{/if}-->
									<a href="cp.php?job=article&action=mod&articleid=$article[articleid]">$article[title]</a>
									<!--{if $article['attachments']}-->
										<img src="./templates/admin/images/attachicon.gif" border="0" alt="该文章有附件" class="attach" width="16" height="16" />
									<!--{/if}-->
									<!--{if $article['readpassword']}-->
										<img src="./templates/admin/images/lockicon.gif" border="0" alt="该文章有密码" class="attach" width="16" height="16" />
									<!--{/if}-->
								</td>
								<td>
									<!--{if $metadb[$article['articleid']]}-->
										<!--{eval $comma = ''}-->
										<!--{loop $metadb[$article['articleid']] $meta}-->								
											$comma <a href="cp.php?job=article&action=list&mid=$meta[mid]">$meta[name]</a>
											<!--{eval $comma = ', '}-->
										<!--{/loop}-->
									<!--{/if}-->
								</td>
								<td><a href="cp.php?job=article&action=list&uid=$article[uid]">$article[username]</a></td>
								<td><span class="rownum"><!--{if $article['comments']}--><a href="cp.php?job=comment&action=list&articleid=$article[articleid]">{$article[comments]}</a><!--{else}-->0<!--{/if}--></span></td>
								<td>$article[dateline]</td>
							</tr>
							<!--{/loop}-->
						<!--{else}-->
							<tr>
								<td colspan="7">没有找到任何文章</td>
							</tr>
						<!--{/if}-->
						</tbody>
					</table>

					<input type="hidden" id="doit" name="doit" value=""/>
					<input type="hidden" name="action" value="domore" />
					<input type="hidden" name="mid" value="$mid" />
					<input type="hidden" name="uid" value="$uid" />
					<input type="hidden" name="m" value="$m" />
					<input type="hidden" name="keywords" value="$keywords" />
					<input type="hidden" name="articleid" value="$articleid" />

					<div class="pageinfo">
						<div class="multipage-submit">
							<input type="button" onclick="javascript:setdoit('stick');" value="置顶" />
							<input type="button" onclick="javascript:setdoit('unstick');" value="取消置顶" />
							<input type="button" onclick="javascript:setdoit('hidden');" value="隐藏" />
							<input type="button" onclick="javascript:setdoit('display');" value="可见" />
							<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:setdoit('delete');}else{return;}" value="删除" />
							<a class="buttons chkall" href="###">全选</a>,
							<a class="buttons dechkall" href="###">取消</a>
						</div>
						<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
						<div class="clear"></div>
					</div>

				</form>

			<!--{elseif in_array($action, array('add', 'mod'))}-->

				<script type="text/javascript" src="$options[url]include/editor/kindeditor.js"></script>
				<script type="text/javascript" src="$options[url]include/jscript/editor.js"></script>
				<script type="text/javascript" src="$options[url]include/jscript/ajax.js"></script>

				<!--load swfupload-->
				<link href="$options[url]include/swfupload/style.css" rel="stylesheet" type="text/css" />
				<script type="text/javascript" src="$options[url]include/swfupload/swfupload.js"></script>
				<script type="text/javascript" src="$options[url]include/swfupload/handlers.js"></script>
				<script type="text/javascript" src="$options[url]include/swfupload/common.js"></script>
				<script type="text/javascript">
				var uploadobject;
				var max_upload_size = '$max_upload_size';
				var sax_cookie_auth = '{$_COOKIE[sax_auth]}';
				$(document).ready(function(){
					uploadobject = new uploadPlugin("uploadobject");
					uploadobject.Init();
				});
				</script>

				<script type="text/javascript">
				function checkform() {
					if ($('#title').val() == "") {
						sa_alert("请输入标题.");
						return false;
					}
					var mids = 0;
					$("input[name='mids[]']").each(function(i){
						if (this.checked)	{
							mids = 1;
						}
					});
					if (!mids)	{
						sa_alert("请选择分类.");
						return false;
					}
					if (oEditor.isEmpty()) {
						sa_alert("请写内容.");
						return false;
					}
					$('#submit').disabled = true;
					return true;
				}
				//插入上传附件
				function addattach(attachid){
					addhtml('[attach=' + attachid + ']');
				}
				var tagnum = 0;
				function addTag(tagName) {
					if (tagnum < 10) {
						var getTagObj=document.getElementById("keywords");
						var tags;
						if (getTagObj.value.length>0) {
							tags=getTagObj.value.split(",");
							if (tags.length>=5) {
								sa_alert('不能超过10个关键词');
								return;
							}
							for (i=0;i<tags.length;i++){
								if (tags[i].toLowerCase()==tagName.toLowerCase()) {
									return;
								}
							}
							getTagObj.value+=","+tagName;
						} else {
							getTagObj.value+=tagName;
						}
						tagnum++;
					} else {
						sa_alert('不能超过10个关键词');
						return;
					}
				}
				var allowpostattach = parseInt('1');
				var inarticle = parseInt('1');
				</script>
				<form action="cp.php?job=article" enctype="multipart/form-data" method="post" name="form1" onsubmit="return checkform();">
				<input type="hidden" name="formhash" value="$formhash" />
				<div style="float:left;width:615px;">
					<fieldset>
						<legend>文章名称</legend>
						<input type="text" class="text-long" name="title" id="title" style="width:98%" value="$article[title]" />
					</fieldset>
					<fieldset>
						<legend>文章内容</legend>
						<script type="text/javascript">
							var editor;
							KindEditor.ready(function(K) {
								oEditor = K.create('textarea[name="content"]');
								oEditor2 = K.create('textarea[name="description"]');
							});
						</script>
						<textarea cols="80" id="content" name="content" rows="10" style="width:100%;height:350px;visibility:hidden;">$article[content]</textarea>
						<!--{if $action == 'add'}-->
							<div>
								<span id="timemsg">禁止自动保存</span>
								<a href="javascript:savedraft('$formhash');">立即保存</a> - 
								<a href="javascript:switchtodraft('$formhash');">恢复内容</a> - 
								<a href="javascript:cleardraft();">清空内容</a>
								<span style="float:right;" id="timemsg2"></span>
								<script type="text/javascript" src="$options[url]include/jscript/autosave.js"></script>
							</div>
						<!--{/if}-->
					</fieldset>
					<fieldset>
						<legend>文章描述</legend>
						<textarea cols="80" id="description" name="description" rows="8" style="width:100%;height:200px;">$article[description]</textarea>
					</fieldset>
					<fieldset>
						<legend>Trackback</legend>
						<textarea class="textarea" id="pingurl" name="pingurl" rows="2" style="width:98%;">$article[pingurl]</textarea>
						<br />每个 URL 用空格分开
						<!--{if $action == 'mod'}-->
							<span class="element-desc">
								<input id="pingagain" name="pingagain" type="checkbox" value="1" /> 再次发送?
							</span>
						<!--{/if}-->
					</fieldset>
					<script type="text/javascript">
					var attachurl = '{$options[url]}{$options[attachments_dir]}';
					function switchUploader(s) {
						if ( s ) {
							$('#swfuploadform').show();
							$('#htmluploadform').hide();
						} else {
							$('#swfuploadform').hide();
							$('#htmluploadform').show();
						}
					}
					</script>

					<!--{if $attachdb}-->
						<fieldset>
							<legend>已上传附件</legend>
							<div class="popupmenu_popup" id="imgpreview_menu" style="position:absolute;width:500px;display: none;"></div>
							<table cellpadding="0" cellspacing="0" border="0" width="100%" class="list_td">
							<!--{loop $attachdb $attach}-->
								<tr>
									<td width="15%"><input type="checkbox" name="delattach[]" value="$attach[attachmentid]" /> 删除</td>
									<td>
										<strong>
										<!--{if $attach['isimage']}-->
											<span onmouseover="showpreview(this, 'imgpreview_$attach[attachmentid]')">
												<a href="###" title="插入文章" onclick="addattach('$attach[attachmentid]')">$attach[filename]</a>
											</span>
											<div id="imgpreview_$attach[attachmentid]" style="display:none"><img id="preview_$attach[attachmentid]" width="$attach[thumb_width]" height="$attach[thumb_height]" src="$attach[thumb_filepath]" /></div>
										<!--{else}-->
											<a href="###" onclick="addattach('$attach[attachmentid]')">$attach[filename]</a>
										<!--{/if}-->
										</strong>
									</td>
									<td width="20%">$attach[filesize]</td>
									<td width="25%">$attach[dateline]</td>
								</tr>
							<!--{/loop}-->
							</table>
						</fieldset>
					<!--{/if}-->
					<fieldset>
						<legend>附件</legend>
						<div id="swfuploadform">
							<!--操作按钮-->
							<div class="mainbox_top">
								<ul>
									<li id="add" style="background-position:bottom;"><span id="selectfile"></span></li>
									<li id="upload" style="display:none;"></li>
									<li id="delete" style="display:none;"></li>
								</ul>
							</div>
							<!--上传列表-->
							<div class="mainbox_left" style="display:none;">
								<ul id="title">
									<li class="no">序号</li>
									<li class="status">状态</li>
									<li class="name">名称</li>
									<li class="size">大小</li>
									<li class="progress">进度</li>
									<li class="exec">操作</li>
								</ul>
							</div>
							<!--提醒-->
							<div class="clear"></div>
							<div id="taginfo"></div>
							<!--暂时取消传统表单上传，不然不好同步附件。-->
							<span style="display:none;">您正在使用高级多文件上传工具。不能正确上传？请尝试使用<a href="javascript:;" onclick="switchUploader(0)">标准的浏览器上传工具</a>。</span>
						</div>

						<div id="htmluploadform" style="display:none;">
							
							<div style="display:block;width:100%;clear:both;">

								<span class="sim_upfile">
									<span id="attachbtnhidden"><span><input class="hifile" type="file" name="attach[]" multiple /></span></span>
									<span id="attachbtn"></span>
									<span class="btn"></span>
								</span>

							</div>

							<div id="uploadlist" class="upfilelist">
								<table cellpadding="0" cellspacing="0" border="0" width="100%" class="list_td" style="border:none;">
									<tbody id="attachbodyhidden" style="display:none"><tr>
										<td class="attachctrl"><span id="localno[]"></span><span id="cpadd[]"></span></td>
										<td class="attachname"><span id="localfile[]"></span><input type="hidden" name="localid[]" /></td>
										<td class="attachdel"><span id="cpdel[]"></span></td>
									</tr></tbody>
								</table>
								<table cellpadding="0" cellspacing="0" border="0" width="100%" class="list_td" style="border:none;">
									<tbody id="attachbody"></tbody>
								</table>
							</div>

							<div id="img_hidden" alt="1" style="position:absolute;top:-100000px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='image');width:400px;height:300px"></div>
							
							<span>您正在使用浏览器内置的标准上传工具。Sablog-X 提供了全新的上传工具。<a href="javascript:;" onclick="switchUploader(1)">改用新的上传工具</a>。</span>
						</div>
						<span class="max-upload-size">上传文件大小限制：{$max_upload_size_unit}。</span>
					</fieldset>
				</div>
				<div style="float:left;width:240px;margin-left:20px;">
					<fieldset>
						<legend>文章分类</legend>
						<ul>
						<!--{loop $catedb $mid $name}-->
							<li><label><input type="checkbox" name="mids[]" value="$mid"{if $mid == $metadb[$article['articleid']]['category'][$mid]['mid']} checked{/if} /> $name</label></li>
						<!--{/loop}-->
						</ul>
					</fieldset>
					<fieldset>
						<legend>标签(关键词)</legend>
						<input type="text" class="text-medium" name="keywords" id="keywords" style="width:180px" value="$article[keywords]" />
						<img src="$options[url]templates/admin/images/insert.gif" alt="插入已经使用的Tag" onclick="getalltag();" style="cursor:pointer" /><br />
						<span>用“,”分隔多个关键词<br />最多允许添加10个关键词<br />每个关键词不能超过30个字符.</span>
					</fieldset>
					<fieldset>
						<legend>自定义URL</legend>
						<input type="text" name="alias" id="alias" class="text-medium"  value="$article[alias]" /><br />
						<span>只能使用半角字母、数字、下划线和减号.</span>
					</fieldset>
					<fieldset>
						<legend>高级选项</legend>
						<ul>
							<li><label for="closecomment"><input id="closecomment" name="closecomment" type="checkbox" value="1" $closecomment_check /> 禁止访客发表评论</label></li>
							<li><label for="closetrackback"><input id="closetrackback" name="closetrackback" type="checkbox" value="1" $closetrackback_check /> 禁止引用本文</label></li>
							<li><label for="visible"><input id="visible" name="visible" type="checkbox" value="1" $visible_check /> 显示本文</label></li>
							<li><label for="stick"><input id="stick" name="stick" type="checkbox" value="1" $stick_check /> 置顶本文</label></li>
						</ul>
					</fieldset>
					<fieldset>
						<legend>阅读密码</legend>
						<input type="text" name="readpassword" id="readpassword" class="text-medium" value="$article[readpassword]" /><br />
						<span>20个字符以内.</span>
					</fieldset>
					<fieldset>
						<legend>自定义发布时间</legend>
						<div style="margin:3px auto;">
							<input class="text-small" name="newyear" type="text" value="$newyear" maxlength="4" style="width:40px" />
							年
							<input class="text-small" name="newmonth" type="text" value="$newmonth" maxlength="2" style="width:20px" />
							月
							<input class="text-small" name="newday" type="text" value="$newday" maxlength="2" style="width:20px" />
							日
						</div>
						<div style="margin-top:5px;">
							<input class="text-small" name="newhour" type="text" value="$newhour" maxlength="2" style="width:20px" />
							时
							<input class="text-small" name="newmin" type="text" value="$newmin" maxlength="2" style="width:20px" />
							分
							<input id="edittime" name="edittime" type="checkbox" value="1" /> 有效?
						<div>
					</fieldset>
				</div>
				<div class="clear"></div>

				<div class="button-submit">
					<input type="hidden" name="oldtags" value="$article[keywords]" />
					<input type="hidden" name="action" value="$act" />
					<input type="hidden" name="articleid" value="$articleid" />
					<input type="submit" value="确定" />
				</div>
				<div class="clear"></div>
				</form>

				<script type="text/javascript" src="$options[url]include/jscript/attachment.js"></script>

			<!--{/if}-->

				</div>
				<!-- // #main -->
            </div>
            <!-- // #container -->