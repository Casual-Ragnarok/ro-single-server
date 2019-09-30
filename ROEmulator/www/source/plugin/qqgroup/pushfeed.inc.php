<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: pushfeed.inc.php 29283 2012-03-31 09:35:36Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['adminid']) {
	return false;
}

$tid = intval($_GET['tid']);

$usergroupsfeedlist = unserialize($_G['setting']['qqgroup_usergroup_feed_list']);
if($_G['adminid'] == 3) {
	$thread = C::t('forum_thread')->fetch($tid);
	$ismoderator = C::t('forum_moderator')->fetch_uid_by_fid_uid($thread['fid'], $_G['uid']);
}

if (empty($usergroupsfeedlist) || !in_array($_G['groupid'], $usergroupsfeedlist) || $_G['adminid'] == 3 && !$ismoderator) {
	$util = Cloud::loadClass('Service_Util');
	if ($util->isfounder($_G['member']) == false) {
		return false;
	}
}

$service = Cloud::loadClass('Service_QQGroup');
$title = trim($_GET['title']);
$content = trim($_GET['content']);
if (!$tid) {
	showmessage('undefined_action');
}
$iframeUrl = $service->iframeUrl($tid, $title, $content);
include template('qqgroup:pushfeed');