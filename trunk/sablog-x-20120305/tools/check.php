<?php
// ========================== 文件说明 ==========================//
// 本文件说明：检查数据库完整性
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

error_reporting(7);
set_magic_quotes_runtime(0);
header("content-Type: text/html; charset=UTF-8");
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('SABLOG_ROOT', dirname(dirname(__FILE__)).'/');

$action = addslashes($_GET['action'] ? $_GET['action'] : $_POST['action']);
$php_self = addslashes(htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
$timestamp = time();

// 防止 PHP 5.1.x 使用时间函数报错
if(PHP_VERSION > '5.1') {
	@date_default_timezone_set('UTC');
}
// 加载核心函数
require_once(SABLOG_ROOT.'include/func/global.func.php');

// 获得IP地址
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
$onlineip = sax_addslashes($onlineip);
@preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);

// 允许程序在 register_globals = off 的环境下工作
$onoff = function_exists('ini_get') ? ini_get('register_globals') : get_cfg_var('register_globals');
if ($onoff != 1) {
	@extract($_POST, EXTR_SKIP);
	@extract($_GET, EXTR_SKIP);
	@extract($_COOKIE, EXTR_SKIP);
}

// 判断 magic_quotes_gpc 状态
if (@get_magic_quotes_gpc()) {
    $_GET = sax_stripslashes($_GET);
    $_POST = sax_stripslashes($_POST);
    $_COOKIE = sax_stripslashes($_COOKIE);
}

// 调试函数
function pr($a) {
	echo '<pre>';
	print_r($a);
	echo '</pre>';
}

function cpmsg($message, $url = 'javascript:history.go(-1);') {
	$message = "<meta HTTP-EQUIV=\"REFRESH\" content=\"2;URL=$url\" /><p>$message</p>";
	if($url) {
		$message .= "<p><a href=\"$url\">跳转</a></p>";
	}
	echo $message;
	exit();
}

function createtable($sql) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=utf8" : " TYPE=$type");
}

