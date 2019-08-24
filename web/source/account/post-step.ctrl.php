<?php
/**
 * 手动添加公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');
load()->model('module');
load()->model('user');
load()->model('account');
load()->classs('weixin.platform');

$uniacid = intval($_GPC['uniacid']);
$step = intval($_GPC['step']) ? intval($_GPC['step']) : 1;
//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
$user_create_account_info = permission_user_account_num();

if($step == 1) {
	if ($user_create_account_info['account_limit'] <= 0 && !$_W['isfounder']) {
		$authurl = "javascript:alert('创建公众号已达上限！');";
	}

	if (empty($authurl) && !empty($_W['setting']['platform']['authstate'])) {
		$account_platform = new WeixinPlatform();
		$authurl = $account_platform->getAuthLoginUrl();
	}
} elseif ($step == 2) {
	//新建平台基本信息. 新路由 account/create/base_info  &sign=account
} elseif ($step == 3) {
	//新建平台分配权限. 新路由 account/create/account_modules &uniacid=
} elseif($step == 4) {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	$uni_account = pdo_get('uni_account', array('uniacid' => $uniacid));
	if (empty($uni_account)) {
		itoast('非法访问');
	}
	$owner_info = account_owner($uniacid);
	if (!(user_is_founder($_W['uid'], true) || $_W['uid'] == $owner_info['uid'])) {
		itoast('非法访问');
	}
	$account = account_fetch($uni_account['default_acid']);
}
template('account/post-step');