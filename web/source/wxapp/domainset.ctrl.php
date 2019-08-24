<?php
/**
 * 小程序域名设置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniapp');
load()->classs('cloudapi');
load()->classs('uploadedfile');
load()->func('file');

$dos = array('display');
$do = in_array($do, $dos) ? $do : 'display';

$wxapp_info = miniapp_fetch($_W['uniacid']);

// 是否是模块打包小程序
$is_module_wxapp = false;
if (!empty($version_id)) {
	$is_single_module_wxapp = $version_info['type'] == WXAPP_CREATE_MODULE; //是否单应用打包
}

if ($do == 'display') {
	$appurl = $_W['siteroot'].'app/index.php';
	$uniacid = 0;
	if ($version_info) {
		$wxapp = pdo_get('account_wxapp', array('uniacid' => $version_info['uniacid']));
		if ($wxapp && !empty($wxapp['appdomain'])) {
			$appurl = $wxapp['appdomain'];
		}
		if (!starts_with($appurl, 'https')) { //不是https 开头强制改为https开头
			$appurl = str_replace('http', 'https', $appurl);
		}
		$uniacid = $version_info['uniacid'];
	}
	if ($_W['ispost']) {
		$appurl = safe_gpc_url($_GPC['appurl'], false);

		if (!starts_with($appurl, 'https')) {
			itoast('域名必须以https开头');
			return;
		}

		$file = $_FILES['file'];
		if (!empty($file['name']) && $file['error'] == 0) {
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
			if (strtolower($ext) == 'txt') {
				$file['name'] = parse_path($file['name']);
				file_move($file['tmp_name'], IA_ROOT . '/' . $file['name']);
			}
		}
		if ($version_info) {
			$update = pdo_update('account_wxapp', array('appdomain' => $appurl), array('uniacid' => $uniacid));
			itoast('更新成功'); //新 旧域名一样 返回$update 为0
		}
	}

	template('wxapp/domainset');
}