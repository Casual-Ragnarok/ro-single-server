<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Notification.php 34003 2013-09-18 04:31:14Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Notification extends Cloud_Service_Client_Restful {

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
	public function add($siteUid, $pkId, $type, $authorId, $author, $fromId, $fromIdType, $note, $fromNum, $dateline, $extra = array()) {

		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkId' => $pkId,
				'type' => $type,
				'authorId' => $authorId,
				'author' => $author,
				'fromId' => $fromId,
				'fromIdType' => $fromIdType,
				'fromNum' => $fromNum,
				'content' => $note,
				'dateline' => $dateline,
				'deviceToken' => $this->getUserDeviceToken($siteUid),
				'extra' => array(
							'isAdminGroup' => getglobal('adminid'),
							'groupId' => getglobal('groupid'),
							'groupName' => getglobal('group/grouptitle')
						)
			);
		if($extra) {
			foreach($extra as $key => $value) {
				$_params['extra'][$key] = $value;
			}
		}
		return $this->_callMethod('connect.discuz.notification.add', $_params);
	}

	public function update($siteUid, $pkId, $fromNum, $dateline, $notekey) {
		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkId' => $pkId,
				'fromNum' => $fromNum,
				'dateline' => $dateline,
				'notekey' => $notekey,
			);
		return $this->_callMethod('connect.discuz.notification.update', $_params);
	}

	public function setNoticeFlag($siteUid, $dateline) {

		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'dateline' => $dateline
			);
		return $this->_callMethod('connect.discuz.notification.read', $_params);
	}

	public function addSiteMasterUserNotify($siteUids, $subject, $content, $authorId, $author, $fromType, $dateline) {
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
					'subject' => $subject,
					'content' => $content,
					'authorId' => $authorId,
					'author' => $author,
					'fromType' => $fromType == 1 ? 1 : 2,
					'dateline' => $dateline,
					'deviceToken' => $this->getUserDeviceToken($siteUids)
			);
			return parent::_callMethod('connect.discuz.notification.addSiteMasterUserNotify', $_params);
		}
		return false;
	}

	protected function _callMethod($method, $args) {
		try {
			return parent::_callMethod($method, $args);
		} catch (Exception $e) {

		}
	}
}