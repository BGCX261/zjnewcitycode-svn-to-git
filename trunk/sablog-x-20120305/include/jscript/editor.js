// 下面的CK的。如果要改变编辑器。先找到要改变编辑器的插入内容到编辑器中的方法。
// 否则插入附件的功能将不能使用。

//插入内容到编辑器

/*
kindeditor 3
function addhtml(content){
	if ( KE.g['content'].html ) {
		KE.insertHtml('content', content);
		oEditor.insertHtml(content) ;
	} else {
		alert('请先转换到所见即所得模式') ;
	}
}
*/

/*
kindeditor 4
*/
function addhtml(content){
	if ( oEditor.edit.designMode ) {
		oEditor.insertHtml(content);
	} else {
		alert('请先转换到所见即所得模式') ;
	}
}