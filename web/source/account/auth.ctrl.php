<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->func('communication');
load()->classs('weixin.platform');
load()->model('account');
set_time_limit(0);

$dos = array('ticket', 'forward', 'test', 'confirm');
$do = in_array($do, $dos) ? $do : 'forward';

$account_platform = new WeixinPlatform();

if ('forward' == $do) {
	if (empty($_GPC['auth_code'])) {
		itoast('授权登录失败，请重试', url('account/manage'), 'error');
	}
	$auth_info = $account_platform->getAuthInfo($_GPC['auth_code']);
	if (is_error($auth_info)) {
		itoast('授权登录新建公众号失败：' . $auth_info['message'], url('account/manage'), 'error');
	}
	$auth_refresh_token = $auth_info['authorization_info']['authorizer_refresh_token'];
	$auth_appid = $auth_info['authorization_info']['authorizer_appid'];

	$account_info = $account_platform->getAuthorizerInfo($auth_appid);
	if (is_error($account_info)) {
		itoast('授权登录新建公众号失败：' . $account_info['message'], url('account/manage'), 'error');
	}
	if (!empty($_GPC['test'])) {
		echo "此为测试平台接入返回结果：<br/> 公众号名称：{$account_info['authorizer_info']['nick_name']} <br/> 接入状态：成功";
		exit;
	}
	if ($account_info['authorizer_info']['service_type_info']['id'] == '0' || $account_info['authorizer_info']['service_type_info']['id'] == '1') {
		if ($account_info['authorizer_info']['verify_type_info']['id'] > '-1') {
			$level = '3';
		} else {
			$level = '1';
		}
	} elseif ($account_info['authorizer_info']['service_type_info']['id'] == '2') {
		if ($account_info['authorizer_info']['verify_type_info']['id'] > '-1') {
			$level = '4';
		} else {
			$level = '2';
		}
	}
	$account_found = $account_platform->fetchSameAccountByAppid($auth_appid);
	if (!empty($account_found)) {
		message('公众号已经在系统中接入，是否要更改为授权接入方式？ <div><a class="btn btn-primary" href="' . url('account/auth/confirm', array('level' => $level, 'auth_refresh_token' => $auth_refresh_token, 'auth_appid' => $auth_appid, 'uniacid' => $account_found['uniacid'])) . '">是</a> &nbsp;&nbsp;<a class="btn btn-default" href="index.php">否</a></div>', '', 'tips');
	}
	$account_insert = array(
		'name' => $account_info['authorizer_info']['nick_name'],
		'description' => '',
		'groupid' => 0,
	);
	$insert_result = table('uni_account')->fill($account_insert)->save();
	if (!$insert_result) {
		itoast('授权登录新建公众号失败，请重试', url('account/manage'), 'error');
	}
	$uniacid = pdo_insertid();
	$template = table('modules')->getTemplateByName('default');
	$style_insert = array(
		'uniacid' => $uniacid,
		'templateid' => $template['mid'],
		'name' => $template['title'] . '_' . random(4),
	);
	table('site_styles')->fill($style_insert)->save();
	$styleid = pdo_insertid();

	$multi_insert = array(
		'uniacid' => $uniacid,
		'title' => $account_insert['name'],
		'styleid' => $styleid,
	);
	table('site_multi')->fill($multi_insert)->save();
	$multi_id = pdo_insertid();

	$unisetting_insert = array(
		'creditnames' => iserializer(array(
			'credit1' => array('title' => '积分', 'enabled' => 1),
			'credit2' => array('title' => '余额', 'enabled' => 1),
		)),
		'creditbehaviors' => iserializer(array(
			'activity' => 'credit1',
			'currency' => 'credit2',
		)),
		'uniacid' => $uniacid,
		'default_site' => $multi_id,
		'sync' => iserializer(array('switch' => 0, 'acid' => '')),
	);
	pdo_insert('uni_settings', $unisetting_insert);
	table('mc_groups')->fill(array('uniacid' => $uniacid, 'title' => '默认会员组', 'isdefault' => 1))->save();

	$account_index_insert = array(
		'uniacid' => $uniacid,
		'type' => ACCOUNT_OAUTH_LOGIN,
		'hash' => random(8),
		'isconnect' => 1,
	);
	if (!$_W['isadmin']  && $_W['user']['endtime'] > USER_ENDTIME_GROUP_UNLIMIT_TYPE) {
		$account_index_insert['endtime'] = $_W['user']['endtime'];
	}
	table('account')->fill($account_index_insert)->save();
	$acid = pdo_insertid();
	if (is_error($acid)) {
		itoast('授权登录新建公众号失败，请重试', url('account/manage'), 'error');
	}
	$subaccount_insert = array(
		'acid' => $acid,
		'uniacid' => $uniacid,
		'name' => $account_insert['name'],
		'account' => $account_info['authorizer_info']['alias'],
		'original' => $account_info['authorizer_info']['user_name'],
		'level' => $level,
		'key' => $auth_appid,
		'auth_refresh_token' => $auth_refresh_token,
		'encodingaeskey' => $account_platform->encodingaeskey,
		'token' => $account_platform->token,
	);
	pdo_insert('account_wechats', $subaccount_insert);
	$user_create_account_info = permission_user_account_num();
	if (empty($_W['isfounder']) && empty($user_create_account_info["usergroup_account_limit"])) {
		pdo_insert('site_store_create_account', array('endtime' => strtotime('+1 month', time()), 'uid' => $_W['uid'], 'uniacid' => $uniacid, 'type' => ACCOUNT_TYPE_OFFCIAL_AUTH));
	}
	if (user_is_vice_founder()) {
		uni_account_user_role_insert($uniacid, $_W['uid'], ACCOUNT_MANAGE_NAME_VICE_FOUNDER);
	}
	if (empty($_W['isfounder'])) {
		uni_account_user_role_insert($uniacid, $_W['uid'], ACCOUNT_MANAGE_NAME_OWNER);
		if (!empty($_W['user']['owner_uid'])) {
			uni_account_user_role_insert($uniacid, $_W['user']['owner_uid'], ACCOUNT_MANAGE_NAME_VICE_FOUNDER);
		}
	}
	pdo_update('uni_account', array('default_acid' => $acid, 'logo' => $account_info['authorizer_info']['head_img'], 'qrcode' => $account_info['authorizer_info']['qrcode_url']), array('uniacid' => $uniacid));
	$headimg = ihttp_request($account_info['authorizer_info']['head_img']);
	$qrcode = ihttp_request($account_info['authorizer_info']['qrcode_url']);
	file_put_contents(IA_ROOT . '/attachment/headimg_' . $acid . '.jpg', $headimg['content']);
	file_put_contents(IA_ROOT . '/attachment/qrcode_' . $acid . '.jpg', $qrcode['content']);

	cache_build_account($uniacid);
	cache_delete(cache_system_key('proxy_wechatpay_account'));
	cache_clean(cache_system_key('user_accounts'));
	itoast('授权登录成功', url('account/manage'), 'success');
}
if ('confirm' == $do) {
	$auth_refresh_token = $_GPC['auth_refresh_token'];
	$auth_appid = $_GPC['auth_appid'];
	$level = intval($_GPC['level']);
	$uniacid = intval($_GPC['uniacid']);

	if ($_W['isfounder']) {
		$user_accounts = table('account')->getAll();
	} else {
		$user_accounts = uni_user_accounts($_W['uid']);
	}
	$user_accounts = array_column($user_accounts, 'uniacid');
	if (empty($user_accounts) || !in_array($uniacid, $user_accounts)) {
		itoast('账号或用户信息错误!', url('account/post', array('uniacid' => $uniacid)), 'error');
	}

	$account_wechats_data = array(
		'auth_refresh_token' => $auth_refresh_token,
		'encodingaeskey' => $account_platform->encodingaeskey,
		'token' => $account_platform->token,
		'level' => $level,
		'key' => $auth_appid,
	);
	table('account_wechats')->where(array('uniacid' => $uniacid))->fill($account_wechats_data)->save();
	$account_index_insert = array(
		'isdeleted' => 0,
		'type' => ACCOUNT_OAUTH_LOGIN,
		'isconnect' => 1,
	);
	if (!$_W['isadmin']  && $_W['user']['endtime'] > USER_ENDTIME_GROUP_UNLIMIT_TYPE) {
		$account_index_insert['endtime'] = $_W['user']['endtime'];
	}
	table('account')->where(array('uniacid' => $uniacid))->fill($account_index_insert)->save();
	cache_delete(cache_system_key('uniaccount', array('uniacid' => $uniacid)));
	cache_delete(cache_system_key('accesstoken', array('uniacid' => $uniacid)));
	cache_delete(cache_system_key('account_auth_refreshtoken', array('uniacid' => $uniacid)));
	itoast('更改公众号授权接入成功', url('account/post', array('uniacid' => $uniacid)), 'success');
}
if ('ticket' == $do) {
	$post = file_get_contents('php://input');
	WeUtility::logging('debug', 'account-ticket' . $post);
	$encode_ticket = isimplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($post) || empty($encode_ticket) || strval($encode_ticket->AppId) != $_W['setting']['platform']['appid']) {
		exit('fail');
	}
	$decode_ticket = aes_decode($encode_ticket->Encrypt, $account_platform->encodingaeskey);
	$ticket_xml = isimplexml_load_string($decode_ticket, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($ticket_xml)) {
		exit('fail');
	}
	if (!empty($ticket_xml->ComponentVerifyTicket) && 'component_verify_ticket' == $ticket_xml->InfoType) {
		$ticket = strval($ticket_xml->ComponentVerifyTicket);
		setting_save($ticket, 'account_ticket');
	}
	exit('success');
}
if ('test' == $do) {
	$authurl = $account_platform->getAuthLoginUrl();
	echo '<a href="' . $authurl . '%26test=1"><img src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon_button3_2.png" /></a>';
}