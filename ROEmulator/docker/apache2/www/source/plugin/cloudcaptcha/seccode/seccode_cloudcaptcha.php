<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: seccode_cloudcaptcha.php 34041 2013-09-24 09:48:15Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class seccode_cloudcaptcha {

	var $version = '1.0';
	var $name = 'cloudcaptcha_name';
	var $description = '';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';

	function check($value, $idhash, $seccheck, $fromjs, $modid) {
		global $_G;
		if(!$_G['setting']['my_siteid']) {
			return false;
		}
		$sig = $_G['cookie']['dcaptchasig'];
		$data = captcha::validate($value, $sig, $fromjs, $modid);
		return $data == '{"errCode":0}';
	}

	function make($idhash, $modid) {
		global $_G;
		if(!$_G['setting']['my_siteid']) {
			return;
		}
		$rand = random(10);
		$src = 'plugin.php?id=cloudcaptcha:get&rand='.$rand.'&modid='.$modid;
		$tips = lang('core', 'seccode_image_tips');
		echo '<span id="seccode_js'.$idhash.'"></span><script type="text/javascript" src="http://discuz.gtimg.cn/cloud/scripts/captcha.js?version='.CLOUDCAPTCHA_VER.'"></script>'.
		    '<script type="text/javascript" reload="1">'.
		    'var refresh = $(\'seccode_'.$idhash.'\').innerHTML ? 1 : 0;'.
		    'var cloudCaptchaTimer = setInterval(function(){if(typeof cloudCaptcha != "undefined"){'.
		    'clearInterval(cloudCaptchaTimer);'.
		    'cloudCaptcha.run("'.$src.'&refresh=" + refresh, "'.$idhash.'", "'.$tips.'");}}, 50);</script>';
	}

	function image($idhash, $modid) {
		global $_G;
		if(!$_G['setting']['my_siteid']) {
			return;
		}
		$rand = random(10);
		return $_G['siteurl'].'plugin.php?id=cloudcaptcha:get&rand='.$rand.'&modid='.$modid;
	}

}

?>