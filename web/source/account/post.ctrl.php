<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('cloud');
load()->model('cache');
load()->classs('weixin.platform');
load()->model('utility');
load()->func('file');
$uniacid = intval($_GPC['uniacid']);
if (empty($uniacid)) {
	$url = url('account/manage', array('account_type' => ACCOUNT_TYPE));
	if ($_W['isajax']) {
		iajax(-1, '请选择要编辑的' . ACCOUNT_TYPE_NAME);
	}
	itoast('请选择要编辑的' . ACCOUNT_TYPE_NAME, $url, 'error');
}
$account = uni_fetch($uniacid);
if (!$account) {
	if ($_W['isajax']) {
		iajax(-1, '无效的uniacid');
	}
	itoast('无效的uniacid');
}
$acid = $account['acid']; 
$state = permission_account_user_role($_W['uid'], $uniacid);
$dos = array('base', 'sms', 'modules_tpl', 'edit_modules_tpl', 'operators');
$role_permission = in_array($state, array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER));
if ($role_permission || $_W['isajax']) {
	$do = in_array($do, $dos) ? $do : 'base';
} elseif (ACCOUNT_MANAGE_NAME_MANAGER == $state) {
	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL || ACCOUNT_TYPE == ACCOUNT_TYPE_APP_AUTH) {
		header('Location: ' . url('wxapp/manage/display', array('uniacid' => $uniacid)));
		exit;
	} else {
		$do = in_array($do, $dos) ? $do : 'modules_tpl';
	}
} else {
	itoast('您是该公众号的操作员，无权限操作！', url('account/manage'), 'error');
}

