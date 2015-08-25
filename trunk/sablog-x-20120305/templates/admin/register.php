<?php if(!defined('SABLOG_ROOT')) exit('Access Denied');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="UTF-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta name="copyright" content="SaBlog" />
<meta name="author" content="angel,4ngel" />
<title>Register</title>
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
		<div id="main" style="padding:0;">
		<h3>Register</h3>
			<form action="cp.php" method="post" onsubmit="this.submit.disabled=true;">
				<input type="hidden" name="action" value="doregister" />
				<input type="hidden" name="formhash" value="$formhash" />
				<input type="hidden" name="referer" value="$referer" />
				<fieldset>
					<legend>用户名(*)</legend>
					<input name="username" id="username" type="text" size="30" onBlur="checkusername();" tabindex="1" maxlength="40" value="" class="text-medium" />
					<span id="checkusername"></span>
				</fieldset>
				<fieldset>
					<legend>密码(*)</legend>
					<input name="password" id="password" type="password" size="30" onBlur="checkpassword();" tabindex="2" maxlength="50" value="" class="text-medium" />
					<span id="checkpassword"></span>
				</fieldset>
				<fieldset>
					<legend>确认密码(*)</legend>
					<input name="comfirpassword" id="comfirpassword" type="password" size="30" onBlur="checkpassword2();" tabindex="3" maxlength="50" value="" class="text-medium" />
					<span id="checkpassword2"></span>
				</fieldset>
				<fieldset>
					<legend>E-mail(*)</legend>
					<input name="email" id="email" type="text" size="40" onBlur="checkemail();" tabindex="4" maxlength="40" value="" class="text-medium" />
					<span id="checkemail"></span>
				</fieldset>
				<fieldset>
					<legend>主页(*)</legend>
					<input name="url" id="url" type="text" size="40" onBlur="checkurl();" tabindex="5" maxlength="75" value="" class="text-medium" />
					<span id="checkurl"></span>
				</fieldset>
				<!--{if $options['seccode']}-->
					<fieldset>
						<legend>Seccode(*)</legend>
						<input onfocus="updateseccode();this.onfocus = null;" name="clientcode" id="clientcode" value="" onBlur="checkseccode();" tabindex="6" class="text-small" size="6" maxlength="6" /> <div id="seccodeimage"></div>
						<span id="checkseccode"></span>
					</fieldset>
				<!--{/if}-->
				<p class="button-submit">
					<input type="submit" value="确定" />
				</p>
				<p><a href="$options[url]">Go home</a></p>
			</form>
			<script type="text/javascript" src="$options[url]include/jscript/login.js"></script>
		</div>
	</div>
</body>
</html>