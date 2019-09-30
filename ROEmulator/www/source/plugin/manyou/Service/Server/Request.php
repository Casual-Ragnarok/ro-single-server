<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Request.php 25522 2011-11-14 03:32:59Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Request extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onRequestSend($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		$now = time();
		$result = array();
		$type = ($type == 'request') ? 1 : 0;

		$fields = array('typename' => $requestName,
				'appid' => $appId,
				'type' => $type,
				'fromuid' => $uId,
				'dateline' => $now
				);

		foreach($recipientIds as $key => $val) {
			$hash = crc32($appId . $val . $now . rand(0, 1000));
			$hash = sprintf('%u', $hash);
			$fields['touid'] = intval($val);
			$fields['hash'] = $hash;
			$fields['myml'] = str_replace('{{MyReqHash}}', $hash, $myml);
			$result[] = C::t('common_myinvite')->insert($fields, true);
			$note = array(
					'from_id' => $fields['touid'],
					'from_idtype' => 'myappquery'
				);
			notification_add($fields['touid'], 'myapp', 'myinvite_request', $note);
		}
		return $result;
	}

}