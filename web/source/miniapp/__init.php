<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniapp');

if (in_array($action, array('post', 'manage'))) {
	define('FRAME', '');
} else {
	$version_id = intval($_GPC['version_id']);
	if (!empty($version_id)) {
		$account = table('account')->getUniAccountByUniacid($_W['uniacid']);
		$version_info = miniapp_version($version_id);
		if ($version_info['uniacid'] != $_W['uniacid']) {
			itoast('', $_W['siteroot'] . 'web/home.php');
		}
	}
	$account_api = WeAccount::createByUniacid();
	if (is_error($account_api)) {
		itoast('', $_W['siteroot'] . 'web/home.php');
	}
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		itoast('', $account_api->displayUrl);
	}
	$account_type = $account_api->menuFrame;
	if ('version' == $action && 'display' == $do) {
		define('FRAME', '');
	} else {
		define('FRAME', $account_type);
	}
	define('ACCOUNT_TYPE', $account_api->type);
	define('TYPE_SIGN', $account_api->typeSign);
	define('ACCOUNT_TYPE_NAME', $account_api->typeName);
}
$account_all_type = uni_account_type();