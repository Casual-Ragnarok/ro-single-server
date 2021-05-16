<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Restful.php 33750 2013-08-09 09:56:01Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_RestfulException');

abstract class Cloud_Service_Client_Restful {

	protected $_cloudApiIp = '';

	protected $_sId = 0;

	protected $_sKey = '';

	protected $_url = 'http://api.discuz.qq.com/site.php';

	protected $_format = 'PHP';

	protected $_ts = 0;

	protected $_debug = false;

	protected $_batchParams = array();

	public $errorCode = 0;

	public $errorMessage = '';

	public $my_status = false;
	public $cloud_status = false;

	public $siteName = '';
	public $uniqueId = '';
	public $siteUrl = '';
	public $charset = '';
	public $timeZone = 0;
	public $UCenterUrl = '';
	public $language = '';
	public $productType = '';
	public $productVersion = '';
	public $productRelease = '';
	public $apiVersion = '';
	public $siteUid = 0;

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		$this->_debug = $debug;

		$this->_initSiteEnv();
	}

	protected function _initSiteEnv() {

		global $_G;

		require_once DISCUZ_ROOT.'./source/discuz_version.php';

		$this->my_status = !empty($_G['setting']['my_app_status']) ? $_G['setting']['my_app_status'] : '';
		$this->cloud_status = !empty($_G['setting']['cloud_status']) ? $_G['setting']['cloud_status'] : '';

		$this->_sId = !empty($_G['setting']['my_siteid']) ? $_G['setting']['my_siteid'] : '';
		$this->_sKey = !empty($_G['setting']['my_sitekey']) ? $_G['setting']['my_sitekey'] : '';
		$this->_ts = TIMESTAMP;

		$this->siteName = !empty($_G['setting']['bbname']) ? $_G['setting']['bbname'] : '';

		$this->uniqueId = $_G['setting']['siteuniqueid'];
		$this->siteUrl = $_G['siteurl'];
		$this->charset = CHARSET;
		$this->timeZone = !empty($_G['setting']['timeoffset']) ? $_G['setting']['timeoffset'] : '';
		$this->UCenterUrl = !empty($_G['setting']['ucenterurl']) ? $_G['setting']['ucenterurl'] : '';
		$this->language = $_G['config']['output']['language'] ? $_G['config']['output']['language'] : 'zh_CN';
		$this->productType = 'DISCUZX';
		$this->productVersion = defined('DISCUZ_VERSION') ? DISCUZ_VERSION : '';
		$this->productRelease = defined('DISCUZ_RELEASE') ? DISCUZ_RELEASE : '';

		$utilService = Cloud::loadClass('Service_Util');
		$this->apiVersion = $utilService->getApiVersion();

		$this->siteUid = $_G['uid'];

		if ($_G['setting']['cloud_api_ip']) {
			$this->setCloudApiIp($_G['setting']['cloud_api_ip']);
		}

	}

	protected function _callMethod($method, $args, $isBatch = false, $return = false) {
		$this->errorCode = 0;
		$this->errorMessage = '';
		$url = $this->_url;
		$avgDomain = explode('.', $method);
		switch ($avgDomain[0]) {
			case 'site':
				$url = 'http://api.discuz.qq.com/site_cloud.php';
				break;
			case 'qqgroup':
				$url = 'http://api.discuz.qq.com/site_qqgroup.php';
				break;
			case 'connect':
				$url = 'http://api.discuz.qq.com/site_connect.php';
				break;
			case 'security':
				$url = 'http://api.discuz.qq.com/site_security.php';
				break;
			default:
				$url = $this->_url;
		}
		$params = array();
		$params['sId'] = $this->_sId;
		$params['method'] = $method;
		$params['format'] = strtoupper($this->_format);

		$params['sig'] = $this->_generateSig($params, $method, $args);
		$params['ts'] = $this->_ts;

		$postData = $this->_createPostData($params, $args);

		if ($isBatch) {
			$this->_batchParams[] = $postData;

			return true;
		} else {

			$utilService = Cloud::loadClass('Service_Util');
			$postString = $utilService->httpBuildQuery($postData, '', '&');

			$result = $this->_postRequest($url, $postString);
			if ($this->_debug) {
				$this->_message('receive data ' . dhtmlspecialchars($result) . "\n\n");
			}

			if(!$return) {
				return $this->_parseResponse($result, false, $return);
			} else {
				$response = @dunserialize($result);
				if(!is_array($response)) {
					return $result;
				} else {
					return $response;
				}
			}
		}
	}

	protected function _parseResponse($response, $isBatch = false) {

		if (!$response) {
			$this->_unknowErrorMessage();
		}

		$response = @dunserialize($response);
		if (!is_array($response)) {
			$this->_unknowErrorMessage();
		}
		if ($response['errCode']) {
			$this->errorCode = $response['errCode'];
			$this->errorMessage = $response['errMessage'];
			throw new Cloud_Service_Client_RestfulException($response['errMessage'], $response['errCode']);
		}
		if (!isset($response['result']) && !isset($response['batchResult'])) {
			$this->_unknowErrorMessage();
		}

		if ($isBatch) {
			return $response['batchResult'];
		} else {
			return $response['result'];
		}

	}

	public function runBatchMethod() {

		if (!$this->_batchParams) {
			return false;
		}

		$postData = array('batchParams' => $this->_batchParams);
		$utilService = Cloud::loadClass('Service_Util');
		$postString = $utilService->httpBuildQuery($postData, '', '&');

		$result = $this->_postRequest($this->_url, $postString);
		if ($this->_debug) {
			$this->_message('receive data ' . dhtmlspecialchars($result) . "\n\n");
		}

		return $this->_parseResponse($result, true);
	}

	protected function _unknowErrorMessage() {
		$this->errorCode = 1;
		$this->errorMessage = 'An unknown error occurred. May be DNS Error. ';

		throw new Cloud_Service_Client_RestfulException($this->errorMessage, $this->errorCode);
	}

	protected function _generateSig($params, $method, $args) {

		$postData = $this->_createPostData($params, $args);

		$utilService = Cloud::loadClass('Service_Util');
		$postString = $utilService->httpBuildQuery($postData, '', '&');

		if ($this->_debug) {
			$this->_message('sig string: ' . $postString . '|' . $this->_sKey . '|' . $this->_ts . "\n\n");
		}

		return md5(sprintf('%s|%s|%s', $postString, $this->_sKey, $this->_ts));
	}

	protected function _createPostData($params, $args) {

		ksort($params);
		ksort($args);

		$params['args'] = $args;

		return $params;
	}

	protected function _postRequest($url, $data, $ip = '') {
		if ($this->_debug) {
			$this->_message('post params: ' . $data. "\n\n");
		}

		$ip = $this->_cloudApiIp;

		$result = $this->_fsockopen($url, 0, $data, '', false, $ip, 5);
		return $result;
	}

	function _fsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true) {
		return dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
	}

	protected function _message($msg) {
		echo $msg;
	}

	public function setCloudApiIp($ip) {

		$this->_cloudApiIp = $ip;

		return true;
	}

	protected function getUserOpenId($uid) {
		$openId = '';
		try {
			$connectInfo = C::t('#qqconnect#common_member_connect')->fetch($uid);
			if($connectInfo) {
				$openId = $connectInfo['conopenid'];
			}
		} catch (Exception $e) {}
		return $openId;
	}
	protected function getUserDeviceToken($uids) {
		$uids = (array)$uids;
		$deviceToken = array();
		try {
			$query = DB::query('SELECT * FROM '.DB::table('common_devicetoken').' WHERE uid IN('.dimplode($uids).')', array(), true);
			while($value = DB::fetch($query)) {
				$deviceToken[$value['uid']][] = $value['token'];
			}
		} catch (Exception $e) {}
		return $deviceToken;
	}

}