<?php
/**
 * [WeEngine System] Copyright (c) 2014 W7.CC
 */
namespace We7\Table\Wxapp;

class Reply extends \We7Table {
	protected $tableName = 'wxapp_reply';
	protected $primaryKey = 'id';
	protected $field = array(
		'rid',
		'title',
		'appid',
		'pagepath',
		'mediaid',
		'createtime',
	);
	protected $default = array(
		'rid' => '',
		'title' => '',
		'appid' => '',
		'pagepath' => '',
		'mediaid' => '',
		'createtime' => '',
	);
}