<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_smilies.php 25510 2011-11-14 02:22:26Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$_GET['anchor'] = in_array($_GET['anchor'], array('base')) ? $_GET['anchor'] : 'base';

shownav('navcloud', 'cloud_storage');

showsubmenu('cloud_storage');
showtips('cloud_storage_tips');