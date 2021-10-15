<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
namespace We7\Table\Uni;
class AccountExtraModules extends \We7Table {
	protected $tableName = 'uni_account_extra_modules';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'modules',
	);
	protected $default = array(
		'uniacid' => 0,
		'modules' => '',
	);
}