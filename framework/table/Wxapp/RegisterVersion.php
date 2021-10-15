<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
namespace We7\Table\Wxapp;

class RegisterVersion extends \We7Table {
	protected $tableName = 'wxapp_register_version';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'version_id',
		'auditid',
		'version',
		'description',
		'status',
		'reason',
		'upload_time',
		'audit_info',
		'submit_info',
		'developer',

	);
	protected $default = array(
		'uniacid' => '',
		'version_id' => '',
		'auditid' => '',
		'version' => '',
		'description' => '',
		'status' => '0',
		'reason' => '',
		'upload_time' => '',
		'audit_info' => '',
		'submit_info' => '',
		'developer' => '',
	);
	
	public function getWithAccountWxappOriginal($original) {
		$result = $this->query->from('wxapp_register_version', 'v')
					->select('v.*')
					->leftjoin('account_wxapp', 'a')
					->on('v.uniacid', 'a.uniacid')
					->where(array('a.original' => $original, 'v.auditid >' => 0, 'v.status' => WXAPP_REGISTER_VERSION_STATUS_CHECKING))
					->orderby('id', 'DESC')
					->get();
		return $result;
	}
	public function getByUniacidAndAuditid($uniacid, $auditid) {
		$result = $this->where('uniacid', $uniacid)->where('auditid', $auditid)->orderby('id', 'desc')->get();
		if (empty($result)) {
			return array();
		}
		$result = $this->dataunserializer($result);
		return $result;
	}

	public function getByUniacidAndVersionidAndStatus($uniacid, $version_id, $status) {
		$result = $this->query
					->where('uniacid', $uniacid)
					->where('version_id', $version_id)
					->where('status', $status)
					->get();
		if (empty($result)) {
			return array();
		}
		$result = $this->dataunserializer($result);
		return $result;
	}
	
	public function getByUniacid($uniacid) {
		$result = $this->where('uniacid', $uniacid)->orderby('id', 'desc')->getall();
		if (empty($result)) {
			return array();
		}
		foreach ($result as $key => $item) {
			$result[$key] = $this->dataunserializer($item);
		}
		return $result;
	}
	
	public function dataunserializer($data) {
		if (!empty($data['audit_info'])) {
			$data['audit_info'] = iunserializer($data['audit_info']);
		}
		if (!empty($data['submit_info'])) {
			$data['submit_info'] = iunserializer($data['submit_info']);
		}
		if (!empty($data['reason'])) {
			$data['reason'] = iunserializer($data['reason']);
		}
		$data['upload_time'] = date('Y-m-d H:i:s', $data['upload_time']);
		return $data;
	}
}