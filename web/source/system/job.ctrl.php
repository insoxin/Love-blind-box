<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('job');
$dos = array('clear', 'execute', 'display');
$do = in_array($do, $dos) ? $do : 'display';

if ('display' == $do) {
	$list = job_list($_W['uid'], $_W['isfounder']);
	$ajax_list = array();
	$jobid = intval($_GPC['jobid']);
	if (is_array($list) && !empty($list)) {
		foreach ($list as &$item) {
			$progress = $item['total'] > 0 ? $item['handled'] / $item['total'] * 100 : 0;
			$item['progress'] = $item['status'] ? 100 : intval($progress);
			$item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
			$item['endtime'] = date('Y-m-d H:i:s', $item['endtime']);
			$ajax_list[] = $item;
		}
	}
	if ($_W['isajax']) {
		$message = array(
			'list' => $ajax_list
		);
		iajax(0, $message);
	}
	template('system/job');
}

if ('execute' == $do) {
	$id = intval($_GPC['id']);
	$job = job_single($id);
		if ($_W['isfounder'] || $job['uid'] == $_W['uid']) {
		$result = job_execute($id);
		if (is_error($result)) {
			iajax(-1, $result['message']);
		}
		if ($_W['isw7_request']) {
			iajax(0, '删除成功');
		}
		iajax(0, $result['message']);
	}
}

if ('clear' == $do) {
	$result = job_clear($uid, $_W['isfounder']);
	if ($_W['isajax']) {
		iajax(0, '清空成功');
	}
	itoast('清除成功', referer());
}
