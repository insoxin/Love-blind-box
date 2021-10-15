<?php
/**
 * [翰飞网络科技 System] Copyright (c) 2018 
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited for more details.
 */
defined('IN_IA') or exit('Access Denied');
define('HTTP_X_FOR', (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://');
define('CLOUD_GATEWAY_URL', HTTP_X_FOR .'sq.jxxdvip.com/app/mod.php');
define('CLOUD_GATEWAY_URL_NORMAL', HTTP_X_FOR .'sq.jxxdvip.com/app/mod.php');
function cloud_client_define() {
    return array(
        '/framework/function/communication.func.php',
        '/framework/model/mod_wmall.mod.php',
        '/web/source/mod/mod_wmall.ctrl.php',
        '/web/source/mod/process_wmall.ctrl.php',
        '/web/source/mod/dock.ctrl.php',
        '/web/themes/default/mod/mod_wmall.html',
        '/web/themes/default/mod/process_wmall.html'
    );
}
function _cloud_build_params() {
    global $_W;
    $pars = array();
    $pars['family'] = MOD_FAMILY;
    $pars['version'] = MOD_VERSION;
    $pars['release'] = MOD_RELEASE_DATE;
	$pars['modname'] = MOD_WMALL;
	$pars['host'] = trim(preg_replace('/http(s)?:\\/\\//', '', trim($_W['siteroot'], '/')));
    $pars['key'] = $_W['setting']['site']['key'];
    $pars['token'] = $_W['setting']['site']['token'];
    //$pars['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
    $clients = cloud_client_define();
    $string = '';
    foreach ($clients as $cli) {
        $string.= md5_file(IA_ROOT . $cli);
    }
    $pars['client'] = md5($string);
    return $pars;
}
function _cloud_shipping_parse($dat, $file) {
    if (is_error($dat)) {
        return error(-1, '网络传输错误, 请检查您的cURL是否可用, 或者服务器网络是否正常. ' . $dat['message']);
    }
    $tmp = unserialize($dat['content']);
    if (is_array($tmp) && is_error($tmp)) {
        if ($tmp['errno'] == '-2') {
            $data = file_get_contents(IA_ROOT . '/addons/we7_wmall/version.php');
            file_put_contents(IA_ROOT . '/addons/we7_wmall/version.php', str_replace("'x'", "'v'", $data));
        }
        return $tmp;
    }
    if ($dat['content'] == 'patching') {
        return error(-1, '补丁程序正在更新中，请稍后再试！');
    }
    if ($dat['content'] == 'frequent') {
        return error(-1, '更新操作太频繁，请稍后再试！');
    }
    $data = @file_get_contents($file);
    @unlink($file);
    $ret = @iunserializer($data);
    $ret = iunserializer($ret['data']);
    if (is_array($ret) && is_error($ret)) {
        if ($ret['errno'] == '-2') {
            $data = file_get_contents(IA_ROOT . '/addons/we7_wmall/version.php');
            file_put_contents(IA_ROOT . '/addons/we7_wmall/version.php', str_replace("'x'", "'v'", $data));
        }
    }
    if (!is_error($ret) && is_array($ret) && !empty($ret)) {
        if ($ret['state'] == 'fatal') {
            return error($ret['errorno'], '发生错误: ' . $ret['message']);
        }
        return $ret;
    } else {
        return error($ret['errno'], "发生错误: {$ret['message']}");
    }
}
function cloud_request($url, $post = '', $extra = array() , $timeout = 60) {
    global $_W;
    load()->func('communication');
    if (!empty($_W['setting']['cloudip']['ip']) && empty($extra['ip'])) {
        //$extra['ip'] = $_W['setting']['cloudip']['ip'];
        //$extra['ip'] = "sq.jxxdvip.com";
        
    }
    return ihttp_request($url, $post, $extra, $timeout);
}

function cloud_build() {
	
	global $_W;
	load()->func('communication');
	if (empty($_W['setting']['site']['token'])) {		
		$sets = array('key' => '001','token' =>'meil','url' => 'sq.jxxdvip.com','version' => '18.3.0' ,'profile_perfect' => '1');		
		pdo_insert('core_settings', array('value' => iserializer($sets), 'key' => 'site'));	
	}				
    $pars = _cloud_build_params();
    $pars['method'] = 'application.build2';
    //$pars['extra'] = cloud_extra_account();
    $dat = cloud_request(HTTP_X_FOR .'sq.jxxdvip.com/app/mod.php', $pars);
    $file = IA_ROOT . '/data/application.build';
    $ret = _cloud_shipping_parse($dat, $file);
    if (!is_error($ret)) {
        if ($ret['state'] == 'warning') {
            $ret['files'] = cloud_client_define();
            unset($ret['schemas']);
            unset($ret['scripts']);
        } else {
            $files = array();
            if (!empty($ret['files'])) {
                foreach ($ret['files'] as $file) {
                    $entry = IA_ROOT . $file['path'];
                    if (!is_file($entry) || md5_file($entry) != $file['hash']) {
                        $files[] = $file['path'];
                    }
                }
            }
            $ret['files'] = $files;
            if (!empty($ret['files'])) {
                cloud_bakup_files($ret['files']);
            }
            $schemas = array();
            if (!empty($ret['schemas'])) {
                load()->func('db');
                foreach ($ret['schemas'] as $remote) {
                    $name = substr($remote['tablename'], 4);
                    $local = db_table_schema(pdo() , $name);
                    unset($remote['increment']);
                    unset($local['increment']);
                    if (empty($local)) {
                        $schemas[] = $remote;
                    } else {
                        $sqls = db_table_fix_sql($local, $remote);
                        if (!empty($sqls)) {
                            $schemas[] = $remote;
                        }
                    }
                }
            }
            $ret['schemas'] = $schemas;
        }
        if ($ret['family'] == 'x' && MOD_FAMILY == 'v') {
            load()->model('setting_wmall');
            setting_upgrade_version_wmall('x', MOD_VERSION, MOD_RELEASE_DATE);
            itoast('您已经购买了商业授权版本, 系统将转换为商业版, 并重新运行自动更新程序.', 'refresh');
        }
		$crelease = MOD_RELEASE_DATE;
        if ($ret['release'] <= $crelease) {
			unset($ret['scripts']);
		}
        $ret['upgrade'] = false;
        if (!empty($ret['files']) || !empty($ret['schemas']) || !empty($ret['scripts'])) {
            $ret['upgrade'] = true;
        }
        $upgrade = array();
        $upgrade['upgrade'] = $ret['upgrade'];
        $upgrade['data'] = $ret;
        $upgrade['lastupdate'] = TIMESTAMP;
        cache_write('upgrade', $upgrade);
        //cache_write('cloud:transtoken', authcode($ret['token'], 'ENCODE'));
    }
    return $ret;
}
function cloud_schema() {
    $pars = _cloud_build_params();
    $pars['method'] = 'application.schema';
    $dat = cloud_request(HTTP_X_FOR .'sq.jxxdvip.com/app/mod.php', $pars);
    $file = IA_ROOT . '/data/application.schema';
    $ret = _cloud_shipping_parse($dat, $file);
    if (!is_error($ret)) {
        $schemas = array();
        if (!empty($ret['schemas'])) {
            load()->func('db');
            foreach ($ret['schemas'] as $remote) {
                $name = substr($remote['tablename'], 4);
                $local = db_table_schema(pdo() , $name);
                unset($remote['increment']);
                unset($local['increment']);
                if (empty($local)) {
                    $schemas[] = $remote;
                } else {
                    $diffs = db_schema_compare($local, $remote);
                    if (!empty($diffs)) {
                        $schemas[] = $remote;
                    }
                }
            }
        }
        $ret['schemas'] = $schemas;
    }
    return $ret;
}
function cloud_download($path, $type = '') {
    $pars = _cloud_build_params();
    $pars['method'] = 'application.shipping';
    $pars['path'] = $path;
    $pars['type'] = $type;
    $pars['gz'] = function_exists('gzcompress') && function_exists('gzuncompress') ? 'true' : 'false';
    $pars['download'] = 'true';
    $headers = array(
        'content-type' => 'application/x-www-form-urlencoded'
    );
    $dat = cloud_request(HTTP_X_FOR .'sq.jxxdvip.com/app/mod.php', $pars, $headers, 300);
    if (is_error($dat)) {
        return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
    }
    if ($dat['content'] == 'success') {
        return true;
    }
    $ret = @json_decode($dat['content'], true);
    if (is_error($ret)) {
        return $ret;
    } else {
        $post = $dat['content'];
        $data = base64_decode($post);
        if (base64_encode($data) !== $post) {
            $data = $post;
        }
        $ret = iunserializer($data);
        $gz = function_exists('gzcompress') && function_exists('gzuncompress');
        $file = base64_decode($ret['file']);
        if ($gz) {
            $file = gzuncompress($file);
        }
        $_W['setting']['site']['token'] = authcode(cache_load('cloud:transtoken') , 'DECODE');
        $string = (md5($file) . $ret['path'] . $_W['setting']['site']['token']);
        if (!empty($_W['setting']['site']['token']) && md5($string) === $ret['sign']) {
            $path = IA_ROOT . $ret['path'];
            load()->func('file');
            @mkdirs(dirname($path));
            if (file_put_contents($path, $file)) {
                return true;
            } else {
                return error(-1, '写入失败');
            }
        }
        return error(-1, '写入失败');
    }
}

function cloud_bakup_files($files) {
    global $_W;
    if (empty($files)) {
        return false;
    }
    $map = json_encode($files);
    $hash = md5($map . $_W['config']['setting']['authkey']);
    if ($handle = opendir(IA_ROOT . '/data/patch/' . date('Ymd'))) {
        while (false !== ($patchpath = readdir($handle))) {
            if ($patchpath != '.' && $patchpath != '..') {
                if (strexists($patchpath, $hash)) {
                    return false;
                }
            }
        }
    }
    $path = IA_ROOT . '/data/patch/' . date('Ymd') . '/' . date('Hi') . '_' . $hash;
    load()->func('file');
    if (!is_dir($path) && mkdirs($path)) {
        foreach ($files as $file) {
            if (file_exists(IA_ROOT . $file)) {
                mkdirs($path . '/' . dirname($file));
                file_put_contents($path . '/' . $file, file_get_contents(IA_ROOT . $file));
            }
        }
        file_put_contents($path . '/' . 'map.json', $map);
    }
    return false;
}



