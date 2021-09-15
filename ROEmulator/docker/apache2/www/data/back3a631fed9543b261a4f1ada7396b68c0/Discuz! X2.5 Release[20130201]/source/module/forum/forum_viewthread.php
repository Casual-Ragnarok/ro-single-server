<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_viewthread.php 32154 2012-11-16 06:20:48Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');
require_once libfile('function/discuzcode');
require_once libfile('function/post');

$thread = & $_G['forum_thread'];
$forum = & $_G['forum'];

if(!$_G['forum_thread'] || !$_G['forum']) {
	showmessage('thread_nonexistence');
}

$page = max(1, $_G['page']);

if($_G['setting']['cachethreadlife'] && $_G['forum']['threadcaches'] && !$_G['uid'] && $page == 1 && !$_G['forum']['special'] && empty($_GET['do']) && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
	viewthread_loadcache();
}

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();

$archiveid = $thread['threadtableid'];
$thread['is_archived'] = $archiveid ? true : false;
$thread['archiveid'] = $archiveid;
$forum['threadtableid'] = $archiveid;
$threadtable = $thread['threadtable'];
$posttableid = $thread['posttableid'];
$posttable = $thread['posttable'];


$_G['action']['fid'] = $_G['fid'];
$_G['action']['tid'] = $_G['tid'];
if($_G['fid'] == $_G['setting']['followforumid'] && $_G['adminid'] != 1) {
	dheader("Location: home.php?mod=follow");
}

$_GET['authorid'] = !empty($_GET['authorid']) ? intval($_GET['authorid']) : 0;
$_GET['ordertype'] = !empty($_GET['ordertype']) ? intval($_GET['ordertype']) : 0;
$_GET['from'] = $_G['setting']['portalstatus'] && !empty($_GET['from']) && $_GET['from'] == 'portal' ? 'portal' : '';

$fromuid = $_G['setting']['creditspolicy']['promotion_visit'] && $_G['uid'] ? '&amp;fromuid='.$_G['uid'] : '';
$feeduid = $_G['forum_thread']['authorid'] ? $_G['forum_thread']['authorid'] : 0;
$feedpostnum = $_G['forum_thread']['replies'] > $_G['ppp'] ? $_G['ppp'] : ($_G['forum_thread']['replies'] ? $_G['forum_thread']['replies'] : 1);

if(!empty($_GET['extra'])) {
	parse_str($_GET['extra'], $extra);
	$_GET['extra'] = array();
	foreach($extra as $_k => $_v) {
		if(preg_match('/^\w+$/', $_k)) {
			if(!is_array($_v)) {
				$_GET['extra'][] = $_k.'='.rawurlencode($_v);
			} else {
				$_GET['extra'][] = http_build_query(array($_k => $_v));
			}
		}
	}
	$_GET['extra'] = implode('&', $_GET['extra']);
}


$aimgs = array();
$skipaids = array();

$thread['subjectenc'] = rawurlencode($_G['forum_thread']['subject']);
$thread['short_subject'] = cutstr($_G['forum_thread']['subject'], 52);

$navigation = '';
if($_GET['from'] == 'portal') {

	$_G['setting']['ratelogon'] = 1;
	$navigation = ' <em>&rsaquo;</em> <a href="portal.php">'.lang('core', 'portal').'</a>';
	$navsubject = $_G['forum_thread']['subject'];
	$navtitle = $_G['forum_thread']['subject'];


} elseif($_G['forum']['status'] == 3) {
	$_G['action']['action'] = 3;
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', 'forum.php?mod=group&fid='.$_G['fid']);
	} elseif($status == 3) {
		showmessage('forum_group_moderated', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	$nav = get_groupnav($_G['forum']);
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.$nav['nav'];
	$upnavlink = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');
	$_G['grouptypeid'] = $_G['forum']['fup'];

} else {
	$navigation = '';
	$upnavlink = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');

	if($_G['forum']['type'] == 'sub') {
		$fup = $_G['cache']['forums'][$_G['forum']['fup']]['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	if($_G['forum']['fup']) {
		$fup = $_G['forum']['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	$t_link = 'forum.php?mod=forumdisplay&amp;fid='.$_G['fid'].($_GET['extra'] && !IS_ROBOT ? '&amp;'.$_GET['extra'] : '');
	$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.($_G['forum']['name']).'</a>';

	if($archiveid) {
		if($threadtable_info[$archiveid]['displayname']) {
			$t_name = dhtmlspecialchars($threadtable_info[$archiveid]['displayname']);
		} else {
			$t_name = lang('core', 'archive').' '.$archiveid;
		}
		$navigation .= ' <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&archiveid='.$archiveid.'">'.$t_name.'</a>';
	}

	unset($t_link, $t_name);
}


$_GET['extra'] = $_GET['extra'] ? rawurlencode($_GET['extra']) : '';

if(@in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('forum_viewthread', 1, '', $_G['tid'], 1, '', '');
} else {
	$canonical = 'forum.php?mod=viewthread&tid='.$_G['tid'];
}
$_G['setting']['seohead'] .= '<link href="'.$_G['siteurl'].$canonical.'" rel="canonical" />';

$_G['forum_tagscript'] = '';

$threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
if($threadsort) {
	require_once libfile('function/threadsort');
	$threadsortshow = threadsortshow($thread['sortid'], $_G['tid']);
}

if(empty($_G['forum']['allowview'])) {

	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid']);
	}

} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

if($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	dheader("Location: $_G[siteurl]forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
	showmessage('thread_nopermission', NULL, array('readperm' => $_G['forum_thread']['readperm']), array('login' => 1));
}

$usemagic = array('user' => array(), 'thread' => array());

$replynotice = getstatus($_G['forum_thread']['status'], 6);

$hiddenreplies = getstatus($_G['forum_thread']['status'], 2);

$rushreply = getstatus($_G['forum_thread']['status'], 3);

$savepostposition = getstatus($_G['forum_thread']['status'], 1);

$incollection = getstatus($_G['forum_thread']['status'], 9);

$_G['forum_threadpay'] = FALSE;
if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
	if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
		C::t('forum_thread')->update($_G['tid'], array('price' => 0), false, false, $archiveid);
		$_G['forum_thread']['price'] = 0;
	} else {
		$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
		if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
			if(!(C::t('common_credit_log')->count_by_uid_operation_relatedid($_G['uid'], 'BTC', $_G['tid']))) {
				require_once libfile('thread/pay', 'include');
				$_G['forum_threadpay'] = TRUE;
			}
		}
	}
}

if($rushreply) {
	$rewardfloor = '';
	$rushresult = $rewardfloorarr = $rewardfloorarray = array();
	$rushresult = C::t('forum_threadrush')->fetch($_G['tid']);
	if($rushresult['creditlimit'] == -996) {
		$rushresult['creditlimit'] = '';
	}
	if((TIMESTAMP < $rushresult['starttimefrom'] || ($rushresult['starttimeto'] && TIMESTAMP > $rushresult['starttimeto']) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 >= $rushresult['stopfloor'])) && $_G['forum_thread']['closed'] == 0) {
		C::t('forum_thread')->update($_G['tid'], array('closed'=>1));
	} elseif(($rushresult['starttimefrom'] && TIMESTAMP > $rushresult['starttimefrom']) && $_G['forum_thread']['closed'] == 1) {
		if(($rushresult['starttimeto'] && TIMESTAMP < $rushresult['starttimeto'] || !$rushresult['starttimeto']) && ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 < $rushresult['stopfloor'] || !$rushresult['stopfloor'])) {
			C::t('forum_thread')->update($_G['tid'], array('closed'=>0));
		}
	}
	$rushresult['starttimefrom'] = $rushresult['starttimefrom'] ? dgmdate($rushresult['starttimefrom']) : '';
	$rushresult['starttimeto'] = $rushresult['starttimeto'] ? dgmdate($rushresult['starttimeto']) : '';
	$rushresult['creditlimit_title'] = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
}

