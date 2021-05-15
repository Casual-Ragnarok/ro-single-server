<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: js.php 25510 2011-11-14 02:22:26Z yexinhao $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

require_once('../../source/class/class_core.php');
require_once('../../source/function/function_home.php');

$cachelist = array();
$discuz = C::app();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = $_GET['module'] == 'Search' ? true : false;
$discuz->init_session = false;
$discuz->init();

$js = "if(!window['ManYou']) {window['ManYou']={};}\n";
$id = $_GET['module'] . '_' . $_GET['method'];
$js .= "if(!window['ManYou.$id']) {window['ManYou.$id']={};}\n";

if(empty($_G['setting']['my_siteid']) || empty($_G['setting']['my_sitekey'])) {
	echo $js . 'ManYou.' . $id . '={status:"error", result: "plug-in has not been opened"};';
	exit;
}

$jsapi = new JSAPI();
$result = $jsapi->request_parse();
$json = $jsapi->response_format($result);
echo $js . "ManYou.$id=" . $json;


class JSAPI {
	function request_check() {
		return true;
		global $_G;
		$module = $_GET['module'];
		$method = $_GET['method'];
		$params = (array) $_GET['params'];

		ksort($params);
		$args   = $delimiter = '';
		foreach ($params as $k => $v) {
			$args .= $delimiter . "params[$k]" . '=' . $v ;
			$delimiter = '&';
		}

		$sig    = $_GET['sig'];
		$secret = md5($module . '|'. $method . '|' . $args . '|' . $_GET['ts'] .'|'. $_GET['salt'] . '|' . $_G['setting']['my_sitekey']);
		if ($sig != $secret) {
			return array('result' => 'error', 'reason' => 'signature error');
		}

		return true;
	}

	function request_parse() {
		$module  = $_GET['module'];
		$method  = $_GET['method'];
		$params  = $_GET['params'];

		$res = $this->request_check();
		if ($res === true) {
			$clazz = $module . '.' . $method;
			switch($clazz) {
				case 'Thread.getReplyAndView' :
					$args = array($params['tids']);
					break;
				case 'Search.getHeader' :
					$args = array();
					break;
				default :
					return array('status' => 'error', 'result' => 'unknown method' . $clazz);
			}

			$result = call_user_func_array(array(&$module, $method), $args);
			$res = array('status' => 'ok', 'result' => $result);
		}

		return $res;
	}

	function response_format($result) {
		if(function_exists('json_encode')) {
			$json = json_encode($result);
		} elseif(function_exists('mb_internal_encoding')) {
			$json = $this->php2json($result);
		} else {
			$json = '{"status":"error","result":"unsuport json_encode or mb_internal_encoding"}';
		}
		return $json;
	}

	function json_encode_string($in_str) {
		mb_internal_encoding("UTF-8");
		$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
		$str = "";
		for($i=mb_strlen($in_str)-1; $i>=0; $i--) {
			$mb_char = mb_substr($in_str, $i, 1);
			if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
				$str = sprintf("\\u%04x", $match[1]) . $str;
			} else {
				$str = $mb_char . $str;
			}
		}
		return $str;
	}

	function _array2json($array) {
		$piece = array();
		foreach ($array as $k => $v) {
			$piece[] = $k . ':' . $this->php2json($v);
		}

		if ($piece) {
			$json = '{' . implode(',', $piece) . '}';
		} else {
			$json = '[]';
		}
		return $json;
	}

	function php2json($value) {
		if (is_array($value)) {
			return $this->_array2json($value);
		}
		if (is_string($value)) {
			$value = str_replace(array("\n", "\t"), array(), $value);
			$value = addslashes($value);
			$value = $this->json_encode_string($value);
			return '"'.$value.'"';
		}
		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}
		if (is_null($value)) {
			return 'null';
		}

		return $value;
	}

}

Cloud::loadFile('Service_SearchHelper');
class Thread {

	function getReplyAndView($tids) {
		if (!$tids) {
			return array();
		}

		$tids = explode(',', $tids);
		$res = array();
		$threads = Cloud_Service_SearchHelper::getThreads($tids);
		foreach($threads as $thread) {
			$res[$thread['tId']] = array('tid' => $thread['tId'],
										 'replies' => $thread['replyNum'],
										 'views' => $thread['viewNum'],
										);
		}
		return $res;
	}
}

class Search {

	function getReplyAndView($tids) {
		if (!$tids) {
			return array();
		}
		$res = array();
		$threads = Cloud_Service_SearchHelper::getThreads($tids);
		foreach($threads as $thread) {
			$res[$thread['tId']] = array('tid' => $thread['tId'],
										 'replies' => $thread['replyNum'],
										 'views' => $thread['viewNum'],
										);
		}
		return $res;
	}

