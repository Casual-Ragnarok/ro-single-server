<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: qrcode.inc.php 34550 2014-05-27 08:32:49Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dir = DISCUZ_ROOT.'./data/cache/qrcode/';

$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

if($_GET['access']) {
	require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
	$url = wsq::$WSQ_DOMAIN.'siteid='.$_G['wechat']['setting']['wsq_siteid'].'&c=index&a=';
	if($_GET['threadqr']) {
		$tid = dintval($_GET['threadqr']);
		include_once template('wechat:wechat_threadqr');
	} elseif($_GET['tid']) {
		$tid = dintval($_GET['tid']);
		require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
		QRcode::png($url.'viewthread&tid='.$_GET['tid'].'&source=pcscan', false, QR_ECLEVEL_Q, 4);
	} elseif($_GET['fid']) {
		$fid = dintval($_GET['fid']);
		$file = $dir.'qr_'.$fid.'.jpg';
		if(!file_exists($file) || !filesize($file)) {
			dmkdir($dir);
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($url.'index&fid='.$_GET['fid'].'&source=pcscan', $file, QR_ECLEVEL_Q, 2);
		}
		dheader('Content-Disposition: inline; filename=qrcode_'.$fid.'.jpg');
		dheader('Content-Type: image/pjpeg');
		@readfile($file);
	} else {
		$file = $dir.'qr_index.jpg';
		if(!file_exists($file) || !filesize($file)) {
			dmkdir($dir);
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($url.'index&source=pcscan', $file, QR_ECLEVEL_Q, 2);
		}
		dheader('Content-Disposition: inline; filename=qrcode_index.jpg');
		dheader('Content-Type: image/pjpeg');
		@readfile($file);
	}
	exit;
}

require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);
list($ticket, $code) = explode("\t", authcode($_G['cookie']['wechat_ticket'], 'DECODE'));

if($ticket) {
	$file = $dir.md5($ticket).'_'.$code.'.jpg';
	if(!file_exists($file) || !filesize($file)) {
		dmkdir($dir);
		$qrcode = dfsockopen($wechat_client->getQrcodeImgUrlByTicket($ticket));
		$fp = @fopen($file, 'wb');
		@fwrite($fp, $qrcode);
		@fclose($fp);
	}
	dheader('Content-Disposition: inline; filename=qrcode.jpg');
	dheader('Content-Type: image/pjpeg');
	@readfile($file);
}