<?php
/**
 * DZAPP Haodai Upgrade Process
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 * @LastModTime 2014/5/21 17:03
 */


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) exit('Access Denied!');
$request_url = str_replace('&accept=1','',$_SERVER['QUERY_STRING']);
if(!$_GET['accept']){
	cpmsg($installlang['rewrite_tip'], "{$request_url}&accept=1", 'form', array(), '', TRUE, $_G['siteurl']."admin.php?{$request_url}&accept=1");
}

$finish = TRUE;
?>