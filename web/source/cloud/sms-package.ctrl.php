<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');

$dos = array('smsTrade', 'diaplay');
$do = in_array($do, $dos) ? $do : 'diaplay';

if ('smsTrade' == $do) {
	$data = cloud_sms_trade(intval($_GPC['page']), intval($_GPC['start_time']), intval($_GPC['end_time']), intval($_GPC['status_order']));

	if (isset($data['data'][0]['createtime']) && is_numeric($data['data'][0]['createtime'])) {
		foreach ($data['data'] as &$item) {
			$item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
		}
	}
	$data['page'] = $data['current_page'];
	$data['page_size'] = $data['per_page'];
	
	$sms_info = cloud_sms_info();
	if (is_error($sms_info)) {
		iajax(-1, $sms_info['message']);
	}
	$message = array(
		'sms_info' => $sms_info,
		'list' => $data
	);

	iajax(0, $message);
}

template('cloud/sms-package');