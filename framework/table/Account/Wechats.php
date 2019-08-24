<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Account;

class Wechats extends \We7Table {
	protected $tableName = 'account_wechats';
	protected $primaryKey = 'acid';
	protected $field = array(
		'uniacid',
		'token',
		'encodingaeskey',
		'auth_refresh_token',
		'level',
		'name',
		'account',
		'original',
		'signature',
		'country',
		'province',
		'city',
		'username',
		'password',
		'lastupdate',
		'key',
		'secret',
		'styleid',
		'subscribeurl',
		'createtime',

	);
	protected $default = array(
		'uniacid' => '',
		'token' => '',
		'encodingaeskey' => '',
		'auth_refresh_token' => '',
		'level' => '0',
		'name' => '',
		'account' => '',
		'original' => '',
		'signature' => '',
		'country' => '',
		'province' => '',
		'city' => '',
		'username' => '',
		'password' => '',
		'lastupdate' => '0',
		'key' => '',
		'secret' => '',
		'styleid' => '1',
		'subscribeurl' => '',
		'createtime' => '',

	);
}