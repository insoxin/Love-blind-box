<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
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

	public function getAccount($uniacid) {
		return $this->query->where('uniacid', $uniacid)->get();
	}

	public function searchWithAccount() {
		return $this->query->from($this->tableName, 't')
			->leftjoin('account', 'a')
			->on(array('t.uniacid' => 'a.uniacid'));
	}
}