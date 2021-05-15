<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_qqgroup.php 31974 2012-10-29 06:02:39Z songlixin $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$op = trim($_GET['op']);

$utilService = Cloud::loadClass('Service_Util');
$signUrl = $utilService->generateSiteSignUrl(array('v' => 2));

$_GET['anchor'] = in_array($_GET['anchor'], array('block', 'list', 'info', 'setting')) ? $_GET['anchor'] : 'block';

if ($_GET['first']) {
	$_GET['anchor'] = 'list';
}

$current = array($_GET['anchor'] => 1);

$qqgroupnav = array();

$qqgroupnav[0] = array('qqgroup_menu_block', 'cloud&operation=qqgroup&anchor=block', $current['block']);
$qqgroupnav[1] = array('qqgroup_menu_list', 'cloud&operation=qqgroup&anchor=list', $current['list']);
$qqgroupnav[2] = array('qqgroup_menu_manager', 'cloud&operation=qqgroup&anchor=info', $current['info']);
$qqgroupnav[3] = array('qqgroup_menu_setting', 'cloud&operation=qqgroup&anchor=setting', $current['setting']);

if (!$_G['inajax']) {
	cpheader();
}

if($_GET['anchor'] == 'list') {
	$utilService->redirect($cloudDomain.'/qun/list/?' . $signUrl);

} elseif($_GET['anchor'] == 'info') {
	$utilService->redirect($cloudDomain.'/qun/siteInfo/?' . $signUrl);

} elseif($_GET['anchor'] == 'setting') {
	if(submitcheck('settingsubmit')) {
		$usergroups = $_POST['groupid'];

		$updateData = array(
							'qqgroup_usergroup_feed_list' => serialize($usergroups),
						);

		C::t('common_setting')->update_batch($updateData);
		updatecache('setting');

		cpmsg('setting_update_succeed', 'action=cloud&operation=qqgroup&anchor='.$_GET['anchor'], 'succeed');

	} else {

		$usergroupsfeedlist = unserialize($_G['setting']['qqgroup_usergroup_feed_list']);
		$groupselect = array();

		foreach (C::t('common_usergroup')->fetch_all_by_radminid(0) as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupsfeedlist) ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		$groupselect = ($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

		shownav('navcloud', 'menu_cloud_qqgroup');
		showsubmenu('menu_cloud_qqgroup', $qqgroupnav);
		showtips('qqgroup_setting_tips');
		showformheader('cloud&operation=qqgroup&anchor=setting');
		showtableheader('qqgroup_feed_setting', '', '', 2);
		showsetting('qqgroup_usergroup_feed_list', '', '', '<select name="groupid[]" multiple="multiple" size="10">'.$groupselect.'</select>');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();

	}
} elseif($_GET['anchor'] == 'block') {

	$perpage = 10;
	$maxPage = 10;
	$page = intval($_GET['page']);
	$page = MAX($page, 1);
	$page = MIN($page, $maxPage);

	$prevPage = MAX($page - 1, 1);
	$nextPage = MIN($page + 1, $maxPage);

	if(submitcheck('setMiniportalThreadsSubmit')) {

		$topic = $_GET['topic'];
		$topic = processMiniportalTopicThread($topic);
		if(!$topic) {
			cpmsg('qqgroup_msg_deficiency', '', 'error');
		}

		$normal = $_GET['normal'];
		$normal = processMiniportalNormalThreads($normal);
		if(!$normal) {
			cpmsg('qqgroup_msg_deficiency', '', 'error');
		}

		$serverResult = sentMiniportalThreadsRemote($topic, $normal);

		$threads = array('topic' => $topic, 'normal' => $normal);
		if($serverResult['status']) {
			storeMiniportalThreads($threads);
			cpmsg('qqgroup_msg_save_succeed', 'action=cloud&operation=qqgroup&anchor=block', 'succeed');
		} else {
			$info = array('threads' => $threads, 'errorIds' => $serverResult['errorIds']);
			QQGroupMessage($serverResult['msg'], 'action=cloud&operation=qqgroup&anchor=block&sentResult=1', $info);
		}

	} elseif($op == 'getTopicThread') {
		getTopicThread();

	} elseif($op == 'getNormalThread') {
		getNormalThread();

	} elseif($op == 'uploadImage') {
		$tid = intval($_GET['tid']);
		if (submitcheck('uploadImageSubmit')) {
			ajaxshowheader();
			if($uploadImage = QQGroupUpload($tid)) {
				echo '<div id="upload_msg_success">'.cplang('qqgroup_msg_upload_succeed').'</div><div id="upload_msg_imgpath" style="display:none;">'.$uploadImage['thumbTarget'].'</div><div id="upload_msg_imgurl" style="display:none;">'.$_G['setting']['attachurl'].$uploadImage['thumbTarget'].'</div>';
			} else {
				echo '<div id="upload_msg_failure">'.cplang('qqgroup_msg_upload_failure').'</div>';
			}
			ajaxshowfooter();
		} else {
			showUploadImageForm($tid);
		}

	} elseif($op == 'searchForm') {
		showSearchThreads();

	} else {

		shownav('navcloud', 'menu_cloud_qqgroup');
		showsubmenu('menu_cloud_qqgroup', $qqgroupnav);

		echo '<div id="ajaxwaitid"></div>';

		showQQGroupCSS();

		showSearchDiv();

		showMiniportalPreview();

		showQQGroupScript();

	}
}

function showSearchForm() {

	require_once libfile('function/forumlist');

	showformheader('cloud&operation=qqgroup&anchor=block&op=searchForm', 'onSubmit="ajaxGetSearchResultThreads(); return false;"', 'search_form');
	showtableheader();
	$orderoptions = array('views', 'replies', 'heats', 'dateline', 'lastpost', 'recommends');
	$orderoption = '';
	foreach($orderoptions as $value) {
		$orderoption .= '<option value="'.$value.'" '.($value == 'dateline' ? 'selected="selected"' : '').'>'.cplang('qqgroup_search_order_'.$value).'</option>';
	}

	$datelineoptions = array(1, 2, 3, 4);
	$datelineoption = '';
	foreach($datelineoptions as $value) {
		$datelineoption .= '<option value="'.$value.'" '.($value == 4 ? 'selected="selected"' : '').'>'.cplang(sprintf('qqgroup_search_dateline_%s', $value)).'</option>';
	}
	echo '
		<tr>
			<td><select id="srchorder" name="srchorder" onchange="ajaxChangeSearch();">'.$orderoption.'</select></td>
			<td><select id="srchdateline" name="srchdateline" onChange="ajaxChangeSearch();">'.$datelineoption.'</select></td>
			<td><label for="srchtid">'.cplang('qqgroup_search_tid').'</label></td>
			<td><input type="text" value="" id="srchtid" name="srchtid" style="width:80px;" /></td>
			<td><input type="submit" value="'.cplang('qqgroup_search_button').'" id="search_submit" name="search_submit" class="btn" /></td>
		</tr>
	';
	showtablefooter();
	showformfooter();
}

function showSearchThreads() {
	global $_G, $page, $perpage;;
	$threads = array();
	$starttime = 0;
	$srchtid = intval($_GET['srchtid']);
	$srchorder = in_array($_GET['srchorder'], array('views', 'replies', 'heats', 'dateline', 'lastpost')) ? $_GET['srchorder'] : 'dateline';

	$datelinearray = array(1 => 3600, 2 => 86400, 3 => 86400 * 7, 4 => 86400 * 30);
	$srchdateline = array_key_exists($_GET['srchdateline'], $datelinearray) ? $datelinearray[$_GET['srchdateline']] : 86400 * 30;

	$start = ($page - 1) * $perpage;

	if($srchtid) {
		$threads = C::t('forum_thread')->fetch_all_by_tid_displayorder($srchtid, 0);
	} else {
		$starttime = TIMESTAMP - $srchdateline;
		$threads = C::t('forum_thread')->fetch_all_by_dateline($starttime, $start, $perpage, $srchorder);
	}

	$mpurl = ADMINSCRIPT.'?action=cloud&operation=qqgroup&anchor=block&op=searchForm'
					.'&srchtid='.$srchtid.'&srchorder='.$srchorder.'&srchdateline='.intval($_GET['srchdateline']);

	return showSearchResultThreads($threads, $mpurl);
}

function showSearchResultThreads($threads, $mpurl) {
	global $_G;
	$threadsOutput = '';
	loadcache('forums');
	if(empty($threads)) {
		$threadsOutput = '
			<tr><td colspan="3">'.cplang('qqgroup_search_nothreads').'</td></tr>
		';
	} else {
		foreach($threads as $thread) {
			$thread['subject'] = strip_tags($thread['subject']);
			$threadsOutput .= '
			<tr id="thread_'.$thread['tid'].'">
				<td class="title"><a href="forum.php?mod=viewthread&tid='.$thread['tid'].'"  title="'.$thread['subject'].'" target="_blank">'.cutstr($thread['subject'], 45).($thread['attachment'] == 2 ? '&nbsp;<img align="absmiddle" src="static/image/admincp/cloud/image_s.gif" alt="attach_img" title="'.cplang('attach_img').'" />' : '').'</a></td>
				<td title="'.dhtmlspecialchars(strip_tags($_G['cache']['forums'][$thread['fid']]['name'])).'">'.cutstr(dhtmlspecialchars(strip_tags($_G['cache']['forums'][$thread['fid']]['name'])), 14, '').'</td>
				<td class="qqqun_op"><a id="thread_addtop_'.$thread['tid'].'" href="javascript:;" onClick="addMiniportalTop('.$thread['tid'].')" class="qqqun_op_top" title="'.cplang('qqgroup_ctrl_add_miniportal_topic').'">top</a><a  id="thread_addlist_'.$thread['tid'].'" href="javascript:;" onClick="addMiniportalList('.$thread['tid'].')" class="qqqun_op_list" title="'.cplang('qqgroup_ctrl_add_miniportal_normal').'">list</a></td>
			</tr>';
		}
	}
	ajaxshowheader();
	echo $threadsOutput;
	showSearchResultPageLinks(count($threads), $mpurl);
	ajaxshowfooter();
}



function showSearchResultPageLinks($num = 0, $mpurl) {
	global $_G, $page, $perpage, $maxPage;
	$needNext = $page < $maxPage && $num == $perpage ? true : false;
	if ($pageLink = QQGroupSearchSimplePage($needNext, $page, $mpurl)) {
		echo '<tr><td colspan="3" class="qqqun_pg">'.$pageLink.'</td></tr>';
	}
}

function QQGroupSearchSimplePage($needNext, $curpage, $mpurl) {
	global $prevPage, $nextPage;
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$searchThreadsRule = 'ajaxGetPageResultThreads(\'%s\', \'%s\');';
	$prevClickFunc = addcslashes(sprintf($searchThreadsRule, $prevPage, dhtmlspecialchars($mpurl)), '"');
	$nextClickFunc = addcslashes(sprintf($searchThreadsRule, $nextPage, dhtmlspecialchars($mpurl)), '"');

	$prev = $curpage > 1 ? '<a href="javascript:;" onClick="'.$prevClickFunc.'" >'.$lang['prev'].'</a>' : '';
	$next = $needNext ? '<a href="javascript:;" onClick="'.$nextClickFunc.'" >'.$lang['next'].'</a>' : '';
	if($next || $prev) {
		$return = $prev.$next;
	}
	return $return;
}

function getTopicThread() {
	global $_G;
	$tid = intval($_GET['tid']);
	if(empty($tid)) {
		ajaxshowheader();
		echo showTopicTemplate(0);
		ajaxshowfooter();
		return false;
	}

	require_once libfile('function/forum');
	require_once libfile('function/discuzcode');
	loadforum();

	$posttable = $_G['thread']['posttable'];

	if(empty($posttable)) {
		ajaxshowheader();
		echo showTopicTemplate(0);
		ajaxshowfooter();
		return false;
	}

	$imagePath = $imageUrl = '';

	$subject = strip_tags($_G['thread']['subject']);
	$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid);
	$pid = intval($post['pid']);
	$message = cutstr(strip_tags(discuzcode($post['message'], 1, 0, 1)), 200);
	$message = preg_replace('/\[attach\](\d+)\[\/attach\]/is', '', $message);

	$imageDir = 'qqgroup';
	$imageName = 'miniportal_tid_'.$tid.'.jpg';
	$thumbTarget = $imageDir.'/'.$imageName;
	if(file_exists($_G['setting']['attachdir'].'./'.$thumbTarget)) {
		$imagePath = $thumbTarget;
		$imageUrl = $_G['setting']['attachurl'].$imagePath;
	} else {
		$attachments = C::t('forum_attachment_n')->fetch_all_by_id('tid:' . $tid, 'pid', $pid, '', array('-1', '1'), false, 0, 1);
		if($attachments && $attachment = reset($attachments)) {
			$imagePath = 'forum/'.$attachment['attachment'];
			$imageUrl = $_G['setting']['attachurl'].$imagePath;
		}
	}

	ajaxshowheader();
	echo showTopicTemplate($tid, $subject, $message, $imagePath, $imageUrl);
	ajaxshowfooter();
}

