<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_form.php 30757 2012-06-18 06:23:23Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_form {


	public static function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
		if(!getgpc($var)) {
			return FALSE;
		} else {
			global $_G;
			if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_GET['formhash']) && $_GET['formhash'] == formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
			preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
				if(checkperm('seccode')) {
					if($secqaacheck && !check_secqaa($_GET['secanswer'], $_GET['sechash'])) {
						showmessage('submit_secqaa_invalid');
					}
					if($seccodecheck && !check_seccode($_GET['seccodeverify'], $_GET['sechash'])) {
						showmessage('submit_seccode_invalid');
					}
				}
				return TRUE;
			} else {
				showmessage('submit_invalid');
			}
		}
	}

	public static function censor($message, $modword = NULL, $return = FALSE) {
		global $_G;
		$censor = discuz_censor::instance();
		$censor->check($message, $modword);
		if($censor->modbanned() && empty($_G['group']['ignorecensor'])) {
			$wordbanned = implode(', ', $censor->words_found);
			if($return) {
				return array('message' => lang('message', 'word_banned', array('wordbanned' => $wordbanned)));
			}
			if(!defined('IN_ADMINCP')) {
				showmessage('word_banned', '', array('wordbanned' => $wordbanned));
			} else {
				cpmsg(lang('message', 'word_banned'), '', 'error', array('wordbanned' => $wordbanned));
			}
		}
		if($_G['group']['allowposturl'] == 0 || $_G['group']['allowposturl'] == 2) {
			$urllist = self::get_url_list($message);
			if(is_array($urllist[1])) foreach($urllist[1] as $key => $val) {
				if(!$val = trim($val)) continue;
				if(!iswhitelist($val)) {
					if($_G['group']['allowposturl'] == 0) {
						if($return) {
							return array('message' => 'post_url_nopermission');
						}
						showmessage('post_url_nopermission');
					} elseif($_G['group']['allowposturl'] == 2) {
						$message = str_replace('[url]'.$urllist[0][$key].'[/url]', $urllist[0][$key], $message);
						$message = preg_replace(
							array(
								"@\[url=[^\]]*?".preg_quote($urllist[0][$key],'@')."[^\]]*?\](.*?)\[/url\]@is",
								"@href=('|\")".preg_quote($urllist[0][$key],'@')."\\1@is",
								"@\[url\]([^\]]*?".preg_quote($urllist[0][$key],'@')."[^\]]*?)\[/url\]@is",
							),
							array(
								'\\1',
								'',
								'\\1',
							),
							$message);
					}
				}
			}
		}
		return $message;
	}

	public static function censormod($message) {
		global $_G;
		if($_G['group']['ignorecensor']) {
			return false;
		}
		$modposturl = false;
		if($_G['group']['allowposturl'] == 1) {
			$urllist = self::get_url_list($message);
			if(is_array($urllist[1])) foreach($urllist[1] as $key => $val) {
				if(!$val = trim($val)) continue;
				if(!iswhitelist($val)) {
					$modposturl = true;
				}
			}
		}
		if($modposturl) {
			return true;
		}

		$censor = discuz_censor::instance();
		$censor->check($message);
		return $censor->modmoderated();
	}


	public static function check_seccode($value, $idhash) {
		global $_G;
		if(!$_G['setting']['seccodestatus']) {
			return true;
		}
		if(!is_numeric($_G['setting']['seccodedata']['type'])) {
			if(file_exists($codefile = libfile('seccode/'.$_G['setting']['seccodedata']['type'], 'class'))) {
				@include_once $codefile;
				$class = 'seccode_'.$_G['setting']['seccodedata']['type'];
				if(class_exists($class)) {
					$code = new $class();
					$code->setting = $_G['setting']['seccodedata']['extra'][$_G['setting']['seccodedata']['type']];
					if(method_exists($code, 'check')) {
						return $code->check($value, $idhash);
					}
				}
			}
			return false;
		}
		if(!isset($_G['cookie']['seccode'.$idhash])) {
			return false;
		}
		list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['seccode'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
		return $checkvalue == strtoupper($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
	}

	public static function check_secqaa($value, $idhash) {
		global $_G;
		if(!$_G['setting']['secqaa']) {
			return true;
		}
		if(!isset($_G['cookie']['secqaa'.$idhash])) {
			return false;
		}
		loadcache('secqaa');
		list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
		return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
	}


	public static function get_url_list($message) {
		$return = array();

		(strpos($message, '[/img]') || strpos($message, '[/flash]')) && $message = preg_replace("/\[img[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[flash[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", '', $message);
		if(preg_match_all("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^ \[\]\"']+/i", $message, $urllist)) {
			foreach($urllist[0] as $key => $val) {
				$val = trim($val);
				$return[0][$key] = $val;
				if(!preg_match('/^http:\/\//is', $val)) $val = 'http://'.$val;
				$tmp = parse_url($val);
				$return[1][$key] = $tmp['host'];
				if($tmp['port']){
					$return[1][$key] .= ":$tmp[port]";
				}
			}
		}
		return $return;
	}

	public static function updatemoderate($idtype, $ids, $status = 0) {
		$ids = is_array($ids) ? $ids : array($ids);
		if(!$ids) {
			return;
		}
		if(!$status) {
			foreach($ids as $id) {
				C::t('common_moderate')->insert($idtype, array(
					'id' => $id,
					'status' => 0,
					'dateline' => TIMESTAMP,
				), false, true);
			}
		} elseif($status == 1) {
			C::t('common_moderate')->update($ids, $idtype, array('status' => 1));
		} elseif($status == 2) {
			C::t('common_moderate')->delete($ids, $idtype);
		}
	}
}

?>