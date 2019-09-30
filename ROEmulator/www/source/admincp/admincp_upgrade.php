<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_upgrade.php 33274 2013-05-14 02:08:00Z jeffjzhang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);
cpheader();
include_once 'source/discuz_version.php';
$discuz_upgrade = new discuz_upgrade();

$step = intval($_GET['step']);
$step = $step ? $step : 1;
$operation = $operation ? $operation : 'check';

if(!$_G['setting']['bbclosed'] && in_array($operation, array('cross', 'patch'))) {
	cpmsg('upgrade_close_site', '', 'error');
}

if($operation == 'patch' || $operation == 'cross') {

	$version = trim($_GET['version']);
	$release = trim($_GET['release']);
	$locale = trim($_GET['locale']);
	$charset = trim($_GET['charset']);
	$upgradeinfo = $upgrade_step = array();

	if($_GET['ungetfrom']) {
		if(md5($_GET['ungetfrom'].$_G['config']['security']['authkey']) == $_GET['ungetfrommd5']) {
			$dbreturnurl = $_G['siteurl'].ADMINSCRIPT.'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&step=5';
			dheader('Location: '.$_G['siteurl'].'install/update.php?step=prepare&from='.rawurlencode($dbreturnurl).'&frommd5='.rawurlencode(md5($dbreturnurl.$_G['config']['security']['authkey'])));
		} else {
			cpmsg('upgrade_param_error', '', 'error');
		}
	}

	$upgrade_step = C::t('common_cache')->fetch('upgrade_step');
	$upgrade_step = dunserialize($upgrade_step['cachevalue']);
	$upgrade_step['step'] = $step;
	$upgrade_step['operation'] = $operation;
	$upgrade_step['version'] = $version;
	$upgrade_step['release'] = $release;
	$upgrade_step['charset'] = $charset;
	$upgrade_step['locale'] = $locale;
	C::t('common_cache')->insert(array(
		'cachekey' => 'upgrade_step',
		'cachevalue' => serialize($upgrade_step),
		'dateline' => $_G['timestamp'],
	), false, true);

	$upgrade_run = C::t('common_cache')->fetch('upgrade_run');
	if(!$upgrade_run) {
		C::t('common_cache')->insert(array(
			'cachekey' => 'upgrade_run',
			'cachevalue' => serialize($_G['setting']['upgrade']),
			'dateline' => $_G['timestamp'],
		), false, true);
		$upgrade_run = $_G['setting']['upgrade'];
	} else {
		$upgrade_run = dunserialize($upgrade_run['cachevalue']);
	}

	shownav('tools', 'nav_founder_upgrade');
	showsubmenusteps('nav_founder_upgrade', array(
		array('founder_upgrade_updatelist', $step == 1),
		array('founder_upgrade_download', $step == 2),
		array('founder_upgrade_compare', $step == 3),
		array('founder_upgrade_upgrading', $step == 4),
		array('founder_upgrade_complete', $step == 5),
	));
	showtableheader();

	if($step != 5) {

		foreach($upgrade_run as $type => $list) {
			if($type == $operation && $version == $list['latestversion'] && $release == $list['latestrelease']) {
				$discuz_upgrade->locale = $locale;
				$discuz_upgrade->charset = $charset;
				$upgradeinfo = $list;
				break;
			}
		}
		if(!$upgradeinfo) {
			cpmsg('upgrade_none', '', '', array('upgradeurl' => upgradeinformation(-1)));
		}

		$updatefilelist = $discuz_upgrade->fetch_updatefile_list($upgradeinfo);
		$updatemd5filelist = $updatefilelist['md5'];
		$updatefilelist = $updatefilelist['file'];

		$theurl = 'upgrade&operation='.$operation.'&version='.$version.'&locale='.$locale.'&charset='.$charset.'&release='.$release;

		if(empty($updatefilelist)) {
			cpmsg('upgrade_download_upgradelist_error', 'action='.$theurl, 'form', array('upgradeurl' => upgradeinformation(-2)));
		}

	}

	if($step == 1) {
		showtablerow('class="header"', '', $lang['founder_upgrade_preupdatelist']);
		foreach($updatefilelist as $file) {
			$file = '<em class="files bold">'.$file.'</em>';
			showtablerow('', '', array($file));
		}
		$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=2';
		showtablerow('', '', array($lang['founder_upgrade_store_directory'].'./data/update/Discuz! X'.$version.' Release['.$release.']'));
		showtablerow('', '', array('<input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_download'].'">'));
		echo upgradeinformation(0);
	} elseif($step == 2) {
		$fileseq = intval($_GET['fileseq']);
		$fileseq = $fileseq ? $fileseq : 1;
		if($fileseq > count($updatefilelist)) {
			if($upgradeinfo['isupdatedb']) {
				$discuz_upgrade->download_file($upgradeinfo, 'install/data/install.sql');
				$discuz_upgrade->download_file($upgradeinfo, 'install/data/install_data.sql');
				$discuz_upgrade->download_file($upgradeinfo, 'update.php', 'utility');
			}
			$linkurl = 'action='.$theurl.'&step=3';
			cpmsg('upgrade_download_complete_to_compare', $linkurl, 'loading', array('upgradeurl' => upgradeinformation(0)));
		} else {
			$downloadstatus = $discuz_upgrade->download_file($upgradeinfo, $updatefilelist[$fileseq-1], 'upload', $updatemd5filelist[$fileseq-1]);
			if($downloadstatus == 1) {
				$linkurl = 'action='.$theurl.'&step=2&fileseq='.$fileseq;
				cpmsg('upgrade_downloading_file', $linkurl, 'loading', array('file' => $updatefilelist[$fileseq-1], 'percent' => sprintf("%2d", 100 * $fileseq/count($updatefilelist)).'%', 'upgradeurl' => upgradeinformation(1)));
			} elseif($downloadstatus == 2) {
				$linkurl = 'action='.$theurl.'&step=2&fileseq='.($fileseq+1);
				cpmsg('upgrade_downloading_file', $linkurl, 'loading', array('file' => $updatefilelist[$fileseq-1], 'percent' => sprintf("%2d", 100 * $fileseq/count($updatefilelist)).'%', 'upgradeurl' => upgradeinformation(1)));
			} else {
				cpmsg('upgrade_redownload', 'action='.$theurl.'&step=2&fileseq='.$fileseq, 'form', array('file' => $updatefilelist[$fileseq-1], 'upgradeurl' => upgradeinformation(-3)));
			}
		}
	} elseif($step == 3) {
		list($modifylist, $showlist, $ignorelist) = $discuz_upgrade->compare_basefile($upgradeinfo, $updatefilelist);
		if(empty($modifylist) && empty($showlist) && empty($ignorelist)) {
			cpmsg('filecheck_nofound_md5file', '', 'error', array('upgradeurl' => upgradeinformation(-4)));
		}
		showtablerow('class="header"', 'colspan="2"', $lang['founder_upgrade_diff_show']);
		foreach($updatefilelist as $v) {
			if(isset($ignorelist[$v])) {
				continue;
			} elseif(isset($modifylist[$v])) {
				showtablerow('', array('class="" style="color:red;"', 'class="td24" style="color:red;"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_diff'].'<em class="edited">&nbsp;</em>'));
			} elseif(isset($showlist[$v])) {
				showtablerow('', array('class=""', 'class="td24"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_normal'].'<em class="fixed">&nbsp;</em>'));
			} else {
				showtablerow('', array('class=""', 'class="td24"'), array('<em class="files bold">'.$v.'</em>', $lang['founder_upgrade_new'].'<em class="unknown">&nbsp;</em>'));
			}
		}

		$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4';
		showtablerow('', 'colspan="2"', $lang['founder_upgrade_download_file'].' ./data/update/Discuz! X'.$version.' Release['.$release.']'.'');
		showtablerow('', 'colspan="2"', $lang['founder_upgrade_backup_file'].' ./data/back/Discuz! '.DISCUZ_VERSION.' Release['.DISCUZ_RELEASE.']'.$lang['founder_upgrade_backup_file2']);
		showtablerow('', 'colspan="2"', '<input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.(!empty($modifylist) ? $lang['founder_upgrade_force'] : $lang['founder_upgrade_regular']).'" />');
		echo upgradeinformation(0);
	} elseif($step == 4) {

		$confirm = $_GET['confirm'];
		if(!$confirm) {
			if($_GET['siteftpsetting']) {
				$action = $theurl.'&step=4&confirm=ftp'.($_GET['startupgrade'] ? '&startupgrade=1' : '');
				siteftp_form($action);
				exit;
			}

			if($upgradeinfo['isupdatedb']) {
				$checkupdatefilelist = array('install/update.php', 'install/data/install.sql','install/data/install_data.sql');
				$checkupdatefilelist = array_merge($checkupdatefilelist, $updatefilelist);
			} else {
				$checkupdatefilelist = $updatefilelist;
			}
			if($discuz_upgrade->check_folder_perm($checkupdatefilelist)) {
				$confirm = 'file';
			} else {
				$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4';
				$ftplinkurl = $linkurl.'&siteftpsetting=1';
				cpmsg('upgrade_cannot_access_file',
					'',
					'',
					array(),
					'<br /><input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_set_ftp'].'" />'.
					' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_reset'].'" /><br /><br />'
				);
			}
		}

		$paraftp = '';
		if($_GET['siteftp']) {
			foreach($_GET['siteftp'] as $k => $v) {
				$paraftp .= '&siteftp['.$k.']='.$v;
			}
		}
		if(!$_GET['startupgrade']) {
			if(!$_GET['backfile']) {
				cpmsg('upgrade_backuping', 'action='.$theurl.'&step=4&backfile=1&confirm='.$confirm.$paraftp, 'loading', array('upgradeurl' => upgradeinformation(2)), false);
			}
			foreach($updatefilelist as $updatefile) {
				$destfile = DISCUZ_ROOT.$updatefile;
				$backfile = DISCUZ_ROOT.'./data/back/Discuz! X'.substr(DISCUZ_VERSION, 1).' Release['.DISCUZ_RELEASE.']/'.$updatefile;
				if(is_file($destfile)) {
					if(!$discuz_upgrade->copy_file($destfile, $backfile, 'file')) {
						cpmsg('upgrade_backup_error', '', 'error', array('upgradeurl' => upgradeinformation(-5)));
					}
				}
			}
			cpmsg('upgrade_backup_complete', 'action='.$theurl.'&step=4&startupgrade=1&confirm='.$confirm.$paraftp, 'loading', array('upgradeurl' => upgradeinformation(3)), false);
		}

		$linkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4&startupgrade=1&confirm='.$confirm.$paraftp;
		$ftplinkurl = ADMINSCRIPT.'?action='.$theurl.'&step=4&startupgrade=1&siteftpsetting=1';
		foreach($updatefilelist as $updatefile) {
			$srcfile = DISCUZ_ROOT.'./data/update/Discuz! X'.$version.' Release['.$release.']/'.$updatefile;
			if($confirm == 'ftp') {
				$destfile = $updatefile;
			} else {
				$destfile = DISCUZ_ROOT.$updatefile;
			}
			if(!$discuz_upgrade->copy_file($srcfile, $destfile, $confirm)) {
				if($confirm == 'ftp') {
					cpmsg('upgrade_ftp_upload_error',
						'',
						'',
						array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-6)),
						'<br /><input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_reupload'].'" />'.
						' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_reset_ftp'].'" /><br /><br />'
					);
				} else {
					cpmsg('upgrade_copy_error',
						'',
						'',
						array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-7)),
						'<br /><input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_recopy'].'" />'.
						' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_set_ftp'].'" /><br /><br />'
					);
				}
			}
		}
		if($upgradeinfo['isupdatedb']) {
			$dbupdatefilearr = array('update.php', 'install/data/install.sql','install/data/install_data.sql');
			foreach($dbupdatefilearr as $dbupdatefile) {
				$srcfile = DISCUZ_ROOT.'./data/update/Discuz! X'.$version.' Release['.$release.']/'.$dbupdatefile;
				$dbupdatefile = $dbupdatefile == 'update.php' ? 'install/update.php' : $dbupdatefile;
				if($confirm == 'ftp') {
					$destfile = $dbupdatefile;
				} else {
					$destfile = DISCUZ_ROOT.$dbupdatefile;
				}
				if(!$discuz_upgrade->copy_file($srcfile, $destfile, $confirm)) {
					if($confirm == 'ftp') {
						cpmsg('upgrade_ftp_upload_error',
							'',
							'',
							array('file' => $dbupdatefile, 'upgradeurl' => upgradeinformation(-6)),
							'<br /><input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_reupload'].'" />'.
							' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_reset_ftp'].'" /><br /><br />'
						);
					} else {
						cpmsg('upgrade_copy_error',
							'',
							'',
							array('file' => $dbupdatefile, 'upgradeurl' => upgradeinformation(-7)),
							'<br /><input type="button" class="btn" onclick="window.location.href=\''.$linkurl.'\'" value="'.$lang['founder_upgrade_recopy'].'" />'.
							' &nbsp; <input type="button" class="btn" onclick="window.location.href=\''.$ftplinkurl.'\'" value="'.$lang['founder_upgrade_set_ftp'].'" /><br /><br />'
						);
					}
				}
			}
			$upgrade_step['step'] = 'dbupdate';
			C::t('common_cache')->insert(array(
				'cachekey' => 'upgrade_step',
				'cachevalue' => serialize($upgrade_step),
				'dateline' => $_G['timestamp'],
			), false, true);
			$dbreturnurl = $_G['siteurl'].ADMINSCRIPT.'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&step=5';
			cpmsg('upgrade_file_successful', $_G['siteurl'].'install/update.php?step=prepare&from='.rawurlencode($dbreturnurl).'&frommd5='.rawurlencode(md5($dbreturnurl.$_G['config']['security']['authkey'])), '', array('upgradeurl' => upgradeinformation(4)));
		}
		dheader('Location: '.ADMINSCRIPT.'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&step=5');

	} elseif($step == 5) {
		$file = DISCUZ_ROOT.'./data/update/Discuz! X'.$version.' Release['.$release.']/updatelist.tmp';
		@unlink($file);
		@unlink(DISCUZ_ROOT.'./install/update.php');
		C::t('common_cache')->delete('upgrade_step');
		C::t('common_cache')->delete('upgrade_run');
		C::t('common_setting')->update('upgrade', '');
		updatecache('setting');
		$old_update_dir = './data/update/';
		$new_update_dir = './data/update'.md5('update'.$_G['config']['security']['authkey']).'/';
		$old_back_dir = './data/back/';
		$new_back_dir = './data/back'.md5('back'.$_G['config']['security']['authkey']).'/';
		$discuz_upgrade->copy_dir(DISCUZ_ROOT.$old_update_dir, DISCUZ_ROOT.$new_update_dir);
		$discuz_upgrade->copy_dir(DISCUZ_ROOT.$old_back_dir, DISCUZ_ROOT.$new_back_dir);
		$discuz_upgrade->rmdirs(DISCUZ_ROOT.$old_update_dir);
		$discuz_upgrade->rmdirs(DISCUZ_ROOT.$old_back_dir);
		cpmsg('upgrade_successful', '', 'succeed', array('version' => $version, 'release' => $release, 'save_update_dir' => $new_update_dir, 'save_back_dir' => $new_back_dir, 'upgradeurl' => upgradeinformation(0)), '<script type="text/javascript">if(parent.document.getElementById(\'notice\')) parent.document.getElementById(\'notice\').style.display = \'none\';</script>');
	}
	showtablefooter();

} elseif($operation == 'check') {
	if(!intval($_GET['rechecking'])) {
		$upgrade_step = C::t('common_cache')->fetch('upgrade_step');
		if(!empty($upgrade_step['cachevalue'])) {
			$upgrade_step['cachevalue'] = dunserialize($upgrade_step['cachevalue']);
			if(!empty($upgrade_step['cachevalue']['step'])) {
				$theurl = 'upgrade&operation='.$upgrade_step['cachevalue']['operation'].'&version='.$upgrade_step['cachevalue']['version'].'&locale='.$upgrade_step['cachevalue']['locale'].'&charset='.$upgrade_step['cachevalue']['charset'].'&release='.$upgrade_step['cachevalue']['release'];
				$steplang = array('', cplang('founder_upgrade_updatelist'), cplang('founder_upgrade_download'), cplang('founder_upgrade_compare'), cplang('founder_upgrade_upgrading'), cplang('founder_upgrade_complete'), 'dbupdate' => cplang('founder_upgrade_dbupdate'));
				$recheckurl = ADMINSCRIPT.'?action=upgrade&operation=recheck';
				if($upgrade_step['cachevalue']['step'] == 'dbupdate') {
					$dbreturnurl = $_G['siteurl'].ADMINSCRIPT.'?action='.$theurl.'&step=5';
					$stepurl =  $_G['siteurl'].'install/update.php?step=prepare&from='.rawurlencode($dbreturnurl).'&frommd5='.rawurlencode(md5($dbreturnurl.$_G['config']['security']['authkey']));
					cpmsg('upgrade_continue',
						'',
						'',
						array('step' => $steplang['dbupdate']),
						'<br /><input type="button" class="btn" onclick="window.location.href=\''.$stepurl.'\'" value="'.$lang['founder_upgrade_continue'].'" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="btn" onclick="window.location.href=\''.$recheckurl.'\'" value="'.$lang['founder_upgrade_recheck'].'" /><br /><br />'
					);
				} else {
					$stepurl =  ADMINSCRIPT.'?action='.$theurl.'&step='.$upgrade_step['cachevalue']['step'];
					cpmsg('upgrade_continue',
						'',
						'',
						array('step' => $steplang[$upgrade_step['cachevalue']['step']]),
						'<br /><input type="button" class="btn" onclick="window.location.href=\''.$stepurl.'\'" value="'.$lang['founder_upgrade_continue'].'" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="btn" onclick="window.location.href=\''.$recheckurl.'\'" value="'.$lang['founder_upgrade_recheck'].'" /><br /><br />'
					);
				}
			}
		}
	} else {
		C::t('common_cache')->delete('upgrade_step');
	}
	if(!intval($_GET['checking'])) {
		cpmsg('upgrade_checking', 'action=upgrade&operation=check&checking=1', 'loading', '', false);
	}
	$discuz_upgrade->check_upgrade();
	dheader('Location: '.ADMINSCRIPT.'?action=upgrade&operation=showupgrade');

} elseif($operation == 'showupgrade') {
	shownav('tools', 'nav_founder_upgrade');
	showsubmenu('nav_founder_upgrade');
	showtableheader();
	if(!$_G['setting']['upgrade']) {
		cpmsg('upgrade_latest_version', '', 'succeed');
	} else {

		C::t('common_cache')->insert(array(
			'cachekey' => 'upgrade_step',
			'cachevalue' => serialize(array('curversion' => $discuz_upgrade->versionpath(), 'currelease' => DISCUZ_RELEASE)),
			'dateline' => $_G['timestamp'],
		), false, true);

		$upgraderow = $patchrow = array();
		$charset = str_replace('-', '', strtoupper($_G['config']['output']['charset']));
		$dbversion = helper_dbtool::dbversion();
		$locale = '';
		if($charset == 'BIG5') {
			$locale = 'TC';
		} elseif($charset == 'GBK') {
			$locale = 'SC';
		} elseif($charset == 'UTF8') {
			if($_G['config']['output']['language'] == 'zh_cn') {
				$locale = 'SC';
			} elseif($_G['config']['output']['language'] == 'zh_tw') {
				$locale = 'TC';
			}
		}
		foreach($_G['setting']['upgrade'] as $type => $upgrade) {
			$unupgrade = 0;
			if(version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
				$unupgrade = 1;
			}

			$linkurl = ADMINSCRIPT.'?action=upgrade&operation='.$type.'&version='.$upgrade['latestversion'].'&locale='.$locale.'&charset='.$charset.'&release='.$upgrade['latestrelease'];
			if($unupgrade) {
				$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', $lang['founder_upgrade_require_config'].' php v'.PHP_VERSION.'MYSQL v'.$dbversion, ''), TRUE);
			} else {
				$upgraderow[] = showtablerow('', '', array('Discuz! X'.$upgrade['latestversion'].'_'.$locale.'_'.$charset.$lang['version'].' [Release '.$upgrade['latestrelease'].']'.($type == 'patch' ? '('.$lang['founder_upgrade_newword'].'release)' : '').'', '<input type="button" class="btn" onclick="confirm(\''.$lang['founder_upgrade_backup_remind'].'\') ? window.location.href=\''.$linkurl.'\' : \'\';" value="'.$lang['founder_upgrade_automatically'].'">', '<a href="'.$upgrade['official'].'" target="_blank">'.$lang['founder_upgrade_manually'].'</a>'), TRUE);
			}
		}
		showtablerow('class="header"','', array($lang['founder_upgrade_select_version'], '', ''));
		if($upgraderow) {
			foreach($upgraderow as $row) {
				echo $row;
			}
		}
		if($patchrow) {
			foreach($patchrow as $row) {
				echo $row;
			}
		}
	}
	showtablefooter();
} elseif($operation == 'recheck') {

	$upgrade_step = C::t('common_cache')->fetch('upgrade_step');
	$upgrade_step = dunserialize($upgrade_step['cachevalue']);
	$file = DISCUZ_ROOT.'./data/update/Discuz! X'.$upgrade_step['version'].' Release['.$upgrade_step['release'].']/updatelist.tmp';
	@unlink($file);
	@unlink(DISCUZ_ROOT.'./install/update.php');
	C::t('common_cache')->delete('upgrade_step');
	C::t('common_cache')->delete('upgrade_run');
	C::t('common_setting')->update('upgrade', '');
	updatecache('setting');
	$old_update_dir = './data/update/';
	$discuz_upgrade->rmdirs(DISCUZ_ROOT.$old_update_dir);
	dheader('Location: '.ADMINSCRIPT.'?action=upgrade');
}
?>