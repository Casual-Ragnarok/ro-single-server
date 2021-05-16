<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_captcha.php 33997 2013-09-17 06:46:37Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('CLOUDCAPTCHA_GET_URL', 'http://api.discuz.qq.com/captcha/get');
define('CLOUDCAPTCHA_VALIDATE_URL', 'http://api.discuz.qq.com/captcha/validate');
define('CLOUDCAPTCHA_ISNEED_URL', 'http://api.discuz.qq.com/captcha/isNeed');
define('CLOUDCAPTCHA_REPORT_URL', 'http://api.discuz.qq.com/captcha/report');
define('CLOUDCAPTCHA_VER', '1.0');

class captcha {

	public function generateSiteSignUrl($params = array()) {
		global $_G;

		$utilService = Cloud::loadClass('Service_Util');

		@include_once DISCUZ_ROOT.'./source/discuz_version.php';
		if(!isset($_G['member']['conopenid'])) {
			$member_connect = $_G['uid'] ? C::t('#qqconnect#common_member_connect')->fetch($_G['uid']) : array();
			$_G['member'] = array_merge($_G['member'], $member_connect);
		}

		$ts = TIMESTAMP;
		$sKey = $_G['setting']['my_sitekey'];

		$params['clientIp'] = $_G['clientip'];
		$params['uid'] = $_G['uid'];
		$params['openId'] = getuserprofile('conopenid');
		$params['sId'] = $_G['setting']['siteuniqueid'];
		$params['appId'] = $_G['setting']['connectappid'];
		$params['ver'] = CLOUDCAPTCHA_VER;
		$params['dzVersion'] = DISCUZ_VERSION;
		$params['sId'] = $_G['setting']['my_siteid'];
		ksort($params);

		$str = $utilService->httpBuildQuery($params, '', '&');
		$sig = md5(sprintf('%s|%s|%s', $str, $sKey, $ts));

		$params['ts'] = $ts;
		$params['sig'] = $sig;
		$params = $utilService->httpBuildQuery($params, '', '&');
		return $params;
	}

	public function cookie_parse($line) {
		$cookies = array();
		foreach(explode(';', $line) as $data) {
			$cinfo = explode('=', $data);
			$cinfo[0] = trim($cinfo[0]);
			if(!in_array($cinfo[0], array('domain', 'expires', 'path', 'secure', 'comment'))) {
				$cookies[$cinfo[0]] = $cinfo[1];
			}
		}
		return $cookies;
	}

	public function get($refresh, $modid) {
		global $_G;
		$params = array(
			'rule' => $_G['cookie']['seccloud'] ? 2 : 1,
			'refresh' => $refresh ? 1 : 0,
			'oper' => $modid,
		);
		return dfsockopen(CLOUDCAPTCHA_GET_URL.'?'.captcha::generateSiteSignUrl($params));
	}

	public function validate($code, $picSig, $fromjs, $modid) {
		global $_G;
		if(!$code || strlen($code) != 4) {
			return false;
		}
		$params = array(
			'code' => $code,
			'picSig' => $picSig,
			'rule' => $_G['cookie']['seccloud'] ? 2 : 1,
			'isJSReq' => $fromjs ? 1 : 0,
			'oper' => $modid,
		);
		return dfsockopen(CLOUDCAPTCHA_VALIDATE_URL.'?'.captcha::generateSiteSignUrl($params));
	}

	public function isneed() {
		return dfsockopen(CLOUDCAPTCHA_ISNEED_URL.'?'.captcha::generateSiteSignUrl()) == '{"errCode":0,"res":"yes"}';
	}

	public function report($content = array()) {
		$params = array(
		    'type' => 1,
		    'content' => (array)$content,
		);
		return dfsockopen(CLOUDCAPTCHA_REPORT_URL.'?'.captcha::generateSiteSignUrl($params));
	}

}

?>