$_W['breadcrumb'] = $account['name'];
if ('base' == $do) {
	if (!$role_permission && !$_W['isajax']) {
		itoast('无权限操作！', url('account/post/modules_tpl', array('uniacid' => $uniacid)), 'error');
	}
	if ($_W['ispost'] && $_W['isajax']) {
		if (!empty($_GPC['type'])) {
			$type = safe_gpc_string($_GPC['type']);
		} else {
			iajax(-1, '参数错误！', '');
		}
		$request_data = safe_gpc_string(trim($_GPC['request_data']));
		switch ($type) {
			case 'qrcodeimgsrc':
			case 'headimgsrc':
				$imgsrc = safe_gpc_path($request_data);
				$image_type = array(
					'qrcodeimgsrc' => 'qrcode',
					'headimgsrc' => 'logo',
				);
				if (file_is_image($imgsrc)) {
					$result = table('uni_account')->where('uniacid', $uniacid)->fill($image_type[$type], $imgsrc)->save();
				} else {
					$result = '';
				}
				break;
			case 'name':
				$check_uniacname = table('account')->searchWithTitle($request_data)->searchWithType($account['type'])->searchAccountList();
				if (!empty($check_uniacname)) {
					iajax(1, "该名称'{$request_data}'已经存在");
				}
				$uni_account = pdo_update('uni_account', array('name' => $request_data), array('uniacid' => $uniacid));
				$account_wechats = pdo_update($account->tablename, array('name' => $request_data), array('uniacid' => $uniacid));
				$result = ($uni_account && $account_wechats) ? true : false;
				break;
			case 'account':
				$data = array('account' => $request_data); break;
			case 'original':
				$data = array('original' => $request_data); break;
			case 'level':
				$data = array('level' => intval($_GPC['request_data'])); break;
			case 'appid':
				if (!empty($request_data)) {
					$hasAppid = uni_get_account_by_appid($request_data, $account['type'], $account['uniacid']);
					if (!empty($hasAppid)) {
						iajax(1, "{$hasAppid['key_title']}已被{$hasAppid['type_title']}[ {$hasAppid['name']} ]使用");
					}
				}
				$data = array('appid' => $request_data); break;
			case 'key':
				if (!empty($request_data) && !in_array($account['type_sign'], array(BAIDUAPP_TYPE_SIGN, TOUTIAOAPP_TYPE_SIGN))) {
					$hasAppid = uni_get_account_by_appid($request_data, $account['type'], $account['uniacid']);
					if (!empty($hasAppid)) {
						iajax(1, "{$hasAppid['key_title']}已被{$hasAppid['type_title']}[ {$hasAppid['name']} ]使用");
					}
				}
				if ($account['key'] == $request_data) {
					iajax(0, '修改成功！', referer());
				}
				$data = array('key' => $request_data); break;
			case 'secret':
				if ($account['secret'] == $request_data) {
					iajax(0, '修改成功！', referer());
				}
				$data = array('secret' => $request_data); break;
			case 'token':
				$oauth = (array) uni_setting_load(array('oauth'), $uniacid);
				if ($oauth['oauth'] == $acid && 4 != $account['level']) {
					$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$data = array('token' => $request_data);
				break;
			case 'encodingaeskey':
				$oauth = (array) uni_setting_load(array('oauth'), $uniacid);
				if ($oauth['oauth'] == $acid && 4 != $account['level']) {
					$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$data = array('encodingaeskey' => $request_data);
				break;
			case 'jointype':
				if (in_array($account['type'], array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_APP_NORMAL))) {
					$result = true;
				} else {
					$change_type = array(
						'type' => 'account' == $account->typeSign ? ACCOUNT_TYPE_OFFCIAL_NORMAL : ACCOUNT_TYPE_APP_NORMAL,
					);
					$update_type = pdo_update('account', $change_type, array('uniacid' => $uniacid));
					$result = $update_type ? true : false;
				}
				break;
			case 'highest_visit':
				if (user_is_vice_founder() || empty($_W['isfounder'])) {
					iajax(1, '只有创始人可以修改！');
				}
				$statistics_setting = (array) uni_setting_load(array('statistics'), $uniacid);
				if (!empty($statistics_setting['statistics'])) {
					$highest_visit = $statistics_setting['statistics'];
					$highest_visit['founder'] = intval($_GPC['request_data']);
				} else {
					$highest_visit = array('founder' => intval($_GPC['request_data']));
				}
				$result = pdo_update('uni_settings', array('statistics' => iserializer($highest_visit)), array('uniacid' => $uniacid));
				break;
			case 'endtime':
				$endtime = strtotime($_GPC['endtime']);
				if ($endtime <= 0) {
					iajax(1, '参数错误！');
				}
				
					$store_create_account_info = table('site_store_create_account')->getByUniacid($uniacid);
				
				if ($_W['isfounder']) {
					$endtime = 1 != $_GPC['endtype'] ? $endtime : 0;
					
						if (!empty($store_create_account_info)) {
							pdo_update('site_store_create_account', array('endtime' => $endtime), array('uniacid' => $uniacid));
						}
					
				} else {
					$owner_id = pdo_getcolumn('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'), 'uid');
					$user_endtime = pdo_getcolumn('users', array('uid' => $owner_id), 'endtime');
					
						if (!empty($store_create_account_info)) {
							$user_endtime = max($user_endtime, $store_create_account_info['endtime']);
						}
					
					if ($user_endtime != USER_ENDTIME_GROUP_UNLIMIT_TYPE && $user_endtime != USER_ENDTIME_GROUP_EMPTY_TYPE && $user_endtime < $endtime && !empty($user_endtime)) {
						iajax(1, '设置到期日期不能超过' . user_end_time($owner_id));
					}
				}
				$result = pdo_update('account', array('endtime' => $endtime), array('uniacid' => $uniacid));
				break;
			case 'attachment_limit':
				if (user_is_vice_founder() || empty($_W['isfounder'])) {
					iajax(1, '只有创始人可以修改！');
				}
				$has_uniacid = pdo_getcolumn('uni_settings', array('uniacid' => $uniacid), 'uniacid');
				if ($_GPC['request_data'] < 0) {
					$attachment_limit = -1;
				} else {
					$attachment_limit = intval($_GPC['request_data']);
				}
				if (empty($has_uniacid)) {
					$result = pdo_insert('uni_settings', array('attachment_limit' => $attachment_limit, 'uniacid' => $uniacid));
				} else {
					$result = pdo_update('uni_settings', array('attachment_limit' => $attachment_limit), array('uniacid' => $uniacid));
				}
				break;
		}
		if (!in_array($type, array('qrcodeimgsrc', 'headimgsrc', 'name', 'endtime', 'jointype', 'highest_visit', 'attachment_limit'))) {
			$result = pdo_update($account->tablename, $data, array('uniacid' => $uniacid));
		}
		if ($result) {
			cache_delete(cache_system_key('uniaccount', array('uniacid' => $uniacid)));
			cache_delete(cache_system_key('accesstoken', array('uniacid' => $uniacid)));
			cache_delete(cache_system_key('statistics', array('uniacid' => $uniacid)));
			iajax(0, '修改成功！', referer());
		} else {
			iajax(1, '修改失败！', '');
		}
	}

	if(!$_W['isadmin']){
		$owner_id = pdo_getcolumn('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'), 'uid');
		$user_endtime = user_end_time($owner_id);
	}
	if ($_W['setting']['platform']['authstate']) {
		$account_platform = new WeixinPlatform();
		$preauthcode = $account_platform->getPreauthCode();
		if (is_error($preauthcode)) {
			if (40013 == $preauthcode['errno']) {
				$url = '微信开放平台 appid 链接不成功，请检查修改后再试' . "<a href='" . url('system/platform') . "' style='color:#3296fa'>去设置</a>";
			} else {
				$url = "{$preauthcode['message']}";
			}

			$authurl = array(
				'errno' => 1,
				'url' => $url,
			);
		} else {
			$authurl_type = $account['type'] == 4 ? ACCOUNT_PLATFORM_API_LOGIN_WXAPP : ACCOUNT_PLATFORM_API_LOGIN_ACCOUNT;
			$callurl = $authurl_type == ACCOUNT_PLATFORM_API_LOGIN_WXAPP ? 'wxapp/auth/forward' : 'account/auth/forward';
			$authurl = array(
				'errno' => 0,
				'url' => sprintf(ACCOUNT_PLATFORM_API_LOGIN, $account_platform->appid, $preauthcode, urlencode(url($callurl, array(), true)), $authurl_type),
			);
		}
	}
	$account['start'] = date('Y-m-d', $account['starttime']);
	$account['end'] = in_array($account['endtime'], array(USER_ENDTIME_GROUP_EMPTY_TYPE, USER_ENDTIME_GROUP_UNLIMIT_TYPE)) ? '永久' : date('Y-m-d', $account['endtime']);
	$account['endtype'] = (in_array($account['endtime'], array(USER_ENDTIME_GROUP_EMPTY_TYPE, USER_ENDTIME_GROUP_UNLIMIT_TYPE)) || 	$account['endtime'] == 0) ? 1 : 2;
	$uni_setting = (array) uni_setting_load(array('statistics', 'attachment_limit', 'attachment_size'), $uniacid);
	$account['highest_visit'] = empty($uni_setting['statistics']['founder']) ? 0 : $uni_setting['statistics']['founder'];
	$account['attachment_size'] = round($uni_setting['attachment_size'] / 1024, 2);

	$attachment_limit = intval($uni_setting['attachment_limit']);
	if (0 == $attachment_limit) {
		$upload = setting_load('upload');
		$attachment_limit = empty($upload['upload']['attachment_limit']) ? 0 : intval($upload['upload']['attachment_limit']);
	}
	if ($attachment_limit <= 0) {
		$attachment_limit = -1;
	}
	$account['attachment_limit'] = intval($attachment_limit);
	$account['switchurl_full'] = $_W['siteroot'] . 'web/' . ltrim($account['switchurl'], './');
	$account['endtime'] = strlen($account['endtime']) == 10 ? $account['endtime'] : time();
	$account['headimgsrc'] = $account['logo'];
	$account['qrcodeimgsrc'] = $account['qrcode'];
	$account['switchurl_full'] = $_W['siteroot'] . 'web/' . ltrim($account['switchurl'], './');
	$account['siteurl'] = $account['type_sign'] != WXAPP_TYPE_SIGN ? $_W['siteroot'] : str_replace('http://', 'https://', $_W['siteroot']);
	$account['service_url'] = $account['siteurl'] . 'api.php?id=' . $account['acid'];
	$account['type_class'] = $account_all_type_sign[$account['type_sign']]['icon'];
	$account['owner_endtime'] = $user_endtime;
	$account['support_version'] = $account->supportVersion;
	$uniaccount = array();
	$uniaccount = pdo_get('uni_account', array('uniacid' => $uniacid));
	
		$account_api = uni_site_store_buy_goods($uniacid, STORE_TYPE_API);
		$account['renewurl'] = url('site/entry/goodsbuyer', array('direct' => 1, 'm' => 'store', 'type' => $account_all_type[$account['type']]['store_type_renew']));
		$account['renewurl_full'] = $_W['siteroot'] . 'web/' . ltrim($account['renewurl'], './');
	
	if ($_W['isajax']) {
		iajax(0, $account);
	} else {
		template('account/manage-base');
	}
}

