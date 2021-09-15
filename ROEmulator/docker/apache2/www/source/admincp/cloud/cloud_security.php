<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: cloud_security.php 33861 2013-08-22 09:16:38Z nemohou $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$op = trim($_GET['op']);

$_GET['anchor'] = in_array($_GET['anchor'], array('index', 'setting', 'thread', 'post', 'member', 'reportOperation', 'reopen')) ? $_GET['anchor'] : 'index';
$pt = in_array($_GET['anchor'], array('thread', 'post')) ? $_GET['anchor'] : 'thread';

$current = array($_GET['anchor'] => 1);

$operateresultmap = array(
	'0' => 1,
	'-1' => 0,
	'-5' => 0
);

$securitynav = array();

$securitynav[0] = array('security_index', 'cloud&operation=security&anchor=index', $current['index']);
$securitynav[1] = array('security_blanklist', 'cloud&operation=security&anchor=setting', $current['setting']);
$securitynav[2] = array('security_thread_list', 'cloud&operation=security&anchor=thread', $current['thread']);
$securitynav[3] = array('security_post_list', 'cloud&operation=security&anchor=post', $current['post']);
$securitynav[4] = array('security_member_list', 'cloud&operation=security&anchor=member', $current['member']);

if (!$_G['inajax']) {
	cpheader();
	shownav('safe', 'menu_cloud_security', 'security_'.$_GET['anchor'].'_list');
	showsubmenu('menu_cloud_security', $securitynav);
}

$tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
$start_limit = ($page - 1) * $tpp;
require_once libfile('function/discuzcode');
require_once libfile('function/core');
$datas = $data = $eviluids = $evilPids = $evilTids = $members = $thread = $post = '';

if($_GET['anchor'] != 'reopen') {
	$apps = $appService->getCloudApps();
	if(empty($apps) || empty($apps[$operation]) || $apps[$operation]['status'] == 'close') {
		cpmsg('security_reopen', '', 'succeed');
	}
}

