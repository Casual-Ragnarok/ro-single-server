<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_mobile.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_mobile {


	public static function mobileoutput() {
		global $_G;
		if(!defined('TPL_DEFAULT')) {
			$content = ob_get_contents();
			ob_end_clean();
			$content = preg_replace("/href=\"(\w+\.php)(.*?)\"/e", "mobilereplace('\\1', '\\2')", $content);

			ob_start();
			$content = '<?xml version="1.0" encoding="utf-8"?>'.$content;
			if('utf-8' != CHARSET) {
				@header('Content-Type: text/html; charset=utf-8');
				$content = diconv($content, CHARSET, 'utf-8');
			}
			echo $content;
			exit();

		} elseif (defined('TPL_DEFAULT') && !$_G['cookie']['dismobilemessage'] && $_G['mobile']) {
			ob_end_clean();
			ob_start();
			$_G['forcemobilemessage'] = true;
			$query_sting_tmp = str_replace(array('&mobile=yes', 'mobile=yes'), array(''), $_SERVER['QUERY_STRING']);
			$_G['setting']['mobile']['pageurl'] = $_G['siteurl'].substr($_G['PHP_SELF'], 1).($query_sting_tmp ? '?'.$query_sting_tmp.'&mobile=no' : '?mobile=no' );
			unset($query_sting_tmp);
			dsetcookie('dismobilemessage', '1', 3600);
			showmessage('not_in_mobile');
			exit;
		}
	}

	function mobilereplace($file, $replace) {
		if(strpos($replace, 'mobile=') === false) {
			if(strpos($replace, '?') === false) {
				$replace = 'href="'.$file.$replace.'?mobile=yes"';
			} else {
				$replace = 'href="'.$file.$replace.'&mobile=yes"';
			}
			return $replace;
		} else {
			return 'href="'.$file.$replace.'"';
		}
	}
}

?>