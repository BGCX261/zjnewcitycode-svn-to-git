function checkform() {
	if ($('#username').length > 0 && $('#username').val() == "") {
		alert("请输入您的名字.");
		return false;
	}
	if ($('#email').length > 0 && $('#email').val() == "") {
		alert("请输入您的电子邮件.");
		return false;
	}
	if ($('#clientcode').length > 0 && $('#clientcode').val() == "")	{
		alert("请输入验证码.");
		return false;
	}
	if ($('#cmcontent').val() == "")	{
		alert("请输入内容.");
		return false;
	}
	if (((postminchars != 0 && $('#cmcontent').val().length < postminchars) || (postmaxchars != 0 && $('#cmcontent').val().length > postmaxchars))) {
		alert("您的评论内容长度不符合要求。\n\n当前长度: "+$('#cmcontent').val().length+" 字节\n系统限制: "+postminchars+" 到 "+postmaxchars+" 字节");
		return false;
	}
	$('#submit').attr("disabled", true);
	return true;
}

function ctlent(event) {
	if((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83)) {
		$("#submit").click();
	}
}

function addquote(obj,strAuthor){
	var text = $('#'+obj).html();
	text = text.replace(/alt\=(\"|)([^\"\s]*)(\"|)/g,"> $2 <");
	text = text.replace(/\<[^\<\>]+\>/g,"\n");
	text = text.replace(/ +/g," ");
	text = text.replace(/\n+/g,"\n");
	text = text.replace(/^\n*/gm,"");
	text = text.replace(/^\s*/gm,"");
	text = text.replace(/\n*$/gm,"");
	text = text.replace(/\s*$/gm,"");
	text = text.replace(/&lt;/g,"<");
	text = text.replace(/&gt;/g,">");
	text = text.replace(/&nbsp;&nbsp;/g,"  ");
	text = text.replace(/&amp;/g,"&");
	$("#cmcontent").val($("#cmcontent").val() + "[quote="+strAuthor+"]"+text+"[/quote]");
	$("#cmcontent").focus();
}

function setCopy(content){
	if(navigator.userAgent.toLowerCase().indexOf('ie') > -1) {
		clipboardData.setData('Text',content);
		alert ("该地址已经复制到剪切板");
	} else {
		prompt("请复制网站地址:",content);
	}
}

function showajaxdiv(url) {
	var x = new Ajax('statusid', 'XML');
	x.get(url, showxml);
}

function tagshow(tag) {
	var x = new Ajax('statusid', 'XML');
	x.get(blogurl + 'getxml.php?action=tag&tag=' + encodeURIComponent(tag), showxml);
}

function fiximage(thumbs_size) {
	var max = thumbs_size.split('x');
	var fixwidth = max[0];
	var fixheight = max[1];
	imgs = document.getElementsByTagName('img');
	for(i=0;i<imgs.length;i++) {
		w=imgs[i].width;h=imgs[i].height;
		if(w>fixwidth) { imgs[i].width=fixwidth;imgs[i].height=h/(w/fixwidth);}
		if(h>fixheight) { imgs[i].height=fixheight;imgs[i].width=w/(h/fixheight);}
	}
}

function commentReply(comment_parent,c){
	var response = document.getElementById('comment-post');
	$('#comment_parent').val(comment_parent);
	$('#cancel-reply').show();
	$('#reply_desc').show();
	c.parentNode.parentNode.appendChild(response);
}
function cancelReply(){
	var commentPlace = document.getElementById('comment-place'),response = document.getElementById('comment-post');
	$('#comment_parent').val(0);
	$('#cancel-reply').hide();
	$('#reply_desc').hide();
	commentPlace.appendChild(response);
}