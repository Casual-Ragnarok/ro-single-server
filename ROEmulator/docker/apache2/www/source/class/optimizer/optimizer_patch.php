<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_patch.php 31344 2012-08-15 04:01:32Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_patch {

	public function __construct() {

	}

	public function check() {
		$discuz_patch = new discuz_patch();
		$discuz_patch->check_patch();

		$discuz_patch = new discuz_patch();
		$patchnum = 0;
		$patchnotice = $discuz_patch->fetch_patch_notice();
		if($patchnotice['data']) {
			foreach($patchnotice['data'] as $patch) {
				if($patch['status'] <= 0) {
					$patchnum++;
				}
			}
		}
		if($patchnum) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_patch_have', array('patchnum' => $patchnum)));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_patch_check_safe'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=patch');
	}
}

?>