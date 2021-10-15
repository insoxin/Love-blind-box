<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'ready_audit', 'commit', 'get_qrcode', 'submit_audit', 'release', 'undo_code_audit',
			'revert_code_release', 'delete_audit');
$do = in_array($do, $dos) ? $do : 'display';

if ('display' == $do) {
	$register_info = table('wxapp_register_version')->getByUniacid($_W['uniacid']);
	if (!empty($register_info)) {
		$register_info['upload_time'] = date('Y-m-d H:i:s', $register_info['upload_time']);
	}

	$result = $_W['account']->getLatestAuditStatus();
	if (!is_error($result) && $result['auditid'] == $register_info['auditid']) {
		switch ($result['status']) {
			case 0:
				miniapp_change_register_version_status($result['auditid'], WXAPP_REGISTER_VERSION_STATUS_CHECKSUCCESS);
				$register_info['status'] = WXAPP_REGISTER_VERSION_STATUS_CHECKSUCCESS;
				break;
			case 1:
				miniapp_change_register_version_status($result['auditid'], WXAPP_REGISTER_VERSION_STATUS_CHECKFAIL, $result['reason']);
				$register_info['status'] = WXAPP_REGISTER_VERSION_STATUS_CHECKFAIL;
				$register_info['reason'] = $result['reason'];
				break;
			case 3:
				miniapp_change_register_version_status($result['auditid'], WXAPP_REGISTER_VERSION_STATUS_RETRACT);
				$register_info['status'] = WXAPP_REGISTER_VERSION_STATUS_RETRACT;
				break;
		}
	}
	template('miniapp/platform-version');
}

if ('ready_audit' == $do) {
		$wxapp_page = $_W['account']->getWxappPage();
	if (is_error($wxapp_page)) {
		iajax(-1, '获取小程序的第三方提交代码的页面配置失败！具体原因：' . $wxapp_page['message']);
	}

		$wxapp_category = $_W['account']->getWxappCategory();
	if (is_error($wxapp_category)) {
		iajax(-1, '获取授权小程序帐号已设置的类目失败！具体原因：' . $wxapp_category['message']);
	}

	template('miniapp/platform-ready-audit');
}

