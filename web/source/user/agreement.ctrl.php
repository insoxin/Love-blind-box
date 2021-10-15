<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('post');
$do = in_array($do, $dos) ? $do : 'post';

$settings = $_W['setting']['register'];

if ('post' == $do) {
	if ($_W['ispost']) {
		$agreement_info = trim($_GPC['agreement_info']) ? trim($_GPC['agreement_info']) : '';
		if (empty($agreement_info)) {
			if ($_W['isajax']) {
				iajax(-1, '协议内容不能为空');
			}
			itoast('协议内容不能为空', '', 'error');
		}
		$settings['agreement_info'] = safe_gpc_html(htmlspecialchars_decode($agreement_info));
		setting_save($settings, 'register');
		if ($_W['isajax']) {
			iajax(0, '编辑成功!');
		}
		itoast('编辑成功!', '', 'success');
	}

	if ($_W['isajax']) {
		$message = array(
			'settings' => $settings
		);
		iajax(0, $message);
	}
	template('user/agreement');

}
