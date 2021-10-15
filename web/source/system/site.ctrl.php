<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('system');

$dos = array('basic', 'copyright', 'about', 'save_setting', 'icps', 'edit_icp', 'del_icp');
$do = in_array($do, $dos) ? $do : 'basic';
$settings = $_W['setting']['copyright'];

if (empty($settings) || !is_array($settings)) {
	$settings = array();
} else {
	$settings['slides'] = iunserializer($settings['slides']);
	$settings['icps'] = !empty($settings['icps']) ? iunserializer($settings['icps']) : array();
}

if ('basic' == $do) {
	$template_ch_name = $login_ch_name = array();
	
		$path = IA_ROOT . '/web/themes/';
		if (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (false !== ($templatepath = readdir($handle))) {
					if ('.' != $templatepath && '..' != $templatepath) {
						if (is_dir($path . $templatepath)) {
							$template[] = $templatepath;
						}
					}
				}
			}
		}

		$template_ch_name = system_template_ch_name();
		$login_ch_name = system_login_template_ch_name();
		$_W['setting']['basic']['login_template'] = !empty($_W['setting']['basic']['login_template']) ? $_W['setting']['basic']['login_template'] : 'base';
		$templates_ch = array_keys($template_ch_name);
		if (!empty($template)) {
			foreach ($template as $template_val) {
				if (!in_array($template_val, $templates_ch)) {
					$template_ch_name[$template_val] = $template_val;
				}
			}
		}
	
	if (!empty($settings['autosignout'])) {
		if ($settings['autosignout'] >= 60) {
			$hour = floor($settings['autosignout']/60);
			$min = $settings['autosignout']%60;
			$res = $hour . '小时';
			$min != 0  &&  $res .= $min . '分钟';
		} else {
			$res = $settings['autosignout'] . '分钟';
		}

		$settings['autosignout_notice'] = "系统无操作，{$res}后自动退出";
	}

	$settings['icon'] = to_global_media($settings['icon']);
	$settings['flogo'] = to_global_media($settings['flogo']);
	$settings['slide_logo'] = to_global_media($settings['slide_logo']);
	if (!empty($settings['slides'])) {
		foreach ($settings['slides'] as $key => $slide) {
			$settings['slides'][$key] = to_global_media($slide);
		}
	}

	if ($_W['isajax']) {
		$message = array(
			'settings' => $settings,
			'template_ch_name' => $template_ch_name,
			'template' => $_W['setting']['basic']['template'],
			'login_ch_name' => $login_ch_name,
			'login_template' => $_W['setting']['basic']['login_template']
		);
		iajax(0, $message);
	}
}

if ('save_setting' == $do) {
	$system_setting_items = system_setting_items();
	$key = safe_gpc_string($_GPC['key']);

	switch ($key) {
		case 'policeicp':
			$settings[$key] = array(
				'policeicp_location' => safe_gpc_string($_GPC['value']['location']),
				'policeicp_code' => safe_gpc_string($_GPC['value']['code']),
			);
			break;
		case 'statcode':
			$settings[$key] = system_check_statcode($_GPC['value']);
			break;
		case 'url':
			$settings[$key] = (strexists($_GPC['value'], 'http://') || strexists($_GPC['value'], 'https://')) ? $_GPC['value'] : "http://{$_GPC['value']}";
			break;
		case 'footerleft':
			$settings[$key] = safe_gpc_html(htmlspecialchars_decode($_GPC['value']));
			break;
		case 'footerright':
			$settings[$key] = safe_gpc_html(htmlspecialchars_decode($_GPC['value']));
			break;
		case 'slides':
			$settings[$key] = iserializer($_GPC['value']);
			break;
		case 'companyprofile':
			$settings[$key] = safe_gpc_html(htmlspecialchars_decode($_GPC['value']));
			break;
		case 'template':
			break;
		case 'baidumap':
			break;
		case 'autosignout':
			$limit_time = 1*24*60;
			if ($limit_time < safe_gpc_int($_GPC['value']) || safe_gpc_int($_GPC['value'] < 1)) {
				iajax(-1, '自动退出时间请在1-' . $limit_time . '分钟内设置！', url('system/site'));
			}
			$settings[$key] = safe_gpc_int($_GPC['value']);
			break;
		case 'login_verify_status':
			if (!$_W['isfounder']) {
				iajax(-1, '您没有权限', url('system/site'));
			}
			if (empty($_W['user']['mobile'])) {
				iajax(-1, '您的账户还未绑定手机号，请先绑定手机号，再开启此功能');
			}
			$settings[$key] = 1 == $_GPC['is_int'] ? intval($_GPC['value']) : safe_gpc_string($_GPC['value']);
			$settings['login_verify_mobile'] = $_W['user']['mobile'];
			break;
		default:
			$settings[$key] = 1 == $_GPC['is_int'] ? intval($_GPC['value']) : safe_gpc_string($_GPC['value']);
			break;
	}

	if (!in_array($key, $system_setting_items)) {
		iajax(-1, '参数错误！', url('system/site'));
	}
	if (in_array($key, array('template', 'login_template'))) {
		$basic_setting = $_W['setting']['basic'];
		$basic_setting[$key] = safe_gpc_string($_GPC['value']);
		setting_save($basic_setting, 'basic');
	} elseif ($key == 'baidumap') {
		$settings['baidumap'] = array('lng' => $_GPC['lng'], 'lat' => $_GPC['lat']);
		setting_save($settings, 'copyright');
	} else {
		setting_save($settings, 'copyright');
	}

	iajax(0, '更新设置成功！', referer());
}

