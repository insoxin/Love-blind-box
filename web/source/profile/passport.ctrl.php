<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('oauth', 'save_oauth', 'uc_setting', 'upload_file', 'oauth_accounts');
$do = in_array($do, $dos) ? $do : 'oauth';

if ('save_oauth' == $do) {
	$type = $_GPC['type'];
	$account = intval($_GPC['account']);
	if ('oauth' == $type) {
		$host = safe_gpc_url(rtrim($_GPC['host'], '/'), false);
		if (!empty($_GPC['host']) && empty($host)) {
			iajax(-1, '域名不合法');
		}
		$oauthInfo = uni_setting_load();
		$oauth_account = empty($oauthInfo['oauth']['account']) ? '' : $oauthInfo['oauth']['account'];
		if ($oauth_account != $account) {
			$delete_result = user_account_delete($_W['acid']);
			if (empty($delete_result)) {
				iajax(-1, '参数错误');
			}
		}
		if (empty($host) && empty($account)) {
			uni_setting_save('oauth', '');
		} else {
			$data = array(
				'host' => $host,
				'account' => $account,
			);
			uni_setting_save('oauth', iserializer($data));
		}
		cache_delete(cache_system_key('unisetting', array('uniacid' => $_W['uniacid'])));
	}
	if ('jsoauth' == $type) {
		uni_setting_save('jsauth_acid', $account);
		cache_delete(cache_system_key('unisetting', array('uniacid' => $_W['uniacid'])));
	}
	iajax(0, '');
}

if ('oauth' == $do) {
	$oauthInfo = uni_setting_load();
	$oauth = $oauthInfo['oauth'];
	$jsoauth = $oauthInfo['jsauth_acid'];
	if (!empty($oauth['account'])) {
		$oauth_accounts[$oauth['account']] = table('account_wechats')->where('uniacid', $oauth['account'])->getcolumn('name');
	}
	if (!empty($jsoauth)) {
		$jsoauth_accounts[$jsoauth] = table('account_wechats')->where('uniacid', $jsoauth)->getcolumn('name');
	}
}

if ('oauth_accounts' == $do) {
	$page = !empty($_GPC['page']) ? safe_gpc_int($_GPC['page']) : 1;
	$page_size = 21;
	$where = array(
		'a.isdeleted !=' => '1',
		't.level' => $_GPC['type'] == 'oauth' ? ACCOUNT_SERVICE_VERIFY : array(ACCOUNT_SERVICE_VERIFY, ACCOUNT_SUBSCRIPTION_VERIFY),
		't.key !=' => '',
	);
	if (!empty($_GPC['keyword'])) {
		$where['t.name LIKE'] = '%' . safe_gpc_string($_GPC['keyword']) . '%';
	}
	$account_wechats_table = table('account_wechats');
	if (!$_W['isadmin']) {
		$uid = $_W['uid'];
		if ($_W['isfounder']) {
			$founder_own_uids = table('users_founder_own_users')->getFounderOwnUsersList($uid);
			$uid = array_merge((array)$uid, $founder_own_uids);
		}
		$account_wechats_table->searchWithsearchWithAccountAndUniAccountUsers();
		$where['u.uid'] = $uid;
		$where['u.role'] = array(ACCOUNT_MANAGE_NAME_OPERATOR, ACCOUNT_MANAGE_NAME_MANAGER, ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER);
	} else {
		$account_wechats_table->searchWithAccount();
	}
	$list = $account_wechats_table
		->select(array('t.uniacid', 't.name', 'ua.logo'))
		->leftjoin('uni_account', 'ua')
		->on(array('ua.uniacid' => 't.uniacid'))
		->where($where)
		->page($page, $page_size)
		->getall();
	$total = $account_wechats_table->getLastQueryTotal();
	$message = array(
		'page' => $page,
		'page_size' => $page_size,
		'total' => $total,
		'list' => $list,
	);
	iajax(0, $message);
 }

template('profile/passport');