if ($_GET['anchor'] == 'index') {
	$utilService = Cloud::loadClass('Service_Util');
	$signUrl = $utilService->generateSiteSignUrl(array('v' => 2));
	$utilService->redirect($cloudDomain.'/security/stats/list/?' . $signUrl);
} elseif ($_GET['anchor'] == 'setting') {

	if (!submitcheck('settingsubmit')) {
		loadcache('setting');

		$evilthreads = C::t('common_setting')->fetch('cloud_security_stats_thread');
		$evilposts = C::t('common_setting')->fetch('cloud_security_stats_post');
		$evilmembers = C::t('common_setting')->fetch('cloud_security_stats_member');

		$usergroupswhitelist = $_G['setting']['security_usergroups_white_list'];
		$groupselect = array();

		foreach (C::t('common_usergroup')->fetch_all_not(array('6','7')) as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupswhitelist) ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

		$forumswhitelist = $_G['setting']['security_forums_white_list'];
		require_once libfile('function/forumlist');
		loadcache('forums');
		$forumselect = str_replace('%', '%%', forumselect(FALSE, 0, $forumswhitelist, TRUE));

		showformheader('cloud&operation=security&anchor=setting');
		showtableheader('security_white_list_setting', '', '', 2);
		showsetting('security_usergroup_white_list', '', '', '<select name="groupid[]" multiple="multiple" size="10">'.$groupselect.'</select>');
		showsetting('security_forum_white_list', '', '', '<select name="fid[]" multiple="multiple" size="10">'.$forumselect.'</select>');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$usergroups = $_POST['groupid'];
		$forums = $_POST['fid'];

		$updateData = array(
							'security_usergroups_white_list' => serialize($usergroups),
							'security_forums_white_list' => serialize($forums)
						);

		C::t('common_setting')->update_batch($updateData);
		updatecache('setting');

		cpmsg('setting_update_succeed', 'action=cloud&operation=security&anchor='.$_GET['anchor'], 'succeed');
	}

} elseif ($_GET['anchor'] == 'thread') {
	$count = C::t('#security#security_evilpost')->count_by_type('1');
	$multipage = multi($count, $tpp, $page, ADMINSCRIPT.'?action=cloud&operation=security&anchor=thread');
	list($datas, $evilTids) = getEvilList('thread', $start_limit, $tpp);
	echo "<p><a href='###' onclick='$(\"recyclebinform\").submit();'>{$lang['security_recyclebin_thread']}</a></p>";
	showformheader('recyclebin&operation=search', 'style="display: none;"', 'recyclebinform');
	showhiddenfields(array('security' => 1, 'searchsubmit' => 1));
	showformfooter();
	showtableheader('', '', 'id = "security_list"');
	showsubtitle(array('security_subject', 'security_forum', 'security_author', 'security_thread_status'));

	foreach($datas as $key => $value) {

		if(!$value['message']) {
			$subjectstyle = 'class = "threadopt"';
		} else {
			$subjectstyle = '';
		}
		$value['message'] = convertMessage($value);
		$modthreadkey = modauthkey($value['tid']);
		$viewlink = $value['message'] ? '<a href="forum.php?mod=redirect&goto=findpost&ptid='.$value['tid'].'&pid='.$value['pid'].'&modthreadkey='.$modthreadkey.'" target="_blank" title="'.$lang['security_view_thread'].'">'.$value['subject'].'</a>' : '';

		$thread = array(convertSubjectandIP($value, $viewlink), getNamebyFid($value['fid']), convertAuthorAndDate($value), //convertIdtoStr($value['eviltype']),
		convertIdtoStr($value['invisible'], 'adminoperate'));
		showtagheader('tbody', '', true, 'hover');
		showtablerow($subjectstyle, array('width = "400px"'), $thread);
		$value['message'] ? showtablerow('class="threadopt" style = "display: none;" id = "mod_'.$value['tid'].'_row_'.$key.'"', 'colspan = "6"', $value['message']) : '';
		showtagfooter('tbody');
	}
	if ($multipage) {
		showtablerow('', 'colspan = "6"', $multipage);
	}
	showtablefooter();

} elseif($_GET['anchor'] == 'post') {

	$count = C::t('#security#security_evilpost')->count_by_type('0');
	$multipage = multi($count, $tpp, $page, ADMINSCRIPT.'?action=cloud&operation=security&anchor=post');
	list($datas, $evilPids) = getEvilList('post', $start_limit, $tpp);
	echo "<p><a href='###' onclick='$(\"recyclebinpostform\").submit();'>{$lang['security_recyclebin_post']}</a></p>";
	showformheader('recyclebinpost&operation=search', 'style="display: none;"', 'recyclebinpostform');
	showhiddenfields(array('security' => 1, 'searchsubmit' => 1));
	showformfooter();
	showtableheader('', '', 'id = "security_list"');
	showsubtitle(array('security_subject', 'security_forum', 'security_author', 'security_post_status'));

	foreach($datas as $key => $value) {

		if(!$value['message']) {
			$subjectstyle = 'class = "threadopt"';
		} else {
			$subjectstyle = '';
		}
		$value['message'] = convertMessage($value);
		$modthreadkey = modauthkey($value['tid']);
		$thread = array(convertSubjectandIP($value), getNamebyFid($value['fid']), convertAuthorAndDate($value), //convertIdtoStr($value['eviltype']),
		convertIdtoStr($value['invisible'], 'adminoperate'), $viewlink);
		showtagheader('tbody', '', true, 'hover');
		showtablerow($subjectstyle,array('width = "400px"'), $thread);
		$value['message'] ? showtablerow('class="threadopt" style="display: none;" id = "mod_'.$value['tid'].'_row_'.$key.'"', 'colspan = "6"', $value['message']) : '';
		showtagfooter('tbody');
	}
	if ($multipage) {
		showtablerow('', 'colspan = "6"', $multipage);
	}
	showtablefooter();

} elseif($_GET['anchor'] == 'member') {
	showtips('security_member_tips');
	if($_GET['ignoreuid']) {
		C::t('#security#security_eviluser')->delete(intval($_GET['ignoreuid']));
	}
	$memberperpage = $_G['setting']['memberperpage'];
	$start_limit = ($page - 1) * $memberperpage;
	$count = C::t('#security#security_eviluser')->count();
	$multipage = multi($count, $memberperpage, $page, ADMINSCRIPT.'?action=cloud&operation=security&anchor=member');

	list($datas, $eviluids) = getEvilList('user', $start_limit, $memberperpage);

	showformheader('recyclebinpost&operation=search', 'style="display: none;"', 'recyclebinmember');
	showhiddenfields(array('security' => 1, 'searchsubmit' => 1));
	echo "\n<input type=\"hidden\" name=\"authors\" id=\"authors\" value=\"\">";
	showformfooter();
	showformheader("members&operation=clean", '');
	showtableheader();
	showsubtitle(array('','security_members_name', 'members_edit_info', 'security_thread_member_group', 'security_createtime', '', '', ''));

	foreach($datas as $value) {
		if ($value['username']) {
			$username = '<a href="home.php?mod=space&uid='.$value['uid'].'&do=profile" target="_blank" title="'.$title.'">'.$value['username'].'</a>';
		} else {
			$username = $lang['security_userdeleted']."(uid:{$value['uid']})";
		}
		$del = '<input type="checkbox" name="uidarray[]" value="'.$value['uid'].'"'.($value['adminid'] == 1 ? 'disabled' : '').' class="checkbox">';
		$optmember = '<a href="'.ADMINSCRIPT.'?action=members&operation=ban&uid='.$value['uid'].'" target="_blank">'.cplang('members_ban').'</a>';
		$ignorethis = '<a href="'.ADMINSCRIPT.'?action=cloud&operation=security&anchor=member&ignoreuid='.$value['uid'].'&page='.$page.'">'.cplang('security_member_ignore_this').'</a>';
		$createtime = date('Y-m-d', $value['createtime']);
		$evilthreads = '<a href="javascript:void(0);" onclick="searchevilpost_member(\''.$value['username'].'\', 1);return false;">'.cplang('security_thread_list').'</a>';
		$evilposts = '<a href="javascript:void(0);" onclick="searchevilpost_member(\''.$value['username'].'\', 2);return false;">'.cplang('security_post_list').'</a>';
		$member = array($del, $username, convertMemberInfo($value), $value['grouptitle'], $createtime, $evilthreads, $evilposts, $optmember, $ignorethis);
		showtablerow('',array('class="td25"'),$member);
	}
	showsubmit('deletesubmit', cplang('delete'), '', '', $multipage);

	showtablefooter();
	showformfooter();
} elseif($_GET['anchor'] == 'reopen') {
	Cloud::loadFile('Service_Client_Cloud');
	$Cloud_Service_Client_Cloud = new Cloud_Service_Client_Cloud;
	$return = $Cloud_Service_Client_Cloud->appOpenWithRegister('security');
	if($return['errCode']) {
		cpmsg($return['errMessage'], 'action=cloud&operation=security&anchor=index', 'error');
	} else {
		dheader('location: '.ADMINSCRIPT.'?action=cloud&operation=security&anchor=index');
	}
}
echo "
		<script type='text/javascript'>
			function searchevilpost_member(username, type) {
				$('recyclebinmember').action= '".ADMINSCRIPT."?'+(type == 1 ? 'action=recyclebin&operation=search' : 'action=recyclebinpost&operation=search');
				$('authors').value=username;
				$('recyclebinmember').submit();
				return false;
			}
			</script>";
