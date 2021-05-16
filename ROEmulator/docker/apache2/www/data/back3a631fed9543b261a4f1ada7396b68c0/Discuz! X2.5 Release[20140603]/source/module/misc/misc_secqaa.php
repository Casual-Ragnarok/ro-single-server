<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_secqaa.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/seccode');

if($_GET['action'] == 'update') {

	$refererhost = parse_url($_SERVER['HTTP_REFERER']);
	$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

	if($refererhost['host'] != $_SERVER['HTTP_HOST']) {
		exit('Access Denied');
	}

	$message = '';
	if($_G['setting']['secqaa']) {
		$question = make_secqaa($_GET['idhash']);
	}
	include template('common/header_ajax');
	echo lang('core', 'secqaa_tips').$question;
	include template('common/footer_ajax');

} elseif($_GET['action'] == 'check') {

	include template('common/header_ajax');
	echo check_secqaa($_GET['secverify'], $_GET['idhash']) ? 'succeed' : 'invalid';
	include template('common/footer_ajax');

}

?>