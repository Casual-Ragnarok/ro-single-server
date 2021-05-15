<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsq.class.php 34559 2014-05-29 09:48:04Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class wsq {

	public static $WSQ_DOMAIN = 'http://wsq.discuz.qq.com/?';
	public static $API_URL = 'http://wsq.discuz.qq.com/?';
	public static $SETTING = array();

	private static function _dfsockopen($get, $post = array()) {
		global $_G;
		$return = dfsockopen(self::$API_URL.http_build_query($get), 0, $post);
		return json_decode($return);
	}

	private static function _check_sign($data, $token, $signature = '') {
		if(isset($data['signature'])) {
			$signature = $data['signature'];
			unset($data['signature'], $data['mobile']);
		}

		$tt = $data['tt'] ? $data['tt'] : $data['timestamp'];
		if(!$tt || TIMESTAMP - $tt > 600) {
			return false;
		}

		$code = $data['code'];
		sort($data, SORT_STRING);
		$data[] = $token;
		$data = implode($data);
		$tmpstr = sha1($data);

		if($tmpstr === $signature && empty($code)){
			return true;
		} else {
			return false;
		}
	}

	private static function _make_sign($data, $token) {
		sort($data, SORT_STRING);
		$data[] = $token;
		$data = implode($data);
		$tmpstr = sha1($data);

		return $tmpstr;
	}

	private static function _convert($post) {
		foreach($post as $k => $v) {
			$post[$k] = diconv($v, CHARSET, 'UTF-8');
		}
		return $post;
	}

	private static function _setting() {
		global $_G;
		if(!self::$SETTING) {
			self::$SETTING = unserialize($_G['setting']['mobilewechat']);
		}
	}

	private static function _token() {
		self::_setting();
		return self::$SETTING['wsq_sitetoken'];
	}

	private static function _siteid() {
		self::_setting();
		return self::$SETTING['wsq_siteid'];
	}

	public static function register($sitename, $siteurl, $sitelogo, $sitesummary, $mptype, $qrtype) {
		global $_G;
		$get = array(
			'c' => 'site',
			'a' => 'register'
		);
		$post = array(
			'sitename' => $sitename,
			'siteurl' => $siteurl,
			'sitelogo' => $sitelogo,
			'sitesummary' => $sitesummary,
			'mptype' => $mptype,
			'qrtype' => $qrtype,
			'siteuniqueid' => $_G['setting']['siteuniqueid'],
		);
		$post = self::_convert($post);
		return self::_dfsockopen($get, $post);
	}

	public static function info() {
		global $_G;
		$get = array(
			'c' => 'site',
			'a' => 'info',
			'siteid' => self::_siteid()
		);
		return self::_dfsockopen($get);
	}

	public static function qrconnectUrl($uid, $qrreferer) {
		$get = array(
			'c' => 'site',
			'a' => 'qrconnect',
			'siteid' => self::_siteid(),
			'siteuid' => $uid,
			'qrreferer' => $qrreferer,
			'tt' => TIMESTAMP,
		);
		$get['signature'] = self::_make_sign($get, self::_token());
		return self::$API_URL.http_build_query($get);
	}

	public static function userregisterUrl($uid, $openid, $openidSign, $qrreferer) {
		$get = array(
			'c' => 'site',
			'a' => 'userregister',
			'siteid' => self::_siteid(),
			'siteuid' => $uid,
			'openid' => $openid,
			'openidsign' => $openidSign,
			'qrreferer' => $qrreferer,
			'tt' => TIMESTAMP,
		);
		$get['signature'] = self::_make_sign($get, self::_token());
		return self::$API_URL.http_build_query($get);
	}

	public static function wxuserregisterUrl($uid) {
		$get = array(
			'c' => 'site',
			'a' => 'wxuserregister',
			'siteid' => self::_siteid(),
			'siteuid' => $uid,
			'tt' => TIMESTAMP,
			'mobile' => 2,
			'qrreferer' => $_GET['referer'],
		);
		$get['signature'] = self::_make_sign($get, self::_token());
		return self::$API_URL.http_build_query($get);
	}

	public static function userunbind($uid, $openid) {
		$get = array(
			'c' => 'site',
			'a' => 'userunbind',
			'siteid' => self::_siteid(),
			'tt' => TIMESTAMP,
		);
		$post = array(
			'openid' => $openid,
			'siteuid' => $uid,
		);
		$post['signature'] = self::_make_sign(array_merge($get, $post), self::_token());
		$return = self::_dfsockopen($get, $post);
		return !$return->code;
	}

	public static function edit($sitename, $siteurl, $sitelogo, $sitesummary, $mptype, $qrtype) {
		global $_G;
		$get = array(
			'c' => 'site',
			'a' => 'edit',
			'siteid' => self::_siteid(),
		);
		$post = array(
			'sitename' => $sitename,
			'siteurl' => $siteurl,
			'sitelogo' => $sitelogo,
			'sitesummary' => $sitesummary,
			'mptype' => $mptype,
			'qrtype' => $qrtype,
			'siteuniqueid' => $_G['setting']['siteuniqueid'],
		);
		$post = self::_convert($post);
		$post['signature'] = self::_make_sign(array_merge($get, $post), self::_token());
		return self::_dfsockopen($get, $post);
	}

	public static function recheck() {
		$get = array(
			'c' => 'site',
			'a' => 'recheck',
			'siteid' => self::_siteid(),
		);
		$post = array();
		$post['signature'] = self::_make_sign(array_merge($get, $post), self::_token());
		return self::_dfsockopen($get, $post);
	}

	public static function report($action) {
		global $_G;
		$get = array(
			'c' => 'report',
			'a' => $action,
			'siteid' => self::_siteid(),
		);
		$post = array(
			'uid' => $_G['uid'],
			'userip' => $_G['clientip']
		);
		$post['signature'] = self::_make_sign(array_merge($get, $post), self::_token());
		return self::_dfsockopen($get, $post);
	}

	public static function check($param) {
		if(self::_check_sign($param, self::_token())) {
			return $param['echostr'];
		}
		return;
	}

	public static function checksign($param) {
		return self::_check_sign($param, self::_token());
	}

	public static function siteinfo() {
		self::_setting();
		return array(
		    'siteInfo' => array(
			'sName' => self::$SETTING['wsq_sitename'],
			'sDesc' => self::$SETTING['wsq_sitesummary'],
			'sLogo' => self::$SETTING['wsq_sitelogo'],
		    )
		);
	}

}

?>