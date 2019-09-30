<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_imgcropper.php 30074 2012-05-09 03:04:37Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


if(!submitcheck('imgcroppersubmit')) {
	if($_GET['op'] == 'loadcropper') {
		$cboxwidth = $_GET['width'] > 50 ? intval($_GET['width']) : 300;
		$cboxheight = $_GET['height'] > 50 ? intval($_GET['height']) : 300;

		$cbgboxwidth = $cboxwidth + 300;
		$cbgboxheight = $cboxheight + 300;
		$dragpt = ($cbgboxwidth - $cboxwidth)/2;
		$dragpl = ($cbgboxheight - $cboxheight)/2;
	} else {
		$prefix = $_GET['picflag'] == 2 ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
	}
	include_once template("common/misc_imgcropper");
} else {
	$cropfile = md5($_GET['cutimg']).'.jpg';
	$ictype = $_GET['ictype'];

	if($ictype == 'block') {
		require_once libfile('function/block');
		$block = C::t('common_block')->fetch($_GET['bid']);
		$cropfile = block_thumbpath($block, array('picflag' => intval($_GET['picflag']), 'pic' => $_GET['cutimg']));
		$cutwidth = $block['picwidth'];
		$cutheight = $block['picheight'];
	} else {
		$cutwidth = $_GET['cutwidth'];
		$cutheight = $_GET['cutheight'];
	}
	$top = intval($_GET['cuttop'] < 0 ? 0 : $_GET['cuttop']);
	$left = intval($_GET['cutleft'] < 0 ? 0 : $_GET['cutleft']);
	$picwidth = $cutwidth > $_GET['picwidth'] ? $cutwidth : $_GET['picwidth'];
	$picheight = $cutheight > $_GET['picheight'] ? $cutheight : $_GET['picheight'];

	require_once libfile('class/image');
	$image = new image();
	$prefix = $_GET['picflag'] == 2 ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
	if(!$image->Thumb($prefix.$_GET['cutimg'], $cropfile, $picwidth, $picheight)) {
		showmessage('imagepreview_errorcode_'.$image->errorcode, null, null, array('showdialog' => true, 'closetime' => true));
	}
	$image->Cropper($image->target, $cropfile, $cutwidth, $cutheight, $left, $top);
	showmessage('do_success', dreferer(), array('icurl' => $cropfile), array('showdialog' => true, 'closetime' => true));
}

?>