<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'recover', 'delete');
$do = in_array($do, $dos) ? $do : 'display';

if (!in_array($_W['highest_role'], array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))) {
	itoast('无权限操作！', referer(), 'error');
}

if ('display' == $do) {
	$page = max(1, intval($_GPC['page']));
	$page_size = 20;
	$account_table = table('account');

	$account_type = safe_gpc_string($_GPC['account_type']);
	if (!empty($account_type)) {
		$account_table->searchWithType($account_all_type_sign[$account_type]['contain_type']);
	}

	$keyword = trim($_GPC['keyword']);
	if (!empty($keyword)) {
		$account_table->searchWithKeyword($keyword);
	}

	$account_table->searchWithPage($page, $page_size);
	$del_accounts = $account_table->searchAccountList(false, 0);
	$total = $account_table->getLastQueryTotal();
	$pager = pagination($total, $page, $page_size);

	foreach ($del_accounts as &$account) {
		$account = uni_fetch($account['uniacid']);
		$account['end'] = 0 == $account['endtime'] ? '永久' : date('Y-m-d', $account['endtime']);
	}
	$del_accounts = array_values($del_accounts);
	if ($_W['isajax']) {
		iajax(0, array(
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size,
			'list' => $del_accounts
		));
	} else {
		template('account/recycle');
	}
}

if ('recover' == $do || 'delete' == $do) {
	$acid = safe_gpc_int($_GPC['acid']);
	$uniacid = safe_gpc_int($_GPC['uniacid']);
	if (!empty($acid) || !empty($uniacid)) {
		$acids = array($acid);
		$uniacids = array($uniacid);
	} else {
		$acids = safe_gpc_array($_GPC['acids']);
		$uniacids = safe_gpc_array($_GPC['uniacids']);
	}
	if (empty($uniacids)) {
		if ($_W['isajax']) {
			iajax(-1, '缺少必要参数！');
		}
		itoast('缺少必要参数！', referer(), 'error');
	}
	foreach ($uniacids as $unacid) {
		$state = permission_account_user_role($_W['uid'], $uniacid);
		if (!in_array($state, array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))) {
			if ($_W['isajax']) {
				iajax(-1, '没有权限，请联系该平台账号的主管理员或网站创始人进行恢复操作！');
			}
			itoast('没有权限，请联系该平台账号的主管理员或网站创始人进行恢复操作！', referer(), 'error');
		}
	}
}

if ('recover' == $do) {
	$account_info = permission_user_account_num();
	foreach ($uniacids as $uniacid) {
		$account = uni_fetch($uniacid);
		$sign_limit = $account['type_sign'].'_limit';
		$founder_sign_limit = 'founder_' . $account['type_sign'] . '_limit';
		if (!(!empty($account_info[$sign_limit]) && (!empty($account_info[$founder_sign_limit]) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || !empty($account_info['store_' . $sign . '_limit']) || $_W['isadmin'])) {
			if ($_W['isajax']) {
				iajax(-1, '您所在用户组可添加的平台账号数量已达上限，请停用后再行恢复此平台账号！');
			}
			itoast('您所在用户组可添加的平台账号数量已达上限，请停用后再行恢复此平台账号！', referer(), 'error');
		}
		if (in_array($account['type_sign'], array(BAIDUAPP_TYPE_SIGN, TOUTIAOAPP_TYPE_SIGN))) {
			$appid = $account['appid'];
		} else {
			$appid = $account['key'];
		}
		if (!empty($appid)) {
			$hasAppid = uni_get_account_by_appid($appid, $account['type'], $account['uniacid']);
			if (!empty($hasAppid)) {
				if ($_W['isajax']) {
					iajax(-1, "该平台{$hasAppid['key_title']}已被其他平台使用, 请停用{$hasAppid['type_title']}[ {$hasAppid['name']} ]后再恢复.");
				}
				itoast("该平台{$hasAppid['key_title']}已被其他平台使用, 请停用{$hasAppid['type_title']}[ {$hasAppid['name']} ]后再恢复.", referer(), 'error');
			}
		}
		pdo_update('account', array('isdeleted' => 0), array('uniacid' => $uniacid));
		cache_delete(cache_system_key('uniaccount', array('uniacid' => $uniacid)));
	}
	if ($_W['isajax']) {
		iajax(0, '恢复成功', referer());
	}
	itoast('恢复成功', referer(), 'success');
}

if ('delete' == $do) {
	if (empty($_W['isajax']) || empty($_W['ispost'])) {
		iajax(-1, '非法操作！', referer());
	}
	foreach ($acids as $acid) {
		account_delete($acid);
	}
	if ($_W['isadmin']) {
		$url = url('system/job/display');
	} else {
		$url = url('account/recycle', array('account_type' => ACCOUNT_TYPE));
	}
	iajax(0, '删除成功！', $url);
}
