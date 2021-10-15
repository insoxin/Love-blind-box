<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');

$dos = array('module_groups', 'user_groups', 'get_module_group_detail_info', 'get_user_group_detail_info', 'get_users_create_group_detail_info', 'account_groups');
if (!in_array($do, $dos) || !$_W['isadmin']) {
	iajax(-1, '没有权限');
}

$keyword = safe_gpc_string($_GPC['keyword']);
$type = !empty($_GPC['type']) ? safe_gpc_string($_GPC['type']) : '';
if ($type == 'vice_founder' && !$_W['isadmin']) {
	iajax('-1', '权限不足');
}
if ('module_groups' == $do) {
	$uni_groups = uni_groups();
	if (empty($uni_groups)) {
		iajax(0, array());
	}
	if (!empty($keyword)) {
		foreach ($uni_groups as $key => $group) {
			if (!strstr($group['name'], $keyword)) {
				unset($uni_groups[$key]);
			}
		}
	}
	$page = max(1, intval($_GPC['page']));
	$page_size = !empty($_GPC['page_size']) ? safe_gpc_int($_GPC['page_size']) : 3;
	$current_uni_groups = array_slice($uni_groups, ($page - 1) * $page_size, $page_size);
	
	$message = array(
		'total' => count($uni_groups),
		'page' => $page,
		'page_size' => $page_size,
		'keyword' => $keyword,
		'list' => $current_uni_groups
	);
	iajax(0, $message);
}
if ('user_groups' == $do) {
	if ($type == 'vice_founder') {
		$groups = user_founder_group();
	} else {
		$groups = user_group();
	}
	if (empty($groups)) {
		iajax(0, array());
	}
	foreach ($groups as $key => $group) {
		if (!empty($keyword) && !strstr($group['name'], $keyword)) {
			unset($groups[$key]);
		}
		if ($group['timelimit'] == 0) {
			$groups[$key]['timelimit'] = '永久有效';
		} else {
			$groups[$key]['timelimit'] .= '天';
		}
		$groups[$key]['package'] = iunserializer($group['package']);
		$groups[$key]['package_num'] = is_array($groups[$key]['package']) ? count($groups[$key]['package']) : 0;
	}
	if (isset($_GPC['getall'])) {
		$current_groups = $groups;
	} else {
		$page = max(1, intval($_GPC['page']));
		$page_size = 3;
		$current_groups = array_slice($groups, ($page - 1) * $page_size, $page_size);
	}

	$message = array(
		'total' => count($groups),
		'page' => $page,
		'page_size' => $page_size,
		'keyword' => $keyword,
		'list' => $current_groups
	);
	iajax(0, $message);
}
if ('get_module_group_detail_info' == $do) {
	$module_group_id = intval($_GPC['id']);
	if (empty($module_group_id)) {
		iajax(-1, '参数有误');
	}
	$group = table('uni_group')->getById($module_group_id);
	$result = $group;
	unset($result['modules'], $result['templates']);
	if (!empty($group['modules'])) {
		$group['modules'] = iunserializer($group['modules']);
		$user_modules = user_modules();
		$module_support_type = module_support_type();
		$modules_all = array();
		foreach ($group['modules'] as $type => $module_name) {
			$modules_all = array_merge($modules_all, $module_name);
		}
		$modules_all = array_unique($modules_all);

		foreach ($user_modules as $name => $module) {
			if (!empty($module['issystem']) || !in_array($name, $modules_all)) {
				continue;
			}
			foreach ($module_support_type as $support => $info) {
				if (MODULE_SUPPORT_SYSTEMWELCOME_NAME == $support) {
					continue;
				}
				if ($module[$support] == $info['support']) {
					$result['modules_all'][] = array(
						'id' => $module['mid'],
						'name' => $module['name'],
						'title' => $module['title'],
						'logo' => $module['logo'],
						'support' => $info['type'],
					);
				}
			}
		}
	}
	if (!empty($group['templates'])) {
		$group['templates'] = iunserializer($group['templates']);
		$template_list = table('modules')->getAllTemplates();
		foreach ($template_list as $temp) {
			$result['templates'][] = array(
				'id' => $temp['id'],
				'name' => $temp['name'],
				'title' => $temp['title'],
				'logo' => $tem['logo'],
				'support' => '',
			);
		}
	}
	iajax(0, $result);
}
if ('get_user_group_detail_info' == $do) {
	$user_group_id = intval($_GPC['user_group_id']);
	if ($type == 'vice_founder') {
		$user_group_detail_info = user_founder_group_detail_info($user_group_id);
	} else {
		$user_group_detail_info = user_group_detail_info($user_group_id);
	}
	iajax(0, $user_group_detail_info);
}
if ('get_users_create_group_detail_info' == $do) {
	$users_create_group_id = intval($_GPC['id']);
	if (empty($users_create_group_id)) {
		iajax(-1, '参数有误');
	}
	$result = table('users_create_group')->getById($users_create_group_id);
	iajax(0, $result);
}
if ('account_groups' == $do) {
	$page = max(1, intval($_GPC['page']));
	$page_size = !empty($_GPC['page_size']) ? safe_gpc_int($_GPC['page_size']) : 3;
	$account_groups_table = table('users_create_group');
	if (!empty($keyword)) {
		$account_groups_table->where(array('group_name LIKE' => "%$keyword%"));
	}
	$account_groups = $account_groups_table->searchWithPage($page, $page_size)->getall();
	$total = $account_groups_table->getLastQueryTotal();
	$message = array(
		'total' => $total,
		'page' => $page,
		'page_size' => $page_size,
		'keyword' => $keyword,
		'list' => $account_groups
	);
	iajax(0, $message);
}