$jsScript = <<<EOF
		<script type='text/javascript'>
			function toggle_mod(id) {
				if($(id).style.display == 'none') {
					$(id).style.display = '';
				} else {
					$(id).style.display = 'none';
				}
				return false;
			}
			function security_foldall() {
				var trs = $('security_list').getElementsByTagName('TR');

				for(var i in trs) {
					if(trs[i].id && trs[i].id.match(/mod_(\d+)_row_(\d+)/) != null) {
						trs[i].style.display = "none";
					}
				}
			}
			function security_exfoldall() {
				var trs = $('security_list').getElementsByTagName('TR');

				for(var i in trs) {
					if(trs[i].id && trs[i].id.match(/mod_(\d+)_row_(\d+)/) != null) {
						trs[i].style.display = "";
					}
				}
			}
		</script>
EOF;
echo $jsScript;

function convertIdtoStr($id, $type = 'security_type', $subtype = 'thread') {
	global $lang;
	if ($type == 'security_type') {
		$id = min(6, $id);
		$result = $lang['security_type_'.$id];
	} elseif($type == 'checkreported') {
		$result = $id ? $lang['security_isreported_yes'] : $lang['security_isreported_no'];
	} elseif($type == 'adminoperate') {

		if ($id === null) {
			return $lang['security_opreateresult_2'];
		}

		if (in_array($subtype, array('thread', 'post'))) {
			global $operateresultmap;

			$result = $lang['security_opreateresult_'.$operateresultmap[$id]];
		} elseif ($subtype == 'member') {

			global $nooperategroup;
			if (in_array($id, $nooperategroup)) {
				$result = $lang['security_opreateresult_0'];
			} else {
				$result = $lang['security_opreateresult_1'];
			}
		}
	}
	return $result;
}

