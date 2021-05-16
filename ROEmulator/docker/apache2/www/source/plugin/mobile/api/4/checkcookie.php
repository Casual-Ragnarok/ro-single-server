<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: checkcookie.php 35044 2014-10-30 05:32:05Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

dsetcookie('testcookie', $_GET['siteid'], 600);

mobile_core::result(array());