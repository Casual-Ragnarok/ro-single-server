<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile.class.php 31281 2012-08-03 02:29:27Z zhangjie $
 */

define("MOBILE_PLUGIN_VERSION", "2");

class mobile_core {

	function result($result) {
		global $_G;
		ob_end_clean();
		function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		echo mobile_core::json($result);
		exit;
	}

	function json($encode) {
		if(!empty($_GET['debug']) && defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			return debug($encode);
		}
		require_once 'source/plugin/mobile/json.class.php';
		return CJSON::encode($encode);
	}

	function getvalues($variables, $keys, $subkeys = array()) {
		$return = array();
		foreach($variables as $key => $value) {
			foreach($keys as $k) {
				if($k{0} == '/' && preg_match($k, $key) || $key == $k) {
					if($subkeys) {
						$return[$key] = mobile_core::getvalues($value, $subkeys);
					} else {
						if(!empty($value) || !empty($_GET['debug']) || (is_numeric($value) && intval($value) === 0 )) {
							$return[$key] = is_array($value) ? mobile_core::arraystring($value) : (string)$value;
						}
					}
				}
			}
		}
		return $return;
	}

	function arraystring($array) {
		foreach($array as $k => $v) {
			$array[$k] = is_array($v) ? mobile_core::arraystring($v) : (string)$v;
		}
		return $array;
	}

	function variable($variables = array()) {
		global $_G;
		$globals = array(
			'cookiepre' => $_G['config']['cookie']['cookiepre'],
			'auth' => $_G['cookie']['auth'],
			'saltkey' => $_G['cookie']['saltkey'],
			'member_uid' => $_G['member']['uid'],
			'member_username' => $_G['member']['username'],
			'groupid' => $_G['groupid'],
			'formhash' => FORMHASH,
			'ismoderator' => $_G['forum']['ismoderator'],
			'readaccess' => $_G['group']['readaccess'],
		);
		if(!empty($_GET['submodule']) == 'checkpost') {
			$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/sub_checkpost.php';
			if(file_exists($apifile)) {
				require_once $apifile;
				$globals = $globals + mobile_api_sub::getvariable();
			}
		}
		$xml = array(
			'Version' => $_GET['version'],
			'Charset' => strtoupper($_G['charset']),
			'Variables' => array_merge($globals, $variables),
		);
		if(!empty($_G['messageparam'])) {
			$message_result = lang('plugin/mobile', $_G['messageparam'][0], $_G['messageparam'][2]);
			if($message_result == $_G['messageparam'][0]) {
				$vars = explode(':', $_G['messageparam'][0]);
				if (count($vars) == 2) {
					$message_result = lang('plugin/' . $vars[0], $vars[1], $_G['messageparam'][2]);
					$_G['messageparam'][0] = $vars[1];
				} else {
					$message_result = lang('message', $_G['messageparam'][0], $_G['messageparam'][2]);
				}
			}
			$message_result = strip_tags($message_result);

			if($_G['messageparam'][4]) {
				$_G['messageparam'][0] = "custom";
			}
			if ($_G['messageparam'][3]['login'] && !$_G['uid']) {
				$_G['messageparam'][0] .= '//' . $_G['messageparam'][3]['login'];
			}
			$xml['Message'] = array("messageval" => $_G['messageparam'][0], "messagestr" => $message_result);
			if($_GET['mobilemessage']) {
				$return = mobile_core::json($xml);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:discuz://" . rawurlencode($_G['messageparam'][0]) . "//" . rawurlencode(diconv($message_result, $_G['charset'], "utf-8")) . ($return ? "//" . rawurlencode($return) : '' ));
				exit;
			}
		}
		return $xml;
	}

}

class base_plugin_mobile {

