<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
	<hr />
	<footer>
		<p>Powered by <a href="http://www.sablog.net" title="$SABLOG_VERSION build $SABLOG_RELEASE" target="_blank">SaBlog-X $SABLOG_VERSION build $SABLOG_RELEASE</a>.
		Copyright &copy; 2003-2012 <a href="$options[url]">$options[name]</a>. Designed by <a href="http://xuui.net/" target="_blank">Xu.hel.</a> 
		<!--{if $options['show_debug']}-->
			<br />{$sa_debug}.
		<!--{/if}-->
		</p>
	</footer>

</section>
<div id="statusmsg"></div>
<!--{if $options['stat_code']}-->
	<div style="display:none;">{$options[stat_code]}</div>
<!--{/if}-->
</body>
</html>