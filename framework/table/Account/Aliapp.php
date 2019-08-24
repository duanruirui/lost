<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Aliapp extends \We7Table {
	protected $tableName = 'account_aliapp';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'level',
		'name',
		'appid',
	);
	protected $default = array(
		'uniacid' => '',
		'level' => 0,
		'name' => '',
		'appid' => '',
	);

	public function getAccount($acid) {
		return $this->query->where('acid', $acid)->get();
	}
}