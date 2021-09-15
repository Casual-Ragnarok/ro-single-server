<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: My.php 29713 2012-04-26 01:51:38Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Server_Restful');
class Cloud_Service_Server_My extends Cloud_Service_Server_Restful {

	public $siteId;
	public $siteKey;

	public $timezone;
	public $version;
	public $charset;
	public $language;

	public $myAppStatus;
	public $mySearchStatus;

	protected static $_instance;

	public static function getInstance($siteId, $siteKey, $timezone, $version, $charset, $language, $myAppStatus, $mySearchStatus) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($siteId, $siteKey, $timezone, $version, $charset, $language, $myAppStatus, $mySearchStatus);
		}

		return self::$_instance;
	}

	public function __construct($siteId, $siteKey, $timezone, $version, $charset, $language, $myAppStatus, $mySearchStatus) {
		$this->siteId = $siteId;
		$this->siteKey = $siteKey;
		$this->timezone = $timezone;
		$this->version = $version;
		$this->charset = $charset;
		$this->language = $language;
		$this->myAppStatus = $myAppStatus;
		$this->mySearchStatus = $mySearchStatus;
	}

	public function run() {

		try {
			$this->_checkRequest();
			$response = $this->_processServerRequest();
		} catch (Exception $e) {
			$response = new Cloud_Service_Server_ErrorResponse($e->getCode(), $e->getMessage());
		}

		@ob_end_clean();
		if(function_exists('ob_gzhandler')) {
			@ob_start('ob_gzhandler');
		} else {
			@ob_start();
		}

		echo serialize($this->_formatLocalResponse($response));
		exit;
	}

	protected function _checkRequest() {
		global $_G;
		if (empty($_G['setting']['siteuniqueid'])) {
			throw new Cloud_Service_Server_RestfulException('Client SiteKey NOT Exists', 11);
		} elseif (empty($this->siteKey)) {
			throw new Cloud_Service_Server_RestfulException('My SiteKey NOT Exists', 12);
		}
	}

	protected function _processServerRequest() {
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		$params = $request['params'];

		if (!$module || !$method) {
			throw new Cloud_Service_Server_RestfulException('Invalid Method: ' . $method, 1);
		}

		$siteKey = $this->siteKey;
		if ($request['ptnId']) {
			$siteKey = md5($this->siteId . $this->siteKey . $request['ptnId'] . $request['ptnMethods']);
		}
		$sign = $this->_generateSign($module, $method, $params, $siteKey);

		if ($sign != $request['sign']) {
			throw new Cloud_Service_Server_RestfulException('Error Sign', 10);
		}

		if ($request['ptnId']) {
			if ($allowMethods = explode(',', $request['ptnMethods'])) {
				$manyouHelper = Cloud::loadClass('Service_ManyouHelper');
				if (!in_array($manyouHelper->getMethodCode($module, $method), $allowMethods)) {
					throw new Cloud_Service_Server_RestfulException('Method Not Allowed', 13);
				}
			}
		}

		$params = dunserialize($params);

		return $this->_callLocalMethod($module, $method, $params);
	}

	protected function _generateSign($module, $method, $params, $siteKey) {
		return md5($module . '|' . $method . '|' . $params . '|' . $siteKey);
	}

	protected function _callLocalMethod($module, $method, $params) {

		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				try {
					$response[] = $this->_callLocalMethod($param['module'], $param['method'], $param['params']);
				} catch (Exception $e) {
					$response[] = new Cloud_Service_Server_ErrorResponse($e->getCode, $e->getMessage());
				}
			}
			return new Cloud_Service_Server_Response($response, 'Batch');
		} else {
			$methodName = $this->_getMethodName($module, $method);
			$className = sprintf('Cloud_Service_Server_%s', ucfirst($module));
			try {
				$class = Cloud::loadClass($className);
			} catch (Exception $e) {
				throw new Cloud_Service_Server_RestfulException('Class not implemented: ' . $className, 2);
			}
			if (method_exists($class, $methodName)) {
				$result = call_user_func_array(array(&$class, $methodName), $params);
				if ($result instanceof Cloud_Service_Server_ErrorResponse) {
					return $result;
				}
				return new Cloud_Service_Server_Response($result);
			} else {
				throw new Cloud_Service_Server_RestfulException('Method not implemented: ' . $methodName, 2);
			}
		}
	}

	protected function _getMethodName($module, $method) {
		return 'on' . ucfirst($module) . ucfirst($method);
	}

	protected function _formatLocalResponse($data) {

		$utilService = Cloud::loadClass('Service_Util');
		$res = array(
							'my_version' => $utilService->getApiVersion(),
							'timezone' => $this->timezone,
							'version' => $this->version,
							'charset' => $this->charset,
							'language' => $this->language
						   );
		if ($data instanceof Cloud_Service_Server_Response) {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if ($result instanceof Cloud_Service_Server_Response) {
						$res['result'][]  = $result->getResult();
					} else {
						$res['result'][] = array(
								'errno' => $result->getCode(),
								'errmsg' =>  $result->getMessage()
							);
					}
				}
			} else {
				$res['result']  = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getCode();
			$res['errMessage'] = $data->getMessage();
		}
		return $res;
	}

}