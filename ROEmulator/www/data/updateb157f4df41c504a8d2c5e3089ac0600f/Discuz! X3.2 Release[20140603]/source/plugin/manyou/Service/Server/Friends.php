<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Friends.php 25693 2011-11-18 02:21:03Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Friends extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onFriendsGet($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		$result = array();
		if ($uIds) {
			foreach($uIds as $uId) {
				$result[$uId] = $this->_getFriends($uId, $friendNum);
			}
		}
		return $result;
	}

	public function onFriendsAreFriends($uId1, $uId2) {
		$friend = C::t('home_friend')->fetch_all_by_uid_fuid($uId1, $uId2);
		$result = false;
		if($friend = $friend[0]) {
			$result = true;
		}

		return $result;
	}

}