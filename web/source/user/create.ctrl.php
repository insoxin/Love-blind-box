<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('user');

$dos = array('post', 'save', 'get_user_group_detail_info', 'check_vice_founder_exists', 'check_user_info', 'check_vice_founder_permission_limit');
$do = in_array($do, $dos) ? $do : 'post';

$uni_account_types = uni_account_type();
$uni_account_type_signs = array_keys(uni_account_type_sign());
foreach ($uni_account_type_signs as $type_sign_name) {
	$max_account_type_signs['max' . $type_sign_name] = 0;
}

if ('post' == $do) {
	$user_type = 'user';
		$groups = user_group();

		$modules = user_modules();
	$module_support_type = module_support_type();
	$user_modules = array('modules' => array(), 'templates' => array());
	if (!empty($modules)) {
		foreach ($modules as $item) {
			if (1 == $item['issystem']) {
				continue;
			}
			foreach ($module_support_type as $module_support_type_key => $module_support_type_val) {
				if ($item[$module_support_type_key] == $module_support_type_val['support']) {
					$item['support'] = $module_support_type_key;
					$item['checked'] = 0;
					$user_modules['modules'][] = $item;
				}
			}
		}
	}

		$modules_group_list = uni_groups();

		if (user_is_vice_founder()) {
		$users_founder_own_create_groups_table = table('users_founder_own_create_groups');
		$account_group_lists = $users_founder_own_create_groups_table->getallGroupsByFounderUid($_W['uid']);
	} else {
		$account_group_table = table('users_create_group');
		$account_group_lists = $account_group_table->getCreateGroupList();
	}

	$create_account = array(
		'create_groups' => $account_group_lists,
		'create_numbers' => $max_account_type_signs,
	);
	template('user/post');
}

