<?php

if(!defined('SABLOG_ROOT') || !isset($php_self) || !preg_match("/[\/\\\\]cp\.php$/", $php_self)) {
	exit('Access Denied');
}
permission(array(1,2));

if ($_POST['action'] == 'autosave') {
	if ($_POST['title'] || $_POST['description'] || $_POST['content']) {
		autosave_recache($_POST['title'], $_POST['description'], $_POST['content']);
	}
}

if ($_GET['action'] == 'switchtodraft') {
	if (@include_once(SABLOG_ROOT.'data/cache/cache_autosave.php')) {
		$autosavedb = sax_stripslashes($autosavedb);
		$title = $autosavedb[$sax_uid]['title'];
		$description = $autosavedb[$sax_uid]['description'];
		$content = $autosavedb[$sax_uid]['content'];
		$content = str_replace(array("\r","\n"), '', $content);
		$description = str_replace(array("\r","\n"), '', $description);
?>
var timestamp = '<?php echo sadate('m月d日,H:i:s');?>';
$('#title').val('<?php echo $title;?>');
oEditor.html('<?php echo $content;?>');
if ($('#description').length) {
	oEditor2.html('<?php echo $description;?>');
}
$('#timemsg2').html('已经恢复'+timestamp+'的数据');
<?php
	}
}

exit;

?>