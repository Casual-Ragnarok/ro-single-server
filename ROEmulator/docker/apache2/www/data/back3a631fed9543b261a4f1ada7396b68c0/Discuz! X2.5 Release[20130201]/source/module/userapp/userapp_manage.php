<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_manage.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!checkperm('allowmyop')) {
	showmessage('no_privilege_myop', '', array(), array('return' => true));
}

$uchUrl = getsiteurl().'userapp.php?mod=manage';

if(submitcheck('ordersubmit')) {
	if(empty($_POST['order'])) $_POST['order'] = array();
	$displayorder = count($_POST['order']);

	foreach($_POST['order'] as $key => $appid) {
		$appid = intval($appid);
		if($_G['my_userapp'][$appid]['menuorder'] != $displayorder) {
			C::t('home_userapp')->update_by_uid_appid($_G['uid'], $appid, array('menuorder'=>$displayorder));
		}
		$displayorder--;
	}

	$_POST['menunum'] = abs(intval($_POST['menunum']));
	if($_POST['menunum']) {
		C::t('common_member_field_home')->update($_G['uid'], array('menunum' => $_POST['menunum']));
	}

	showmessage('do_success', 'userapp.php?mod=manage&ac=menu');
}

$my_prefix = 'http://uchome.manyou.com';
if(empty($_GET['my_suffix'])) {
	$appId = intval($_GET['appid']);
	if($appId) {
		$mode = $_GET['mode'];
		if($mode == 'about') {
			$my_suffix = '/userapp/about?appId='.$appId;
		} else {
			$my_suffix = '/userapp/privacy?appId='.$appId;
		}
	} else {
		$my_suffix = $_GET['ac'] == 'menu' ? '/userapp/list' : '/app/list';
	}
} else {
	$my_suffix = $_GET['my_suffix'];
}
$my_extra = isset($_GET['my_extra']) ? $_GET['my_extra'] : '';

$delimiter = strrpos($my_suffix, '?') ? '&' : '?';
$myUrl = $my_prefix.urldecode($my_suffix.$delimiter.'my_extra='.$my_extra);



$my_userapp = $my_default_userapp = array();

if($_GET['ac'] == 'menu' && $my_suffix == '/userapp/list') {
	$_GET['op'] = 'menu';
	$max_order = 0;
	if(is_array($_G['cache']['userapp'])) {
		foreach($_G['cache']['userapp'] as $value) {
			if(isset($_G['my_userapp'][$value['appid']])) {
				$my_default_userapp[$value['appid']] = $value;
				unset($_G['my_userapp'][$value['appid']]);
			}
		}
	}
	if(is_array($_G['my_userapp'])) {
		foreach($_G['my_userapp'] as $value) {
			$my_userapp[$value['appid']] = $value;
			if($value['displayorder']>$max_order) $max_order = $value['displayorder'];
		}
	}
	$userapps = C::t('home_userapp')->fetch_all_by_uid_appid($_G['uid'], 0, 'menuorder');
	foreach($userapps as $value) {
		if($value['allowsidenav'] != 0) {
			continue;
		}
		if(!isset($my_userapp[$value['appid']]) && !isset($my_default_userapp[$value['appid']]) && $value['flag'] != -1) {
			if($value['flag'] == 1) {
				$my_default_userapp[$value['appid']] = $value;
			} else {
				$my_userapp[$value['appid']] = $value;
			}
		}
	}
}

$hash = $_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$_G['timestamp'];
$hash = md5($hash);
$delimiter = strrpos($myUrl, '?') ? '&' : '?';

$url = $myUrl.$delimiter.'s_id='.$_G['setting']['my_siteid'].'&uch_id='.$_G['uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.urlencode($my_suffix).'&timestamp='.$_G['timestamp'].'&my_sign='.$hash;

$actives = array('view'=> ' class="active"');
$menunum[$_G['member']['menunum']] = ' selected ';

$navtitle = lang('core', 'title_userapp_manage', array('userapp' => $_G['setting']['navs'][5]['navname'])).' - '.$navtitle;

$metakeywords = $_G['setting']['seokeywords']['userapp'];
if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][5]['navname'];
}

$metadescription = $_G['setting']['seodescription']['userapp'];
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][5]['navname'];
}

include_once template("userapp/userapp_manage");

?>