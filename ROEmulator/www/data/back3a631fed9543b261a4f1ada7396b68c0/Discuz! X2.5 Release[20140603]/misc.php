<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 24842 2011-10-12 09:51:37Z zhengqingpeng $
 */

define('APPTYPEID', 100);
define('CURSCRIPT', 'misc');


require './source/class/class_core.php';

$discuz = C::app();

$discuz->reject_robot();
$modarray = array('seccode', 'secqaa', 'initsys', 'invite', 'faq', 'report', 'swfupload', 'manyou', 'stat', 'ranklist', 'buyinvitecode', 'tag', 'diyhelp', 'mobile', 'patch', 'getatuser', 'imgcropper');

$modcachelist = array(
	'ranklist' => array('forums', 'diytemplatename'),
);

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;

if(in_array($mod, array('seccode', 'secqaa', 'initsys', 'faq', 'swfupload', 'mobile'))) {
	define('ALLOWGUEST', 1);
}

$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}

$discuz->cachelist = $cachelist;

switch ($mod) {
	case 'secqaa':
	case 'manyou':
	case 'seccode':
		$discuz->init_cron = false;
		$discuz->init_session = false;
		break;
	case 'updatecache':
		$discuz->init_cron = false;
		$discuz->init_session = false;
	default:
		break;
}

$discuz->init();

define('CURMODULE', $mod);
runhooks();

require DISCUZ_ROOT.'./source/module/misc/misc_'.$mod.'.php';

?>