	function common() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		global $_G;
		if(!empty($_GET['tpp'])) {
			$_G['tpp'] = intval($_GET['tpp']);
		}
		if(!empty($_GET['ppp'])) {
			$_G['ppp'] = intval($_GET['ppp']);
		}
		$_G['siteurl'] = preg_replace('/api\/mobile\/$/', '', $_G['siteurl']);
		$_G['setting']['msgforward'] = '';
		$_G['setting']['cacheindexlife'] = $_G['setting']['cachethreadlife'] = false;
		if(class_exists('mobile_api', 'common')) {
			mobile_api::common();
		}
	}

	function discuzcode() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		global $_G;
		$_G['discuzcodemessage'] = preg_replace(array(
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
			"/\[\/size]/i",
		), '', $_G['discuzcodemessage']);
	}

	function global_mobile() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'output')) {
			mobile_api::output();
		}
	}

}

class base_plugin_mobile_forum extends base_plugin_mobile {

	function post_mobile_message($param) {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'post_mobile_message')) {
			list($message, $url_forward, $values, $extraparam, $custom) = $param['param'];
			mobile_api::post_mobile_message($message, $url_forward, $values, $extraparam, $custom);
		}
	}

	function viewthread_postbottom_output() {
		global $_G, $postlist;
		foreach($postlist as $k => $post) {
			$frommobiletype = '';
			if($post['mobiletype'] == 1) {
				$frommobiletype = lang('plugin/mobile', 'mobile_fromtype_ios');
			} elseif($post['mobiletype'] == 2) {
				$frommobiletype = lang('plugin/mobile', 'mobile_fromtype_android');
			} elseif($post['mobiletype'] == 3) {
				$frommobiletype = lang('plugin/mobile', 'mobile_fromtype_windowsphone');
			}
			$post['message'] .= $frommobiletype ? '<br><a href="misc.php?mod=mobile" target="_blank" style="font-size:12px;color:#708090;">'.$frommobiletype.'</a>' : '';
			$postlist[$k] = $post;
		}
		return array();
	}

}

class base_plugin_mobile_misc extends base_plugin_mobile {

	function mobile() {
		global $_G;
		if(empty($_GET['view']) && !defined('MOBILE_API_OUTPUT')) {
			$_G['setting']['pluginhooks'] = array();
			$qrfile = DISCUZ_ROOT.'./data/cache/mobile_siteqrcode.png';
			if(!file_exists($qrfile) || $_G['adminid'] == 1) {
				require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
				QRcode::png($_G['siteurl'], $qrfile);
			}
			define('MOBILE_API_OUTPUT', 1);
			$_G['disabledwidthauto'] = 1;
			define('TPL_DEFAULT', true);
			include template('mobile:mobile');
			exit;
		}
	}

}

class plugin_mobile extends base_plugin_mobile {}
class plugin_mobile_forum extends base_plugin_mobile_forum {}
class plugin_mobile_misc extends base_plugin_mobile_misc {}
class mobileplugin_mobile extends base_plugin_mobile {
	function global_header_mobile() {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'iphone') !== false || strpos($useragent, 'ios') !== false) {
			return lang('plugin/mobile', 'mobile_tip_ios');
		} elseif(strpos($useragent, 'android') !== false) {
			return lang('plugin/mobile', 'mobile_tip_android');
		} elseif(strpos($useragent, 'windows phone') !== false) {
			return lang('plugin/mobile', 'mobile_tip_wp7');
		}
	}
}
class mobileplugin_mobile_forum extends base_plugin_mobile_forum {}
class mobileplugin_mobile_misc extends base_plugin_mobile_misc {}

class plugin_mobile_connect extends plugin_mobile {

	function login_mobile_message($param) {
		if(substr($_GET['referer'], 0, 7) == 'Mobile_') {
			if($_GET['referer'] == 'Mobile_iOS' || $_GET['referer'] == 'Mobile_Android') {
				$_GET['mobilemessage'] = 1;
			}
			global $_G;
			$param = array('con_auth_hash' => $_G['cookie']['con_auth_hash']);
			mobile_core::result(mobile_core::variable($param));
		}
	}

}

?>