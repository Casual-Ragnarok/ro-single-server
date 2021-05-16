<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newthread.php 32176 2012-11-23 03:23:39Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	if(!defined('IN_MOBILE')) {
		showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
	} else {
		showmessage('postperm_login_nopermission_mobile', NULL, array('referer' => rawurlencode(dreferer())), array('login' => 1));
	}
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('postperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessagenoperm('postperm', $_G['fid'], $_G['forum']['formulaperm']);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {

	$savethreads = array();
	$savethreadothers = array();
	foreach(C::t('forum_post')->fetch_all_by_authorid(0, $_G['uid'], false, '', 0, 20, 1, -3) as $savethread) {
		$savethread['dateline'] = dgmdate($savethread['dateline'], 'u');
		if($_G['fid'] == $savethread['fid']) {
			$savethreads[] = $savethread;
		} else {
			$savethreadothers[] = $savethread;
		}
	}
	$savethreadcount = count($savethreads);
	$savethreadothercount = count($savethreadothers);
	if($savethreadothercount) {
		loadcache('forums');
	}
	$savecount = $savethreadcount + $savethreadothercount;
	unset($savethread);

	$isfirstpost = 1;
	$allownoticeauthor = 1;
	$tagoffcheck = '';
	$showthreadsorts = !empty($sortid) || $_G['forum']['threadsorts']['required'] && empty($special);
	if(empty($sortid) && empty($special) && $_G['forum']['threadsorts']['required'] && $_G['forum']['threadsorts']['types']) {
		$tmp = array_keys($_G['forum']['threadsorts']['types']);
		$sortid = $tmp[0];

		require_once libfile('post/threadsorts', 'include');
	}

	if($special == 2 && $_G['group']['allowposttrade']) {

		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));

	} elseif($specialextra) {

		$threadpluginclass = null;
		if(isset($_G['setting']['threadplugins'][$specialextra]['module'])) {
			$threadpluginfile = DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
			if(file_exists($threadpluginfile)) {
				@include_once $threadpluginfile;
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread')) {
					$threadplughtml = $threadpluginclass->newthread($_G['fid']);
					$buttontext = lang('plugin/'.$specialextra, $threadpluginclass->buttontext);
					$iconfile = $threadpluginclass->iconfile;
					$iconsflip = array_flip($_G['cache']['icons']);
					$thread['iconid'] = $iconsflip[$iconfile];
				}
			}
		}

		if(!is_object($threadpluginclass)) {
			$specialextra = '';
		}
	}

	if($special == 4) {
		$activity = array('starttimeto' => '', 'starttimefrom' => '', 'place' => '', 'class' => '', 'cost' => '', 'number' => '', 'gender' => '', 'expiration' => '');
		$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	!isset($attachs['unused']) && $attachs['unused'] = array();
	!isset($imgattachs['unused']) && $imgattachs['unused'] = array();

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {

	if(trim($subject) == '') {
		showmessage('post_sm_isnull');
	}

	if(!$sortid && !$special && trim($message) == '') {
		showmessage('post_sm_isnull');
	}

	if($post_invalid = checkpost($subject, $message, ($special || $sortid))) {
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	}

	if(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxperhour('tid')) {
		showmessage('thread_flood_ctrl_threads_per_hour', '', array('threads_per_hour' => $_G['group']['maxthreadsperhour']));
	}
	$_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;

	if ($_G['group']['allowsetpublishdate'] && $_GET['cronpublish'] && $_GET['cronpublishdate']) {
		$publishdate = strtotime($_GET['cronpublishdate']);
		if ($publishdate > $_G['timestamp']) {
			$_GET['save'] = 1;
		} else {
			$publishdate = $_G['timestamp'];
		}
	} else {
		$publishdate = $_G['timestamp'];
	}

	$typeid = isset($typeid) && isset($_G['forum']['threadtypes']['types'][$typeid]) && (empty($_G['forum']['threadtypes']['moderators'][$typeid]) || $_G['forum']['ismoderator']) ? $typeid : 0;
	$displayorder = $modnewthreads ? -2 : (($_G['forum']['ismoderator'] && $_G['group']['allowstickthread'] && !empty($_GET['sticktopic'])) ? 1 : (empty($_GET['save']) ? 0 : -4));
	if($displayorder == -2) {
		C::t('forum_forum')->update($_G['fid'], array('modworks' => '1'));
	} elseif($displayorder == -4) {
		$_GET['addfeed'] = 0;
	}
	$digest = $_G['forum']['ismoderator'] && $_G['group']['allowdigestthread'] && !empty($_GET['addtodigest']) ? 1 : 0;
	$readperm = $_G['group']['allowsetreadperm'] ? $readperm : 0;
	$isanonymous = $_G['group']['allowanonymous'] && $_GET['isanonymous'] ? 1 : 0;
	$price = intval($price);
	$price = $_G['group']['maxprice'] && !$special ? ($price <= $_G['group']['maxprice'] ? $price : $_G['group']['maxprice']) : 0;

	if(!$typeid && $_G['forum']['threadtypes']['required'] && !$special) {
		showmessage('post_type_isnull');
	}

	if(!$sortid && $_G['forum']['threadsorts']['required'] && !$special) {
		showmessage('post_sort_isnull');
	}

	if($price > 0 && floor($price * (1 - $_G['setting']['creditstax'])) == 0) {
		showmessage('post_net_price_iszero');
	}

	if($special == 1) {

		$polloption = $_GET['tpolloption'] == 2 ? explode("\n", $_GET['polloptions']) : $_GET['polloption'];
		$pollarray = array();
		foreach($polloption as $key => $value) {
			$polloption[$key] = censor($polloption[$key]);
			if(trim($value) === '') {
				unset($polloption[$key]);
			}
		}

		if(count($polloption) > $_G['setting']['maxpolloptions']) {
			showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
		} elseif(count($polloption) < 2) {
			showmessage('post_poll_inputmore');
		}

		$curpolloption = count($polloption);
		$pollarray['maxchoices'] = empty($_GET['maxchoices']) ? 0 : ($_GET['maxchoices'] > $curpolloption ? $curpolloption : $_GET['maxchoices']);
		$pollarray['multiple'] = empty($_GET['maxchoices']) || $_GET['maxchoices'] == 1 ? 0 : 1;
		$pollarray['options'] = $polloption;
		$pollarray['visible'] = empty($_GET['visibilitypoll']);
		$pollarray['overt'] = !empty($_GET['overt']);

		if(preg_match("/^\d*$/", trim($_GET['expiration']))) {
			if(empty($_GET['expiration'])) {
				$pollarray['expiration'] = 0;
			} else {
				$pollarray['expiration'] = TIMESTAMP + 86400 * $_GET['expiration'];
			}
		} else {
			showmessage('poll_maxchoices_expiration_invalid');
		}

	} elseif($special == 3) {

		$rewardprice = intval($_GET['rewardprice']);
		if($rewardprice < 1) {
			showmessage('reward_credits_please');
		} elseif($rewardprice > 32767) {
			showmessage('reward_credits_overflow');
		} elseif($rewardprice < $_G['group']['minrewardprice'] || ($_G['group']['maxrewardprice'] > 0 && $rewardprice > $_G['group']['maxrewardprice'])) {
			if($_G['group']['maxrewardprice'] > 0) {
				showmessage('reward_credits_between', '', array('minrewardprice' => $_G['group']['minrewardprice'], 'maxrewardprice' => $_G['group']['maxrewardprice']));
			} else {
				showmessage('reward_credits_lower', '', array('minrewardprice' => $_G['group']['minrewardprice']));
			}
		} elseif(($realprice = $rewardprice + ceil($rewardprice * $_G['setting']['creditstax'])) > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][2])) {
			showmessage('reward_credits_shortage');
		}
		$price = $rewardprice;

	} elseif($special == 4) {

		$activitytime = intval($_GET['activitytime']);
		if(empty($_GET['starttimefrom'][$activitytime])) {
			showmessage('activity_fromtime_please');
		} elseif(@strtotime($_GET['starttimefrom'][$activitytime]) === -1 || @strtotime($_GET['starttimefrom'][$activitytime]) === FALSE) {
			showmessage('activity_fromtime_error');
		} elseif($activitytime && ((@strtotime($_GET['starttimefrom']) > @strtotime($_GET['starttimeto']) || !$_GET['starttimeto']))) {
			showmessage('activity_fromtime_error');
		} elseif(!trim($_GET['activityclass'])) {
			showmessage('activity_sort_please');
		} elseif(!trim($_GET['activityplace'])) {
			showmessage('activity_address_please');
		} elseif(trim($_GET['activityexpiration']) && (@strtotime($_GET['activityexpiration']) === -1 || @strtotime($_GET['activityexpiration']) === FALSE)) {
			showmessage('activity_totime_error');
		}

		$activity = array();
		$activity['class'] = censor(dhtmlspecialchars(trim($_GET['activityclass'])));
		$activity['starttimefrom'] = @strtotime($_GET['starttimefrom'][$activitytime]);
		$activity['starttimeto'] = $activitytime ? @strtotime($_GET['starttimeto']) : 0;
		$activity['place'] = censor(dhtmlspecialchars(trim($_GET['activityplace'])));
		$activity['cost'] = intval($_GET['cost']);
		$activity['gender'] = intval($_GET['gender']);
		$activity['number'] = intval($_GET['activitynumber']);

		if($_GET['activityexpiration']) {
			$activity['expiration'] = @strtotime($_GET['activityexpiration']);
		} else {
			$activity['expiration'] = 0;
		}
		if(trim($_GET['activitycity'])) {
			$subject .= '['.dhtmlspecialchars(trim($_GET['activitycity'])).']';
		}
		$extfield = $_GET['extfield'];
		$extfield = explode("\n", $_GET['extfield']);
		foreach($extfield as $key => $value) {
			$extfield[$key] = censor(trim($value));
			if($extfield[$key] === '' || is_numeric($extfield[$key])) {
				unset($extfield[$key]);
			}
		}
		$extfield = array_unique($extfield);
		if(count($extfield) > $_G['setting']['activityextnum']) {
			showmessage('post_activity_extfield_toomany', '', array('maxextfield' => $_G['setting']['activityextnum']));
		}
		$activity['ufield'] = array('userfield' => $_GET['userfield'], 'extfield' => $extfield);
		$activity['ufield'] = serialize($activity['ufield']);
		if(intval($_GET['activitycredit']) > 0) {
			$activity['credit'] = intval($_GET['activitycredit']);
		}
	} elseif($special == 5) {

		if(empty($_GET['affirmpoint']) || empty($_GET['negapoint'])) {
			showmessage('debate_position_nofound');
		} elseif(!empty($_GET['endtime']) && (!($endtime = @strtotime($_GET['endtime'])) || $endtime < TIMESTAMP)) {
			showmessage('debate_endtime_invalid');
		} elseif(!empty($_GET['umpire'])) {
			if(!C::t('common_member')->fetch_uid_by_username($_GET['umpire'])) {
				$_GET['umpire'] = dhtmlspecialchars($_GET['umpire']);
				showmessage('debate_umpire_invalid', '', array('umpire' => $umpire));
			}
		}
		$affirmpoint = censor(dhtmlspecialchars($_GET['affirmpoint']));
		$negapoint = censor(dhtmlspecialchars($_GET['negapoint']));
		$stand = censor(intval($_GET['stand']));

	} elseif($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
			$threadpluginclass->newthread_submit($_G['fid']);
		}
		$special = 127;

	}

	$sortid = $special && $_G['forum']['threadsorts']['types'][$sortid] ? 0 : $sortid;
	$typeexpiration = intval($_GET['typeexpiration']);

	if($_G['forum']['threadsorts']['expiration'][$typeid] && !$typeexpiration) {
		showmessage('threadtype_expiration_invalid');
	}

	$_G['forum_optiondata'] = array();
	if($_G['forum']['threadsorts']['types'][$sortid] && !$_G['forum']['allowspecialonly']) {
		$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], $pid);
	}

	$author = !$isanonymous ? $_G['username'] : '';

	$moderated = $digest || $displayorder > 0 ? 1 : 0;

	$thread['status'] = 0;

	$_GET['ordertype'] && $thread['status'] = setstatus(4, 1, $thread['status']);

	$_GET['hiddenreplies'] && $thread['status'] = setstatus(2, 1, $thread['status']);

	if($_G['group']['allowpostrushreply'] && $_GET['rushreply']) {
		$_GET['rushreplyfrom'] = strtotime($_GET['rushreplyfrom']);
		$_GET['rushreplyto'] = strtotime($_GET['rushreplyto']);
		$_GET['rewardfloor'] = trim($_GET['rewardfloor']);
		$_GET['stopfloor'] = intval($_GET['stopfloor']);
		$_GET['creditlimit'] = $_GET['creditlimit'] == '' ? '-996' : intval($_GET['creditlimit']);
		if($_GET['rushreplyfrom'] > $_GET['rushreplyto'] && !empty($_GET['rushreplyto'])) {
			showmessage('post_rushreply_timewrong');
		}
		if(($_GET['rushreplyfrom'] > $_G['timestamp']) || (!empty($_GET['rushreplyto']) && $_GET['rushreplyto'] < $_G['timestamp']) || ($_GET['stopfloor'] == 1) ) {
			$closed = true;
		}
		if(!empty($_GET['rewardfloor']) && !empty($_GET['stopfloor'])) {
			$floors = explode(',', $_GET['rewardfloor']);
			if(!empty($floors) && is_array($floors)) {
				foreach($floors AS $key => $floor) {
					if(strpos($floor, '*') === false) {
						if(intval($floor) == 0) {
							unset($floors[$key]);
						} elseif($floor > $_GET['stopfloor']) {
							unset($floors[$key]);
						}
					}
				}
				$_GET['rewardfloor'] = implode(',', $floors);
			}
		}
		$thread['status'] = setstatus(3, 1, $thread['status']);
		$thread['status'] = setstatus(1, 1, $thread['status']);
	}

	$_GET['allownoticeauthor'] && $thread['status'] = setstatus(6, 1, $thread['status']);
	$isgroup = $_G['forum']['status'] == 3 ? 1 : 0;

	if($_G['group']['allowreplycredit']) {
		$_GET['replycredit_extcredits'] = intval($_GET['replycredit_extcredits']);
		$_GET['replycredit_times'] = intval($_GET['replycredit_times']);
		$_GET['replycredit_membertimes'] = intval($_GET['replycredit_membertimes']);
		$_GET['replycredit_random'] = intval($_GET['replycredit_random']);

		$_GET['replycredit_random'] = $_GET['replycredit_random'] < 0 || $_GET['replycredit_random'] > 99 ? 0 : $_GET['replycredit_random'] ;
		$replycredit = $replycredit_real = 0;
		if($_GET['replycredit_extcredits'] > 0 && $_GET['replycredit_times'] > 0) {
			$replycredit_real = ceil(($_GET['replycredit_extcredits'] * $_GET['replycredit_times']) + ($_GET['replycredit_extcredits'] * $_GET['replycredit_times'] *  $_G['setting']['creditstax']));
			if($replycredit_real > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][10])) {
				showmessage('replycredit_morethan_self');
			} else {
				$replycredit = ceil($_GET['replycredit_extcredits'] * $_GET['replycredit_times']);
			}
		}
	}


	$newthread = array(
		'fid' => $_G['fid'],
		'posttableid' => 0,
		'readperm' => $readperm,
		'price' => $price,
		'typeid' => $typeid,
		'sortid' => $sortid,
		'author' => $author,
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $publishdate,
		'lastpost' => $publishdate,
		'lastposter' => $author,
		'displayorder' => $displayorder,
		'digest' => $digest,
		'special' => $special,
		'attachment' => 0,
		'moderated' => $moderated,
		'status' => $thread['status'],
		'isgroup' => $isgroup,
		'replycredit' => $replycredit,
		'closed' => $closed ? 1 : 0
	);
	$tid = C::t('forum_thread')->insert($newthread, true);
	useractionlog($_G['uid'], 'tid');

	if(!getuserprofile('threads') && $_G['setting']['newbie']) {
		C::t('forum_thread')->update($tid, array('icon' => $_G['setting']['newbie']));
	}
	if ($publishdate != $_G['timestamp']) {
		loadcache('cronpublish');
		$cron_publish_ids = dunserialize($_G['cache']['cronpublish']);
		$cron_publish_ids[$tid] = $tid;
		$cron_publish_ids = serialize($cron_publish_ids);
		savecache('cronpublish', $cron_publish_ids);
	}


	if(!$isanonymous) {
		C::t('common_member_field_home')->update($_G['uid'], array('recentnote'=>$subject));
	}

	if($special == 3 && $_G['group']['allowpostreward']) {
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][2] => -$realprice), 1, 'RTC', $tid);
	}

	if($moderated) {
		updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
		updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
	}

	if($special == 1) {

		foreach($pollarray['options'] as $polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			C::t('forum_polloption')->insert(array('tid' => $tid, 'polloption' => $polloptvalue));
		}
		$polloptionpreview = '';
		$query = C::t('forum_polloption')->fetch_all_by_tid($tid, 1, 2);
		foreach($query as $option) {
			$polloptvalue = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $option['polloption']);
			$polloptionpreview .= $polloptvalue."\t";
		}

		$polloptionpreview = daddslashes($polloptionpreview);

		$data = array('tid' => $tid, 'multiple' => $pollarray['multiple'], 'visible' => $pollarray['visible'], 'maxchoices' => $pollarray['maxchoices'], 'expiration' => $pollarray['expiration'], 'overt' => $pollarray['overt'], 'pollpreview' => $polloptionpreview);
		C::t('forum_poll')->insert($data);
	} elseif($special == 4 && $_G['group']['allowpostactivity']) {
		$data = array('tid' => $tid, 'uid' => $_G['uid'], 'cost' => $activity['cost'], 'starttimefrom' => $activity['starttimefrom'], 'starttimeto' => $activity['starttimeto'], 'place' => $activity['place'], 'class' => $activity['class'], 'gender' => $activity['gender'], 'number' => $activity['number'], 'expiration' => $activity['expiration'], 'aid' => $_GET['activityaid'], 'ufield' => $activity['ufield'], 'credit' => $activity['credit']);
		C::t('forum_activity')->insert($data);

	} elseif($special == 5 && $_G['group']['allowpostdebate']) {

		C::t('forum_debate')->insert(array(
		    'tid' => $tid,
		    'uid' => $_G['uid'],
		    'starttime' => $publishdate,
		    'endtime' => $endtime,
		    'affirmdebaters' => 0,
		    'negadebaters' => 0,
		    'affirmvotes' => 0,
		    'negavotes' => 0,
		    'umpire' => $_GET['umpire'],
		    'winner' => '',
		    'bestdebater' => '',
		    'affirmpoint' => $affirmpoint,
		    'negapoint' => $negapoint,
		    'umpirepoint' => ''
		));

	} elseif($special == 127) {

		$message .= chr(0).chr(0).chr(0).$specialextra;

	}

	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata'])) {
		$filedname = $valuelist = $separator = '';
		foreach($_G['forum_optiondata'] as $optionid => $value) {
			if($value) {
				$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
				$valuelist .= $separator."'".daddslashes($value)."'";
				$separator = ' ,';
			}

			if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
				$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
				$sortaids[] = intval($_GET['typeoption'][$identifier]['aid']);
			}

			C::t('forum_typeoptionvar')->insert(array(
				'sortid' => $sortid,
				'tid' => $tid,
				'fid' => $_G['fid'],
				'optionid' => $optionid,
				'value' => censor($value),
				'expiration' => ($typeexpiration ? $publishdate + $typeexpiration : 0),
			));
		}

		if($filedname && $valuelist) {
			C::t('forum_optionvalue')->insert($sortid, "($filedname, tid, fid) VALUES ($valuelist, '$tid', '$_G[fid]')");
		}
	}
	if($_G['group']['allowat']) {
		$atlist = $atlist_tmp = array();
		preg_match_all("/@([^\r\n]*?)\s/i", $message.' ', $atlist_tmp);
		$atlist_tmp = array_slice(array_unique($atlist_tmp[1]), 0, $_G['group']['allowat']);
		if(!empty($atlist_tmp)) {
			if(empty($_G['setting']['at_anyone'])) {
				foreach(C::t('home_follow')->fetch_all_by_uid_fusername($_G['uid'], $atlist_tmp) as $row) {
					$atlist[$row['followuid']] = $row['fusername'];
				}
				if(count($atlist) < $_G['group']['allowat']) {
					$query = C::t('home_friend')->fetch_all_by_uid_username($_G['uid'], $atlist_tmp);
					foreach($query as $row) {
						$atlist[$row['fuid']] = $row['fusername'];
					}
				}
			} else {
				foreach(C::t('common_member')->fetch_all_by_username($atlist_tmp) as $row) {
					$atlist[$row['uid']] = $row['username'];
				}
			}
		}
		if($atlist) {
			foreach($atlist as $atuid => $atusername) {
				$atsearch[] = "/@".str_replace('/', '\/', preg_quote($atusername))." /i";
				$atreplace[] = "[url=home.php?mod=space&uid=$atuid]@{$atusername}[/url] ";
			}
			$message = preg_replace($atsearch, $atreplace, $message.' ', 1);
		}
	}

	$bbcodeoff = checkbbcodes($message, !empty($_GET['bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_GET['smileyoff']));
	$parseurloff = !empty($_GET['parseurloff']);
	$htmlon = $_G['group']['allowhtml'] && !empty($_GET['htmlon']) ? 1 : 0;
	$usesig = !empty($_GET['usesig']) && $_G['group']['maxsigsize'] ? 1 : 0;
	$class_tag = new tag();
	$tagstr = $class_tag->add_tag($_GET['tags'], $tid, 'tid');

	if($_G['group']['allowreplycredit']) {
		if($replycredit > 0 && $replycredit_real > 0) {
			updatemembercount($_G['uid'], array('extcredits'.$_G['setting']['creditstransextra'][10] => -$replycredit_real), 1, 'RCT', $tid);
			$insertdata = array(
					'tid' => $tid,
					'extcredits' => $_GET['replycredit_extcredits'],
					'extcreditstype' => $_G['setting']['creditstransextra'][10],
					'times' => $_GET['replycredit_times'],
					'membertimes' => $_GET['replycredit_membertimes'],
					'random' => $_GET['replycredit_random']
				);
			C::t('forum_replycredit')->insert($insertdata);
		}
	}

	if($_G['group']['allowpostrushreply'] && $_GET['rushreply']) {
		$rushdata = array('tid' => $tid, 'stopfloor' => $_GET['stopfloor'], 'starttimefrom' => $_GET['rushreplyfrom'], 'starttimeto' => $_GET['rushreplyto'], 'rewardfloor' => $_GET['rewardfloor'], 'creditlimit' => $_GET['creditlimit']);
		C::t('forum_threadrush')->insert($rushdata);
	}

	$pinvisible = $modnewthreads ? -2 : (empty($_GET['save']) ? 0 : -3);
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $tid,
		'first' => '1',
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $publishdate,
		'message' => $message,
		'useip' => $_G['clientip'],
		'invisible' => $pinvisible,
		'anonymous' => $isanonymous,
		'usesig' => $usesig,
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'tags' => $tagstr,
		'replycredit' => 0,
		'status' => (defined('IN_MOBILE') ? 8 : 0)
	));
	if($_G['group']['allowat'] && $atlist) {
		foreach($atlist as $atuid => $atusername) {
			notification_add($atuid, 'at', 'at_message', array('from_id' => $tid, 'from_idtype' => 'at', 'buyerid' => $_G['uid'], 'buyer' => $_G['username'], 'tid' => $tid, 'subject' => $subject, 'pid' => $pid, 'message' => messagecutstr($message, 150)));
		}
		set_atlist_cookie(array_keys($atlist));
	}
	$threadimageaid = 0;
	$threadimage = array();
	if($special == 4 && $_GET['activityaid']) {
		$threadimageaid = $_GET['activityaid'];
		convertunusedattach($_GET['activityaid'], $tid, $pid);
	}

	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata']) && $sortaids) {
		foreach($sortaids as $sortaid) {
			convertunusedattach($sortaid, $tid, $pid);
		}
	}

	if(($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_GET['attachnew'] || $sortid || !empty($_GET['activityaid']))) {
		updateattach($displayorder == -4 || $modnewthreads, $tid, $pid, $_GET['attachnew']);
		if(!$threadimageaid) {
			$threadimage = C::t('forum_attachment_n')->fetch_max_image('tid:'.$tid, 'tid', $tid);
			$threadimageaid = $threadimage['aid'];
		}
	}

	$values = array('fid' => $_G['fid'], 'tid' => $tid, 'pid' => $pid, 'coverimg' => '', 'sechash' => !empty($_GET['sechash']) ? $_GET['sechash'] : '');
	$param = array();
	if($_G['forum']['picstyle']) {
		if(!setthreadcover($pid, 0, $threadimageaid)) {
			preg_match_all("/(\[img\]|\[img=\d{1,4}[x|\,]\d{1,4}\])\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $message, $imglist, PREG_SET_ORDER);
			$values['coverimg'] = "<p id=\"showsetcover\">".lang('message', 'post_newthread_set_cover')."<span id=\"setcoverwait\"></span></p><script>if($('forward_a')){\$('forward_a').style.display='none';setTimeout(\"$('forward_a').style.display=''\", 5000);};ajaxget('forum.php?mod=ajax&action=setthreadcover&tid=$tid&pid=$pid&fid=$_G[fid]&imgurl={$imglist[0][2]}&newthread=1', 'showsetcover', 'setcoverwait')</script>";
			$param['clean_msgforward'] = 1;
			$param['timeout'] = $param['refreshtime'] = 15;
		}
	}

	if($threadimageaid) {
		if(!$threadimage) {
			$threadimage = C::t('forum_attachment_n')->fetch('tid:'.$tid, $threadimageaid);
		}
		$threadimage = daddslashes($threadimage);
		C::t('forum_threadimage')->insert(array(
			'tid' => $tid,
			'attachment' => $threadimage['attachment'],
			'remote' => $threadimage['remote'],
		));
	}

	$statarr = array(0 => 'thread', 1 => 'poll', 2 => 'trade', 3 => 'reward', 4 => 'activity', 5 => 'debate', 127 => 'thread');
	include_once libfile('function/stat');
	updatestat($isgroup ? 'groupthread' : $statarr[$special]);

	dsetcookie('clearUserdata', 'forum');

	if($specialextra) {

		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
			$threadpluginclass->newthread_submit_end($_G['fid'], $tid);
		}

	}

	if(!empty($_G['setting']['rewriterule']['forum_viewthread']) && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
		$returnurl = rewriteoutput('forum_viewthread', 1, '', $tid, 1, '', $extra);
	} else {
		$returnurl = "forum.php?mod=viewthread&tid=$tid&extra=$extra";
	}
	if($modnewthreads) {
		updatemoderate('tid', $tid);
		C::t('forum_forum')->update_forum_counter($_G['fid'], 0, 0, 1);
		manage_addnotify('verifythread');
		showmessage('post_newthread_mod_succeed', $returnurl, $values, $param);
	} else {

		if($displayorder >= 0 && helper_access::check_module('follow') && !empty($_GET['adddynamic']) && !$isanonymous) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feedcontent = array(
				'tid' => $tid,
				'content' => followcode($message, $tid, $pid, 1000),
			);
			C::t('forum_threadpreview')->insert($feedcontent);
			C::t('forum_thread')->update_status_by_tid($tid, '512');
			$followfeed = array(
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'tid' => $tid,
				'note' => '',
				'dateline' => TIMESTAMP
			);
			$values['feedid'] = C::t('home_follow_feed')->insert($followfeed, true);
			C::t('common_member_count')->increase($_G['uid'], array('feeds'=>1));
		}

		$feed = array(
			'icon' => '',
			'title_template' => '',
			'title_data' => array(),
			'body_template' => '',
			'body_data' => array(),
			'title_data'=>array(),
			'images'=>array()
		);
		if(!empty($_GET['addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
			$message = !$price && !$readperm ? $message : '';
			if($special == 0) {
				$feed['icon'] = 'thread';
				$feed['title_template'] = 'feed_thread_title';
				$feed['body_template'] = 'feed_thread_message';
				$feed['body_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
					'message' => messagecutstr($message, 150)
				);
				if(!empty($_G['forum_attachexist'])) {
					$imgattach = C::t('forum_attachment_n')->fetch_max_image('tid:'.$tid, 'pid', $pid);
					$firstaid = $imgattach['aid'];
					unset($imgattach);
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				}
			} elseif($special > 0) {
				if($special == 1) {
					$pvs = explode("\t", messagecutstr($polloptionpreview, 150));
					$s = '';
					$i = 1;
					foreach($pvs as $pv) {
						$s .= $i.'. '.$pv.'<br />';
					}
					$s .= '&nbsp;&nbsp;&nbsp;...';
					$feed['icon'] = 'poll';
					$feed['title_template'] = 'feed_thread_poll_title';
					$feed['body_template'] = 'feed_thread_poll_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => $s
					);
				} elseif($special == 3) {
					$feed['icon'] = 'reward';
					$feed['title_template'] = 'feed_thread_reward_title';
					$feed['body_template'] = 'feed_thread_reward_message';
					$feed['body_data'] = array(
						'subject'=> "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'rewardprice'=> $rewardprice,
						'extcredits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'],
					);
				} elseif($special == 4) {
					$feed['icon'] = 'activity';
					$feed['title_template'] = 'feed_thread_activity_title';
					$feed['body_template'] = 'feed_thread_activity_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'starttimefrom' => $_GET['starttimefrom'][$activitytime],
						'activityplace'=> $activity['place'],
						'message' => messagecutstr($message, 150),
					);
					if($_GET['activityaid']) {
						$feed['images'] = array(getforumimg($_GET['activityaid']));
						$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				} elseif($special == 5) {
					$feed['icon'] = 'debate';
					$feed['title_template'] = 'feed_thread_debate_title';
					$feed['body_template'] = 'feed_thread_debate_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => messagecutstr($message, 150),
						'affirmpoint'=> messagecutstr($affirmpoint, 150),
						'negapoint'=> messagecutstr($negapoint, 150)
					);
				}
			}

			$feed['title_data']['hash_data'] = "tid{$tid}";
			$feed['id'] = $tid;
			$feed['idtype'] = 'tid';
			if($feed['icon']) {
				postfeed($feed);
			}
		}

		if($displayorder != -4) {
			if($digest) {
				updatepostcredits('+',  $_G['uid'], 'digest', $_G['fid']);
			}
			updatepostcredits('+',  $_G['uid'], 'post', $_G['fid']);
			if($isgroup) {
				C::t('forum_groupuser')->update_counter_for_user($_G['uid'], $_G['fid'], 1);
			}

			$subject = str_replace("\t", ' ', $subject);
			$lastpost = "$tid\t".$subject."\t$_G[timestamp]\t$author";
			C::t('forum_forum')->update($_G['fid'], array('lastpost' => $lastpost));
			C::t('forum_forum')->update_forum_counter($_G['fid'], 1, 1, 1);
			if($_G['forum']['type'] == 'sub') {
				C::t('forum_forum')->update($_G['forum']['fup'], array('lastpost' => $lastpost));
			}
		}

		if($_G['forum']['status'] == 3) {
			C::t('forum_forumfield')->update($_G['fid'], array('lastupdate' => TIMESTAMP));
			require_once libfile('function/grouplog');
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}

		showmessage('post_newthread_succeed', $returnurl, $values, $param);

	}
}

?>