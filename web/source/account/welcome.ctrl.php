<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('article');
load()->model('module');

if (!empty($_W['uid'])) {
	header('Location: ' . $_W['siteroot'] . 'web/home.php');
	exit;
}


$settings = $_W['setting'];

	$welcome_bind = pdo_get('system_welcome_binddomain', array('domain IN ' => array('http://' . $_SERVER['HTTP_HOST'], 'https://' . $_SERVER['HTTP_HOST'])));
	if (!empty($welcome_bind)) {
		$site = WeUtility::createModuleSystemWelcome($welcome_bind['module_name']);
		if (!is_error($site) && !empty($site)) {
			exit($site->systemWelcomeDisplay($welcome_bind['uid']));
		}
	}

$copyright = $settings['copyright'];
$copyright['slides'] = iunserializer($copyright['slides']);
if (isset($copyright['showhomepage']) && empty($copyright['showhomepage'])) {
	header('Location: ' . url('user/login'));
	exit;
}

$notices = article_notice_home();
$news = article_news_home();
template('account/welcome');
