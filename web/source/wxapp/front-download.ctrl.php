<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniapp');
load()->classs('cloudapi');
load()->classs('uploadedfile');

$dos = array('front_download', 'domainset', 'qrcode', 'qrscan', 'publish', 'getpackage', 'entrychoose', 'set_wxapp_entry', 'platform_version_manage',
	'custom', 'custom_save', 'custom_default', 'custom_convert_img', 'upgrade_module', 'tominiprogram');
$do = in_array($do, $dos) ? $do : 'front_download';

$wxapp_info = miniapp_fetch($_W['uniacid']);

$is_module_wxapp = false;
if (!empty($version_id)) {
	$is_single_module_wxapp = WXAPP_CREATE_MODULE == $version_info['type']; }


		if ('entrychoose' == $do) {
		$entrys = $version_info['cover_entrys'];
		template('wxapp/version-front-download');
	}
		if ('set_wxapp_entry' == $do) {
		$entry_id = intval($_GPC['entry_id']);
		$result = miniapp_update_entry($version_id, $entry_id);
		iajax(0, '设置入口成功');
	}


if ('custom' == $do) {
	$default_appjson = miniapp_code_current_appjson($version_id);

	$default_appjson = json_encode($default_appjson);
	template('wxapp/version-front-download');
}
if ('custom_default' == $do) {
	$result = miniapp_code_set_default_appjson($version_id);
	if (false === $result) {
		iajax(1, '操作失败，请重试！');
	} else {
		iajax(0, '设置成功！', url('wxapp/front-download/front_download', array('version_id' => $version_id)));
	}
}

if ('custom_save' == $do) {
	if (empty($version_info)) {
		iajax(1, '参数错误！');
	}
	$json = array();
	if (!empty($_GPC['json']['window'])) {
		$json['window'] = array(
			'navigationBarTitleText' => safe_gpc_string($_GPC['json']['window']['navigationBarTitleText']),
			'navigationBarTextStyle' => safe_gpc_string($_GPC['json']['window']['navigationBarTextStyle']),
			'navigationBarBackgroundColor' => safe_gpc_string($_GPC['json']['window']['navigationBarBackgroundColor']),
			'backgroundColor' => safe_gpc_string($_GPC['json']['window']['backgroundColor']),
		);
	}
	if (!empty($_GPC['json']['tabBar'])) {
		$json['tabBar'] = array(
			'color' => safe_gpc_string($_GPC['json']['tabBar']['color']),
			'selectedColor' => safe_gpc_string($_GPC['json']['tabBar']['selectedColor']),
			'backgroundColor' => safe_gpc_string($_GPC['json']['tabBar']['backgroundColor']),
			'borderStyle' => in_array($_GPC['json']['tabBar']['borderStyle'], array('black', 'white')) ? $_GPC['json']['tabBar']['borderStyle'] : '',
		);
	}
	$result = miniapp_code_save_appjson($version_id, $json);
	cache_delete(cache_system_key('miniapp_version', array('version_id' => $version_id)));
	iajax(0, '设置成功！', url('wxapp/front-download/front_download', array('version_id' => $version_id)));
}

if ('custom_convert_img' == $do) {
	$attchid = intval($_GPC['att_id']);
	$filename = miniapp_code_path_convert($attchid);
	iajax(0, $filename);
}

if ('front_download' == $do) {
	permission_check_account_user('publish_front_download');
	$appurl = $_W['siteroot'] . '/app/index.php';
	$uptype = $_GPC['uptype'];
	if (!in_array($uptype, array('auto', 'normal'))) {
		$uptype = 'auto';
	}
	if (!empty($version_info['last_modules'])) {
		$last_modules = current($version_info['last_modules']);
	}
	$need_upload = false;
	$module = array();
	if (!empty($version_info['modules'])) {
		foreach ($version_info['modules'] as $item) {
			$module = module_fetch($item['name']);
			$need_upload = !empty($last_modules) && ($module['version'] != $last_modules['version']);
		}
	}
	if (!empty($version_info['version'])) {
		$user_version = explode('.', $version_info['version']);
		$user_version[count($user_version) - 1] += 1;
		$user_version = join('.', $user_version);
	}
	if (WXAPP_TYPE_SIGN == $_W['account']->typeSign) {
		$module_config = array(
			'has_live_player_plugin' => false
		);
		if ($version_info['type'] == 0) {
			$account_wxapp_info = miniapp_fetch($version_info['uniacid'], $version_id);
			$params = array(
				'module' => array_shift($account_wxapp_info['version']['modules'])
			);
			$module_config = cloud_wxapp_info($params);
			if (is_error($module_config)) {
				itoast($module_config['message'], url('miniapp/version/home', array('version_id' => $version_id, 'uniacid' => $version_info['uniacid'])), 'error');
			}
		}
		$version_info['support_live'] = !empty($module_config['has_live_player_plugin']) ? STATUS_ON : STATUS_OFF;
	}
	template('wxapp/version-front-download');
}

