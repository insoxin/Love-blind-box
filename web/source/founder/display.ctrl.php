<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$dos = array('display');
$do = in_array($do, $dos) ? $do : 'display';
$founders = explode(',', $_W['config']['setting']['founder']);
if ('display' == $do) {
	$founder_groups = user_founder_group();

	$page = max(1, intval($_GPC['page']));
	$page_size = 20;

	$users_table = table('users');
	$users_table->searchWithFounder(ACCOUNT_MANAGE_GROUP_VICE_FOUNDER);
	$users_table->searchWithStatus(USER_STATUS_NORMAL);

	$keyword = safe_gpc_string($_GPC['keyword']);
	if (!empty($keyword)) {
		$users_table->searchWithNameOrMobile($keyword);
	}

	$group_id = intval($_GPC['groupid']);
	if (!empty($group_id)) {
		$users_table->searchWithGroupId($group_id);
	}

	$users_table->searchWithPage($page, $page_size);
	$users_table->searchWithViceFounder();
	$users = $users_table->getUsersList();
	$total = $users_table->getLastQueryTotal();
	$users = array_values(user_list_format($users));
	if ($_W['isajax']) {
		$result = array(
			'list' => array_values($users),
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size
		);
		iajax(0, $result);
	}
	$pager = pagination($total, $page, $page_size);
	template('founder/display');
}
