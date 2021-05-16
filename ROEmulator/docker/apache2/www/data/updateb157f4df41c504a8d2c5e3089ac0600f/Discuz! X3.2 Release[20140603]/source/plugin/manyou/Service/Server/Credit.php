<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Credit.php 34004 2013-09-18 05:06:47Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Credit extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onCreditGet($uId) {
		global $_G;

		$_G['setting']['myapp_credit'] = '';
		if($_G['setting']['creditstransextra'][7]) {
			$_G['setting']['myapp_credit'] = 'extcredits'.intval($_G['setting']['creditstransextra'][7]);
		} elseif ($_G['setting']['creditstrans']) {
			$_G['setting']['myapp_credit'] = 'extcredits'.intval($_G['setting']['creditstrans']);
		}

		if(empty($_G['setting']['myapp_credit'])) {
			return 0;
		}

		$row = C::t('common_member_count')->fetch($uId);
		return $row[$_G['setting']['myapp_credit']];
	}

	public function onCreditSign($uId) {
		global $_G;

		if($uId) {
			return updatecreditbyaction('mobileoemdaylogin', $uId) ? 1 : 0;
		}
		return 0;
	}

}