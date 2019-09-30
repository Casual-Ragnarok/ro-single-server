<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api.php 23508 2011-07-21 06:34:40Z cnteacher $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

$modarray = array('js' => 'javascript/javascript', 'ad' => 'javascript/advertisement');

$mod = !empty($_GET['mod']) ? $_GET['mod'] : '';
if(empty($mod) || !array_key_exists($mod, $modarray)) {
	exit('Access Denied');
}

require_once './api/'.$modarray[$mod].'.php';

function loadcore() {
	global $_G;
	require_once './source/class/class_core.php';

	$discuz = C::app();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();
}

?>