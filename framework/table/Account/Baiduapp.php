<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Baiduapp extends \We7Table {
	protected $tableName = 'account_baiduapp';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'name',
		'appid',
		'key',
		'secret',
		'description',
	);
	protected $default = array(
		'uniacid' => '',
		'name' => '',
		'appid' => '',
		'key' => '',
		'secret' => '',
		'description' => '',
	);

	public function getAccount($acid) {
		return $this->query->where('acid', $acid)->get();
	}
}