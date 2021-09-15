<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Common.php 25828 2011-11-23 10:50:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Common extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onCommonSetConfig($data) {
		$settings = array();
		if (is_array($data) && $data) {
			foreach($data as $key => $val) {
				if (substr($key, 0, 3) != 'my_') {
					continue;
				}
				$settings[$key] = $val;
			}
			if ($settings) {
				C::t('common_setting')->update_batch($settings);
				require_once DISCUZ_ROOT . './source/function/function_cache.php';
				updatecache('setting');
				return true;
			}
		}
		return false;
	}

	public function onCommonGetConfig($keys) {
		global $_G;
		$confs = array();

		foreach ($keys as $key) {
			if ($key && $_G['setting']) {
				$setting = $_G['setting'];
				if ($key == 'search' && is_array($setting['search'])) {
					$conf = array();
					foreach ($setting['search'] as $app => $v) {
						$conf[$app] = array(
							'status' => $v['status'] ? true : false,
							'interval' => $v['searchctrl'],
							'frequence' => $v['maxspm'],
							'maxResults' => $v['maxsearchresults']
						);
					}
					$confs[$key] = $conf;
					continue;
				}

				if ($key == 'rewrite') {
					$conf = array();
					if ($setting['rewritestatus'] && $setting['rewriterule']) {
						$conf['compatible'] = $setting['rewritecompatible'] ? true : false;
						foreach($setting['rewriterule'] as $mod => $rule) {
							$conf['modules'][$mod]['rule'] = $rule;
							if (in_array($mod, $setting['rewritestatus'])) {
								$conf['modules'][$mod]['status'] = true;
							} else {
								$conf['modules'][$mod]['status'] = false;
							}
						}
					}
					$confs[$key] = $conf;
					continue;
				}
			}
		}

		return $confs;
	}

	public function onCommonGetNavs($type = '') {
		Cloud::loadFile('Service_SearchHelper');
		$navtype = null;
		switch($type) {
			case 'footer':
				$navtype = 1;
				break;
			case 'space':
				$navtype = 2;
				break;
			case 'my':
				$navtype = 3;
				break;
			case 'header':
				$navtype = 0;
				break;
		}
		$navs = $subNavs = array();
		foreach(C::t('common_nav')->fetch_all_by_navtype($navtype) as $nav) {
			if (!$nav['parentid']) {
				$navs[$nav['id']] = Cloud_Service_SearchHelper::convertNav($nav);
			} else {
				$subNavs[$nav['id']] = $nav;
			}
		}
		foreach($subNavs as $k => $v) {
			$navs[$v['parentid']]['navs'][$v['id']] = Cloud_Service_SearchHelper::convertNav($v);
		}
		return $navs;
	}

}