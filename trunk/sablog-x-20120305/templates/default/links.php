<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

	<div id="page">
		<div id="wrap">
			<div class="title">友情链接</div>
			<!--{if $linkdb}-->
				<ul class="linkover">
				<!--{loop $linkdb $link}-->
					<li class="onelink"><a href="$link[url]" rel="nofollow" target="_blank" title="$link[note]">$link[name]</a></li>
				<!--{/loop}-->
				</ul>
			<!--{else}-->
				<p>没有任何友情链接</p>
			<!--{/if}-->
		</div>
		<div id="sidebar">

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