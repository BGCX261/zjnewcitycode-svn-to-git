<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

		<form action="$options[url]post.php" class="search" id="searchform" method="post">
			<input type="hidden" name="action" value="search" />
			<input type="hidden" name="formhash" value="$formhash" />
			<div>
				<!--
				隐藏表单,可有可无
				<select name="mids[]" multiple="multiple" style="width:100%" size="8">
					<option value="" selected="selected">搜索所有分类</option>
					<!--{loop $catecache $data}-->
						<option value="$data[mid]">&nbsp;&nbsp;&#0124;&#45;&#45; $data[name]</option>
					<!--{/loop}-->
				</select>
				<select name="searchin">
					<option value="title" selected="selected">标题搜索</option>
					<option value="content">全文搜索</option>
				</select>
				-->
				<input type="text" class="search_text" value="" id="keywords" name="keywords">
				<input type="submit" onmouseout="this.className='search_submit'" onmousedown="this.className='search_submitactive'" onmousemove="this.className='search_submithover'" class="search_submit" value="" id="button">
			</div>
		</form>

		<!--{if $options['show_calendar']}-->
			<ul>
				<li>
					<h3>日历</h3>
					<div id="showcalendar">
						<table cellpadding="0" cellspacing="1" style="width:100%">
							<tr align="center">
								<td colspan="7" class="curdate"><a href="$prevmonth">&laquo;</a> $calendar[cur_date] <a href="$nextmonth">&raquo;</a></td>
							</tr>
							<tr>
								<th class="week"><span style="color:#833">日</span></th>
								<th class="week">一</th>
								<th class="week">二</th>
								<th class="week">三</th>
								<th class="week">四</th>
								<th class="week">五</th>
								<th class="week"><font color="#53A300">六</font></th>
							</tr>
							$calendar[html]
						</table>
					</div>
				</li>
			</ul>
		<!--{/if}-->

		<ul>
			<li>
				<h3>分类</h3>
				<ul>
					<!--{if !$catecache}-->
						<li>没有任何分类</li>
					<!--{else}-->
						<!--{loop $catecache $data}-->
						<li><a title="$data[name]" href="$data[url]">$data[name]</a> <a title="$data[name] RSS Feed" href="$data[rss_url]"><img src="$options[url]templates/$options[templatename]/img/rss.gif" alt="rss" /></a> <span>($data[count])</span></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			</li>
		</ul>

		<!--{if $options['randarticle_num'] && $rand_article}-->
			<ul>
				<li>
					<h3>随机文章</h3>
					<ul>
					<!--{if !$rand_article}-->
						<li>没有任何文章</li>
					<!--{else}-->
						<!--{loop $rand_article $data}-->
						<li><a title="$data[title]" href="$data[url]">$data[title]</a></li>
						<!--{/loop}-->
					<!--{/if}-->
					</ul>
				</li>
			</ul>
		<!--{/if}-->