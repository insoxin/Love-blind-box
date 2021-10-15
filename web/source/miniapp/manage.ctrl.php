<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

define('FRAME', 'system');
load()->model('system');

$dos = array('display', 'edit_version', 'del_version');
$do = in_array($do, $dos) ? $do : 'display';

$uniacid = intval($_GPC['uniacid']);
if (empty($uniacid)) {
	if ($_W['isajax']) {
		iajax(-1, '请选择要编辑的小程序');
	}
	itoast('请选择要编辑的小程序', referer(), 'error');
}

$state = permission_account_user_role($_W['uid'], $uniacid);
$role_permission = in_array($state, array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_MANAGER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER));
if (!$role_permission) {
	if ($_W['isajax']) {
		iajax(-1, '无权限操作！');
	}
	itoast('无权限操作！', referer(), 'error');
}

$account = uni_fetch($uniacid);
if (is_error($account) || empty($account['type'])) {
	if ($_W['isajax']) {
		iajax(-1, $account['message']);
	}
	itoast($account['message'], url('account/manage'), 'error');
}

if ('display' == $do) {
	$miniapp_info = table($account_all_type[$account['type']]['table_name'])
		->where(array('uniacid' => $account['uniacid']))
		->get();
	$version_exist = miniapp_fetch($account['uniacid']);
	if (!empty($version_exist)) {
		$version_lists = miniapp_version_all($uniacid);
		if (!empty($version_lists)) {
			foreach ($version_lists as &$row) {
				$row['enter_account_url'] = url('account/display/switch', array('uniacid' => $uniacid, 'version_id' => $row['version_id']), true);
				if (!empty($row['modules'])) {
					$row['module']['module_info'] = current($row['modules']);
				}
				if (!empty($row['last_modules'])) {
					$row['last_modules'] = current($row['last_modules']);
					$module = module_fetch($row['last_modules']['name']);
					if (!empty($module)) {
						$row['last_modules'] = array_merge($module, $row['last_modules']);
					}
				}
				if (empty($row['last_modules'])) {
					$row['last_modules'] = $row['module'];
				}
			}
			unset($row);
		}
		if (WXAPP_CREATE_DEFAULT < $version_exist['version']['type']) {
			$uid = empty($_GPC['uid']) || !is_numeric($_GPC['uid']) ? $_W['uid'] : intval($_GPC['uid']);
			$miniapp_modules = user_modules($uid);
			$sysmods = module_system();
			$module_type = 'account';
			foreach ($miniapp_modules as $k => $value) {
				if ('system' == $value['type'] || in_array($value['name'], $sysmods)) {
					unset($miniapp_modules[$k]);
					continue;
				}
				$continue = false;
				foreach ($account_all_type as $account_type) {
					if ($module_type == $account_type['type_sign'] && $value[$account_type['module_support_name']] != $account_type['module_support_value']) {
						$continue = true;
						break;
					}
				}
				if ($continue) {
					unset($miniapp_modules[$k]);
					continue;
				}
				$module_entries = module_entries($value['name'], array('cover'));
				if (empty($module_entries)) {
					unset($miniapp_modules[$k]);
					continue;
				}
				$miniapp_modules[$k]['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微信魔方团队'));
			}
		} else {
			$miniapp_modules = miniapp_support_uniacid_modules($uniacid);
			if ($miniapp_modules && WXAPP_TYPE_SIGN == $account->typeSign) {
				foreach ($miniapp_modules as $k => $module) {
					if (MODULE_SUPPORT_WXAPP != $module[MODULE_SUPPORT_WXAPP_NAME]) {
						unset($miniapp_modules[$k]);
					}
				}
			}
		}
	}
	$_W['breadcrumb'] = $account['name'];
	if ($_W['isajax']) {
		$message = array(
			'add_version_url' => url('miniapp/post', array('uniacid' => $uniacid, 'type' => $account['type']), true),
			'list' => $version_lists,
			'version_exist' => $version_exist ? 1 : 0,
		);
		iajax(0, $message);
	}
	template('miniapp/manage');
}

if ('edit_version' == $do) {
	$versionid = intval($_GPC['version_id']);
	$module_name = safe_gpc_string($_GPC['name']);

	if (empty($module_name)) {
		iajax(1, '模块名不可为空！');
	}
	if (empty($versionid)) {
		iajax(1, '版本号不可为空!');
	}
	$module_info = module_fetch($module_name);
	if (empty($module_info)) {
		iajax(1, '模块不存在！');
	}
	$version_exist = miniapp_fetch($uniacid, $versionid);
	if (empty($version_exist)) {
		iajax(1, '版本不存在或已删除！');
	}
	$miniapp_modules = miniapp_support_uniacid_modules($uniacid);
	$supoort_modulenames = array_keys($miniapp_modules);
	if (!in_array($module_name, $supoort_modulenames)) {
		iajax(1, '没有模块：' . $module_info['title'] . '的权限！');
	}

	$new_module_data = array();
	$new_module_data[$module_name] = array(
		'name' => $module_name,
		'version' => $module_info['version'],
	);
	table($account_all_type[$account['type']]['version_tablename'])
		->where(array(
			'id' => $versionid,
			'uniacid' => $uniacid
		))
		->fill(array('modules' => iserializer($new_module_data)))
		->save();
	$version_module = current($version_exist['version']['modules']);
	if (!empty($version_module['uniacid']) && !empty($version_module['name'])) {
		table('uni_link_uniacid')->searchWithUniacidModulenameVersionid($uniacid, $version_module['name'], $versionid)->delete();
	}
	cache_delete(cache_system_key('miniapp_version', array('version_id' => $versionid)));
	iajax(0, '修改成功！', referer());
}

if ('del_version' == $do) {
	$versionid = intval($_GPC['version_id']);
	if (empty($versionid)) {
		iajax(1, '参数错误！');
	}
	$version_exist = miniapp_fetch($uniacid, $versionid);
	if (empty($version_exist)) {
		iajax(1, '模块版本不存在！');
	}
	$version_module = !empty($version_exist['version']['modules']) ? current($version_exist['version']['modules']) : array();
	if (!empty($version_module['name'])) {
		table('uni_link_uniacid')->searchWithUniacidModulenameVersionid($uniacid, $version_module['name'], $versionid)->delete();
	}
	$result = table($account_all_type[$account['type']]['version_tablename'])
		->where(array(
			'id' => $versionid,
			'uniacid' => $uniacid
		))
		->delete();
	if (!empty($result)) {
		iajax(0, '删除成功！', referer());
	} else {
		iajax(1, '删除失败，请稍候重试！');
	}
}