function showTopicTemplate($tid, $subject = '', $message = '', $imagePath = '', $imageUrl = '') {

	$html = '';
	if ($tid) {
		$html .= '
					<div class="qqqun_editor">
						<ul>
							<li class="e_edit"><a href="javascript:;" title="'.cplang('qqgroup_ctrl_edit').'" onClick="clickTopicEditor(\'title\')">edit</a></li>
							<li class="e_pic"><a onclick="showWindow(\'uploadImgWin\', this.href); return false;" href="'.ADMINSCRIPT.'?action=cloud&operation=qqgroup&anchor=block&op=uploadImage&tid='.$tid.'" title="'.cplang('qqgroup_ctrl_upload_image').'">pic</a></li>
							<li class="e_del"><a href="javascript:;" title="'.cplang('qqgroup_ctrl_remove').'" onClick="removeTopicThread('.$tid.')">del</a></li>
						</ul>
					</div>
					<dl>
						<input type="hidden" name="topic[id]" value="'.$tid.'" />
						<input type="hidden" name="topic[displayorder]" value="0" />
						<input type="hidden" id="topic_image_value" name="topic[extra][image]" value="'.$imagePath.'" />
						<dt class="title"><input id="topic-editor-input-title" type="text" class="px tpx" name="topic[title]" value="'.str_replace(array('\\', '"'), array('&#92;', '&#34;'), $subject).'" onblur="blurTopic(this);" onClick="clickTopicEditor(\'title\');" /></dt>
						<dd class="thumb"><a onclick="clickTopicEditor(\'image\'); showWindow(\'uploadImgWin\', this.href); return false;" href="'.ADMINSCRIPT.'?action=cloud&operation=qqgroup&anchor=block&op=uploadImage&tid='.$tid.'" title="'.cplang('qqgroup_ctrl_upload_image').'"><img id="topic_editor_thumb" src="'.($imageUrl ? $imageUrl.'?'.rand() : 'static/image/admincp/cloud/thumb.png').'" alt="'.cplang('qqgroup_ctrl_upload_image').'" /></a></dd>
						<dd class="info">
							<textarea id="topic-editor-textarea-content" class="pt ipt" onblur="blurTopic(this);"  onClick="clickTopicEditor(\'content\');" name="topic[extra][content]">'.$message.'</textarea>
						</dd>
					</dl>
		';
	} else {
		$html = '
					<dl>
						<div class="tips">'.cplang('qqgroup_preview_tips_topic').'</div>
					</dl>
		';
	}


	return $html;
}