if ('commit' == $do) {
	$commit_type = in_array($_GPC['commit_type'], array('develop', 'audit')) ? $_GPC['commit_type'] : 'develop';
	$draft_list = $_W['account']->getTemplateDraftList();
	if (is_error($draft_list)) {
		iajax(-1, $draft_list['errmsg']);
	}
	$keysValue = array();
	$draft_id = 0;
		foreach ($draft_list['draft_list'] as $draft_key => $draft_value) {
		if ($_W['setting']['platform']['bindappid'] != $draft_value['source_miniprogram_appid']) {
			continue;
		}
		$keysValue[$draft_key] = $draft_value['create_time'];
	}
	if (!empty($keysValue)) {
		array_multisort($keysValue, SORT_ASC, $draft_list['draft_list']);
		$draft = array_pop($draft_list['draft_list']);
		$draft_id = $draft['draft_id'];
	}
	if (empty($draft_id)) {
		iajax(-1, '绑定的开发小程序在草稿箱内无版本！');
	}
	
		$template_list = $_W['account']->getTemplatelist();
	if (is_error($template_list)) {
		iajax(-1, $template_list['errmsg']);
	}
	foreach($template_list['template_list'] as $template) {
		if (!empty($template['template_id']) && $_W['setting']['platform']['bindappid'] == $template['source_miniprogram_appid']) {
			$_W['account']->deleteTemplate($template['template_id']);
		}
	}
	$add_to_template = $_W['account']->addToTemplate($draft_id);
	if (is_error($add_to_template)) {
		iajax(-1, $add_to_template['errmsg']);
	}
	$template_info = array();
	$template_list = $_W['account']->getTemplatelist();
	if (is_error($template_list)) {
		iajax(-1, $template_list['errmsg']);
	}
	foreach($template_list['template_list'] as $template) {
		if ((!empty($template['template_id']) || $template['template_id'] == 0) && $_W['setting']['platform']['bindappid'] == $template['source_miniprogram_appid']) {
			$template_info = $template;
		}
	}
	if (empty($template_info)) {
		iajax(-1, '模板库中没有绑定开发小程序的模板');
	}
	$ext_json = array('extEnable' => true, 'extAppid' => $_W['account']['key']);
	$result = $_W['account']->commit($template_info['template_id'], $ext_json, $template_info['user_version'], $template_info['user_desc']);
	if (is_error($result)) {
		iajax(-1, $result['message']);
	}
	$data = array(
		'version_id' => $version_id,
		'uniacid' => $_W['uniacid'],
		'version' => $template_info['user_version'],
		'description' => $template_info['user_desc'],
		'developer' => $template_info['developer'],
		'upload_time' => TIMESTAMP,
		'status' => WXAPP_REGISTER_VERSION_STATUS_DEVELOP,
	);
	if ('audit' == $commit_type) {
		$submit_audit = $_W['account']->submitAudit();
		if (is_error($submit_audit)) {
			iajax(-1, $submit_audit['message']);
		}
		$data['auditid'] = $submit_audit['auditid'];
		$data['status'] = WXAPP_REGISTER_VERSION_STATUS_CHECKING;
	}
	
	$update_status = miniapp_add_register_version($data);
	iajax(0, '上传成功！');
}
if ('get_qrcode' == $do) {
	$path = safe_gpc_string($_GPC['path']);
	$result = $_W['account']->getQrcode($path);
	if (is_error($result)) {
		iajax($result['errno'], $result['message']);
	}
	header('Content-Type: image/jpeg;text/html; charset=utf-8');
	echo $result['content'];
	exit;
}
if ('submit_audit' == $do) {
	$data = safe_gpc_array($_GPC['item_list']);
	$result = $_W['account']->submitAudit($data);
	if (is_error($result)) {
		iajax($result['errno'], $result['message']);
	}
	$update_status = miniapp_create_submit_audit($version_id, $result['auditid']);
	if ($update_status) {
		iajax(0, '已将第三方提交的代码包提交审核');
	} else {
		iajax(-1, '已将第三方提交的代码包提交审核,但数据更新失败！');
	}
}
if ('release' == $do) {
	$result = $_W['account']->release();
	if (is_error($result)) {
		iajax($result['errno'], $result['message']);
	}
	$update_status = miniapp_create_release($version_id);
	if ($update_status) {
		iajax(0, '发布成功！');
	} else {
		iajax(-1, '发布失败！');
	}
}
if ('delete_audit' == $do) {
	$result = miniapp_delete_audit($version_id, WXAPP_REGISTER_VERSION_STATUS_CHECKFAIL);
	if ($result) {
		iajax(0, '删除成功！');
	} else {
		iajax(-1, '删除失败！');
	}
}
if ('undo_code_audit' == $do) {
	$result = $_W['account']->undoCodeAudit();
	if (is_error($result)) {
		iajax($result['errno'], $result['message']);
	}
	$wxapp_register_version_info = pdo_get('wxapp_register_version', array('uniacid' => $_W['uniacid'], 'version_id' => $version_id, 'status' => WXAPP_REGISTER_VERSION_STATUS_CHECKING), array('uniacid', 'version_id', 'auditid'));
	$result = miniapp_delete_audit($version_id, WXAPP_REGISTER_VERSION_STATUS_CHECKING);
	if ($result) {
		if (!empty($wxapp_register_version_info)) {
			pdo_insert('wxapp_undocodeaudit_log', array('uniacid' => $wxapp_register_version_info['uniacid'], 'version_id' => $wxapp_register_version_info['version_id'], 'auditid' => $wxapp_register_version_info['auditid'], 'revoke_time' => time()));
		}
		iajax(0, '撤回成功！');
	} else {
		iajax(-1, '撤回失败！');
	}
}
if ('revert_code_release' == $do) {
	$result = $_W['account']->revertCodeRelease();
	if (is_error($result)) {
		iajax($result['errno'], $result['message']);
	}
	iajax(0, '回退成功！');
}