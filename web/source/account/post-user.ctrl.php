<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
load()->model('system');

$dos = array('delete', 'edit', 'set_permission', 'save_permission', 'set_manager', 'module');
$do = in_array($do, $dos) ? $do : 'edit';

$uniacid = intval($_GPC['uniacid']);
if (empty($uniacid)) {
	if ($_W['isajax']) {
		iajax(-1, '请选择要编辑的平台账号');
	}
	itoast('请选择要编辑的平台账号', referer(), 'error');
}
$_W['role'] = permission_account_user_role($_W['uid'], $uniacid);
$role_permission = in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_MANAGER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER));
if (!$role_permission) {
	if ($_W['isajax']) {
		iajax(-1, '无权限操作');
	}
	itoast('无权限操作！', referer(), 'error');
}
$founders = explode(',', $_W['config']['setting']['founder']);
$founder_info = pdo_getcolumn('users', array('uid' => current($founders)), 'username');
$account = uni_fetch($uniacid);
$_W['breadcrumb'] = $account['name'];
$headimgsrc = tomedia('headimg_' . $account['acid'] . '.jpg');
if (is_error($account)) {
	itoast($account['message']);
}

if ('edit' == $do) {
	$account_belong_user = pdo_getcolumn('uni_account_users', array('uniacid' => $uniacid,'role' => 'owner'), 'uid');
	$permissions = pdo_fetchall('SELECT id, uid, role FROM ' . tablename('uni_account_users') . " WHERE uniacid = '$uniacid' ORDER BY uid ASC, role DESC", array(), 'uid');
	if (!empty($permissions)) {
		$member = pdo_fetchall('SELECT username, uid FROM ' . tablename('users') . ' WHERE uid IN (' . implode(',', array_keys($permissions)) . ')', array(), 'uid');
		if (!empty($member)) {
			$operator = $manager = $owner = $vice_founder = array();
			foreach ($permissions as $key => $per_val) {
				$per_val['isfounder'] = in_array($member[$key]['uid'], $founders) ? 1 : 0;
				$per_val['username'] = $member[$key]['username'] ? $member[$key]['username'] : '';
				switch ($per_val['role']) {
					case 'vice_founder':
						$vice_founder = $per_val;
						break;
					case 'owner':
						$owner = $per_val;
						break;
					case 'manager':
						$manager[] = $per_val;
						break;
					case 'operator':
						$operator[] = $per_val;
						break;
					default:
						break;
				}
			}
		}
	}
	if ($_W['isajax']) {
		iajax(0, array(
			'founder_info' => $founder_info,
			'vice_founder' => empty($vice_founder) ? '' : $vice_founder,
			'owner' => empty($owner) ? '' : $owner,
			'manager' => empty($manager) ? '' : $manager,
			'operator' => empty($operator) ? '' : $operator,
			'create_user_url' => $_W['siteroot'] . 'web' . ltrim(url('user/create'), '.'),
		));
	}
	template('account/manage-users');
}

if ('delete' == $do) {
	$uid = intval($_GPC['uid']);
	if (empty($uid)) {
		iajax(-1, '请选择要删除的用户！');
	}
	$data = array(
		'uniacid' => $uniacid,
		'uid' => $uid,
	);
	$exists = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'uid' => $uid));
	if (empty($exists)) {
		iajax(-1, '该平台账号下不存在该用户！');
	}

	if (ACCOUNT_MANAGE_NAME_MANAGER == $_W['role'] && (ACCOUNT_MANAGE_NAME_OWNER == $exists['role'] || ACCOUNT_MANAGE_NAME_MANAGER == $exists['role'])) {
		iajax(-1, '管理员不可操作其他管理员');
	}
	$result = pdo_delete('uni_account_users', $data);
	if ($result) {
		if (ACCOUNT_MANAGE_NAME_OPERATOR == $exists['role']) {
			pdo_delete('users_permission', $data);
		}
		pdo_delete('users_lastuse', $data);
		pdo_delete('users_operate_history', $data);
		iajax(0, '删除成功！', referer());
	} else {
		iajax(-1, '删除失败，请重试');
	}
}

