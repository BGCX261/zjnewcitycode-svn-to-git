<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="UTF-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta name="copyright" content="SaBlog" />
<meta name="author" content="angel,4ngel" />
<title>Login</title>
<link rel="stylesheet" href="$options[url]templates/admin/transdmin.css" type="text/css" media='all' />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="$options[url]templates/admin/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="$options[url]templates/admin/ie7.css" /><![endif]-->
<script type="text/javascript">
var blogurl = '$options[url]';
</script>
<script type="text/javascript" src="$options[url]include/jscript/jquery.js?ver=1.6.1"></script>
<script type="text/javascript" src="$options[url]include/jscript/common.js"></script>
<script type="text/javascript" src="$options[url]include/jscript/admin.js"></script>
</head>
<body>
	<div id="loginBox">
		<div id="main">
			<form action="cp.php" method="post" onsubmit="this.submit.disabled=true;">
				<input type="hidden" name="action" value="dologin" />
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="referer" value="$referer" />
				<fieldset>
					<legend>Userame OR E-mail</legend>
					<input name="username" id="username" type="text" size="30" onBlur="checkusername();" tabindex="1" maxlength="40" value="" class="text-medium" />
					<span id="checkusername"></span>
				</fieldset>
				<fieldset>
					<legend>Password</legend>
					<input name="password" id="password" type="password" size="30" onBlur="checkpassword();" tabindex="2" maxlength="50" value="" class="text-medium" />
					<span id="checkpassword"></span>
				</fieldset>
				<p class="button-submit">
					<input type="submit" name="submit" value="Submit" />
				</p>
				<!--{if !$options['closereg']}-->
					<p><a href="$options[url]cp.php?action=register">Register</a></p>
				<!--{/if}-->
			</form>
			<script type="text/javascript" src="$options[url]include/jscript/login.js"></script>
		</div>
	</div>
</body>
</html>