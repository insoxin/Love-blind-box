<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'detail');
$do = in_array($do, $dos) ? $do : 'display';

if($do == 'display') {
	$profile = mc_fetch($_W['member']['uid'], array('nickname', 'avatar', 'mobile', 'groupid'));
	$mcgroups = mc_groups();
	$profile['group'] = $mcgroups[$profile['groupid']];
	$stores = pdo_fetchall('SELECT * FROM ' . tablename('activity_stores') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
}

if($do == 'detail') {
	$id = intval($_GPC['id']);
	$store = pdo_get('activity_stores', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($store)) {
		message('门店不存在或已删除', referer(), 'error');
	}
	$store['photo_list'] = iunserializer($store['photo_list']);
	$store['category'] = iunserializer($store['category']);
}
template('mc/store');