if ('set_manager' == $do) {
	$username = safe_gpc_string($_GPC['username']);
	$user = user_single(array('username' => $username));

	if (!empty($user)) {
		if (2 != $user['status']) {
			iajax(3, '用户未通过审核或不存在！', '');
		}

		
			if (user_is_vice_founder($_W['uid'])) {
				$owner_user_uids = table('users_founder_own_users')->getFounderOwnUsersList($_W['uid']);
				if (!in_array($user['uid'], array_keys($owner_user_uids))) {
					iajax(-1, '对不起您不能操作该用户，请重新选择');
				}
			}
		
		if (ACCOUNT_OPERATE_CLERK == $user['type']) {
			iajax(-1, '该用户是应用操作员，不能成为账号的操作员!', '');
		}

		$addtype = intval($_GPC['addtype']);

		if (in_array($addtype, array(ACCOUNT_MANAGE_TYPE_OWNER, ACCOUNT_MANAGE_NAME_FOUNDER))) {
			$user_modules_info = user_modules($user['uid']);
			$user_modules = array_keys($user_modules_info);
			if (!empty($user_modules)) {
				$module_expired_list = module_expired_list();
				if (is_error($module_expired_list)) {
					iajax(-1, $module_expired_list['message']);
				}
				$expired_modules = array_intersect($module_expired_list, $user_modules);
				if ($expired_modules) {
					$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
					$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
					iajax(-1, '用户 ' . $username . ' 的应用：' . $expired_modules_name . '，服务费到期，无法修改！', referer());
				}
			}
		}

		if (ACCOUNT_MANAGE_TYPE_OWNER == $addtype) {
			$user_end_time = user_end_time($user['uid']);
			$account_end_time = 0 == $user_end_time ? 0 : strtotime($user_end_time);
			pdo_update('account', array('endtime' => $account_end_time), array('uniacid' => $uniacid));
		}

		if (!in_array($addtype, array(ACCOUNT_MANAGE_TYPE_OPERATOR, ACCOUNT_MANAGE_TYPE_MANAGER, ACCOUNT_MANAGE_TYPE_OWNER, ACCOUNT_MANAGE_TYPE_VICE_FOUNDER))) {
			iajax(-1, '添加使用者类型有误，只能添加操作员/管理员/主管理员/副创始人！');
		}
		if (in_array($user['uid'], $founders)) {
			if (ACCOUNT_MANAGE_TYPE_OWNER == $addtype) {
				pdo_delete('uni_account_users', array('uniacid' => $uniacid, 'role' => ACCOUNT_MANAGE_NAME_OWNER));
				iajax(0, '修改成功！', '');
			}
			iajax(1, '不可操作网站创始人！', '');
		}
				if (ACCOUNT_MANAGE_TYPE_OWNER == $addtype && !in_array($_W['uid'], $founders)) {
			$creat_limit = permission_user_account_num($user['uid']);
			if ($creat_limit[TYPE_SIGN . '_limit'] <= 0) {
				itoast(error(5, ACCOUNT_TYPE_NAME . '创建数量已达上限'), '', 'error');
			}
		}

		$data = array(
			'uniacid' => $uniacid,
			'uid' => $user['uid'],
		);

		$exists = pdo_get('uni_account_users', $data);
		if (empty($exists)) {
			
				if (ACCOUNT_MANAGE_TYPE_VICE_FOUNDER == $addtype) {
					if (ACCOUNT_MANAGE_GROUP_VICE_FOUNDER != $user['founder_groupid']) {
						iajax(6, '副创始人不存在！', '');
					}
					pdo_delete('uni_account_users', array('uniacid' => $uniacid, 'role' => ACCOUNT_MANAGE_NAME_VICE_FOUNDER));
					$data['role'] = ACCOUNT_MANAGE_NAME_VICE_FOUNDER;
				}
			
			if (ACCOUNT_MANAGE_TYPE_OWNER == $addtype) {
				if (ACCOUNT_MANAGE_NAME_MANAGER == $_W['role']) {
					iajax(4, '管理员不可操作主管理员', '');
				}
				$vice_founder = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => ACCOUNT_MANAGE_NAME_VICE_FOUNDER));
				if (!empty($vice_founder) && $user['owner_uid'] != $vice_founder['uid']) {
					iajax(-1, '只能修改平台副创始人下的用户为主管理员!');
				}
				$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'));
				if (empty($owner)) {
					$data['role'] = ACCOUNT_MANAGE_NAME_OWNER;
				} else {
					$result = pdo_update('uni_account_users', $data, array('id' => $owner['id']));
					if ($result) {
												pdo_delete('users_permission', array('uniacid' => $uniacid, 'uid' => $user['uid']));
						cache_clean(cache_system_key('user_accounts'));
						cache_build_account_modules($uniacid, $user['uid']);
						iajax(0, '修改成功！', '');
					} else {
						iajax(1, '修改失败！', '');
					}
					exit;
				}
			} elseif (ACCOUNT_MANAGE_TYPE_MANAGER == $addtype) {
				if (ACCOUNT_MANAGE_NAME_MANAGER == $_W['role']) {
					iajax(4, '管理员不可操作管理员', '');
				}
				$data['role'] = ACCOUNT_MANAGE_NAME_MANAGER;
			} elseif (ACCOUNT_MANAGE_TYPE_OPERATOR == $addtype) {
				$data['role'] = ACCOUNT_MANAGE_NAME_OPERATOR;
			}
			pdo_delete('uni_account_users', array('uniacid' => $uniacid, 'uid' => $user['uid']));
			$result = pdo_insert('uni_account_users', $data);

			if ($result) {
				if (ACCOUNT_MANAGE_TYPE_OWNER == $addtype) {
					pdo_delete('users_permission', array('uniacid' => $uniacid, 'uid' => $user['uid']));
				}
				if (in_array($addtype, array(ACCOUNT_MANAGE_TYPE_MANAGER, ACCOUNT_MANAGE_TYPE_OPERATOR))) {
					permission_account_user_init($user['uid'], $uniacid);
				}
				cache_clean(cache_system_key('user_accounts'));
				cache_build_account_modules($uniacid, $user['uid']);
				iajax(0, '添加成功！', '');
			} else {
				iajax(1, '添加失败！', '');
			}
		} else {
			$permissions = table('users_permission')->getAllUserPermission($user['uid'], $uniacid);
			$modulelist = uni_modules();
			if (!empty($permissions)) {
				$i = 0;
				foreach ($permissions as $permission) {
					if ($i >= 3) {
						break;
					}
					if (!in_array($permission['type'], array_keys($modulelist))) {
						continue;
					}
					$module_info = module_fetch($permission['type']);
					$modules_str .= $module_info['title'] . ',';
					++$i ;
				}
			}

			if (empty($permissions) && !empty($exists)) {
				if (ACCOUNT_MANAGE_NAME_OPERATOR == $exists['role']) {
					$role_name = '操作员';
				} elseif (ACCOUNT_MANAGE_NAME_MANAGER == $exists['role']) {
					$role_name = '管理员';
				} elseif (ACCOUNT_MANAGE_NAME_OWNER == $exists['role']) {
					$role_name = '主管理员';
				} elseif (ACCOUNT_MANAGE_NAME_VICE_FOUNDER == $exists['role']) {
					$role_name = '副创始人';
				}

				iajax(2, "添加平台使用者失败, <a style='color:#3296fa'>" . $username . "<a/>, 已经是公众号的<a style='color:#3296fa'>" . $role_name . '<a/> ，请勿重复添加！', '');
			} else {
				iajax(2, "添加平台使用者失败, <a style='color:#3296fa'>" . $username . "<a/>, 已经是 <a style='color:#3296fa'>" . rtrim($modules_str, ',') . '</a>的应用操作员，请先取消其应用内操作员身份后再添加平台使用者！', '');
			}
		}
	} else {
		iajax(-1, '用户不存在!', '');
	}
}

