<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: qqgroup.class.php 30904 2012-06-29 07:50:16Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_qqgroup {

	protected static $util;
	protected static $siteId;
	protected static $siteKey;

	public function plugin_qqgroup() {
		global $_G;
		self::$siteId = $_G['setting']['my_siteid'];
		self::$siteKey = $_G['setting']['my_sitekey'];
		self::$util = Cloud::loadClass('Service_Util');
	}

	public function viewthread_modoption() {
		global $_G;

		if (!$_G['adminid']) {
			return false;
		}
		$usergroupsfeedlist = unserialize($_G['setting']['qqgroup_usergroup_feed_list']);

		if (empty($usergroupsfeedlist) || !in_array($_G['groupid'], $usergroupsfeedlist)) {
			if (self::$util->isfounder($_G['member']) == false) {
				return false;
			}
		}

		$tid = $_G['tid'];
		$title = urlencode(trim($_G['forum_thread']['subject']));
		$post = C::t('forum_post')->fetch_all_by_tid_position($_G['fotum_thread']['posttableid'], $_G['tid'], 1);
		include_once libfile('function/discuzcode');
		$content = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", '', trim($post[0]['message']));
		$content = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", '', $content);
		$content = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", '', $content);
		$content = preg_replace("/\[hide[=]?(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", '', $content);
		$content = strip_tags(discuzcode($content, 0, 0, 0));
		$content = preg_replace('%\[attach\].*\[/attach\]%im', '', $content);
		$content = str_replace('&nbsp;', ' ', $content);
		$content = urlencode(cutstr($content, 50, ''));
		include template('qqgroup:push');

		return trim($return);
	}
}

class plugin_qqgroup_forum extends plugin_qqgroup {}