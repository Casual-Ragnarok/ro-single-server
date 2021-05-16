<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_myapp.php 28626 2012-03-06 09:10:25Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_myapp extends discuz_block{

	var $setting = array();

	function block_myapp(){
		$this->setting = array(
			'titlelength' => array(
				'title' => 'myapp_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'myapp_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_myapp_script_myapp');
	}

	function blockclass() {
		return array('myapp', lang('blockclass', 'blockclass_html_myapp'));
	}

	function fields() {
		return array(
				'url' => array('name' => lang('blockclass', 'blockclass_myapp_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_myapp_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'icon' => array('name' => lang('blockclass', 'blockclass_myapp_field_icon'), 'formtype' => 'text', 'datatype' => 'string'),
				'icon_small' => array('name' => lang('blockclass', 'blockclass_myapp_field_icon_small'), 'formtype' => 'text', 'datatype' => 'string'),
				'icon_abouts' => array('name' => lang('blockclass', 'blockclass_myapp_field_icon_abouts'), 'formtype' => 'text', 'datatype' => 'string'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$startrow       = !empty($parameter['startrow']) ? intval($parameter['startrow']) : '0';
		$items          = !empty($parameter['items']) ? intval($parameter['items']) : 10;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
		$bansql = $bannedids ? ' AND appid NOT IN ('.dimplode($bannedids).')' : '';

		$sql = 'SELECT * FROM '.DB::table('common_myapp')." WHERE flag>=0 $bansql ORDER BY flag DESC, displayorder LIMIT $startrow, $items";
		$query = DB::query($sql);
		while($data = DB::fetch($query)) {
			$list[] = array(
				'id' => $data['appid'],
				'idtype' => 'appid',
				'title' => cutstr(str_replace('\\\'', '&#39;', $data['appname']), $titlelength, ''),
				'url' => 'userapp.php?id='.$data['appid'],
				'pic' => '',
				'picflag' => '',
				'summary' => '',
				'fields' => array(
					'icon' => 'http://appicon.manyou.com/logos/'.$data['appid'],
					'icon_small' => 'http://appicon.manyou.com/icons/'.$data['appid'],
					'icon_abouts' => 'http://appicon.manyou.com/abouts/'.$data['appid'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}


?>