<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile_extends_check.php 31964 2012-10-26 07:27:36Z zhangjie $
 */

class mobile_api {

	var $variable = array();

	function common() {


		$this->variable = array(
			'extends' => array(
				'extendversion' => '1',
				'extendlist' => array(
					array(
						'identifier' => 'dz_newthread',
						'name' => lang('plugin/mobile', 'mobile_extend_newthread'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_newreply',
						'name' => lang('plugin/mobile', 'mobile_extend_newreply'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_digest',
						'name' => lang('plugin/mobile', 'mobile_extend_digest'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_newpic',
						'name' => lang('plugin/mobile', 'mobile_extend_newpic'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
				),
			)
		);
	}

	function output() {
		mobile_core::result(mobile_core::variable($this->variable));
	}
}
?>