if ('edit_modules_tpl' == $do) {
	$owner = $account->owner;
	if (!$role_permission) {
		iajax(-1, '无权限');
	}
	if ('group' == $_GPC['type']) {
		$groups = safe_gpc_array($_GPC['groupdata']);
		if (!empty($groups)) {
			$extra_group = pdo_getall('uni_account_group', array('uniacid' => $uniacid, 'groupid IN ' => $groups), array('groupid'));
			$extra_group_ids = array_column($extra_group, 'groupid');
			$group_id = array_diff($groups, $extra_group_ids);
			if (!empty($group_id)) {
				$modules_name = module_name_list($group_id);
				$module_expired_list = module_expired_list();
				if (is_error($module_expired_list)) {
					iajax(-1, $module_expired_list['message']);
				}
				if (!empty($module_expired_list)) {
					$expired_modules = array_intersect($module_expired_list, $modules_name);
					if ($expired_modules) {
						$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
						$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
						iajax(-1, '应用：' . $expired_modules_name . '，服务费到期，无法添加！', referer());
					}
				}
			}
		}
		pdo_delete('uni_account_group', array('uniacid' => $uniacid));
		if (!empty($groups)) {
			$group = pdo_get('users_group', array('id' => $owner['groupid']));
			$group['package'] = (array) iunserializer($group['package']);
			$group['package'] = array_unique($group['package']);
			foreach ($groups as $packageid) {
				if (!empty($packageid) && !in_array($packageid, $group['package'])) {
					pdo_insert('uni_account_group', array(
						'uniacid' => $uniacid,
						'groupid' => $packageid,
					));
				}
			}
		}
		cache_build_account_modules($uniacid);
		cache_build_account($uniacid);
		iajax(0, '修改成功！', '');
	}

	if ('extend' == $_GPC['type']) {
				$module = safe_gpc_array($_GPC['module']);
		if (!empty($module)) {
			$data = array(
				'modules' => array('modules' => array(), 'wxapp' => array(), 'webapp' => array(), 'phoneapp' => array()),
				'uniacid' => $uniacid,
			);
			switch ($account['type']) {
				case ACCOUNT_TYPE_OFFCIAL_NORMAL:
				case ACCOUNT_TYPE_OFFCIAL_AUTH:
					$data['modules']['modules'] = $module;
					break;
				case ACCOUNT_TYPE_APP_NORMAL:
				case ACCOUNT_TYPE_APP_AUTH:
				case ACCOUNT_TYPE_WXAPP_WORK:
					$data['modules']['wxapp'] = $module;
					break;
				case ACCOUNT_TYPE_WEBAPP_NORMAL:
					$data['modules']['webapp'] = $module;
					break;
				case ACCOUNT_TYPE_PHONEAPP_NORMAL:
					$data['modules']['phoneapp'] = $module;
					break;
				case ACCOUNT_TYPE_ALIAPP_NORMAL:
					$data['modules']['aliapp'] = $module;
					break;
			}

			$modules_name = array_reduce(iunserializer($data['modules']), 'array_merge', array());
			$extra_modules_name = pdo_get('uni_account_extra_modules', array('uniacid' => $uniacid), array('modules'));
			$extra_modules_name['modules'] = empty($extra_modules_name['modules']) ? array() : array_reduce(iunserializer($extra_modules_name['modules']), 'array_merge', array());
			$modules_name_list = array_diff($modules_name, $extra_modules_name['modules']);
			if (!empty($modules_name_list)) {
				$module_expired_list = module_expired_list();
				if (is_error($module_expired_list)) {
					iajax(-1, $module_expired_list['message']);
				}
				$expired_modules = array_intersect($module_expired_list, $modules_name_list);
				if ($expired_modules) {
					$expired_modules = pdo_getall('modules', array('name IN' => $expired_modules), array('title'), 'title');
					$expired_modules_name = implode('，', array_column($expired_modules, 'title'));
					iajax(-1, '应用：' . $expired_modules_name . '，服务费到期，无法添加！', referer());
				}
			}

			$data['modules'] = iserializer($data['modules']);
			$uni_groups_modules_old = array_keys(uni_modules_by_uniacid($uniacid));
			$id = pdo_fetchcolumn('SELECT id FROM ' . tablename('uni_account_extra_modules') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
			if (empty($id)) {
				pdo_insert('uni_account_extra_modules', $data);
			} else {
				pdo_update('uni_account_extra_modules', $data, array('id' => $id));
			}
		} else {
			$uni_groups_modules_old = array_keys(uni_modules_by_uniacid($uniacid));
			pdo_delete('uni_account_extra_modules', array('uniacid' => $uniacid));
		}
		cache_build_account_modules($uniacid);
		cache_build_account($uniacid);

		iajax(0, '修改成功！', '');
	}
	
		if ('store_endtime' == $_GPC['type'] && $_W['isfounder'] && !user_is_vice_founder()) {
			$order_id = intval($_GPC['order_id']);
			$new_endtime = safe_gpc_string($_GPC['new_time']);
			if (empty($order_id)) {
				iajax(-1, '参数错误！');
			}
			$condition = array('uniacid' => $uniacid, 'type' => STORE_ORDER_FINISH,  'id' => $order_id);
			$order_exist = pdo_get('site_store_order', $condition);
			if (!empty($order_exist)) {
				pdo_update('site_store_order', array('endtime' => strtotime($new_endtime)), $condition);
			} else {
				iajax(-1, '您未购买该权限组！');
			}
			iajax(0, '修改成功！', referer());
		}
	

	iajax(-1, '参数错误！', '');
}

if ('modules_tpl' == $do) {
	$owner = $account->owner;
	$owner['is_admin'] = user_is_founder($owner['uid'], true);
	if ($_W['isadmin']) {
		$uni_groups = uni_groups();
	}
	$modules = user_modules($_W['uid']);

		$type_info = uni_account_type(intval($_GPC['account_type']));
	foreach ($modules as $k => $module) {
		if (1 == $module['issystem'] || MODULE_SUPPORT_ACCOUNT != $module[$type_info['type_sign'] . '_support']) {
			unset($modules[$k]);
		} else {
			$modules[$k]['support'] = $type_info['type_sign'] . '_support';
		}
	}

		$modules_tpl = array();
	if (!$owner['is_admin']) {
		if (ACCOUNT_MANAGE_GROUP_VICE_FOUNDER == $owner['founder_groupid']) {
			$owner['group'] = pdo_get('users_founder_group', array('id' => $owner['groupid']), array('id', 'name', 'package'));
		} else {
			$owner['group'] = pdo_get('users_group', array('id' => $owner['groupid']), array('id', 'name', 'package'));
		}
		$owner['group']['package'] = (array) iunserializer($owner['group']['package']);

				if (!empty($owner['group']['package'])) {
			foreach ($owner['group']['package'] as $package_value) {
				if ($package_value == -1) {
					$modules_tpl[] = array(
						'id' => -1,
						'name' => '所有服务',
						'modules' => array(array('name' => 'all', 'title' => '所有模块')),
						'templates' => array(array('name' => 'all', 'title' => '所有模板')),
						'modules_all' => array(array('name' => 'all', 'title' => '所有模板')),
						'type' => 'default',
					);
				} elseif (0 == $package_value) {
				} else {
					$defaultmodule = current(uni_groups(array($package_value), $account->typeSign));
					$defaultmodule['type'] = 'default';
					$modules_tpl[] = $defaultmodule;
				}
			}
		}

				$users_extra_group_table = table('users_extra_group');
		$extra_groups = $users_extra_group_table->getUniGroupsByUid($owner['uid']);
		if (!empty($extra_groups)) {
			$extra_uni_groups = uni_groups(array_keys($extra_groups), $account->typeSign);
			foreach ($extra_uni_groups as $extra_group_val) {
				$extra_group_val['type'] = 'extend';
				$modules_tpl[] = $extra_group_val;
			}
		}

				$user_extend_modules_talbe = table('users_extra_modules');
		$user_extend_modules_talbe->searchByUid($owner['uid']);
		$user_extend_modules_talbe->searchBySupport($account->typeSign . '_support');
		$user_extend_modules = $user_extend_modules_talbe->getall();
		if (!empty($user_extend_modules)) {
			foreach ($user_extend_modules as $k => $info) {
				$module_info = module_fetch($info['module_name']);
				if (!empty($module_info)) {
					$user_extend_modules[$k] = $module_info;
				} else {
					unset($user_extend_modules[$k]);
				}
			}
		}
	}

		$extend = array(
		'groups' => array(),
		'modules' => array(),
		'templates' => array(),
	);
		$extendpackage = pdo_getall('uni_account_group', array('uniacid' => $uniacid), array(), 'groupid');
	if (!empty($extendpackage)) {
		foreach ($extendpackage as $extendpackage_val) {
			if ($extendpackage_val['groupid'] == -1) {
				$extend['groups'] = array(array(
					'id' => -1,
					'name' => '所有服务',
					'modules' => array(array('name' => 'all', 'title' => '所有模块')),
					'templates' => array(array('name' => 'all', 'title' => '所有模板')),
					'modules_all' => array(array('name' => 'all', 'title' => '所有模板')),
					'type' => 'extend', 				));
				break;
			} elseif (0 != $extendpackage_val['groupid']) {
				$ex_module = current(uni_groups(array($extendpackage_val['groupid']), $account->typeSign));
				if (!empty($ex_module)) {
					$extend['groups'][] = $ex_module;
				}
			}
		}
	}
		$extend_uni_group = pdo_get('uni_account_extra_modules', array('uniacid' => $uniacid));
	if (!empty($extend_uni_group)) {
		$extend_uni_group['modules'] = iunserializer($extend_uni_group['modules']);
		if (is_array($extend_uni_group['modules'])) {
			$current_module_names = array();
			foreach ($extend_uni_group['modules'] as $modulenames) {
				if (!is_array($modulenames)) {
					continue;
				}
				$current_module_names = array_merge($current_module_names, $modulenames);
			}
			$current_module_names = array_unique($current_module_names);
			if (!empty($current_module_names)) {
				foreach ($current_module_names as $name) {
					$fetch_module = module_fetch($name);
					if (!empty($fetch_module)) {
						$extend['modules'][$name] = $fetch_module;
					}
				}
			}
		}
	}
	$can_modify = false;
		$urls = array();
	if (ACCOUNT_MANAGE_NAME_FOUNDER == $_W['highest_role'] && !$owner['is_admin'] || ACCOUNT_MANAGE_NAME_VICE_FOUNDER == $_W['highest_role'] && $owner['uid'] != $_W['uid']) {
		$can_modify = true;
		if ($owner['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) {
			$urls['edit_owner_group'] = url('founder/edit/edit_modules_tpl', array('uid' => $owner['uid']), true);
		} else {
			$urls['edit_owner_group'] = url('user/edit/edit_modules_tpl', array('uid'=>$owner['uid']), true);
		}
	}

	
				$type_info = uni_account_type(intval($_GPC['account_type']));
		$account_buy_modules = uni_site_store_buy_goods($uniacid, empty($type_info['store_type_module']) ? 0 : $type_info['store_type_module']);
		if (!empty($account_buy_modules) && is_array($account_buy_modules)) {
			foreach ($account_buy_modules as $key => &$module) {
				$module = module_fetch($module);
				$order_info = pdo_fetch('SELECT o.`goodsid`, o.`id`, max(o.`endtime`) as endtime FROM ' . tablename('site_store_order') . ' AS o LEFT JOIN ' . tablename('site_store_goods') . ' AS g ON o.goodsid = g.id WHERE o.uniacid = :uniacid AND g.module = :module_name AND o.type = ' . STORE_ORDER_FINISH, array(':module_name' => $module['name'], ':uniacid' => $uniacid));
				if (empty($order_info)) {
					unset($account_buy_modules[$key]);
					continue;
				}
				$module['order_id'] = $order_info['id'];
				$module['goods_id'] = $order_info['goodsid'];
				$module['expire_time'] = $order_info['endtime'];
			}
			unset($module);
		}
				$account_buy_package = array();
		$account_buy_group = uni_site_store_buy_goods($uniacid, STORE_TYPE_PACKAGE);
		if (is_array($account_buy_group) && !empty($account_buy_group)) {
			foreach ($account_buy_group as $group) {
				$account_buy_package[$group] = current(uni_groups(array($group)));
				$account_buy_package[$group]['goods_id'] = pdo_getcolumn('site_store_goods', array('module_group' => $group), 'id');
				$order_info = pdo_fetch(
					'SELECT id, endtime from ' . tablename('site_store_order') . ' WHERE (uniacid = :uniacid OR wxapp = :wxapp) AND `type` = :status AND goodsid = :goodsid ORDER BY endtime DESC LIMIT 1', array(':uniacid' => $uniacid, ':wxapp' => $uniacid, ':status' => STORE_ORDER_FINISH,  ':goodsid' => $account_buy_package[$group]['goods_id'])
				);
				$account_buy_package[$group]['order_id'] = $order_info['id'];
				$account_buy_package[$group]['expire_time'] = $order_info['endtime'];
				if (TIMESTAMP > $account_buy_package[$group]['expire_time']) {
					$account_buy_package[$group]['expire'] = true;
				} else {
					$account_buy_package[$group]['expire'] = false;
					$account_buy_package[$group]['near_expire'] = strtotime('-1 week', $account_buy_package[$group]['expire_time']) < time() ? true : false;
				}
				$account_buy_package[$group]['expire_time'] = date('Y-m-d', $account_buy_package[$group]['expire_time']);
			}
			unset($group);
		}
	

	if ($_W['isajax']) {
		$message = array(
			'owner' => array('is_admin' => $owner['is_admin'], 'groupname' => $owner['group']['name']),
			'can_modify' => $can_modify,
			'modules_tpl' => $modules_tpl,
			'user_extend_modules' => $user_extend_modules,
			'extend' => $extend,
			'urls' => $urls,
		);
		iajax(0, $message);
	}
	template('account/manage-modules-tpl');
}

if ('operators' == $do) {
	$page = max(1, intval($_GPC['page']));
	$username = safe_gpc_string($_GPC['username']);
	$page_size = 15;
	$total = 0;

	$permission_table = table('users_permission');
	$permission_table->searchWithPage($page, $page_size);
	$list = $permission_table->getClerkPermissionList($uniacid, 0, $username);
	$total = $permission_table->getLastQueryTotal();
	if (!empty($list)) {
		foreach ($list as $k => $clerk) {
			$modules_info = module_fetch($clerk['type']);
			if (empty($modules_info)) {
				unset($list[$k]);
				continue;
			}
			$list[$k]['module_name'] = $modules_info['title'];
			if ($clerk['permission'] == 'all') {
				$list[$k]['permission'] = '所有';
			} else {
				$list[$k]['permission'] = count(explode('|', $clerk['permission'])) . '项';
			}

			if (empty($modules_info['main_module'])) {
				$list[$k]['can_delete'] = true;
			} else {
				$list[$k]['can_delete'] = false;
			}
			$clerk_userinfo = user_single($clerk['uid']);
			$list[$k]['username'] = $clerk_userinfo['username'];
			$list[$k]['permission_setting_url'] = url('module/display/switch', array('module_name' => $clerk['type'], 'uniacid' => $clerk['uniacid'], 'redirect' => urlencode(url('module/permission/post', array('uid' => $clerk['uid'], 'module_name' => $clerk['type'], 'uniacid' => $clerk['uniacid'])))), true);
		}
	}
	$pager = pagination($total, $page, $page_size);
	if ($_W['isajax']) {
		$message = array(
			'list' => $list,
			'total' => $total,
			'page' => $page,
			'page_size' => $page_size,
		);
		iajax(0, $message);
	}
	template('account/manage-operatoers');
}
