<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');

$dos = array('list', 'check_receive', 'templates', 'modules');
if (!in_array($do, $dos)) {
	if ($_W['isajax']) {
		iajax(-1, 'Access Denied');
	}
	exit('Access Denied');
}
$keyword = safe_gpc_string($_GPC['keyword']);
$account_type_sign = safe_gpc_string($_GPC['account_type_sign']);

if ('check_receive' == $do) {
	$module_name = trim($_GPC['module_name']);
	$module_obj = WeUtility::createModuleReceiver($module_name);
	if (!empty($module_obj)) {
		$module_obj->uniacid = $_W['uniacid'];
		$module_obj->acid = $_W['acid'];
		$module_obj->message = array(
			'event' => 'subscribe',
		);
		if (method_exists($module_obj, 'receive')) {
			$module_obj->receive();

			return iajax(0, '');
		}
	}

	return iajax(1, '');
}

if ('list' == $do) {
	global $_W;
	if (!empty($_COOKIE['special_reply_type'])) {
		$enable_modules = array();
		$_W['account']['modules'] = uni_modules();
		foreach ($_W['account']['modules'] as $m) {
			if (is_array($_W['account']['modules'][$m['name']]['handles']) && in_array($_COOKIE['special_reply_type'], $_W['account']['modules'][$m['name']]['handles'])) {
				$enable_modules[$m['name']] = $m;
			}
		}
		setcookie('special_reply_type', '', time() - 3600);
	} else {
		$installedmodulelist = uni_modules();
		foreach ($installedmodulelist as $k => $value) {
			$installedmodulelist[$k]['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微信魔方团队'));
			if (1 == $value['enabled']) {
				$enable_modules[$k] = $value;
			}
		}
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 21;
	$current_module_list = array_slice($enable_modules, ($pindex - 1) * $psize, $psize);
	if ($_W['isw7_request']) {
		$message = array(
			'total' => count($enable_modules),
			'page' => $pindex,
			'page_size' => $psize,
			'list' => $current_module_list
		);
		iajax(0, $message);
	}

	$result = array(
		'items' => $current_module_list,
		'pager' => pagination(count($enable_modules), $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback' => 'null')),
	);
	iajax(0, $result);
}

if ('templates' == $do) {
	$page = max(1, intval($_GPC['page']));
	$page_size = 6;
	$templates_table = table('modules');
	if (!empty($keyword)) {
		$templates_table->where('title LIKE', "%{$keyword}%");
	}
	$templates = $templates_table->select(array('mid', 'name', 'title'))->where('application_type', APPLICATION_TYPE_TEMPLATES)->page($page, $page_size)->getall();
	if (!empty($templates)) {
		foreach ($templates as $key => $template) {
						$templates[$key]['id'] = $template['mid'];
			$templates[$key]['logo'] = $_W['siteroot'] . 'app/themes/' . $template['name'] . '/preview.jpg';
		}
	}
	$total = $templates_table->getLastQueryTotal();
	$message = array(
		'keyword' => $keyword,
		'page' => $page,
		'page_size' => $page_size,
		'total' => $total,
		'list' => $templates
	);
	iajax(0, $message);
}

if ('modules' == $do) {
	$modules = user_modules($_W['uid']);
	if (empty($modules)) {
		$message = array(
			'total' => 0,
			'page' => 1,
			'page_size' => 10,
			'keyword' => $keyword,
			'account_type_sign' => $account_type_sign,
			'list' => array()
		);
		iajax(0, $message);
	}

	if (!empty($keyword)) {
		foreach($modules as $k => $module) {
			if (!strstr($module['title'], $keyword)) {
				unset($modules[$k]);
			}
		}
	}
	$module_list = array();
	if (!empty($account_type_sign)) {
		foreach ($modules as $k => $module) {
			if (1 == $module['issystem'] || MODULE_SUPPORT_ACCOUNT != $module[$account_type_sign . '_support']) {
				unset($modules[$k]);
				continue;
			}
			$module_list[] = array(
				'id' => $module['mid'],
				'name' => $module['name'],
				'title' => $module['title'],
				'logo' => $module['logo'],
				'support' => $account_type_sign,
			);
		}
	} else {
		$module_support_type = module_support_type();
		foreach ($modules as $name => $module) {
			foreach ($module_support_type as $support => $info) {
				if (MODULE_SUPPORT_SYSTEMWELCOME_NAME == $support) {
					continue;
				}
				if ($module[$support] == $info['support']) {
					$module_list[] = array(
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
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$current_module_list = array_slice($module_list, ($pindex - 1) * $psize, $psize);
	
	$message = array(
		'total' => count($module_list),
		'page' => $pindex,
		'page_size' => $psize,
		'keyword' => $keyword,
		'account_type_sign' => $account_type_sign,
		'list' => $current_module_list
	);
	iajax(0, $message);
}
