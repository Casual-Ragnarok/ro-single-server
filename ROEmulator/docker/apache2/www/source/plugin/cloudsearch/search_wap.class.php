<?php
/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_wap.class.php 34044 2013-09-25 02:55:02Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_cloudsearch {

	public function mobileplugin_cloudsearch() {
		global $_G, $searchparams;

		$cloudAppService = Cloud::loadClass('Service_App');
		$this->allow = $cloudAppService->getCloudAppStatus('search');
		if($this->allow) {
			include_once template('cloudsearch:module');
			if(!function_exists('tpl_global_header_mobile')) {
				$this->allow = false;
				return;
			}

			if (!$searchparams) {
				$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
				$searchparams = $searchHelper->makeSearchSignUrl();
			}
		}
	}

	function global_header_mobile() {

		if (!$this->allow) {
			return;
		}

		global $_G, $searchparams;

		$srchotquery = '';
		if(!empty($searchparams['params'])) {
			foreach($searchparams['params'] as $key => $value) {
				$srchotquery .= '&' . $key . '=' . rawurlencode($value);
			}
		}

		return tpl_global_header_mobile($searchparams, $srchotquery);
	}

}