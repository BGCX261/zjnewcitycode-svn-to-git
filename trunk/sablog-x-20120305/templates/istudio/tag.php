<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<section id="content">
		<section class="postlist">
			<div class="archive">
				<h4 class="title">标签</h4>
			</div>
			<div class="post">
				<!--{if $stats['tag_count']}-->
					<div class="taglist">
						<ul>
						<!--{loop $tagdb $tag}-->
							<li><span style="font-size:$tag[fontsize]px;"><a href="$tag[url]" title="使用次数: $tag[count]">$tag[name]</a></span></li>
						<!--{/loop}-->
						</ul>
						<div class="clear"></div>
					</div>
				<!--{else}-->
					<p>没有任何标签</p>
				<!--{/if}-->
				$multipage
			</div>
		</section>
		<aside>

			{template sidecomm}

			<!--{if $options['recentarticle_num']}-->
				<ul>
					<li>
						<h3>最新文章</h3>
						<ul>
						<!--{if !$newarticlecache}-->
							<li>没有任何文章</li>
						<!--{else}-->
							<!--{loop $newarticlecache $data}-->
							<li><a title="$data[title]" href="$data[article_url]">$data[trimmed_title]</a></li>
							<!--{/loop}-->
						<!--{/if}-->
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<!--{if $archivecache}-->
				<ul>
					<li>
						<h3>归档</h3>
						<ul>
						<!--{loop $archivecache $key $data}-->
							<li><a title="$key" href="$data[url]">$key</a> <span>($data[num])</span></li>
						<!--{/loop}-->
						<!--{if $archivenum > 12}-->
							<li><a href="$archives_url">更多...</a></li>
						<!--{/if}-->
						</ul>
					</li>
				</ul>
			<!--{/if}-->

			<ul>
				<li>
					<h3>其他</h3>
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
				</li>
			</ul>
		</aside>
		<section class="clear"></section>
	</section>