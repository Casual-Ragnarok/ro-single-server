<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_topic.php 27332 2012-01-16 09:24:24Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['diy']=='yes' && !$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;

if($topicid) {
	$topic = C::t('portal_topic')->fetch($topicid);
} elseif($_GET['topic']) {
	$topic = C::t('portal_topic')->fetch_by_name($_GET['topic']);
}

if(empty($topic)) {
	showmessage('topic_not_exist');
}

if($topic['closed'] && !$_G['group']['allowmanagetopic'] && !($topic['uid'] == $_G['uid'] && $_G['group']['allowaddtopic'])) {
	showmessage('topic_is_closed');
}

if($_GET['diy'] == 'yes' && $topic['uid'] != $_G['uid'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = intval($topic['topicid']);

C::t('portal_topic')->increase($topicid, array('viewnum' => 1));

$navtitle = $topic['title'];
$metadescription = empty($topic['summary']) ? $topic['title'] : $topic['summary'];
$metakeywords =  empty($topic['keyword']) ? $topic['title'] : $topic['keyword'];

$attachtags = $aimgs = array();

$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;

$file = 'portal/portal_topic_content:'.$topicid;
$tpldirectory = '';
$primaltplname = $topic['primaltplname'];
if(strpos($primaltplname, ':') !== false) {
	list($tpldirectory, $primaltplname) = explode(':', $primaltplname);
}
include template('diy:'.$file, NULL, $tpldirectory, NULL, $primaltplname);

function portaltopicgetcomment($topcid, $limit = 20, $start = 0) {
	global $_G;
	$topcid = intval($topcid);
	$limit = intval($limit);
	$start = intval($start);
	$data = array();
	if($topcid) {
		$query = C::t('portal_comment')->fetch_all_by_id_idtype($topcid, 'topicid', 'dateline', 'DESC', $start, $limit);
		foreach($query as $value) {
			if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
				$data[$value['cid']] = $value;
			}
		}
	}
	return $data;
}
?>