if($_G['forum_thread']['replycredit'] > 0) {
	$_G['forum_thread']['replycredit_rule'] = C::t('forum_replycredit')->fetch($thread['tid']);
	$_G['forum_thread']['replycredit_rule']['remaining'] = $_G['forum_thread']['replycredit'] / $_G['forum_thread']['replycredit_rule']['extcredits'];
	$_G['forum_thread']['replycredit_rule']['extcreditstype'] = $_G['forum_thread']['replycredit_rule']['extcreditstype'] ? $_G['forum_thread']['replycredit_rule']['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
}
$_G['group']['raterange'] = $_G['setting']['modratelimit'] && $adminid == 3 && !$_G['forum']['ismoderator'] ? array() : $_G['group']['raterange'];

$_G['group']['allowgetattach'] = !empty($_G['forum']['allowgetattach']) || ($_G['group']['allowgetattach'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['group']['allowgetimage'] = !empty($_G['forum']['allowgetimage']) || ($_G['group']['allowgetimage'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['getattachcredits'] = '';
if($_G['forum_thread']['attachment']) {
	$exemptvalue = $_G['forum']['ismoderator'] ? 32 : 4;
	if(!($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 0, $_G['forum_thread']['fid']);
		$p = '';
		if($creditlog['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $creditlog['extcredits'.$i]) {
				$_G['getattachcredits'] .= $p.$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}
}

$exemptvalue = $_G['forum']['ismoderator'] ? 64 : 8;
$_G['forum_attachmentdown'] = $_G['group']['exempt'] & $exemptvalue;

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'];

$postlist = $_G['forum_attachtags'] = $attachlist = $_G['forum_threadstamp'] = array();
$aimgcount = 0;
$_G['forum_attachpids'] = array();

if(!empty($_GET['action']) && $_GET['action'] == 'printable' && $_G['tid']) {
	require_once libfile('thread/printable', 'include');
	dexit();
}

if($_G['forum_thread']['stamp'] >= 0) {
	$_G['forum_threadstamp'] = $_G['cache']['stamps'][$_G['forum_thread']['stamp']];
}

$lastmod = viewthread_lastmod($_G['forum_thread']);

$showsettings = str_pad(decbin($_G['setting']['showsettings']), 3, '0', STR_PAD_LEFT);

$showsignatures = $showsettings{0};
$showavatars = $showsettings{1};
$_G['setting']['showimages'] = $showsettings{2};

$highlightstatus = isset($_GET['highlight']) && str_replace('+', '', $_GET['highlight']) ? 1 : 0;

$_G['forum']['allowreply'] = isset($_G['forum']['allowreply']) ? $_G['forum']['allowreply'] : '';
$_G['forum']['allowpost'] = isset($_G['forum']['allowpost']) ? $_G['forum']['allowpost'] : '';

$allowpostreply = ($_G['forum']['allowreply'] != -1) && (($_G['forum_thread']['isgroup'] || (!$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
$fastpost = $_G['setting']['fastpost'] && !$_G['forum_thread']['archiveid'] && ($_G['forum']['status'] != 3 || $_G['isgroupuser']);
$allowfastpost = $_G['setting']['fastpost'] && $allowpostreply;
if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum']) || !$_G['adminid'] && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60)) {
	$allowfastpost = false;
}
$_G['group']['allowpost'] = $_G['forum']['allowpost'] != -1 && ((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || $_G['forum']['allowpost']);

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$allowpostattach = $allowpostreply && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
} else {
	$_G['group']['allowpostpoll'] = $_G['group']['allowposttrade'] = $_G['group']['allowpostreward'] = $_G['group']['allowpostactivity'] = $_G['group']['allowpostdebate'] = FALSE;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : dunserialize($_G['forum']['threadplugin']) : array();

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] && $_G['forum']['status'] != 3 ? visitedforums() : '';



$relatedthreadlist = array();
$relatedthreadupdate = $tagupdate = FALSE;
$relatedkeywords = $tradekeywords = $_G['forum_firstpid'] = '';

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'modarea_c') === FALSE) {
	$collapseimg['modarea_c'] = 'collapsed_no';
	$collapse['modarea_c'] = '';
} else {
	$collapseimg['modarea_c'] = 'collapsed_yes';
	$collapse['modarea_c'] = 'display: none';
}

$threadtag = array();
viewthread_updateviews($archiveid);

$_G['setting']['infosidestatus']['posts'] = $_G['setting']['infosidestatus'][1] && isset($_G['setting']['infosidestatus']['f'.$_G['fid']]['posts']) ? $_G['setting']['infosidestatus']['f'.$_G['fid']]['posts'] : $_G['setting']['infosidestatus']['posts'];


$postfieldsadd = $specialadd1 = $specialadd2 = $specialextra = '';
$tpids = array();
if($_G['forum_thread']['special'] == 2) {
	if(!empty($_GET['do']) && $_GET['do'] == 'tradeinfo') {
		require_once libfile('thread/trade', 'include');
	}
	$query = C::t('forum_trade')->fetch_all_thread_goods($_G['tid']);
	foreach($query as $trade) {
		$tpids[] = $trade['pid'];
	}
	$specialadd2 = 1;

} elseif($_G['forum_thread']['special'] == 5) {
	$_GET['stand'] = isset($_GET['stand']) && in_array($_GET['stand'], array(0, 1, 2)) ? $_GET['stand'] : null;
	if(isset($_GET['stand'])) {
		$specialadd2 = 1;
		$specialextra = "&amp;stand=$_GET[stand]";
	}
}

$onlyauthoradd = $threadplughtml = '';

$maxposition = 0;
if(empty($_GET['viewpid'])) {
	$disablepos = !$rushreply && C::t('forum_threaddisablepos')->fetch($_G['tid']) ? 1 : 0;
	if(!$disablepos && !in_array($_G['forum_thread']['special'], array(2,3,5))) {
		if($_G['forum_thread']['maxposition']) {
			$maxposition = $_G['forum_thread']['maxposition'];
		} else {
			$maxposition = C::t('forum_post')->fetch_maxposition_by_tid($posttableid, $_G['tid']);
		}
	}

	$ordertype = empty($_GET['ordertype']) && getstatus($_G['forum_thread']['status'], 4) ? 1 : $_GET['ordertype'];

	$sticklist = array();
	if($_G['forum_thread']['stickreply'] && $page == 1 && (!$_GET['authorid'] || $_GET['authorid'] == $_G['thread']['authorid'])) {
		$poststick = C::t('forum_poststick')->fetch_all_by_tid($_G['tid']);
		foreach(C::t('forum_post')->fetch_all($posttableid, array_keys($poststick)) as $post) {
			$post['position'] = $poststick[$post['pid']]['position'];
			$post['message'] = messagecutstr($post['message'], 400);
			$post['avatar'] = avatar($post['authorid'], 'small');
			$sticklist[$post['pid']] = $post;
		}
		$stickcount = count($sticklist);
	}
	if($rushreply) {
		$rushids = $rushpids = $rushpositionlist = $preg = $arr = array();
		$str = ',,';
		$preg_str = rushreply_rule($rushresult);
		if($_GET['checkrush']) {
			$maxposition = 0;
			for($i = 1; $i <= $_G['forum_thread']['replies'] + 1; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$temp_reply = $_G['forum_thread']['replies'];
			$_G['forum_thread']['replies'] = $countrushpost = max(0, count($rushids) - 1);
			$rushids = array_slice($rushids, ($page - 1) * $_G['ppp'], $_G['ppp']);
			foreach(C::t('forum_post')->fetch_all_by_tid_position($posttableid, $_G['tid'], $rushids) as $post) {
				$postarr[$post['position']] = $post;
			}
		} else {
			for($i = ($page - 1) * $_G['ppp'] + 1; $i <= $page * $_G['ppp']; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$_G['forum_thread']['replies'] = $_G['forum_thread']['replies'] - 1;
		}
	}

	if($_GET['authorid']) {
		$maxposition = 0;
		$_G['forum_thread']['replies'] = C::t('forum_post')->count_by_tid_invisible_authorid($_G['tid'], $_GET['authorid']);
		$_G['forum_thread']['replies']--;
		if($_G['forum_thread']['replies'] < 0) {
			showmessage('undefined_action');
		}
		$onlyauthoradd = 1;
	} elseif($_G['forum_thread']['special'] == 5) {
		if(isset($_GET['stand']) && $_GET['stand'] >= 0 && $_GET['stand'] < 3) {
			$_G['forum_thread']['replies'] = C::t('forum_debatepost')->count_by_tid_stand($_G['tid'], $_GET['stand']);
		} else {
			$_G['forum_thread']['replies'] = C::t('forum_post')->count_visiblepost_by_tid($_G['tid']);
			$_G['forum_thread']['replies'] > 0 && $_G['forum_thread']['replies']--;
		}
	} elseif($_G['forum_thread']['special'] == 2) {
		$tradenum = C::t('forum_trade')->fetch_counter_thread_goods($_G['tid']);
		$_G['forum_thread']['replies'] -= $tradenum;
	}

	if($maxposition) {
		$_G['forum_thread']['replies'] = $maxposition - 1;
	}
	$_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
	$totalpage = ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp']);
	$page > $totalpage && $page = $totalpage;
	$_G['forum_pagebydesc'] = !$maxposition && $page > 2 && $page > ($totalpage / 2) ? TRUE : FALSE;

	if($_G['forum_pagebydesc']) {
		$firstpagesize = ($_G['forum_thread']['replies'] + 1) % $_G['ppp'];
		$_G['forum_ppp3'] = $_G['forum_ppp2'] = $page == $totalpage && $firstpagesize ? $firstpagesize : $_G['ppp'];
		$realpage = $totalpage - $page + 1;
		if($firstpagesize == 0) {
			$firstpagesize = $_G['ppp'];
		}
		$start_limit = max(0, ($realpage - 2) * $_G['ppp'] + $firstpagesize);
		$_G['forum_numpost'] = ($page - 1) * $_G['ppp'];
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
		}
	} else {
		$start_limit = $_G['forum_numpost'] = max(0, ($page - 1) * $_G['ppp']);
		if($start_limit > $_G['forum_thread']['replies']) {
			$start_limit = $_G['forum_numpost'] = 0;
			$page = 1;
		}
		if($ordertype != 1) {
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
		}
	}
	$multipage = multi($_G['forum_thread']['replies'] + 1, $_G['ppp'], $page, 'forum.php?mod=viewthread&tid='.$_G['tid'].
		($_G['forum_thread']['is_archived'] ? '&archive='.$_G['forum_thread']['archiveid'] : '').
		'&amp;extra='.$_GET['extra'].
		($ordertype && $ordertype != getstatus($_G['forum_thread']['status'], 4) ? '&amp;ordertype='.$ordertype : '').
		(isset($_GET['highlight']) ? '&amp;highlight='.rawurlencode($_GET['highlight']) : '').
		(!empty($_GET['authorid']) ? '&amp;authorid='.$_GET['authorid'] : '').
		(!empty($_GET['from']) ? '&amp;from='.$_GET['from'] : '').
		(!empty($_GET['checkrush']) ? '&amp;checkrush='.$_GET['checkrush'] : '').
		(!empty($_GET['modthreadkey']) ? '&amp;modthreadkey='.rawurlencode($_GET['modthreadkey']) : '').
		$specialextra);
} else {
	$_GET['viewpid'] = intval($_GET['viewpid']);
	$pageadd = "AND p.pid='$_GET[viewpid]'";
}

$_G['forum_newpostanchor'] = $_G['forum_postcount'] = 0;

$_G['forum_onlineauthors'] = $_G['forum_cachepid'] = array();


$isdel_post = $cachepids = $postusers = $skipaids = array();

if($_G['forum_auditstatuson'] || in_array($_G['forum_thread']['displayorder'], array(-2, -3, -4)) && $_G['forum_thread']['authorid'] == $_G['uid']) {
	$visibleallflag = 1;
}

if($maxposition) {
	$start = ($page - 1) * $_G['ppp'] + 1;
	$end = $start + $_G['ppp'];
	if($ordertype == 1) {
		$end = $maxposition - ($page - 1) * $_G['ppp'] + ($page > 1 ? 2 : 1);
		$start = $end - $_G['ppp'] + ($page > 1 ? 0 : 1);
		$start = max(array(1,$start));
	}
	$have_badpost = $realpost = $lastposition = 0;
	foreach(C::t('forum_post')->fetch_all_by_tid_range_position($posttableid, $_G['tid'], $start, $end, $maxposition, $ordertype) as $post) {
		if($post['invisible'] != 0) {
			$have_badpost = 1;
		}
		$cachepids[$post[position]] = $post['pid'];
		$postarr[$post[position]] = $post;
		$lastposition = $post['position'];
	}
	$realpost = count($postarr);
	if($realpost != $_G['ppp'] || $have_badpost) {
		$k = 0;
		for($i = $start; $i < $end; $i ++) {
			if(!empty($cachepids[$i])) {
				$k = $cachepids[$i];
				$isdel_post[$i] = array('deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i);
			} elseif($i < $maxposition || ($lastposition && $i < $lastposition)) {
				$isdel_post[$i] = array('deleted' => 1, 'pid' => $k, 'message' => '', 'position' => $i);
			}
			$k ++;
		}
	}
	$pagebydesc = false;
}

if($_GET['checkrush'] && $rushreply) {
	$_G['forum_thread']['replies'] = $temp_reply;
}


if(!$maxposition && empty($postarr)) {

	if(empty($_GET['viewpid'])) {
		if($_G['forum_thread']['special'] == 2) {
			$postarr = C::t('forum_post')->fetch_all_tradepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $tpids, $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		} elseif($_G['forum_thread']['special'] == 5) {
			$postarr = C::t('forum_post')->fetch_all_debatepost_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_GET['stand'], $_G['forum_pagebydesc'], $ordertype, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		} else {
			$postarr = C::t('forum_post')->fetch_all_common_viewthread_by_tid($_G['tid'], $visibleallflag, $_GET['authorid'], $_G['forum_pagebydesc'], $ordertype, $_G['forum_thread']['replies'] + 1, $start_limit, ($_G['forum_pagebydesc'] ? $_G['forum_ppp2'] : $_G['ppp']));
		}
	} else {
		$post = array();
		if($_G['forum_thread']['special'] == 2) {
			if(!in_array($_GET['viewpid'], $tpids)) {
				$post = C::t('forum_post')->fetch('tid:'.$_G['tid'],$_GET['viewpid']);
			}
		} elseif($_G['forum_thread']['special'] == 5) {
			$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
			$debatpost = C::t('forum_debatepost')->fetch($_GET['viewpid']);
			if(!isset($_GET['stand']) || (isset($_GET['stand']) && ($post['first'] == 1 || $debatpost['stand'] == $_GET['stand']))) {
				$post = array_merge($post, $debatpost);
			} else {
				$post = array();
			}
			unset($debatpost);
		} else {
			$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $_GET['viewpid']);
		}

		if($post) {
			if($visibleallflag || (!$visibleallflag && !$post['invisible'])) {
				$postarr[0] = $post;
			}
		}
	}

}
if(!empty($isdel_post)) {
	$updatedisablepos = false;
	foreach($isdel_post as $id => $post) {
		if(isset($postarr[$id]['invisible']) && ($postarr[$id]['invisible'] == 0 || $postarr[$id]['invisible'] == -3 || $visibleallflag)) {
			continue;
		}
		$postarr[$id] = $post;
		$updatedisablepos = true;
	}
	if($updatedisablepos && !$rushreply) {
		C::t('forum_threaddisablepos')->insert(array('tid' => $_G['tid']), false, true);
	}
	$ordertype != 1 ? ksort($postarr) : krsort($postarr);
}
$summary = '';
if($page == 1 && $ordertype == 1) {
	$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	if($firstpost['invisible'] == 0 || $visibleallflag == 1) {
		$postarr = array_merge(array($firstpost), $postarr);
		unset($firstpost);
	}
}
$tagnames = $locationpids = array();
foreach($postarr as $post) {
	if(($onlyauthoradd && $post['anonymous'] == 0) || !$onlyauthoradd) {
		$postusers[$post['authorid']] = array();
		if($post['first']) {
			if($ordertype == 1 && $page != 1) {
				continue;
			}
			$_G['forum_firstpid'] = $post['pid'];
			if(IS_ROBOT || $_G['adminid'] == 1) $summary = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($post['message']), 160));
			$tagarray_all = $posttag_array = array();
			$tagarray_all = explode("\t", $post['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$tag = explode(',', $var);
						$posttag_array[] = $tag;
						$tagnames[] = $tag[1];
					}
				}
			}
			$post['tags'] = $posttag_array;
			if($post['tags']) {
				$post['relateitem'] = getrelateitem($post['tags'], $post['tid'], $_G['setting']['relatenum'], $_G['setting']['relatetime']);
			}
			if(!$_G['forum']['disablecollect']) {
				if($incollection) {
					$post['relatecollection'] = getrelatecollection($post['tid'], false, $post['releatcollectionnum'], $post['releatcollectionmore']);
					if($_G['group']['allowcommentcollection'] && $_GET['ctid']) {
						$ctid = dintval($_GET['ctid']);
						$post['sourcecollection'] = C::t('forum_collection')->fetch($ctid);
					}
				} else {
					$post['releatcollectionnum'] = 0;
				}
			}
		}
		$postlist[$post['pid']] = $post;
	}
}

$seodata = array('forum' => $_G['forum']['name'], 'fup' => $_G['cache']['forums'][$fup]['name'], 'subject' => $_G['forum_thread']['subject'], 'summary' => $summary, 'tags' => @implode(',', $tagnames), 'page' => intval($_GET['page']));
if($_G['forum']['status'] != 3) {
	$seotype = 'viewthread';
} else {
	$seotype = 'viewthread_group';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata);
if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($_G['forum_thread']['subject'], $_G['page']).' - '.strip_tags($_G['forum']['name']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = strip_tags($thread['subject']);
}
if(!$metadescription) {
	$metadescription = $summary.' '.strip_tags($_G['forum_thread']['subject']);
}

$postno = & $_G['cache']['custominfo']['postno'];
if($postusers) {
	$member_verify = $member_field_forum = $member_status = $member_count = $member_profile = $member_field_home = array();
	$uids = array_keys($postusers);
	$uids = array_filter($uids);
	if($_G['setting']['verify']['enabled']) {
		$member_verify = C::t('common_member_verify')->fetch_all($uids);
	}
	$member_field_forum = C::t('common_member_field_forum')->fetch_all($uids);
	$member_status = C::t('common_member_status')->fetch_all($uids);
	$member_count = C::t('common_member_count')->fetch_all($uids);
	$member_profile = C::t('common_member_profile')->fetch_all($uids);
	$member_field_home = C::t('common_member_field_home')->fetch_all($uids);
	foreach(C::t('common_member')->fetch_all($uids) as $uid => $postuser) {
		$member_field_home[$uid]['privacy'] = empty($member_field_home[$uid]['privacy']) ? array() : dunserialize($member_field_home[$uid]['privacy']);
		$postuser['memberstatus'] = $postuser['status'];
		$postuser['authorinvisible'] = $member_status[$uid]['invisible'];
		$postuser['signature'] = $member_field_forum[$uid]['sightml'];
		unset($member_field_home[$uid]['privacy']['feed'], $member_field_home[$uid]['privacy']['view'], $postuser['status'], $member_status[$uid]['invisible'], $member_field_forum[$uid]['sightml']);
		$postusers[$uid] = array_merge((array)$member_verify[$uid], (array)$member_field_home[$uid], (array)$member_profile[$uid], (array)$member_count[$uid], (array)$member_status[$uid], (array)$member_field_forum[$uid], $postuser);
		if($postusers[$uid]['regdate'] + $postusers[$uid]['oltime'] * 3600 > TIMESTAMP) {
			$postusers[$uid]['oltime'] = 0;
		}
		$postusers[$uid]['office'] = $postusers[$uid]['position'];
		unset($postusers[$uid]['position']);
	}
	unset($member_field_forum, $member_status, $member_count, $member_profile, $member_field_home);
	$_G['medal_list'] = array();
	foreach($postlist as $pid => $post) {
		if(getstatus($post['status'], 6)) {
			$locationpids[] = $pid;
		}
		$post = array_merge($postlist[$pid], (array)$postusers[$post['authorid']]);
		$postlist[$pid] = viewthread_procpost($post, $_G['member']['lastvisit'], $ordertype, $maxposition);
	}

}

if($locationpids) {
	$locations = C::t('forum_post_location')->fetch_all($locationpids);
}

if($postlist && $rushids) {
	foreach($postlist as $pid => $post) {
		$post['number'] = $post['position'];
		$postlist[$pid] = checkrushreply($post);
	}
}


if($_G['forum_thread']['special'] > 0 && (empty($_GET['viewpid']) || $_GET['viewpid'] == $_G['forum_firstpid'])) {
	$_G['forum_thread']['starttime'] = gmdate($_G['forum_thread']['dateline']);
	$_G['forum_thread']['remaintime'] = '';
	switch($_G['forum_thread']['special']) {
		case 1: require_once libfile('thread/poll', 'include'); break;
		case 2: require_once libfile('thread/trade', 'include'); break;
		case 3: require_once libfile('thread/reward', 'include'); break;
		case 4: require_once libfile('thread/activity', 'include'); break;
		case 5: require_once libfile('thread/debate', 'include'); break;
		case 127:
			if($_G['forum_firstpid']) {
				$sppos = strpos($postlist[$_G['forum_firstpid']]['message'], chr(0).chr(0).chr(0));
				$specialextra = substr($postlist[$_G['forum_firstpid']]['message'], $sppos + 3);
				$postlist[$_G['forum_firstpid']]['message'] = substr($postlist[$_G['forum_firstpid']]['message'], 0, $sppos);
				if($specialextra) {
					if(array_key_exists($specialextra, $_G['setting']['threadplugins'])) {
						@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
						$classname = 'threadplugin_'.$specialextra;
						if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'viewthread')) {
							$threadplughtml = $threadpluginclass->viewthread($_G['tid']);
						}
					}
				}
			}
			break;
	}
}
if(empty($_GET['authorid']) && empty($postlist)) {
	if($rushreply) {
		dheader("Location: forum.php?mod=redirect&tid=$_G[tid]&goto=lastpost");
	} else {
		$replies = C::t('forum_post')->count_visiblepost_by_tid($_G['tid']);
		$replies = intval($replies) - 1;
		if($_G['forum_thread']['replies'] != $replies && $replies > 0) {
			C::t('forum_thread')->update($_G['tid'], array('replies' => $replies), false, false, $archiveid);
			dheader("Location: forum.php?mod=redirect&tid=$_G[tid]&goto=lastpost");
		}
	}
}

if($_G['forum_pagebydesc'] && (!$savepostposition || $_GET['ordertype'] == 1)) {
	$postlist = array_reverse($postlist, TRUE);
}

if(!empty($_G['setting']['sessionclose'])) {
	$_G['setting']['vtonlinestatus'] = 1;
}

if($_G['setting']['vtonlinestatus'] == 2 && $_G['forum_onlineauthors']) {
	foreach(C::app()->session->fetch_all_by_uid(array_keys($_G['forum_onlineauthors'])) as $author) {
		if(!$author['invisible']) {
			$_G['forum_onlineauthors'][$author['uid']] = 1;
		}
	}
} else {
	$_G['forum_onlineauthors'] = array();
}
$ratelogs = $comments = $commentcount = $totalcomment = array();
if($_G['forum_cachepid']) {
	foreach(C::t('forum_postcache')->fetch_all($_G['forum_cachepid']) as $postcache) {
		if($postcache['rate']) {
			$postcache['rate'] = dunserialize($postcache['rate']);
			$postlist[$postcache['pid']]['ratelog'] = $postcache['rate']['ratelogs'];
			$postlist[$postcache['pid']]['ratelogextcredits'] = $postcache['rate']['extcredits'];
			$postlist[$postcache['pid']]['totalrate'] = $postcache['rate']['totalrate'];
		}
		if($postcache['comment']) {
			$postcache['comment'] = dunserialize($postcache['comment']);
			$commentcount[$postcache['pid']] = $postcache['comment']['count'];
			$comments[$postcache['pid']] = $postcache['comment']['data'];
			$totalcomment[$postcache['pid']] = $postcache['comment']['totalcomment'];
		}
		unset($_G['forum_cachepid'][$postcache['pid']]);
	}
	$postcache = $ratelogs = array();
	if($_G['forum_cachepid']) {
		$ratelogs = C::t('forum_ratelog')->fetch_postrate_by_pid($_G['forum_cachepid'], $postlist, $postcache, $_G['setting']['ratelogrecord']);
	}
	foreach($postlist as $key => $val) {
		if(!empty($val['ratelogextcredits'])) {
			ksort($postlist[$key]['ratelogextcredits']);
		}
	}

	if($_G['forum_cachepid'] && $_G['setting']['commentnumber']) {
		$comments = C::t('forum_postcomment')->fetch_postcomment_by_pid($_G['forum_cachepid'], $postcache, $commentcount, $totalcomment, $_G['setting']['commentnumber']);
	}

	foreach($postcache as $pid => $data) {
		C::t('forum_postcache')->insert(array('pid' => $pid, 'rate' => serialize($data['rate']), 'comment' => serialize($data['comment']), 'dateline' => TIMESTAMP), false, true);
	}
}

if($_G['forum_attachpids'] && !defined('IN_ARCHIVER')) {
	require_once libfile('function/attachment');
	if(is_array($threadsortshow) && !empty($threadsortshow['sortaids'])) {
		$skipaids = $threadsortshow['sortaids'];
	}
	parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}

if(empty($postlist)) {
	showmessage('post_not_found');
} elseif(!defined('IN_MOBILE_API')) {
	foreach($postlist as $pid => $post) {
		$postlist[$pid]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $postlist[$pid]['message']);
	}
}

if(defined('IN_ARCHIVER')) {
	include loadarchiver('forum/viewthread');
	exit();
}

$_G['forum_thread']['heatlevel'] = $_G['forum_thread']['recommendlevel'] = 0;
if($_G['setting']['heatthread']['iconlevels']) {
	foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
		if($_G['forum_thread']['heats'] > $i) {
			$_G['forum_thread']['heatlevel'] = $k + 1;
			break;
		}
	}
}

if(!empty($_G['setting']['recommendthread']['status']) && $_G['forum_thread']['recommends']) {
	foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
		if($_G['forum_thread']['recommends'] > $i) {
			$_G['forum_thread']['recommendlevel'] = $k+1;
			break;
		}
	}
}

