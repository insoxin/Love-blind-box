<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
namespace We7\Table\Core;

class Refundlog extends \We7Table {
	protected $tableName = 'core_refundlog';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'refund_uniontid',
		'reason',
		'uniontid',
		'fee',
		'status',
		'is_wish',
	);
	protected $default = array(
		'uniacid' => '0',
		'refund_uniontid' => '',
		'reason' => '',
		'uniontid' => '',
		'fee' => '',
		'status' => 0,
		'is_wish' => 0,
	);

	public function getByUniontid($uniontid) {
		return $this->query->where('uniontid', $uniontid)->get();
	}

}