if ('save' == $do) {
	$user_info = array(
		'username' => safe_gpc_string($_GPC['username']),
		'password' => $_GPC['password'],
		'repassword' => $_GPC['repassword'],
		'remark' => safe_gpc_string($_GPC['remark']),
		'starttime' => TIMESTAMP,
	);

	if (user_is_vice_founder()) {
		$user_info['owner_uid'] = $_W['uid'];
	}

	$user_add = user_info_save($user_info);
	if (is_error($user_add)) {
		iajax(-1, $user_add['message'], url('user/display'));
	}
	$uid = $user_add['uid'];
	$module_expired_list = module_expired_list();
	if (is_error($module_expired_list)) {
		iajax(-1, $module_expired_list['message'], 'user/display');
	}
	if (!empty($_GPC['vice_founder_name'])) {
		$vice_founder_name = safe_gpc_string($_GPC['vice_founder_name']);
		$vice_founder_info = user_single(array('username' => $vice_founder_name));
		if (empty($vice_founder_info)) {
			iajax(-1, '副创始人不存在！', 'user/display');
		}
		if (!user_is_vice_founder($vice_founder_info['uid'])) {
			iajax(-1, '请勿添加非副创始人姓名！', 'user/display');
		}
		$user_modules_info = user_modules($vice_founder_info['uid']);
		$user_modules = array_keys($user_modules_info);
		if (!empty($user_modules)) {
			$expired_modules = array_intersect($module_expired_list, $user_modules);
			if ($expired_modules) {
				$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
				$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
				iajax(-1, '副创始人 ' .$vice_founder_info['username']. ' 的应用：' . $expired_modules_name . '，服务费到期，无法添加！', 'user/display');
			}
		}
	}
	if (!empty($vice_founder_name) && !empty($vice_founder_info)) {
		$vice_founder_uid = $vice_founder_info['uid'];
	} else {
		$vice_founder_uid = !user_is_vice_founder($_W['uid']) ? 0 : $_W['uid'];
	}

	if (!empty($_GPC['vice_founder_name']) && !empty($vice_founder_uid)) {
		$founder_own_user_table = table('users_founder_own_users');
		$founder_own_add_res = $founder_own_user_table->addOwnUser($uid, $vice_founder_uid);
		if (!$founder_own_add_res) {
			iajax('-1', '添加副创始人失败', 'user/display');
		}
	}

	$user_update['groupid'] = intval($_GPC['groupid']) ? intval($_GPC['groupid']) : 0;
	if (0 == $user_update['groupid']) {
		$user_update['endtime'] = empty($_GPC['timelimit']) ? TIMESTAMP : strtotime(intval($_GPC['timelimit']) . ' days', TIMESTAMP);
	}
	pdo_update('users', $user_update, array('uid' => $uid));
	user_related_update($uid, $user_update);

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
		foreach ($_GPC['uni_groups'] as $uni_group_key => $uni_group_id) {
			$uni_group_exists = $ext_group_table->getUniGroupByUidAndGroupid($uid, $uni_group_id);
			if (!$uni_group_exists) {
				$res = $ext_group_table->addExtraUniGroup($uid, $uni_group_id);
				if (!$res) {
					iajax('-1', '添加应用权限组失败!', 'user/display');
				}
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
			if (!$extra_module_exists) {
				$res = $extra_modules_table->addExtraModule($uid, $module_val['name'], $module_val['support']);
				if (!$res) {
					iajax('-1', '添加附加模块失败!', 'user/display');
				}
			}
		}
	}

	if (!empty($_GPC['templates'])) {
		$extra_template_table = table('users_extra_templates');
		foreach ($_GPC['templates'] as $template_key => $template_id) {
			$extra_template_exists = $extra_template_table->getExtraTemplateByUidAndTemplateid($uid, $template_id);
			if (!$extra_template_exists) {
				$res = $extra_template_table->addExtraTemplate($uid, $template_id);
				if (!$res) {
					iajax('-1', '添加附加模板失败!', 'user/display');
				}
			}
		}
	}

	if (!empty($_GPC['create_account_groups'])) {
		$ext_group_table = table('users_extra_group');
		foreach ($_GPC['create_account_groups'] as $create_account_group_id) {
			$create_account_group_exists = $ext_group_table->getCreateGroupByUidAndGroupid($uid, $create_account_group_id);
			if (!$create_account_group_exists) {
				$res = $ext_group_table->addExtraCreateGroup($uid, $create_account_group_id);
				if (!$res) {
					iajax('-1', '添加账户权限组失败!', 'user/display');
				}
			}
		}
	}

	if (!empty($_GPC['create_account_nums']) || !empty($_GPC['timelimit'])) {
		$extra_limit_table = table('users_extra_limit');
		$extra_limit_exists = $extra_limit_table->getExtraLimitByUid($uid);
		foreach ($max_account_type_signs as $type_sign_name => $type_sign_val) {
			$data[$type_sign_name] = intval($_GPC['create_account_nums'][$type_sign_name]);
		}

		if ($extra_limit_exists) {
			$data['uid'] = $uid;
		}

		$res = $extra_limit_table->saveExtraLimit($data, $uid);
		if (!$res) {
			iajax('-1', '添加附加账户数量失败!', 'user/display');
		}
	}

	if (!empty($_GPC['timelimit'])) {
		$extra_limit_table = table('users_extra_limit');
		$extra_limit_exists = $extra_limit_table->getExtraLimitByUid($uid);
		$data = array(
			'timelimit' => intval($_GPC['timelimit']),
		);

		if ($extra_limit_exists) {
			$data['uid'] = $uid;
		}
		$extra_limit_add_res = $extra_limit_table->saveExtraLimit($data, $uid);
		if (!$extra_limit_add_res) {
			iajax('-1', '添加有效时间失败', 'user/display');
		}
	}

	iajax(0, '添加成功', url('user/display'));
}

if ('get_user_group_detail_info' == $do) {
	$user_group_id = intval($_GPC['user_group_id']);
	$user_group_detail_info = user_group_detail_info($user_group_id);
	iajax(0, $user_group_detail_info);
}

if ('check_vice_founder_exists' == $do) {
	$vice_founder_name = safe_gpc_string($_GPC['vice_founder_name']);
	$vice_founder_info = user_single(array('username' => $vice_founder_name));
	if (empty($vice_founder_info)) {
		iajax(-1, '副创始人不存在！', url('user/create'));
	}
	if (!user_is_vice_founder($vice_founder_info['uid'])) {
		iajax(-1, '请勿添加非副创始人姓名！', url('user/create'));
	}
	iajax(0, '');
}

if ('check_user_info' == $do) {
	$user = $_GPC['user'];
	$user['username'] = safe_gpc_string($user['username']);
	$check_result = user_info_check($user);
	iajax($check_result['errno'], $check_result['message'], url('user/create'));
}

if ('check_vice_founder_permission_limit' == $do) {
	if (user_is_vice_founder()) {
		$timelimit['timelimit'] = $_GPC['timelimit'];
		$create_account_nums = $_GPC['create_account_nums'];
		if (USER_CREATE_PERMISSION_GROUP_TYPE == $_GPC['permissionType']) {
			iajax(0, '权限正确');
		}
		if (empty($create_account_nums)) {
			$create_account_nums = array();
		}
		$check_result = permission_check_vice_founder_limit(array_merge($timelimit, $create_account_nums));
		if (is_error($check_result)) {
			iajax(-1, $check_result['message']);
		} else {
			iajax(0, '权限正确');
		}
	} else {
		iajax(0, '权限错误');
	}
}