<?php
// ========================== 文件说明 ==========================//
// 本文件说明：前台公共函数
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

/*
// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {

	// IIS Mod-Rewrite
	if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	}
	// IIS Isapi_Rewrite
	else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	}
	else {
		// If root then simulate that no script-name was specified
		if (empty($_SERVER['PATH_INFO']))
			$_SERVER['REQUEST_URI'] = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . '/';
		elseif ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
			// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
			$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
		else
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];

		// Append the query string if it exists and isn't null
		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}
*/
// 加载公用函数
require_once('include/common.inc.php');
require_once(SABLOG_ROOT.'include/func/front.func.php');

/*////////////////////////////////////////////////////////////////////////
$home_dir = dirname($php_self);
if (substr($home_dir, -1) != '/') {
	$home_dir = $home_dir.'/';
}
$request = str_replace($home_dir,'',$_SERVER['REQUEST_URI']);
$rewrite_rules = unserialize($options['permalink_rules']);
$i=0;
foreach ($rewrite_rules as $match => $query) {
	if (preg_match("!^$match!", $request, $matches) || preg_match("!^$match!", urldecode($request), $matches)) {
		$query = preg_replace("!^.+\?!", '', $query);
		$parms = explode('&', $query);
		$i++;
		foreach ($parms as $parm) {
			$frontlen = strrpos($parm, '=');
			$front    = substr($parm, 0, $frontlen);
			eval("$$front = \"$matches[$i]\";");
		}
		break;
	}
}
*/



// 检查模版
$t_dir = SABLOG_ROOT.'templates/'.$options['templatename'].'/';
if (!is_dir($t_dir)) {
	if (is_dir(SABLOG_ROOT.'templates/default')) {
		$options['templatename'] = 'default';
	} else {
		exit('Template Error: '.$t_dir.' is not a directory.');
	}
}

// 状态检查
if ($options['close']) {
	message(html_clean($options['close_note']),'');
}

// 获取时间，假如是WIN系统，一定要做范围的限制。否则.....
$setdate = (int)$_GET['setdate'];
if ($setdate && getstrlen($setdate) == 6) {
	$setyear = substr($setdate,0,4);
	if ($setyear >= 2038 || $setyear <= 1970) {
		$setyear = sadate('Y');
		$setmonth = sadate('m');
		$start = $end = 0;
	} else {
		$setmonth = substr($setdate,-2);
		list($start, $end) = explode('-', gettimestamp($setyear,$setmonth));
	}
} else {
	$setyear = sadate('Y');
	$setmonth = sadate('m');
	$start = $end = 0;
}

// 查询按月归档
//$monthname = array('','January','February','March','April','May','June','July','August','September','October','November','December');
// 查询并生成日历
if ($options['show_calendar']) {
	$calendar = calendar($setyear,$setmonth);
	$prevmonth = getdatelink($calendar['prevmonth']);
	$nextmonth = getdatelink($calendar['nextmonth']);
}

// 查询随机文章
if ($options['randarticle_num']) {
	$rand_article = get_rand_article();
}

/***
*处理一些数据以便直接应用到模板
**/

// 设置永久连接
if ($options['permalink']) {
	$archives_url = $options['url'].'archives/';
	$links_url = $options['url'].'links/';
	$tagslist_url = $options['url'].'tagslist/';
	$comments_url = $options['url'].'comments/';
} else {
	$archives_url = $options['url'].'?action=archives';
	$links_url = $options['url'].'?action=links';
	$tagslist_url = $options['url'].'?action=tagslist';
	$comments_url = $options['url'].'?action=comments';
}

// 处理归档显示
if ($archivecache) {
	$tmp = $archivesdb = array();
	foreach($archivecache as $key => $value) {
		list($y, $m) = explode('-', $key);
		$key = $y.'年'.$m.'月';
		$tmp[$key] = $value;
		$archivesdb[$y.$m] = $value['num'];
	}
	$archivecache = $tmp;
	$tmp = array();
}

/***
*处理缓存数据结束
**/

?>