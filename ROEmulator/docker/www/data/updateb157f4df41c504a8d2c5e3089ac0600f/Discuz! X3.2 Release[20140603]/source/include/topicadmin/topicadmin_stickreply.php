<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_stickreply.php 30872 2012-06-27 10:11:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowstickreply'] && !$specialperm) {
	showmessage('no_privilege_stickreply');
}

$topiclist = $_GET['topiclist'];
$modpostsnum = count($topiclist);
if(empty($topiclist)) {
	showmessage('admin_stickreply_invalid');
} elseif(!$_G['tid']) {
	showmessage('admin_nopermission', NULL);
}
$sticktopiclist = $posts = array();
foreach($topiclist as $pid) {
	$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid, false);
	$sticktopiclist[$pid] = $post['position'];
}

if(!submitcheck('modsubmit')) {

	$stickpid = '';
	foreach($sticktopiclist as $id => $postnum) {
		$stickpid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
	}

	include template('forum/topicadmin_action');

} else {

	if($_GET['stickreply']) {
		foreach($sticktopiclist as $pid => $postnum) {
			C::t('forum_poststick')->insert(array(
				'tid' => $_G['tid'],
				'pid' => $pid,
				'position' => $postnum,
				'dateline' => $_G['timestamp'],
			), false, true);
		}
	} else {
		foreach($sticktopiclist as $pid => $postnum) {
			C::t('forum_poststick')->delete($_G['tid'], $pid);
		}
	}

	$sticknum = C::t('forum_poststick')->count_by_tid($_G['tid']);
	$stickreply = intval($_GET['stickreply']);

	if($sticknum == 0 || $stickreply == 1) {
		C::t('forum_thread')->update($_G['tid'], array('moderated'=>1, 'stickreply'=>$stickreply));
	}

	$modaction = $_GET['stickreply'] ? 'SRE' : 'USR';
	$reason = checkreasonpm();

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array(array('authorid' => $post['authorid'])), 'var' => 'post', 'notictype' => 'post', 'item' => $_GET['stickreply'] ? 'reason_stickreply': 'reason_stickdeletereply') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason),
	'modlog'	=> $thread
	);

}

?>