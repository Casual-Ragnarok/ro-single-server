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

showtableheader($lang['stat_info']);
showtablerow('', array('class="td24"'), array($lang['stat_panel'], '<a href="http://yun.haodai.com" target="_blank">'.$lang['click_stat_panel'].'</a>'));
showtablerow('', array('class="td24"'), array($lang['stat_notice'], '<font color="red">'.$lang['stat_notice_content'].'</font>'));
showtablefooter();

?>