function getNormalThread() {
	global $_G;
	$tid = intval($_GET['tid']);
	if(empty($tid)) {
		return false;
	}

	require_once libfile('function/forum');
	require_once libfile('function/discuzcode');
	loadforum();

	$subject = strip_tags($_G['thread']['subject']);
	$hasImage = $_G['thread']['attachment'] ? true : false;

	ajaxshowheader();
	echo showNormalTemplateLi($tid, $subject, $hasImage, true);
	ajaxshowfooter();
}

function showNormalTemplateLi($tid, $subject = '', $hasImage = false) {
	if ($tid) {
		$html = '
						<input type="hidden" class="normal_thread_tid" name="normal['.$tid.'][id]" value="'.$tid.'" />
						<input type="hidden" name="normal['.$tid.'][hasImage]" value="'.$hasImage.'" />
						<input class="preview_displayorder" type="hidden" name="normal['.$tid.'][displayorder]" value="" />
						<textarea class="pt" name="normal['.$tid.'][title]" onClick="clickNormalEditor(this);" onblur="blurNormalTextarea(this)">'.$subject.'</textarea>
		';
	} else {
		$html = '
			<div class="tips">'.cplang('qqgroup_preview_tips_normal').'</div>
		';
	}
	return $html;
}

function getMiniportalThreads() {
	$threads = array();
	$threads = C::t('common_setting')->fetch('cloud_qqgroup_miniportal_threads', true);
	$normalThreads = array();
	if($threads['normal']) {
		$i = 1;
		foreach($threads['normal'] as $tid => $normal) {
			$normal['displayorder'] = $i;
			$normalThreads[$i] = $normal;
			$i++;
		}
	}
	$threads['normalThreads'] = $normalThreads;
	return $threads;
}

