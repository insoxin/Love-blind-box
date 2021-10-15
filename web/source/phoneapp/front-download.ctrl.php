<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('phoneapp');
load()->classs('cloudapi');
load()->classs('uploadedfile');

$dos = array('front_download', 'getpackage');
$do = in_array($do, $dos) ? $do : 'front_download';
$version_id = intval($_GPC['version_id']);

$is_module_wxapp = false;
if (!empty($version_id)) {
	$version_info = phoneapp_version($version_id);
	$module = !empty($version_info['modules']) ? current($version_info['modules']) : array();
}

if ('front_download' == $do) {
	$appurl = $_W['siteroot'] . '/app/index.php';
	$type = empty($_GPC['type']) ? 'apk' : $_GPC['type'];
	$account_info = phoneapp_version($version_id);
	$siteurl = $_W['siteroot'] . 'app/index.php';
	if (!empty($account_info['appdomain'])) {
		$siteurl = $account_info['appdomain'];
	}
	$siteinfo = array(
		'name' => $account_info['name'],
		'm' => !empty($account_info['modules'][0]) ? $account_info['modules'][0]['name'] : '',
		'uniacid' => $account_info['uniacid'],
		'acid' => $account_info['acid'],
		'version' => $account_info['version'],
		'siteroot' => $siteurl,
	);
	template('phoneapp/front-download');
}

if ('getpackage' == $do) {
	if (empty($version_id)) {
		itoast('参数错误！', '', '');
	}
	$account_info = phoneapp_version($version_id);
	if (empty($account_info)) {
		itoast('版本不存在！', referer(), 'error');
	}
	if (0 == count($account_info['modules'])) {
		itoast('请先配置模块');
	}

	$module = current($account_info['modules']);
	$request_cloud_data = array(
		'module' => array(
			'name' => $module['name'],
			'version' => $module['version'],
		),
		'support' => 'apk' == $_GPC['type'] ? 'android' : 'ios',
	);
	$result = cloud_miniapp_get_package($request_cloud_data);

	if (is_error($result)) {
		itoast($result['message'], '', '');
	} else {
		header("http/1.1 301 moved permanently");
		header( "location: " . $result['url'] );
	}
	exit;
}
