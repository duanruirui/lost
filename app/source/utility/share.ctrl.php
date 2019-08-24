<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/app/source/utility/share.ctrl.php : v 9db086b9ae3d : 2014/11/06 06:04:46 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');
$ret = array();
//必须要有的参数
if(empty($_GPC['module']) || empty($_GPC['sign']) || empty($_W['uniacid']) || empty($_GPC['action'])) {
	return false;
}

$name = trim($_GPC['module']);
$site = WeUtility::createModuleSite($name);
//creditOperate方法需要返回积分上限和当前操作所需的积分数
$return = $site->creditOperate($_GPC['sign'], $_GPC['action']);


if(empty($return)) {
	return false;
} elseif(empty($return['credit_total'])) {
	$ret['result'] = 'total-miss';
	moduleInit($_GPC['module'], $ret);
}

$ret = array();
//判断积分是否达到上限
$total = pdo_fetchcolumn('SELECT SUM(credit_value) FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND module = :module AND sign = :sign', array(':uniacid' => $_W['uniacid'], ':module' => $_GPC['module'], ':sign' => $_GPC['sign']));
$credit_total = intval($return['credit_total']);
if($total >= $credit_total) {
	$ret['result'] = 'total-limit';
	moduleInit($_GPC['module'], $ret);
}

if(empty($_GPC['uid'])) {
	$uid = $_W['member']['uid'];
} else {
	$uid = intval($_GPC['uid']);
}

//判断用户是否已经加过积分
if(!empty($uid)) {
	$user = pdo_fetchcolumn('SELECT uid FROM ' . tablename('mc_members'). ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
	if(empty($user)) {
		$ret['result'] = 'uid-error';
		moduleInit($_GPC['module'], $ret);
	}
	if(!empty($_GPC['action'])) {
		$sql = 'SELECT id FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND touid = :touid AND fromuid = :fromuid AND module = :module AND sign = :sign AND action = :action';
		$parm = array(':uniacid' => $_W['uniacid'], ':touid' => $uid, ':fromuid' => 0, ':module' => $_GPC['module'], ':sign' => $_GPC['sign'], ':action' => $_GPC['action']);
		$is_add = pdo_fetchcolumn($sql, $parm);
		if(empty($is_add)) {
			$creditbehaviors = pdo_fetchcolumn('SELECT creditbehaviors FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
			$creditbehaviors = iunserializer($creditbehaviors) ? iunserializer($creditbehaviors) : array();
			if(empty($creditbehaviors['activity'])) {
				$ret['result'] = 'creditset-miss';
				moduleInit($_GPC['module'], $ret);
			} else {
				$credittype = $creditbehaviors['activity'];
			}

			$data = array(
				'uniacid' => $_W['uniacid'],
				'touid' => $uid,
				'fromuid' => 0,
				'module' => $_GPC['module'],
				'sign' => $_GPC['sign'],
				'action' => $_GPC['action'],
				'credit_value' => intval($return['credit_value']),
				'createtime' => TIMESTAMP
			);
			pdo_insert('mc_handsel', $data);
			$note = empty($_GPC['note']) ? '系统赠送积分' : $_GPC['note'];
			$log = array(
				'uid' => $uid,
				'credittype' => $credittype,
				'uniacid' => $_W['uniacid'],
				'num' => intval($return['credit_value']),
				'createtime' => TIMESTAMP,
				'operator' => 0,
				'remark' => $note
			);
			$credit_value = intval($return['credit_value']);
			mc_credit_update($uid, $credittype, $credit_value, $log);
			$ret['result'] = 'success';
			moduleInit($_GPC['module'], $ret);
		} else {
			$ret['result'] = 'repeat';
			moduleInit($_GPC['module'], $ret);
		}
	} else {
		$ret['result'] = 'action-miss';
		moduleInit($_GPC['module'], $ret);
	}
} else {
	$ret['result'] = 'uid-miss';
	moduleInit($_GPC['module'], $ret);
}


function moduleInit($name, $params = array()) {
	if(empty($name)) {
		return false;
	}
	$site = WeUtility::createModuleSite($name);
	if(!is_error($site)) {
		$method = 'shareResult';
		if(method_exists($site, $method)) {
			$site->$method($params);
			exit('success');
		}
	}
}


