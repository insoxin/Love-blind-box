<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$dos = array('post', 'save', 'get_user_founder_group_detail_info');
$do = in_array($do, $dos) ? $do : 'post';

$is_used = safe_gpc_string($_GPC['is_used']);
$groups = user_founder_group();
$modules = user_modules($_W['uid']);
$modules = array_filter($modules, function ($module) {
	return empty($module['issystem']);
});
$templates = table('modules')->getAllTemplates();

$user_extra_modules = table('users_extra_modules')->getExtraModulesByUid($uid);

$module_support_type = module_support_type();
$user_modules = array('modules' => array(), 'templates' => array());
if (!empty($modules)) {
	foreach ($modules as $item) {
		if (0 == $item['issystem']) {
			foreach ($module_support_type as $module_support_type_key => $module_support_type_val) {
				if ($item[$module_support_type_key] == $module_support_type_val['support']) {
					$item['support'] = $module_support_type_key;
					$item['checked'] = 0;
					$user_modules['modules'][] = $item;
				}
			}
		}
	}
}

if (user_is_vice_founder($_W['uid'])) {
	$founder_group_info = user_founder_group_detail_info($_W['user']['groupid']);
	$modules_group_list = $founder_group_info['package_detail'];
} else {
	$uni_group_table = table('uni_group');
	$uni_group_table->searchWithUid();
	$modules_group_list = $uni_group_table->getUniGroupList();
}

if (!empty($modules_group_list)) {
	foreach ($modules_group_list as $key => $value) {
		$modules = (array) iunserializer($value['modules']);
				$modules_all = array();
		if (!empty($modules)) {
			foreach ($modules as $type => $modulenames) {
				if (empty($modulenames) || !is_array($modulenames)) {
					continue;
				}
				foreach ($modulenames as $name) {
					$modules_all[] = $name;
				}
			}
		}
		$modules_all = array_unique($modules_all);

		$module_support = array();
		foreach ($module_support_type as $support => $info) {
			if (MODULE_SUPPORT_SYSTEMWELCOME_NAME == $support) {
				continue;
			}
			if (MODULE_SUPPORT_ACCOUNT_NAME == $support) {
				$info['type'] = 'modules';
			}
			if (empty($modules[$info['type']])) {
				continue;
			}
			foreach ($modules[$info['type']] as $modulename) {
				$module_support[$modulename][$support] = $info['support'];
			}
		}

		foreach ($modules_all as $name) {
			$module = module_fetch($name);
			if (empty($module)) {
				continue;
			}

			$module['group_support'] = $module_support[$name];
			$modules_group_list[$key]['modules_all'][] = $module;
		}

		$templates = (array) iunserializer($value['templates']);
		$modules_group_list[$key]['template_num'] = !empty($templates) ? count($templates) : 0;
		$modules_group_list[$key]['templates'] = table('modules')->getAllTemplateByIds($templates);
	}
}

$uni_account_types = uni_account_type();
$uni_account_type_signs = array_keys(uni_account_type_sign());
foreach ($uni_account_type_signs as $type_sign_name) {
	$max_account_type_signs['max' . $type_sign_name] = 0;
}

if ('post' == $do) {
	$user_type = 'vice_founder';
		$account_group_table = table('users_create_group');
	$account_group_lists = $account_group_table->getCreateGroupList();

	$user_extra_limits = table('users_extra_limit')->getExtraLimitByUid($uid);
	$create_account = array(
		'create_groups' => $account_group_lists,
		'create_numbers' => !empty($user_extra_limits) ? $user_extra_limits : $max_account_type_signs,
	);
	template('user/post');
}