$allowblockrecommend = $_G['group']['allowdiy'] || getstatus($_G['member']['allowadmincp'], 4) || getstatus($_G['member']['allowadmincp'], 5) || getstatus($_G['member']['allowadmincp'], 6);
if($_G['setting']['portalstatus']) {
	$allowpostarticle = $_G['group']['allowmanagearticle'] || $_G['group']['allowpostarticle'] || getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3);
	$allowpusharticle = empty($_G['forum_thread']['special']) && empty($_G['forum_thread']['sortid']) && !$_G['forum_thread']['pushedaid'];
} else {
	$allowpostarticle = $allowpusharticle = false;
}
if($_G['forum_thread']['displayorder'] != -4) {
	$modmenu = array(
		'thread' => $_G['forum']['ismoderator'] || $allowblockrecommend || $allowpusharticle && $allowpostarticle,
		'post' => $_G['forum']['ismoderator'] && ($_G['group']['allowwarnpost'] || $_G['group']['allowbanpost'] || $_G['group']['allowdelpost'] || $_G['group']['allowstickreply']) || $_G['forum_thread']['pushedaid'] && $allowpostarticle || $_G['forum_thread']['authorid'] == $_G['uid']
	);
} else {
	$modmenu = array();
}

if($_G['forum']['alloweditpost'] && $_G['uid']) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $_G['forum_thread']['special'] + 1);
	if(!$alloweditpost_status) {
		$edittimelimit = $_G['group']['edittimelimit'] * 60;
	}
}

