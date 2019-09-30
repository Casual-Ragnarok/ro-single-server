<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: soso.class.php 34306 2014-01-17 04:31:33Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_soso_smilies_base {
	function _soso_smiles($smilieid = '', $maxsmilies = -1, $pid = 0, $imgcode = 0) {
		static $smiliecount;
		$imgsrc = '';
		$pid = intval($pid);
		$maxsmilies = intval($maxsmilies);
		$smilieid = $smiliekey = (string) $smilieid;
		$imgid = "soso_{$smilieid}";
		if($maxsmilies == 0) {
			return "{:soso_$smilieid:}";
		}
		if(strpos($smilieid, '_') === 0) {
			$realsmilieid = $smiliekey = substr($smilieid, 1, -2);
			$serverid = intval(substr($smilieid, -1));
			$imgsrc = "http://imgstore0{$serverid}.cdn.sogou.com/app/a/100520032/{$realsmilieid}";
		} elseif(strpos($smilieid, 'e') === 0) {
			$imgsrc = "http://imgstore01.cdn.sogou.com/app/a/100520032/{$smilieid}";
		} else {
			return "{:soso_$smilieid:}";
		}
		if($maxsmilies > 0) {
			if(!isset($smiliecount)) {
				$smiliecount = array();
			}
			$smiliekey = "{$pid}_{$smiliekey}";
			if(empty($smiliecount[$smiliekey])) {
				$smiliecount[$smiliekey] = 1;
			} else {
				$smiliecount[$smiliekey]++;
			}
			if($smiliecount[$smiliekey] > $maxsmilies) {
				return "{:soso_$smilieid:}";
			}
		}
		if($imgcode) {
			return "[img]{$imgsrc}[/img]";
		} else {
			return "<img class=\"s\" src=\"{$imgsrc}\" smilieid=\"{$imgid}\" border=\"0\" alt=\"\" />";
		}
	}
}

class plugin_soso_smilies extends plugin_soso_smilies_base {

	function global_footer() {
		global $_G;
		if(CURSCRIPT == 'home' && !empty($_G['uid'])) {
			if($_GET['ac'] == 'pm' && $_GET['mod'] == 'spacecp') {
				if(empty($_GET['op'])) {
					return $this->_soso_script('send');
				} elseif($_GET['op'] == 'showmsg') {
					return $this->_soso_script('pm');
				}
			} elseif($_GET['subop'] == 'view' && $_GET['do'] == 'pm' && $_GET['mod'] == 'space') {
				return $this->_soso_script('reply');
			}
		}
		return '';
	}

	function discuzcode($param) {
		global $_G;
		if($param['caller'] == 'discuzcode') {
			$smileyoff = $param['param'][1];
			$allowsmilies = $param['param'][4];
			$pid = $param['param'][12];
			if(!$smileyoff && $allowsmilies && strpos($_G['discuzcodemessage'], '{:soso_') !== false) {
				$_G['discuzcodemessage'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", "'.$_G['setting']['maxsmilies'].'", "'.$pid.'")', $_G['discuzcodemessage'], $_G['setting']['maxsmilies']);
			}
		} else {
			$_G['discuzcodemessage'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/", '', $_G['discuzcodemessage']);
		}
	}

	function _soso_script($textareaid) {
		return '<script type="text/javascript" src="source/plugin/soso_smilies/js/soso_smilies.js?'.VERHASH.'" charset="utf-8"></script>'.
			'<script type="text/javascript" charset="utf-8">SOSO_EXP_CHECK("'.$textareaid.'");</script>';
	}

}

class plugin_soso_smilies_forum extends plugin_soso_smilies {

	function forumdisplay_bottom_output($template = array()) {
		global $_G;
		if(!empty($_G['uid'])) {
			return $this->_soso_script('fastpost');
		}
	}

	function viewthread_fastpost_content_output($template = array()) {
		global $_G;
		if(!empty($_G['uid'])) {
			return $this->_soso_script('fastpost');
		}
	}

	function post_smileyoff() {
		global $_G;
		if(!empty($_GET['message'])) {
			$_G['cache']['smileycodes'][] = '{:soso_';
		}
		return '';
	}

	function post_bottom_output($template = array()) {
		global $_G;
		if(!empty($_G['uid'])) {
			return $this->_soso_script('newthread');
		}
	}

}

class plugin_soso_smilies_home extends plugin_soso_smilies {

	function spacecp_profile_bottom_output() {
		global $_G;
		if(!empty($_G['uid'])) {
			return $this->_soso_script('sightml');
		}
	}

	function spacecp_profile_sightml() {
		global $_G;
		if($_GET['ac'] == 'profile' && submitcheck('profilesubmitbtn') && !empty($_POST['sightml'])) {
			$_POST['sightml'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", -1, 0, 1)', $_POST['sightml'], $_G['setting']['maxsmilies']);
		}
	}

	function spacecp_pm_output() {
		global $_G;
		if(!empty($GLOBALS['msglist'])) {
			foreach($GLOBALS['msglist'] as $day => $result) {
				foreach($result as $key => $value) {
					$GLOBALS['msglist'][$day][$key]['message'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", -1, 0, 0)', $GLOBALS['msglist'][$day][$key]['message'], $_G['setting']['maxsmilies']);
				}
			}
		} elseif($_GET['op'] == 'showchatmsg' && $GLOBALS['list']) {
			foreach($GLOBALS['list'] as $key => $value) {
				$GLOBALS['list'][$key]['message'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", -1, 0, 0)', $GLOBALS['list'][$key]['message'], $_G['setting']['maxsmilies']);
			}
		}
	}

	function space_pm_output() {
		global $_G;
		if(!empty($GLOBALS['list'])) {
			foreach($GLOBALS['list'] as $key => $value) {
				if(!empty($_GET['subop'])) {
					$GLOBALS['list'][$key] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", -1, 0, 0)', $GLOBALS['list'][$key], $_G['setting']['maxsmilies']);
				} else {
					$GLOBALS['list'][$key] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/", '', $GLOBALS['list'][$key], $_G['setting']['maxsmilies']);
				}
			}
		}
	}

	function follow_soso_output() {
		global $_G;
		if(!empty($GLOBALS['list']['content'])) {
			foreach($GLOBALS['list']['content'] as $key => $value) {
				$GLOBALS['list']['content'][$key]['content'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/e", '$this->_soso_smiles("\\1", -1, 0, 0)', $GLOBALS['list']['content'][$key]['content'], $_G['setting']['maxsmilies']);
			}
		}
	}

}

class mobileplugin_soso_smilies extends plugin_soso_smilies_base {

	function discuzcode($param) {
		global $_G;
		$_G['discuzcodemessage'] = preg_replace("/\{\:soso_((e\d+)|(_\d+_\d))\:\}/", '', $_G['discuzcodemessage']);
	}

}