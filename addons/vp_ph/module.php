<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
defined('IN_IA') or exit('Access Denied');
define('MD_NAME', 'vp_ph');
define('MD_BRANCH', 1);
class Vp_phModule extends WeModule
{
    public function settingsDisplay($settings)
    {
        global $_GPC, $_FILES, $_W;
        if (empty($settings) || count($settings) == 0) {
            if (MD_BRANCH < 10000) {
                $cnt = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('uni_account_modules') . ' WHERE module=:module', array(":module" => MD_NAME));
                if ($cnt > MD_BRANCH) {
                    echo '该应用数量已达上限，最多支持' . MD_BRANCH . '个公众号使用';
                    exit;
                }
            }
        }
        if (checksubmit()) {
            $cfg = array("fee_in1_1" => floatval($_GPC['fee_in1_1']), "fee_in_1" => floatval($_GPC['fee_in_1']), "fee_out_1" => floatval($_GPC['fee_out_1']), "fee_in1_2" => floatval($_GPC['fee_in1_2']), "fee_in_2" => floatval($_GPC['fee_in_2']), "fee_out_2" => floatval($_GPC['fee_out_2']), "slides" => $_GPC['slides'], "MD_BRANCH" => MD_BRANCH, "set_app_status" => intval($_GPC['set_app_status']), "set_app_hint" => $_GPC['set_app_hint'], "set_verify_status" => intval($_GPC['set_verify_status']), "set_app_name" => $_GPC['set_app_name'], "set_app_logo" => $_GPC['set_app_logo'], "set_app_bgclb" => $_GPC['set_app_bgclb'], "set_app_bgctr" => $_GPC['set_app_bgctr'], "set_app_bg" => $_GPC['set_app_bg'], "set_app_intro" => $_GPC['set_app_intro'], "set_acc_name" => $_GPC['set_acc_name'], "set_acc_logo" => $_GPC['set_acc_logo'], "set_acc_qrcode" => $_GPC['set_acc_qrcode'], "server_qrcode" => $_GPC['server_qrcode'], "app_qa" => htmlspecialchars_decode($_GPC['app_qa']), "edit_sp_pname" => intval($_GPC['edit_sp_pname']), "edit_sp_images" => intval($_GPC['edit_sp_images']), "tags_male" => $_GPC['tags_male'], "tags_female" => $_GPC['tags_female'], "pos_status" => intval($_GPC['pos_status']), "qmap_key" => $_GPC['qmap_key'], "coin_icon" => $_GPC['coin_icon'], "coin_name" => $_GPC['coin_name'], "look_free" => intval($_GPC['look_free']), "look_cnt" => intval($_GPC['look_cnt']), "look_fee" => intval($_GPC['look_fee']), "like_free" => intval($_GPC['like_free']), "like_cnt" => intval($_GPC['like_cnt']), "like_fee" => intval($_GPC['like_fee']), "super_free" => intval($_GPC['super_free']), "super_cnt" => intval($_GPC['super_cnt']), "super_fee" => intval($_GPC['super_fee']), "peek_fee" => intval($_GPC['peek_fee']), "inmoney_way" => intval($_GPC['inmoney_way']), "inmoney_coin1" => intval($_GPC['inmoney_coin1']), "inmoney_fee1" => floatval($_GPC['inmoney_fee1']), "inmoney_coin2" => intval($_GPC['inmoney_coin2']), "inmoney_fee2" => floatval($_GPC['inmoney_fee2']), "inmoney_coin3" => intval($_GPC['inmoney_coin3']), "inmoney_fee3" => floatval($_GPC['inmoney_fee3']), "outcash_way" => intval($_GPC['outcash_way']), "outcash_min" => intval($_GPC['outcash_min']), "outcash_max" => intval($_GPC['outcash_max']), "outcash_sp" => intval($_GPC['outcash_sp']), "invite_status" => intval($_GPC['invite_status']), "invite_logo" => $_GPC['invite_logo'], "invite_title" => $_GPC['invite_title'], "invite_desc" => $_GPC['invite_desc'], "invite_line" => $_GPC['invite_line'], "invite_poster" => $_GPC['invite_poster'], "invite_prize" => floatval($_GPC['invite_prize']), "invite_prize_limit" => intval($_GPC['invite_prize_limit']), "agent_fee" => floatval($_GPC['agent_fee']), "agent_name" => $_GPC['agent_name'], "agent_intro" => htmlspecialchars_decode($_GPC['agent_intro']), "agent_rate" => intval($_GPC['agent_rate']), "agent_rate1" => intval($_GPC['agent_rate1']), "agent_rate2" => intval($_GPC['agent_rate2']), "agent_poster" => $_GPC['agent_poster'], "agent_sphurl" => $_GPC['agent_sphurl'], "nt_pair" => $_GPC['nt_pair'], "share_post_logo" => $_GPC['share_post_logo'], "share_post_title" => $_GPC['share_post_title'], "share_post_desc" => $_GPC['share_post_desc'], "share_post_line" => $_GPC['share_post_line'], "auth_key" => trim($_GPC['auth_key']), "task_key" => $_GPC['task_key']);
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        load()->func('tpl');
        include $this->template('setting');
    }
}