if($_G['forum_thread']['replies'] > $_G['forum_thread']['views']) {
	$_G['forum_thread']['views'] = $_G['forum_thread']['replies'];
}

require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
$_G['forum_thread']['relay'] = 0;

if(getstatus($_G['forum_thread']['status'], 10)) {
	$preview = C::t('forum_threadpreview')->fetch($_G['tid']);
	$_G['forum_thread']['relay'] = $preview['relay'];
}

if(empty($_GET['viewpid'])) {
	$sufix = '';
	if($_GET['from'] == 'portal') {
		$_G['disabledwidthauto'] = 1;
		$_G['widthauto'] = 0;
		$sufix = '_portal';
		$post = &$postlist[$_G['forum_firstpid']];
	}
	include template('diy:forum/viewthread'.$sufix.':'.$_G['fid']);
} else {
	$_G['setting']['admode'] = 0;
	$post = $postlist[$_GET['viewpid']];
	if($maxposition) {
		$post['number'] = $post['position'];
	} else {
		$post['number'] = C::t('forum_post')->count_by_tid_dateline($posttableid, $post['tid'], $post['dbdateline']);
	}
	if($rushreply) {
		$post['number'] = $post['position'];
		$preg_str = rushreply_rule($rewardfloorarr);
		preg_match_all($preg_str, ",,".$post['number'].",,", $arr);
		if($post['number'] == str_replace(",", '', $arr['0']['0'])) {
			$post['rewardfloor'] = 1;
		}
	}
	include template('common/header_ajax');
	hookscriptoutput('viewthread');
	$postcount = 0;
	if($_GET['from']) {
		include template('forum/viewthread_from_node');
	} else {
		include template('forum/viewthread_node');
	}
	include template('common/footer_ajax');
}