if ('set_permission' == $do || 'save_permission' == $do) {
	$uid = intval($_GPC['uid']);
	$user = user_single(array('uid' => $uid));
	if (empty($user)) {
		if ($_W['isajax']) {
			iajax(-1, '您操作的用户不存在或是已经被删除');
		}
		itoast('您操作的用户不存在或是已经被删除！', '', '');
	}
	$role = permission_account_user_role($_W['uid'], $uniacid);
	if (empty($role)) {
		if ($_W['isajax']) {
			iajax(-1, '此用户没有操作该统一公众号的权限，请选指派“管理员”或是“操作员”权限！');
		}
		itoast('此用户没有操作该统一公众号的权限，请选指派“管理员”或是“操作员”权限！', '', '');
	}
	$module_permission = permission_account_user_menu($uid, $uniacid, 'modules');

	if ('all' == current($module_permission)) {
		$module_permission_keys = array();
		$module_permission_all = 1;
	} else {
		$module_permission_keys = array_keys($module_permission);
		$module_permission_all = 0;
	}
	$user_all_module_permission = table('users_permission')->getAllUserModulePermission($uid, $uniacid);
		if ($account->supportVersion) {
		$version_modules = array();
		$version_all = miniapp_version_all($uniacid);
		if (!empty($version_all)) {
			foreach ($version_all as $key => $version) {
				if (!empty($version['modules'])) {
					$version_modules = array_merge($version_modules, array_keys($version['modules']));
				}
			}
		}
		foreach ($version_modules as $version_module) {
			$module[$version_module] = module_fetch($version_module);
		}
	} else {
		$module = uni_modules_by_uniacid($uniacid);
	}
		$user_modules_permissions = array();
	if (!empty($module)) {
		foreach ($module as $module_name_key => $value) {
			if (1 == $value['issystem']) {
				unset($module[$module_name_key]);
				continue;
			}
			if (!empty($user_all_module_permission[$module_name_key]['permission'])) {
				$have_module_permission = explode('|', $user_all_module_permission[$module_name_key]['permission']);
			} else {
				$have_module_permission = array();
			}
			$user_modules_permissions[$module_name_key] = array('module_title' => $value['title'], 'permission' => array());
			$permissions = module_permission_fetch($module_name_key);
			if (empty($permissions)) {
				$user_modules_permissions[$module_name_key]['permission'] = (in_array('all', $have_module_permission) || in_array('all', $module_permission)) ? array('all') : array();
				continue;
			}
			foreach ($permissions as $permission_info) {
				$permission_info['checked'] = 0;
				if (in_array($permission_info['permission'], $have_module_permission) || in_array('all', $have_module_permission) || in_array('all', $module_permission)) {
					$permission_info['checked'] = 1;
				}
				$user_modules_permissions[$module_name_key]['permission'][] = $permission_info;
			}
		}
	}

	foreach ($account_all_type_sign as $account_type_sign => $account_type_info) {
		if (in_array($account['type'], $account_type_info['contain_type'])) {
			$account_type = 'account' == $account_type_sign ? 'system' : $account_type_sign;
		}
	}

	$user_menu_permissions = permission_account_user_menu($uid, $uniacid, $account_type);
	$menus = system_menu_permission_list($role);

	permission_account_user_init($user['uid'], $uniacid);

	if (in_array($account['type_sign'], array(ACCOUNT_TYPE_SIGN, WEBAPP_TYPE_SIGN))) {
		$account_type_menu = ACCOUNT_TYPE_SIGN;
	} elseif (in_array($account['type_sign'], array(ALIAPP_TYPE_SIGN, BAIDUAPP_TYPE_SIGN, TOUTIAOAPP_TYPE_SIGN))) {
		$account_type_menu = WXAPP_TYPE_SIGN;
	} else {
		$account_type_menu = $account['type_sign'];
	}

	if ('set_permission' == $do) {
		if ($_W['isajax']) {
			if (!empty($menus[$account_type_menu]) && !empty($menus[$account_type_menu]['section'])) {
				foreach ($menus[$account_type_menu]['section'] as $section_key => &$section) {
					if ($section_key == 'platform_module') {
						unset($menus[$account_type_menu]['section']['platform_module']);
						continue;
					}
					if (empty($section['menu'])) {
						continue;
					}
					foreach ($section['menu'] as &$menu) {
						
						if (in_array($menu['permission_name'], $user_menu_permissions)) {
							$menu['checked'] = 1;
						} else {
							$menu['checked'] = 0;
						}
						if (empty($menu['sub_permission'])) {
							continue;
						}
						foreach ($menu['sub_permission'] as &$sub_permission) {
							if (in_array($sub_permission['permission_name'], $user_menu_permissions)) {
								$sub_permission['checked'] = 1;
							} else {
								$sub_permission['checked'] = 0;
							}
						}
						unset($menu);
					}
					unset($sub_permission);
				}
				unset($section);
			}
			$result = array(
				'uni_modules_permission' => $user_modules_permissions,
				'menus' => $menus[$account_type_menu],
			);
			iajax(0, $result);
		}
		template('account/set-permission');
	}
}

