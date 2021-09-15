<?php
/**
 * DZAPP Haodai SEO Settings
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$lang = array_merge($lang, $scriptlang['dzapp_haodai']);

if(empty($_GET['ac'])) {
	if(!submitcheck('seosubmit')) {
		echo '<script type="text/javascript">
		function insertContent(obj, text) {
			var obj = obj.parentNode.parentNode.firstChild.lastChild;
			selection = document.selection;
			obj.focus();
			if(!isUndefined(obj.selectionStart)) {
				var opn = obj.selectionStart + 0;
				obj.value = obj.value.substr(0, obj.selectionStart) + text + obj.value.substr(obj.selectionEnd);
			} else if(selection && selection.createRange) {
				var sel = selection.createRange();
				sel.text = text;
				sel.moveStart(\'character\', -strlen(text));
			} else {
				obj.value += text;
			}
		}
		</script>';
		$dzapp_haodai_seo = dunserialize($_G['setting']['dzapp_haodai_seo']);
		$codetypes['main'] = array('bbname');
		$codetypes['search'] = array('bbname', 'xd_type');
		$codetypes['view'] = array('bbname', 'xd_type', 'bank_name', 'xd_name');
		$codetypes['news'] = array('bbname', 'news_title');
		$codetypes['apply'] = array('bbname');
		$codetypes['calc'] = array('bbname', 'calc_type');
		$codenames['bbname'] = $lang['bbname'];
		$codenames['xd_type'] = $lang['xd_type'];
		$codenames['bank_name'] = $lang['bank_name'];
		$codenames['xd_name'] = $lang['xd_name'];
		$codenames['news_title'] = $lang['news_title'];
		$codenames['calc_type'] = $lang['calc_type'];
		foreach(array('main', 'search', 'view', 'news', 'apply', 'calc') as $page) {
			$codes[$page] = $lang['code'];
			foreach($codetypes[$page] as $type) {
				$codes[$page] .= '<a onclick="insertContent(this, \'{'.$type.'}\');return false;" href="javascript:;" title="'.$codenames[$type].'">{'.$codenames[$type].'}</a>';
			}
		}

		showformheader('plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_seo');
		showtableheader();
		showtitle('dzapp_haodai_main');
		showsetting('seotitle', 'dzapp_haodai_seo[main][seotitle]', $dzapp_haodai_seo['main']['seotitle'], 'text', '', 0, $codes['main']);
		showsetting('seokeywords', 'dzapp_haodai_seo[main][seokeywords]', $dzapp_haodai_seo['main']['seokeywords'], 'text', '', 0, $codes['main']);
		showsetting('seodescription', 'dzapp_haodai_seo[main][seodescription]', $dzapp_haodai_seo['main']['seodescription'], 'text', '', 0, $codes['main']);
		showtitle('dzapp_haodai_search');
		showsetting('seotitle', 'dzapp_haodai_seo[search][seotitle]', $dzapp_haodai_seo['search']['seotitle'], 'text', '', 0, $codes['search']);
		showsetting('seokeywords', 'dzapp_haodai_seo[search][seokeywords]', $dzapp_haodai_seo['search']['seokeywords'], 'text', '', 0, $codes['search']);
		showsetting('seodescription', 'dzapp_haodai_seo[search][seodescription]', $dzapp_haodai_seo['search']['seodescription'], 'text', '', 0, $codes['search']);
		showtitle('dzapp_haodai_view');
		showsetting('seotitle', 'dzapp_haodai_seo[view][seotitle]', $dzapp_haodai_seo['view']['seotitle'], 'text', '', 0, $codes['view']);
		showsetting('seokeywords', 'dzapp_haodai_seo[view][seokeywords]', $dzapp_haodai_seo['view']['seokeywords'], 'text', '', 0, $codes['view']);
		showsetting('seodescription', 'dzapp_haodai_seo[view][seodescription]', $dzapp_haodai_seo['view']['seodescription'], 'text', '', 0, $codes['view']);
		showtitle('dzapp_haodai_news');
		showsetting('seotitle', 'dzapp_haodai_seo[news][seotitle]', $dzapp_haodai_seo['news']['seotitle'], 'text', '', 0, $codes['news']);
		showsetting('seokeywords', 'dzapp_haodai_seo[news][seokeywords]', $dzapp_haodai_seo['news']['seokeywords'], 'text', '', 0, $codes['news']);
		showsetting('seodescription', 'dzapp_haodai_seo[news][seodescription]', $dzapp_haodai_seo['news']['seodescription'], 'text', '', 0, $codes['news']);
		showtitle('dzapp_haodai_apply');
		showsetting('seotitle', 'dzapp_haodai_seo[apply][seotitle]', $dzapp_haodai_seo['apply']['seotitle'], 'text', '', 0, $codes['apply']);
		showsetting('seokeywords', 'dzapp_haodai_seo[apply][seokeywords]', $dzapp_haodai_seo['apply']['seokeywords'], 'text', '', 0, $codes['apply']);
		showsetting('seodescription', 'dzapp_haodai_seo[apply][seodescription]', $dzapp_haodai_seo['apply']['seodescription'], 'text', '', 0, $codes['apply']);
		showtitle('dzapp_haodai_calc');
		showsetting('seotitle', 'dzapp_haodai_seo[calc][seotitle]', $dzapp_haodai_seo['calc']['seotitle'], 'text', '', 0, $codes['calc']);
		showsetting('seokeywords', 'dzapp_haodai_seo[calc][seokeywords]', $dzapp_haodai_seo['calc']['seokeywords'], 'text', '', 0, $codes['calc']);
		showsetting('seodescription', 'dzapp_haodai_seo[calc][seodescription]', $dzapp_haodai_seo['calc']['seodescription'], 'text', '', 0, $codes['calc']);
		showsubmit('seosubmit');
		showtablefooter();
		showformfooter();
	} else {
		$dzapp_haodai_seo = serialize($_GET['dzapp_haodai_seo']);
		DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('dzapp_haodai_seo', '$dzapp_haodai_seo')");
		updatecache('setting');
		cpmsg('seo_update_succeed', 'action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_seo', 'succeed');
	}
}

?>