function viewthread_updateviews($tableid) {
	global $_G;

	if(!$_G['setting']['preventrefresh'] || $_G['cookie']['viewid'] != 'tid_'.$_G['tid']) {
		if(!$tableid && $_G['setting']['optimizeviews']) {
			if($_G['forum_thread']['addviews']) {
				if($_G['forum_thread']['addviews'] < 100) {
					C::t('forum_threadaddviews')->update_by_tid($_G['tid']);
				} else {
					if(!discuz_process::islocked('update_thread_view')) {
						$row = C::t('forum_threadaddviews')->fetch($_G['tid']);
						C::t('forum_threadaddviews')->update($_G['tid'], array('addviews' => 0));
						C::t('forum_thread')->increase($_G['tid'], array('views' => $row['addviews']+1), true);
						discuz_process::unlock('update_thread_view');
					}
				}
			} else {
				C::t('forum_threadaddviews')->insert(array('tid' => $_G['tid'], 'addviews' => 1), false, true);
			}
		} else {
			C::t('forum_thread')->increase($_G['tid'], array('views' => 1), true, $tableid);
		}
	}
	dsetcookie('viewid', 'tid_'.$_G['tid']);
}

function viewthread_procpost($post, $lastvisit, $ordertype, $maxposition = 0) {
	global $_G, $rushreply;

	if(!$_G['forum_newpostanchor'] && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$_G['forum_newpostanchor'] = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = ($ordertype != 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies']) || ($ordertype == 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies'] + 2) ? '<a name="lastpost"></a>' : '';

	if($_G['forum_pagebydesc']) {
		if($ordertype != 1) {
			$post['number'] = $_G['forum_numpost'] + $_G['forum_ppp2']--;
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : ($_G['forum_numpost'] - 1) - $_G['forum_ppp2']--;
		}
	} else {
		if($ordertype != 1) {
			$post['number'] = ++$_G['forum_numpost'];
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : --$_G['forum_numpost'];
			$post['number'] = $post['number'] - 1;
		}
	}

	if($maxposition) {
		$post['number'] = $post['position'];
	}
	$_G['forum_postcount']++;

	$post['dbdateline'] = $post['dateline'];
	$post['dateline'] = dgmdate($post['dateline'], 'u', '9999', getglobal('setting/dateformat').' H:i:s');
	$post['groupid'] = $_G['cache']['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {

		$_G['forum_onlineauthors'][$post['authorid']] = 0;
		$post['usernameenc'] = rawurlencode($post['username']);
		$post['readaccess'] = $_G['cache']['usergroups'][$post['groupid']]['readaccess'];
		if($_G['cache']['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_G['cache']['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_G['cache']['usergroups'][$post['groupid']]['stars'];
		}
		$post['upgradecredit'] = false;
		if($_G['cache']['usergroups'][$post['groupid']]['type'] == 'member' && $_G['cache']['usergroups'][$post['groupid']]['creditslower'] != 999999999) {
			$post['upgradecredit'] = $_G['cache']['usergroups'][$post['groupid']]['creditslower'] - $post['credits'];
		}

		$post['taobaoas'] = addslashes($post['taobao']);
		$post['regdate'] = dgmdate($post['regdate'], 'd');
		$post['lastdate'] = dgmdate($post['lastvisit'], 'd');

		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';

		if($post['medals']) {
			loadcache('medals');
			foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
				list($medalid, $medalexpiration) = explode("|", $medalid);
				if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
					$post['medals'][$key] = $_G['cache']['medals'][$medalid];
					$post['medals'][$key]['medalid'] = $medalid;
					$_G['medal_list'][$medalid] = $_G['cache']['medals'][$medalid];
				} else {
					unset($post['medals'][$key]);
				}
			}
		}

		$post['avatar'] = avatar($post['authorid']);
		$post['groupicon'] = $post['avatar'] ? g_icon($post['groupid'], 1) : '';
		$post['banned'] = $post['status'] & 1;
		$post['warned'] = ($post['status'] & 2) >> 1;

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = array();
	$post['imagelist'] = $post['attachlist'] = '';

	if($post['attachment']) {
		if($_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
			$_G['forum_attachpids'][] = $post['pid'];
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $post['message']);
		}
	}

	if($_G['setting']['ratelogrecord'] && $post['ratetimes']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	if($_G['setting']['commentnumber'] && ($post['first'] && $_G['setting']['commentfirstpost'] || !$post['first']) && $post['comment']) {
		$_G['forum_cachepid'][$post['pid']] = $post['pid'];
	}
	$post['allowcomment'] = $_G['setting']['commentnumber'] && in_array(1, $_G['setting']['allowpostcomment']) && ($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) &&
		($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) ||
		(!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3))));
	$forum_allowbbcode = $_G['forum']['allowbbcode'] ? -$post['groupid'] : 0;
	$post['signature'] = $post['usesig'] ? ($_G['setting']['sigviewcond'] ? (strlen($post['message']) > $_G['setting']['sigviewcond'] ? $post['signature'] : '') : $post['signature']) : '';
	if(!defined('IN_ARCHIVER')) {
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $forum_allowbbcode, ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload'], $post['dbdateline']);
		if($post['first']) {
			$_G['relatedlinks'] = '';
			$relatedtype = !$_G['forum_thread']['isgroup'] ? 'forum' : 'group';
			if(!$_G['setting']['relatedlinkstatus']) {
				$_G['relatedlinks'] = get_related_link($relatedtype);
			} else {
				$post['message'] = parse_related_link($post['message'], $relatedtype);
			}

		}
	}
	$_G['forum_firstpid'] = intval($_G['forum_firstpid']);
	$post['custominfo'] = viewthread_custominfo($post);
	$post['mobiletype'] = getstatus($post['status'], 4) ? base_convert(getstatus($post['status'], 10).getstatus($post['status'], 9).getstatus($post['status'], 8), 2, 10) : 0;
	return $post;
}

