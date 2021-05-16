<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_index.php 29203 2012-03-28 10:16:25Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(@file_exists(DISCUZ_ROOT.'./install/index.php') && !DISCUZ_DEBUG) {
	@unlink(DISCUZ_ROOT.'./install/index.php');
	if(@file_exists(DISCUZ_ROOT.'./install/index.php')) {
		dexit('Please delete install/index.php via FTP!');
	}
}

@include_once DISCUZ_ROOT.'./source/discuz_version.php';
require_once libfile('function/attachment');
$isfounder = isfounder();

$siteuniqueid = C::t('common_setting')->fetch('siteuniqueid');
if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = 'DX'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
	C::t('common_setting')->update('siteuniqueid', $siteuniqueid);
	require_once libfile('function/cache');
	updatecache('setting');
}


if(submitcheck('notesubmit', 1)) {
	if(!empty($_GET['noteid']) && is_numeric($_GET['noteid'])) {
		C::t('common_adminnote')->delete($_GET['noteid'], ($isfounder ? '' : $_G['username']));
	}
	if(!empty($_GET['newmessage'])) {
		$newaccess = 0;
		$_GET['newexpiration'] = TIMESTAMP + (intval($_GET['newexpiration']) > 0 ? intval($_GET['newexpiration']) : 30) * 86400;
		$_GET['newmessage'] = nl2br(dhtmlspecialchars($_GET['newmessage']));
		$data = array(
			'admin' => $_G['username'],
			'access' => 0,
			'adminid' => $_G['adminid'],
			'dateline' => $_G['timestamp'],
			'expiration' => $_GET['newexpiration'],
			'message' => $_GET['newmessage'],
		);
		C::t('common_adminnote')->insert($data);
	}
}

$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
$serversoft = $_SERVER['SERVER_SOFTWARE'];
$dbversion = helper_dbtool::dbversion();

if(@ini_get('file_uploads')) {
	$fileupload = ini_get('upload_max_filesize');
} else {
	$fileupload = '<font color="red">'.$lang['no'].'</font>';
}


$dbsize = helper_dbtool::dbsize();
$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

if(isset($_GET['attachsize'])) {
	$attachsize = C::t('forum_attachment_n')->get_total_filesize();
	$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];
} else {
	$attachsize = '<a href="'.ADMINSCRIPT.'?action=index&attachsize">[ '.$lang['detail'].' ]</a>';
}

$membersmod = C::t('common_member_validate')->count_by_status(0);
$threadsdel = C::t('forum_thread')->count_by_displayorder(-1);
$groupmod = C::t('forum_forum')->validate_level_num();

$modcount = array();
foreach(C::t('common_moderate')->count_group_idtype_by_status(0) as $value) {
	$modcount[$value['idtype']] = $value['count'];
}

$medalsmod = C::t('forum_medallog')->count_by_type(2);
$threadsmod = $modcount['tid'];
$postsmod = $modcount['pid'];
$blogsmod = $modcount['blogid'];
$doingsmod = $modcount['doid'];
$picturesmod = $modcount['picid'];
$sharesmod = $modcount['sid'];
$commentsmod = $modcount['uid_cid'] + $modcount['blogid_cid'] + $modcount['sid_cid'] + $modcount['picid_cid'];
$articlesmod = $modcount['aid'];
$articlecommentsmod = $modcount['aid_cid'];
$topiccommentsmod = $modcount['topicid_cid'];
$verify = '';
foreach(C::t('common_member_verify_info')->group_by_verifytype_count() as $value) {
	if($value['num']) {
		if($value['verifytype']) {
			$verifyinfo = !empty($_G['setting']['verify'][$value['verifytype']]) ? $_G['setting']['verify'][$value['verifytype']] : array();
			if($verifyinfo['available']) {
				$verify .= '<a href="'.ADMINSCRIPT.'?action=verify&operation=verify&do='.$value['verifytype'].'">'.cplang('home_mod_verify_prefix').$verifyinfo['title'].'</a>(<em class="lightnum">'.$value['num'].'</em>)';
			}
		} else {
			$verify .= '<a href="'.ADMINSCRIPT.'?action=verify&operation=verify&do=0">'.cplang('home_mod_verify_prefix').cplang('members_verify_profile').'</a>(<em class="lightnum">'.$value['num'].'</em>)';
		}
	}
}

