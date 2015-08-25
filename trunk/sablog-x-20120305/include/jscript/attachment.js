
var aid = 1;
var attachexts = new Array();
var attachwh = new Array();

function delAttach(id) {
	document.getElementById('attachbody').removeChild(document.getElementById('localno_' + id).parentNode.parentNode);
	document.getElementById('attachbtn').removeChild(document.getElementById('attachnew_' + id).parentNode);
	document.getElementById('attachbody').innerHTML == '' && addAttach();
	document.getElementById('localimgpreview_' + id) ? document.body.removeChild(document.getElementById('localimgpreview_' + id)) : null;
}

function addAttach() {
	var id = aid;
	var tags, newnode, i;
	newnode = document.getElementById('attachbtnhidden').firstChild.cloneNode(true);
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'attach[]') {
			tags[i].id = 'attachnew_' + id;
			tags[i].onchange = function() {insertAttach(id)};
			tags[i].unselectable = 'on';
		}
	}
	document.getElementById('attachbtn').appendChild(newnode);
	newnode = document.getElementById('attachbodyhidden').firstChild.cloneNode(true);
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'localid[]') {
			tags[i].value = id;
		}
	}
	tags = newnode.getElementsByTagName('span');
	for(i in tags) {
		if(tags[i].id == 'localfile[]') {
			tags[i].id = 'localfile_' + id;
		} else if(tags[i].id == 'cpadd[]') {
			tags[i].id = 'cpadd_' + id;
		} else if(tags[i].id == 'cpdel[]') {
			tags[i].id = 'cpdel_' + id;
		} else if(tags[i].id == 'localno[]') {
			tags[i].id = 'localno_' + id;
		}
	}
	aid++;
	newnode.style.display = 'none';
	document.getElementById('attachbody').appendChild(newnode);
	document.getElementById('uploadlist').scrollTop = 10000;
}

function insertAttach(id) {
	var localimgpreview = '';
	var path = document.getElementById('attachnew_' + id).value;
	var extpos = path.lastIndexOf('.');
	var ext = extpos == -1 ? '' : path.substr(extpos + 1, path.length).toLowerCase();
	var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
	var localfile = document.getElementById('attachnew_' + id).value.substr(document.getElementById('attachnew_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);
	var filename = localfile;

	if(path == '') {
		return;
	}

	document.getElementById('cpdel_' + id).innerHTML = '<a href="###" onclick="delAttach(' + id + ')">删除</a>';
	if (inarticle == 1) {
		document.getElementById('cpadd_' + id).innerHTML = '<a href="###" title="点击这里将本附件插入帖子内容中当前光标的位置"' + 'onclick="insertAttachtext(' + id + ');return false;">插入</a>';
	}
	document.getElementById('localfile_' + id).innerHTML = '<span>' + filename + '</span>';
	document.getElementById('attachnew_' + id).style.display = 'none';
	document.getElementById('localno_' + id).parentNode.parentNode.style.display = '';
	addAttach();
}

lastshowpreview = null;
function showpreview(ctrlobj, showid) {
	if(lastshowpreview) {
		lastshowpreview.id = '';
	}
	if(!ctrlobj.onmouseout) {
		 ctrlobj.onmouseout = function() { hideMenu(); }
	}
	ctrlobj.id = 'imgpreview';
	lastshowpreview = ctrlobj;
	document.getElementById('imgpreview_menu').innerHTML = '<table width="100%" height="100%"><tr><td align="center" valign="middle">' + document.getElementById(showid).innerHTML + '</td></tr></table>';
	showMenu('imgpreview', false, 2, 1, 0);
	document.getElementById('imgpreview_menu').style.top = (parseInt(document.getElementById('imgpreview_menu').style.top) - document.getElementById('uploadlist').scrollTop) + 'px';
}

function insertAttachtext(id) {
	addhtml('[localfile=' + id + ']');
}

function attachthumbImg(w, h, twidth, theight) {
	twidth = !twidth ? thumbwidth : twidth;
	theight = !theight ? thumbheight : theight;
	var x_ratio = twidth / w;
	var y_ratio = theight / h;
	var wh = new Array();
	if((x_ratio * h) < theight) {
		wh['h'] = Math.ceil(x_ratio * h);
		wh['w'] = twidth;
	} else {
		wh['w'] = Math.ceil(y_ratio * w);
		wh['h'] = theight;
	}
	return wh;
}


if(allowpostattach) {
	addAttach();
}