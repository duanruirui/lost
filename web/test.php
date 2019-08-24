<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/index.php : v 14b9a4299104 : 2015/09/11 10:44:21 : yanghf $
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
load()->web('common');
load()->web('template');
load()->func('communication');
load()->model('cache');
load()->model('cloud');
load()->classs('coupon');
load()->func('file');
load()->func('db');
load()->web('tpl');
load()->classs('cloudapi');
require_once IA_ROOT . '/framework/library/pkcs7/pkcs7Encoder.php';


$cloudapi = new CloudApi();
$result = $cloudapi->post('model_info', '', array('module' => 'ceshi_1221'));
print_r($result);exit;
$encrypt_data = 'BppjtZycpQoTnm6SYDrvBYhDc8kWd/yookJIr49DnLNtL3EHfBzIv6buVeZT6GW8LoX0P5lpEBsadMtw91BXAfchJAztHN42lQkkBqaIm3fT+R/me8n4jt47PZvNfxVqSXl2wxTHf2UmDCeNgK4C1G6bsXXJCfYhmoY6MsuaENvegU6/rA79H3/5O0MZEs5c6+pk4sMWPjTXdE/P03oRRDmO7FzLSagusqanIBCNpvrok9NSaDNyeGhjvu2OwS2tOVmCbotp2pe4KL/mAdzY/UGiAZNrNLY5CRx81eI+EWqhyRI0rAdygbiKyq5YeN8mU5vnTtbutCBk/ddsotNyTSoaj7n7sMaXpdBTigti3dmYMgI4nIImcv78qeRwjyzKb+zeK+scxfSz45z3igm02xsMgXp01JStxUBKMR048qySpYs/aRvXFMu5k/dLAl7x0IoUdTAkOlym2MlGMb74QR48AYyyS4GjvhCjRuJutGO3547+IMVAHsOetkPGJqzkh7BFSUAGRoOiHrChi2r8knmWCCI5+JqA7RzdweCXfhat8C03BtDGslrQvLRAtmaawi7qwJhKrG61zHXeI9Htik3XwRBL0rOaytE1eztikfJsfGkuCy2S7XpV+l2MiEtnbQk+YrM2g36XAC3rr0zMXtB9hQEfFxSwiFhF7nRp/vDtf2VkWtO9LM5dRcp7Hxf01b8Kpi5jIvuktWVRmL9yAOFy5Hh7ogVk7BbnLFXUgSgDDN9S5PhR0XDoJcgYFhb9Ugm1RKm0djrTCXMig8KYllnAKlfn9Ifv77ryAy5a4LgfNo4vKdDqLkILFZc2Q8Utc9emRM8+MI2ZjdgaWK734hh/qQ8uFRGdKDlzwwI8uR0mIlLh7qcVoo9GfDvmoWCjLdyxNZyTppx9/FbKJODjDw6zz3U8BevHZeaf1ekfNk2Y15t45LXL+PxPnQ+KHEn69Bz2BgrAV6ywRRx2J/kIvobhCWWk2DflWFjfSl8hSnRTUC1VSmDW0lU2iAwGCsbefjOdGaLBfEnebovzIwtQCelBI+SKX5IpHcANEAxPsVp23j/1IZO1UXEkZDTk8GgB';
$key = md5('k550y1ihjp3pntlfgytw0lyo5534to0w');
$pc = new Prpcrypt($key);
$result = $pc->decrypt(base64_decode($encrypt_data));
print_r($result);exit;

$result = pdo_getall('account', array('type !=' => ''), array('acid', 'uniacid'), 'uid', ' acid  xxxxx desc, uniacid desc', '   1    ,5');
print_r($result);exit;
