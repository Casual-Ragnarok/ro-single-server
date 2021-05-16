<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Cloud.php 33755 2013-08-10 03:21:02Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Cloud extends Cloud_Service_Client_Restful {

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

	public function appOpen() {
		return $this->_callMethod('site.appOpen', array('sId' => $this->_sId, 'appIdentifier' => 'search'));
	}

	public function appOpenWithRegister($appIdentifier, $extra = array()) {
		$this->reloadId();
		return $this->_callMethod('site.appOpen', array(
			'sId' => $this->_sId,
			'appIdentifier' => $appIdentifier,
			'sName' => $this->siteName,
			'sSiteKey' => $this->uniqueId,
			'sUrl' => $this->siteUrl,
			'sCharset' => $this->charset,
			'sTimeZone' => $this->timeZone,
			'sUCenterUrl' => $this->UCenterUrl,
			'sLanguage' => $this->language,
			'sProductType' => $this->productType,
			'sProductVersion' => $this->productVersion,
			'sTimestamp' => $this->_ts,
			'sApiVersion' => $this->apiVersion,
			'sSiteUid' => $this->siteUid,
			'sProductRelease' => $this->productRelease,
			'extra' => $extra,
		), false, true);
	}

	public function appOpenFormView($appIdentifier, $submitUrl, $fromUrl) {
		return $this->_callMethod('site.getAppOpenFormView', array(
			'sId' => $this->_sId,
			'sCharset' => $this->charset,
			'appIdentifier' => $appIdentifier,
			'submitUrl' => $submitUrl,
			'fromUrl' => $fromUrl,
			'type' => 'open',
		));
	}

	public function appClose($appIdentifier) {
		return $this->_callMethod('site.appClose', array(
			'sId' => $this->_sId,
			'appIdentifier' => $appIdentifier,
			'sSiteUid' => $this->siteUid,
		), false, true);
	}

	public function appCloseReasonsView($appIdentifier, $submitUrl, $fromUrl) {
		return $this->_callMethod('site.getAppCloseReasonsView', array(
			'sId' => $this->_sId,
			'appIdentifier' => $appIdentifier,
			'submitUrl' => $submitUrl,
			'fromUrl' => $fromUrl,
		));
	}

	private function reloadId() {
		global $_G;
		loadcache('setting', 1);
		$this->_sId = !empty($_G['setting']['my_siteid']) ? $_G['setting']['my_siteid'] : '';
		$this->_sKey = !empty($_G['setting']['my_sitekey']) ? $_G['setting']['my_sitekey'] : '';
	}

	public function bindQQ($appIdentifier, $fromUrl, $extra = array()) {
		$this->reloadId();
		$utilService = Cloud::loadClass('Service_Util');
		$fromUrl .= $extra ? '&'.$utilService->httpBuildQuery(array('extra' => $extra), '', '&') : '';
		$params = array(
			's_id' => $this->_sId,
			'app_identifier' => $appIdentifier,
			's_site_uid' => $this->siteUid,
			'from_url' => $fromUrl,
			'ADTAG' => 'CP.CLOUD.BIND.INDEX',
		);
		ksort($params);
		$str = $utilService->httpBuildQuery($params, '', '&');
		$params['sig'] = md5(sprintf('%s|%s|%s', $str, $this->_sKey, $this->_ts));
		$params['ts'] = $this->_ts;
		return 'http://cp.discuz.qq.com/addon_bind/index?'.$utilService->httpBuildQuery($params, '', '&');
	}

	public function register() {

		return $this->_callMethod('site.register', array(
														 'sName' => $this->siteName,
														 'sSiteKey' => $this->uniqueId,
														 'sUrl' => $this->siteUrl,
														 'sCharset' => $this->charset,
														 'sTimeZone' => $this->timeZone,
														 'sUCenterUrl' => $this->UCenterUrl,
														 'sLanguage' => $this->language,
														 'sProductType' => $this->productType,
														 'sProductVersion' => $this->productVersion,
														 'sTimestamp' => $this->_ts,
														 'sApiVersion' => $this->apiVersion,
														 'sSiteUid' => $this->siteUid,
														 'sProductRelease' => $this->productRelease,
												   )
								  );
	}

	public function sync() {
		return $this->_callMethod('site.sync', array(
													 'sId' => $this->_sId,
													 'sName' => $this->siteName,
													 'sSiteKey' => $this->uniqueId,
													 'sUrl' => $this->siteUrl,
													 'sCharset' => $this->charset,
													 'sTimeZone' => $this->timeZone,
													 'sUCenterUrl' => $this->UCenterUrl,
													 'sLanguage' => $this->language,
													 'sProductType' => $this->productType,
													 'sProductVersion' => $this->productVersion,
													 'sTimestamp' => $this->_ts,
													 'sApiVersion' => $this->apiVersion,
													 'sSiteUid' => $this->siteUid,
													 'sProductRelease' => $this->productRelease
													 )
								  );
	}

	public function resetKey() {

		return $this->_callMethod('site.resetKey', array('sId' => $this->_sId));
	}

	public function resume() {

		return $this->_callMethod('site.resume', array(
																			   'sUrl' => $this->siteUrl,
																			   'sCharset' => 'UTF-8',
																			   'sProductType' => $this->productType,
																			   'sProductVersion' => $this->productVersion
																			   )
												 );
	}

	public function registerCloud($cloudApiIp = '') {

		try {

			$returnData = $this->register();

		} catch (Cloud_Service_Client_RestfulException $e) {

			if ($e->getCode() == 1 && $cloudApiIp) {

				$this->setCloudApiIp($cloudApiIp);

				try {

					$returnData = $this->register();
					C::t('common_setting')->update('cloud_api_ip', $cloudApiIp);

				} catch (Cloud_Service_Client_RestfulException $e) {

					throw new Cloud_Service_Client_RestfulException($e);
				}
			} else {
					throw new Cloud_Service_Client_RestfulException($e);
			}
		}

		$sId = intval($returnData['sId']);
		$sKey = trim($returnData['sKey']);

		if ($sId && $sKey) {
			C::t('common_setting')->update_batch(array('my_siteid' => $sId, 'my_sitekey' =>$sKey ,'cloud_status' => '2'));
			updatecache('setting');
		} else {
			throw new Cloud_Service_Client_RestfulException('Error Response.', 2);
		}

		return true;
	}


	public function upgradeManyou($cloudApiIp = '') {

		try {

			$returnData = $this->sync();

		} catch (Cloud_Service_Client_RestfulException $e) {

			if ($e->getCode() == 1 && $cloudApiIp) {

				$this->setCloudApiIp($cloudApiIp);

				try {

					$returnData = $this->sync();
					C::t('common_setting')->update('cloud_api_ip', $cloudApiIp);

				} catch (Cloud_Service_Client_RestfulException $e) {

					throw new Cloud_Service_Client_RestfulException($e);
				}
			}

		}

		return true;
	}

}