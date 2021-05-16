<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: evilnotice.inc.php 33944 2013-09-04 07:33:32Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_G['adminid'] <= 0) {
	exit('Access Denied');
}

if ($_GET['formhash'] != formhash()) {
    exit('Access Denied');
}

$count = empty($_G['cookie']['evilnotice']) ? C::t('#security#security_eviluser')->count_by_day() : 0;
include template('common/header_ajax');
if($count) {
	echo '<div class="bm"><div class="bm_h cl"><a href="javascript:;" onclick="$(\'evil_notice\').style.display=\'none\';setcookie(\'evilnotice\', 1, 86400)" class="y" title="'.lang('plugin/security', 'notice_close').'">'.lang('plugin/security', 'notice_close').'</a>';
	echo '<h2 class="i">'.lang('plugin/security', 'notice_title').'</h2></div><div class="bm_c">';
	echo '<div class="cl bbda pbm">'.lang('plugin/security', 'notice_memo', array('count' => $count)).'</div>';
	echo '<div class="ptn cl"><a href="admin.php?action=cloud&operation=security&anchor=member" class="xi2 y">'.lang('plugin/security', 'notice_link').' &raquo;</a></div>';
	echo '</div></div>';
}
include template('common/footer_ajax');

?>