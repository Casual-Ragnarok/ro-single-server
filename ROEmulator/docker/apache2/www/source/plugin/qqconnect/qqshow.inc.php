<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: qqshow.inc.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$connectService = Cloud::loadClass('Service_Connect');
$connectService->connectMergeMember();

if($_G['member']['conisbind']) {
	$qqshow = '<img src="http://open.show.qq.com/cgi-bin/qs_open_snapshot?appid='.$_G['setting']['connectappid'].'&openid='.$_G['member']['conopenid'].'" />';
	if(submitcheck('connectsubmit')) {
		C::t('#qqconnect#common_member_connect')->update($_G['uid'], array('conisqqshow' => $_GET['do'] == 'open' ? 1 : 0));
		$message = $_GET['do'] == 'open' ? lang('plugin/qqconnect', 'connect_qqshow_open') : lang('plugin/qqconnect', 'connect_qqshow_close');
		include template('common/header_ajax');
		echo '<script type="text/javascript">showDialog(\''.$message.'\', \'right\', null, function(){location.href=\'home.php?mod=spacecp&ac=plugin&id=qqconnect:qqshow\'}, 0, null, \'\', \'\', \'\', 0, 3);</script>';
		include template('common/footer_ajax');
		exit;
	}
} else {
	$_G['connect']['loginbind_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&type=loginbind&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');
}