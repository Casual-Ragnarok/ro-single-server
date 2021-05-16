<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Manyou.php 33053 2013-04-12 10:09:51Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_RestfulException');

class Cloud_Service_Client_Manyou {

	private $_myurl = 'http://api.manyou.com/uchome.php';

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function sync() {
		global $_G;

		$this->getResponse('siteRefresh');
		return true;
	}

	public function getMenuApps() {
		$result = $this->getResponse('getMenuApps');
		if($result) {
			$result['dateline'] = TIMESTAMP;
			C::t('common_setting')->update('appmenu', $result['result']);
		}
		return $result['errCode'] ? false : $result['result'];
	}

	private function getResponse($action) {
		global $_G;

		$response = @dfsockopen($this->_myurl, 0, $this->getGlobalPostString($action), '', false, $_G['setting']['my_ip']);
		$result = unserialize($response);
		if(!$response) {
			throw new Cloud_Service_Client_RestfulException('Empty Response', 111);
		} elseif(!$result) {
			throw new Cloud_Service_Client_RestfulException('Error Response: ' . $response, 110);
		}
		if($result['errCode']) {
			throw new Cloud_Service_Client_RestfulException($result['errMessage'], $result['errCode']);
		}
		return $result;
	}

	private function getGlobalPostString($action) {
		global $_G;

		$setting = $_G['setting'];

		$mySiteId = empty($_G['setting']['my_siteid'])?'':$_G['setting']['my_siteid'];
		$siteName = $_G['setting']['bbname'];
		$siteUrl = $_G['siteurl'];
		$ucUrl = rtrim($_G['setting']['ucenterurl'], '/').'/';
		$siteCharset = $_G['charset'];
		$siteTimeZone = $_G['setting']['timeoffset'];
		$mySiteKey = empty($_G['setting']['my_sitekey']) ? '' : $_G['setting']['my_sitekey'];
		$siteKey = C::t('common_setting')->fetch('siteuniqueid');
		$siteLanguage = $_G['config']['output']['language'];
		$siteVersion = $_G['setting']['version'];

		$utilService = Cloud::loadClass('Service_Util');
		$myVersion = $utilService->getApiVersion();

		$productType = 'DISCUZX';
		$siteRealNameEnable = '';
		$siteRealAvatarEnable = '';
		$siteEnableApp = intval($setting['my_app_status']);

		$key = $mySiteId . $siteName . $siteUrl . $ucUrl . $siteCharset . $siteTimeZone . $siteRealNameEnable . $mySiteKey . $siteKey;
		$key = md5($key);
		$siteTimeZone = urlencode($siteTimeZone);
		$siteName = urlencode($siteName);

		$register = false;
		return sprintf('action=%s&productType=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s&siteEnableApp=%s&from=cloud', $action, $productType, $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteKey, $siteLanguage, $siteVersion, $myVersion, $siteEnableApp);

	}

}