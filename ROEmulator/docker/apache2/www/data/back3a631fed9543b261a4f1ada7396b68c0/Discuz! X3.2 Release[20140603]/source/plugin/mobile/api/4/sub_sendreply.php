<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sub_sendreply.php 34424 2014-04-24 05:17:08Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $pid, $thread, $_G;

$newmessage = preg_replace('/<\/*.*?>|&nbsp;|\r\n|\[attachimg\].*?\[\/attachimg\]|\[quote\].*?\[\/quote\]|\[\/*.*?\]/ms', '', $GLOBALS['message']);
$newmessage = messagecutstr($newmessage, 100);

$key = C::t('#mobile#mobile_wsq_threadlist')->fetch($_G['tid']);
$posts = dunserialize($key['svalue']);

if (trim($newmessage) != '') {
	if (!$posts) {
		$posts = array();
	}
	if (count($posts) > 2) {
		array_shift($posts);
	}
	$post = array(
	    'pid' => $pid,
	    'author' => $_G['username'],
	    'authorid' => $_G['uid'],
	    'message' => $newmessage,
	    'avatar' => avatar($_G['uid'], 'small', true),
	);
	array_push($posts, $post);
}

if (count($posts) < 3 && ($thread['replies'] >= count($posts))) {
	$posts = array();
	foreach (C::t('forum_post')->fetch_all_by_tid($thread['posttableid'], $thread['tid'], true, 'DESC', 0, 10, 0, 0) as $p) {
		$p['message'] = preg_replace('/<\/*.*?>|&nbsp;|\r\n|\[attachimg\].*?\[\/attachimg\]|\[quote\].*?\[\/quote\]|\[\/*.*?\]/ms', '', $p['message']);
		$p['message'] = trim(messagecutstr($p['message'], 100));
		$post = array(
		    'pid' => $p['pid'],
		    'author' => $p['author'],
		    'authorid' => $p['authorid'],
		    'message' => $p['message'],
		    'avatar' => avatar($p['authorid'], 'small', true),
		);
		if ($post['message'] != '') {
			array_push($posts, $post);
		}
		if (count($posts) > 2) {
			break;
		}
	}
	$posts = array_reverse($posts);
}

$data = array(
	'skey' => $_G['tid'],
	'svalue' => serialize($posts)
);

C::t('#mobile#mobile_wsq_threadlist')->insert($_G['tid'], $data, false, true);

?>