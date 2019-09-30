<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ftn_cross.inc.php 29265 2012-03-31 06:03:26Z yexinhao $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$storageService = Cloud::loadClass('Service_Storage');

if(empty($_GET['ftn_formhash']) || empty($_G['uid']) || empty($_GET['filesize']) || empty($_GET['sha1']) || empty($_GET['filename'])){
	if(empty($_GET['allcount']) && empty($_GET['uploadedcount']) && empty($_GET['errorcount'])){
		exit;
	} else {
		if($_GET['allcount'] == ($_GET['uploadedcount']+$_GET['errorcount'])){
			$allowUpdate = 1;
		} else {
			$allowUpdate = 0;
		}
		include template('xf_storage:cross');
	}
} elseif($_GET['ftn_formhash'] != $storageService->ftnFormhash()){
	exit;//showmessage('操作超时或者数据来源错误','','error');
}

if($_GET['ftn_submit']) {

	$data = array();$index = array();
	$filesize = intval($_GET['filesize']);
	$filename = diconv(trim($_GET['filename']),'UTF-8');
	$filename = str_replace(array('\'','"','\/','\\','<','>'),array('','','','','',''),$filename);
	$sha = trim($_GET['sha1']);
	$index = array(
		'tid' => 0,
		'pid' => 0,
		'uid' => $_G['uid'],
		'tableid' => '127',
		'downloads' => 0
	);
	$aid = C::t('forum_attachment')->insert($index, 1);

	$data = array(
		'aid' => $aid,
		'uid' => $_G['uid'],
		'dateline' => $_G['timestamp'],
		'filename' => $filename,
		'filesize' => $filesize,
		'attachment' => 'storage:' . $sha,
		'remote' => 0,
		'isimage' => 0,
		'width' => 0,
		'thumb' => 0,
	);
	C::t('forum_attachment_unused')->insert($data);
	if(empty($_GET['allcount']) && empty($_GET['uploadedcount']) && empty($_GET['errorcount'])){
		exit;
	} else {
		if($_GET['allcount'] == ($_GET['uploadedcount'] + $_GET['errorcount'])){
			$allowUpdate = 1;
		} else {
			$allowUpdate = 0;
		}
		include template('xf_storage:cross');
	}
}