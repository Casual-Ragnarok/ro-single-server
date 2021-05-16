<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: QQGroup.php 28558 2012-03-05 02:59:09Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_QQGroup {

	protected $_util;

	protected static $_instance;

	public static function getInstance() {
		global $_G;

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->_util = Cloud::loadClass('Service_Util');
	}

	public function iframeUrl($tid, $title, $content) {
		global $_G;

		if (!$_G['adminid']) {
			return false;
		}

		$url = 'http://qun.discuz.qq.com/feed/push?';
		$params = array(
			't_id' => $tid,
			's_url' => $_G['siteurl'],
			'title' => $title,
			'content' => $content
		);

		$signUrl = $this->_util->generateSiteSignUrl($params);

		return $url . $signUrl;
	}
}