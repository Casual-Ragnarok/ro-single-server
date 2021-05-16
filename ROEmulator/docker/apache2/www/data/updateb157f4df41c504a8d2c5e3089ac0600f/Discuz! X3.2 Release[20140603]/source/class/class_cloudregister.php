<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_cloudregister.php 33799 2013-08-15 02:29:22Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Cloud');

class Cloud_Register extends Cloud_Service_Client_Cloud {

	public $appIdentifier;
	public $pluginid;
	protected $lang;

	public function __construct($appIdentifier, $pluginid, $startStep, $debug = false) {
		global $_G;
		parent::__construct($debug);

		$this->appIdentifier = $appIdentifier;
		$this->pluginid = $pluginid;
		$step = !empty($_GET['step']) ? authcode($_GET['step'], 'DECODE', $_G['config']['security']['authkey']) : $startStep;
		$method = 'step_'.$step;
		$this->lang = lang('cloud_register');
		if(!empty($_GET['skip'])) {
			return false;
		}
		if(method_exists($this, $method)) {
			return $this->$method();
		} else {
			return false;
		}
	}

	private function _step($v) {
		global $_G;
		return rawurlencode(authcode($v, 'ENCODE', $_G['config']['security']['authkey'], 3600));
	}

	private function _msg($message, $extra = '') {
		if(defined('ADMINSCRIPT')) {
			cpmsg($message, '', 'succeed', array(), ($extra ? $extra.'<br />' : '').'<br /><a href="'.ADMINSCRIPT.'?action=plugins">'.$this->lang['back'].'</a>');
		} else {
			showmessage($message.'<br />'.$extra, $url, $values, array('alert' => 'info'));
		}
	}


	private function step_appOpenFormView() {
		global $_G;
		$submiturl = $_G['siteurl'].ADMINSCRIPT.'?action=plugins&operation=enable&pluginid='.$this->pluginid.'&formhash='.FORMHASH.'&step='.$this->_step('appOpenWithRegister');
		$fromurl = $_G['siteurl'].ADMINSCRIPT.'?action=plugins';
		$data = $this->appOpenFormView($this->appIdentifier, $submiturl, $fromurl);
		if($data) {
			echo $data;
			exit;
		}
		$this->step_appOpenWithRegister();
	}

	private function step_appOpenWithRegister() {
		global $_G;
		$return = $this->appOpenWithRegister($this->appIdentifier, $_GET['extra']);
		if($return['errCode']) {
			if($return['errCode'] == '1000') {
				$this->step_bindQQ();
			} else {
				$this->_msg($return['errMessage']);
			}
		}
		if($return['result']) {
			if($return['result']['sId'] && $return['result']['sKey']) {
				C::t('common_setting')->update_batch(array('my_siteid' => $return['result']['sId'], 'my_sitekey' => $return['result']['sKey']));
				updatecache('setting');
			}
			if($return['result']['needBindQQ']) {
				$this->step_bindQQ();
			}
		}
		$this->step_over();
	}

	private function step_bindQQ() {
		global $_G;
		$fromurl = $_G['siteurl'].ADMINSCRIPT.'?frame=no&action=plugins&operation=enable&pluginid='.$this->pluginid.'&formhash='.FORMHASH.'&step='.$this->_step('bindQQBack');
		$url = $this->bindQQ($this->appIdentifier, $fromurl, $_GET['extra']);
		$script = '<script type="text/javascript">function BindQQ() {var url = \''.$url.'\';var left = (window.screen.width - 700) / 2;var top = (window.screen.height - 460) / 2;var A=window.open(url, \'TencentLogin\', \'left=\'+left+\',top=\'+top+\',width=700,height=460,menubar=0,scrollbars=1,resizable=1,status=1,titlebar=0,toolbar=0,scrollbars=0,location=1\');}</script>';
		$this->_msg($this->lang['bindqq'], $script.$this->lang['bindqq_link']);
	}

	private function step_bindQQBack() {
		$stepurl = $_G['siteurl'].ADMINSCRIPT.'?action=plugins&operation=enable&pluginid='.$this->pluginid.'&formhash='.FORMHASH.'&step='.$this->_step('appOpenWithRegister');
		if($_GET['extra']) {
			$utilService = Cloud::loadClass('Service_Util');
			$stepurl .= '&'.$utilService->httpBuildQuery(array('extra' => $_GET['extra']), '', '&');
		}
		echo '<script type="text/javascript">if(window.opener) {window.opener.location.href=\''.$stepurl.'\';};window.close();</script>';
		exit;
	}

	private function step_over() {
		return true;
	}

	private function step_appCloseReasonsView() {
		$submiturl = $_G['siteurl'].ADMINSCRIPT.'?action=plugins&operation=disable&pluginid='.$this->pluginid.'&formhash='.FORMHASH.'&step='.$this->_step('appClose');
		$fromurl = $_G['siteurl'].ADMINSCRIPT.'?action=plugins';
		$data = $this->appCloseReasonsView($this->appIdentifier, $submiturl, $fromurl);
		if($data) {
			echo $data;
			exit;
		}
		$this->step_over();
	}

	private function step_appClose() {
		$return = $this->appClose($this->appIdentifier);
		if($return['errCode']) {
			$this->_msg($return['errMessage']);
		}
		$this->step_over();
	}

}

?>