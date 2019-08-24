<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Account extends \We7Table {
	protected $tableName = 'account';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'hash',
		'type',
		'isconnect',
		'isdeleted',
		'endtime',

	);
	protected $default = array(
		'uniacid' => '0',
		'hash' => '',
		'type' => '1',
		'isconnect' => '',
		'isdeleted' => '0',
		'endtime' => '0',

	);
}