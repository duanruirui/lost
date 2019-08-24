<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/utility/__init.php : v 8ce4c4d4ca11 : 2014/10/22 10:28:06 : yanghf $
 * 
 * account 所有操作在GW界面进行
 */
define('IN_GW', true);

if ($do == 'wechat_upload') {
	define('FRAME', 'account');
}