if ('platform_version_manage' == $do) {
	$platform_version_info = array('success' => array(), 'audit' => array(), 'develop' => array());
	$wxapp_register_version = table('wxapp_register_version')->getByUniacid($_W['uniacid']);
	foreach ($wxapp_register_version as $key => $value) {
		if (WXAPP_REGISTER_VERSION_STATUS_RELEASE == $value['status']) {
			$platform_version_info['success'][] = $value;
		} elseif (in_array($value['status'], array(WXAPP_REGISTER_VERSION_STATUS_CHECKING, WXAPP_REGISTER_VERSION_STATUS_CHECKFAIL, WXAPP_REGISTER_VERSION_STATUS_CHECKSUCCESS))) {
			$params = array(
				':uniacid' => $value['uniacid'],
				':version_id' => $value['version_id'],
			);
			$day_num = pdo_fetch('select count(id) day_num from ' . tablename('wxapp_undocodeaudit_log') . ' where TO_DAYS(from_unixtime(`revoke_time`)) = TO_DAYS(NOW()) and uniacid = :uniacid and version_id = :version_id;', $params);
			$month_num = pdo_fetch('select count(id) month_num from ' . tablename('wxapp_undocodeaudit_log') . ' where DATE_FORMAT(from_unixtime(`revoke_time`), "%Y%m")=DATE_FORMAT(CURDATE(), "%Y%m") and uniacid = :uniacid and version_id = :version_id;', $params);
			$value['day_num'] = empty($day_num) ? 1 : $day_num['day_num'] >= 1 ? 0 : 1;
			$value['month_num'] = empty($month_num) ? 10 : $month_num['month_num'] >= 10 ? 0 : 10 - $month_num['month_num'];
			$platform_version_info['audit'][] = $value;
		} elseif (WXAPP_REGISTER_VERSION_STATUS_DEVELOP == $value['status']) {
			$platform_version_info['develop'][] = $value;
		}
	}
	template('wxapp/version-front-download');
}
if ('upgrade_module' == $do) {
	$modules = table('wxapp_versions')
		->where(array('id' => $version_id))
		->getcolumn('modules');
	$modules = iunserializer($modules);
	if (!empty($modules)) {
		foreach ($modules as $name => $module) {
			$module_info = module_fetch($name);
			if (!empty($module_info['version'])) {
				$modules[$name]['version'] = $module_info['version'];
			}
		}
		$modules = iserializer($modules);
		table('wxapp_versions')
			->where(array('id' => $version_id))
			->fill(array(
				'modules' => $modules,
				'last_modules' => $modules,
				'version' => $_GPC['version'],
				'description' => trim($_GPC['description']),
				'upload_time' => TIMESTAMP,
			))
			->save();
		cache_delete(cache_system_key('miniapp_version', array('version_id' => $version_id)));
	}
	iajax(0, '更新模块信息成功');
}

