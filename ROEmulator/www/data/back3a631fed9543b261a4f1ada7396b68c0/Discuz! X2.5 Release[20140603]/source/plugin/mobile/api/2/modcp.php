<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: modcp.php 31964 2012-10-26 07:27:36Z zhangjie $
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'modcp';
include_once 'forum.php';

class mobile_api {
	function common() {

	}

	function output() {
		mobile_core::result(mobile_core::variable());
	}
}
?>