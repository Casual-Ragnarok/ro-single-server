<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newtrade.php 30397 2012-05-25 09:03:57Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if($special != 2 || !submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {
	showmessage('submitcheck_error', NULL);
}

if(!$_G['group']['allowposttrade']) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessage('post_forum_newthread_nopermission', NULL);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if($post_invalid = checkpost($subject, $message, 1)) {
	showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
}

if($time_left = checkflood()) {
	showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl'], 'time_left' => $time_left));
} elseif(checkmaxperhour('tid')) {
		showmessage('thread_flood_ctrl_threads_per_hour', '', array('threads_per_hour' => $_G['group']['maxthreadsperhour']));
	}

$item_price = floatval($_GET['item_price']);
$item_credit = intval($_GET['item_credit']);
$_GET['item_name'] = censor($_GET['item_name']);
if(!trim($_GET['item_name'])) {
	showmessage('trade_please_name');
} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
	showmessage('trade_price_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
	showmessage('trade_credit_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
	showmessage('trade_price_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
	showmessage('trade_credit_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
} elseif($item_price <= 0 && $item_credit <= 0) {
	showmessage('trade_pricecredit_need');
} elseif($_GET['item_number'] < 1) {
	showmessage('tread_please_number');
}

if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
	$_FILES['attach'] = array_merge_recursive((array)$_FILES['attach'], $_FILES['tradeattach']);
}

if(($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && is_array($_FILES['attach'])) {
	foreach($_FILES['attach']['name'] as $attachname) {
		if($attachname != '') {
			checklowerlimit('postattach', 0, 1, $_G['forum']['fid']);
			break;
		}
	}
}

$_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;
$typeid = isset($typeid) ? $typeid : 0;
$displayorder = $modnewthreads ? -2 : (($_G['forum']['ismoderator'] && !empty($_GET['sticktopic'])) ? 1 : (empty($_GET['save']) ? 0 : -4));
if($displayorder == -2) {
	C::t('forum_forum')->update($_G['fid'], array('modworks' => '1'));
} elseif($displayorder == -4) {
	$_GET['addfeed'] = 0;
}
$digest = ($_G['forum']['ismoderator'] && !empty($addtodigest)) ? 1 : 0;
$readperm = $_G['group']['allowsetreadperm'] ? $readperm : 0;
$isanonymous = $_GET['isanonymous'] && $_G['group']['allowanonymous'] ? 1 : 0;

$author = !$isanonymous ? $_G['username'] : '';

$moderated = $digest || $displayorder > 0 ? 1 : 0;
$isgroup = $_G['forum']['status'] == 3 ? 1 : 0;

$newthread = array(
	'fid' => $_G['fid'],
	'posttableid' => 0,
	'readperm' => $readperm,
	'price' => $price,
	'typeid' => $typeid,
	'author' => $author,
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'lastpost' => $_G['timestamp'],
	'lastposter' => $author,
	'displayorder' => $displayorder,
	'digest' => $digest,
	'special' => $special,
	'attachment' => $attachment,
	'moderated' => $moderated,
	'replies' => 1,
	'status' => $thread['status'],
	'isgroup' => $isgroup
);
$tid = C::t('forum_thread')->insert($newthread, true);
useractionlog($_G['uid'], 'tid');

if($moderated) {
	updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
	updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
}

$bbcodeoff = checkbbcodes($message, !empty($_GET['bbcodeoff']));
$smileyoff = checksmilies($message, !empty($_GET['smileyoff']));
$parseurloff = !empty($_GET['parseurloff']);
$htmlon = $_G['group']['allowhtml'] && !empty($_GET['htmlon']) ? 1 : 0;
$attentionon = empty($_GET['attention_add']) ? 0 : 1;

$pinvisible = $modnewthreads ? -2 : (empty($_GET['save']) ? 0 : -3);

$class_tag = new tag();
$tagstr = $class_tag->add_tag($_GET['tags'], $tid, 'tid');
insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $tid,
	'first' => '1',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => '',
	'useip' => $_G['clientip'],
	'invisible' => $pinvisible,
	'anonymous' => $isanonymous,
	'usesig' => $_GET['usesig'],
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => '0',
	'tags' => $tagstr,
));

$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
$pid = insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $tid,
	'first' => '0',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => $message,
	'useip' => $_G['clientip'],
	'invisible' => 0,
	'anonymous' => $isanonymous,
	'usesig' => $_GET['usesig'],
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => $attachment,
	'tags' => $tagstr,
	'status' => (defined('IN_MOBILE') ? 8 : 0)
));