cpheader();
shownav();

showsubmenu('home_welcome', array(), '', array('bbname' => $_G['setting']['bbname']));

$save_master = C::t('common_setting')->fetch_all(array('mastermobile', 'masterqq', 'masteremail'));
$save_mastermobile = $save_master['mastermobile'];
$save_mastermobile = !empty($save_mastermobile) ? authcode($save_mastermobile, 'DECODE', $_G['config']['security']['authkey']) : '';
$save_masterqq = $save_master['masterqq'] ? $save_master['masterqq'] : '';
$save_masteremail = $save_master['masteremail'] ? $save_master['masteremail'] : '';

$securityadvise = '';
if($isfounder) {
	$securityadvise = $_G['setting']['cloud_status'] ? cplang('home_security_service_open_info') : cplang('home_security_service_close_info');
	$securityadvise .= !$_G['config']['admincp']['founder'] ? $lang['home_security_nofounder'] : '';
	$securityadvise .= !$_G['config']['admincp']['checkip'] ? $lang['home_security_checkip'] : '';
	$securityadvise .= $_G['config']['admincp']['runquery'] ? $lang['home_security_runquery'] : '';
	if(!empty($_GET['securyservice'])) {
		$_GET['new_mastermobile'] = trim($_GET['new_mastermobile']);
		$_GET['new_masterqq'] = trim($_GET['new_masterqq']);
		$_GET['new_masteremail'] = trim($_GET['new_masteremail']);
		if(empty($_GET['new_mastermobile'])) {
			$save_mastermobile = $_GET['new_mastermobile'];
		} elseif(strlen($_GET['new_mastermobile']) == 11 && is_numeric($_GET['new_mastermobile']) && in_array(substr($_GET['new_mastermobile'], 0, 2), array('13', '15', '18'))) {
			$save_mastermobile = $_GET['new_mastermobile'];
			$_GET['new_mastermobile'] = authcode($_GET['new_mastermobile'], 'ENCODE', $_G['config']['security']['authkey']);
		} else {
			$_GET['new_mastermobile'] = $save_master['mastermobile'];
		}
		if(empty($_GET['new_masterqq']) || is_numeric($_GET['new_masterqq'])) {
			$save_masterqq = $_GET['new_masterqq'];
		} else {
			$_GET['new_masterqq'] = $save_masterqq;
		}
		if(empty($_GET['new_masteremail']) || (strlen($_GET['new_masteremail']) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $_GET['new_masteremail']))) {
			$save_masteremail = $_GET['new_masteremail'];
		} else {
			$_GET['new_masteremail'] = $save_masteremail;
		}

		C::t('common_setting')->update_batch(array('mastermobile' => $_GET['new_mastermobile'], 'masterqq' => $_GET['new_masterqq'], 'masteremail' => $_GET['new_masteremail']));
	}

	$view_mastermobile = !empty($save_mastermobile) ? substr($save_mastermobile, 0 , 3).'*****'.substr($save_mastermobile, -3) : '';
}

if($securityadvise) {
	showtableheader('home_security_tips', '', '', 0);
	showtablerow('', 'class="tipsblock"', '<ul>'.$securityadvise.'</ul>');
	showtablefooter();
}

$onlines = '';
$admincp_session = C::t('common_admincp_session')->fetch_all_by_panel(1);
$members = C::t('common_member')->fetch_all(array_keys($admincp_session), false, 0);
foreach($admincp_session as $uid => $online) {
	$onlines .= '<a href="home.php?mod=space&uid='.$online['uid'].'" title="'.dgmdate($online['dateline']).'" target="_blank">'.$members[$uid]['username'].'</a>&nbsp;&nbsp;&nbsp;';
}


