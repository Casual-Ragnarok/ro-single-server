<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Notifications.php 25828 2011-11-23 10:50:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Notifications extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onNotificationsSend($uId, $recipientIds, $appId, $notification) {
		$this->getUserSpace($uId);

		$result = array();

		foreach($recipientIds as $recipientId) {
			$val = intval($recipientId);
			if($val) {
				if ($uId) {
					$result[$val] = notification_add($val, $appId, $notification) === null;
				} else {
					$result[$val] = notification_add($val, $appId, $notification, array(), 1) === null;
				}
			} else {
				$result[$recipientId] = null;
			}
		}
		return $result;
	}

	public function onNotificationsGet($uId) {
		$notify = $result = array();
		$result = array(
			'message' => array(
				'unread' => 0,
				'mostRecent' => 0
			),
			'notification' => array(
				'unread' => 0 ,
				'mostRecent' => 0
			),
			'friendRequest' => array(
				'uIds' => array()
			)
		);

		$i = 0;
		foreach(C::t('home_notification')->fetch_all_by_uid($uId, 1) as $value) {
			$i++;
			if(!$result['notification']['mostRecent']) $result['notification']['mostRecent'] = $value['dateline'];
		}
		$result['notification']['unread'] = $i;

		loaducenter();
		$pmarr = uc_pm_list($uId, 1, 1, 'newbox', 'newpm');
		if($pmarr['count']) {
			$result['message']['unread'] = $pmarr['count'];
			$result['message']['mostRecent'] = $pmarr['data'][0]['dateline'];
		}

		$fIds = array();
		foreach(C::t('home_friend_request')->fetch_all_by_uid($uId) as $value) {
			if(!$result['friendRequest']['mostRecent']) {
				$result['friendRequest']['mostRecent'] = $value['dateline'];
			}
			$fIds[] = $value['uid'];
		}
		$result['friendRequest']['uIds'] = $fIds;

		return $result;
	}

}