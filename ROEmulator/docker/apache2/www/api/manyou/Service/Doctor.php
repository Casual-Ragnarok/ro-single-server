<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Doctor.php 29521 2012-04-17 09:24:42Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Doctor {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function checkDNS($url) {
		if (empty($url)) {
			return false;
		}
		$matches = parse_url($url);
		$host = !empty($matches['host']) ? $matches['host'] : $matches['path'];
		if (!$host) {
			return false;
		}
		$ip = gethostbyname($host);
		if ($ip == $host) {
			return false;
		} else {
			return $ip;
		}
	}

	public function checkDNSResult($type = 1, $setting = array()) {

		switch ($type) {
			case 1:
				$setIP = $setting['cloud_api_ip'] ? cplang('cloud_doctor_setting_ip') . $setting['cloud_api_ip'] : '';
				$host = 'api.discuz.qq.com';
				break;
			case 2:
				$setIP = $setting['my_ip'] ? cplang('cloud_doctor_setting_ip') . $setting['my_ip'] : '';
				$host = 'api.manyou.com';
				break;
			case 3:
				$setIP = $setting['connect_api_ip'] ? cplang('cloud_doctor_setting_ip') . $setting['connect_api_ip'] : '';
				$host = 'openapi.qzone.qq.com';
				break;
		}

		$ip = $this->checkDNS($host);
		if ($ip) {
			return sprintf(cplang('cloud_doctor_dns_success'), $host, $ip, $setIP, ADMINSCRIPT);
		} else {
			return sprintf(cplang('cloud_doctor_dns_failure'), $host, $setIP, ADMINSCRIPT);
		}
	}

	public function showPlugins() {
		global $_G;
		$plugins = array();
		$identifiers = array('qqconnect', 'cloudstat', 'soso_smilies', 'cloudsearch', 'qqgroup', 'security', 'xf_storage');
		$plugins = C::t('common_plugin')->fetch_all_by_identifier($identifiers);

		if ($plugins && count($plugins) == count($identifiers)) {
			$systemPluginStatus = cplang('cloud_doctor_result_success').' '.cplang('available').' '.cplang('cloud_doctor_system_plugin_list');
		} else {
			$initsysFormHash = substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_G['uid'].$_G['authkey']), 8, 8);
			$systemPluginStatus = cplang('cloud_doctor_result_failure') . cplang('cloud_doctor_system_plugin_status_false', array('formhash' => $initsysFormHash));
		}
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_system_plugin_status').'</strong>',
			$systemPluginStatus
		));
		foreach($plugins as $plugin) {
			$moduleStatus = cplang('cloud_doctor_plugin_module_error');
			$plugin['modules'] = @dunserialize($plugin['modules']);
			if(is_array($plugin['modules']) && $plugin['modules']) {
				$moduleStatus = '';
			}

			showtablerow('', array('class="td24"'), array(
				'<strong>'.$plugin['name'].'</strong>',
				cplang('version').' '.$plugin['version'].' '.$moduleStatus
			));
		}
	}

	public function showCloudStatus($cloudStatus) {

		$cloudStatus = intval($cloudStatus);

		return cplang('cloud_doctor_status_' . $cloudStatus);
	}

	public function testAPI($type = 1, $ip = '', $setting = array()) {

		switch ($type) {
		case 1:
			$url = 'http://api.discuz.qq.com/site.php';
			$result = dfsockopen($url, 0, '', '', false, $ip ? $ip : $setting['cloud_api_ip'], 5);
			break;
		case 2:
			$url = 'http://api.manyou.com/uchome.php';
			$result = dfsockopen($url, 0, 'action=siteRefresh', '', false, $ip ? $ip : $setting['my_ip'], 5);
			break;
		case 3:
			$url = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';
			$result = dfsockopen($url, 0, '', '', false, $ip ? $ip : $setting['connect_api_ip'], 5);
			if($result) {
				return true;
			}
			break;
		}

		$result = trim($result);

		if(!$result) {
			return false;
		}

		$result = @dunserialize($result);
		if(!$result) {
			return false;
		}
		return true;
	}

	public function showTestJS($type = 1, $ip = '') {
		$html = sprintf('<div id="_doctor_apitest_%1$s_%2$s"></div><script type="text/javascript">ajaxget("%3$s?action=cloud&operation=doctor&op=apitest&api_type=%1$s&api_ip=%2$s", "_doctor_apitest_%1$s_%2$s");</script>', $type, $ip, ADMINSCRIPT);
		return $html;
	}

	public function showAPIJS() {
		echo '<script type="text/javascript" src="static/image/admincp/cloud/cloud.js"></script> <script type="text/javascript" src="http://cp.discuz.qq.com/cloud/apiIp" charset="utf-8"></script>';
	}

	public function showSiteTestAPIJS($position = 'doctor') {
		global $_G;
		require_once DISCUZ_ROOT.'./source/discuz_version.php';

		echo '<script type="text/javascript" src="http://cp.discuz.qq.com/cloud/siteTest?s_url=' . urlencode($_G['siteurl']) . '&charset=' . CHARSET . '&productVersion=' . DISCUZ_VERSION . '&position=' . $position . '" charset="utf-8"></script>';
	}

	public function showCloudDoctorJS($position = 'doctor') {
		global $_G;

		require_once DISCUZ_ROOT.'./source/discuz_version.php';
		$url = $_G['siteurl'];
		$charset = CHARSET;
		$version = DISCUZ_VERSION;
		$rand = rand();
		$time = time();
		$output = <<<EOF
		<script type="text/javascript">
			var discuzUrl = '$url';
			var discuzCharset = '$charset';
			var productVersion = '$version';
			var checkPosition = '$position';
			var discuzTime = '$time';
		</script>
		<script type="text/javascript" src="http://discuz.gtimg.cn/cloud/scripts/doctor.js?v=$rand" charset="utf-8"></script>
EOF;
		echo $output;
	}

	public function fixGuestGroup($name) {
		$connect = C::t('common_setting')->fetch('connect', true);
		$guestGroupId = $connect['guest_groupid'];
		$group = C::t('common_usergroup')->fetch($guestGroupId);
		if ($group) {
			return true;
		}
		$userGroupData = array(
			'type' => 'special',
			'grouptitle' => $name,
			'allowvisit' => 1,
			'color' => '',
			'stars' => '',
		);
		$newGroupId = C::t('common_usergroup')->insert($userGroupData, true);

		$dataField = array(
			'groupid' => $newGroupId,
			'allowsearch' => 2,
			'readaccess' => 1,
			'allowgetattach' => 1,
			'allowgetimage' => 1,
		);
		C::t('common_usergroup_field')->insert($dataField);

		$connect['guest_groupid'] = $newGroupId;
		C::t('common_setting')->update('connect', serialize($connect));
		updatecache('usergroups');
	}

	public function checkGuestGroup() {
		$connect = C::t('common_setting')->fetch('connect', true);
		$guestGroupId = $connect['guest_groupid'];
		$group = C::t('common_usergroup')->fetch($guestGroupId);
		if ($group) {
			return true;
		}

		return false;
	}
}