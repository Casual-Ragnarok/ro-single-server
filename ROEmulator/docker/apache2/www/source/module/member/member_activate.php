<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_activate.php 25756 2011-11-22 02:47:45Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if($_GET['uid'] && $_GET['id']) {

	$member = getuserbyuid($_GET['uid']);
	if($member && $member['groupid'] == 8) {
		$member = array_merge(C::t('common_member_field_forum')->fetch($member['uid']), $member);
	} else {
		showmessage('activate_illegal', 'index.php');
	}
	list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

	if($operation == 2 && $idstring == $_GET['id']) {
		$newgroup = C::t('common_usergroup')->fetch_by_credits($member['credits']);
		C::t('common_member')->update($member['uid'], array('groupid' => $newgroup['groupid'], 'emailstatus' => '1'));
		C::t('common_member_field_forum')->update($member['uid'], array('authstr' => ''));
		showmessage('activate_succeed', 'index.php', array('username' => $member['username']));
	} else {
		showmessage('activate_illegal', 'index.php');
	}

}
?>