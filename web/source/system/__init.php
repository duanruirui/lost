<?php
/**
 * 系统管理公共文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('site', 'menu', 'attachment', 'systeminfo', 'logs', 'filecheck', 'optimize',
	'database', 'scan', 'bom', 'ipwhitelist', 'sensitiveword', 'thirdlogin', 'oauth', 'usersetting', 'job', 'check', 'save_setting', 'scrapfile'))) {
	define('FRAME', 'site');
}
if ($action == 'platform') {
	define('FRAME', 'account_manage');
}
if ($action == 'workorder') {
	define('FRAME', 'workorder');
}
if (in_array($action, array('template', 'updatecache'))) {
	define('FRAME', 'system');
}