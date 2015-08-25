var AutoSaveTime	= 60;   //修改每次保存时间(秒)

var autosave_time		= '秒后自动保存';
var autosave_wait		= '正在保存';
var autosave_error		= '自动保存错误';
var clear_error			= '清除失败';
var sure_restore		= '此操作将覆盖当前内容,确定要恢复数据吗?';
var switchtodraft_ok	= '已经恢复数据';
var switchtodraft_error	= '恢复数据错误';
var switchtodraft_wait	= '正在恢复';

savertimer = window.setTimeout("timer()", 0);

savetime = AutoSaveTime;
function timer() { 
	savetime = savetime - 1;
	$('#timemsg').html(savetime + autosave_time);
	if (savetime >= 0){
		savertimer = window.setTimeout("timer()", 1000);
	} else {
		if (savetime<=-1000) {
			savetime=AutoSaveTime;timer();
		} else {
			$('#timemsg').html(autosave_wait);
			savedraft();
			savetime=AutoSaveTime;
			timer();
		}
	}
}

function savedraft(formhash) {
	var title = $('#title').val();
	if ($('#description').val() != "") {
		var description = oEditor2.html();
	} else {
		var description = '';
	}
	$('#timemsg2').html(autosave_wait);
	var nowtime;
	var content = oEditor.html();
	var gourl = './cp.php';
	var postData = 'job=misc&action=autosave&formhash='+formhash+'&title='+title+'&description='+description+'&content='+content;
	makeRequest(gourl, postData, function () {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				var d = new Date();
				var nowtime = d.format("MM月dd日,hh:mm:ss");
				$('#timemsg2').html('自动保存于: ' + nowtime);
				savetime=-1000;
			}  else {
				$('#timemsg2').html(autosave_error);
			}
		}
	});
}

function cleardraft() {
	$('#title').val('');
	if ($('#description').length) {
		oEditor2.html('');
	}
	oEditor.html('');
	oEditor.focus();
}

function switchtodraft(formhash) {
	if(confirm(sure_restore)){
		$('#timemsg2').html(switchtodraft_wait);
		var oHead = document.getElementsByTagName('head').item(0);
		var oScript = document.createElement("script");
		oScript.type = 'text/javascript';
		oScript.src = 'cp.php?job=misc&action=switchtodraft';
		oHead.appendChild(oScript);
	} else {
		return;
	}
}

var http_request = false;
function makeRequest(url, sendData, functionName) {

	http_request = false;

	if (window.XMLHttpRequest) { // Non-IE...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			http_request.overrideMimeType('text/xml');
		}
	} else if (window.ActiveXObject) { // IE
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for(var i=0; i<versions.length; i++) {
			try {
				http_request = new ActiveXObject(versions[i]);
				if(http_request) {
					return http_request;
				}
			} catch(e) {
			}
		}
	}

	if (!http_request) {
		alert('Cannot send an XMLHTTP request');
		return false;
	}

	if(functionName) {
		var changefunc="http_request.onreadystatechange = "+functionName;
		eval(changefunc);
	}

	http_request.open('POST', url, true);
	http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http_request.send(sendData);

}

Date.prototype.format = function(format){

	var o = {
		"M+" :  this.getMonth()+1,  //month
		"d+" :  this.getDate(),     //day
		"h+" :  this.getHours(),    //hour
		"m+" :  this.getMinutes(),  //minute
		"s+" :  this.getSeconds(), //second
		"q+" :  Math.floor((this.getMonth()+3)/3),  //quarter
		"S"  :  this.getMilliseconds() //millisecond
	}
 
	if(/(y+)/.test(format)) {
		format = format.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
	}
 
	for(var k in o) {
		if(new RegExp("("+ k +")").test(format)) {
			format = format.replace(RegExp.$1, RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
		}
	}
	return format;
}