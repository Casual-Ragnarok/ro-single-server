<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_index.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_GET['view'])) {
	$_GET['view'] = 'all';
}

$perpage = $_G['setting']['feedmaxnum'] < 50 ? 50 : $_G['setting']['feedmaxnum'];

$page = intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1) * $perpage;

ckstart($start, $perpage);



$_G['home_today'] = strtotime('today');

if(empty($_GET['view'])) $_GET['view'] = 'top';
space_merge($space, 'field_home');

$uids = '';


if ($_GET['view'] == 'all') {

	$theurl = "userapp.php?view=all";
	$f_index = '';

} else {

	if(empty($space['feedfriend'])) $_GET['view'] = 'me';

	if($_GET['view'] == 'me') {
		$uids = array($space['uid']);
		$theurl = "userapp.php?view=me";
		$f_index = '';

	} else {
		$uids = array_merge(explode(',', $space['feedfriend']), array(0));
		$theurl = "userapp.php?view=we";
		$f_index = 'dateline';
		$_GET['view'] = 'we';
		$_G['home_tpl_hidden_time'] = 1;
	}
}

$icon = empty($_GET['icon'])?'':trim($_GET['icon']);

$multi = '';

$feed_list = $appfeed_list = $hiddenfeed_list = $filter_list = $hiddenfeed_num = $icon_num = array();
$count = $filtercount = 0;
$query = C::t('home_feed')->fetch_all_by_search(1, $uids, $icon, '', '', '', '', '', $start, $perpage, $f_index);
foreach ($query as $value) {
	$feed_list[$value['icon']][] = $value;
	$count++;
}
$multi = simplepage($count, $perpage, $page, $theurl);
require_once libfile('function/feed');

$list = $filter_list = array();
foreach ($feed_list as $key => $values) {
	$nowcount = 0;
	foreach ($values as $value) {
		$value = mkfeed($value);
		$nowcount++;
		if($nowcount>5 && empty($icon)) {
			break;
		}
		$list[$key][] = $value;
	}
}


getuserapp();
$my_userapp = array();

if($_G['uid']) {
	if(is_array($_G['cache']['userapp'])) {
		foreach($_G['cache']['userapp'] as $value) {
			$my_userapp[$value['appid']] = $value;
		}
	}

	if(is_array($_G['my_userapp'])) {
		foreach($_G['my_userapp'] as $value) {
			$my_userapp[$value['appid']] = $value;
		}
	}
}

$actives = array((in_array($_GET['view'], array('we', 'me', 'all', 'hot', 'top')) ? $_GET['view'] : 'top') => ' class="a"');
if($_GET['view'] != 'top') {
	$navtitle = lang('core', 'title_userapp_index_'.$_GET['view']).' - '.$navtitle;
}

$metakeywords = $_G['setting']['seokeywords']['userapp'];
if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][5]['navname'];
}

$metadescription = $_G['setting']['seodescription']['userapp'];
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][5]['navname'];
}

include_once template("userapp/userapp_index");
?>