if ('save' == $do) {
	$user = array(
		'username' => safe_gpc_string($_GPC['username']),
		'password' => $_GPC['password'],
		'repassword' => $_GPC['repassword'],
		'remark' => safe_gpc_string($_GPC['remark']),
	);
	$user_info = user_single(array('username' => $user['username']));

	if ('used' == $is_used && empty($user_info)) {
		if ($_W['isajax']) {
			iajax(-1, '用户不存在!');
		}
		itoast('用户不存在!', '', 'error');
	}

	if (!$_W['isfounder']) {
		if ($_W['isajax']) {
			iajax(-1, '没有权限!');
		}
		itoast('没有权限!', '', 'error');
	}

	$user_founder = $user;
	$user_founder['founder_groupid'] = ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;

	if ('used' == $is_used) {
		unset($user_founder['repassword']);
		unset($user_founder['password']);
		$user_save_result = pdo_update('users', $user_founder, array('uid' => $user_info['uid']));
		if (is_error($user_save_result)) {
			iajax(-1, $user_save_result['message'], url('user/display'));
		}
		$uid = $user_info['uid'];
	} else {
		$user_founder['starttime'] = TIMESTAMP;
		$user_save_result = user_info_save($user_founder, true);
		if (is_error($user_save_result)) {
			iajax(-1, $user_save_result['message'], url('user/display'));
		}
		$uid = $user_save_result['uid'];
	}

	$user_update['groupid'] = safe_gpc_int($_GPC['groupid']) ? safe_gpc_int($_GPC['groupid']) : 0;
	if (0 == $user_update['groupid']) {
		$user_update['endtime'] = empty($_GPC['timelimit']) ? USER_ENDTIME_GROUP_DELETE_TYPE : strtotime(intval($_GPC['timelimit']) . ' days', TIMESTAMP);
	}
	pdo_update('users', $user_update, array('uid' => $uid));
	user_related_update($uid, $user_update);

	if (!empty($_GPC['templates'])) {
		$extra_template_table = table('users_extra_templates');
		foreach ($_GPC['templates'] as $template_key => $template_val) {
			$extra_template_exists = $extra_template_table->getExtraTemplateByUidAndTemplateid($uid, $template_val['id']);
			if ($extra_template_exists) {
				continue;
			}
			$res = $extra_template_table->addExtraTemplate($uid, $template_val['id']);
			if (!$res) {
				iajax('-1', '添加附加模板失败!', 'founder/display');
			}
		}
	}

	if (!empty($_GPC['create_account_groups'])) {
		$ext_group_table = table('users_extra_group');
		foreach ($_GPC['create_account_groups'] as $create_account_group_val) {
			$create_account_group_exists = $ext_group_table->getCreateGroupByUidAndGroupid($uid, $create_account_group_val['id']);
			if ($create_account_group_exists) {
				continue;
			}
			$res = $ext_group_table->addExtraCreateGroup($uid, $create_account_group_val['id']);
			if (!$res) {
				iajax('-1', '添加账户权限组失败!', 'founder/display');
			}
		}
	}

	if (!empty($_GPC['create_account_nums']) || !empty($_GPC['timelimit'])) {
		$extra_limit_table = table('users_extra_limit');
		$extra_limit_exists = $extra_limit_table->getExtraLimitByUid($uid);
		foreach ($max_account_type_signs as $type_sign_name => $type_sign_val) {
			$data[$type_sign_name] = intval($_GPC['create_account_nums'][$type_sign_name]);
		}
		$data['timelimit'] = intval($_GPC['timelimit']);
		if ($extra_limit_exists) {
			$data['uid'] = $uid;
		}

		$res = $extra_limit_table->saveExtraLimit($data, $uid);
		if (is_error($res)) {
			iajax('-1', '添加附加账户数量及有效时间失败!', 'founder/display');
		}
	}

	$module_expired_list = module_expired_list();
	if (is_error($module_expired_list)) {
		iajax(-1, $module_expired_list['message'], 'user/display');
	}

	if (!empty($_GPC['uni_groups'])) {
		$_GPC['uni_groups'] = safe_gpc_array($_GPC['uni_groups']);
		$modules = uni_groups($_GPC['uni_groups']);
		$module_name = array_reduce(array_column($modules, 'modules_all'), 'array_merge', array());
		if (!empty($module_expired_list)) {
			$expired_modules = array_intersect($module_expired_list, array_keys($module_name));
			if ($expired_modules) {
				$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
				$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
				iajax('-1', '应用：' . $expired_modules_name . '，服务费到期，无法添加！', 'user/display');
			}
		}

		$ext_group_table = table('users_extra_group');
		foreach ($_GPC['uni_groups'] as $uni_group_key => $uni_group_val) {
			$uni_group_exists = $ext_group_table->getUniGroupByUidAndGroupid($uid, $uni_group_val['id']);
			if ($uni_group_exists) {
				continue;
			}
			$res = $ext_group_table->addExtraUniGroup($uid, $uni_group_val['id']);
			if (!$res) {
				iajax('-1', '添加应用权限组失败!', 'user/display');
			}
		}
	}

	if (!empty($_GPC['modules'])) {
		$_GPC['modules'] = safe_gpc_array($_GPC['modules']);
		$module_name = array_column($_GPC['modules'], 'name');
		if (!empty($module_expired_list)) {
			$expired_modules = array_intersect($module_expired_list, $module_name);
			if ($expired_modules) {
				$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
				$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
				iajax('-1', '应用：' . $expired_modules_name . '，服务费到期，无法添加！', 'user/display');
			}
		}

		$extra_modules_table = table('users_extra_modules');
		foreach ($_GPC['modules'] as $module_key => $module_val) {
			$extra_modules_table->searchByUid($uid);
			$extra_modules_table->searchBySupport($module_val['support']);
			$extra_modules_table->searchByModuleName($module_val['name']);
			$extra_module_exists = $extra_modules_table->get();
			if ($extra_module_exists) {
				continue;
			}
			$res = $extra_modules_table->addExtraModule($uid, $module_val['name'], $module_val['support']);
			if (!$res) {
				iajax('-1', '添加附加模块失败!', 'user/display');
			}
		}
	}

	iajax(0, '操作成功', url('founder/display'));
}

if ('get_user_founder_group_detail_info' == $do) {
	$user_group_id = safe_gpc_int($_GPC['user_group_id']);
	$user_group_detail_info = user_founder_group_detail_info($user_group_id);
	iajax(0, $user_group_detail_info);
}

