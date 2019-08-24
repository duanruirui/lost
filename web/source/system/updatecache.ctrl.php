<?php
/** 更新缓存
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/system/updatecache.ctrl.php : v 25c4f271f9c1 : 2015/09/16 10:49:43 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cache');
load()->model('setting');
load()->object('cloudapi');

//清空缓存分为两种，一种为重建，一种为清空。
//清空类的直接把缓存全部删除，不在一条一条的删除
if (checksubmit('submit', true)) {
	cache_updatecache();
	iajax(0, '更新缓存成功！', '');
}

template('system/updatecache');