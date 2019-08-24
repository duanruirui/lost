<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($do == 'system_home') {
	define('FRAME', 'welcome');
}

if ($do == 'system') {
	if (safe_gpc_string($_GPC['page']) == 'home') {
		define('FRAME', 'welcome');
	} else {
		define('FRAME', 'system');
	}
}

if (in_array($do, array('platform','ext')) || empty($do)) {
	define('FRAME', 'account');
}