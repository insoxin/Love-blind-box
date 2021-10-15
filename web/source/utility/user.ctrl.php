<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('user');
$dos = array('browser', 'detail_info');
$do = in_array($do, $dos) ? $do: 'browser';

if ('browser' == $do) {
	$mode = in_array($_GPC['mode'], array('invisible','visible')) ? $_GPC['mode'] : 'visible';

	$callback = $_GPC['callback'];

	$uids = $_GPC['uids'];
	$uidArr = array();
	if(empty($uids)){
		$uids='';
	}else{
		foreach (explode(',', $uids) as $uid) {
			$uidArr[] = intval($uid);
		}
		$uids = implode(',', $uidArr);
	}
	$where = " WHERE status = '2' and type != '".ACCOUNT_OPERATE_CLERK."' AND founder_groupid != " . ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;
	if($mode == 'invisible' && !empty($uids)){
		$where .= " AND uid not in ( {$uids} )";
	}
	$params = array();
	if(!empty($_GPC['keyword'])) {
		$where .= ' AND `username` LIKE :username';
		$params[':username'] = '%' . safe_gpc_string($_GPC['keyword']) . '%';
	}
	if (user_is_vice_founder()) {
		$founder_users = table('users_founder_own_users')->getFounderOwnUsersList($_W['uid']);
		if (!empty($founder_users)) {
			$founder_users = implode(',', array_keys($founder_users));
			$where .= " AND `uid` in ($founder_users)";
		} else {
			exit('暂无用户');
		}
	}
	$page = max(1, intval($_GPC['page']));
	$page_size = 10;
	$total = 0;

	$list = pdo_fetchall("SELECT uid, groupid, username, remark FROM ".tablename('users')." {$where} ORDER BY `uid` LIMIT ".(($page - 1) * $page_size).",{$page_size}", $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('users'). $where , $params);

	$pager = pagination($total, $page, $page_size, '', array('ajaxcallback'=>'null','mode'=>$mode,'uids'=>$uids));
	if (!empty($list)) {
		$usergroups = array();
		$group_ids = array();
		foreach ($list as $item) {
			if (!empty($item['groupid']) && !in_array($item['groupid'], $group_ids)) {
				$group_ids[] = $item['groupid'];
			}
		}
		if (!empty($group_ids)) {
			$usergroups = table('users_group')->getAllById($group_ids);
		}
		foreach ($list as $key => $item) {
			$list[$key]['group_name'] = empty($usergroups[$item['groupid']]) ? '未分组' : $usergroups[$item['groupid']]['name'];
		}
	}
	if ($_W['isw7_request'] && $_W['isajax']) {
		iajax(0, array(
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size,
			'list' => $list,
		));
	}
	template('utility/user-browser');
	exit;
}

if ('detail_info' == $do) {
	if (!$_W['isfounder']) {
		iajax(-1, '非法请求数据！');
	}
	$sign = $_GPC['sign'];
	$uid = intval($_GPC['uid']);
	if (empty($uid)) {
		$uid = intval($_GPC['uid'][0]);
	}
	$user = user_single(array('uid' => $uid));

	if (empty($user)) {
		iajax(-1, '用户不存在或是已经被删除', '');
	}
	$user['group'] = user_group_detail_info($user['groupid']);
	$user['endtime'] = user_end_time($user['uid']);
	$user['modules'] = array();
	$user['package'] = empty($user['group']['package']) ? array() : iunserializer($user['group']['package']);
	
	$user_modules = user_modules($user['uid']);
	if (!empty($user_modules)) {
		foreach ($user_modules as $module) {
			if (1 != $module['issystem'] && (MODULE_SUPPORT_ACCOUNT == $module[$sign . '_support']) && !empty($sign)) {
				$user['modules'][] = $module;
			}
		}
	}
	iajax(0, $user);
}