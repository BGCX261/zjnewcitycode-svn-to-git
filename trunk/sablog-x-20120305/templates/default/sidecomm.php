<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>

			<div class="stitle">分类</div>
			<ul>
				<!--{if !$catecache}-->
					<li>没有任何分类</li>
				<!--{else}-->
					<!--{loop $catecache $data}-->
					<li><a href="$data[url]">$data[name]</a> <a href="$data[rss_url]"><img src="$options[url]templates/$options[templatename]/img/rss.gif" alt="rss" /></a>  <span>($data[count])</span></li>
					<!--{/loop}-->
				<!--{/if}-->
			</ul>

		<!--{if $options['randarticle_num'] && $rand_article}-->
			<div class="stitle">随机文章</div>
			<ul>
			<!--{loop $rand_article $data}-->
				<li><a title="Permanent Link to $data[title]" href="$data[url]">$data[title]</a></li>
			<!--{/loop}-->
			</ul>
		<!--{/if}-->