$dbsql = SABLOG_ROOT.'install/sax.sql';
!$action && $action = 'dbcheck';
if ($action == 'dbcheck') {

	// 加载数据库配置信息
	require_once(SABLOG_ROOT.'config.php');
	// 加载数据库类
	require_once(SABLOG_ROOT.'include/class/mysql.class.php');
	// 初始化数据库类
	$DB = new DB_MySQL;
	$DB->connect($servername, $dbusername, $dbpassword, $dbname, $usepconnect);
	unset($servername, $dbusername, $dbpassword, $dbname, $usepconnect);

	if(!$DB->query("SHOW FIELDS FROM {$db_prefix}settings", 'SILENT')) {
		cpmsg('您的服务器环境不允许执行数据库校验，无法进行此操作。');
	}

	if(!isset($start)) {
		cpmsg('正在进行数据库校验，请稍候......', $php_self.'?action=dbcheck&start=yes');
	} else {

		if(!file_exists(SABLOG_ROOT.'tools/saxdb.md5')) {
			cpmsg('不存在校验文件，无法进行此操作。');
		}

		$fp = fopen(SABLOG_ROOT.'tools/saxdb.md5', "rb");
		$saxdb = fread($fp, filesize(SABLOG_ROOT.'tools/saxdb.md5'));
		fclose($fp);
		$DBmd5 = substr($saxdb, 0, 32);
		$saxdb = unserialize(substr($saxdb, 34));
		$settingsdata = $saxdb[1];
		$saxdb = $saxdb[0][0];
		$repair = !empty($repair) ? $repair : array();
		$setting = !empty($setting) ? $setting : array();
		$missingtable = !empty($missingtable) ? $missingtable : array();
		$repairtable = is_array($repairtable) && !empty($repairtable) ? $repairtable : array();

		if($_POST['repairsubmit'] && (!empty($repair) || !empty($setting) || !empty($repairtable) || !empty($missingtable))) {
			$error = '';$errorcount = 0;
			$alter = $fielddefault = array();

			foreach($missingtable as $value) {
				if(!isset($installdata)) {
					$fp = fopen($dbsql, "rb");
					$installdata = fread($fp, filesize($dbsql));
					$installdata = str_replace("\r", "\n", str_replace(' sablog_', ' '.$db_prefix, $installdata));
					fclose($fp);
				}
				preg_match("/CREATE TABLE ".$db_prefix.$value."\s+\(.+?;/is", $installdata, $a);
				$DB->query(createtable($a[0]));
			}

			foreach($repair as $table => $valuedata) {
				foreach ($valuedata as $value) {
					echo print_r($value);
					echo '<br>';
					if(!in_array($r_table, $repairtable)) {
						list($r_table, $r_field, $option) = explode('|', $value);
						if(!isset($repairrtable[$r_table]) && $fieldsquery = $DB->query("SHOW FIELDS FROM $db_prefix$r_table", 'SILENT')) {
							while($fields = $DB->fetch_array($fieldsquery)) {
								$fielddefault[$r_table][$fields['Field']] = $fields['Default'];
							}
						}

						$field = $saxdb[$r_table][$r_field];
						$altersql = '`'.$field['Field'].'` '.$field['Type'];
						$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
						$altersql .= in_array($fielddefault[$r_table][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
							$field['Null'] == 'NO' && $field['Default'] == '' ||
							preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
							'' : ' default \''.$field['Default'].'\'';
						$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
						$altersql = $option == 'modify' ? "MODIFY COLUMN ".$altersql : "ADD COLUMN ".$altersql;
						$alter[$r_table][] = $altersql;
					}
				}
			}

			foreach($alter as $r_table => $sqls) {
				$DB->query("ALTER TABLE `$db_prefix$r_table` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = $DB->geterrdesc()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}
			$alter = array();

			foreach($repairtable as $value) {
				foreach($saxdb[$value] as $field) {
					if(!isset($fielddefault[$value]) && $fieldsquery = $DB->query("SHOW FIELDS FROM $db_prefix$value", 'SILENT')) {
						while($fields = $DB->fetch_array($fieldsquery)) {
							$fielddefault[$value][$fields['Field']] = $fields['Default'];
						}
					}
					$altersql = '`'.$field['Field'].'` '.$field['Type'];
					$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
					$altersql .= in_array($fielddefault[$value][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
						$field['Null'] == 'NO' && $field['Default'] == '' ||
						preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
						'' : ' default \''.$field['Default'].'\'';
					$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
					$altersql = "MODIFY COLUMN ".$altersql;
					$alter[$value][] = $altersql;
				}
			}

			foreach($alter as $r_table => $sqls) {
				$DB->query("ALTER TABLE `$db_prefix$r_table` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = $DB->geterrdesc()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}

			if(!empty($setting)) {
				$settingsdatanow = array();
				$settingsquery = $DB->query("SELECT title FROM {$db_prefix}settings ORDER BY title");
				while($settings = $DB->fetch_array($settingsquery)) {
					$settingsdatanew[] = $settings['title'];
				}
				$settingsdellist = @array_diff($settingsdata, $settingsdatanew);
				if($setting['del'] && is_array($settingsdellist)) {
					foreach($settingsdellist as $title) {
						$DB->query("INSERT INTO {$db_prefix}settings (title, value) VALUES ('$title', '')", 'SILENT');
					}
				}
				//updatecache('settings');
			}

			if($errorcount) {
				cpmsg('数据库修复成功，但仍有 '.$errorcount.' 个数据字段修复失败，请返回。');
			} else {
				cpmsg('数据库修复成功。', $php_self.'?action=dbcheck');
			}
		}

		$installexists = file_exists($dbsql);
		$saxdbnew = $deltables = $excepttables = $missingtables = $charseterror = array();
		foreach($saxdb as $DBtable => $fields) {
			if($fieldsquery = $DB->query("SHOW FIELDS FROM $db_prefix$DBtable", 'SILENT')) {
				while($fields = $DB->fetch_array($fieldsquery)) {
					$r = '/^'.$db_prefix.'/';
					$cuttable = preg_replace($r, '', $DBtable);
					$saxdbnew[$cuttable][$fields['Field']]['Field'] = $fields['Field'];
					$saxdbnew[$cuttable][$fields['Field']]['Type'] = $fields['Type'];
					$saxdbnew[$cuttable][$fields['Field']]['Null'] = $fields['Null'] == '' ? 'NO' : $fields['Null'];
					$saxdbnew[$cuttable][$fields['Field']]['Extra'] = $fields['Extra'];
					$saxdbnew[$cuttable][$fields['Field']]['Default'] = $fields['Default'] == '' || $fields['Default'] == '0' ? '' : $fields['Default'];
				}
				ksort($saxdbnew[$cuttable]);
			} else {
				$missingtables[] = '<span style="float:left;width:33%">'.(($installexists ? '<input name="missingtable[]" type="checkbox" class="checkbox" value="'.$DBtable.'">' : '').$db_prefix.$DBtable).'</span>';
				$excepttables[] = $DBtable;
			}
		}

		if($DB->version() > '4.1') {
			$query = $DB->query("SHOW TABLE STATUS LIKE '$db_prefix%'");
			$standard_db_charset = 'utf8';
			while($tables = $DB->fetch_array($query)) {
				$r = '/^'.$db_prefix.'/';
				$cuttable = preg_replace($r, '', $tables['Name']);
				$tabledbcharset = substr($tables['Collation'], 0, strpos($tables['Collation'], '_'));
				if(strtoupper($standard_db_charset) != strtoupper($tabledbcharset)) {
					$charseterror[] = '<span style="float:left;width:33%">'.$db_prefix.$cuttable.'('.$tabledbcharset.')</span>';
				}
			}
		}

		$DBmd5new = md5(serialize($saxdbnew));

		$settingsdatanow = array();
		$settingsquery = $DB->query("SELECT title FROM {$db_prefix}settings ORDER BY title");
		while($settings = $DB->fetch_array($settingsquery)) {
			$settingsdatanew[] = $settings['title'];
		}
		$settingsdellist = @array_diff($settingsdata, $settingsdatanew);

		if($DBmd5 == $DBmd5new && empty($charseterror) && empty($settingsdellist)) {
			cpmsg('您的数据库完整无误');
		}

		$showlist = $addlists = '';
		foreach($saxdb as $DBtable => $fields) {
			$addlist = $modifylist = $dellist = array();
			if($fields != $saxdbnew[$DBtable]) {
				foreach($saxdb[$DBtable] as $key => $value) {
					if(is_array($missingtables) && in_array($db_prefix.$DBtable, $missingtables)) {
					} elseif(!isset($saxdbnew[$DBtable][$key])) {
						$dellist[] = $value;
					} elseif($value != $saxdbnew[$DBtable][$key]) {
						$modifylist[] = $value;
					}
				}
				if(is_array($saxdbnew[$DBtable])) {
					foreach($saxdbnew[$DBtable] as $key => $value) {
						if(!isset($saxdb[$DBtable][$key])) {
							$addlist[] = $value;
						}
					}
				}
			}

			if(($modifylist || $dellist) && !in_array($DBtable, $excepttables)) {
				$showlist .= '<table width="100%" border="1" cellpadding="4" cellspacing="0">';

				$showlist .= "<tr class=\"header\"><td width=\"40%\"><strong>$db_prefix$DBtable</strong> 错误的字段</td><td width=\"40%\">正确的字段</td><td width=\"20%\">状态</td></tr>";

				foreach($modifylist as $value) {
					$showlist .= "<tr class=\"altbg2\"><td><input name=\"repair[$DBtable][]\" class=\"checkbox\" type=\"checkbox\" value=\"$DBtable|$value[Field]|modify\"> <strong>".$value['Field']."</strong> ".
						$saxdbnew[$DBtable][$value['Field']]['Type'].
						($saxdbnew[$DBtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $saxdbnew[$DBtable][$value['Field']]['Extra']) && !preg_match('/text/i', $saxdbnew[$DBtable][$value['Field']]['Type']) ? ' default \''.$saxdbnew[$DBtable][$value['Field']]['Default'].'\'' : '').
						' '.$saxdbnew[$DBtable][$value['Field']]['Extra'].
						"</td><td><strong>".$value['Field']."</strong> ".$value['Type'].
						($value['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $value['Extra']) && !preg_match('/text/i', $value['Type']) ? ' default \''.$value['Default'].'\'' : '').
						' '.$value['Extra']."</td><td>".
						"<font color=\"#FF0000\">字段被修改</font></td></tr>";
				}

				foreach($dellist as $value) {
					$showlist .= "<tr class=\"altbg2\"><td><input name=\"repair[$DBtable][]\" class=\"checkbox\" type=\"checkbox\" value=\"$DBtable|$value[Field]|add\"> <strike><strong>".$value['Field']."</strong></strike></td><td> <strong>".$value['Field']."</strong> ".$value['Type'].($value['Null'] == 'NO' ? ' NOT NULL' : '')."</td><td>".
						"<font color=\"#0000FF\">字段被删除</font></td></tr>";
				}

				if($modifylist || $dellist) {
					//$showlist .= "<tr class=\"altbg1\"><td colspan=\"3\"><input onclick=\"setrepaircheck(this, this.form, '$DBtable')\" name=\"repairtable[]\" class=\"checkbox\" type=\"checkbox\" value=\"$DBtable\"> <strong>修复所有被修改的字段</strong></td></tr>";
				}

				$showlist .= '</td></tr></table><br />';
			}

			if($addlist) {
				$addlists .= "<tr class=\"category\"><td><strong>$db_prefix$DBtable</strong> 新增的字段</td></tr>";

				foreach($addlist as $value) {
					$addlists .= "<tr><td class=\"altbg1\">&nbsp;&nbsp;&nbsp;&nbsp;<strong>".$value['Field']."</strong> ".$saxdbnew[$DBtable][$value['Field']]['Type'].($saxdbnew[$DBtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '')."</td></tr>";
				}
			}

		}

		if($showlist) {
			$showlist = '<tr class="header"><td colspan="3">存在错误字段的数据表 (为了保证博客的正常运行，请立即修复以下字段)</td></tr><tr><td class="altbg1" colspan="3"><br />'.$showlist.'</td></tr>';
		}

		if($missingtables) {
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">缺少的数据表 (为了保证博客的正常运行，请立即补充以下缺少的数据表)</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">'.implode('', $missingtables).'</td></tr>';
		}

		if($settingsdellist) {
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">缺少的博客设置参数 (为了保证博客的正常运行，请立即补充以下设置参数)</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">';
			$showlist .= "<input name=\"setting[del]\" class=\"checkbox\" type=\"checkbox\" value=\"1\"> ".implode(', ', $settingsdellist).'<br />';
			$showlist .= '</td></tr>';
		}

		$showlist = $showlist ? '<form method="post" action="'.$php_self.'?action=dbcheck&start=yes"><input type="hidden" name="formhash" value="'.FORMHASH.'">'.
			'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.$showlist.'</table><br /><center>'.
			'<input type="submit" class="button" name="repairsubmit" value="修复选择的字段或数据"></center></form>' : '';

		if($charseterror) {
			$showlist .= '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">';
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">字符集错误的数据表 (字符集不一致可能会导致乱码，请手动修复以下数据表，标准的是字符集 $standard_db_charset)</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">'.implode('', $charseterror).'</td></tr></table>';
		}

		if($addlists) {
			$showlist .= '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
				'<tr class="header"><td colspan="3">新增字段的数据表 (以下数据表中的字段可能是某些插件添加的，如您确认无误，可以忽略它们)</td></tr>'.$addlists.'</table>';
		}

	}

	if(!$showlist) {
		cpmsg('您的数据库完整无误');
	} else {
?>
<script type="text/javascript">
function setrepaircheck(obj, form, table) {
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'checkbox' && e.name == 'repair['+table+'][]') {
			if(obj.checked) {
				e.checked = true;
			} else {
				e.checked = false;
			}
		}
	}
}
</script>
<?
		echo $showlist;
	}

?>
</form>
<?
}
?>