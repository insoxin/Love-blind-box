<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->model('module');
load()->model('mc');
load()->model('material');

$dos = array('post', 'send', 'preview');
$do = in_array($do, $dos) ? $do : 'post';

if ('post' == $do) {
	permission_check_account_user('platform_masstask_post');
	$groups = mc_fans_groups();
	$account_api = WeAccount::createByUniacid();
	$supports = $account_api->getMaterialSupport();
	$show_post_content = $supports['mass'];

	if (checksubmit('submit')) {
		$type = in_array(intval($_GPC['type']), array(0, 1)) ? intval($_GPC['type']) : 0;
		$group = @json_decode(safe_gpc_html(htmlspecialchars_decode($_GPC['group'])), true);

		if (empty($_GPC['reply'])) {
			itoast('请选择要群发的素材', '', 'error');
		}
		$mass_record = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'groupname' => htmlspecialchars($group['name']),
			'fansnum' => htmlspecialchars($group['count']),
			'msgtype' => '',
			'group' => $group['id'],
			'attach_id' => '',
			'media_id' => '',
			'status' => 1,
			'type' => $type,
			'sendtime' => TIMESTAMP,
			'createtime' => TIMESTAMP,
			'cron_id' => 0,
		);
		foreach ($_GPC['reply'] as $material_type => $material) {
			if (empty($material)) {
				continue;
			}
			list($temp, $msgtype) = explode('_', $material_type);
			$attachment = material_get($material);
			if ('local' == $attachment['model']) {
				itoast('图文素材请选择微信素材', '', 'error');
			}

			if ('reply_basic' == $material_type) {
				$material = safe_gpc_html(htmlspecialchars_decode($material));
				$material = trim($material, '\"');
			}
			if ($msgtype == 'basic') {
				$mass_record['content'] = $material;
			}
			$mass_record['media_id'] = $material;
			$mass_record['attach_id'] = $attachment['id'];
			$mass_record['msgtype'] = $msgtype;
			break;
		}

		$account_api = WeAccount::createByUniacid();
		$msgtype = 'basic' == $msgtype ? 'text' : $msgtype;
		if ('text' == $msgtype) {
			$mass_record['media_id'] = urlencode(emoji_unicode_decode($mass_record['media_id']));
		}
		$result = $account_api->fansSendAll($group['id'], $msgtype, $mass_record['media_id']);
		if (is_error($result)) {
			itoast($result['message'], url('platform/mass'), 'info');
		}
		if ('news' == $msgtype) {
			$mass_record['msg_id'] = $result['msg_id'];
			$mass_record['msg_data_id'] = $result['msg_data_id'];
		}
		$mass_record['status'] = 0;
		pdo_insert('mc_mass_record', $mass_record);
		itoast('立即发送设置成功', url('platform/mass/send'), 'success');
	}
	template('platform/mass-post');
}

if ('send' == $do) {
	permission_check_account_user('platform_masstask_send');
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' WHERE `uniacid` = :uniacid';
	$params = array();
	$params[':uniacid'] = $_W['uniacid'];
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_mass_record') . $condition, $params);
	$lists = pdo_getall('mc_mass_record', array('uniacid' => $_W['uniacid']), array(), '', 'id DESC', 'LIMIT ' . ($pindex - 1) * $psize . ',' . $psize);
	$types = array('basic' => '文本消息', 'text' => '文本消息', 'image' => '图片消息', 'voice' => '语音消息', 'video' => '视频消息', 'news' => '图文消息', 'wxcard' => '微信卡券');
	$pager = pagination($total, $pindex, $psize);
	template('platform/mass-send');
}

if ('preview' == $do) {
	$wxname = trim($_GPC['wxname']);
	if (empty($wxname)) {
		iajax(1, '微信号不能为空', '');
	}
	$type = trim($_GPC['type']);
	$media_id = trim($_GPC['media_id']);
	$account_api = WeAccount::createByUniacid();
	$data = $account_api->fansSendPreview($wxname, $media_id, $type);
	if (is_error($data)) {
		iajax(-1, $data['message'], '');
	}
	iajax(0, 'success', '');
}