function getResultThreads() {
	global $_G;
	$info = $_GET['info'];
	$info = dunserialize(base64_decode(trim($info)));
	if(!$info) {
		return false;
	}
	$threads = $info['threads'];
	if (!$threads) {
		return false;
	}
	$errorIds = $info['errorIds'];
	if (!$errorIds) {
		$errorIds = array();
	}

	if($threads['topic']['id'] && in_array($threads['topic']['id'], $errorIds)) {
		$threads['topic'] = array();
	}

	$normalThreads = array();
	if($threads['normal']) {
		$i = 1;
		foreach($threads['normal'] as $tid => $normal) {
			if (in_array($tid, $errorIds)) {
				unset($theads['normal'][$tid]);
				continue;
			} else {
				$normal['displayorder'] = $i;
				$normalThreads[$i] = $normal;
				$i++;
			}
		}
	}
	$threads['normalThreads'] = $normalThreads;
	return $threads;
}

function showSearchDiv() {

	echo '<div class="qqqun_bblist">';
	showSearchForm();
	showSearchResultDiv();
	echo '</div>';

}

function showSearchResultDiv() {
	echo '
	<table class="qqqun_tl">
		<tr>
			<th>'.cplang('qqgroup_search_threadslist').'</th>
			<th width="100">'.cplang('qqgroup_search_inforum').'</th>
			<th>'.cplang('qqgroup_search_operation').'</th>
		</tr>';
	echo '<tbody id="search_result"><tr><td colspan="3">'.cplang('qqgroup_search_loading').'</td></tr></tbody>';
	echo '</table>';
}

