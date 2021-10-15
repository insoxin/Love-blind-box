<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$system_roles = array('founder','vice_founder', 'owner', 'manager', 'operator', 'clerk', 'unbind_user', 'expired');
$user_defined_constants = get_defined_constants('true');
$user_defined_constants = $user_defined_constants['user'];

$links = array(
	'create_user' => url('user/create', array(), true),
);
$common_info = array(
	'uid' => $_W['uid'],
	'submit_token' => $_W['token'],
	'siteroot' => $_W['siteroot'],
	'isfounder' => $_W['isfounder'],
	'highest_role' => $_W['highest_role'],
	'system_roles' => $system_roles,
	'links' => $links,
	'uni_account_type' => $account_all_type,
	'uni_account_type_sign' => $account_all_type_sign,
	'permission' => $acl['see_more_info'][$_W['highest_role']],
	'defined_constants' => $user_defined_constants,
);
if ($_W['isw7_request']) {
	$common_info['sitekey'] = $_W['setting']['site']['key'];
}
iajax(0, $common_info);