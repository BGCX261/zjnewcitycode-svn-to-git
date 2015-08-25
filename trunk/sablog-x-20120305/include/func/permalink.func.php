<?php

// 返回分类连接
function getcatelink($cid, $url) {
	global $options;
	if ($options['permalink']) {
		if ($url) {
			return $options['url'].'category/'.urlencode($url).'/';
		} else {
			return $options['url'].'category/'.$cid.'/';
		}
	} else {
		return $options['url'].'?cid='.$cid;
	}
}

// 返回搜索连接
function getsearchlink($searchid) {
	global $options;
	if ($options['permalink']) {
		return $options['url'].'search/'.$searchid.'/';
	} else {
		return $options['url'].'?action=article&amp;searchid='.$searchid;
	}
}

function getrsslink($cid = 0, $url = '') {
	global $options;
	if ($options['permalink']) {
		if ($url) {
			$permalink = $options['url'].'rss/'.urlencode($url).'/';
		} else {
			$permalink = $options['url'].'rss/'.($cid ? $cid.'/' : '');
		}
		return $permalink;
	} else {
		return $options['url'].'rss.php'.($cid ? '?cid='.$cid : '');
	}
}

function gettaglink($url) {
	global $options;
	if ($options['permalink']) {
		return $options['url'].'tag/'.urlencode($url).'/';
	} else {
		return $options['url'].'?action=article&amp;tag='.$url;
	}
}

function getdatelink($setdate) {
	global $options;
	if ($options['permalink']) {
		return $options['url'].'date/'.$setdate.'/';
	} else {
		return $options['url'].'?action=article&amp;setdate='.$setdate;
	}
}

function getdaylink($setdate, $setday = 0) {
	global $options;
	if ($options['permalink']) {
		return $options['url'].'date/'.$setdate.'/'.($setday ? $setday.'/' : '');
	} else {
		return $options['url'].'?action=article&amp;setdate='.$setdate.($setday ? '&amp;setday='.$setday : '');
	}
}

function getpagelink($action = '') {
	global $options;
	if ($options['permalink']) {
		return $options['url'].$action.'/';
	} else {
		return $options['url'].'?action='.$action;
	}
}

function getpermalink($articleid, $alias = '', $page = 0) {
	global $options;
	if ($options['permalink']) {
		if ($alias) {
			$permalink = $options['url'].urlencode($alias).'/'.($page ? $page.'/' : '');
		} else {
			$permalink = $options['url'].'archives/'.$articleid.'/'.($page ? $page.'/' : '');
		}
		return $permalink;
	} else {
		return $options['url'].'?action=show&amp;id='.$articleid.($page ? '&amp;page='.$page : '');
	}
}

function redirect_permalink($articleid, $alias = '', $page = 0) {
	return str_replace('&amp;', '&', getpermalink($articleid, $alias = '', $page));
}

function getuserlink($userinfo, $isname = true) {
	global $options;
	if ($options['permalink']) {
		if ($isname) {
			return $options['url'].'user/'.urlencode($userinfo).'/';
		} else {
			return $options['url'].'uid/'.$userinfo.'/';
		}
	} else {
		if ($isname) {
			return $options['url'].'?action=article&amp;user='.urlencode($userinfo);
		} else {
			return $options['url'].'?action=article&amp;uid='.$userinfo;
		}
	}
}

?>