if ('save_permission' == $do) {
	$user_menu_permission_new = array();
	if (!empty($_GPC['menus'])) {
		$all_menu_permission = permission_menu_name();
		foreach ($_GPC['menus'] as $permission_name) {
			if (in_array($permission_name, $all_menu_permission)) {
				$user_menu_permission_new[] = $permission_name;
			}
		}
		$data = array(
			'type' => $account_type,
			'permission' => implode('|', $user_menu_permission_new),
		);
		$result = permission_update_account_user($uid, $uniacid, $data);
		if (is_error($result)) {
			if ($_W['isajax']) {
				iajax(-1, $result['message']);
			}
			itoast($result['message']);
		}
	} else {
		$data = array(
			'type' => $account_type,
			'permission' => '|',
		);
		$result = permission_update_account_user($uid, $uniacid, $data);
	}
	if (is_error($result)) {
		if ($_W['isajax']) {
			iajax(-1, $result['message']);
		}
		itoast($result['message']);
	}
	
			
	$permission_names = "'" . implode(array(PERMISSION_ACCOUNT, PERMISSION_WXAPP, PERMISSION_WEBAPP, PERMISSION_PHONEAPP, PERMISSION_ALIAPP, PERMISSION_BAIDUAPP, PERMISSION_TOUTIAOAPP, PERMISSION_SYSTEM), "','") . "'";
	pdo_query('DELETE FROM ' . tablename('users_permission') . ' WHERE uniacid = :uniacid AND uid = :uid AND type not in (' . $permission_names . ')', array(':uniacid' => $uniacid, ':uid' => $uid));

	if (!empty($_GPC['module'])) {
		$module_names = array();
		foreach ($_GPC['module'] as $module_val) {
			$insert = array(
				'uniacid' => $uniacid,
				'uid' => $uid,
				'type' => $module_val,
			);
			if (!empty($module[$module_val])) {
				$module_names[] = $module_val;
			}
			if (1 == $_GPC[$module_val . '_select']) {
				$insert['permission'] = 'all';
				pdo_insert('users_permission', $insert);
				continue;
			} else {
				$data = array();
				foreach ($_GPC['module_' . $module_val] as $v) {
					$data[] = $v;
				}
				if (!empty($data)) {
					$insert['permission'] = implode('|', $data);
					pdo_insert('users_permission', $insert);
				}
			}
		}
		$module_names = implode(',', $module_names);
		pdo_query('DELETE FROM ' . tablename('users_operate_history') . ' WHERE uniacid = :uniacid AND uid = :uid AND module_name not in (:module_name)', array(':uniacid' => $uniacid, ':uid' => $uid, ':module_name' => $module_names));
	} else {
		$module_all_permission_data = array(
			'uid' => $uid,
			'uniacid' => $uniacid,
			'type' => 'modules',
		);
		$module_all_permission = pdo_get('users_permission', $module_all_permission_data);
		if (empty($module_all_permission)) {
			$module_all_permission_data['permission'] = 'none';
			pdo_insert('users_permission', $module_all_permission_data);
		}
	}
	cache_delete(cache_system_key('permission', array('uniacid' => $uniacid, 'uid' => $uid)));
	if ($_W['isajax']) {
		iajax(0, '操作菜单权限成功!');
	}
	itoast('操作菜单权限成功！', referer(), 'success');
}

