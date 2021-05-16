<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_model extends discuz_base
{

	public $app;

	public function __construct() {
		parent::__construct();
	}

	public function config($name) {
		return getglobal('config/'.$name);
	}

	public function setting($name) {
		return getglobal('setting/'.$name);
	}

	public function table($name) {
		return C::t($name);
	}

	public function cache($name) {
		if (!isset($this->app->var['cache'][$name])) {
			loadcache($name);
		}
		if($this->app->var['cache'][$name] === null) {
			return null;
		} else {
			return getglobal('cache/'.$name);
		}
	}

}
?>