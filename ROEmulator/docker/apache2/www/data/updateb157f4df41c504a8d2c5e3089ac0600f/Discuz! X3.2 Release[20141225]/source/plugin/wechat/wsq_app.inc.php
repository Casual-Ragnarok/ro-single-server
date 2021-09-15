<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsq_app.inc.php 35159 2014-12-23 02:22:03Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

showtableheader(lang('plugin/wechat', 'wsq_viewapp_local'));
echo '<tr><td style="line-height:30px">'.lang('plugin/wechat', 'wsq_viewapp_local_comment').'</td></tr>';
showtablefooter();

showtableheader(lang('plugin/wechat', 'wsq_viewapp_online'));
echo '<tr><td style="line-height:30px">'.lang('plugin/wechat', 'wsq_viewapp_online_comment').'</td></tr>';
showtablefooter();

?>