<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class PhoneappAccount extends WeAccount {
	public function __construct($account = array()) {
		$this->menuFrame = 'phoneapp';
		$this->type = ACCOUNT_TYPE_PHONEAPP_NORMAL;
		$this->typeName = 'PHONEAPP';
		$this->typeTempalte = '-phoneapp';
	}

	public function checkIntoManage() {
		if (empty($this->account) || (!empty($this->uniaccount['account']) && $this->uniaccount['type'] != ACCOUNT_TYPE_PHONEAPP_NORMAL && !defined('IN_MODULE'))) {
			return false;
		}
		return true;
	}

	public function fetchAccountInfo() {
		$account_table = table('account');
		$account = $account_table->getPhoneappAccount($this->uniaccount['acid']);
		return $account;
	}

	public function accountDisplayUrl() {
		return url('account/display', array('type' => PHONEAPP_TYPE_SIGN));
	}
}