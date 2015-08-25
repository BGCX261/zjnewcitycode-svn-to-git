<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
	<div class="clear"></div>
</div>
<div id="footer">
	Powered by <a href="http://www.sablog.net" title="$SABLOG_VERSION build $SABLOG_RELEASE" target="_blank">SaBlog-X</a> $SABLOG_VERSION build $SABLOG_RELEASE.
	Copyright &copy; 2003-2012 <a href="$options[url]">$options[name]</a>
	<!--{if $options['show_debug']}-->
		<br />{$sa_debug}.
	<!--{/if}-->
</div>
<div id="statusmsg"></div>
<!--{if $options['stat_code']}-->
	<div style="display:none;">{$options[stat_code]}</div>
<!--{/if}-->
</body>
</html>