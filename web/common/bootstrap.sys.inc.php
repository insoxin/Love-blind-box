<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->web('common');
load()->web('template');
load()->func('file');
load()->func('tpl');
load()->model('user');
load()->model('permission');
load()->model('attachment');
load()->classs('oauth2/oauth2client');
load()->model('switch');
load()->model('system');

$_W['token'] = token();
$session = json_decode(authcode($_GPC['__session']), true);
if (is_array($session)) {
	$user = user_single(array('uid' => $session['uid']));
	if (is_array($user) && $session['hash'] === $user['hash']) {
		$_W['uid'] = $user['uid'];
		$_W['username'] = $user['username'];
		$user['currentvisit'] = $user['lastvisit'];
		$user['currentip'] = $user['lastip'];
		$user['lastvisit'] = $session['lastvisit'];
		$user['lastip'] = $session['lastip'];
		$_W['user'] = $user;
		$_W['isfounder'] = user_is_founder($_W['uid']);
		$_W['isadmin'] = user_is_founder($_W['uid'], true);
	} else {
		isetcookie('__session', false, -100);
	}
	unset($user);
}
unset($session);
$_W['isw7_request'] = isset($_GPC['w7_request_secret']) ? true : false;
if ($_W['isw7_request'] && !$_W['isadmin']) {
	$request_token = cloud_w7_request_token(safe_gpc_string($_GPC['w7_request_secret']));
	if (isset($_GPC['w7_accesstoken'])) {
		if ($_GPC['w7_accesstoken'] != $request_token) {
			$request_token = cloud_w7_request_token(safe_gpc_string($_GPC['w7_request_secret']), true);
		}
		if (!is_error($request_token) && !empty($request_token) && $_GPC['w7_accesstoken'] == $request_token) {
			$_W['user'] = user_single(current(explode(',', $_W['config']['setting']['founder'])));
			$_W['uid'] = $_W['user']['uid'];
			$_W['username'] = $_W['user']['username'];
			$_W['isfounder'] = $_W['isadmin'] = user_is_founder($_W['uid'], true);
		}
		if (empty($_W['uid'])) {
			iajax(403, '身份验证失败！');
		}
	}
}
$_W['uniacid'] = igetcookie('__uniacid');
if (empty($_W['uniacid'])) {
	$_W['uniacid'] = switch_get_account_display();
}
$_W['uniacid'] = intval($_W['uniacid']);

if (!empty($_W['uid'])) {
	$_W['highest_role'] = permission_account_user_role($_W['uid']);
	$_W['role'] = permission_account_user_role($_W['uid'], $_W['uniacid']);
}

$_W['template'] = '2.0';

	$_W['template'] = !empty($_W['setting']['basic']['template']) ? $_W['setting']['basic']['template'] : '2.0';


$_W['attachurl'] = attachment_set_attach_url();