if ('qrcode' == $do) {
	$data = cloud_wxapp_login_qrcode();
	if (is_error($data)) {
		iajax(-1, '系统错误');
	}
	iajax(0, $data);
}
if ('qrscan' == $do) {
	$uuid = $_GPC['uuid'];
	if (empty($uuid)) {
		iajax(-1, '参数错误');
	}
	$params = array(
		'uuid' => $uuid
	);
	$data = cloud_wxapp_login_qrscan($params);
	if (is_error($data)) {
		iajax(-1, '系统错误');
	}
	iajax(0, $data);
}
if ('publish' == $do) {
	if (empty($_GPC['version_id']) || empty($_GPC['ticket']) || empty($_GPC['user_version']) || empty($_GPC['user_desc'])) {
		iajax(-1, '参数错误');
	}
	$version_id = intval($_GPC['version_id']);
	$version_info = miniapp_version($version_id);
	$account_wxapp_info = miniapp_fetch($version_info['uniacid'], $version_id);

	if ($version_info['type'] == 0) {
		$module = array_shift($account_wxapp_info['version']['modules']);
	}
	if (empty($account_wxapp_info)) {
		iajax(-1, '版本不存在');
	}
	$siteurl = $_W['siteroot'] . 'app/index.php';
	if (!empty($account_wxapp_info['appdomain'])) {
		$siteurl = $account_wxapp_info['appdomain'];
	}
	if (!starts_with($siteurl, 'https')) {
		iajax(-1, '小程序域名必须为https');
	}
	if ($version_info['type'] == WXAPP_CREATE_MODULE && $version_info['entry_id'] <= 0) {
		iajax(-1, '请先设置小程序入口');
	}
	if (ACCOUNT_TYPE_APP_AUTH == $_W['account']['type']) {
		if (empty($_W['setting']['platform']['authstate'])) {
			iajax(-1, '开放平台未开启，无法上传');
		}
		if (empty($_W['setting']['platform']['bindappid'])) {
			iajax(-1, '未设置开放平台绑定的开发小程序，无法给该授权小程序上传，请先<a href="./index.php?c=system&a=platform" class="color-default">绑定开发小程序</a>');
		}
		$appid = $_W['setting']['platform']['bindappid'];
	} else {
		$appid = $account_wxapp_info['key'];
	}
	if ($version_info['use_default'] == 0) {
		$appjson = miniapp_code_custom_appjson_tobase64($version_id);
		if ($appjson) {
			if (!isset($appjson['tabBar']['list'])) {
				unset($appjson['tabBar']);
			}
		}
	}
	$siteinfo = array(
		'siteinfo' => array(
			'name' => $account_wxapp_info['name'],
			'uniacid' => $account_wxapp_info['uniacid'],
			'acid' => $account_wxapp_info['acid'],
			'multiid' => $account_wxapp_info['version']['multiid'],
			'version' => $_GPC['user_version'],
			'siteroot' => $siteurl,
		)
	);
	$appjson = !empty($appjson) ? array_merge($siteinfo, $appjson) : $siteinfo;
	$params = array(
		'preview' => isset($_GPC['commit_type']) ? $_GPC['commit_type'] : 1,
		'ticket' => $_GPC['ticket'],
		'module' => empty($module) ? array() : $module,
		'publish' => array(
			'version' => $_GPC['user_version'],
			'description' => $_GPC['user_desc']
		),
		'clear_live_player_plugin' => isset($_GPC['support_live']) && $_GPC['support_live'] == 'true' ? 0 : 1,
		'appid' => $appid,
		'wxapp_type' => isset($version_info['type']) ? $version_info['type'] : 0,
		'appjson' => json_encode($appjson),
		'tominiprogram' => array_keys($version_info['tominiprogram'])
	);

	$data = cloud_wxapp_publish($params);
	if (is_error($data)) {
		if (ACCOUNT_TYPE_APP_AUTH == $_W['account']['type']) {
			iajax(-1, $_W['account']->errorCode($data['errno']));
		} else {
			iajax(-1, $data['message']);
		}
	}
	iajax(0, $data);
}

if ('tominiprogram' == $do) {
	$tomini_lists = iunserializer($version_info['tominiprogram']);
	if (!is_array($tomini_lists)) {
		$tomini_lists = array();
		miniapp_version_update($version_id, array('tominiprogram' => iserializer(array())));
	}

	if (checksubmit()) {
		$appids = $_GPC['appid'];
		$app_names = $_GPC['app_name'];
		$is_add = intval($_GPC['is_add']);

		if (!is_array($appids) || !is_array($app_names)) {
			itoast('参数有误！', referer(), 'error');
		}
		$data = $is_add ? $tomini_lists : array();
		foreach ($appids as $k => $appid) {
			if (empty($appid) || empty($app_names[$k])) {
				continue;
			}
			$appid = safe_gpc_string($appid);
			$data[$appid] = array(
				'appid' => $appid,
				'app_name' => safe_gpc_string($app_names[$k])
			);
			if (count($data) >= 10) {
				break;
			}
		}
		miniapp_version_update($version_id, array('tominiprogram' => iserializer($data)));
		itoast('保存成功！', referer(), 'success');
	}
	template('wxapp/version-front-download');
}

if ('getpackage' == $do) {
	if (empty($version_id)) {
		itoast('参数错误！', '', '');
	}
	$account_wxapp_info = miniapp_fetch($version_info['uniacid'], $version_id);
	if (empty($account_wxapp_info)) {
		itoast('版本不存在！', referer(), 'error');
	}

	$request_cloud_data = array(
		'module' => array_shift($account_wxapp_info['version']['modules']),
		'support' => $_W['account']['type_sign'],
	);
	$result = cloud_miniapp_get_package($request_cloud_data);

	if (is_error($result)) {
		itoast($result['message'], '', '');
	} else {
		header("http/1.1 301 moved permanently");
		header("location: " . $result['url']);
	}
	exit;
}
