<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');
$dos = array('display', 'post', 'del');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ('display' == $do) {
	$pageindex = max(1, intval($_GPC['page']));
	$pagesize = 10;

	$condition = '';
	$params = array();
	$keyword = safe_gpc_string($_GPC['keyword']);
	if (!empty($keyword)) {
		$condition .= 'WHERE name LIKE :name';
		$params[':name'] = "%{$keyword}%";
	}
	$lists = pdo_fetchall('SELECT * FROM ' . tablename('users_founder_group') . $condition . ' LIMIT ' . ($pageindex - 1) * $pagesize . ',' . $pagesize, $params);
	$lists = user_group_format($lists);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users_founder_group') . $condition, $params);
	$pager = pagination($total, $pageindex, $pagesize);
	template('founder/group');
}

if ('post' == $do) {
	$user_type = 'vice_founder';
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$group_info = pdo_get('users_founder_group', array('id' => $id));
		$group_info['package'] = iunserializer($group_info['package']);
		if (!empty($group_info['package']) && in_array(-1, $group_info['package'])) {
			$group_info['check_all'] = true;
		}else {
			$checked_groups = pdo_getall('uni_group', array('uniacid' => 0, 'id' => $group_info['package']), array('id', 'name'), '', array('id DESC'));
		}
	}
	$packages = uni_groups();
	if (!empty($packages)) {
		foreach ($packages as $uni_group_id => &$package_info) {
			if (!empty($group_info['package']) && in_array($uni_group_id, $group_info['package'])) {
				$package_info['checked'] = true;
			} else {
				$package_info['checked'] = false;
			}
		}
		unset($package_info);
		$packages = array_values($packages);
	}

	$pagesize = 15;
	$pager = pagination(count($packages), 1, $pagesize, '', array('ajaxcallback' => true, 'callbackfuncname' => 'loadMore'));
	template('user/group-post');
}

if ('save' == $do) {
	if ($_W['ispost']) {
		$account_all_type = uni_account_type();
		$account_all_type_sign = array_keys(uni_account_type_sign());
		$founder_user_group = array(
			'id' => safe_gpc_int($_GPC['id']),
			'name' => safe_gpc_string($_GPC['name']),
			'package' => safe_gpc_array($_GPC['package']),
			'timelimit' => safe_gpc_int($_GPC['timelimit']),
		);
		foreach ($account_all_type_sign as $account_type) {
			$maxtype = 'max' . $account_type;
			$founder_user_group[$maxtype] = safe_gpc_int($_GPC[$maxtype]);
		}

		if (!empty($founder_user_group['package'])) {
			$user_group_package = empty($founder_user_group['package']) ? array() : $founder_user_group['package'];
			if (!empty($founder_user_group['id'])) {
				$group_info = pdo_get('users_founder_group', array('id' => $founder_user_group['id']), array('package'));
				$group_info['package'] = empty($group_info['package']) ? array() : iunserializer($group_info['package']);
				$user_group_package = array_diff($founder_user_group['package'], $group_info['package']);
			}
			if (!empty($user_group_package)) {
				$modules_name = module_name_list($user_group_package);
				$module_expired_list = module_expired_list();
				if (is_error($module_expired_list)) {
					if ($_W['isajax']) {
						iajax(-1, $module_expired_list['message'], referer());
					}
					itoast($module_expired_list['message'], '', '');
				}
				if (!empty($module_expired_list)) {
					$expired_modules = array_intersect($module_expired_list, $modules_name);
					if ($expired_modules) {
						$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
						$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
						if ($_W['isajax']) {
							iajax(-1, '应用：' . $expired_modules_name . '，服务费到期，无法添加！', referer());
						}
						itoast('应用：' . $expired_modules_name . '，服务费到期，无法添加！', '', '');
					}
				}
			}
		}

		$user_group = user_save_founder_group($founder_user_group);
		if (is_error($user_group)) {
			if ($_W['isajax']) {
				iajax(-1, $user_group['message']);
			}
			itoast($user_group['message'], '', '');
		}
		cache_clean(cache_system_key('user_modules'));
		if ($_W['isajax']) {
			iajax(0, '用户组更新成功！');
		}
		itoast('用户组更新成功！', url('founder/group'), 'success');
	}
}

if ('del' == $do) {
	$id = intval($_GPC['id']);
	$result = pdo_delete('users_founder_group', array('id' => $id));
	if (!empty($result)) {
		if ($_W['isajax']) {
			iajax(0, '删除成功！');
		}
		itoast('删除成功！', url('founder/group/'), 'success');
	} else {
		if ($_W['isajax']) {
			iajax(-1, '删除失败！请稍候重试！');
		}
		itoast('删除失败！请稍候重试！', url('founder/group'), 'error');
	}
	exit;
}
