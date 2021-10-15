<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$this->_doMobileAuth();
$user = $this->_user;
$is_user_infoed = $this->_is_user_infoed;
$this->_doMobileInitialize();
$cmd = $this->_cmd;
$mine = $this->_mine;
$cmd = $_GPC["cmd"];
if ($mine["agent"] == 0) {
    if ($_GPC["submit"] == "join") {
        $fee = floatval($_W["module_setting"]["agent_fee"]);
        if ($fee <= 0) {
            return $this->returnError("支付金额有误");
        }
        $money = floor($fee * 100);
        $agentp_money = 0;
        if ($mine["agentp"] > 0) {
            $agentp_rate = intval($_W["module_setting"]["agent_rate"]) / 100;
            if ($agentp_rate > 0) {
                $agentp_money = intval($money * $agentp_rate);
            }
        }
        $agentp1_money = 0;
        if ($mine["agentp1"] > 0) {
            $agentp1_rate = intval($_W["module_setting"]["agent_rate1"]) / 100;
            if ($agentp1_rate > 0) {
                $agentp1_money = intval($money * $agentp1_rate);
            }
        }
        $agentp2_money = 0;
        if ($mine["agentp1"] > 0) {
            $agentp2_rate = intval($_W["module_setting"]["agent_rate2"]) / 100;
            if ($agentp2_rate > 0) {
                $agentp2_money = intval($money * $agentp2_rate);
            }
        }
        $profit = $money - $agentp_money - $agentp1_money - $agentp2_money;
        $biz = "agent";
        $biz_name = "购买" . $_W["module_setting"]["agent_name"];
        $title = $money / 100 . "元" . "购买" . $_W["module_setting"]["agent_name"];
        pdo_insert("vp_ph_order", array("uniacid" => $_W["uniacid"], "biz" => $biz, "biz_id" => $mine["id"], "biz_name" => $biz_name, "title" => $title, "uid" => $mine["id"], "openid" => $mine["openid"], "num" => '', "price" => $money / 100, "cnt" => 1, "amount" => $money / 100, "to_pay" => $money / 100, "agentp" => $mine["agentp"], "agentp_money" => $agentp_money / 100, "agentp1" => $mine["agentp1"], "agentp1_money" => $agentp1_money / 100, "agentp2" => $mine["agentp2"], "agentp2_money" => $agentp2_money / 100, "profit" => $profit / 100, "create_time" => $_W["timestamp"], "remark" => '', "status" => 10, "status_time" => $_W["timestamp"], "pay_way" => 0, "pay_callback" => $_SERVER["HTTP_REFERER"]));
        $order_id = pdo_insertid();
        if (empty($order_id)) {
            $this->returnError("系统繁忙，请重试");
        }
        $params = array("tid" => "AGENT_" . $order_id, "ordersn" => "AGENT_" . $order_id, "title" => $title, "fee" => $money / 100, "user" => $mine["uid"]);
        $params = $this->payReady($params);
        $this->returnSuccess('', array("params" => base64_encode(json_encode($params))));
    } else {
        include $this->template("agent_door");
        exit;
    }
}
if ($cmd == "outcash") {
    $money = floatval($_GPC["money"]);
    $channel = $_W["module_setting"]["outcash_way"];
    if ($money < intval($_W["module_setting"]["outcash_min"])) {
        return $this->returnError("单笔提现至少满" . $_W["module_setting"]["outcash_min"] . "元");
    }
    if ($money > intval($_W["module_setting"]["outcash_max"])) {
        return $this->returnError("单笔提现最多" . $_W["module_setting"]["outcash_max"] . "元");
    }
    if ($money > $mine["agent_money"]) {
        return $this->returnError("提现金额不能超过账户余额");
    }
    $last_outcash = pdo_fetch("select * from " . tablename("vp_ph_outcash") . " where uniacid=:uniacid and biz='agent' and uid=:uid ORDER BY id DESC  limit 0,1 ", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"]));
    if ($last_outcash && $_W["timestamp"] - $last_outcash["create_time"] < $_W["module_setting"]["outcash_sp"] * 86400) {
        return $this->returnError("提现间隔不能小于" . $_W["module_setting"]["outcash_sp"] . "天");
    }
    $weixin = $_GPC["weixin"];
    if (empty($weixin)) {
        return $this->returnError("请填写您的微信账号");
    }
    $account = '';
    $realname = '';
    if ($channel == 1) {
        $account_imgid = $_GPC["account"];
        $realname = $_GPC["realname"];
        if (empty($account_imgid)) {
            return $this->returnError("请上传微信收款码用于接收提现");
        }
        load()->func("file");
        $WeiXinAccountService = WeiXinAccount::create($_W["uniacid"]);
        $ret = @$WeiXinAccountService->downloadMedia($account_imgid);
        if (is_error($ret)) {
            return $this->returnError("收款码上传失败:" . $ret["message"]);
        }
        $account = $ret;
        if (empty($realname)) {
            return $this->returnError("请填写收款账户姓名");
        }
    } else {
        if (!($channel == 2)) {
            return $this->returnError("抱歉，暂不支持该提现方式");
        }
    }
    pdo_insert("vp_ph_outcash", array("uniacid" => $_W["uniacid"], "biz" => "agent", "biz_id" => $mine["id"], "uid" => $mine["id"], "nickname" => $mine["nickname"], "weixin" => $weixin, "openid" => $_W["openid"], "money" => $money, "money_before" => $mine["agent_money"], "money_after" => $mine["agent_money"] - $money, "cash" => $money, "status" => 0, "channel" => $channel, "channel_account" => $account, "channel_realname" => $realname, "create_time" => time(), "update_time" => time()));
    $outcash_id = pdo_insertid();
    if (empty($outcash_id)) {
        return $this->returnError("提现发起失败，请联系客服处理");
    }
    $outcash_message = '';
    if ($channel == 1) {
        $outcash_message = "已收到您的提现申请，48小时内将对您填写的账户打款，请留意账户通知";
    } else {
        if ($channel == 2) {
            try {
                $ret = $this->transferByPay(array("id" => $outcash_id, "openid" => $mine["openid"], "amount" => $money * 100, "desc" => $_W["module_setting"]["agent_name"] . "账户提现"));
            } catch (Exception $e) {
                load()->func("logging");
                logging_run("提现异常：" . $e->getMessage());
            }
            if ($ret == false || is_error($ret)) {
                return $this->returnError("提现到账失败，请联系客服处理" . $ret["message"]);
            } else {
                pdo_query("UPDATE " . tablename("vp_ph_outcash") . " SET tag=:tag where uniacid = :uniacid AND id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $outcash_id, ":tag" => iserializer($ret)));
                $outcash_message = "提现成功，即将到账，请关注微信收款通知";
            }
        }
    }
    $ret1 = pdo_query("UPDATE " . tablename("vp_ph_user") . " SET agent_money=agent_money-:agent_money,agent_money_outcash=agent_money_outcash+:agent_money_outcash where uniacid=:uniacid and id=:id AND agent_money=:agent_money_old", array(":uniacid" => $_W["uniacid"], ":id" => $mine["id"], ":agent_money" => $money, ":agent_money_outcash" => $money, ":agent_money_old" => $mine["agent_money"]));
    if (!($ret1 > 0)) {
        return $this->returnError("操作失败，请重试");
    }
    pdo_insert("vp_ph_money", array("uniacid" => $_W["uniacid"], "who" => "agent", "who_id" => $mine["id"], "uid" => $mine["id"], "money" => 0 - $money, "biz" => "outcash", "biz_id" => $outcash_id, "biz_name" => "提现" . $money . "元", "create_time" => $_W["timestamp"]));
    $this->returnSuccess($outcash_message);
    exit;
}
if ($cmd == "moneys") {
    $start = $_GPC["start"];
    if (!isset($start) || empty($start) || intval($start <= 0)) {
        $start = 0;
    } else {
        $start = intval($start);
    }
    $limit = 50;
    $list = pdo_fetchall("select * from " . tablename("vp_ph_money") . " where  uniacid=:uniacid AND uid=:uid order by id desc limit " . $start . "," . $limit . " ", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"]));
    $more = 1;
    if (empty($list) || count($list) < $limit) {
        $more = 0;
    }
    $start += count($list);
    return $this->returnSuccess('', array("list" => $list, "start" => $start, "more" => $more));
}
if ($cmd == "users") {
    $start = $_GPC["start"];
    if (!isset($start) || empty($start) || intval($start <= 0)) {
        $start = 0;
    } else {
        $start = intval($start);
    }
    $filt = intval($_GPC["filt"]);
    $limit = 50;
    $list = pdo_fetchall("select id,uid,pname,cover,sex,money_in,play_time from " . tablename("vp_ph_user") . " where  uniacid=:uniacid AND agentp=:agentp order by id desc limit " . $start . "," . $limit . " ", array(":uniacid" => $_W["uniacid"], ":agentp" => $mine["id"]));
    $i = 0;
    while ($i < count($list)) {
        $list[$i]["_cover"] = VP_IMAGE_URL($list[$i]["cover"]);
        $i++;
    }
    $more = 1;
    if (empty($list) || count($list) < $limit) {
        $more = 0;
    }
    $start += count($list);
    return $this->returnSuccess('', array("list" => $list, "start" => $start, "more" => $more));
}
if ($cmd == "agents") {
    $start = $_GPC["start"];
    if (!isset($start) || empty($start) || intval($start <= 0)) {
        $start = 0;
    } else {
        $start = intval($start);
    }
    $filt = $_GPC["filt"];
    $limit = 50;
    $where = " where  uniacid=:uniacid AND agent>0 ";
    $params = array(":uniacid" => $_W["uniacid"]);
    if ($filt == "b1") {
        $where .= " AND agentp=:agentp ";
        $params[":agentp"] = $mine["id"];
    } else {
        if ($filt == "b2") {
            $where .= " AND (agentp1=:agentp1) ";
            $params[":agentp1"] = $mine["id"];
        }
    }
    $list = pdo_fetchall("select id,uid,nickname,avatar,agent,agent_time,users from " . tablename("vp_ph_user") . $where . " order by id desc limit " . $start . "," . $limit . " ", $params);
    $i = 0;
    while ($i < count($list)) {
        $list[$i]["_avatar"] = VP_IMAGE_URL($list[$i]["avatar"]);
        $i++;
    }
    $more = 1;
    if (empty($list) || count($list) < $limit) {
        $more = 0;
    }
    $start += count($list);
    return $this->returnSuccess('', array("list" => $list, "start" => $start, "more" => $more));
}
/*疑似授权开始
$auth_pass = 0;
$auth_key = $_W["module_setting"]["auth_key"];
if (!empty($auth_key)) {
    $auth_key = pdecode($auth_key);
    if (!empty($auth_key)) {
        $domains = explode(",", $auth_key);
        if (in_array($_SERVER["SERVER_NAME"], $domains)) {
            $auth_pass = 1;
        }
    }
}
if ($auth_pass == 0) {
    $_users = pdo_fetchcolumn("select COUNT(id) from " . tablename("vp_ph_user") . " where uniacid=:uniacid ", array(":uniacid" => $_W["uniacid"]));
    if ($_users > 1) {
        return $this->returnError(pdecode("e735oqx5q2J77yM5oKo55qE5bqU55So5pyq5o6I5p2D5peg5rOV57un57ut5L2*55So77yM6K!36IGU57O7UVE6Mjg5NTM2NDI4NA"));
    }
}
疑似授权结束*/
$publish_url = $_W["siteroot"] . "app/" . substr($this->createMobileUrl("index", array("fuid" => pencode($mine["id"]), "auid" => pencode($mine["id"]))), 2);
$publish_qrcode = $this->createMobileUrl("qr", array("raw" => base64_encode($publish_url)));
$public_sphurl = $_W["module_setting"]["agent_sphurl"] . "&uin=" . pencode($_W["uniacid"] . "_" . $mine["id"]);
$invite_url = $_W["siteroot"] . "app/" . substr($this->createMobileUrl("agent", array("auid" => pencode($mine["id"]))), 2);
$invite_qrcode = $this->createMobileUrl("qr", array("raw" => base64_encode($invite_url)));
include $this->template("agent_index");