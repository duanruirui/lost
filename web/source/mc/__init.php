<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$version_id = intval($_GPC['version_id']);
if (!empty($version_id)) {
	load()->model('miniapp');
	$version_info = miniapp_version($version_id);
}
$account_api = WeAccount::createByUniacid();
if (is_error($account_api)) {
	message($account_api['message'], url('account/display'));
}
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	itoast('', $account_api->displayUrl);
} else {
	$account_type = $account_api->menuFrame;
	define('FRAME', $account_type);
}