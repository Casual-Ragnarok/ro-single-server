<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_patch.php 29258 2012-03-31 03:56:17Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation , array('patchsetting', 'fixpatch', 'checkpatch', 'recheckpatch')) ? $operation : 'checkpatch';

$discuz_patch = new discuz_patch();

if($operation == 'patchsetting') {
	$save_master = C::t('common_setting')->fetch_all(array('mastermobile', 'masterqq', 'masteremail'));
	$save_mastermobile = $save_master['mastermobile'];
	$save_mastermobile = !empty($save_mastermobile) ? authcode($save_mastermobile, 'DECODE', $_G['config']['security']['authkey']) : '';

	if(!submitcheck('settingsubmit')) {
		$view_mastermobile = !empty($save_mastermobile) ? substr($save_mastermobile, 0 , 3).'*****'.substr($save_mastermobile, -3) : '';

		shownav('founder', 'nav_founder_patch');
		showsubmenu('nav_founder_patch', array(
			array('founder_patch_list', 'patch&operation=fixpatch',  0),
			array('founder_patch_updatesetting', 'patch&operation=patchsetting', 1),
		));
		showformheader('patch&operation=patchsetting');
		showtableheader();
		showsetting('founder_patch_autoupdate', 'settingnew[patch][autoopened]', $_G['setting']['patch']['autoopened'], 'radio');
		showsubmit('settingsubmit', 'submit');
		showtablefooter();
		showformfooter();
	} else {
		$settings = array();
		$settingnew = $_POST['settingnew'];
		if($settingnew) {



			if(!$discuz_patch->save_patch_setting($settingnew)) {
				cpmsg('patch_no_privilege_autoupdate', '', 'error');
			}
		}
		cpmsg('patch_updatesetting_successful', 'action=patch&operation='.$operation, 'succeed');
	}

} elseif($operation == 'fixpatch') {

	if(!submitcheck('fixpatchsubmit', 1)) {
		shownav('founder', 'nav_founder_patch');
		showsubmenu('nav_founder_patch', array(
			array('founder_patch_list', 'patch&operation=fixpatch',  1),
			array('founder_patch_updatesetting', 'patch&operation=patchsetting', 0),
		));
		showformheader('patch&operation=fixpatch');
		showtableheader('', 'fixpadding', '', 5);
		showtablerow('class="header"', array('class="td25"','class="td24"','', 'class="td31"', 'class="td25"'), array(
			'',
			$lang['founder_patch_serial'],
			$lang['founder_patch_note'],
			$lang['founder_patch_dateline'],
			$lang['founder_patch_status'],
		));
		$patchlist = C::t('common_patch')->fetch_all();
		foreach($patchlist as $patch) {
			showtablerow($patch['status'] <= 0 ? 'title="'.$lang['founder_patchstatus_'.($patch['status'] < 0 ? 'error'.$patch['status'] : $patch['status'])].'"' : '', '', array(
				'<input class="checkbox" type="checkbox" value="'.$patch['serial'].'"'.($patch['status'] >= 1 ? ' disabled' : ' name="deletefix[]" checked').'>',
				$patch['serial'],
				$patch['note'],
				dgmdate($patch['dateline'], 'Y-m-d H:i:s'),
				'<em class="'.($patch['status'] <= 0 ? 'unfixed' : 'fixed').'">&nbsp;</em>',
			));
		}
		showsubmit('fixpatchsubmit', 'founder_patch_fix', 'select_all', ' <input type="button" class="btn" onclick="window.location.href=\''.ADMINSCRIPT.'?action=patch&operation=recheckpatch'.'\';" value="'.$lang['founder_patch_rescan'].'">');
		showtablefooter();
		showformfooter();

	} else {
		$patchlist = $_GET['deletefix'];
		if(empty($patchlist)) {
			cpmsg('patch_please_select_patch', '', 'error');
		}
		$confirm = $_GET['confirm'];
		if(!$confirm) {

			if($_GET['siteftpsetting']) {
				$action = 'patch&operation=fixpatch&fixpatchsubmit=yes&confirm=ftp';
				foreach($patchlist as $serial) {
					$action .= '&deletefix[]='.$serial;
				}
				siteftp_form($action);
				exit;
			}

			$flag = 0;
			foreach(C::t('common_patch')->fetch_needfix_patch($patchlist) as $patch) {
				if(!$discuz_patch->test_patch_writable($patch)) {
					$flag = 1;
					break;
				}
			}
			if(!$flag) {
				$confirm = 'file';
			} else {
				$linkurl = ADMINSCRIPT.'?action=patch&operation='.$operation.'&fixpatchsubmit=yes';
				foreach($patchlist as $serial) {
					$linkurl .= '&deletefix[]='.$serial;
				}
				$ftplinkurl = $linkurl.'&siteftpsetting=1';
				cpmsg('patch_cannot_access_file',
					'',
					'',
					array(),
					'<br><input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_patch_set_ftpinfo'].'">'.
					'&nbsp;&nbsp;&nbsp;<input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_patch_reset'].'"><br><br>'
				);
			}
		}

		$failed = array();
		if($patchlist) {
			$patchlist = C::t('common_patch')->fetch_needfix_patch($patchlist);
			foreach($patchlist as $patch) {
				$result = $discuz_patch->fix_patch($patch, $confirm);
				if($result < 0) {
					$failed[] = array('serial' => $patch['serial'], 'reason' => $lang['founder_patchstatus_'.($result < 0 ? 'error'.$result : $result)]);
				}
			}
		}
		if($failed) {
			$failstr = '';
			foreach($failed as $v) {
				$failstr .= $lang['founder_patch_fixpatch'].$v['serial'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$lang['founder_patch_failedreason'].': '.$v['reason']."<br>\r\n";
			}
			cpmsg('patch_updatesetting_failed', 'action=patch&operation='.$operation, 'error', array('list' => $failstr));
		} else {
			cpmsg('patch_successful', 'action=patch&operation='.$operation, 'succeed', array(), '<script type="text/javascript">if(parent.document.getElementById(\'notice\')) parent.document.getElementById(\'notice\').style.display = \'none\';</script>');
		}
	}

} elseif($operation == 'checkpatch') {

	if(!intval($_GET['checking'])) {
		cpmsg('patch_cheking', 'action=patch&operation=checkpatch&checking=1', 'loading', '', false);
	}
	$discuz_patch->check_patch(1);
	dheader('Location: '.ADMINSCRIPT.'?action=patch&operation=fixpatch');

} elseif($operation == 'recheckpatch') {

	$discuz_patch->recheck_patch();
	cpmsg('patch_successful', 'action=patch&operation=fixpatch', 'succeed');

}
?>