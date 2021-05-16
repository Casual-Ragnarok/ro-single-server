<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Credit.php 25512 2011-11-14 02:31:42Z yexinhao $
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

}