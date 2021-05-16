<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_setting.inc.php 33704 2013-08-06 06:45:48Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

dheader('location: '.ADMINSCRIPT.'?action=cloud&operation=manyou&anchor='.$_GET['ac']);

?>