<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: soso.class.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_pcmgr_url_safeguard{
	function _include_js(){
		global $_G;
		$jsscript = '<script src="http://pc1.gtimg.com/js/jquery-1.4.4.min.js" type="text/javascript"></script>';
		$jsscript .= '<script type="text/javascript">jQuery.noConflict();</script>';
		$jsscript .= "<script type=\"text/javascript\">(function(d){j=d.createElement('script');j.src='//openapi.guanjia.qq.com/fcgi-bin/getdzjs?cmd=urlquery_" . $_G['config']['output']['charset'] . "_" . $_G['config']['output']['language'] . "';j.setAttribute('ime-cfg','lt=2');d.getElementsByTagName('head')[0].appendChild(j)})(document)</script>";

		$jsscript .= '<link rel="stylesheet" type="text/css" href="http://s.pc.qq.com/discuz/css/style.css" />';
		return $jsscript;
	}
}

class plugin_pcmgr_url_safeguard_forum extends plugin_pcmgr_url_safeguard {
	function viewthread_top_output($template = array()) {
		return $this->_include_js();
	}

}