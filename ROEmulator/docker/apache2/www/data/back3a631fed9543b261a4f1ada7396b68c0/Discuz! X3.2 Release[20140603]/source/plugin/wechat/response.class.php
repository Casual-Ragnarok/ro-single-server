<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: response.class.php 34565 2014-05-30 03:22:02Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';

class WSQResponse {

	private static $expire = 1296000;
	public static $keyword = 'LOGIN_WSQ';

	public static function text($param) {
		list($data) = $param;
		self::_init();
		global $_G;
		$data['content'] = diconv($data['content'], 'UTF-8');
		$isloginkeyword = self::_custom('text', $data['content']);
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		$authcode = C::t('#wechat#mobile_wechat_authcode')->fetch_by_code($data['content']);
		if(!$authcode || $authcode['status']) {
			if($isloginkeyword) {
				wsq::report('loginclick');
				self::_show('access', $data['from']);
			}
		} else {
			wsq::report('sendnum');
			self::_show('sendnum', $data['from']."\t".$authcode['sid'], 60);
		}
	}

	public static function click($param) {
		list($data) = $param;
		self::_init();
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		if($data['key'] == self::$keyword) {
			wsq::report('loginclick');
			self::_show('access', $data['from']);
		}
	}

	public static function custom($param) {
		self::_init();
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		self::_custom('subscribe');
	}

	public static function subscribe($param) {
		list($data) = $param;
		self::_init();
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		if($data['key']) {
			self::scan($param);
		} else {
			self::_show('access', $data['from']);
		}
	}

	public static function scan($param) {
		list($data) = $param;
		self::_init();
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		$authcode = C::t('#wechat#mobile_wechat_authcode')->fetch_by_code($data['key']);
		if(!$authcode || $authcode['status']) {
		} else {
			if($authcode['uid']) {
				$member = getuserbyuid($authcode['uid'], 1);
				if($member['adminid'] == 0 && !$_G['wechat']['setting']['wechat_confirmtype']) {
					C::t('#wechat#mobile_wechat_authcode')->update($authcode['sid'], array('uid' => $member['uid'], 'status' => 1));
					$authcode['sid'] = '';
				}
			} else {
				$wechatuser = C::t('#wechat#common_member_wechat')->fetch_by_openid($data['from']);
				if($wechatuser) {
					$member = getuserbyuid($wechatuser['uid'], 1);
					if($member['adminid'] == 0 && !$_G['wechat']['setting']['wechat_confirmtype']) {
						C::t('#wechat#mobile_wechat_authcode')->update($authcode['sid'], array('uid' => $member['uid'], 'status' => 1));
						$authcode['sid'] = '';
					}
				} elseif($_G['wechat']['setting']['wechat_allowregister'] && $_G['wechat']['setting']['wechat_allowfastregister'] && $_G['wechat']['setting']['wechat_mtype'] == 2) {
					require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.class.php';
					require_once libfile('function/member');
					$uid = WeChat::register(WeChat::getnewname($data['from']), 1);
					if($uid) {
						WeChatHook::bindOpenId($uid, $data['from'], 1);
						C::t('#wechat#mobile_wechat_authcode')->update($authcode['sid'], array('uid' => $uid, 'status' => 1));
					}
					wsq::report('register');
					$authcode['sid'] = '';
				}
			}
			wsq::report('scanqr');
			self::_show('scan', $data['from']."\t".$authcode['sid']);
		}
	}

	public static function redirect($type) {
		self::_init();
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		if($_G['wechat']['setting']['wsq_siteid'] && !defined('IN_MOBILE_API')) {
			$in_wechat = strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
			$url = wsq::$WSQ_DOMAIN.'siteid='.$_G['wechat']['setting']['wsq_siteid'].'&c=index&a=';
			if($type) {
				$modid = $_G['basescript'].'::'.CURMODULE;
				if($in_wechat && $modid == 'forum::viewthread' && !empty($_GET['tid'])) {
					dheader('location: '.$url.'viewthread&tid='.$_GET['tid']);
				} elseif($in_wechat && $modid == 'forum::forumdisplay' && !empty($_GET['fid'])) {
					dheader('location: '.$url.'index&fid='.$_GET['fid']);
				} elseif($in_wechat && $modid == 'forum::index') {
					dheader('location: '.$url.'index');
				}
			} else {
				if(isset($_GET['referer'])) {
					return $_GET['referer'];
				} else {
					return $url.'index';
				}
			}
		}

	}

	private static function _show($messagekey, $key, $expire = 0) {
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		$expire = $expire ? $expire : self::$expire;
		$key = authcode($key, 'ENCODE', $_G['config']['security']['authkey'], $expire);
		$url = $_G['siteurl'] . 'plugin.php?mobile=2&id=wechat&op='.$messagekey.'&key=' . urlencode(base64_encode($key));
		$param = array('bbname' => $_G['wechat']['setting']['wsq_sitename'], 'date' => dgmdate(TIMESTAMP + $expire, 'Y-m-d'));
		loadcache('wechat_response');
		$desc = !empty($_G['cache']['wechat_response'][$messagekey]) ? $_G['cache']['wechat_response'][$messagekey] : 'wechat_response_text_' . $messagekey;
		$list = array(array(
			'title' => lang('plugin/wechat', 'wechat_response_text_title', $param),
			'desc' => lang('plugin/wechat', $desc, $param),
			'url' => $url
		    )
		);
		echo WeChatServer::getXml4RichMsgByArray($list);
		exit;
	}

	private static function _custom($type, $keyword = '') {
		global $_G;
		loadcache('wechat_response');
		$response = & $_G['cache']['wechat_response'];
		$query = $type == 'text' ? $response['query']['text'][$keyword] : $response['query']['subscribe'];
		if($query) {
			if($query == self::$keyword) {
				return 1;
			}
			echo WeChatServer::getXml4Txt($query);
			exit;
		}
		return 0;
	}

	private static function _init() {
		global $_G;
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}
	}

}