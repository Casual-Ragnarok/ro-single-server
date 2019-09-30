<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit.php 32023 2012-10-31 08:20:37Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['inajax'] && $_GET['showcredit']) {
	include template('common/extcredits');
	exit;
}

$perpage = 20;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1) * $perpage;
ckstart($start, $perpage);

checkusergroup();

$operation = in_array($_GET['op'], array('base', 'buy', 'transfer', 'exchange', 'log', 'rule')) ? trim($_GET['op']) : 'base';
$opactives = array($operation =>' class="a"');
if(in_array($operation, array('base', 'buy', 'transfer', 'exchange', 'rule'))) {
	$operation = 'base';
}
include_once libfile('spacecp/credit_'.$operation, 'include');


function makecreditlog($log, $otherinfo=array()) {
	global $_G;

	$log['dateline'] = dgmdate($log['dateline'], 'Y-m-d H:i');
	$log['optype'] = lang('spacecp', 'logs_credit_update_'.$log['operation']);
	$log['opinfo'] = '';
	$info = $url = '';
	switch($log['operation']) {
		case 'TRC':
			$log['opinfo'] = '<a href="home.php?mod=task&do=view&id='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'done').(!empty($otherinfo['tasks'][$log['relatedid']]) ? ' <strong>'.$otherinfo['tasks'][$log['relatedid']].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'task_credit').'</a>';
			break;
		case 'RTC':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('forum/template', 'published').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'special_3_credit').'</a>';
			break;
		case 'RAC':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'security_answer').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'special_3_best_answer').'</a>';
			break;
		case 'MRC':
			$log['opinfo'] = lang('spacecp', 'magic_credit');
			break;
		case 'BMC':
			$log['opinfo'] = '<a href="home.php?mod=magic&action=log&operation=buylog" target="_blank">'.lang('home/template', 'magics_operation_buy').' <strong>'.(!empty($_G['cache']['magics'][$log['relatedid']]['name']) ? $_G['cache']['magics'][$log['relatedid']]['name'] : '').'</strong> '.lang('home/template', 'magic').'</a>';
			break;
		case 'BME':
			$log['opinfo'] = '<a href="home.php?mod=medal" target="_blank">'.lang('spacecp', 'buy_medal').'</a>';
			break;
		case 'BGC':
			$log['opinfo'] = lang('spacecp','magic_space_gift');
			break;
		case 'RGC':
			$log['opinfo'] = lang('spacecp','magic_space_re_gift');
			break;
		case 'AGC':
			$log['opinfo'] = lang('spacecp', 'magic_space_get_gift');
			break;
		case 'TFR':
			$log['opinfo'] = '<a href="home.php?mod=space&uid='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'to').'<strong> '.$otherinfo['users'][$log['relatedid']].' </strong>'.lang('spacecp', 'credit_transfer').'</a>';
			break;
		case 'RCV':
			$log['opinfo'] = '<a href="home.php?mod=space&uid='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'comefrom').'<strong> '.$otherinfo['users'][$log['relatedid']].' </strong>'.lang('spacecp', 'credit_transfer_tips').'</a>';
			break;
		case 'CEC':
			$log['opinfo'] = lang('spacecp', 'credit_exchange_tips_1').'<strong>'.$_G['setting']['extcredits'][$log['minid']]['title'].'</strong> '.lang('spacecp', 'credit_exchange_to').' <strong>'.$_G['setting']['extcredits'][$log['maxid']]['title'].'</strong>';
			break;
		case 'ECU':
			$log['opinfo'] = lang('spacecp', 'credit_exchange_center');
			break;
		case 'SAC':
			$log['opinfo'] = '<a href="forum.php?mod=redirect&goto=findpost&ptid='.$otherinfo['attachs'][$log['relatedid']]['tid'].'&pid='.$otherinfo['attachs'][$log['relatedid']]['pid'].'" target="_blank">'.lang('spacecp', 'attach_sell').' <strong>'.$otherinfo['attachs'][$log['relatedid']]['filename'].'</strong> '.lang('spacecp', 'attach_sell_tips').'</a>';
			break;
		case 'BAC':
			$log['opinfo'] = '<a href="forum.php?mod=redirect&goto=findpost&ptid='.$otherinfo['attachs'][$log['relatedid']]['tid'].'&pid='.$otherinfo['attachs'][$log['relatedid']]['pid'].'" target="_blank">'.lang('spacecp', 'attach_buy').' <strong>'.$otherinfo['attachs'][$log['relatedid']]['filename'].'</strong> '.lang('spacecp', 'attach_buy_tips').'</a>';
			break;
		case 'PRC':
			$tid = $otherinfo['post'][$log['relatedid']];
			$log['opinfo'] = '<a href="forum.php?mod=redirect&goto=findpost&pid='.$log['relatedid'].'" target="_blank">'.(!empty($otherinfo['threads'][$tid]['subject']) ? ' <strong>'.$otherinfo['threads'][$tid]['subject'].'</strong> ' : lang('home/template', 'post')).lang('spacecp', 'grade_credit').'</a>';
			break;
		case 'RSC':
			$tid = $otherinfo['post'][$log['relatedid']];
			$log['opinfo'] = '<a href="forum.php?mod=redirect&goto=findpost&pid='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'credits_give').(!empty($otherinfo['threads'][$tid]['subject']) ? ' <strong>'.$otherinfo['threads'][$tid]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'grade_credit2').'</a>';
			break;
		case 'STC':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'attach_sell').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'thread_credit').'</a>';
			break;
		case 'BTC':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'attach_buy').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'thread_credit2').'</a>';
			break;
		case 'AFD':
			$log['opinfo'] = lang('spacecp', 'buy_credit');
			break;
		case 'UGP':
			$log['opinfo'] = lang('spacecp', 'buy_usergroup');
			break;
		case 'RPC':
			$log['opinfo'] = lang('spacecp', 'report_credit');
			break;
		case 'ACC':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'join').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'activity_credit').'</a>';
			break;
		case 'RCT':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'thread_send').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> ' : '').lang('spacecp', 'replycredit').'</a>';
			break;
		case 'RCA':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('home/template', 'reply').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> '.lang('home/template', 'eccredit_s') : '').lang('spacecp', 'add_credit').'</a>';
			break;
		case 'RCB':
			$log['opinfo'] = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'recovery').(!empty($otherinfo['threads'][$log['relatedid']]['subject']) ? ' <strong>'.$otherinfo['threads'][$log['relatedid']]['subject'].'</strong> ' : lang('spacecp', 'replycredit_post')).lang('spacecp', 'replycredit_thread').'</a>';
			break;
		case 'CDC':
			$log['opinfo'] = lang('spacecp', 'card_credit');
			break;
		case 'RKC':
			$log['opinfo'] = lang('spacecp', 'ranklist_top');
			break;
		case 'RPR':
			$log['opinfo'] = lang('spacecp', 'admincp_op_credit');
			break;
		case 'RPZ':
			$log['opinfo'] = lang('spacecp', 'admincp_op_credit');
			break;
		case 'FCP':
			$log['opinfo'] = '<a href="forum.php?mod=forumdisplay&fid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'buy_forum').'</a>';
			break;
		case 'BGR':
			$log['opinfo'] = '<a href="forum.php?mod=forumdisplay&fid='.$log['relatedid'].'" target="_blank">'.lang('spacecp', 'buildgroup').'</a>';
			break;

	}
	return $log;
}
function getotherinfo($aids, $pids, $tids, $taskids, $uids) {
	global $_G;

	$otherinfo = array('attachs' => array(), 'threads' => array(), 'tasks' => array(), 'users' => array());
	if(!empty($aids)) {
		$attachs = C::t('forum_attachment')->fetch_all($aids);
		foreach($attachs as $value) {
			$value['tableid'] = intval($value['tableid']);
			$attachtable[$value['tableid']][] = $value['aid'];
			$tids[$value['tid']] = $value['tid'];
		}
		foreach($attachtable as $id => $value) {
			$attachs = C::t('forum_attachment_n')->fetch_all($id, $value);
			foreach($attachs as $value) {
				$otherinfo['attachs'][$value['aid']] = $value;
			}
		}
	}
	if(!empty($pids)) {
		foreach(C::t('forum_post')->fetch_all(0, $pids) as $value) {
			$tids[$value['tid']] = $value['tid'];
			$otherinfo['post'][$value['pid']] = $value['tid'];
		}
	}
	if(!empty($tids)) {
		foreach(C::t('forum_thread')->fetch_all_by_tid($tids) as $value) {
			$otherinfo['threads'][$value['tid']] = $value;
		}
	}
	if(!empty($taskids)) {
		foreach(C::t('common_task')->fetch_all($taskids) as $value) {
			$otherinfo['tasks'][$value['taskid']] = $value['name'];
		}
	}
	if(!empty($uids)) {
		foreach(C::t('common_member')->fetch_all($uids) as $uid => $value) {
			$otherinfo['users'][$uid] = $value['username'];
		}
	}
	return $otherinfo;
}
?>