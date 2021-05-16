<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db_driver_mysql_slave.php 31912 2012-10-24 04:10:37Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class db_driver_mysql_slave extends db_driver_mysql
{

	var $slaveid = null;

	var $slavequery = 0;

        var $slaveexcept = false;

        var $excepttables = array();

	function set_config($config) {
		parent::set_config($config);
		if(!empty ($this->config['slave'])) {
			$sid = array_rand($this->config['slave']);
			$this->slaveid = 1000 + $sid;
			$this->config[$this->slaveid] = $this->config['slave'][$sid];

			if($this->config['common']['slave_except_table']) {
				$this->excepttables = explode(',', str_replace(' ', '', $this->config['common']['slave_except_table']));
			}
			unset($this->config['slave']);
		}
	}

        function table_name($tablename) {
		if($this->slaveid && !$this->slaveexcept && $this->excepttables) {
			if(in_array($tablename, $this->excepttables)) {
				$this->slaveexcept = true;
			}
		}
		return parent::table_name($tablename);
        }

	function slave_connect() {
		if($this->slaveid) {
			if(!isset($this->link[$this->slaveid])) {
				$this->connect($this->slaveid);
			}
			$this->slavequery ++;
			$this->curlink = $this->link[$this->slaveid];
		}
	}

	function query($sql, $silent = false, $unbuffered = false) {
		if($this->slaveid && !$this->slaveexcept && strtoupper(substr($sql, 0 , 6)) == 'SELECT') {
			$this->slave_connect();
		}
		$this->slaveexcept = false;
		return parent::query($sql, $silent, $unbuffered);
	}

}
?>