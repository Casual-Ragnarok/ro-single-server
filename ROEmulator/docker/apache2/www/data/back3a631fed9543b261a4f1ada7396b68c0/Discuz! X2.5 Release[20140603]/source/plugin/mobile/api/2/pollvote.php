<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: pollvote.php 31418 2012-08-27 08:47:01Z zhangjie $
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'votepoll';
include_once 'forum.php';

class mobile_api {
	function common() {

	}

	function output() {
		mobile_core::result(mobile_core::variable());
	}
}

?>