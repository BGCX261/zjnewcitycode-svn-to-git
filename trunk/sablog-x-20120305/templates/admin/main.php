<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container">
        		<div id="sidebar">
                	<ul class="sideNav">
						<li><a href="cp.php?job=article&amp;action=add">添加文章</a></li>
						<li><a href="cp.php?job=template">模板管理</a></li>
                    </ul>
                    <!-- // .sideNav -->
                </div>    
                <!-- // #sidebar -->
                
                <!-- h2 stays for breadcrumbs -->
                <h2 class="maintitle">控制面板</h2>
                <div id="main-700">

					<fieldset id="news_box" style="display:none;">
						<legend id="news_title">读取中...</legend>
						<div id="news_content" class="alertmsg">读取中...</div>
					</fieldset>
					
					<div style="width:340px;float:left;">
						<h3>数据统计</h3>
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<tr>
								<td width="80">隐藏文章:</td>
								<td><a href="cp.php?job=article&amp;action=list&amp;view=hidden">{$hiddenarttotal} 篇</a></td>
							</tr>
							<tr>
								<td>隐藏评论:</td>
								<td><a href="cp.php?job=comment&amp;action=list&amp;view=hidden">{$hiddencomtotal} 条</a></td>
							</tr>
							<tr>
								<td>标签个数:</td>
								<td><a href="cp.php?job=category&amp;action=taglist">{$tagtotal} 个</a></td>
							</tr>
							<tr>
								<td>友情链接:</td>
								<td><a href="cp.php?job=link">{$linktotal} 个</a></td>
							</tr>
						</table>
					</div>
					<div style="width:340px;float:right;">
						<h3>附件统计</h3>
						<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
							<tr>
								<td width="80">附件数量:</td>
								<td>$stats[count] 个</td>
							</tr>
							<tr>
								<td>记录大小:</td>
								<td>$stats[sum]</td>
							</tr>
							<tr>
								<td>实际大小:</td>
								<td>$realattachsize</td>
							</tr>
							<tr>
								<td>子目录数:</td>
								<td>$dircount 个</td>
							</tr>
						</table>
					</div>
					<div class="clear"></div>

					<h3>运行状态</h3>
					<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<tr>
							<td width="80">当前时间:</td>
							<td>$server[datetime]</td>
						</tr>
						<tr>
							<td>系统环境:</td>
							<td>$server[software]</td>
						</tr>
						<tr>
							<td>文件上传:</td>
							<td>$fileupload</td>
						</tr>
						<tr>
							<td>全局变量:</td>
							<td>$globals</td>
						</tr>
						<tr>
							<td>安全模式:</td>
							<td>$safemode</td>
						</tr>
						<tr>
							<td>图形处理:</td>
							<td>$gd_version</td>
						</tr>
						<tr>
							<td>数据存储:</td>
							<td>MySQL $mysql_version</td>
						</tr>
						<tr>
							<td>已经运行:</td>
							<td>$mysql_runtime</td>
						</tr>
						<!--{if $server['memory_info']}-->
						<tr>
							<td>内存占用:</td>
							<td>$server[memory_info]</td>
						</tr>
						<!--{/if}-->
					</table>
					<h3>程序信息</h3>
					<table align="center" border="0" cellspacing="0" cellpadding="0" class="list_td">
						<tr>
							<td width="80">当前版本:</td>
							<td>$SABLOG_VERSION Build $SABLOG_RELEASE</td>
						</tr>
						<tr>
							<td>最新版本:</td>
							<td><span id="newest_version">读取中...</span></td>
						</tr>
						<tr>
							<td>程序开发:</td>
							<td><a href="mailto:4ngel08@gmail.com" target="_blank" title="QQ:291427">angel</a></td>
						</tr>
						<tr>
							<td>官方主页:</td>
							<td><a href="http://www.sablog.net" target="_blank">http://www.sablog.net</a></td>
						</tr>
					</table>
                </div>
                <!-- // #main -->                
                <div class="clear"></div>
            </div>
            <!-- // #container -->

			<script type="text/javascript">
			var oHead = document.getElementsByTagName('head').item(0);
			var oScript = document.createElement("script");
			oScript.type = 'text/javascript';
			oScript.src = 'http://www.sablog.net/update.php?version=$now_version&release=$now_release&hostname=$now_hostname&url=$now_url';
			oHead.appendChild(oScript);
			</script>