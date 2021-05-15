<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp.php 33017 2013-04-08 07:28:47Z zhengqingpeng $
 */

define('APPTYPEID', 5);
define('CURSCRIPT', 'userapp');

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = C::app();

$modarray = array('app', 'manage');
$cachelist = array('userapp','usergroups', 'myapp');

$mod = !in_array($discuz->var['mod'], $modarray) ? 'manage' : $discuz->var['mod'];

$appid = empty($_GET['id']) ? '': intval($_GET['id']);
if($appid) {
	$mod = 'app';
}

$discuz->cachelist = $cachelist;
$discuz->init();

if(empty($_G['uid']) && $mod == 'app') {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode('userapp.php?mod=app&id='.$appid));
	}
	showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
}

if(empty($_G['setting']['my_app_status'])) {
	showmessage('no_privilege_my_app_status', '', array(), array('return' => true));
}

if($mod == 'app' && !checkperm('allowmyop')) {
	showmessage('no_privilege_myop', '', array(), array('return' => true));
}
$space = $_G['uid']? getuserbyuid($_G['uid']) : array();

define('CURMODULE', 'userapp');
runhooks();

getuserapp();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['userapp']);
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][5]['navname'];
} else {
	$nobbname = true;
}

require_once libfile('userapp/'.$mod, 'module');

?>