($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_GET['attachnew'] || $_GET['tradeaid']) && updateattach($displayorder == -4 || $modnewthreads, $tid, $pid, $_GET['attachnew']);
require_once libfile('function/trade');
trade_create(array(
	'tid' => $tid,
	'pid' => $pid,
	'aid' => $_GET['tradeaid'],
	'item_expiration' => $_GET['item_expiration'],
	'thread' => $thread,
	'discuz_uid' => $_G['uid'],
	'author' => $author,
	'seller' => empty($_GET['paymethod']) && $_GET['seller'] ? dhtmlspecialchars(trim($_GET['seller'])) : '',
	'tenpayaccount' => $_GET['tenpay_account'],
	'item_name' => $_GET['item_name'],
	'item_price' => $_GET['item_price'],
	'item_number' => $_GET['item_number'],
	'item_quality' => $_GET['item_quality'],
	'item_locus' => $_GET['item_locus'],
	'transport' => $_GET['transport'],
	'postage_mail' => $_GET['postage_mail'],
	'postage_express' => $_GET['postage_express'],
	'postage_ems' => $_GET['postage_ems'],
	'item_type' => $_GET['item_type'],
	'item_costprice' => $_GET['item_costprice'],
	'item_credit' => $_GET['item_credit'],
	'item_costcredit' => $_GET['item_costcredit']
));

if(!empty($_GET['tradeaid'])) {
	convertunusedattach($_GET['tradeaid'], $tid, $pid);
}

if($_G['forum']['picstyle']) {
	setthreadcover($pid);
}
$param = array('fid' => $_G['fid'], 'tid' => $tid, 'pid' => $pid, 'coverimg' => '');

include_once libfile('function/stat');
updatestat($isgroup ? 'groupthread' : 'trade');

dsetcookie('clearUserdata', 'forum');

if($modnewthreads) {

	updatemoderate('tid', $tid);
	C::t('forum_forum')->update_forum_counter($_G['fid'], 0, 0, 1);
	manage_addnotify('verifythread');
	showmessage('post_newthread_mod_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);

} else {
	$feed = array();
	if(!empty($_GET['addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
		$feed['icon'] = 'goods';
		$feed['title_template'] = 'feed_thread_goods_title';
		if($_GET['item_price'] > 0) {
			if($_G['setting']['creditstransextra'][5] != -1 && $_GET['item_credit']) {
				$feed['body_template'] = 'feed_thread_goods_message_1';
			} else {
				$feed['body_template'] = 'feed_thread_goods_message_2';
			}
		} else {
			$feed['body_template'] = 'feed_thread_goods_message_3';
		}
		$feed['body_data'] = array(
			'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid\">$_GET[item_name]</a>",
			'itemprice'=> $_GET['item_price'],
			'itemcredit'=> $_GET['item_credit'],
			'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title']
		);
		if($_GET['tradeaid']) {
			$feed['images'] = array(getforumimg($_GET['tradeaid']));
			$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
		}
		if($_GET['tradeaid']) {
			$attachment = C::t('forum_attachment_n')->fetch('tid:'.$tid, $_GET['tradeaid']);
			if(in_array($attachment['filetype'], array('image/gif', 'image/jpeg', 'image/png'))) {
				$imgurl = $_G['setting']['attachurl'].'forum/'.($attachment['thumb'] && $attachment['filetype'] != 'image/gif' ? getimgthumbname($attachment['attachment']) : $attachment['attachment']);
				$feed['images'][] = $attachment['attachment'] ? $imgurl : '';
				$feed['image_links'][] = $attachment['attachment'] ? "forum.php?mod=viewthread&tid=$tid" : '';
			}
		}

		$feed['title_data']['hash_data'] = "tid{$tid}";
		$feed['id'] = $tid;
		$feed['idtype'] = 'tid';
		postfeed($feed);
	}

	if($displayorder != -4) {
		if($digest) {
			updatepostcredits('+',  $_G['uid'], 'digest', $_G['fid']);
		}
		updatepostcredits('+',  $_G['uid'], 'post', $_G['fid']);
		if($isgroup) {
			C::t('forum_groupuser')->update_counter_for_user($_G['uid'], $_G['fid'], 1);
		}

		$lastpost = "$tid\t".$subject."\t$_G[timestamp]\t$author";
		C::t('forum_forum')->update($_G['fid'], array('lastpost' => $lastpost));
		C::t('forum_forum')->update_forum_counter($_G['fid'], 1, 2, 1);
		if($_G['forum']['type'] == 'sub') {
			C::t('forum_forum')->update($_G['forum']['fup'], array('lastpost' => $lastpost));
		}
	}

	if($_G['forum']['status'] == 3) {
		C::t('forum_forumfield')->update($_G['fid'], array('lastupdate' => TIMESTAMP));
		require_once libfile('function/grouplog');
		updategroupcreditlog($_G['fid'], $_G['uid']);
	}

	if(!empty($_GET['continueadd'])) {
		showmessage('post_newthread_succeed', "forum.php?mod=post&action=reply&fid=$_G[fid]&tid=$tid&addtrade=yes", $param, array('header' => true));
	} else {
		showmessage('post_newthread_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);
	}

}

?>