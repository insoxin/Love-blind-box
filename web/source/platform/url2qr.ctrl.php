<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('account');
load()->func('communication');
load()->library('qrcode');

$dos = array('display', 'qr', 'chat', 'down_qr');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'display';
permission_check_account_user('platform_qr_qr');

if ('display' == $do) {
	template('platform/url2qr');
}

if ('qr' == $do) {
	$url = $_GPC['url'];
	$errorCorrectionLevel = 'L';
	$matrixPointSize = '5';
	QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
	exit();
}

if ('down_qr' == $do) {
	$qrlink = $_GPC['qrlink'];
	$errorCorrectionLevel = 'L';
	$matrixPointSize = '5';
	$qr_pic = QRcode::png($qrlink, false, $errorCorrectionLevel, $matrixPointSize);
	$name = random(8);
	header('cache-control:private');
	header('content-type:image/jpeg');
	header('content-disposition: attachment;filename="' . $name . '.jpg"');
	readfile($qr_pic);
	exit;
}