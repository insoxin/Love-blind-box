<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function setting_save($data = '', $key = '') {
	if (empty($data) && empty($key)) {
		return FALSE;
	}
	if (is_array($data) && empty($key)) {
		foreach ($data as $key => $value) {
			$record[] = "('$key', '" . iserializer($value) . "')";
		}
		if ($record) {
			$return = pdo_query("REPLACE INTO " . tablename('core_settings') . " (`key`, `value`) VALUES " . implode(',', $record));
		}
	} else {
		$return = table('coresetting')->settingSave($key, $data);
	}
	$cachekey = "setting";
	cache_write($cachekey, '');
	return $return;
}


function setting_load($key = '') {
	global $_W;
	$cachekey = "setting";
	$settings = cache_load($cachekey);
	if (empty($settings)) {
		$settings = pdo_fetchall('SELECT * FROM ' . tablename('core_settings'), array(), 'key');
		if (is_array($settings)) {
			foreach ($settings as $k => &$v) {
				$settings[$k] = iunserializer($v['value']);
			}
		}
		cache_write($cachekey, $settings);
	}
	if (!is_array($_W['setting'])) {
		$_W['setting'] = array();
	}
	$_W['setting'] = array_merge($_W['setting'], $settings);
	if (!empty($key)) {
		return array($key => $settings[$key]);
	} else {
		return $settings;
	}
}

function setting_upgrade_version_wmall($version, $release) {
	$verfile = IA_ROOT . '/addons/we7_wmall/version.php';
	$verdat = <<<VER
<?php
/**
 * [翰飞网络科技 System] Copyright (c) 2018 
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited for more details.
 */
defined('IN_IA') or exit('Access Denied');

define('MOD_VERSION', '{$version}');
define('MOD_RELEASE_DATE', '{$release}');
VER;
	return file_put_contents($verfile, trim($verdat));
}
