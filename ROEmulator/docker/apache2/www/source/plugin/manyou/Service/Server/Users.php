<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Users.php 25756 2011-11-22 02:47:45Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Users extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onUsersGetInfo($uIds, $fields = array(), $isExtra = false) {
		$users = $this->getUsers($uIds, false, true, $isExtra, false);
		$result = array();
		if ($users) {
			if ($fields) {
				foreach($users as $key => $user) {
					foreach($user as $k => $v) {
						if (in_array($k, $fields)) {
							$result[$key][$k] = $v;
						}
					}
				}
			}
		}

		if (!$result) {
			$result = $users;
		}

		return $result;
	}

	public function onUsersGetFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		$users = $this->getUsers(array($uId), false, true, $isExtra, true, $num, false, true);

		$totalNum = C::t('home_friend')->count_by_uid($uId);
		$friends = $users[0]['friends'];
		unset($users[0]['friends']);
		$result = array('totalNum' => $totalNum,
				'friends' => $friends,
				'me' => $users[0],
				);
		return $result;
	}

	public function onUsersGetExtraInfo($uIds) {
		$result = $this->getExtraByUsers($uIds);
		return $result;
	}

	public function onUsersGetFormHash($uId, $userAgent) {
		global $_G;
		$uId = intval($uId);
		if (!$uId) {
			return false;
		}

		$member = getuserbyuid($uId, 1);
		$_G['username'] = $member['username'];
		$_G['uid'] = $member['uid'];
		$_G['authkey'] = md5($_G['config']['security']['authkey'] . $userAgent);
		return formhash();
	}

}