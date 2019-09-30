<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: feed_callback.inc.php 29283 2012-03-31 09:35:36Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['adminid']) {
	return false;
}
$usergroupsfeedlist = unserialize($_G['setting']['qqgroup_usergroup_feed_list']);
if (empty($usergroupsfeedlist) || !in_array($_G['groupid'], $usergroupsfeedlist)) {
	$util = Cloud::loadClass('Service_Util');
	if ($util->isfounder($_G['member']) == false) {
		return false;
	}
}

$w = intval($_GET['w']) > 0 ? intval($_GET['w']) : 0;
$h = intval($_GET['h']) > 0 ? intval($_GET['h']) : 0;
$type = intval($_GET['type']) == 1 ? 1 : 2;

if ((!$w || !$h) && ($type != 1)) {
	showmessage('undefined_action');
}

include template('qqgroup:resize');