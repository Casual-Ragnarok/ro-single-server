<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Cloud.php 25607 2011-11-16 06:41:15Z yexinhao $
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