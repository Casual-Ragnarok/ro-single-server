<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_clearcookies.php 28247 2012-02-26 10:42:38Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(is_array($_COOKIE) && (empty($_G['uid']) || ($_G['uid'] && $formhash == formhash()))) {
	foreach($_G['cookie'] as $key => $val) {
		dsetcookie($key, '', -1, 0);
	}
	foreach($_COOKIE as $key => $val) {
		setcookie($key, '', -1, $_G['config']['cookie']['cookiepath'], '');
	}
}

showmessage('login_clearcookie', dreferer(), array(), $_G['inajax'] ? array('msgtype' => 3, 'showmsg' => true) : array());

?>