function showMiniportalPreview() {
	global $_G;
	if($_GET['sentResult'] && $_GET['info']) {
		$mcThreads = getResultThreads();
	} else {
		$mcThreads = getMiniportalThreads();
	}

	echo '<div id="qqqun">';
	echo '
				<div class="qqqun_title">
					<em><a href="#">'.cplang('qqgroup_preview_more').'</a></em>
					'.cplang('qqgroup_preview_shortname').'
				</div>
			';

	showformheader('cloud&operation=qqgroup&anchor=block', '', 'previewForm');
	$topic = $mcThreads['topic'];
	$topicId = intval($topic['id']);
	echo '<div class="qqqun_top" id="topicDiv">'.showTopicTemplate($topicId, $topic['title'], $topic['extra']['content'], $topic['extra']['image'], $topic['extra']['image'] ? $_G['setting']['attachurl'].$topic['extra']['image'] : '').'</div>';

	echo '<div class="qqqun_list">';

	echo '
				<div class="qqqun_editor">
					<ul>
						<li class="e_up"><a href="javascript:;" onClick="moveNormalThread(true);" title="'.cplang('qqgroup_ctrl_up').'">up</a></li>
						<li class="e_down"><a href="javascript:;" onClick="moveNormalThread(false);"  title="'.cplang('qqgroup_ctrl_down').'">down</a></li>
						<li class="e_edit"><a href="javascript:;" onClick="editNormalThread();" title="'.cplang('qqgroup_ctrl_edit').'">edit</a></li>
						<li class="e_del"><a href="javascript:;" onClick="removeNormalThread();" title="'.cplang('qqgroup_ctrl_remove').'">del</a></li>
					</ul>
				</div>
	';

	echo '<ul class="qqqun_xl">';
	$normalIds = array();
	$normalThreads = $mcThreads['normalThreads'];
	for($i=1; $i<=5; $i++) {
		$normal = $normalThreads[$i];
		if($normal) {
			$normalIds[] = $normalThreads[$i]['id'];
			echo '<li id="normal_thread_'.$i.'" displayorder="'.$i.'">'.showNormalTemplateLi($normalThreads[$i]['id'], $normalThreads[$i]['title'], $normalThreads[$i]['extra']['hasImage']).'</li>';
		} else {
			if($i == 1) {
				echo '<li id="normal_thread_'.$i.'" displayorder="'.$i.'"><div class="tips">'.cplang('qqgroup_preview_tips_normal').'</div></li>';
			} else {
				echo '<li id="normal_thread_'.$i.'" displayorder="'.$i.'" style="display:none;"></li>';
			}
		}
	}
	echo '</ul>';
	echo '</div>';

	echo '
		<div class="qqqun_btn">
			<button id="previewFormSubmit" type="submit" class="btn"><span>'.cplang('qqgroup_preview_button').'</span></button>
		</div>
	';

	echo '<input type="hidden" name="setMiniportalThreadsSubmit" value="1" />';
	showformfooter();

	echo '</div>';

	echo '
		<script type="text/javascript">
			var selectedTopicId = '.$topicId.';
			var selectedNormalIds = ['.implode(', ', $normalIds).'];
		</script>
		';

}

