<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$account_api = WeAccount::createByUniacid();
if ($action == 'post-step') {
	define('FRAME', '');
} else {
	define('FRAME', $account_api->menuFrame);
}