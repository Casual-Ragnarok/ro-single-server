<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Manyou.php 29263 2012-03-31 05:45:08Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_RestfulException');

class Cloud_Service_Client_Manyou {

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

		$setting = $_G['setting'];
		$my_url = 'http://api.manyou.com/uchome.php';

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
		$postString = sprintf('action=%s&productType=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s&siteEnableApp=%s&from=cloud', 'siteRefresh', $productType, $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteKey, $siteLanguage, $siteVersion, $myVersion, $siteEnableApp);

		$response = @dfsockopen($my_url, 0, $postString, '', false, $setting['my_ip']);
		$res = unserialize($response);
		if (!$response) {
			throw new Cloud_Service_Client_RestfulException('Empty Response', 111);
		} elseif(!$res) {
			throw new Cloud_Service_Client_RestfulException('Error Response: ' . $response, 110);
		}
		if($res['errCode']) {
			throw new Cloud_Service_Client_RestfulException($res['errMessage'], $res['errCode']);
		}

		return true;
	}

}