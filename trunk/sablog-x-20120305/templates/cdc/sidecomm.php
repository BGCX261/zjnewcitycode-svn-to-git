<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

		<!--Search-->
		<div class="search">
			<form method="post" action="$options[url]post.php">
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="action" value="search" />
				<input name="keywords" type="text" value="" class="search-input" title="请输入关键字" />
				<input type="submit" value="" class="so" onmouseout="this.className='so'" onmouseover="this.className='soHover'" />
			</form>
		</div>
		<!--Search End-->

		<!--{if $options['show_calendar']}-->
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
		<!--{/if}-->

		<h1>分类</h1>
		<div class="classify">
			<ul>
				<!--{if !$catecache}-->
					<li>没有任何分类</li>
				<!--{else}-->
					<!--{eval $i=0}-->
					<!--{loop $catecache $data}-->
					<!--{eval $i++}-->
					<li{if count($catecache) == $i} class="none"{/if}><a href="$data[url]">$data[name]</a> <span>($data[count])</span></li>
					<!--{/loop}-->
				<!--{/if}-->
			</ul>
		</div>

		<!--{if $options['randarticle_num'] && $rand_article}-->
			<h1>随机文章</h1>
			<div class="classify">
				<ul>
				<!--{eval $i=0}-->
				<!--{loop $rand_article $data}-->
					<!--{eval $i++}-->
					<li{if count($rand_article) == $i} class="none"{/if}><a title="Permanent Link to $data[title]" href="$data[url]">$data[title]</a></li>
				<!--{/loop}-->
				</ul>
			</div>
		<!--{/if}-->