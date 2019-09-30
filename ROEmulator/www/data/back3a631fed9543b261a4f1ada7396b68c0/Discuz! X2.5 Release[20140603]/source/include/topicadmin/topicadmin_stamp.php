<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_stamp.php 25289 2011-11-03 10:06:19Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowstampthread']) {
	showmessage('no_privilege_stampthread');
}

loadcache('stamps');

if(!submitcheck('modsubmit')) {

	include template('forum/topicadmin_action');

} else {

	$modaction = $_GET['stamp'] !== '' ? 'SPA' : 'SPD';
	$_GET['stamp'] = $_GET['stamp'] !== '' ? $_GET['stamp'] : -1;
	$reason = checkreasonpm();

	C::t('forum_thread')->update($_G['tid'], array('moderated'=>1, 'stamp'=>$_GET['stamp']));
	if($modaction == 'SPA' && $_G['cache']['stamps'][$_GET['stamp']]['icon']) {
		C::t('forum_thread')->update($_G['tid'], array('icon'=>$_G['cache']['stamps'][$_GET['stamp']]['icon']));
	} elseif($modaction == 'SPD' && $_G['cache']['stamps'][$thread['stamp']]['icon'] == $thread['icon']) {
		C::t('forum_thread')->update($_G['tid'], array('icon'=>-1));
	}

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => $_GET['stamp'] !== '' ? 'reason_stamp_update' : 'reason_stamp_delete') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason, 'stamp' => $_G['cache']['stamps'][$stamp]['text']),
	'modaction'	=> $modaction,
	'modlog'	=> $thread
	);
	$modpostsnum = 1;

	updatemodlog($_G['tid'], $modaction, 0, 0, '', $modaction == 'SPA' ? $_GET['stamp'] : 0);

}

?>