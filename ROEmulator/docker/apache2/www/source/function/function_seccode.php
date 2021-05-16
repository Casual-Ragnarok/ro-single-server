<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_seccode.php 26635 2011-12-19 01:59:13Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function make_seccode($idhash){
	global $_G;
	$seccode = random(6, 1);
	$seccodeunits = '';
	if($_G['setting']['seccodedata']['type'] == 1) {
		$lang = lang('seccode');
		$len = strtoupper(CHARSET) == 'GBK' ? 2 : 3;
		$code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
		$seccode = '';
		for($i = 0; $i < 2; $i++) {
			$seccode .= substr($lang['chn'], $code[$i] * $len, $len);
		}
	} elseif($_G['setting']['seccodedata']['type'] == 3) {
		$s = sprintf('%04s', base_convert($seccode, 10, 20));
		$seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
	} else {
		$s = sprintf('%04s', base_convert($seccode, 10, 24));
		$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	}
	if($seccodeunits) {
		$seccode = '';
		for($i = 0; $i < 4; $i++) {
			$unit = ord($s{$i});
			$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
		}
	}
	dsetcookie('seccode'.$idhash, authcode(strtoupper($seccode)."\t".(TIMESTAMP - 180)."\t".$idhash."\t".FORMHASH, 'ENCODE', $_G['config']['security']['authkey']), 0, 1, true);
	return $seccode;
}

function make_secqaa($idhash) {
	global $_G;
	loadcache('secqaa');
	$secqaakey = max(1, random(1, 1));
	if($_G['cache']['secqaa'][$secqaakey]['type']) {
		if(file_exists($qaafile = libfile('secqaa/'.$_G['cache']['secqaa'][$secqaakey]['question'], 'class'))) {
			@include_once $qaafile;
			$class = 'secqaa_'.$_G['cache']['secqaa'][$secqaakey]['question'];
			if(class_exists($class)) {
				$qaa = new $class();
				if(method_exists($qaa, 'make')) {
					$_G['cache']['secqaa'][$secqaakey]['answer'] = md5($qaa->make($_G['cache']['secqaa'][$secqaakey]['question']));
				}
			}
		}
	}
	dsetcookie('secqaa'.$idhash, authcode($_G['cache']['secqaa'][$secqaakey]['answer']."\t".(TIMESTAMP - 180)."\t".$idhash."\t".FORMHASH, 'ENCODE', $_G['config']['security']['authkey']), 0, 1, true);
	return $_G['cache']['secqaa'][$secqaakey]['question'];
}

?>