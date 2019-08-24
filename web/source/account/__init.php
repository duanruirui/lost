<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if ($action != 'display') {
	define('FRAME', 'account_manage');
} else {
	if ($action == 'display') {
		define('FRAME', 'platform');
	} else {
		//高版本php引用未定义常量报错，此处定义空值兼容高版本
		define('FRAME', '');
	}
}

if ($controller == 'account' && $action == 'manage') {
	if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
		define('ACTIVE_FRAME_URL', url('account/manage/display', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
	}
}

$account_all_type = uni_account_type();
$account_all_type_sign = uni_account_type_sign();
$account_param = WeAccount::create(array('type' => $_GPC['account_type']));
define('ACCOUNT_TYPE', $account_param->type);
define('TYPE_SIGN', $account_param->typeSign);
define('ACCOUNT_TYPE_NAME', $account_param->typeName);
define('ACCOUNT_TYPE_TEMPLATE', $account_param->typeTempalte);