if ('module' == $do) {
	$uid = intval($_GPC['uid']);
	$user = user_single($uid);
	if (empty($user)) {
		iajax(-1, '访问错误, 未找到指定操作用户.', '');
	}
	$isfounder = user_is_founder($uid, true);
	if ($isfounder) {
		iajax(-1, '访问错误, 无法编辑站长.', '');
	}
	$module_name = safe_gpc_string($_GPC['m']);
	$module = pdo_fetch('SELECT * FROM ' . tablename('modules') . ' WHERE name = :m', array(':m' => $module_name));
		$purview = pdo_fetch('SELECT * FROM ' . tablename('users_permission') . ' WHERE uniacid = :aid AND uid = :uid AND type = :type', array(':aid' => $uniacid, ':uid' => $uid, ':type' => $module_name));
	$user_module_permissions = permission_account_user_menu($uid, $uniacid, 'modules');
	if (!empty($purview['permission'])) {
		$purview['permission'] = explode('|', $purview['permission']);
	} else {
		$purview['permission'] = array();
	}

	$mineurl = array();
	$all = 0;
	if (!empty($mods)) {
		foreach ($mods as $mod) {
			if ('all' == $mod['url']) {
				$all = 1;
				break;
			} else {
				$mineurl[] = $mod['url'];
			}
		}
	}
	$data = array();
	if ($module['settings']) {
		$data[] = array('title' => '参数设置', 'permission' => $module_name . '_settings');
	}
	if ($module['isrulefields']) {
		$data[] = array('title' => '回复规则列表', 'permission' => $module_name . '_rule');
	}
	$entries = module_entries($module_name);
	if (!empty($entries['home'])) {
		$data[] = array('title' => '微站首页导航', 'permission' => $module_name . '_home');
	}
	if (!empty($entries['profile'])) {
		$data[] = array('title' => '个人中心导航', 'permission' => $module_name . '_profile');
	}
	if (!empty($entries['shortcut'])) {
		$data[] = array('title' => '快捷菜单', 'permission' => $module_name . '_shortcut');
	}
	if (!empty($entries['cover'])) {
		foreach ($entries['cover'] as $cover) {
			$data[] = array('title' => $cover['title'], 'permission' => $module_name . '_cover_' . $cover['do']);
		}
	}
	if (!empty($entries['menu'])) {
		foreach ($entries['menu'] as $menu) {
			if (!empty($menu['multilevel'])) {
				continue;
			}
			$data[] = array('title' => $menu['title'], 'permission' => $module_name . '_menu_' . $menu['do']);
		}
	}
	unset($entries);
	if (!empty($module['permissions'])) {
		$module['permissions'] = (array) iunserializer($module['permissions']);
		$data = array_merge($data, $module['permissions']);
	}
	foreach ($data as &$data_val) {
		$data_val['checked'] = 0;
		if (in_array($data_val['permission'], $purview['permission']) || in_array('all', $purview['permission']) || in_array('all', $user_module_permissions)) {
			$data_val['checked'] = 1;
		}
	}
	unset($data_val);
	if (empty($data)) {
		iajax(-1, '无子权限！', '');
	} else {
		iajax(0, $data, '');
	}
}
