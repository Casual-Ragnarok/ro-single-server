<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.php 33953 2013-09-05 03:17:10Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['my_siteid'] || !$_G['setting']['my_sitekey']) {
	cpmsg('cloudcaptcha:switch_error', '', 'error');
}