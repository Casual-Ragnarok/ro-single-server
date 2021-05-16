<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewthread.php 32474 2013-01-24 07:38:35Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'viewthread';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G, $thread;
		if($GLOBALS['hiddenreplies']) {
			foreach($GLOBALS['postlist'] as $k => $post) {
				if(!$post['first'] && $_G['uid'] != $post['authorid'] && $_G['uid'] != $_G['forum_thread']['authorid'] && !$_G['forum']['ismoderator']) {
					$GLOBALS['postlist'][$k]['message'] = lang('plugin/mobile', 'mobile_post_author_visible');
					$GLOBALS['postlist'][$k]['attachments'] = array();
				}
			}
		}

		$variable = array(
			'thread' => mobile_core::getvalues($_G['thread'], array('tid', 'author', 'authorid', 'subject', 'views', 'replies', 'attachment', 'price', 'freemessage')),
			'fid' => $_G['fid'],
			'postlist' => array_values(mobile_core::getvalues($GLOBALS['postlist'], array('/^\d+$/'), array('pid', 'tid', 'author', 'first', 'dbdateline', 'dateline', 'username', 'adminid', 'memberstatus', 'authorid', 'username', 'groupid', 'memberstatus', 'status', 'message', 'number', 'memberstatus', 'groupid', 'attachment', 'attachments', 'attachlist', 'imagelist', 'anonymous'))),
			'ppp' => $_G['ppp'],
			'setting_rewriterule' => $_G['setting']['rewriterule'],
			'setting_rewritestatus' => $_G['setting']['rewritestatus'],
			'forum_threadpay' => $_G['forum_threadpay'],
			'cache_custominfo_postno' => $_G['cache']['custominfo']['postno'],
		);

		if(!empty($GLOBALS['threadsortshow'])) {
			$optionlist = array();
			foreach ($GLOBALS['threadsortshow']['optionlist'] AS $key => $val) {
				$val['optionid'] = $key;
				$optionlist[] = $val;
			}
			if(!empty($optionlist)) {
				$GLOBALS['threadsortshow']['optionlist'] = $optionlist;
				$GLOBALS['threadsortshow']['threadsortname'] = $_G['forum']['threadsorts']['types'][$thread['sortid']];
			}
		}
		$threadsortshow = mobile_core::getvalues($GLOBALS['threadsortshow'], array('/^(?!typetemplate).*$/'));
		if(!empty($threadsortshow)) {
			$variable['threadsortshow'] = $threadsortshow;
		}
		foreach($variable['$postlist'] as $k => $v) {
			$variable['$postlist'][$k]['attachments'] = array_values(mobile_core::getvalues($v['attachments'], array('/^\d+$/'), array('aid', 'tid', 'uid', 'dbdateline', 'dateline', 'filename', 'filesize', 'url', 'attachment', 'remote', 'description', 'readperm', 'price', 'width', 'thumb', 'picid', 'ext', 'imgalt', 'attachsize', 'payed', 'downloads')));
		}

		if(!empty($GLOBALS['polloptions'])) {
			$variable['special_poll']['polloptions'] = $GLOBALS['polloptions'];
			$variable['special_poll']['expirations'] = $GLOBALS['expirations'];
			$variable['special_poll']['multiple'] = $GLOBALS['multiple'];
			$variable['special_poll']['maxchoices'] = $GLOBALS['maxchoices'];
			$variable['special_poll']['voterscount'] = $GLOBALS['voterscount'];
			$variable['special_poll']['visiblepoll'] = $GLOBALS['visiblepoll'];
			$variable['special_poll']['allowvote'] = $_G['group']['allowvote'];
			$variable['special_poll']['remaintime'] = $thread['remaintime'];
		}

		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>