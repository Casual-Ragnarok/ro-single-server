<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_collection.php 27779 2012-02-14 07:33:17Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = in_array($operation, array('admin', 'comment')) ? $operation : 'admin';
$current = array($operation => 1);
$fromumanage = $_GET['fromumanage'] ? 1 : 0;
shownav('global', 'collection');
showsubmenu('collection', array(
	array('collection_admin', 'collection&operation=admin', $current['admin']),
	array('collection_comment', 'collection&operation=comment', $current['comment']),
));
echo '<script src="static/js/calendar.js"></script>';

if($operation == 'comment') {
	$tagarray = array();
	if(submitcheck('submit') && !empty($_GET['cidarray']) && is_array($_GET['cidarray']) && count($_GET['cidarray']) && !empty($_GET['operate_type'])) {
		$class_tag = new tag();
		$cidarray = array();
		$operate_type = $_GET['operate_type'];
		$cidarray = $_GET['cidarray'];
		if($operate_type == 'delete') {
			require_once libfile('function/delete');
			$cidlist = C::t('forum_collectioncomment')->fetch_all($cidarray);
			C::t('forum_collectioncomment')->delete_by_cid_ctid($cidarray);
			foreach($cidlist as $uniquecid) {
				$decreasnum[$uniquecid['ctid']]++;
			}
			foreach($cidlist as $uniquecid) {
				C::t('forum_collection')->update_by_ctid($uniquecid['ctid'], 0, 0, -$decreasnum[$uniquecid['ctid']]);
			}
		}
		cpmsg('collection_admin_updated', 'action=collection&operation=comment&searchsubmit=yes&perpage='.$_GET['perpage'].'&page='.$_GET['page'], 'succeed');
	}
	if(!submitcheck('searchsubmit', 1)) {
		showformheader('collection&operation=comment');
		showtableheader();
		showsetting('collection_ctid', 'comment_ctid', $comment_ctid, 'text');
		showsetting('collection_comment_message', 'comment_message', $comment_message, 'text');
		showsetting('collection_comment_cid', 'comment_cid', $comment_cid, 'text');
		showsetting('collection_comment_username', 'comment_username', $comment_username, 'text');
		showsetting('collection_comment_uid', 'comment_uid', $comment_uid, 'text');
		showsetting('collection_comment_rate', 'comment_rate', $comment_rate, 'text');
		showsetting('collection_comment_useip', 'comment_useip', $comment_useip, 'text');
		if(!$fromumanage) {
			empty($_GET['starttime']) && $_GET['starttime'] = date('Y-m-d', time() - 86400 * 30);
		}
		echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
		showsetting('threads_search_time', array('starttime', 'endtime'), array($_GET['starttime'], $_GET['endtime']), 'daterange');
		showsetting('feed_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
	} else {
		$comment_message = trim($_GET['comment_message']);
		$comment_cid = dintval($_GET['comment_cid']);
		$comment_ctid = dintval($_GET['comment_ctid']);
		$comment_uid = dintval($_GET['comment_uid']);
		$comment_username = trim($_GET['comment_username']);
		$comment_useip = trim($_GET['comment_useip']);
		$comment_rate = dintval($_GET['comment_rate']);
		$starttime = $_GET['starttime'] ? strtotime($_GET['starttime']) : '';
		$endtime = $_GET['endtime'] ? strtotime($_GET['endtime']) : '';

		$ppp = $_GET['perpage'];
		$startlimit = ($page - 1) * $ppp;
		$multipage = '';
		$totalcount = C::t('forum_collectioncomment')->fetch_all_for_search($comment_cid, $comment_ctid, $comment_username, $comment_uid, $comment_useip, $comment_rate, $comment_message, $starttime, $endtime, -1);
		$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=collection&operation=comment&searchsubmit=yes&comment_message=$comment_message&comment_cid=$comment_cid&comment_username=$comment_username&comment_uid=$comment_uid&comment_ctid=$comment_ctid&comment_useip=$comment_useip&comment_rate=$comment_rate&starttime=$starttime&endtime=$endtime&perpage=$ppp");
		$collectioncomment = C::t('forum_collectioncomment')->fetch_all_for_search($comment_cid, $comment_ctid, $comment_username, $comment_uid, $comment_useip, $comment_rate, $comment_message, $starttime, $endtime, $startlimit, $ppp);
		showformheader('collection&operation=comment');
		showtableheader(cplang('collection_comment_result').' '.$totalcount.' <a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=collection&operation=comment\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
		showhiddenfields(array('page' => $_GET['page'], 'tagname' => $tagname, 'status' => $status, 'perpage' => $ppp));
		showsubtitle(array('', 'collection_comment_message', 'collection_comment_cid', 'collection_name', 'collection_comment_username', 'collection_comment_useip', 'collection_comment_ratenum', 'collection_date'));

		foreach($collectioncomment as $uniquecomment) {
			$ctidarray[$uniquecomment['ctid']] = 1;
		}
		$ctidarray = array_keys($ctidarray);
		$collectiondata = C::t('forum_collection')->fetch_all($ctidarray);
		foreach($collectioncomment as $uniquecomment) {
			if($uniquecomment['rate'] == 0) $uniquecomment['rate'] = '-';
			showtablerow('', array('class="td25"', 'width=400', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"cidarray[]\" value=\"$uniquecomment[cid]\" />",
				$uniquecomment['message'],
				$uniquecomment['cid'],
				"<a href='forum.php?mod=collection&action=view&ctid={$uniquecomment['ctid']}' target='_blank'>{$collectiondata[$uniquecomment['ctid']]['name']}</a>",
				"<a href='home.php?mod=space&uid={$uniquecomment['uid']}' target='_blank'>{$uniquecomment['username']}</a>",
				$uniquecomment['useip'],
				$uniquecomment['rate'],
				dgmdate($uniquecomment['dateline']),
			));
		}
		showtablerow('', array('class="td25" colspan="3"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'cidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
		showtablerow('', array('class="td25"', 'colspan="2"'), array(
				cplang('operation'),
				'<input class="radio" type="radio" name="operate_type" value="delete"> '.cplang('delete').' '
			));
		showsubmit('submit', 'submit', '', '', $multipage);
		showtablefooter();
		showformfooter();
	}
} elseif($operation == 'admin') {
	$tagarray = array();
	if(submitcheck('submit') && !empty($_GET['ctidarray']) && is_array($_GET['ctidarray']) && count($_GET['ctidarray']) && !empty($_GET['operate_type'])) {
		$class_tag = new tag();
		$ctidarray = array();
		$operate_type = $_GET['operate_type'];
		$ctidarray = $_GET['ctidarray'];
		if($operate_type == 'delete') {
			require_once libfile('function/delete');
			foreach($ctidarray as $ctid) {
				deletecollection($ctid);
			}
		}
		cpmsg('collection_admin_updated', 'action=collection&operation=admin&searchsubmit=yes&perpage='.$_GET['perpage'].'&page='.$_GET['page'], 'succeed');
	}
	if(!submitcheck('searchsubmit', 1)) {
		showformheader('collection&operation=admin');
		showtableheader();
		showsetting('collection_name', 'collection_name', $collection_name, 'text');
		showsetting('collection_ctid', 'collection_ctid', $collection_ctid, 'text');
		showsetting('collection_username', 'collection_username', $collection_username, 'text');
		showsetting('collection_uid', 'collection_uid', $collection_uid, 'text');
		showsetting('feed_search_perpage', '', $_GET['perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
	} else {
		$collection_name = trim($_GET['collection_name']);
		$collection_ctid = dintval($_GET['collection_ctid']);
		$collection_username = trim($_GET['collection_username']);
		$collection_uid = dintval($_GET['collection_uid']);


		$ppp = $_GET['perpage'];
		$startlimit = ($page - 1) * $ppp;
		$multipage = '';
		$totalcount = C::t('forum_collection')->fetch_all_for_search($collection_name, $collection_ctid, $collection_username, $collection_uid, -1);
		$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=collection&operation=admin&searchsubmit=yes&collection_name=$collection_name&collection_ctid=$collection_ctid&collection_username=$collection_username&collection_uid=$collection_uid&perpage=$ppp&status=$status");
		$collection = C::t('forum_collection')->fetch_all_for_search($collection_name, $collection_ctid, $collection_username, $collection_uid, $startlimit, $ppp);
		showformheader('collection&operation=admin');
		showtableheader(cplang('collection_result').' '.$totalcount.' <a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=collection&operation=admin\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
		showhiddenfields(array('page' => $_GET['page'], 'collection_name' => $collection_name, 'collection_ctid' => $collection_ctid, 'perpage' => $ppp));
		showsubtitle(array('', 'collection_name', 'collection_username', 'collection_date'));
		foreach($collection as $uniquecollection) {
			showtablerow('', array('class="td25"', 'width=400', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"ctidarray[]\" value=\"$uniquecollection[ctid]\" />",
				"<a href='forum.php?mod=collection&action=view&ctid={$uniquecollection['ctid']}' target='_blank'>{$uniquecollection['name']}</a>",
				"<a href='home.php?mod=space&uid={$uniquecollection['uid']}' target='_blank'>{$uniquecollection['username']}</a>",
				dgmdate($uniquecollection['dateline']),
			));
		}
		showtablerow('', array('class="td25" colspan="3"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ctidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
		showtablerow('', array('class="td25"', 'colspan="2"'), array(
				cplang('operation'),
				'<input class="radio" type="radio" name="operate_type" value="delete"> '.cplang('delete').' '
			));
		showsubmit('submit', 'submit', '', '', $multipage);
		showtablefooter();
		showformfooter();
	}
}
?>