<?php
/**
 * [翰飞网络科技 System] Copyright (c) 2018 WEBY.CC
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited http://w.4tiaomao.com/ for more details.
 */
load()->func('communication');
load()->model('cloud');
$prepare = cloud_prepare();
if (is_error($prepare)) {
	itoast($prepare['message'], url('cloud/profile'), 'error');
}

$step = $_GPC['step'];
$steps = array('files', 'schemas', 'scripts');
$step = in_array($step, $steps) ? $step : 'files';

if ($step == 'files' && $_W['ispost']) {
	$ret = cloud_download($_GPC['path'], $_GPC['type']);
	if (!is_error($ret)) {
		exit('success');
	}
	exit($ret['message']);
}

if ($step == 'scripts' && $_W['ispost']) {
	$fname = trim($_GPC['fname']);
	$entry = IA_ROOT . '/data/update/' . $fname;
	if (is_file($entry) && preg_match('/^update\(\d{12}\-\d{12}\)\.php$/', $fname)) {
		$evalret = include $entry;
		if (!empty($evalret)) {
			cache_build_users_struct();
			cache_build_setting();
			@unlink($entry);
			exit('success');
		}
	}
	exit('failed');
}

if (!empty($_GPC['m'])) {
	$m = $_GPC['m'];
	$type = 'module';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_m_build($_GPC['m']);
} elseif (!empty($_GPC['t'])) {
	$m = $_GPC['t'];
	$type = 'theme';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_t_build($_GPC['t']);
} elseif (!empty($_GPC['w'])) {
	$m = $_GPC['w'];
	$type = 'webtheme';
	$is_upgrade = intval($_GPC['is_upgrade']);
	$packet = cloud_w_build($_GPC['w']);
} else {
	$m = '';
	$packet = cloud_build();
}
if ($step == 'schemas' && $_W['ispost']) {
	$tablename = $_GPC['table'];
	foreach ($packet['schemas'] as $schema) {
		if (substr($schema['tablename'], 4) == $tablename) {
			$remote = $schema;
			break;
		}
	}
	if (!empty($remote)) {
		load()->func('db');
		$local = db_table_schema(pdo(), $tablename);
		$sqls = db_table_fix_sql($local, $remote);
		$error = false;
		foreach ($sqls as $sql) {
			if (pdo_query($sql) === false) {
				$error = true;
				$errormsg .= pdo_debug(false);
				break;
			}
		}
		if (!$error) {
			exit('success');
		}
	}
	exit;
}
if (!empty($packet) && (!empty($packet['upgrade']) || !empty($packet['install']))) {
	$schemas = array();
	if (!empty($packet['schemas'])) {
		foreach ($packet['schemas'] as $schema) {
			$schemas[] = substr($schema['tablename'], 4);
		}
	}
	$scripts = array();
	if (empty($packet['install'])) {
		$updatefiles = array();
		if (!empty($packet['scripts']) && empty($packet['type'])) {
			$updatedir = IA_ROOT . '/data/update/';
			load()->func('file');
			rmdirs($updatedir, true);
			mkdirs($updatedir);
			$cversion = IMS_VERSION;
			$crelease = IMS_RELEASE_DATE;
			foreach ($packet['scripts'] as $script) {
				if ($script['release'] <= $crelease) {
					continue;
				}
				$fname = "update({$crelease}-{$script['release']}).php";
				$crelease = $script['release'];
				$script['script'] = @base64_decode($script['script']);
				if (empty($script['script'])) {
					$script['script'] = <<<DAT
<?php
load()->model('setting');
setting_upgrade_version('{$packet['family']}', '{$script['version']}', '{$script['release']}');
return true;
DAT;
				}
				$updatefile = $updatedir . $fname;
				file_put_contents($updatefile, $script['script']);
				$updatefiles[] = $updatefile;
				$s = array_elements(array('message', 'release', 'version'), $script);
				$s['fname'] = $fname;
				$scripts[] = $s;
			}
		}
	}
} else {
	if (is_error($packet)) {
		itoast($packet['message'], '', 'error');
	} else {
		cache_delete('checkupgrade:system');
		cache_delete('cloud:transtoken');
		itoast('更新已完成. ', url('cloud/upgrade'), 'success');
	}
}
template('cloud/process');