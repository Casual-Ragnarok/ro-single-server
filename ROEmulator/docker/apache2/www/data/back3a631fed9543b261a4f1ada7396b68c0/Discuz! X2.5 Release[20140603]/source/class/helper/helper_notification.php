<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_notification.php 30479 2012-05-30 07:28:46Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_notification {


	public static function notification_add($touid, $type, $note, $notevars = array(), $system = 0) {
		global $_G;

		if(!($tospace = getuserbyuid($touid))) {
			return false;
		}
		space_merge($tospace, 'field_home');
		$filter = empty($tospace['privacy']['filter_note'])?array():array_keys($tospace['privacy']['filter_note']);

		if($filter && (in_array($type.'|0', $filter) || in_array($type.'|'.$_G['uid'], $filter))) {
			return false;
		}

		$notevars['actor'] = "<a href=\"home.php?mod=space&uid=$_G[uid]\">".$_G['member']['username']."</a>";
		if(!is_numeric($type)) {
			$vars = explode(':', $note);
			if(count($vars) == 2) {
				$notestring = lang('plugin/'.$vars[0], $vars[1], $notevars);
			} else {
				$notestring = lang('notification', $note, $notevars);
			}
			$frommyapp = false;
		} else {
			$frommyapp = true;
			$notestring = $note;
		}

		$oldnote = array();
		if($notevars['from_id'] && $notevars['from_idtype']) {
			$oldnote = C::t('home_notification')->fetch_by_fromid_uid($notevars['from_id'], $notevars['from_idtype'], $touid);
		}
		if(empty($oldnote['from_num'])) $oldnote['from_num'] = 0;
		$notevars['from_num'] = $notevars['from_num'] ? $notevars['from_num'] : 1;
		$setarr = array(
			'uid' => $touid,
			'type' => $type,
			'new' => 1,
			'authorid' => $_G['uid'],
			'author' => $_G['username'],
			'note' => $notestring,
			'dateline' => $_G['timestamp'],
			'from_id' => $notevars['from_id'],
			'from_idtype' => $notevars['from_idtype'],
			'from_num' => ($oldnote['from_num']+$notevars['from_num'])
		);
		if($system) {
			$setarr['authorid'] = 0;
			$setarr['author'] = '';
		}
		$pkId = 0;
		if($oldnote['id']) {
			C::t('home_notification')->update($oldnote['id'], $setarr);
			$pkId = $oldnote['id'];
		} else {
			$oldnote['new'] = 0;
			$pkId = C::t('home_notification')->insert($setarr, true);
		}
		$banType = array('task');
		if($_G['setting']['cloud_status'] && !in_array($type, $banType)) {
			$noticeService = Cloud::loadClass('Service_Client_Notification');
			if($oldnote['id']) {
				$noticeService->update($touid, $pkId, $setarr['from_num'], $setarr['dateline']);
			} else {
				$extra = $type == 'post' ? array('pId' => $notevars['pid']) : array();
				$noticeService->add($touid, $pkId, $type, $setarr['authorid'], $setarr['author'], $setarr['from_id'], $setarr['from_idtype'], $setarr['note'], $setarr['from_num'], $setarr['dateline'], $extra);
			}
		}

		if(empty($oldnote['new'])) {
			C::t('common_member')->increase($touid, array('newprompt' => 1));

			require_once libfile('function/mail');
			$mail_subject = lang('notification', 'mail_to_user');
			sendmail_touser($touid, $mail_subject, $notestring, $frommyapp ? 'myapp' : $type);
		}

		if(!$system && $_G['uid'] && $touid != $_G['uid']) {
			C::t('home_friend')->update_num_by_uid_fuid(1, $_G['uid'], $touid);
		}
	}

	public static function manage_addnotify($type, $from_num = 0, $langvar = array()) {
		global $_G;
		$notifyusers = dunserialize($_G['setting']['notifyusers']);
		$notifytypes = explode(',', $_G['setting']['adminnotifytypes']);
		$notifytypes = array_flip($notifytypes);
		$notearr = array('from_id' => 1,'from_idtype' => $type, 'from_num' => $from_num);
		if($langvar) {
			$langkey = $langvar['langkey'];
			$notearr = array_merge($notearr, $langvar);
		} else {
			$langkey = 'manage_'.$type;
		}
		foreach($notifyusers as $uid => $user) {
			if($user['types'][$notifytypes[$type]]) {
				helper_notification::notification_add($uid, $type, $langkey, $notearr, 1);
			}
		}
	}
}

?>