function convertMemberInfo($value) {
	global $lang;
	$result = '';
	if ($value['username']) {
		$result = $lang['members_edit_regdate'] . ': ' . dgmdate($value['regdate']) . '<br/>';
		$result .= $lang['members_edit_regip'] . ': ' . $value['regip'] . ' ' . convertip($value['regip']) . '<br/>';
		$result .= 'Email: ' . $value['email'];
	} else {
		$result = '<p style="margin:14px 0;">' . $lang['security_userdeleted'] . '</p>';
	}
	return $result;
}

function convertOperate($id = 0) {
	$ids = array('1' => 'delete',
				 '2' => 'restore');
	if (!$ids[$id]) {
		return false;
	}
	return $ids[$id];
}

function getEvilList($type, $start, $ppp) {
	$datas = $data = $evilids = '';

	if ($type == 'member') {
		$type = 'user';
	}

	if ($type == 'user') {
		$query = C::t('#security#security_eviluser')->fetch_range($start, $ppp);
		$idtype = 'uid';
	} elseif($type == 'thread') {
		$query = C::t('#security#security_evilpost')->fetch_range_by_type('1', $start, $ppp);
		$idtype = 'pid';
	} elseif($type == 'post') {
		$query = C::t('#security#security_evilpost')->fetch_range_by_type('0', $start, $ppp);
		$idtype = 'pid';
	}

	foreach ($query as $data) {
		$datas[$data[$idtype]] = $data;
		$evilids[] = $data[$idtype];
		if ($data['tid']) {
			$evilTids[] = $data['tid'];
			$threadPid[$data['tid']][] = $data['pid'];
		}
	}

	if (is_array($evilTids)) {
		$evilTids = array_unique($evilTids);
	}

	if (!$evilids) {
		return false;
	}
	if ($type == 'user') {
		$usergroups = array();
		foreach (C::t('common_usergroup')->range() as $group) {
			$usergroups[$group['groupid']] = $group['grouptitle'];
		}

		$regips = C::t('common_member_status')->fetch_all($evilids);

		$query = C::t('common_member')->fetch_all($evilids);
		foreach ($query as $key => $user) {
			if(!empty($user) && !in_array($user['groupid'], array(4,5,6))) {
				$query[$key]['regip'] = $regips[$key]['regip'];
			}
		}
		if(count($evilids) != count($query)) {
			$deleviluids = array();
			foreach($evilids as $key => $eviluid) {
				if(empty($query[$eviluid])) {
					$deleviluids[] = $eviluid;
					unset($evilids[$key]);
				}
			}
			C::t('#security#security_eviluser')->delete($deleviluids);
		}
	} elseif($type == 'thread' || $type == 'post') {

		$query = C::t('forum_thread')->fetch_all_by_tid($evilTids);
	}

	foreach ($query as $data) {

		if ($type == 'thread' || $type == 'post') {
			foreach($threadPid[$data['tid']] as $pid) {
				$isFirst = ($type == 'thread') ? 1 : 0;
				$postData = C::t('forum_post')->fetch($data['posttableid'], $pid);
				if ($postData['pid']) {
					$datas[$postData['pid']] = array_merge($datas[$postData['pid']], $postData);
					if ($type == 'post') {
						$datas[$postData['pid']]['subject'] = $data['subject'];
					}
				}
			}
		} else {
			$data['grouptitle'] = $usergroups[$data['groupid']];
			$datas[$data[$idtype]] = array_merge($datas[$data[$idtype]], $data);
		}
	}
	return array($datas, $evilids);
}