	function getHeader() {
		global $_G;

		$leftHtmlCode = '<div id="navs-wraper-v2" class="v2" onmouseover="document.getElementById(\'navs-menu\').style.display=\'block\'" onmouseout="document.getElementById(\'navs-menu\').style.display=\'none\'">';
		$leftHtmlCode .= '<p id="return-homepage"><a href="'.(!empty($_G['setting']['defaultindex']) ? $_G['setting']['defaultindex'] : 'forum.php').'">' . lang('home/template', 'return_homepage') . '</a></p>' . "\n";
		$leftHtmlCode .= "<ul id=\"navs-menu\">\n";
		foreach($_G['setting']['navs'] as $navsid => $nav) {
			$nav['nav'] = '<li ' . $nav['nav'] . '></li>';
			if($nav['available']) {
				if($navsid == 6 && !empty($_G['setting']['plugins']['jsmenu'])) {
					$leftHtmlCode .= "\t$nav[nav]\n";
				} else {
					if(!$nav['level'] || ($nav['level'] == 1 && $_G['uid']) || ($nav['level'] == 2 && $_G['adminid'] > 0) || ($nav['level'] == 3 && $_G['adminid'] == 1)) {
						$leftHtmlCode .= "\t$nav[nav]\n";
					}
				}
			}
		}
		$leftHtmlCode .= "</ul></div>\n";

		$rightHtmlCode = "<p>\n";
		if($_G['uid']) {
			$rightHtmlCode .= "\t<strong><a href=\"home.php?mod=space\" class=\"noborder\" target=\"_blank\">{$_G[member][username]}</a></strong>\n";
			if($_G['group']['allowinvisible']) {
				$rightHtmlCode .= "<span id=\"loginstatus\" class=\"xg1\"><a href=\"member.php?mod=switchstatus\" title=\"".lang('template', 'login_switch_invisible_mode')."\">";
				if($_G['session']['invisible']) {
					$rightHtmlCode .= lang('template', 'login_invisible_mode');
				} else {
					$rightHtmlCode .= lang('template', 'login_normal_mode');
				}
				$rightHtmlCode .= "</a></span>\n";
			}
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"home.php?mod=space&do=home\">".lang('template', 'my_space')."</a>\n";
			$rightHtmlCode .= "\t<span class=\"xg1\"><a href=\"home.php?mod=spacecp\">".lang('template', 'setup')."</a></span>\n";
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"home.php?mod=space&do=notice\" id=\"myprompt\"".($_G['member']['newprompt'] ? ' class="new"' : '').">".lang('template', 'notice').($_G['member']['newprompt'] ? '('.$_G['member']['newprompt'].')' : '')."</a><span id=\"myprompt_check\"></span>\n";
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"home.php?mod=space&do=pm\" id=\"pm_ntc\"".($_G['member']['newpm'] ? ' class="new"' : '').">".lang('template', 'pm_center').($_G['member']['newpm'] ? '('.$_G['member']['newpm'].')' : '')."</a>\n";
			if($_G['group']['allowmanagearticle'] || $_G['group']['allowdiy']) {
				$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"portal.php?mod=portalcp\" target=\"_blank\">".lang('template', 'portal_manage')."</a>\n";
			}
			if($_G['uid'] && $_G['adminid'] > 1) {
				$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"forum.php?mod=modcp&fid=$_G[fid]\" target=\"_blank\">".$_G['setting']['navs']['2']['navname'].lang('template', 'manage')."</a>\n";
			}
			if($_G['uid'] && ($_G['adminid'] == 1 || $_G['member']['allowadmincp'])) {
				$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"admin.php\" target=\"_blank\">".lang('template', 'admincp')."</a>\n";
			}
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"member.php?mod=logging&action=logout&formhash=".FORMHASH."\">".lang('template', 'logout')."</a>\n";
		} elseif(!empty($_G['cookie']['loginuser'])) {
			$rightHtmlCode .= "\t<strong><a id=\"loginuser\" class=\noborder\">".$_G['cookie']['loginuser']."</a></strong>\n";
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"member.php?mod=logging&action=login\">".lang('template', 'activation')."</a>\n";
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"member.php?mod=logging&action=logout&formhash={FORMHASH}\">".lang('template', 'logout')."</a>\n";
		} else {
			$rightHtmlCode .= "\t<a href=\"member.php?mod=".$_G['setting']['regname']."\" class=\"noborder\">".$_G['setting']['reglinkname']."</a>\n";
			$rightHtmlCode .= "\t<span class=\"pipe\">|</span><a href=\"member.php?mod=logging&action=login\">".lang('template', 'login')."</a>\n";
		}
		$rightHtmlCode .= "\t</p>\n";
		$leftHtmlCode = urlcovert($leftHtmlCode, $_G['siteurl']);
		$rightHtmlCode = urlcovert($rightHtmlCode, $_G['siteurl']);
		if(strtolower($_G['config']['output']['charset']) != 'utf-8') {
			require_once libfile('class/chinese');
			$chinese = new Chinese($_G['config']['output']['charset'], 'utf-8', true);
			$leftHtmlCode = $chinese->Convert($leftHtmlCode);
			$rightHtmlCode = $chinese->Convert($rightHtmlCode);
		}
		$ret = array('left' => $leftHtmlCode, 'right' => $rightHtmlCode);
		return $ret;
	}

}

function urlcovert($html, $siteurl) {
	if(preg_match_all("/\s+href=\"(.+?)\"/is", $html, $match)) {
		foreach($match[1] as $key => $val) {
			if(preg_match('/^http:\/\//is', $val) || $val == 'javascript:;' || $val{0} == '#') continue;
			$html = str_replace($match[0][$key], ' href="'.$siteurl.$val.'"', $html);
		}
	}
	return $html;
}