<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: check.php 31281 2012-08-03 02:29:27Z zhangjie $
 */

chdir('../../../');

define('APPTYPEID', 127);
define('CURSCRIPT', 'plugin');

$_GET['mobile'] = 'no';

require './source/class/class_core.php';
require './source/plugin/mobile/mobile.class.php';
if(!defined('DISCUZ_VERSION')) {
    require './source/discuz_version.php';
}

$discuz = C::app();

$discuz->init();

$setting = array();
$allowsetting = array('closeforumorderby');
if(is_array($_GET['setting'])) {
	$settings = array_intersect($_GET['setting'], $allowsetting);
} else {
	$settings = $allowsetting;
}
foreach($settings as $v) {
	$setting[$v] = $_G['setting'][$v];
}

if(in_array('mobile', $_G['setting']['plugins']['available'])) {
	if(is_array($_GET['setting'])) {
		$array = array(
			'setting' => $setting,
		);
	} else {
		$extendsetting = C::t('#mobile#mobile_setting')->fetch_all(array(
			'extend_used',
			'extend_lastupdate'
		));
		$array = array(
			'discuzversion' => DISCUZ_VERSION,
			'charset' => CHARSET,
			'version' => MOBILE_PLUGIN_VERSION,
			'pluginversion' => $_G['setting']['plugins']['version']['mobile'],
			'regname' => $_G['setting']['regname'],
			'qqconnect' => in_array('qqconnect', $_G['setting']['plugins']['available']) ? '1' : '0',
			'sitename' => $_G['setting']['bbname'],
			'mysiteid' => $_G['setting']['my_siteid'],
			'ucenterurl' => $_G['setting']['ucenterurl'],
			'setting' => $setting,
			'extends' => array('used' => $extendsetting['extend_used'], 'lastupdate' => $extendsetting['extend_lastupdate']),
		);
	}
} else {
	$array = array();
}
mobile_core::result($array);

?>