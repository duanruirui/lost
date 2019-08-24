<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Site;

class Multi extends \We7Table {
	protected $tableName = 'site_multi';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'title',
		'styleid',
		'site_info',
		'status',
		'bindhost',
	);
	protected $default = array(
		'uniacid' => '',
		'title' => '',
		'styleid' => '',
		'site_info' => '',
		'status' => '1',
		'bindhost' => '',
	);

	public function getById($id) {
		global $_W;
		return $this->query->where('id', $id)->where('uniacid', $_W['uniacid'])->get();
	}
}