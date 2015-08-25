<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!--Footer-->
<div class="footer">
	<div class="footerbar">

		<!--Copyright-->
		<div class="copyright">
			<p class="about_info">
				<a href="$options[url]">Home</a> | <a href="$options[url]sitemap.xml">Sitemap</a>
				<!--{if $options['icp']}-->
				| <a href="http://www.miibeian.gov.cn/" target="_blank">$options[icp]</a>
				<!--{/if}-->
			</p>

			<p>
				Copyright &copy; 2003-2012 <a href="$options[url]">$options[name]</a>. Powered By <a href="http://www.sablog.net" title="$SABLOG_VERSION build $SABLOG_RELEASE" target="_blank">SaBlog-X</a>.<!--{if $options['show_debug']}-->{$sa_debug}.<!--{/if}-->
			</p>
		</div>
		<!--Copyright End-->   
	
	</div>

</div>
<!--Footer End-->
<div id="statusmsg"></div>
<!--{if $options['stat_code']}-->
	<div style="display:none;">{$options[stat_code]}</div>
<!--{/if}-->

<!--[if IE 6]>
<script type="text/javascript" src="http://cdc.tencent.com/wp-content/themes/cdcblog2/js/png24.js" ></script>
<script type="text/javascript">
DD_belatedPNG.fix('#top,a#logo,a#logo:hover,#menu li a:hove,#menu li#on a,#menu li#rss,#menu li a.rss,#menu li a.rss:hover,#menu li a.feed,#menu li a.feed:hover');
</script>
<![endif]-->
</body>
</html>