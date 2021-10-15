<?php 
/**
 * [翰飞网络科技 System] Copyright (c) 2018 WEBY.CC
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited http://w.4tiaomao.com/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->model('setting');

$dos = array(
	'auth',
	'callback',
	'build',
	'init',
	'schema',
	'download',
	'module.query',
	'module.info',
	'module.build',
	'module.setting.cloud',
	'theme.query',
	'theme.info',
	'theme.build',
	'application.build',
	'sms.send',
	'sms.info',
	'api.oauth',
);
$do = in_array($do, $dos) ? $do : '';


if($do == 'callback') {
	$secret = $_GPC['token'];
	if(!empty($secret)) {
			$site = json_decode(base64_decode($secret),true);
			setting_save($site, 'site');
			exit("1");
	}
}

if($do != 'auth') {
	if(is_error(cloud_prepare())) {
		exit('cloud service is unavailable.');
	}
}

$post = file_get_contents('php://input');

if($do == 'auth') {
	$secret = random(32);
	$auth = @json_decode(base64_decode($post), true);
	if(empty($auth)) {
		exit;
	}
	$auth['secret'] = $secret;
	cache_write('cloud:auth:transfer', $auth);
	exit($secret);
}

if($do == 'build') {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/application.build', iserializer($ret));
		exit($secret);
	}
}

if($do == 'schema') {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/application.schema', iserializer($ret));
		exit($secret);
	}
}

if($do == 'download') {
	$data = base64_decode($post);
	if (base64_encode($data) !== $post) {
		$data = $post;
	}
	$ret = iunserializer($data);
	$gz = function_exists('gzcompress') && function_exists('gzuncompress');
	$file = base64_decode($ret['file']);

	if($gz) {
		$file = gzuncompress($file);
	}
	
	//$_W['setting']['site']['token'] = authcode(cache_load('cloud:transtoken'), 'DECODE');
	$string = (md5($file) . $ret['path'] . $_W['setting']['site']['token']);
	if(!empty($_W['setting']['site']['token']) && md5($string) === $ret['sign']) {
		$path = IA_ROOT . $ret['path'];
		load()->func('file');
		@mkdirs(dirname($path));
		file_put_contents($path, $file);
		$sign = md5(md5_file($path) . $ret['path'] . $_W['setting']['site']['token']);
		if($ret['sign'] === $sign) {
			exit('success');
		}
	}
	exit("failed$post ".$_W['setting']['site']['token']);
}

if(in_array($do, array('module.query', 'module.info', 'module.build', 'theme.query', 'theme.info', 'theme.build', 'application.build'))) {
	$dat = __secure_decode($post);
	if(!empty($dat)) {
		$secret = random(32);
		$ret = array();
		$ret['data'] = $dat;
		$ret['secret'] = $secret;
		file_put_contents(IA_ROOT . '/data/' . $do, iserializer($ret));
		exit($secret);
	}
}

if ($do == 'module.setting.cloud') {
	$data = __secure_decode($post);
	$data = unserialize($data);
	$setting = $data['setting'];
	$uniacid = $data['acid'];
	
	foreach ($data['struct'] as $name => $type) {
		if ($type == 'image') {
			$url = $setting[$name];
			if (empty($url)) {
				$setting[$name] = '';
			} else {
				$attach = cloud_resource_to_local($uniacid, 'image', $url);
				if (!is_error($attach)) {
					$setting[$name] = $attach['attachment'];
				} else {
					echo "单图上传(字段: {$name})中图片本地化失败. ";
					exit;
				}
			}
		} elseif ($type == 'richtext'){
			$content = $setting[$name];
			if (empty($content)) {
				$setting[$name] = '';
				continue;
			}
			preg_match_all('/src=&quot;(\S*)&quot;/', $content, $matches);
			if ($matches[1]) {
				$new_urls = array();
				foreach ($matches[1] as $url) {
					$attach = cloud_resource_to_local($uniacid, 'image', $url);
					if (!is_error($attach)) {
						$new_urls[] = $attach['url'];;
					} else {
						echo "富文本(字段 {$name})中图片本地化失败";
						exit;
					}
				};
				$setting[$name] = str_replace($matches[1], $new_urls, $setting[$name]);
			} else {
				$setting[$name] = $content;
			}
		} elseif ($type == 'images'){
			if (empty($setting[$name])) {
				$setting[$name] = array();
				continue;
			}
			foreach ($setting[$name] as $idx => $url) {
				if (empty($url)) {
					$setting[$name][$idx] = '';
					continue;
				} else {
					$attach = cloud_resource_to_local($uniacid, 'image', $url);
					if (!is_error($attach)) {
						$setting[$name][$idx] = $attach['attachment'];
					} else {
						echo "多图上传(字段 {$name})中图片本地化失败";
						exit;
					}
				}
			}
		}
	}
	
	$_W['uniacid'] = $data['acid'];
	$module = WeUtility::createModule($data['module']);
	$module->saveSettings($setting);
	cache_write("modulesetting:{$data['acid']}:{$data['module']}", $setting);
	
	echo 'success';
	exit;
}

if ($do == 'sms.send') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
}

if ($do == 'sms.info') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
	if(!empty($dat) && is_array($dat)) {
		setting_save($dat, "sms.info");
		cache_clean();
		die('success');
	}
	die('fail');
}

if ($do == 'api.oauth') {
	$dat = __secure_decode($post);
	$dat = iunserializer($dat);
	if(!empty($dat) && is_array($dat)) {
		if ($dat['module'] == 'core') {
			$result = file_put_contents(IA_ROOT.'/framework/builtin/core/module.cer', $dat['access_token']);
		} else {
			$result = file_put_contents(IA_ROOT."/addons/{$dat['module']}/module.cer", $dat['access_token']);
		}
		if ($result !== false) {
			die('success');
		}
		die('获取到的访问云API的数字证书写入失败.');
	}
	die('获取云API授权失败: api oauth.');
}

function __secure_decode($post) {
	global $_W;
	$data = base64_decode($post);
	if (base64_encode($data) !== $post) {
		$data = $post;
	}
	$ret = iunserializer($data);
	$string = ($ret['data'] . $_W['setting']['site']['token']);
	if(md5($string) === $ret['sign']) {
		return $ret['data'];
	}
	return false;
}