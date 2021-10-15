<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
namespace We7\Table\Uni;

class Settings extends \We7Table {
	protected $tableName = 'uni_settings';
	protected $primaryKey = 'uniacid';
	protected $field = array(
		'uniacid',
		'passport',
		'oauth',
		'jsauth_acid',
		'notify',
		'creditnames',
		'creditbehaviors',
		'welcome',
		'default',
		'default_message',
		'shortcuts',
		'payment',
		'stat',
		'default_site',
		'sync',
		'recharge',
		'tplnotice',
		'grouplevel',
		'mcplugin',
		'exchange_enable',
		'coupon_type',
		'statistics',
		'bind_domain',
		'comment_status',
		'reply_setting',
		'default_module',
		'attachment_limit',
		'attachment_size',
		'sync_member',
		'remote'
	);
	protected $default = array(
		'uniacid' => 0,
		'passport' => '',
		'oauth' => '',
		'jsauth_acid' => 0,
		'notify' => '',
		'creditnames' => '',
		'creditbehaviors' => '',
		'welcome' => '',
		'default' => '',
		'default_message' => '',
		'shortcuts' => '',
		'payment' => '',
		'stat' => '',
		'default_site' => 0,
		'sync' => '',
		'recharge' => '',
		'tplnotice' => '',
		'grouplevel' => 1,
		'mcplugin' => '',
		'exchange_enable' => 0,
		'coupon_type' => 1,
		'statistics' => '',
		'bind_domain' => '',
		'comment_status' => 0,
		'reply_setting' => 0,
		'default_module' => '',
		'attachment_limit' => 0,
		'attachment_size' => '',
		'sync_member' => 0,
		'remote' => ''
	);
	public function searchWithUniAccountAndAccount(){
		return $this->query->from($this->tableName, 'a')
			->leftjoin('uni_account', 'b')
			->on('a.uniacid', 'b.uniacid')
			->leftjoin('account', 'c')
			->on('a.uniacid', 'c.uniacid');
	}

}