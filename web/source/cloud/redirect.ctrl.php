<?php
/**
 * [翰飞网络科技 System] Copyright (c) 2018 WEBY.CC
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited http://w.4tiaomao.com/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->func('communication');

$dos = array('profile', 'callback', 'appstore', 'buybranch', 'sms');
$do = in_array($do, $dos) ? $do : 'profile';

if($do == 'profile') {
	define('ACTIVE_FRAME_URL', url('cloud/profile'));
	$iframe = cloud_auth_url('profile');
	$title = '注册站点';
}

if($do == 'sms') {
	define('ACTIVE_FRAME_URL', url('cloud/sms'));
	uni_user_permission_check('system_cloud_sms');
	$iframe = cloud_auth_url('sms');
	$title = '云短信';
}

if($do == 'appstore') {
	$iframe = cloud_auth_url('appstore');
	$title = '应用商城';
	header("Location: $iframe");
	exit;
}

if($do == 'promotion') {
	if(empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
		itoast("你的程序需要在微信魔方云服务平台注册你的站点资料, 来接入云平台服务后才能使用推广功能.", url('cloud/profile'), 'error');
	}
	$iframe = cloud_auth_url('promotion');
	$title = '我要推广';
}

if ($do == 'buybranch') {
	
	$auth = array();
	$auth['name'] = $_GPC['m'];
	$auth['branch'] = intval($_GPC['branch']);
	$url = cloud_auth_url('buybranch', $auth);
	
	$response = ihttp_request($url);
	$response = json_decode($response['content'], true);

	if (is_error($response['message'])) {
		itoast($response['message']['message'], url('system/module'), 'error');
	}

	$params = array(
		'is_upgrade' => 1,
		'is_buy' => 1,
	);
	if (trim($_GPC['type']) == 'theme') {
		$params['t'] = $auth['name'];
	} else {
		$params['m'] = $auth['name'];
	}

	itoast($response['message']['message'], url('cloud/process', $params), 'success');
}

if($do == 'callback') {
	$secret = $_GPC['token'];
	if(strlen($secret) == 32) {
		$cache = cache_read('cloud:auth:transfer');
		cache_delete('cloud:auth:transfer');
		if(!empty($cache) && $cache['secret'] == $secret) {
			$site = $cache;
			unset($site['secret']);
			setting_save($site, 'site');
			$auth = array();
			$auth['key'] = $site['key'];
			$auth['password'] = md5($site['key'] . $site['token']);
			$url = cloud_auth_url('profile', $auth);
			header('Location: ' . $url);
			exit();
		}
	}
	itoast('访问错误.', '', '');
}

template('cloud/frame');