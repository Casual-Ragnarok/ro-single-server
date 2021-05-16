<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_templates.php 29301 2012-04-01 02:55:08Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

$operation = empty($operation) ? 'admin' : $operation;

if($operation == 'admin') {

	if(!submitcheck('tplsubmit')) {

		$templates = '';
		foreach(C::t('common_template')->fetch_all_data() as $tpl) {
			$basedir = basename($tpl['directory']);
			$templates .= showtablerow('', array('class="td25"', '', 'class="td29"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" ".($tpl['templateid'] == 1 ? 'disabled ' : '')."value=\"$tpl[templateid]\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"namenew[$tpl[templateid]]\" value=\"$tpl[name]\">".
				($basedir != 'default' ? '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.urlencode($basedir).'.template" target="_blank" title="'.$lang['cloudaddons_linkto'].'">'.$lang['view'].'</a>' : ''),
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"directorynew[$tpl[templateid]]\" value=\"$tpl[directory]\">",
				!empty($tpl['copyright']) ?
					$tpl['copyright'] :
					"<input type=\"text\" class=\"txt\" size=\"8\" name=\"copyrightnew[$tpl[templateid]]\" value=>"
			), TRUE);
		}

		shownav('style', 'templates_admin');
		showsubmenu('templates_admin');
		showformheader('templates');
		showtableheader();
		showsubtitle(array('', 'templates_admin_name', 'dir', 'copyright'));
		echo $templates;
		echo '<tr><td>'.$lang['add_new'].'</td><td><input type="text" class="txt" size="8" name="newname"></td><td class="td29"><input type="text" class="txt" size="20" name="newdirectory"></td><td><input type="text" class="txt" size="25" name="newcopyright"></td><td>&nbsp;</td></tr>';
		showsubmit('tplsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['newname']) {
			if(!$_GET['newdirectory']) {
				cpmsg('tpl_new_directory_invalid', '', 'error');
			} elseif(!istpldir($_GET['newdirectory'])) {
				$directory = $_GET['newdirectory'];
				cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
			}
			C::t('common_template')->insert(array('name' => $_GET['newname'], 'directory' => $_GET['newdirectory'], 'copyright' => $_GET['newcopyright']));
		}

		foreach($_GET['directorynew'] as $id => $directory) {
			if(!$_GET['delete'] || ($_GET['delete'] && !in_array($id, $_GET['delete']))) {
				if(!istpldir($directory)) {
					cpmsg('tpl_directory_invalid', '', 'error', array('directory' => $directory));
				} elseif($id == 1 && $directory != './template/default') {
					cpmsg('tpl_default_directory_invalid', '', 'error');
				}
				C::t('common_template')->update($id, array('name' => $_GET['namenew'][$id], 'directory' => $_GET['directorynew'][$id]));
				if(!empty($_GET['copyrightnew'][$id])) {
					$template = C::t('common_template')->fetch($id);
					if(!$template['copyright']) {
						C::t('common_template')->update($id, array('copyright' => $_GET['copyrightnew'][$id]));
					}
				}
			}
		}

		if(is_array($_GET['delete'])) {
			if(in_array('1', $_GET['delete'])) {
				cpmsg('tpl_delete_invalid', '', 'error');
			}
			if($_GET['delete']) {
				C::t('common_template')->delete($_GET['delete']);
				C::t('common_style')->update($_GET['delete'], array('templateid' => 1));
			}
		}

		updatecache('styles');
		cpmsg('tpl_update_succeed', 'action=templates', 'succeed');

	}

}
?>