<?php
/**
 * 用户协议
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('post');
$do = in_array($do, $dos) ? $do : 'post';

$settings = $_W['setting']['register'];

if ($do == 'post') {
	if (checksubmit('submit')) {
		$agreement_info = trim($_GPC['agreement_info']) ? trim($_GPC['agreement_info']) : itoast('协议内容不能为空', '', 'error');
		$settings['agreement_info'] = safe_gpc_html(htmlspecialchars_decode($agreement_info));
		setting_save($settings, 'register');
		itoast('编辑成功!', '', 'success');
	}
	template('user/agreement');
}