function viewthread_loadcache() {
	global $_G;
	$_G['forum']['livedays'] = ceil((TIMESTAMP - $_G['forum']['dateline']) / 86400);
	$_G['forum']['lastpostdays'] = ceil((TIMESTAMP - $_G['forum']['lastthreadpost']) / 86400);
	$threadcachemark = 100 - (
	$_G['forum']['displayorder'] * 15 +
	$_G['thread']['digest'] * 10 +
	min($_G['thread']['views'] / max($_G['forum']['livedays'], 10) * 2, 50) +
	max(-10, (15 - $_G['forum']['lastpostdays'])) +
	min($_G['thread']['replies'] / $_G['setting']['postperpage'] * 1.5, 15));
	if($threadcachemark < $_G['forum']['threadcaches']) {

		$threadcache = getcacheinfo($_G['tid']);

		if(TIMESTAMP - $threadcache['filemtime'] > $_G['setting']['cachethreadlife']) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
		} else {
			readfile($threadcache['filename']);

			viewthread_updateviews($_G['forum_thread']['threadtableid']);
			$_G['setting']['debug'] && debuginfo();
			$_G['setting']['debug'] ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($_G['setting']['debug'] ? 'Updated at '.gmdate("H:i:s", $threadcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($_G['gzipcompress'] ? ', Gzip enabled' : '') : '').'";</script>') : die();
		}
	}
}

