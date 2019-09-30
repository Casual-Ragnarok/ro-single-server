<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.php 33950 2013-09-05 02:17:27Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$appIdentifier = 'security';
$pluginid = intval($_GET['pluginid']);

require_once libfile('class/cloudregister');

if($operation == 'enable') {

	new Cloud_Register($appIdentifier, $pluginid, 'appOpenFormView');

} elseif($operation == 'disable') {

	new Cloud_Register($appIdentifier, $pluginid, 'appCloseReasonsView');

}