<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<div id="page">
		<div id="wrap">
			<h3 class="title">标签</h3>
			<!--{if $stats['tag_count']}-->
				<div class="tags">
				<!--{loop $tagdb $tag}-->
					<span style="line-height:160%;font-size:$tag[fontsize]px;margin-right:10px;"><a href="$tag[url]" title="使用次数: $tag[count]">$tag[name]</a></span>
				<!--{/loop}-->
				</div>
				$multipage
			<!--{else}-->
				<p>没有任何标签</p>
			<!--{/if}-->
		</div>
		<div id="sidebar">

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

			{template sidecomm}

			<!--{if $options['recentarticle_num']}-->
				<div class="stitle">最新文章</div>
				<ul>
					<!--{if !$newarticlecache}-->
						<li>没有任何文章</li>
					<!--{else}-->
						<!--{loop $newarticlecache $data}-->
						<li><a href="$data[article_url]" title="$data[title]">$data[trimmed_title]</a></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			<!--{/if}-->

			<!--{if $archivecache}-->
				<div class="stitle">归档</div>
				<ul>
					<!--{loop $archivecache $key $data}-->
						<li><a href="$data[url]">$key</a> <span>($data[num])</span></li>
					<!--{/loop}-->
					<!--{if $archivenum > 12}-->
						<li><a href="$archives_url">更多...</a></li>
					<!--{/if}-->
				</ul>
			<!--{/if}-->

			<div class="stitle">其他</div>
			<ul>
				<!--{if $options['rss_enable']}-->
				<li><a href="$options[url]rss.php">RSS 2.0</a></li>
				<!--{/if}-->
				<!--{if $options['wap_enable']}-->
				<li><a href="$options[url]wap/">WAP</a></li>
				<!--{/if}-->
				<li><a href="http://validator.w3.org/check?uri=referer" target="_blank">HTML 5</a></li>
				<!--{if $options['icp']}-->
				<li><a href="http://www.miibeian.gov.cn/" target="_blank">$options[icp]</a></li>
				<!--{/if}-->
			</ul>

		</div>
	</div>