function getNamebyFid($fid) {
	global $_G;
	if (!$fid) {
		return false;
	}
	$forumInfo = C::t('forum_forum')->fetch_all_name_by_fid($fid);
	$name = $forumInfo[$fid]['name'];
	$name = "<a href='forum.php?mod=forumdisplay&fid=$fid' target='_blank'>".$name."</a>";
	return $name;
}

function convertSubjectandIP($value, $viewlink = '') {
	global $lang;
	if ($viewlink) {
		$result = '<h3>'.$viewlink.'</h3>';
	} else {
		$result = '<h3><a title="'.$lang['security_clicktotoggle'].'" href="javascript:;" onclick="return toggle_mod(\'mod_'.$value['tid'].'_row_'.$value['pid'].'\');" target="_blank">'.$value['subject'].'</a></h3>';
	}

	$result .= '<p>'.$value['useip'].' '.convertip($value['useip']).' ( pid : '.$value['pid'].' ) </p>';
	if (!$value['message']) {
		return $lang['security_postdeleted']."(tid:{$value['tid']} pid:{$value['pid']})";
	}
	return $result;
}

function convertMessage($value) {
	global $lang;
	if (!$value['message']) {
		return false;
	}
	$value['message'] = discuzcode($value['message'], 0, 0, sprintf('%00b', $value['htmlon']), 1, 1, 1, 0);
	$value['message'] = '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$value['message'].'</div>';
	return $value['message'];
}

function convertAuthorAndDate($value) {
	if (!$value['author']) {
		return false;
	}
	$result = "<a href='home.php?mod=space&uid={$value[authorid]}&do=profile' target='_blank'>" . $value['author'] . "</a>" . '<p>';
	$result .= dgmdate($value['dateline']);
	$result .= '</a>';
	return $result;
}

function getDataToReport($operateType, $datatosync, $datas) {
	$datatoreport = array();
	foreach($datatosync as $operateresult => $ids) {
		foreach($ids as $id) {
			if(!$datas[$id]['isreported']) {
				$data = array(
						'operateType' => $operateType,
						'operate' => $operateresult == 'validate' ? 'restore' : 'delete',
						'operateId' => $id,
						'uid' => $datas[$id]['authorid'] ? $datas[$id]['authorid'] : $datas[$id]['uid'],
						);
				$data['openId'] = getOpenId($data['uid']);
				$data['clientIp'] = $datas[$id]['userip'] ? $datas[$id]['userip'] : getMemberIp($data['uid']);
				if ($operateType != 'member') {
					$data['tid'] = $datas[$id]['tid'];
					$data['pid'] = $datas[$id]['pid'];
					$data['fid'] = $datas[$id]['fid'];
				}
				array_push($datatoreport, $data);
			}
		}
	}
	return $datatoreport;
}