<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container-all">
				<ul class="submenu">
					<!--{if $action == 'list'}-->
						<li><a href="cp.php?job=user&amp;action=add"{if $action == 'add'} class="current"{/if}>添加用户</a></li>
						<li>|</li>
						<li><a href="cp.php?job=user"{if !$groupid && !$srhname} class="current"{/if}>全部<span class="count">($usertotal)</span></a></li>
						<li><a href="cp.php?job=user&amp;groupid=1"{if $groupid == 1} class="current"{/if}>管理者<span class="count">($admintotal)</span></a></li>
						<li><a href="cp.php?job=user&amp;groupid=2"{if $groupid == 2} class="current"{/if}>撰写者<span class="count">($editortotal)</span></a></li>
						<li><a href="cp.php?job=user&amp;groupid=3"{if $groupid == 3} class="current"{/if}>普通用户<span class="count">($publictotal)</span></a></li>
					<!--{else}-->
						<li><a href="cp.php?job=user">用户管理</a></li>
					<!--{/if}-->
				</ul>
                
                <!-- h2 stays for breadcrumbs -->
                <h2><a href="cp.php?job=user">用户管理</a>$navlink_L</h2>
                <div id="main">

				<!--{if $action == 'list'}-->
					<form method="post" action="cp.php?job=user&action=list">
					<input type="hidden" name="formhash" value="$formhash" />
						<table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:15px;">
						<tr>
							<td style="padding-right:8px;">
								<input title="请输入关键词搜索" type="text" name="srhname" size="20" maxlength="30" value="$srhname" />
							</td>
							<td>
								<input type="hidden" name="action" value="list" />
								<input type="submit" class="buttons" value="搜索" />
							</td>
						</tr>
						</table>
					</form>
				<!--{/if}-->

				<form action="cp.php?job=user" method="post" name="form1" id="form1">
				<input type="hidden" name="formhash" value="$formhash" />
				<!--{if $action == 'list'}-->

					<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<thead>
							<tr>
								<th width="4%">&nbsp;</th>
								<th>用户名</th>
								<th>用户组</th>
								<th>电子邮件</th>
								<th>主页</th>
								<th>注册时间</th>
								<th>最近访问时间</th>
							</tr>
						</thead>
						<tbody>
						<!--{if $userdb}-->
							<!--{loop $userdb $user}-->
								<tr>
									<td class="check-column"><input type="checkbox" name="selectall[]" value="$user[userid]" $user[disabled] /></td>
									<td><a href="cp.php?job=user&action=mod&userid=$user[userid]">$user[username]</a></td>
									<td>$user[group]</td>
									<td>				
										<!--{if $user['email']}-->
											<a href="mailto:{$user[email]}" target="_blank">$user[email]</a>
										<!--{else}-->
											&nbsp;
										<!--{/if}-->
									</td>
									<td>
										<!--{if $user['url']}-->
											<a href="$user[url]" target="_blank"><img src="./templates/admin/images/homelink.gif" border="0" alt="访问该用户的网站" /></a>
										<!--{else}-->
											&nbsp;
										<!--{/if}-->
									</td>
									<td>$user[regdateline]</td>
									<td>$user[lastvisit]</td>
								</tr>
							<!--{/loop}-->
						<!--{else}-->
							<tr><td colspan="7">没有找到用户</td></tr>
						<!--{/if}-->
						</tbody>
					</table>

					<input type="hidden" name="action" value="delusers" />

					<div class="pageinfo">
						<div class="multipage-submit">
							<input type="button" onclick="if (confirm('确定要删除所选项目么?')) {javascript:doaction('delusers');}else{return;}" value="删除" />
							<a class="buttons chkall" href="###">全选</a>,
							<a class="buttons dechkall" href="###">取消</a>
						</div>
						<div class="multipage">记录:$total <!--{if $multipage}-->, $multipage<!--{/if}--></div>
						<div class="clear"></div>
					</div>

				<!--{elseif in_array($action, array('add', 'mod', 'profile'))}-->

					<!--{if in_array($action, array('add', 'mod'))}-->
						<div style="width:430px;float:left;">
							<fieldset>
								<legend>登陆名</legend>
								<input class="text-medium" type="text" name="username" id="username" size="35" maxlength="50" value="$username" />
							</fieldset>
						</div>
						<div style="width:430px;float:right;">
							<fieldset>
								<legend>用户组</legend>
								<select name="groupid" id="groupid">
									<option value="1" $groupselect[1]>管理员</option>
									<option value="2" $groupselect[2]>撰写组</option>
									<option value="3" $groupselect[3]>注册组</option>
								</select>
							</fieldset>
						</div>
						<div class="clear"></div>
					<!--{/if}-->
					<!--{if $action == 'profile'}-->
						<fieldset>
							<legend>旧密码</legend>
							<input class="text-medium" type="password" name="old_password" id="old_password" size="35" maxlength="50" value="" /><br />
							<span>修改密码时需要输入旧密码</span>
						</fieldset>
					<!--{/if}-->
					<div style="width:430px;float:left;">
						<fieldset>
							<legend>新密码</legend>
							<input class="text-medium" type="password" name="newpassword" id="newpassword" size="35" maxlength="50" value="" /><br />
							<span>不改请留空, 密码不能少于8个字符.</span>
						</fieldset>
					</div>
					<div style="width:430px;float:right;">
						<fieldset>
							<legend>确认新密码</legend>
							<input class="text-medium" type="password" name="comfirpassword" id="comfirpassword" size="35" maxlength="50" value="" /><br />
							<span>请再输入一次密码.</span>
						</fieldset>
					</div>
					<div class="clear"></div>
					<div style="width:430px;float:left;">
						<fieldset>
							<legend>E-mail</legend>
							<input class="text-long" type="text" name="email" id="email" size="35" maxlength="50" value="$email" />
						</fieldset>
					</div>
					<div style="width:430px;float:right;">					
						<fieldset>
							<legend>主页</legend>
							<input class="text-long" type="text" name="url" id="url" size="35" maxlength="50" value="$url" />
						</fieldset>
					</div>
					<div class="clear"></div>
					<p class="button-submit">
						<input type="hidden" name="action" value="$do" />
						<input type="hidden" name="userid" value="$info[userid]" />
						<input type="submit" value="确定" />
					</p>
					<!--{if in_array($action, array('mod', 'profile'))}-->
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<tr>
								<td width="100">注册时间</td>
								<td>$info[regdateline]</td>
							</tr>
							<tr>
								<td>注册IP</td>
								<td>$info[regip]</td>
							</tr>
							<tr>
								<td>最后评论时间</td>
								<td>$info[lastpost]</td>
							</tr>
							<tr>
								<td>最后访问IP</td>
								<td>$info[lastip]</td>
							</tr>
							<tr>
								<td>最后访问时间</td>
								<td>$info[lastvisit]</td>
							</tr>
							<tr>
								<td>最后活动时间</td>
								<td>$info[lastactivity]</td>
							</tr>
						</table>
					<!--{/if}-->
				<!--{/if}-->
				</form>
				</div>
				<!-- // #main -->
            </div>
            <!-- // #container -->