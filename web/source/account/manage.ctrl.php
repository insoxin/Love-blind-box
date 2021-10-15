<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('message');
load()->model('miniapp');

$dos = array('display', 'delete', 'user_account_num', 'account_list', 'account_detailinfo');
$do = in_array($_GPC['do'], $dos) ? $do : 'display';

if ('display' == $do) {
	if (!$_W['isfounder']) {
		itoast('', $_W['siteroot'] . 'web/home.php');
	}
	$message_id = intval($_GPC['message_id']);
	message_notice_read($message_id);
	foreach ($account_all_type_sign as $type_sign => $type_value) {
		if ($_W['isadmin']) {
			$account_all_type_sign[$type_sign]['account_num'] = 1;
			continue;
		}
		$type_accounts = uni_user_accounts($_W['uid'], $type_sign);
		$account_all_type_sign[$type_sign]['account_num'] = empty($type_accounts) ? 0 : count($type_accounts);
	}
	template('account/manage-display');
}

if ('account_list' == $do) {
	$page = max(1, intval($_GPC['page']));
	$page_size = empty($_GPC['page_size']) ? 20 : max(1, intval($_GPC['page_size']));
	$order = !empty($_GPC['order']) ? safe_gpc_string($_GPC['order']) : 'desc';
	$keyword = safe_gpc_string($_GPC['keyword']);
	$account_type = empty($account_all_type_sign[$_GPC['account_type']]) ? 0 : $_GPC['account_type'];
	$expire_type = in_array($_GPC['type'], array('expire', 'unexpire')) ? $_GPC['type'] : '';

	$account_table = table('account');
	if (!empty($account_type)) {
		$account_table->searchWithType($account_all_type_sign[$account_type]['contain_type']);
	}
	if (!empty($keyword)) {
		$account_table->searchWithKeyword($keyword);
	}
	if(in_array($order, array('asc', 'desc'))) {
		$account_table->accountUniacidOrder($order);
	}
	if (in_array($order, array('endtime_asc', 'endtime_desc'))) {
		$account_table->accountEndtimeOrder($order);
	}
	$account_table->searchWithPage($page, $page_size);
	$list = $account_table->searchAccountList($expire_type);
	$total = $account_table->getLastQueryTotal();

	foreach ($list as $uniacid => $info) {
		$account = uni_fetch($uniacid);
		if (is_error($account) && empty($account)) {
			continue;
		}
		$account['switchurl_full'] = $_W['siteroot'] . 'web/' . ltrim($account['switchurl'], './');
		$account['owner_name'] = $account->owner['username'];
		$account['support_version'] = $account->supportVersion;
		$account['sms_num'] = !empty($account['setting']['notify']) ? $account['setting']['notify']['sms']['balance'] : 0;
		$account['end'] = USER_ENDTIME_GROUP_EMPTY_TYPE == $account['endtime'] || USER_ENDTIME_GROUP_UNLIMIT_TYPE == $account['endtime'] ? '永久' : date('Y-m-d', $account['endtime']);
		$account['manage_premission'] = in_array($account['current_user_role'], array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER, ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_MANAGER));
		if ($account['support_version']) {
			$account['versions'] = miniapp_get_some_lastversions($uniacid);
			if (!empty($account['versions'])) {
				foreach ($account['versions'] as $version) {
					if (!empty($version['current'])) {
						$account['current_version'] = $version;
					}
				}
			}
		}
		$list[$uniacid] = $account;
	}
	$pager = pagination($total, $page, $page_size, '', array('isajax' => 1, 'callbackfuncname' => 'getAccountList'));
	iajax(0, array(
		'total' => $total,
		'page' => $page,
		'page_size' => $page_size,
		'pager' => $pager,
		'list' => array_values($list),
	));
}

if ('delete' == $do) {
	$uniacids = empty($_GPC['uniacids']) && !empty($_GPC['uniacid']) ? array($_GPC['uniacid']) : $_GPC['uniacids'];
	if (!empty($uniacids)) {
		foreach ($uniacids as $uniacid) {
			$uniacid = intval($uniacid);
			$state = permission_account_user_role($_W['uid'], $uniacid);
			if (!in_array($state, array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))) {
				continue;
			}

			if (!empty($uniacid)) {
				$account = pdo_get('account', array('uniacid' => $uniacid));
				if (empty($account)) {
					continue;
				}
				pdo_update('account', array('isdeleted' => 1), array('uniacid' => $uniacid));
				pdo_delete('uni_modules', array('uniacid' => $uniacid));
				table('users_operate_star')->where(array('uniacid' => $uniacid))->delete();
				pdo_delete('users_lastuse', array('uniacid' => $uniacid));
				pdo_delete('core_menu_shortcut', array('uniacid' => $uniacid));
				pdo_delete('uni_link_uniacid', array('link_uniacid' => $uniacid));
				if ($uniacid == $_W['uniacid']) {
					cache_delete(cache_system_key('last_account', array('switch' => $_GPC['__switch'], 'uid' => $_W['uid'])));
					isetcookie('__uniacid', '');
				}
				cache_delete(cache_system_key('user_accounts', array('type' => $account_all_type[$account['type']]['type_sign'], 'uid' => $_W['uid'])));
				cache_delete(cache_system_key('uniaccount', array('uniacid' => $uniacid)));
			}
		}
	}

	$redirct_url = url('account/manage');
	if (!$_W['iscontroller']) {
		$redirct_url = $_W['siteroot'] . 'web/home.php';
	}
	if (!$_W['isajax'] || !$_W['ispost']) {
		itoast('停用成功！，您可以在回收站中恢复', $redirct_url);
	}
	iajax(0, '停用成功！，您可以在回收站中恢复', $redirct_url);
}