echo '<div id="boardnews"></div>';

echo '<style>.rssbook{margin:8px 0 0 25px;}</style>';
echo '<script >var nId = "4d1e7b6dd9c5070d1a82aeb8be5e72fc64db42701a1bc4d4",nWidth="400px",sColor="light",sText="'.cplang('subscribe_comsenz_email').'" ;</script><script src="http://list.qq.com/zh_CN/htmledition/js/qf/page/qfcode.js" charset="gb18030"></script>';
showtableheader('', 'nobottom fixpadding');
if($membersmod || $threadsmod || $postsmod || $medalsmod || $blogsmod || $picturesmod || $doingsmod || $sharesmod || $commentsmod || $articlesmod || $articlecommentsmod || $topiccommentsmod || $threadsdel || !empty($verify)) {
	showtablerow('', '', '<h3 class="left margintop">'.cplang('home_mods').': </h3><p class="left difflink">'.
		($membersmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=members">'.cplang('home_mod_members').'</a>(<em class="lightnum">'.$membersmod.'</em>)' : '').
		($threadsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=threads&dateline=all">'.cplang('home_mod_threads').'</a>(<em class="lightnum">'.$threadsmod.'</em>)' : '').
		($postsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=replies&dateline=all">'.cplang('home_mod_posts').'</a>(<em class="lightnum">'.$postsmod.'</em>)' : '').
		($medalsmod ? '<a href="'.ADMINSCRIPT.'?action=medals&operation=mod">'.cplang('home_mod_medals').'</a>(<em class="lightnum">'.$medalsmod.'</em>)' : '').
		($groupmod ? '<a href="'.ADMINSCRIPT.'?action=group&operation=mod">'.cplang('group_mod_wait').'</a>(<em class="lightnum">'.$groupmod.'</em>)' : '').
		($blogsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=blogs&dateline=all">'.cplang('home_mod_blogs').'</a>(<em class="lightnum">'.$blogsmod.'</em>)' : '').
		($picturesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=pictures&dateline=all">'.cplang('home_mod_pictures').'</a>(<em class="lightnum">'.$picturesmod.'</em>)' : '').
		($doingsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=doings&dateline=all">'.cplang('home_mod_doings').'</a>(<em class="lightnum">'.$doingsmod.'</em>)' : '').
		($sharesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=shares&dateline=all">'.cplang('home_mod_shares').'</a>(<em class="lightnum">'.$sharesmod.'</em>)' : '').
		($commentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=comments&dateline=all">'.cplang('home_mod_comments').'</a>(<em class="lightnum">'.$commentsmod.'</em>)' : '').
		($articlesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=articles&dateline=all">'.cplang('home_mod_articles').'</a>(<em class="lightnum">'.$articlesmod.'</em>)' : '').
		($articlecommentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=articlecomments&dateline=all">'.cplang('home_mod_articlecomments').'</a>(<em class="lightnum">'.$articlecommentsmod.'</em>)' : '').
		($topiccommentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=topiccomments&dateline=all">'.cplang('home_mod_topiccomments').'</a>(<em class="lightnum">'.$topiccommentsmod.'</em>)' : '').
		($threadsdel ? '<a href="'.ADMINSCRIPT.'?action=recyclebin">'.cplang('home_del_threads').'</a>(<em class="lightnum">'.$threadsdel.'</em>)' : '').
		$verify.
		'</p><div class="clear"></div>'
	);
}
showtablefooter();

showtableheader('home_onlines', 'nobottom fixpadding');
echo '<tr><td>'.$onlines.'</td></tr>';
showtablefooter();

showformheader('index');
showtableheader('home_notes', 'fixpadding"', '', '3');

foreach(C::t('common_adminnote')->fetch_all_by_access(0) as $note) {
	if($note['expiration'] < TIMESTAMP) {
		C::t('common_adminnote')->delete($note['id']);
	} else {
		$note['adminenc'] = rawurlencode($note['admin']);
		$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
		$note['dateline'] = dgmdate($note['dateline'], 'dt');
		showtablerow('', array('', '', ''), array(
			$isfounder || $_G['member']['username'] == $note['admin'] ? '<a href="'.ADMINSCRIPT.'?action=index&notesubmit=yes&noteid='.$note['id'].'"><img src="static/image/admincp/close.gif" width="7" height="8" title="'.cplang('delete').'" /></a>' : '',
			"<span class=\"bold\"><a href=\"home.php?mod=space&username=$note[adminenc]\" target=\"_blank\">$note[admin]</a></span> $note[dateline] (".cplang('validity').": $note[expiration] ".cplang('days').")<br />$note[message]",
		));
	}
}

showtablerow('', array(), array(
	cplang('home_notes_add'),
	'<input type="text" class="txt" name="newmessage" value="" style="width:300px;" />'.cplang('validity').': <input type="text" class="txt" name="newexpiration" value="30" style="width:30px;" />'.cplang('days').'&nbsp;<input name="notesubmit" value="'.cplang('submit').'" type="submit" class="btn" />'
));
showtablefooter();
showformfooter();

loaducenter();

showtableheader('home_sys_info', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_discuz_version'),
	'Discuz! '.DISCUZ_VERSION.' Release '.DISCUZ_RELEASE.' <a href="http://faq.comsenz.com/checkversion.php?product=Discuz&version='.DISCUZ_VERSION.'&release='.DISCUZ_RELEASE.'&charset='.CHARSET.'&dbcharset='.$dbcharset.'" class="lightlink2 smallfont" target="_blank">'.cplang('home_check_newversion').'</a> <a href="http://www.comsenz.com/purchase/discuz/" class="lightlink2 smallfont" target="_blank">&#19987;&#19994;&#25903;&#25345;&#19982;&#26381;&#21153;</a> <a href="http://idc.comsenz.com" class="lightlink2 smallfont" target="_blank">&#68;&#105;&#115;&#99;&#117;&#122;&#33;&#19987;&#29992;&#20027;&#26426;</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_ucclient_version'),
	'UCenter '.UC_CLIENT_VERSION.' Release '.UC_CLIENT_RELEASE
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_environment'),
	$serverinfo
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_serversoftware'),
	$serversoft
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database'),
	$dbversion
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_upload_perm'),
	$fileupload
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database_size'),
	$dbsize
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_attach_size'),
	$attachsize
));
showtablefooter();

showtableheader('home_dev', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"'), array(
	cplang('home_dev_copyright'),
	'<span class="bold"><a href="http://www.comsenz.com" class="lightlink2" target="_blank">&#x5317;&#x4EAC;&#x5EB7;&#x76DB;&#x65B0;&#x521B;&#x79D1;&#x6280;&#x6709;&#x9650;&#x8D23;&#x4EFB;&#x516C;&#x53F8;</a></span>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
	cplang('home_dev_manager'),
	'<a href="http://www.discuz.net/home.php?mod=space&uid=1" class="lightlink2 smallfont" target="_blank">&#x6234;&#x5FD7;&#x5EB7; (Kevin \'Crossday\' Day)</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
	cplang('home_dev_team'),
	'
	 <a href="http://www.discuz.net/home.php?mod=space&uid=174393" class="lightlink2 smallfont" target="_blank">Guode \'sup\' Li</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=859" class="lightlink2 smallfont" target="_blank">Hypo \'Cnteacher\' Wang</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=263098" class="lightlink2 smallfont" target="_blank">Liming \'huangliming\' Huang</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=706770" class="lightlink2 smallfont" target="_blank">Jun \'Yujunhao\' Du</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=80629" class="lightlink2 smallfont" target="_blank">Ning \'Monkey\' Hou</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=246213" class="lightlink2 smallfont" target="_blank">Lanbo Liu</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=322293" class="lightlink2 smallfont" target="_blank">Qingpeng \'andy888\' Zheng</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=401635" class="lightlink2 smallfont" target="_blank">Guosheng \'bilicen\' Zhang</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=2829" class="lightlink2 smallfont" target="_blank">Mengshu \'msxcms\' Chen</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=492114" class="lightlink2 smallfont" target="_blank">Liang \'Metthew\' Xu</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=1087718" class="lightlink2 smallfont" target="_blank">Yushuai \'Max\' Cong</a>
	 <a href="http://www.discuz.net/home.php?mod=space&uid=875919" class="lightlink2 smallfont" target="_blank">Jie \'tom115701\' Zhang</a>
	 '
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight team"'), array(
	cplang('home_dev_skins'),
	'<a href="http://www.discuz.net/home.php?mod=space&uid=294092" class="lightlink2 smallfont" target="_blank">Fangming \'Lushnis\' Li</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=674006" class="lightlink2 smallfont" target="_blank">Jizhou \'Iavav\' Yuan</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=717854" class="lightlink2 smallfont" target="_blank">Ruitao \'Pony.M\' Ma</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight team"'), array(
	cplang('home_dev_thanks'),
	'<a href="http://www.discuz.net/home.php?mod=space&uid=122246" class="lightlink2 smallfont" target="_blank">Heyond</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=632268" class="lightlink2 smallfont" target="_blank">JinboWang</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=15104" class="lightlink2 smallfont" target="_blank">Redstone</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=10407" class="lightlink2 smallfont" target="_blank">Qiang Liu</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=210272" class="lightlink2 smallfont" target="_blank">XiaoDunFang</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=86282" class="lightlink2 smallfont" target="_blank">Jianxieshui</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=9600" class="lightlink2 smallfont" target="_blank">Theoldmemory</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=2629" class="lightlink2 smallfont" target="_blank">Rain5017</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=26926" class="lightlink2 smallfont" target="_blank">Snow Wolf</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=17149" class="lightlink2 smallfont" target="_blank">Hehechuan</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=9132" class="lightlink2 smallfont" target="_blank">Pk0909</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=248" class="lightlink2 smallfont" target="_blank">feixin</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=675" class="lightlink2 smallfont" target="_blank">Laobing Jiuba</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=13877" class="lightlink2 smallfont" target="_blank">Artery</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=233" class="lightlink2 smallfont" target="_blank">Huli Hutu</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=122" class="lightlink2 smallfont" target="_blank">Lao Gui</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=159" class="lightlink2 smallfont" target="_blank">Tyc</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=177" class="lightlink2 smallfont" target="_blank">Stoneage</a>
	<a href="http://www.discuz.net/home.php?mod=space&uid=7155" class="lightlink2 smallfont" target="_blank">Gregry</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	cplang('home_dev_links'),
	'<a href="http://www.comsenz.com" class="lightlink2" target="_blank">&#x516C;&#x53F8;&#x7F51;&#x7AD9;</a>,
		<a href="http://idc.comsenz.com" class="lightlink2" target="_blank">&#x865A;&#x62DF;&#x4E3B;&#x673A;</a>,
		<a href="http://www.comsenz.com/purchase/discuzx" class="lightlink2" target="_blank">&#x8D2D;&#x4E70;&#x6388;&#x6743;</a>,
		<a href="http://www.discuz.com/" class="lightlink2" target="_blank">&#x44;&#x69;&#x73;&#x63;&#x75;&#x7A;&#x21;&#x20;&#x4EA7;&#x54C1;</a>,
		<a href="http://www.comsenz.com/downloads/styles/discuz" class="lightlink2" target="_blank">&#x6A21;&#x677F;</a>,
		<a href="http://www.comsenz.com/downloads/plugins/discuz" class="lightlink2" target="_blank">&#x63D2;&#x4EF6;</a>,
		<a href="http://faq.comsenz.com" class="lightlink2" target="_blank">&#x6587;&#x6863;</a>,
		<a href="http://www.discuz.net/" class="lightlink2" target="_blank">&#x8BA8;&#x8BBA;&#x533A;</a>'
));
showtablefooter();

echo '</div>';

?>