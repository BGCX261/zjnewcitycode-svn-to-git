function checkusername() {
	if ($("#username").val() == "") {
		$("#checkusername").html("请输入您的名字");
		return false;
	} else {
		$("#checkusername").html('');
		return true;
	}
}
function checkpassword() {
	if ($("#password").val() == "") {
		$("#checkpassword").html("请输入密码");
		return false;
	} else {
		if ($("#password").length() < 8)	{
			$("#checkpassword").html("密码长度不能少于8位");
		} else {
			$("#checkpassword").html('');
			return true;
		}
	}
}
function checkpassword2() {
	if ($("#password").val() !== $("#comfirpassword").val()) {
		$("#checkpassword2").html("两次输入密码不一样");
		return false;
	} else {
		$("#checkpassword2").html('');
		$("#checkpassword").html('');
		return true;
	}
}
function checkseccode() {
	if ($("#clientcode").val() == "")	{
		$("#checkseccode").html("请输入验证码");
		return false;
	} else {
		$("#checkseccode").html('');
		return true;
	}
}

function checkemail() {
	if ($("#email").val() == "")	{
		$("#checkemail").html("请输入E-mail");
		return false;
	} else {
		$("#checkemail").html('');
		return true;
	}
}

function checkurl() {
}