if ($do == 'icps') {
	$keyword = !empty($_GPC['keyword']) ? safe_gpc_string($_GPC['keyword']) : '';
	$page = max(1, safe_gpc_int($_GPC['page']));
	$page_size = 10;
	$icps = (array)$settings['icps'];
	if (!empty($icps)) {
		foreach ($icps as $key => $icp) {
			if (!empty($keyword) && !strexists($icp['icp'], $keyword) && !strexists($icp['domain'], $keyword)) {
				unset($icps[$key]);
			}
			$icp['electronic_license'] = htmlspecialchars_decode($icp['electronic_license']);
		}
	}
	$total = count($icps);
	$icps = array_slice($icps, ($page - 1) * $page_size, $page_size);
	if ($_W['isajax']) {
		$result = array(
			'list' => $icps,
			'page' => $page,
			'page_size' => $page_size,
			'total' => $total
		);
		iajax(0, $result);
	}

	$pager = pagination($total, $page, $page_size, '', array('before' => '2', 'after' => '3'));
}

if ($do == 'edit_icp') {
	$id = safe_gpc_int($_GPC['id']);
	$icp = safe_gpc_string($_GPC['icp']);
	$domain = safe_gpc_string($_GPC['domain']);
	$policeicp_location = safe_gpc_string($_GPC['policeicp_location']);
	$policeicp_code = safe_gpc_string($_GPC['policeicp_code']);
	$electronic_license = safe_gpc_html(htmlspecialchars_decode($_GPC['electronic_license']));
	if (empty($icp)) {
		iajax(-1, '请至少填写一条icp信息！');
	}
	if (starts_with($domain, 'http')) {
		iajax(-1, '域名格式为w7.cc,无http://或以https://开头');
	}
	$special_domain = array('.com.cn', '.net.cn', '.gov.cn', '.org.cn', '.com.hk', '.com.tw');
	$domain_data = str_replace($special_domain, '.com', $domain);
	$domain_array = explode('.', $domain_data);
	if (count($domain_array) > 3 || count($domain_array) < 2) {
		iajax(-1, '只支持一级域名和二级域名！');
	}

	if (empty($id)) {
		$icps = (array)$settings['icps'];
		if (!empty($icps)) {
			$icp_domains = array_column($icps, 'domain');
			$max_id = max(max(array_column($icps, 'id')), 1);
		}
		if (!empty($icp) && in_array($domain, $icp_domains)) {
			iajax(-1, $domain . '已存在！');
		}
		$max_id++;
		$icp_domains[] = $domain;
		$icps[] = array(
			'id' => $max_id,
			'icp' => $icp,
			'domain' => $domain,
			'policeicp_location' => $policeicp_location,
			'policeicp_code' => $policeicp_code,
			'electronic_license' => $electronic_license,
		);
		$settings['icps'] = iserializer($icps);
		setting_save($settings, 'copyright');
		iajax(0, '添加成功');
	} else {
		if (empty($icp) || empty($domain)) {
			iajax(-1, '参数错误');
		}
		$else_icps = $icps = $settings['icps'];
		foreach ($icps as $k => $value) {
			if ($value['id'] == $id) {
				$key = $k;
				break;
			}
		}
		if (!isset($key)) {
			iajax(-1, '参数错误');
		}
		unset($else_icps[$key]);
		$icp_domains = array_column($else_icps, 'domain');

		if (in_array($domain, $icp_domains)) {
			iajax(-1, $domain . '已存在！');
		}
		$icps[$key]['icp'] = $icp;
		$icps[$key]['domain'] = $domain;
		$icps[$key]['policeicp_location'] = $policeicp_location;
		$icps[$key]['policeicp_code'] = $policeicp_code;
		$icps[$key]['electronic_license'] = $electronic_license;
		$settings['icps'] = iserializer($icps);
		setting_save($settings, 'copyright');
		iajax(0, '保存成功');
	}
}

if ($do == 'del_icp') {
	$id = safe_gpc_int($_GPC['id']);
	if (empty($id)) {
		iajax(-1, '参数错误');
	}
	$icps = $settings['icps'];
	foreach ($icps as $k => $icp) {
		if ($icp['id'] == $id) {
			$key = $k;
			break;
		}
	}
	if (!isset($key)) {
		iajax(-1, '参数错误');
	}
	unset($icps[$key]);
	$settings['icps'] = iserializer($icps);
	setting_save($settings, 'copyright');
	iajax(0, '删除成功');
}

if ($_W['isajax']) {
	$message = array(
		'settings' => $settings,
	);
	iajax(0, $message);
}

template('system/site');