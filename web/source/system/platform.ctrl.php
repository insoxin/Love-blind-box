<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->classs('weixin.platform');

setting_load('platform');

if ($_W['isajax'] && $_W['ispost']) {
	$data = array();
	$token = safe_gpc_string(trim($_GPC['token']));
	$encodingaeskey = safe_gpc_string(trim($_GPC['encodingaeskey']));
	$appid = safe_gpc_string(trim($_GPC['appid']));
	$appsecret = safe_gpc_string(trim($_GPC['appsecret']));
	$bindappid = safe_gpc_string(trim($_GPC['bindappid']));
	
	$authstate = isset($_GPC['authstate']) ? intval($_GPC['authstate']) : $_W['setting']['platform']['authstate'];
	$data['token'] = !empty($token) ? $token : $_W['setting']['platform']['token'];
	$data['encodingaeskey'] = !empty($encodingaeskey) ? $encodingaeskey : $_W['setting']['platform']['encodingaeskey'];
	$data['appid'] = !empty($appid) ? $appid : $_W['setting']['platform']['appid'];
	$data['appsecret'] = !empty($appsecret) ? $appsecret : $_W['setting']['platform']['appsecret'];
	$data['bindappid'] = !empty($bindappid) ? $bindappid : $_W['setting']['platform']['bindappid'];
	$data['authstate'] = !empty($authstate) ? 1 : 0;

	$result = setting_save($data, 'platform');
	if ($result) {
		iajax(0, '修改成功！', '');
	} else {
		iajax(1, '修改失败！', '');
	}
}

if (empty($_W['setting']['platform'])) {
	$_W['setting']['platform'] = array(
		'token' => random(32),
		'encodingaeskey' => random(43),
		'appsecret' => '',
		'appid' => '',
		'authstate' => 1,
	);
	setting_save($_W['setting']['platform'], 'platform');
}
$siteroot_parse_array = parse_url($_W['siteroot']);
$account_platform = new WeixinPlatform();
$authurl = $account_platform->getAuthLoginUrl();

if ($_W['isajax']) {
	if ($_W['isw7_request']) {
		$authurl = '';
		$preauthcode = $account_platform->getPreauthCode();
		if (!is_error($preauthcode)) {
			$authurl = sprintf(ACCOUNT_PLATFORM_API_LOGIN, $account_platform->appid,
				$preauthcode, urlencode($GLOBALS['_W']['siteroot'] . 'index.php?c=account&a=auth&do=forward'), ACCOUNT_PLATFORM_API_LOGIN_ACCOUNT);
		}
	}
	iajax(0, array(
		'platform' => empty($_W['setting']['platform']) ? array() : $_W['setting']['platform'],
		'siteroot' => $_W['siteroot'],
		'siteroot_parse' => $siteroot_parse_array,
		'authurl' => $authurl,
		'authurl_error' => empty($preauthcode['message']) ? '' : $preauthcode['message'],
	));
}

template('system/platform');
