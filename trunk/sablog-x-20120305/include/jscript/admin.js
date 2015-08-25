function resizeup(obj) {
	$('#'+obj).height($('#'+obj).height() + 50);
}
function resizedown(obj) {
	$('#'+obj).height($('#'+obj).height() - 50);
}

function setdoit(operation) {
	$('#doit').val(operation);
	$('#form1').submit();
}

function doaction(operation) {
	$('#action').val(operation);
	$('#form1').submit();
}

function submit(form) {
	$('#' + form).submit();
}

function getalltag() {
	var x = new Ajax('statusid', 'XML');
	x.get(blogurl + 'getxml.php?action=getalltag', showxml);
}


function sa_alert(content) {

	if($("#statusmsg").length == 0) {
		var mDiv = $('<div id="statusmsg"></div>');
		$('body').append(mDiv);
	} else {
		var mDiv = $("#statusmsg");
	}
	if(is_opera) {
		clientHeight = document.body.clientHeight /2;
		clientWidth = document.body.clientWidth /2;
		scrollTop = document.body.scrollTop;
		scrollLeft = document.body.scrollLeft;
	} else {
		clientHeight = document.documentElement.clientHeight /2;
		clientWidth = document.documentElement.clientWidth /2;
		scrollTop = document.documentElement.scrollTop;
		scrollLeft = document.documentElement.scrollLeft;
	}
	mDiv.addClass('ajaxmsg');
	var popupWidth = 200;
	mDiv.css({
		"top": clientHeight + scrollTop,
		"left": clientWidth + scrollLeft - popupWidth / 2
	});
	mDiv.width(popupWidth);
	mDiv.show();
	mDiv.html('<span style="float:right;"><a style="color:#fff;" href="javascript:;" onclick="document.getElementById(\'statusmsg\').style.display=\'none\';">X</a></span><span style="float:left;">' + content + '</span>');

}