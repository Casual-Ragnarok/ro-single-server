<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_upload.php 29000 2012-03-22 03:52:01Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getuploadconfig($uid=0, $fid=0, $limit=true) {
	global $_G;

	$notallow = $config = array();
	$config['limit'] = 0;
	$uid = !empty($uid) ? intval($uid) : $_G['uid'];
	$authkey = $_G['config']['security']['authkey'];
	$config['hash'] = md5(substr(md5($authkey), 8).$uid);

	$imageexts = array('jpg','jpeg','gif','png','bmp');
	$forumattachextensions = '';
	$fid = intval($fid);
	if($fid) {
		$forum = $fid != $_G['fid'] ? C::t('forum_forum')->fetch_info_by_fid($fid) : $_G['forum'];
		$levelinfo = C::t('forum_grouplevel')->fetch($forum['level']);
		if($forum['status'] == 3 && $forum['level'] && $postpolicy = $levelinfo['postpolicy']) {
			$postpolicy = dunserialize($postpolicy);
			$forumattachextensions = $postpolicy['attachextensions'];
		} else {
			$forumattachextensions = $forum['attachextensions'];
		}
	}
	$extendtype = '';

	loadcache('attachtype');
	$fid = isset($_G['cache']['attachtype'][$fid]) ? $fid : 0;
	$filter = array();
	foreach($_G['cache']['attachtype'][$fid] as $extension => $maxsize) {
		if($maxsize == 0) {
			$notallow[] = $extension;
		} else {
			$filter[] = "'$extension':$maxsize";
		}
	}
	if(!empty($filter)) {
		$config['filtertype'] = '{'.implode(',', $filter).'}';
	}
	$_G['group']['attachextensions'] = !$forumattachextensions ? $_G['group']['attachextensions'] : $forumattachextensions;

	$config['imageexts'] = array('ext' => '', 'depict' => 'Image File');
	$config['attachexts'] = array('ext' => '*.*', 'depict' => 'All Support Formats');

	if($_G['group']['attachextensions'] !== '') {
		$_G['group']['attachextensions'] = str_replace(' ', '', $_G['group']['attachextensions']);
		$exts = explode(',', $_G['group']['attachextensions']);

		$imagext = filterexts(array_intersect($imageexts, $exts), $notallow);
		$config['imageexts']['ext'] = !empty($imagext) ? '*.'.implode(';*.', $imagext) : '';
		$exts = filterexts($exts, $notallow);
		$config['attachexts']['ext'] = !empty($exts) ? '*.'.implode(';*.', $exts) : '';
	} else {
		$imageexts = filterexts($imageexts, $notallow);
		$config['imageexts']['ext'] = !empty($imageexts) ? '*.'.implode(';*.', $imageexts) : '';
	}
	$config['max'] = 0;
	if(!empty($_G['group']['maxattachsize'])) {
		$config['max'] = intval($_G['group']['maxattachsize']);
	} else {
		$config['max'] = @ini_get(upload_max_filesize);
		$unit = strtolower(substr($config['max'], -1, 1));
		$config['max'] = intval($config['max']);
		if($unit == 'k') {
			$config['max'] = $config['max']*1024;
		} elseif($unit == 'm') {
			$config['max'] = $config['max']*1024*1024;
		} elseif($unit == 'g') {
			$config['max'] = $config['max']*1024*1024*1024;
		}
	}
	$config['max'] = $config['max'] / 1024;

	if($limit) {
		if($_G['group']['maxattachnum']) {
			$todayattachs = getuserprofile('todayattachs');
			$config['maxattachnum'] = $_G['group']['maxattachnum'] - $todayattachs;
			$config['maxattachnum'] = $config['maxattachnum'] > 0 ? $config['maxattachnum'] : -1;
			$config['limit'] = $config['maxattachnum'] > 0 ? $config['maxattachnum'] : 0;
		}
		if($_G['group']['maxsizeperday']) {
			$todayattachsize = getuserprofile('todayattachsize');
			$config['maxsizeperday'] = $_G['group']['maxsizeperday'] - $todayattachsize;
			$config['maxsizeperday'] = $config['maxsizeperday'] > 0 ? $config['maxsizeperday'] : -1;
		}
	}
	return $config;
}
function filterexts($needle, $haystack) {

	foreach($needle as $key => $value) {
		if(in_array($value, $haystack)) {
			unset($needle[$key]);
		}
	}
	return $needle;
}
?>