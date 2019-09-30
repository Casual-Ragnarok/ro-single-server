<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_app.php 34091 2013-10-09 04:04:17Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($appid == '1036584') {
} else {
	if(!checkperm('allowmyop')) {
		showmessage('no_privilege_myop', '', array(), array('return' => true));
	}
}

$app = array();
if($app = C::t('common_myapp')->fetch($appid)) {
	if($app['flag']<0) {
		showmessage('no_privilege_myapp');
	}
}

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}

$my_appId = $appid;
$my_suffix = htmlspecialchars(base64_decode($_GET['my_suffix']));

$my_prefix = getsiteurl();

updatecreditbyaction('useapp', 0, array(), $appid);

if (!$my_suffix) {
	dheader('Location: userapp.php?mod=app&id='.$my_appId.'&my_suffix='.urlencode(base64_encode('/')));
	exit;
}

if (preg_match('/^\//', $my_suffix)) {
	$url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
	if ($my_suffix) {
		$url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
	} else {
		$url = 'http://apps.manyou.com/'.$my_appId;
	}
}
if (strpos($my_suffix, '?')) {
	$url = $url.'&my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
} else {
	$url = $url.'?my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
}
$url .= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);
$current_url = getsiteurl().'userapp.php';
if ($_SERVER['QUERY_STRING']) {
	$current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}
$extra = $_GET['my_extra'];
$url .= '&my_current='.urlencode($current_url);
$url .= '&my_extra='.urlencode($extra);
$url .= '&my_ts='.$_G['timestamp'];
$url .= '&my_appVersion='.$app['version'];
$url .= '&my_fullscreen='.$isFullscreen;
$hash = $_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$appid.'|'.$current_url.'|'.$extra.'|'.$_G['timestamp'].'|'.$_G['setting']['my_sitekey'];
$hash = md5($hash);
$url .= '&my_sig='.$hash;
$my_sign = md5($_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$_G['timestamp']);
$url .= '&timestamp='. $_G['timestamp'] .'&my_sign='.$my_sign;
$my_suffix = urlencode($my_suffix);

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if ($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if ($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if ($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}

if($_G['uid'] && $appid && $appid != '1036584') {
	$usedArr = array();
	$usedInfo = explode('|', $_G['cookie']['usedapp']);
	if($usedInfo[0] == $_G['uid']) {
		$usedArr = !empty($usedInfo[1]) ? explode(',', $usedInfo[1]) : array();
		if(!in_array($appid, $usedArr)) {
			if(count($usedArr) >= 5) {
				unset($usedArr[0]);
			}
			$usedArr[] = $appid;
		}
	}
	dsetcookie('usedapp', $_G['uid'].'|'.implode(',', $usedArr), 31536000);
}


$navtitle = $app['appname'].' - '.$navtitle;

$metakeywords = $app['appname'].' '.$_G['setting']['seokeywords']['userapp'];
$metadescription = $app['appname'].' '.$_G['setting']['seodescription']['userapp'];
include_once template("userapp/userapp_app");
?>