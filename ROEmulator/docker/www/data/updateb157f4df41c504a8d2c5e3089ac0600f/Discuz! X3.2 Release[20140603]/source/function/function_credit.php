<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_credit.php 31380 2012-08-21 07:25:54Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function _checklowerlimit($action, $uid = 0, $coef = 1, $fid = 0, $returnonly = 0) {
	global $_G;

	include_once libfile('class/credit');
	$credit = & credit::instance();
	$limit = $credit->lowerlimit($action, $uid, $coef, $fid);
	if($returnonly) return $limit;
	if($limit !== true) {
		$GLOBALS['id'] = $limit;
		$lowerlimit = is_array($action) && $action['extcredits'.$limit] ? abs($action['extcredits'.$limit]) + $_G['setting']['creditspolicy']['lowerlimit'][$limit] : $_G['setting']['creditspolicy']['lowerlimit'][$limit];
		$rulecredit = array();
		if(!is_array($action)) {
			$rule = $credit->getrule($action, $fid);
			foreach($_G['setting']['extcredits'] as $extcreditid => $extcredit) {
				if($rule['extcredits'.$extcreditid]) {
					$rulecredit[] = $extcredit['title'].($rule['extcredits'.$extcreditid] > 0 ? '+'.$rule['extcredits'.$extcreditid] : $rule['extcredits'.$extcreditid]);
				}
			}
		} else {
			$rule = array();
		}
		$values = array(
			'title' => $_G['setting']['extcredits'][$limit]['title'],
			'lowerlimit' => $lowerlimit,
			'unit' => $_G['setting']['extcredits'][$limit]['unit'],
			'ruletext' => $rule['rulename'],
			'rulecredit' => implode(', ', $rulecredit)
		);
		if(!is_array($action)) {
			if(!$fid) {
				showmessage('credits_policy_lowerlimit', '', $values);
			} else {
				showmessage('credits_policy_lowerlimit_fid', '', $values);
			}
		} else {
			showmessage('credits_policy_lowerlimit_norule', '', $values);
		}
	}
}


function _updatemembercount($uids, $dataarr = array(), $checkgroup = true, $operation = '', $relatedid = 0, $ruletxt = '', $customtitle = '', $custommemo = '') {
	if(empty($uids)) return;
	if(!is_array($dataarr) || empty($dataarr)) return;
	if($operation && $relatedid || $customtitle) {
		$writelog = true;
	} else {
		$writelog = false;
	}
	$data = $log = array();
	foreach($dataarr as $key => $val) {
		if(empty($val)) continue;
		$val = intval($val);
		$id = intval($key);
		$id = !$id && substr($key, 0, -1) == 'extcredits' ? intval(substr($key, -1, 1)) : $id;
		if(0 < $id && $id < 9) {
			$data['extcredits'.$id] = $val;
			if($writelog) {
				$log['extcredits'.$id] = $val;
			}
		} else {
			$data[$key] = $val;
		}
	}
	if($writelog) {
		credit_log($uids, $operation, $relatedid, $log, $customtitle, $custommemo);
	}
	if($data) {
		include_once libfile('class/credit');
		$credit = & credit::instance();
		$credit->updatemembercount($data, $uids, $checkgroup, $ruletxt);
	}
}

function credit_log($uids, $operation, $relatedid, $data, $customtitle, $custommemo) {
	if((!$operation || empty($relatedid)) && !strlen($customtitle) || empty($uids) || empty($data)) {
		return;
	}
	$log = array(
		'uid' => $uids,
		'operation' => $operation,
		'relatedid' => $relatedid,
		'dateline' => TIMESTAMP,
	);
	foreach($data as $k => $v) {
		$log[$k] = $v;
	}
	if(is_array($uids)) {
		foreach($uids as $k => $uid) {
			$log['uid'] = $uid;
			$log['relatedid'] = is_array($relatedid) ? $relatedid[$k] : $relatedid;
			$insertid = C::t('common_credit_log')->insert($log, true);
			C::t('common_credit_log_field')->insert(array('logid' => $insertid, 'title' => $customtitle, 'text' => $custommemo));
		}
	} else {
		$insertid = C::t('common_credit_log')->insert($log, true);
		C::t('common_credit_log_field')->insert(array('logid' => $insertid, 'title' => $customtitle, 'text' => $custommemo));
	}
}
?>