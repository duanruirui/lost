<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Core;

class Settings extends \We7Table {
	protected $tableName = 'core_settings';
	protected $primaryKey = 'key';
	protected $field = array(
		'value',
	);
	protected $default = array(
		'value' => '',
	);
}