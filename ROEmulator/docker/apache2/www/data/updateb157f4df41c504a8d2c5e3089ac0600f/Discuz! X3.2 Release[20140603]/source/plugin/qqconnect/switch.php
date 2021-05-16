<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.php 34009 2013-09-18 07:36:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$appIdentifier = 'connect';
$pluginid = intval($_GET['pluginid']);

require_once libfile('class/cloudregister');

if($operation == 'enable') {

	new Cloud_Register($appIdentifier, $pluginid, 'appOpenFormView');
	$_G['setting']['plugins']['available'][] = 'qqconnect';
	$_G['setting']['plugins']['version']['qqconnect'] = $pluginarray['plugin']['version'];

} elseif($operation == 'disable') {

	new Cloud_Register($appIdentifier, $pluginid, 'appCloseReasonsView');
	foreach($_G['setting']['plugins']['available'] as $_k => $_v) {
		if($_v == 'qqconnect') {
			unset($_G['setting']['plugins']['available'][$_k]);
		}
	}

}

updatecache('mobile:mobile');