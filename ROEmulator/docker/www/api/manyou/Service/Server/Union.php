<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Union.php 25702 2011-11-18 04:28:41Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Union extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onUnionAddAdvs($advs) {
		$result = array();
		if (is_array($advs)) {
			foreach($advs as $advid => $adv) {
				$data = $this->_addAdv($adv);
				if($data === true) {
					$result['succeed'][$advid] = $advid;
				} else {
					$result['failed'][$advid] = $data;
				}
			}

			require_once libfile('function/cache');
			updatecache('advs');
			updatecache('setting');
		} else {
			$result['error'] = 'no adv';
		}

		return $result;
	}

	protected function _addAdv($adv) {
		global $_G;

		foreach($adv as $k => $v) {
			$_GET[''.$k] = $v;
		}

		$type = $_GET['type'];
		$advlibfile = libfile('adv/'.$type, 'class');
		if (file_exists($advlibfile)) {
			require_once $advlibfile;
		} else {
			return 'err_1';
		}
		$advclass = 'adv_'.$type;
		$advclass = new $advclass;
		$advnew = $_GET['advnew'];

		$parameters = !empty($_GET['parameters']) ? $_GET['parameters'] : array();
		if(@in_array('custom', $advnew['targets'])) {
			$targetcustom = explode(',', $advnew['targetcustom']);
			$advnew['targets'] = array_merge($advnew['targets'], $targetcustom);
		}
		$advclass->setsetting($advnew, $parameters);

		$advnew['starttime'] = $advnew['starttime'] ? strtotime($advnew['starttime']) : 0;
		$advnew['endtime'] = $advnew['endtime'] ? strtotime($advnew['endtime']) : 0;

		if(!$advnew['title']) {
			return 'err_2';
		} elseif(strlen($advnew['title']) > 50) {
			return 'err_3';
		} elseif(!$advnew['style']) {
			return 'err_4';
		} elseif(!$advnew['targets']) {
			return 'err_5';
		} elseif($advnew['endtime'] && ($advnew['endtime'] <= TIMESTAMP || $advnew['endtime'] <= $advnew['starttime'])) {
			return 'err_6';
		} elseif(($advnew['style'] == 'code' && !$advnew['code']['html'])
			|| ($advnew['style'] == 'text' && (!$advnew['text']['title'] || !$advnew['text']['link']))
			|| ($advnew['style'] == 'image' && (!$_FILES['advnewimage'] && !$_GET['advnewimage'] || !$advnew['image']['link']))
			|| ($advnew['style'] == 'flash' && (!$_FILES['advnewflash'] && !$_GET['advnewflash'] || !$advnew['flash']['width'] || !$advnew['flash']['height']))) {
			return 'err_7';
		}

		$advid = C::t('common_advertisement')->insert(array('available' => 1, 'type' => $type), true);

		if($advnew['style'] == 'image' || $advnew['style'] == 'flash') {
			$advnew[$advnew['style']]['url'] = $_GET['advnew'.$advnew['style']];
		}

		$advnew['displayorder'] = isset($advnew['displayorder']) ? implode("\t", $advnew['displayorder']) : '';
		$advnew['code'] = $this->_encodeAdvCode($advnew);

		$advnew['parameters'] = serialize(array_merge(is_array($parameters) ? $parameters : array(), array('style' => $advnew['style']), $advnew['style'] == 'code' ? array() : $advnew[$advnew['style']], array('html' => $advnew['code']), array('displayorder' => $advnew['displayorder'])));

		C::t('common_advertisement')->update($advid, array(
			'title' => $advnew['title'],
			'targets' => $advnew['targets'],
			'parameters' => $advnew['parameters'],
			'code' => $advnew['code'],
			'starttime' => $advnew['starttime'],
			'endtime' => $advnew['endtime']
		));
		return true;
	}

	protected function _encodeAdvCode($advnew) {
		switch($advnew['style']) {
			case 'code':
				$advnew['code'] = $advnew['code']['html'];
				break;
			case 'text':
				$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
				break;
			case 'image':
				$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
				break;
			case 'flash':
				$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash" wmode="transparent"></embed>';
				break;
		}
		return $advnew['code'];
	}

}