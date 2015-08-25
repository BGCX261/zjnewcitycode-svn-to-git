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
	<div id="container">
		<!--Content-->
		<div id="content">
			<div class="content_text">
				<div class="title">
					<h3>文章归档</h3>
				</div>
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
		</div>
		<!--Content End-->

		<!--Sidebar-->

		<div id="sidebar">

			{template sidecomm}

			<!--{if $options['recentarticle_num']}-->
				<h1>最新文章</h1>
				<div class="classify">
					<ul>
						<!--{if !$newarticlecache}-->
							<li class="none">没有任何文章</li>
						<!--{else}-->
							<!--{eval $i=0}-->
							<!--{loop $newarticlecache $data}-->
							<!--{eval $i++}-->
							<li{if count($newarticlecache) == $i} class="none"{/if}><a href="$data[article_url]" title="$data[title]&#13;$data[dateline]">$data[trimmed_title]</a></li>
							<!--{/loop}-->
						<!--{/if}-->
					</ul>
				</div>
			<!--{/if}-->

			<!--{if $archivecache}-->
				<h1>归档</h1>
				<div class="classify">
					<ul>
						<!--{loop $archivecache $key $data}-->
							<li><a href="$data[url]">$key</a> <span>($data[num])</span></li>
						<!--{/loop}-->
						<!--{if $archivenum > 12}-->
							<li class="none"><a href="$archives_url">更多...</a></li>
						<!--{/if}-->
					</ul>
				</div>
			<!--{/if}-->

			<!--{if $options['hottags_shownum']}-->
				<h1>热门标签</h1>
				<div class="classify">
					<ul>
						<!--{if !$tagcache}-->
							<li>没有任何标签</li>
						<!--{else}-->
							<!--{eval $i=0}-->
							<!--{loop $tagcache $data}-->
							<!--{eval $i++}-->
							<li{if count($tagcache) == $i} class="none"{/if}><a href="$data[url]">$data[name]</a> <span>($data[count])</span></li>
							<!--{/loop}-->
						<!--{/if}-->
					</ul>
				</div>
			<!--{/if}-->

			<!--{if $options['show_statistics']}-->
				<h1>统计</h1>
				<div class="classify">
					<ul>
						<li>文章: <span class="num">$stats[article_count]</span> 篇</li>
						<li>评论: <span class="num">$stats[comment_count]</span> 条</li>
						<li class="none">标签: <span class="num">$stats[tag_count]</span> 个</li>
					</ul>
				</div>
			<!--{/if}-->

		</div>
		<!--Sidebar End-->
	</div>
	<!--Container End-->