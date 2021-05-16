<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 31159 2012-07-20 04:27:18Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['uid'] = intval($_POST['uid']);
if((empty($_G['uid']) && $_GET['operation'] != 'upload') || $_POST['hash'] != md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid'])) {
	exit();
} else {
	if($_G['uid']) {
		$_G['member'] = getuserbyuid($_G['uid']);
	}
	$_G['groupid'] = $_G['member']['groupid'];
	loadcache('usergroup_'.$_G['member']['groupid']);
	$_G['group'] = $_G['cache']['usergroup_'.$_G['member']['groupid']];
}

if($_GET['operation'] == 'upload') {

	if(empty($_GET['simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
		$_FILES['Filedata']['type'] = $_GET['filetype'];
	}
	$forumattachextensions = '';
	$fid = intval($_GET['fid']);
	if($fid) {
		$forum = $fid != $_G['fid'] ? C::t('forum_forum')->fetch_info_by_fid($fid) : $_G['forum'];
		if($forum['status'] == 3 && $forum['level']) {
			$levelinfo = C::t('forum_grouplevel')->fetch($forum['level']);
			if($postpolicy = $levelinfo['postpolicy']) {
				$postpolicy = dunserialize($postpolicy);
				$forumattachextensions = $postpolicy['attachextensions'];
			}
		} else {
			$forumattachextensions = $forum['attachextensions'];
		}
		if($forumattachextensions) {
			$_G['group']['attachextensions'] = $forumattachextensions;
		}
	}
	$upload = new forum_upload();
} elseif($_GET['operation'] == 'album') {


	$showerror = true;
	if(helper_access::check_module('album')) {
		require_once libfile('function/spacecp');
		if($_FILES["Filedata"]['error']) {
			$file = lang('spacecp', 'file_is_too_big');
		} else {
			require_once libfile('function/home');
			$_FILES["Filedata"]['name'] = addslashes(diconv(urldecode($_FILES["Filedata"]['name']), 'UTF-8'));
			$file = pic_save($_FILES["Filedata"], 0, '', true, 0);
			if(!empty($file) && is_array($file)) {
				$url = pic_get($file['filepath'], 'album', $file['thumb'], $file['remote']);
				$bigimg = pic_get($file['filepath'], 'album', 0, $file['remote']);
				echo "{\"picid\":\"$file[picid]\", \"url\":\"$url\", \"bigimg\":\"$bigimg\"}";
				$showerror = false;
			}
		}
	}
	if($showerror) {
		echo "{\"picid\":\"0\", \"url\":\"0\", \"bigimg\":\"0\"}";
	}


} elseif($_GET['operation'] == 'portal') {

	$aid = intval($_POST['aid']);
	$catid = intval($_POST['catid']);
	$msg = '';
	$errorcode = 0;
	require_once libfile('function/portalcp');
	if($aid) {
		$article = C::t('portal_article_title')->fetch($aid);
		if(!$article) {
			$errorcode = 1;
		}

		if(check_articleperm($catid, $aid, $article, false, true) !== true) {
			$errorcode = 2;
		}

	} else {
		if(check_articleperm($catid, $aid, null, false, true) !== true) {
			$errorcode = 3;
		}
	}

	$upload = new discuz_upload();

	$_FILES["Filedata"]['name'] = addslashes(diconv(urldecode($_FILES["Filedata"]['name']), 'UTF-8'));
	$upload->init($_FILES['Filedata'], 'portal');
	$attach = $upload->attach;
	if(!$upload->error()) {
		$upload->save();
	}
	if($upload->error()) {
		$errorcode = 4;
	}
	if(!$errorcode) {
		if($attach['isimage'] && empty($_G['setting']['portalarticleimgthumbclosed'])) {
			require_once libfile('class/image');
			$image = new image();
			$thumbimgwidth = $_G['setting']['portalarticleimgthumbwidth'] ? $_G['setting']['portalarticleimgthumbwidth'] : 300;
			$thumbimgheight = $_G['setting']['portalarticleimgthumbheight'] ? $_G['setting']['portalarticleimgthumbheight'] : 300;
			$attach['thumb'] = $image->Thumb($attach['target'], '', $thumbimgwidth, $thumbimgheight, 2);
			$image->Watermark($attach['target'], '', 'portal');
		}

		if(getglobal('setting/ftp/on') && ((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['size'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
			if(ftpcmd('upload', 'portal/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'portal/'.getimgthumbname($attach['attachment'])))) {
				@unlink($_G['setting']['attachdir'].'/portal/'.$attach['attachment']);
				@unlink($_G['setting']['attachdir'].'/portal/'.getimgthumbname($attach['attachment']));
				$attach['remote'] = 1;
			} else {
				if(getglobal('setting/ftp/mirror')) {
					@unlink($attach['target']);
					@unlink(getimgthumbname($attach['target']));
					$errorcode = 5;
				}
			}
		}

		$setarr = array(
			'uid' => $_G['uid'],
			'filename' => $attach['name'],
			'attachment' => $attach['attachment'],
			'filesize' => $attach['size'],
			'isimage' => $attach['isimage'],
			'thumb' => $attach['thumb'],
			'remote' => $attach['remote'],
			'filetype' => $attach['extension'],
			'dateline' => $_G['timestamp'],
			'aid' => $aid
		);
		$setarr['attachid'] = C::t('portal_attachment')->insert($setarr, true);
		if($attach['isimage']) {
			require_once libfile('function/home');
			$smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
			$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
			$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
			echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"smallimg\":\"$smallimg\", \"bigimg\":\"$bigimg\", \"errorcode\":$errorcode, \"cover\":\"$coverstr\"}";
			exit();
		} else {
			$fileurl = 'portal.php?mod=attachment&id='.$attach['attachid'];
			echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"file\":\"$fileurl\", \"errorcode\":$errorcode}";
			exit();
		}
	} else {
		echo "{\"aid\":0, \"errorcode\":$errorcode}";
	}

}

?>