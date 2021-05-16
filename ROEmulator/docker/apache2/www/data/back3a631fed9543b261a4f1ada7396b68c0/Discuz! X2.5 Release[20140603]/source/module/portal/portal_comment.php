<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_comment.php 26746 2011-12-22 07:17:27Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
$idtype = in_array($_GET['idtype'], array('aid', 'topicid')) ? $_GET['idtype'] : 'aid';
$url = '';
if(empty($id)) {
	showmessage('comment_no_'.$idtype.'_id');
}
if($idtype == 'aid') {
	$csubject = C::t('portal_article_title')->fetch($id);
	if($csubject) {
		$csubject = array_merge($csubject, C::t('portal_article_count')->fetch($id));
	}
	$url = 'portal.php?mod=view&aid='.$id;
} elseif($idtype == 'topicid') {
	$csubject = C::t('portal_topic')->fetch($id);
	$url = 'portal.php?mod=topic&topicid='.$id;
}
if(empty($csubject)) {
	showmessage('comment_'.$idtype.'_no_exist');
} elseif(empty($csubject['allowcomment'])) {
	showmessage($idtype.'_comment_is_forbidden');
}

$perpage = 25;
$page = intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

$commentlist = array();
$multi = '';

if($csubject['commentnum']) {
	$pricount = 0;
	$query = C::t('portal_comment')->fetch_all_by_id_idtype($id, $idtype, 'dateline', 'DESC', $start, $perpage);
	foreach($query as $value) {
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$commentlist[] = $value;
		} else {
			$pricount ++;
		}
	}
}

$multi = multi($csubject['commentnum'], $perpage, $page, "portal.php?mod=comment&id=$id&idtype=$idtype");
$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;
include_once template("diy:portal/comment");

?>