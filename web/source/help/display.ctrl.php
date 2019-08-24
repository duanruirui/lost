<?php
/**
 * 帮助系统
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
global $_W;
load()->model('user');
load()->model('system');

$_W['page']['title'] = '帮助系统';
$dos = array('custom', 'system');
$do = in_array($do , $dos) ? $do : 'system';

if ($do == 'custom') {
	$site_info = system_site_info();
	$wiki_id = !empty($site_info['wiki']) ? intval($site_info['wiki']) : 0;
}
template('help/display');