function processMiniportalTopicThread($topic) {
	if(empty($topic)) {
		return false;
	}
	$id = intval($topic['id']);
	if(empty($id)) {
		return false;
	}
	$title = trim($topic['title']);
	if(!$title) {
		return false;
	}
	$displayorder = 0;
	$content = trim($topic['extra']['content']);
	if(!$content) {
		return false;
	}
	$extra = array('image' => strip_tags(trim($topic['extra']['image'])), 'content' => $content);

	$newTopic = array('id' => $id, 'idtype' => 1, 'miniportaltype' => 2, 'title' => $title, 'extra' => $extra, 'displayorder' => $displayorder, 'dateline' => TIMESTAMP);
	return $newTopic;
}

function processMiniportalNormalThreads($normal) {
	$newNormal = array();
	$i = 0;
	foreach($normal as $thread) {
		$thread = processlNormalThread($thread);
		if($thread && $thread['id']) {
			$i ++;
			$thread['displayorder'] = $i;
			$newNormal[$thread['id']] = $thread;
		}
	}
	return $newNormal;
}

function processlNormalThread($thread) {
	if(empty($thread)) {
		return false;
	}
	$id = intval($thread['id']);
	if(empty($id)) {
		return false;
	}
	$title = trim($thread['title']);
	if(!$title) {
		return false;
	}
	$displayorder = intval($thread['displayorder']);
	$hasImage = $thread['extra']['hasImage'] ? true : false;
	$extra = array('hasImage' => $hasImage);

	$newThread = array('id' => $id, 'idtype' => 1, 'miniportaltype' => 1, 'title' => $title, 'extra' => $extra, 'displayorder' => $displayorder, 'dateline' => TIMESTAMP);
	return $newThread;
}

function storeMiniportalThreads($threads) {
	return C::t('common_setting')->update('cloud_qqgroup_miniportal_threads', $threads);
}

function sentMiniportalThreadsRemote($topic, $normal, $gIds = array()) {
	global $_G;

	if($topic['extra']['image'] && $topic['extra']['image'] = @file_get_contents($_G['setting']['attachdir'].'./'.$topic['extra']['image'])) {
		$topic['extra']['image'] = base64_encode($topic['extra']['image']);
	}

	$groupClient = Cloud::loadClass('Service_Client_QQGroup');

	try {
		$res = $groupClient->miniportal($topic, $normal, $gIds);
	} catch (Cloud_Service_Client_RestfulException $e) {
		if ($e->getCode() == 1) {
			$res = array('status' => false, 'msg' => cplang('qqgroup_msg_unknown_dns'));
		} else {
			$res = array('status' => false, 'msg' => cplang('qqgroup_msg_remote_exception', array('errmsg' => $e->getMessage(), 'errno' => $e->getCode())));
		}
	}

	if(!is_array($res)) {
		$res = array('status' => false, 'msg' => cplang('qqgroup_msg_remote_error'));
	}

	return $res;

}

