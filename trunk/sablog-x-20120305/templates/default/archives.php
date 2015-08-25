<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<!--{eval $curyear = sadate('Y')}-->
	<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('.car-list').find('.car-monthlisting').hide();
				jQuery('.car-list').find('.year-{$curyear}').show();
				jQuery('.car-list').find('.year-{$curyear}').parent().find('.car-toggle').html('-');
				jQuery('.car-list').find('.car-toggle').click(function() {
					jQuery(this).parent('div').next('ul').slideToggle('fast',function(){
						if (jQuery(this).parent().find('.car-monthlisting').is(':hidden')) {
							var state = "closed";
							jQuery(this).parent().find('.car-toggle').html('+');
						} else {
							var state = "open";
							jQuery(this).parent().find('.car-toggle').html('-');
						}
					});
				});
				jQuery('.car-toggler').click(function() {
					if ( '展开所有月份' == jQuery(this).text() ) {
						jQuery('.car-monthlisting').show();
						jQuery(this).text('折叠所有月份');
						jQuery('.car-toggle').html('-');
					}
					else {
						jQuery('.car-monthlisting').hide();
						jQuery(this).text('展开所有月份');
						jQuery('.car-toggle').html('+');
					}
					return false;
				});
			});
	</script>
	<div id="page">
		<div id="wrap">
			<div class="title">文章归档</div>

			<!--{if $stats['article_count']}-->
				<div class="car-container car-collapse">
					<p>共有 $stats[article_count] 篇日志, <a href="#" class="car-toggler">展开所有月份</a></p>
					<ul class="car-list">
					<!--{loop $articledb $date $data}-->
						<!--{eval list($year,$month) = explode('-', $date)}-->
						<!--{eval $yearurl = getdatelink($year.$month)}-->
						<!--{eval $month_num = count($data)}-->
						<li><div class="car-yearmonth"><span class="car-toggle">+</span> <a href="$yearurl">{$year}年{$month}月</a> <span title="日志数量">({$month_num})</span></div>
							<ul class="car-monthlisting year-{$year}">
								<!--{loop $data $article}-->
									<li>$article[dateline] - <a href="$article[url]">$article[title]</a> (评论: <span style="color:#833">$article[comments]</span>)</li>
								<!--{/loop}-->
							</ul>
						</li>
					<!--{/loop}-->
					</ul>
				</div>
			<!--{else}-->
				<p>没有任何文章</p>
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

			<!--{if $options['hottags_shownum']}-->
				<div class="stitle">热门标签</div>
				<!--{if !$tagcache}-->
					<ul>
						<li>没有任何标签</li>
					</ul>
				<!--{else}-->
					<div class="tags">
					<!--{loop $tagcache $data}-->
						<span style="line-height:160%;font-size:$data[fontsize]px;margin-right:10px;"><a href="$data[url]" title="使用次数: $data[count]">$data[name]</a></span>
					<!--{/loop}-->
					</div>
				<!--{/if}-->
			<!--{/if}-->

			<!--{if $options['recentcomment_num']}-->
				<div class="stitle">最新评论</div>
				<ul>
					<!--{if !$newcommentcache}-->
						<li>没有任何评论</li>
					<!--{else}-->
						<!--{loop $newcommentcache $data}-->
						<li><a href="$data[article_url]">$data[content]</a><br /><span>$data[dateline] - $data[author]</span></li>
						<!--{/loop}-->
					<!--{/if}-->
				</ul>
			<!--{/if}-->

			<!--{if $options['show_statistics']}-->
				<div class="stitle">统计</div>
				<ul>
					<li>文章: <span class="num">$stats[article_count]</span> 篇</li>
					<li>评论: <span class="num">$stats[comment_count]</span> 条</li>
					<li>标签: <span class="num">$stats[tag_count]</span> 个</li>
				</ul>
			<!--{/if}-->

			<!--{if $linkcache}-->
				<div class="stitle">友情链接</div>
				<ul>
					<!--{loop $linkcache $data}-->
						<li><a href="$data[url]" target="_blank" title="$data[note]">$data[name]</a></li>
					<!--{/loop}-->
					<li><a href="$links_url">更多...</a></li>
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
				<li><a href="http://validator.w3.org/check?uri=referer" target="_blank">XHTML 1.0</a></li>
				<!--{if $options['icp']}-->
				<li><a href="http://www.miibeian.gov.cn/" target="_blank">$options[icp]</a></li>
				<!--{/if}-->
			</ul>

	  </div>
	</div>