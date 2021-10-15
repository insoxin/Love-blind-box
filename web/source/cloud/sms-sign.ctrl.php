<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');

$dos = array('sms_sign', 'sms_edit', 'display');
$do = in_array($do, $dos) ? $do : 'display';

if ('sms_sign' == $do) {
	$data = cloud_sms_sign(intval($_GPC['page']), intval($_GPC['start_time']), intval($_GPC['end_time']), intval($_GPC['status_audit']), intval($_GPC['status_order']));
	if (isset($data['data'][0]['createtime']) && is_numeric($data['data'][0]['createtime'])) {
		foreach ($data['data'] as &$item) {
			$item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
		}
	}
	$data['page'] = $data['current_page'];
	$data['page_size'] = $data['per_page'];
	iajax(0, $data);
}
if ('sms_edit' == $do) {
	$sign = array(
		'sign_id' => intval($_GPC['sign_id']),
		'sign_content' => safe_gpc_string($_GPC['sign_content']),
	);
	$result = cloud_sms_edit($sign);
	if (is_error($result)) {
		iajax(-1, $result['message']);
	}
	iajax(0, '修改成功!', referer());
}
template('cloud/sms-sign');