function showUploadImageForm($tid) {
	ajaxshowheader();
	echo '
		<ul class="fwin-menu">
			<li class="a"><a>'.cplang('qqgroup_ctrl_upload_image').'</a></li>
			<li style="float:right;"><a class="flbc" href="javascript:;" onClick="hideWindow(\'uploadImgWin\')" title="'.cplang('qqgroup_ctrl_close').'">'.cplang('qqgroup_ctrl_close').'</a></li>
		</ul>
		';
	echo '<div class="c">';
	showformheader('cloud&operation=qqgroup&anchor=block&op=uploadImage', 'enctype="multipart/form-data"', 'uploadImage');
	echo '<input type="hidden" name="tid" value="'.$tid.'" />';
	echo '<input type="hidden" name="uploadImageSubmit" value="1" />';
	echo '
			<table class="tb">
				<tr style="display:none;">
					<td id="uploadImageResult" colspan="2" align="center"></td>
				</tr>
				<tr>
					<td width="70" align="right">'.cplang('qqgroup_ctrl_choose_image').'</td>
					<td align="left"><input type="file" name="imageFile" size="30" /></td>
				</tr>
				<tr>
					<td align="right"></td>
					<td align="left">
						<p>'.cplang('qqgroup_ctrl_choose_image_tips').'</p>
					</td>
				</tr>
			</table>
			';
	showformfooter();
	echo '</div>';
	echo '
		<div class="o pns"><button class="pn pnc" onClick="ajaxUploadQQGroupImage();"><span>'.cplang('qqgroup_ctrl_upload_image').'</span></button></div>
		';
	ajaxshowfooter();
}

function QQGroupUpload($tid) {
	global $_G;
	$imageDir = 'qqgroup';
	$imageName = 'miniportal_tid_'.$tid.'.jpg';
	$fieldName = 'imageFile';

	$_FILES[$fieldName]['name'] = addslashes(urldecode($_FILES[$fieldName]['name']));
	$_FILES[$fieldName]['type'] = addslashes(urldecode($_FILES[$fieldName]['type']));
	$upload = new discuz_upload();
	$upload->init($_FILES[$fieldName]);
	$attach = & $upload->attach;

	if (!$attach['isimage']) {
		return false;
	}
	if ($attach['size'] > 5000000) {
		return false;
	}

	$upload->save();

	list($imgwidth, $imgheight) = $attach['imageinfo'];
	if($imgwidth < 75 || $imgheight < 75) {
		@unlink($attach['target']);
		return false;
	}

	require_once libfile('class/image');
	$image = new image;
	$image->param['thumbquality'] = 100;
	$thumbTarget = $imageDir.'/'.$imageName;
	@unlink($_G['setting']['attachdir'].'./'.$thumbTarget);

	$thumb = $image->Thumb($attach['target'], $thumbTarget, 75, 75) ? 1 : 0;

	if(!$thumb && !@copy($attach['target'], $_G['setting']['attachdir'].'./'.$thumbTarget)) {
		@unlink($attach['target']);
		return false;
	}

	@unlink($attach['target']);

	$res = $attach;
	$res['thumbTarget'] = $thumbTarget;

	return $res;

}

function showQQGroupScript() {
	global $adminscript;
	echo
<<<EOF
	<script type="text/javascript" src="static/image/admincp/cloud/jquery.min.js"></script>
	<script type="text/javascript">
		var adminscript = '$adminscript';
	</script>
	<script type="text/javascript" src="static/image/admincp/cloud/qqgroup.js?v=20121101"></script>
EOF;
}

function showQQGroupCSS() {
	echo
<<<EOF
	<link href="static/image/admincp/cloud/qqgroup.css" rel="stylesheet" type="text/css" />
EOF;
}

function QQGroupMessage($message, $url = '', $info = '') {

	if($url) {
		$url = addcslashes(substr($url, 0, 5) == 'http:' ? $url : ADMINSCRIPT . '?' . $url, '\'');
	}
	if($info) {
		$info = base64_encode(serialize($info));
	}

	$message = "<h4 class=\"infotitle3\">$message</h4>";
	$message .= '<form id="qqgroup_message_form" action="'.$url.'" method="POST" style="display:none;">';
	$message .= '<textarea name="info" style="display:none;">'.$info.'</textarea>';
	$message .= '</form>';
	$message .= '<p class="marginbot"><a href="javascript:;" onClick="$(\'qqgroup_message_form\').submit();" class="lightlink">'.cplang('message_return').'</a></p>';
	echo '<h3>'.cplang('discuz_message').'</h3><div class="infobox">'.$message.'</div>';
	exit();

}