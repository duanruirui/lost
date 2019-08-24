<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Mc;

class OauthFans extends \We7Table {
	protected $tableName = 'mc_oauth_fans';
	protected $primaryKey = 'id';
	protected $field = array(
		'oauth_openid',
		'acid',
		'uid',
		'openid',
	);
	protected $default = array(
		'oauth_openid' => '',
		'acid' => '',
		'uid' => '',
		'openid' => '',
	);

	public function searchWithoAuthopenid($oauth_openid) {
		return $this->query->where('oauth_openid', $oauth_openid);
	}

	public function searchWithAcid($acid) {
		return $this->query->where('acid', $acid);
	}
}