function viewthread_lastmod(&$thread) {
	global $_G;
	if(!$thread['moderated']) {
		return array();
	}
	$lastmod = array();
	$lastlog = C::t('forum_threadmod')->fetch_by_tid($thread['tid']);
	if($lastlog) {
		$lastmod = array(
					'moduid' => $lastlog['uid'],
					'modusername' => $lastlog['username'],
					'moddateline' => $lastlog['dateline'],
					'modaction' => $lastlog['action'],
					'magicid' => $lastlog['magicid'],
					'stamp' => $lastlog['stamp'],
					'reason' => $lastlog['reason']
				);
	}
	if($lastmod) {
		$modactioncode = lang('forum/modaction');
		$lastmod['modusername'] = $lastmod['modusername'] ? $lastmod['modusername'] : 'System';
		$lastmod['moddateline'] = dgmdate($lastmod['moddateline'], 'u');
		$lastmod['modactiontype'] = $lastmod['modaction'];
		if($modactioncode[$lastmod['modaction']]) {
			$lastmod['modaction'] = $modactioncode[$lastmod['modaction']].($lastmod['modaction'] != 'SPA' ? '' : ' '.$_G['cache']['stamps'][$lastmod['stamp']]['text']);
		} elseif(substr($lastmod['modaction'], 0, 1) == 'L' && preg_match('/L(\d\d)/', $lastmod['modaction'], $a)) {
			$lastmod['modaction'] = $modactioncode['SLA'].' '.$_G['cache']['stamps'][intval($a[1])]['text'];
		} else {
			$lastmod['modaction'] = '';
		}
		if($lastmod['magicid']) {
			loadcache('magics');
			$lastmod['magicname'] = $_G['cache']['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		C::t('forum_thread')->update($thread['tid'], array('moderated' => 0), false, false, $thread['threadtableid']);
		$thread['moderated'] = 0;
	}
	return $lastmod;
}

function viewthread_custominfo($post) {
	global $_G;

	$types = array('left', 'menu');
	foreach($types as $type) {
		if(!is_array($_G['cache']['custominfo']['setting'][$type])) {
			continue;
		}
		$data = '';
		foreach($_G['cache']['custominfo']['setting'][$type] as $key => $order) {
			$v = '';
			if(substr($key, 0, 10) == 'extcredits') {
				$i = substr($key, 10);
				$extcredit = $_G['setting']['extcredits'][$i];
				if($extcredit) {
					$v = '<dt>'.($extcredit['img'] ? $extcredit['img'].' ' : '').$extcredit['title'].'</dt><dd>'.$post['extcredits'.$i].' '.$extcredit['unit'].'</dd>';
				}
			} elseif(substr($key, 0, 6) == 'field_') {
				$field = substr($key, 6);
				if(!empty($post['privacy']['profile'][$field])) {
					continue;
				}
				require_once libfile('function/profile');
				$v = profile_show($field, $post);
				if($v) {
					$v = '<dt>'.$_G['cache']['custominfo']['profile'][$key][0].'</dt><dd title="'.dhtmlspecialchars(strip_tags($v)).'">'.$v.'</dd>';
				}
			} elseif($key == 'creditinfo') {
				$v = '<dt>'.lang('space', 'viewthread_userinfo_buyercredit').'</dt><dd><a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#buyercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['buyercredit']).'.gif" /></a></dd>';
				$v .= '<dt>'.lang('space', 'viewthread_userinfo_sellercredit').'</dt><dd><a href="home.php?mod=space&uid='.$post['uid'].'&do=trade&view=eccredit#sellercredit" target="_blank" class="vm"><img src="'.STATICURL.'image/traderank/seller/'.countlevel($post['sellercredit']).'.gif" /></a></dd>';
			} else {
				switch($key) {
					case 'uid': $v = $post['uid'];break;
					case 'posts': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=reply&view=me&from=space" target="_blank" class="xi2">'.$post['posts'].'</a>';break;
					case 'threads': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=thread&view=me&from=space" target="_blank" class="xi2">'.$post['threads'].'</a>';break;
					case 'doings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=doing&view=me&from=space" target="_blank" class="xi2">'.$post['doings'].'</a>';break;
					case 'blogs': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=blog&view=me&from=space" target="_blank" class="xi2">'.$post['blogs'].'</a>';break;
					case 'albums': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=album&view=me&from=space" target="_blank" class="xi2">'.$post['albums'].'</a>';break;
					case 'sharings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=share&view=me&from=space" target="_blank" class="xi2">'.$post['sharings'].'</a>';break;
					case 'friends': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=friend&view=me&from=space" target="_blank" class="xi2">'.$post['friends'].'</a>';break;
					case 'follower': $v = '<a href="home.php?mod=follow&do=follower&uid='.$post['uid'].'" target="_blank" class="xi2">'.$post['follower'].'</a>';break;
					case 'following': $v = '<a href="home.php?mod=follow&do=following&uid='.$post['uid'].'" target="_blank" class="xi2">'.$post['following'].'</a>';break;
					case 'digest': $v = $post['digestposts'];break;
					case 'credits': $v = $post['credits'];break;
					case 'readperm': $v = $post['readaccess'];break;
					case 'regtime': $v = $post['regdate'];break;
					case 'lastdate': $v = $post['lastdate'];break;
					case 'oltime': $v = $post['oltime'].' '.lang('space', 'viewthread_userinfo_hour');break;
				}
				if($v !== '') {
					$v = '<dt>'.lang('space', 'viewthread_userinfo_'.$key).'</dt><dd>'.$v.'</dd>';
				}
			}
			$data .= $v;
		}
		$return[$type] = $data;
	}
	return $return;
}
function countlevel($usercredit) {
	global $_G;

	$rank = 0;
	if($usercredit){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($usercredit <= $credit) {
				$rank = $level;
				break;
			}
		}
	}
	return $rank;
}
function remaintime($time) {
	$days = intval($time / 86400);
	$time -= $days * 86400;
	$hours = intval($time / 3600);
	$time -= $hours * 3600;
	$minutes = intval($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	return array((int)$days, (int)$hours, (int)$minutes, (int)$seconds);
}

function getrelateitem($tagarray, $tid, $relatenum, $relatetime, $relatecache = '', $type = 'tid') {
	$tagidarray = $relatearray = $relateitem = array();
	$updatecache = 0;
	$limit = $relatenum;
	if(!$limit) {
		return '';
	}
	foreach($tagarray as $var) {
		$tagidarray[] = $var['0'];
	}
	if(!$tagidarray) {
		return '';
	}
	if(empty($relatecache)) {
		$thread = C::t('forum_thread')->fetch($tid);
		$relatecache = $thread['relatebytag'];
	}
	if($relatecache) {
		$relatecache = explode("\t", $relatecache);
		if(TIMESTAMP > $relatecache[0] + $relatetime * 60) {
			$updatecache = 1;
		} else {
			if(!empty($relatecache[1])) {
				$relatearray = explode(',', $relatecache[1]);
			}
		}
	} else {
		$updatecache = 1;
	}
	if($updatecache) {
		$query = C::t('common_tagitem')->select($tagidarray, $tid, $type, '', '', $limit, 0, '<>');
		foreach($query as $result) {
			if($result['itemid']) {
				$relatearray[] = $result['itemid'];
			}
		}
		if($relatearray) {
			$relatebytag = implode(',', $relatearray);
		}
		C::t('forum_thread')->update($tid, array('relatebytag'=>TIMESTAMP."\t".$relatebytag));
	}


	if(!empty($relatearray)) {
		foreach(C::t('forum_thread')->fetch_all_by_tid($relatearray) as $result) {
			if($result['displayorder'] >= 0) {
				$relateitem[] = $result;
			}
		}
	}
	return $relateitem;
}

function rushreply_rule () {
	global $rushresult;
	if(!empty($rushresult['rewardfloor'])) {
		$rushresult['rewardfloor'] = preg_replace('/\*+/', '*', $rushresult['rewardfloor']);
		$rewardfloorarr = explode(',', $rushresult['rewardfloor']);
		if($rewardfloorarr) {
			foreach($rewardfloorarr as $var) {
				$var = trim($var);
				if(strlen($var) > 1) {
					$var = str_replace('*', '[^,]?[\d]*', $var);
				} else {
					$var = str_replace('*', '\d+', $var);
				}
				$preg[] = "(,$var,)";
			}
			$preg_str = "/".implode('|', $preg)."/";
		}
	}
	return $preg_str;
}

function checkrushreply($post) {
	global $_G, $rushids;
	if($_GET['authorid']) {
		return $post;
	}
	if(in_array($post['number'], $rushids)) {
		$post['rewardfloor'] = 1;
	}
	return $post;
}

?>