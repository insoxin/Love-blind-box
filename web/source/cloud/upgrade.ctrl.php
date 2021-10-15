<?php
/**
 * [翰飞网络科技 System] Copyright (c) 2018 WEBY.CC
 * 翰飞网络科技 is NOT a free software, it under the license terms, visited http://w.4tiaomao.com/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('cloud');
load()->func('communication');
load()->func('db');
$r = cloud_prepare();
if (is_error($r)) {
    itoast($r['message'], url('cloud/profile') , 'error');
}
$dos = array(
    'upgrade'
);
$do = in_array($do, $dos) ? $do : 'upgrade';
uni_user_permission_check('system_cloud_upgrade');
if (empty($_W['setting']['site']['profile_perfect'])) {
}
if ($do == 'upgrade') {
    $_W['page']['title'] = '一键更新 - 云服务';
    if (empty($_W['setting']['cloudip']) || $_W['setting']['cloudip']['expire'] < TIMESTAMP) {
        $cloudip = gethostbyname('sq.jxxdvip.com');
        if (empty($cloudip) || !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $cloudip)) {
            itoast('云服务域名解析失败，请查看服务器DNS设置或是在“云服务诊断”中手动设置云服务IP', url('cloud/diagnose') , 'error');
        }
        setting_save(array(
            'ip' => $cloudip,
            'expire' => TIMESTAMP + 3600
        ) , 'cloudip');
    }
    if (checksubmit('submit')) {
        $upgrade = cloud_build();
        if (is_error($upgrade)) {
            itoast($upgrade['message'], '', 'error');
        }
        if ($upgrade['upgrade']) {
            itoast("检测到新版本: <strong>{$upgrade['version']} (Release {$upgrade['release']})</strong>, 请立即更新.", 'refresh');
        } else {
            cache_delete('checkupgrade:system');
            cache_delete('cloud:transtoken');
            itoast('检查结果: 恭喜, 你的程序已经是最新版本. ', 'refresh');
        }
    }
    cache_load('upgrade');
    if (!empty($_W['cache']['upgrade'])) {
        $upgrade_cache = $_W['cache']['upgrade'];
    }
    if (!empty($upgrade_cache) || TIMESTAMP - $upgrade_cache['lastupdate'] >= 3600 * 24 || empty($upgrade_cache['data'])) {
        $upgrade = cloud_build();
    } else {
        $upgrade = $upgrade_cache['data'];
    }
    cache_delete('cloud:transtoken');
    if (!empty($upgrade['schemas'])) {
        $upgrade['database'] = array();
        foreach ($upgrade['schemas'] as $remote) {
            $row = array();
            $row['tablename'] = $remote['tablename'];
            $name = substr($remote['tablename'], 4);
            $local = db_table_schema(pdo() , $name);
            unset($remote['increment']);
            unset($local['increment']);
            if (empty($local)) {
                $row['new'] = true;
            } else {
                $row['new'] = false;
                $row['fields'] = array();
                $row['indexes'] = array();
                $diffs = db_schema_compare($local, $remote);
                if (!empty($diffs['fields']['less'])) {
                    $row['fields'] = array_merge($row['fields'], $diffs['fields']['less']);
                }
                if (!empty($diffs['fields']['diff'])) {
                    $row['fields'] = array_merge($row['fields'], $diffs['fields']['diff']);
                }
                if (!empty($diffs['indexes']['less'])) {
                    $row['indexes'] = array_merge($row['indexes'], $diffs['indexes']['less']);
                }
                if (!empty($diffs['indexes']['diff'])) {
                    $row['indexes'] = array_merge($row['indexes'], $diffs['indexes']['diff']);
                }
                $row['fields'] = implode($row['fields'], ' ');
                $row['indexes'] = implode($row['indexes'], ' ');
            }
            $upgrade['database'][] = $row;
        }
    }
    $path = IA_ROOT . '/data/patch/' . date('Ymd') . '/';
    if (is_dir($path)) {
        if ($handle = opendir($path)) {
            while (false !== ($patchpath = readdir($handle))) {
                if ($patchpath != '.' && $patchpath != '..') {
                    if (is_dir($path . $patchpath)) {
                        $patchs[] = $patchpath;
                    }
                }
            }
        }
        if (!empty($patchs)) {
            sort($patchs, SORT_NUMERIC);
        }
    }
}
template('cloud/upgrade');