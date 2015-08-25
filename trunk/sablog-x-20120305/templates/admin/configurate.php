<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div id="container">
				<div id="sidebar">
					<ul class="sideNav">
					<!--{loop $adminitem['configurate']['submenu'] $item}-->
						<li><a href="cp.php?job=configurate&amp;action=$item[action]"{if $item[action] == $action} class="active"{/if}>$item[name]</a></li>
					<!--{/loop}-->
					</ul>
					<!-- // .sideNav -->
				</div>		
				<!-- // #sidebar -->
								
				<!-- h2 stays for breadcrumbs -->
				<h2 class="maintitle"><a href="cp.php?job=flash">系统设置</a>$navlink_L</h2>
				
				<div id="main-700">
					<form action="cp.php?job=configurate" method="post">
					<input type="hidden" name="formhash" value="$formhash" />
					
				<!--{if $action == '' || $action == 'all' || $action == 'basic'}-->
					<fieldset>
						<legend>基本设置</legend>
						<table class="config">
							<tr>
								<td width="160">博客名称</td>
								<td><input class="text-long" type="text" name="setting[name]" size="70" value="$settings[name]" /></td>
							</tr>
							<tr>
								<td>博客描述</td>
								<td><input class="text-long" type="text" name="setting[description]" size="70" value="$settings[description]" /></td>
							</tr>
							<tr>
								<td>博客地址</td>
								<td><input class="text-long" type="text" name="setting[url]" size="70" value="$settings[url]" />
									<br />
									<span>如果不填写此项将自动探测地址,假如您使用了多镜像服务器,请留空此项,否则镜像将不起作用.也可以填写 http://{{host}}/blog 来表示自动探测地址.</span></td>
							</tr>
							<tr>
								<td>网站备案号</td>
								<td><input class="text-medium" type="text" name="setting[icp]" size="70" value="$settings[icp]" /></td>
							</tr>
							<tr>
								<td>页面Gzip压缩</td>
								<td><input type="radio" name="setting[gzipcompress]" value="1" $gzipcompress_Y />
									是
									<input type="radio" name="setting[gzipcompress]" value="0" $gzipcompress_N />
									否<br />
									<span>将页面内容以 gzip 压缩后传输,可以加快传输速度,需 PHP 4.0.4 以上且支持 Zlib 模块才能使用</span> </td>
							</tr>
							<tr>
								<td>是否显示提示信息</td>
								<td><input type="radio" name="setting[showmsg]" value="1" $showmsg_Y />
									是
									<input type="radio" name="setting[showmsg]" value="0" $showmsg_N />
									否<br />
									<span>某些成功的操作不显示提示信息,直接跳转到下一个页面,可以节省用户等待跳转的时间.此开关影响发表评论,搜索引擎,跳转最新评论,跳转上一篇或下一篇文章.</span> </td>
							</tr>
							<tr>
								<td>关闭博客</td>
								<td><input type="radio" name="setting[close]" value="1" $close_Y />
									是
									<input type="radio" name="setting[close]" value="0" $close_N />
									否 </td>
							</tr>
							<tr>
								<td>关闭的原因</td>
								<td><textarea id="close_note" class="textarea" name="setting[close_note]" style="width:400px;height:80px;">$settings[close_note]</textarea>
									<strong><a href="###" onclick="resizeup('close_note');">[+]</a> <a href="###" onclick="resizedown('close_note');">[-]</a></strong> </td>
							</tr>
							<tr>
								<td>禁止注册</td>
								<td><input type="radio" name="setting[closereg]" value="1" $closereg_Y />
									是
									<input type="radio" name="setting[closereg]" value="0" $closereg_N />
									否<br />
									<span>选择“是”将禁止游客注册,但不影响过去已注册的用户.</span> </td>
							</tr>
							<tr>
								<td>开启网站运行信息</td>
								<td><input type="radio" name="setting[show_debug]" value="1" $show_debug_Y />
									是
									<input type="radio" name="setting[show_debug]" value="0" $show_debug_N />
									否<br />
									<span>选择“是”将显示SQL查询次数和脚本执行时间.</span> </td>
							</tr>
							<tr>
								<td>统计代码</td>
								<td><textarea id="stat_code" class="textarea" name="setting[stat_code]" style="width:400px;height:80px;">$settings[stat_code]</textarea>
									<strong><a href="###" onclick="resizeup('stat_code');">[+]</a> <a href="###" onclick="resizedown('stat_code');">[-]</a></strong> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'display'}-->
					<fieldset>
						<legend>显示设置</legend>
						<table class="config">
							<tr>
								<td width="160">每页显示文章数</td>
								<td><input class="text-small" type="text" name="setting[article_shownum]" size="15" value="$settings[article_shownum]" /></td>
							</tr>
							<tr>
								<td>分页最大限度</td>
								<td><input class="text-small" type="text" name="setting[maxpages]" size="15" value="$settings[maxpages]" />
									<br />
									<span>一般不用改,除非是数据量过万的站点需要限制一下以达到控制负荷的目的.</span></td>
							</tr>
							<tr>
								<td>随机文章数量</td>
								<td><input class="text-small" type="text" name="setting[randarticle_num]" size="15" value="$settings[randarticle_num]" />
									<br />
									<span>设置0则表示不显示.开启随机文章每次打开页面都会查询一次.</span> </td>
							</tr>
							<tr>
								<td>侧栏最新文章数量</td>
								<td><input class="text-small" type="text" name="setting[recentarticle_num]" size="15" value="$settings[recentarticle_num]" />
									<br />
									<span>设置0则表示不显示.</span> </td>
							</tr>
							<tr>
								<td>侧栏最新文章截取字节数</td>
								<td><input class="text-small" type="text" name="setting[recentarticle_limit]" size="15" value="$settings[recentarticle_limit]" /></td>
							</tr>
							<tr>
								<td>显示上下篇文章</td>
								<td><input type="radio" name="setting[show_n_p_title]" value="1" $show_n_p_title_Y />
									是
									<input type="radio" name="setting[show_n_p_title]" value="0" $show_n_p_title_N />
									否
									<br />
									<span>显示上下篇文章的标题,文章数据量巨大的时候,会稍微加重服务器负担.</span> </td>
							</tr>
							<tr>
								<td>热门标签显示数量</td>
								<td><input class="text-small" type="text" name="setting[hottags_shownum]" size="15" value="$settings[hottags_shownum]" />
									<br />
									<span>设置0则表示不显示.</span> </td>
							</tr>
							<tr>
								<td>相关文章显示数量</td>
								<td><input class="text-small" type="text" name="setting[related_shownum]" size="15" value="$settings[related_shownum]" />
									<br />
									<span>浏览文章的时候,可以显示使用相同标签的文章,选择不显示在浏览文章的时候减少一次查询以提高程序执行效率.建议不要设置太大,建议设置10,设置为0表示不显示相关文章.</span> </td>
							</tr>
							<tr>
								<td>相关文章标题截取字数</td>
								<td><input class="text-long" type="text" name="setting[related_title_limit]" size="15" value="$settings[related_title_limit]" /></td>
							</tr>
							<tr>
								<td>显示日历</td>
								<td><input type="radio" name="setting[show_calendar]" value="1" $show_calendar_Y />
									是
									<input type="radio" name="setting[show_calendar]" value="0" $show_calendar_N />
									否 </td>
							</tr>
							<tr>
								<td>显示博客统计</td>
								<td><input type="radio" name="setting[show_statistics]" value="1" $show_statistics_Y />
									是
									<input type="radio" name="setting[show_statistics]" value="0" $show_statistics_N />
									否 </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'comment'}-->
					<fieldset>
						<legend>评论设置</legend>
						<table class="config">
							<tr>
								<td width="160">禁止发表评论</td>
								<td><input type="radio" name="setting[close_comment]" value="1" $close_comment_Y />
									是
									<input type="radio" name="setting[close_comment]" value="0" $close_comment_N />
									否
									<br />
									<span>禁止发表评论并不影响之前存在的评论.</span> </td>
							</tr>
							<tr>
								<td>评论需要审核</td>
								<td><input type="radio" name="setting[audit_comment]" value="1" $audit_comment_Y />
									是
									<input type="radio" name="setting[audit_comment]" value="0" $audit_comment_N />
									否 </td>
							</tr>
							<tr>
								<td>新评论在文章中的排序</td>
								<td><input type="radio" name="setting[comment_order]" value="1" $comment_order_Y />
									靠后
									<input type="radio" name="setting[comment_order]" value="0" $comment_order_N />
									靠前 </td>
							</tr>
							<tr>
								<td>回复评论邮件通知</td>
								<td><input type="radio" name="setting[comment_email_reply]" value="1" $comment_email_reply_Y />
									是
									<input type="radio" name="setting[comment_email_reply]" value="0" $comment_email_reply_N />
									否
									<br />
									<span>回复别人的评论,给别人的邮箱发送有回复的通知.</span></td>
							</tr>
							<tr>
								<td>侧栏最新评论数量</td>
								<td><input class="text-small" type="text" name="setting[recentcomment_num]" size="15" value="$settings[recentcomment_num]" />
									<br />
									<span>设置0则表示不显示.</span> </td>
							</tr>
							<tr>
								<td>侧栏最新评论截取字节数</td>
								<td><input class="text-small" type="text" name="setting[recentcomment_limit]" size="15" value="$settings[recentcomment_limit]" /></td>
							</tr>
							<tr>
								<td>单篇文章显示评论数</td>
								<td><input class="text-small" type="text" name="setting[article_comment_num]" size="15" value="$settings[article_comment_num]" />
									<br />
									<span>设为“0”则显示全部评论.不分页.</span> </td>
							</tr>
							<tr>
								<td>评论列表每页数量</td>
								<td><input class="text-small" type="text" name="setting[commentlist_num]" size="15" value="$settings[commentlist_num]" />
									<br />
									<span>评论列表每页显示的评论条数.</span> </td>
							</tr>
							<tr>
								<td>提交评论时间间隔(秒)</td>
								<td><input class="text-small" type="text" name="setting[comment_post_space]" size="15" value="$settings[comment_post_space]" />
									<br />
									<span>可以防止他人灌水,设为“0”则不限制.</span> </td>
							</tr>
							<tr>
								<td>评论中允许出现的链接数</td>
								<td><input class="text-small" type="text" name="setting[spam_url_num]" size="15" value="$settings[spam_url_num]" />
									<br />
									<span>如果出现的链接数大于所设置的数量,则怀疑是垃圾信息,需要人工审核.如果设置为&quot;0&quot;则不限制.</span> </td>
							</tr>
							<tr>
								<td>评论内容的最少字节数</td>
								<td><input class="text-small" type="text" name="setting[comment_min_len]" size="15" value="$settings[comment_min_len]" />
									<br />
									<span>三个字节是一个汉字.</span> </td>
							</tr>
							<tr>
								<td>评论内容允许最大字数</td>
								<td><input class="text-small" type="text" name="setting[comment_max_len]" size="15" value="$settings[comment_max_len]" />
									<br />
									<span>可以有效控制游客输入内容的数据量.</span> </td>
							</tr>
							<tr>
								<td>评论垃圾内容大小界限</td>
								<td><input class="text-small" type="text" name="setting[spam_content_size]" size="15" value="$settings[spam_content_size]" />
									<br />
									<span>和上面的&quot;评论内容允许最大字数&quot;不同,超过这里设置的字节数则怀疑是垃圾信息,需要人工审核,如果设置为&quot;0&quot;或大于上面的最大字数则不启用此设置.</span> </td>
							</tr>
							<tr>
								<td>启用Trackback</td>
								<td><input type="radio" name="setting[enable_trackback]" value="1" $enable_trackback_Y />
									是
									<input type="radio" name="setting[enable_trackback]" value="0" $enable_trackback_N />
									否
									<br />
									<span>禁用Trackback后将停用一切关于Trackback的功能,也不会显示之前存在的Trackback.</span> </td>
							</tr>
							<tr>
								<td>接收需要审核</td>
								<td><input type="radio" name="setting[audit_trackback]" value="1" $audit_trackback_Y />
									是
									<input type="radio" name="setting[audit_trackback]" value="0" $audit_trackback_N />
									否<br />
									<span>可以修改Trackback Spam机制防范等级来防范大部分垃圾信息,防范Spam不会检查内容是否为国家法律允许内容,如果有比较敏感内容,建议开启人工审核所有接收的Trackback.</span> </td>
							</tr>
							<tr>
								<td>接收有效期控制</td>
								<td><input type="radio" name="setting[trackback_life]" value="1" $trackback_life_Y />
									是
									<input type="radio" name="setting[trackback_life]" value="0" $trackback_life_N />
									否<br />
									<span>如果开启该项,只有文章发表后的24小时内允许Trackback.</span> </td>
							</tr>
							<tr>
								<td>垃圾信息防范强度</td>
								<td><input type="radio" name="setting[tb_spam_level]" value="strong" $tb_spam_level[strong] />
									强<br />
									<input type="radio" name="setting[tb_spam_level]" value="weak" $tb_spam_level[weak] />
									弱<br />
									<input type="radio" name="setting[tb_spam_level]" value="never" $tb_spam_level[never] />
									无<br />
									<span>强的等级过滤最为严格,包括人工发送的重复性,弱等级会对数据来源做基本检查.无则不做任何检查直接通过验证.</span> </td>
							</tr>
							<tr>
								<td>显示头像</td>
								<td><input type="radio" name="setting[show_avatar]" value="1" $show_avatar_Y />
									是 
									<input type="radio" name="setting[show_avatar]" value="0" $show_avatar_N />
									否</td>
							</tr>
							<tr>
								<td>头像大小</td>
								<td><input class="text-small" type="text" name="setting[avatar_size]" size="15" value="$settings[avatar_size]" /> px
									<br />
									<span>头像的大小,宽和高都应用改设置.</span> </td>
							</tr>
							<tr>
								<td>头像等级</td>
								<td>
							<input type="radio" name="setting[avatar_level]" value="G" $avatar_level[G] /> G &#8212; 普通级别, 任何年龄的访客都适合查看<br />
							<input type="radio" name="setting[avatar_level]" value="PG" $avatar_level[PG] /> PG &#8212; 有一定争议性的头像, 只适合13岁以上查看<br />
							<input type="radio" name="setting[avatar_level]" value="R" $avatar_level[R] /> R &#8212; 成人级, 只适合17岁以上成年人查看<br />
							<input type="radio" name="setting[avatar_level]" value="X" $avatar_level[X] /> X &#8212; 最高等级, 不适合大多数人查看
							</td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'attach'}-->
					<fieldset>
						<legend>附件设置</legend>
						<table class="config">
							<tr>
								<td width="160">附件存放目录</td>
								<td><input class="text-medium" type="text" name="setting[attachments_dir]" size="70" value="$settings[attachments_dir]" />
									<br />
									<span>不要加“/”.程序会在该目录下自动按照年月的目录形式存放附件.</span> </td>
							</tr>
							<tr>
								<td>图片生成缩略图</td>
								<td><input type="radio" name="setting[attachments_thumbs]" value="1" $attachments_thumbs_Y />
									是
									<input type="radio" name="setting[attachments_thumbs]" value="0" $attachments_thumbs_N />
									否<br />
									<span>上传的图片附件尺寸大于下面的设置,就生成缩略图,以减少页面输出带宽.$gd_version</span> </td>
							</tr>
							<tr>
								<td>图片缩略触发大小</td>
								<td><input class="text-medium" type="text" name="setting[attachments_thumbs_size]" size="70" value="$settings[attachments_thumbs_size]" />
									<br />
									<span>例如：150x150, 即使没有缩略图, 文章也会按照此设定输出图片附件大小</span> </td>
							</tr>
							<tr>
								<td>图片是否直接显示</td>
								<td><input type="radio" name="setting[display_attach]" value="1" $display_attach_Y />
									是
									<input type="radio" name="setting[display_attach]" value="0" $display_attach_N />
									否<br />
									<span>如果选“是”,如果是图片附件,就直接显示出来,否则就会提示保存到本地.</span> </td>
							</tr>
							<tr>
								<td>附件防盗链</td>
								<td><input type="radio" name="setting[remote_open]" value="1" $remote_open_Y />
									是
									<input type="radio" name="setting[remote_open]" value="0" $remote_open_N />
									否<br />
									<span>如果选“是”,就禁止直接从地址栏输入地址和从其他站点访问.只能从附件所属文章点击.反之不做任何限制.</span> </td>
							</tr>
							<tr>
								<td>使用图片水印功能</td>
								<td><input type="radio" name="setting[watermark]" value="1" $watermark_Y />
									是
									<input type="radio" name="setting[watermark]" value="0" $watermark_N />
									否<br />
									<span>上传的图片中加上图片水印,水印图片位于 ./templates/$options[templatename]/img/watermark.png,您可替换此文件以实现不同的水印效果.不支持动画 GIF 格式.</span> </td>
							</tr>
							<tr>
								<td>添加水印的图片触发大小</td>
								<td><input class="text-medium" type="text" name="setting[watermark_size]" size="70" value="$settings[watermark_size]" />
									<br />
									<span>只对超过设置大小的图片才加上水印图片,如果留空则不做限制.例如：150x150</span> </td>
							</tr>
							<tr>
								<td>水印位置</td>
								<td><input type="radio" name="setting[waterpos]" value="1" $$waterpos[1] />
									左上<br />
									<input type="radio" name="setting[waterpos]" value="2" $waterpos[2] />
									左下<br />
									<input type="radio" name="setting[waterpos]" value="3" $waterpos[3] />
									右上<br />
									<input type="radio" name="setting[waterpos]" value="4" $waterpos[4] />
									右下<br />
									<input type="radio" name="setting[waterpos]" value="5" $waterpos[5] />
									中间<br />
									<input type="radio" name="setting[waterpos]" value="6" $waterpos[6] />
									随机<br /></td>
							</tr>
							<tr>
								<td>水印透明度</td>
								<td><input class="text-small" type="text" name="setting[watermarktrans]" size="70" value="$settings[watermarktrans]" />
									<br />
									<span>范围为 1~100 的整数,数值越大水印图片透明度越低.本功能需要开启水印功能后才有效.</span> </td>
							</tr>
							<tr>
								<td>水印边距</td>
								<td><input class="text-small" type="text" name="setting[pos_padding]" size="70" value="$settings[pos_padding]" />
									<br />
									<span>图片水印位于原图边缘的距离.请填入大于0的整数,不填默认为 5px.</span> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'dateline'}-->
					<fieldset>
						<legend>时间设置</legend>
						<table class="config">
							<tr>
								<td width="160">使用人性化时间格式</td>
								<td><input type="radio" name="setting[dateconvert]" value="1" $dateconvert_Y />
									是
									<input type="radio" name="setting[dateconvert]" value="0" $dateconvert_N />
									否<br />
									<span>选择“是”，时间将显示以“n分钟前”、“昨天”、“n天前”等形式显示.</span> </td>
							</tr>
							<tr>
								<td>服务器所在时区</td>
								<td><select name="setting[server_timezone]">
										<option value="-12" $zone_012>(标准时-12:00) 日界线西</option>
										<option value="-11" $zone_011>(标准时-11:00) 中途岛、萨摩亚群岛</option>
										<option value="-10" $zone_010>(标准时-10:00) 夏威夷</option>
										<option value="-9" $zone_09>(标准时-9:00) 阿拉斯加</option>
										<option value="-8" $zone_08>(标准时-8:00) 太平洋时间(美国和加拿大)</option>
										<option value="-7" $zone_07>(标准时-7:00) 山地时间(美国和加拿大)</option>
										<option value="-6" $zone_06>(标准时-6:00) 中部时间(美国和加拿大)、墨西哥城</option>
										<option value="-5" $zone_05>(标准时-5:00) 东部时间(美国和加拿大)、波哥大</option>
										<option value="-4" $zone_04>(标准时-4:00) 大西洋时间(加拿大)、加拉加斯</option>
										<option value="-3.5" $zone_03_5>(标准时-3:30) 纽芬兰</option>
										<option value="-3" $zone_03>(标准时-3:00) 巴西、布宜诺斯艾利斯、乔治敦</option>
										<option value="-2" $zone_02>(标准时-2:00) 中大西洋</option>
										<option value="-1" $zone_01>(标准时-1:00) 亚速尔群岛、佛得角群岛</option>
										<option value="111" $zone_111>(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡</option>
										<option value="1" $zone_1>(标准时+1:00) 中欧时间、安哥拉、利比亚</option>
										<option value="2" $zone_2>(标准时+2:00) 东欧时间、开罗,雅典</option>
										<option value="3" $zone_3>(标准时+3:00) 巴格达、科威特、莫斯科</option>
										<option value="3.5" $zone_3_5>(标准时+3:30) 德黑兰</option>
										<option value="4" $zone_4>(标准时+4:00) 阿布扎比、马斯喀特、巴库</option>
										<option value="4.5" $zone_4_5>(标准时+4:30) 喀布尔</option>
										<option value="5" $zone_5>(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇</option>
										<option value="5.5" $zone_5_5>(标准时+5:30) 孟买、加尔各答、新德里</option>
										<option value="6" $zone_6>(标准时+6:00) 阿拉木图、 达卡、新亚伯利亚</option>
										<option value="7" $zone_7>(标准时+7:00) 曼谷、河内、雅加达</option>
										<option value="8" $zone_8>(北京时间) 北京、重庆、香港、新加坡</option>
										<option value="9" $zone_9>(标准时+9:00) 东京、汉城、大阪、雅库茨克</option>
										<option value="9.5" $zone_9_5>(标准时+9:30) 阿德莱德、达尔文</option>
										<option value="10" $zone_10>(标准时+10:00) 悉尼、关岛</option>
										<option value="11" $zone_11>(标准时+11:00) 马加丹、索罗门群岛</option>
										<option value="12" $zone_12>(标准时+12:00) 奥克兰、惠灵顿、堪察加半岛</option>
									</select>
									<br />
									<span>Sablog-X所在的服务器是放在哪个时区?</span> </td>
							</tr>
							<tr>
								<td>文章的时间格式</td>
								<td><input class="text-medium" type="text" name="setting[article_timeformat]" size="70" value="$settings[article_timeformat]" />
									<br />
									<span>$settings[article_timeformat] 显示为 <!--{eval echo date($settings[article_timeformat])}--></span> </td>
							</tr>
							<tr>
								<td>评论和Trackback的时间格式</td>
								<td><input class="text-medium" type="text" name="setting[comment_timeformat]" size="70" value="$settings[comment_timeformat]" />
									<br />
									<span>$settings[comment_timeformat] 显示为 <!--{eval echo date($settings[comment_timeformat])}--></span> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'seo'}-->
					<fieldset>
						<legend>SEO设置</legend>
						<table class="config">
							<tr>
								<td width="160">标题附加字</td>
								<td><textarea id="title_keywords" class="textarea" name="setting[title_keywords]" style="width:400px;height:80px;">$settings[title_keywords]</textarea>
									<strong><a href="###" onclick="resizeup('title_keywords');">[+]</a> <a href="###" onclick="resizedown('title_keywords');">[-]</a></strong><br />
									<span>网页标题通常是搜索引擎关注的重点,本附加字设置将出现在标题中,如果有多个关键字,建议用 &quot;|&quot;、&quot;,&quot; 等符号分隔</span> </td>
							</tr>
							<tr>
								<td>Meta Keywords</td>
								<td><textarea id="textarea" class="textarea" name="setting[meta_keywords]" style="width:400px;height:80px;">$settings[meta_keywords]</textarea>
									<strong><a href="###" onclick="resizeup('meta_keywords');">[+]</a> <a href="###" onclick="resizedown('meta_keywords');">[-]</a></strong><br />
									<span>Keywords 项出现在页面头部的 Meta 标签中,用于记录本页面的关键字,多个关键字间请用半角逗号 &quot;,&quot; 隔开</span> </td>
							</tr>
							<tr>
								<td>Meta Description</td>
								<td><textarea id="meta_description" class="textarea" name="setting[meta_description]" style="width:400px;height:80px;">$settings[meta_description]</textarea>
									<strong><a href="###" onclick="resizeup('meta_description');">[+]</a> <a href="###" onclick="resizedown('meta_description');">[-]</a></strong><br />
									<span>Description 出现在页面头部的 Meta 标签中,用于记录本页面的概要与描述</span> </td>
							</tr>
							<tr>
								<td>启用SiteMap</td>
								<td><input type="radio" name="setting[sitemap]" value="1" $sitemap_Y />
									是
									<input type="radio" name="setting[sitemap]" value="0" $sitemap_N />
									否<br />
									<span>启用SiteMap会增加或者加快某些搜索对您网站的收录.</span> </td>
							</tr>
							<tr>
								<td>启用301跳转</td>
								<td><input type="radio" name="setting[jumpwww]" value="1" $jumpwww_Y />
									是
									<input type="radio" name="setting[jumpwww]" value="0" $jumpwww_N />
									否<br />
									<span>将domain.com跳转到www.domain.com，避免搜索引擎分散网站的权重</span> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'wap'}-->
					<fieldset>
						<legend>WAP设置</legend>
						<table class="config">
							<tr>
								<td width="160">启用WAP</td>
								<td><input type="radio" name="setting[wap_enable]" value="1" $wap_enable_Y />
									是
									<input type="radio" name="setting[wap_enable]" value="0" $wap_enable_N />
									否<br />
									<span>WAP是一种无线通信应用协议,开启WAP后用户可通过手机访问你的博客,实现浏览,评论等功能,你自己亦可以通过手机使用本功能发表文章.</span> </td>
							</tr>
							<tr>
								<td>文章页面长度控制</td>
								<td><input class="text-small" type="text" name="setting[wap_article_limit]" size="15" value="$settings[wap_article_limit]" />
									<br />
									<span>请设置为大于 0 的整数,用于控制 WAP 看帖页面长度,并根据该长度对帖子内容进行拆分.建议设置为 300~3000 以内的整数,以便获得更多的兼容性和浏览易用性.</span> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'ban'}-->
					<fieldset>
						<legend>安全设置</legend>
						<table class="config">
							<tr>
								<td width="160">启用验证码</td>
								<td><input type="radio" name="setting[seccode]" value="1" $seccode_Y />
									是
									<input type="radio" name="setting[seccode]" value="0" $seccode_N />
									否<br />
									<span>$gd_version</span> </td>
							</tr>
							<tr>
								<td>验证码随机背景图形</td>
								<td><input type="radio" name="setting[seccode_adulterate]" value="1" $seccode_adulterate_Y />
									是
									<input type="radio" name="setting[seccode_adulterate]" value="0" $seccode_adulterate_N />
									否</td>
							</tr>
							<tr>
								<td>验证码随机TTF字体</td>
								<td><input type="radio" name="setting[seccode_ttf]" value="1" $seccode_ttf_Y />
									是
									<input type="radio" name="setting[seccode_ttf]" value="0" $seccode_ttf_N />
									否</td>
							</tr>
							<tr>
								<td>验证码随机倾斜度</td>
								<td><input type="radio" name="setting[seccode_angle]" value="1" $seccode_angle_Y />
									是
									<input type="radio" name="setting[seccode_angle]" value="0" $seccode_angle_N />
									否</td>
							</tr>
							<tr>
								<td>验证码随机颜色</td>
								<td><input type="radio" name="setting[seccode_color]" value="1" $seccode_color_Y />
									是
									<input type="radio" name="setting[seccode_color]" value="0" $seccode_color_N />
									否</td>
							</tr>
							<tr>
								<td>验证码随机文字大小</td>
								<td><input type="radio" name="setting[seccode_size]" value="1" $seccode_size_Y />
									是
									<input type="radio" name="setting[seccode_size]" value="0" $seccode_size_N />
									否</td>
							</tr>
							<tr>
								<td>验证码文字阴影</td>
								<td><input type="radio" name="setting[seccode_shadow]" value="1" $seccode_shadow_Y />
									是
									<input type="radio" name="setting[seccode_shadow]" value="0" $seccode_shadow_N />
									否</td>
							</tr>
							<tr>
								<td>启用IP禁止功能</td>
								<td><input type="radio" name="setting[banip_enable]" value="1" $banip_enable_Y />
									是
									<input type="radio" name="setting[banip_enable]" value="0" $banip_enable_N />
									否<br />
									<span>选择“是”将杜绝下面设置的IP提交评论.</span> </td>
							</tr>
							<tr>
								<td>禁止IP列表</td>
								<td><textarea id="ban_ip" class="textarea" name="setting[ban_ip]" style="width:400px;height:80px;">$settings[ban_ip]</textarea>
									<strong><a href="###" onclick="resizeup('ban_ip');">[+]</a> <a href="###" onclick="resizedown('ban_ip');">[-]</a></strong><br />
									<span>输入禁止发表评论的IP地址,可以使用&quot;*&quot;作为通配符禁止某段地址,用&quot;,&quot;格开.</span> </td>
							</tr>
							<tr>
								<td>启用Spam防范功能</td>
								<td><input type="radio" name="setting[spam_enable]" value="1" $spam_enable_Y />
									是
									<input type="radio" name="setting[spam_enable]" value="0" $spam_enable_N />
									否<br />
									<span>Spam是指利用程序进行广播式的广告宣传的行为.这种行为给很多人的信箱、留言、评论里塞入大量无关或无用的信息.开启后以下设置才生效.</span> </td>
							</tr>
							<tr>
								<td>垃圾词语特征</td>
								<td><textarea id="spam_words" class="textarea" name="setting[spam_words]" style="width:400px;height:80px;">$settings[spam_words]</textarea>
									<strong><a href="###" onclick="resizeup('spam_words');">[+]</a> <a href="###" onclick="resizedown('spam_words');">[-]</a></strong><br />
									<span>开启Spam防范功能后,在新发表的评论、Trackback内容中如果包含了这里的关键词则需要人工审核.用&quot;,&quot;格开.</span> </td>
							</tr>
							<tr>
								<td>用户名保留关键字</td>
								<td><textarea id="censoruser" class="textarea" name="setting[censoruser]" style="width:400px;height:80px;">$settings[censoruser]</textarea>
									<strong><a href="###" onclick="resizeup('censoruser');">[+]</a> <a href="###" onclick="resizedown('censoruser');">[-]</a></strong><br />
									<span>注册的用户名中无法使用这些关键字.每个关键字用半角逗号隔开,如 angel,4ngel. 访客同样无法使用这些关键字作为用户名发表评论.</span> </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'rss'}-->
					<fieldset>
						<legend>RSS设置</legend>
						<table class="config">
							<tr>
								<td width="160">启用RSS订阅</td>
								<td><input type="radio" name="setting[rss_enable]" value="1" $rss_enable_Y />
									是
									<input type="radio" name="setting[rss_enable]" value="0" $rss_enable_N />
									否<br />
									<span>开启后将允许用户使用 RSS 客户端软件接收最新的文章.</span> </td>
							</tr>
							<tr>
								<td>RSS 订阅文章数量</td>
								<td><input class="text-small" type="text" name="setting[rss_num]" size="15" value="$settings[rss_num]" /></td>
							</tr>
							<tr>
								<td>RSS TTL(分钟)</td>
								<td><input class="text-small" type="text" name="setting[rss_ttl]" size="15" value="$settings[rss_ttl]" />
									<br />
									<span>TTL(Time to Live) 是RSS 2.0 的一项属性,用于控制订阅内容的自动刷新时间,时间越短则资料实时性就越高,但会加重服务器负担,通常可设置为 30～180 范围内的数值默认值为60分钟,设置为0则不缓存(稍微消耗系统资源)</span></td>
							</tr>
							<tr>
								<td>RSS聚合全文输出</td>
								<td><input type="radio" name="setting[rss_all_output]" value="1" $rss_all_output_Y />
									是
									<input type="radio" name="setting[rss_all_output]" value="0" $rss_all_output_N />
									否
									<br />
									<span>选择“否”,只输出文章描述,如果文章无描述,还是输出全文.</span></td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<!--{if $action == '' || $action == 'all' || $action == 'permalink'}-->
					<fieldset>
						<legend>伪静态设置</legend>
						<table class="config">
							<tr>
								<td width="160">使用html扩展名(当前设置无效)</td>
								<td><input type="radio" name="setting[use_html]" value="1" $use_html_Y />
									是
									<input type="radio" name="setting[use_html]" value="0" $use_html_N />
									否<br />
									<span>如果使用html扩展，将在每个链接后加html，否则所有链接将以目录形式出现</span> </td>
							</tr>
							<tr>
								<td>默认(关闭)</td>
								<td><input style="float:right" type="radio" name="setting[permalink]" value="0" $permalink_N />
									$options[url]show.php?action&amp;id=文章ID </td>
							</tr>
							<tr>
								<td>文章名或文章ID</td>
								<td><input style="float:right;margin:10px 0;" type="radio" name="setting[permalink]" value="1" $permalink_Y />
									$options[url]archives/文章ID/<br />
									$options[url]文章名/ </td>
							</tr>
						</table>
					</fieldset>
				<!--{/if}-->
				<div class="button-submit">
					<input type="hidden" name="action" value="updatesetting" />
					<input type="hidden" name="oldaction" value="$action" />
					<input type="submit" value="确定" />
				</div>
				</form>

			</div>
			<!-- // #main -->
			<div class="clear"></div>
		</div>
		<!-- // #container -->