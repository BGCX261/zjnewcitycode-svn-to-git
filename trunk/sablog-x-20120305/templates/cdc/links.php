<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
{template header}

<div id="container">
	<!--Content-->
	<div id="content">
		<div class="content_text">
			<div class="title">
				<h3>友情链接</h3>
			</div>
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

	</div>
	<!--Sidebar End-->
</div>
<!--Container End-->