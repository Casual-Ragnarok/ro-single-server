<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Message.php 31448 2012-08-28 09:04:57Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Message extends Cloud_Service_Client_Restful {

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		return parent::__construct($debug);
	}

	public function add($siteUids, $authorId, $author, $dateline) {
		$toUids = array();
		if($siteUids) {
			$users = C::t('#qqconnect#common_member')->fetch_all((array)$siteUids);
			$connectUsers = C::t('#qqconnect#common_member_connect')->fetch_all((array)$siteUids);
			$i = 1;
			foreach ($users as $uid => $user) {
				$conopenid = $connectUsers[$uid]['conopenid'];
				if (!$conopenid) {
					$conopenid = 'n' . $i ++;
				}
				$toUids[$conopenid] = $user['uid'];
			}

			$_params = array(
					'openidData' => $toUids,
					'authorId' => $authorId,
					'author' => $author,
					'dateline' => $dateline,
					'deviceToken' => $this->getUserDeviceToken($siteUids),
					'extra' => array(
							'isAdminGroup' => getglobal('adminid'),
							'groupId' => getglobal('groupid'),
							'groupName' => getglobal('group/grouptitle')
						)
				);
			return $this->_callMethod('connect.discuz.message.add', $_params);
		}
		return false;
	}
	public function setMsgFlag($siteUid, $dateline) {
		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'dateline' => $dateline
			);
		return $this->_callMethod('connect.discuz.message.read', $_params);
	}

	protected function _callMethod($method, $args) {
		try {
			return parent::_callMethod($method, $args);
		} catch (Exception $e) {

		}
	}
}