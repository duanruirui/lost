<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Message;

class NoticeLog extends \We7Table {
	protected $tableName = 'message_notice_log';
	protected $primaryKey = 'id';
	protected $field = array(
		'message',
		'is_read',
		'uid',
		'sign',
		'type',
		'status',
		'create_time',
		'end_time',
		'url',
	);
	protected $default = array(
		'message' => '',
		'is_read' => 1,
		'uid' => 0,
		'sign' => '',
		'type' => 0,
		'status' => 0,
		'create_time' => 0,
		'end_time' => 0,
		'url' => '',
	);

	public function messageList($type = '') {
		global $_W;

		if (!user_is_founder($_W['uid']) || user_is_vice_founder($_W['uid'])) {
			$this->query->where('uid', $_W['uid']);
		}

		if (user_is_founder($_W['uid']) && !user_is_vice_founder() && empty($type)) {
			$this->query->where('type !=', array(MESSAGE_USER_EXPIRE_TYPE));
		}

		return $this->query->from($this->tableName)->orderby('id', 'DESC')->getall();

	}

	public function messageRecord() {
		return $this->query->from($this->tableName)->orderby('id', 'DESC')->get();
	}


	public function messageNoReadCount() {
		global $_W;
		if (!user_is_founder($_W['uid']) || user_is_vice_founder($_W['uid'])) {
			$this->query->where('uid', $_W['uid']);
		}
		if (user_is_founder($_W['uid']) && !user_is_vice_founder()) {
			$this->query->where('type !=', array(MESSAGE_USER_EXPIRE_TYPE));
		}
		$list =  $this->query->from($this->tableName)->orderby('id', 'DESC')->getall();
		return count($list);
	}

	public function messageExists($message) {
		return $this->query->from($this->tableName)->where($message)->get();
	}
}