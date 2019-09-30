<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: get.inc.php 33997 2013-09-17 06:46:37Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$data = captcha::get($_GET['refresh'], $_GET['modid']);
preg_match('/verifysession=(\w+);/', $GLOBALS['filesockheader'], $r);
dsetcookie('dcaptchasig', $r[1]);

dheader('Content-Disposition: inline');
dheader('Content-Type: image/pjpeg');
echo $data;

?>