<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewcomment.php 35044 2014-10-30 05:32:05Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}



$_GET['mod'] = 'misc';
$_GET['action'] = 'commentmore';
$_GET['inajax'] = 1;
include_once 'forum.php';

class mobile_api {

	function common() {

	}

	function output() {
        $comments = mobile_core::getvalues($GLOBALS['comments'], array('/^\d+$/'), array('id', 'tid', 'pid', 'author', 'authorid', 'dateline', 'comment', 'avatar'));
        foreach($GLOBALS['comments'] as $k => $c) {
            $comments[$k]['avatar'] = avatar($c['authorid'], 'small', true);
        }
        $variables = array(
            'tid' => $_GET['tid'],
            'pid' => $_GET['pid'],
            'comments' => array($_GET['pid'] => $comments),
            'totalcomment' => $GLOBALS['totalcomment'],
            'count' => $GLOBALS['count'],
        );
		mobile_core::result(mobile_core::variable($variables));
	}
}

?>