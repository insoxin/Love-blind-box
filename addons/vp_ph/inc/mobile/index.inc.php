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
$invite_url = $_W["siteroot"] . "app/" . substr($this->createMobileUrl("index", array("fuid" => pencode($mine["id"]))), 2);
if ($cmd == "mine") {
    exit;
}
if ($cmd == "feedin") {
    $sex = intval($_GPC["sex"]);
    $content = trim($_GPC["content"]);
    $images = $_GPC["images"];
    $ctype = $_GPC["ctype"];
    $contact = trim($_GPC["contact"]);
    if (empty($content)) {
        return $this->returnError("请填写您的交友简介");
    }
    if (empty($images) || count($images) == 0) {
        return $this->returnError("请至少上传一张照片");
    }
    if (empty($contact)) {
        return $this->returnError("请填写您的联系方式");
    }
    load()->func("file");
    $down_images = array();
    $WeiXinAccountService = WeiXinAccount::create($_W["uniacid"]);
    foreach ($images as $imgid) {
        if (strpos($imgid, "images/") === 0) {
            $down_images[] = $imgid;
        } else {
            $ret = @$WeiXinAccountService->downloadMedia($imgid);
            if (is_error($ret)) {
                $this->returnError("照片上传失败:" . $ret["message"]);
            }
            $down_images[] = $ret;
        }
    }
    $cover = $down_images[0];
    $images = iserializer($down_images);
    $feed = array("uniacid" => $_W["uniacid"], "uid" => $mine["id"], "openid" => $mine["openid"], "sex" => $sex, "content" => $content, "images" => $images, "ctype" => $ctype, "contact" => $contact, "create_time" => time());
    $fee = 0;
    if ($sex == 1) {
        if ($mine["feedin1"] > 0) {
            $fee = $_W["module_setting"]["fee_in_1"];
        } else {
            $fee = $_W["module_setting"]["fee_in1_1"];
        }
    } else {
        if ($sex == 2) {
            if ($mine["feedin2"] > 0) {
                $fee = $_W["module_setting"]["fee_in_2"];
            } else {
                $fee = $_W["module_setting"]["fee_in1_2"];
            }
        } else {
            $this->returnError("请选择有效的性别");
        }
    }
    if ($fee > 0) {
        $feed["status"] = 0;
    } else {
        $feed["status"] = 1;
    }
    pdo_insert("vp_ph_feed", $feed);
    $feed["id"] = pdo_insertid();
    if (empty($feed["id"])) {
        $this->returnError("系统繁忙，请重试");
    }
    $redirect_url = $_W["siteroot"] . "app/" . substr($this->createMobileUrl("index", array("cmd" => "iins")), 2);
    if ($fee > 0) {
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
        $biz = "feedin" . $sex;
        $biz_name = "放入交友纸条到" . ($sex == 1 ? "男生" : "女生") . "盒子";
        $title = $money / 100 . "元" . $biz_name;
        pdo_insert("vp_ph_order", array("uniacid" => $_W["uniacid"], "biz" => $biz, "biz_id" => $feed["id"], "biz_name" => $biz_name, "title" => $title, "uid" => $mine["id"], "openid" => $mine["openid"], "num" => '', "price" => $money / 100, "cnt" => 1, "amount" => $money / 100, "to_pay" => $money / 100, "agentp" => $mine["agentp"], "agentp_money" => $agentp_money / 100, "agentp1" => $mine["agentp1"], "agentp1_money" => $agentp1_money / 100, "agentp2" => $mine["agentp2"], "agentp2_money" => $agentp2_money / 100, "profit" => $profit / 100, "create_time" => $_W["timestamp"], "remark" => '', "status" => 10, "status_time" => $_W["timestamp"], "pay_way" => 0, "pay_callback" => $redirect_url));
        $order_id = pdo_insertid();
        if (empty($order_id)) {
            $this->returnError("系统繁忙，请重试");
        }
        $params = array("tid" => "FEEDIN_" . $order_id, "ordersn" => "FEEDIN_" . $order_id, "title" => $title, "fee" => $money / 100, "user" => $mine["uid"]);
        $params = $this->payReady($params);
        $this->returnSuccess('', array("params" => base64_encode(json_encode($params))));
    } else {
        pdo_query("UPDATE " . tablename("vp_ph_user") . " SET feedin" . $sex . "=feedin" . $sex . "+1 where uniacid=:uniacid AND id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $mine["id"]));
        return $this->returnSuccess($redirect_url);
    }
} else {
    if ($cmd == "feedout") {
        $sex = intval($_GPC["sex"]);
        $feed = pdo_fetch("SELECT * FROM " . tablename("vp_ph_feed") . " WHERE uniacid = :uniacid AND sex=:sex AND status=1 AND verify_status=20 AND is_del=0 AND id NOT IN( SELECT feed_id FROM " . tablename("vp_ph_user_feed") . " WHERE uniacid = :uniacid1 AND uid=:uid AND status=1 ) order by rand() limit 0,1 ", array(":uniacid" => $_W["uniacid"], ":uniacid1" => $_W["uniacid"], ":uid" => $mine["id"], ":sex" => $sex));
        if (empty($feed)) {
            $this->returnError("抱歉，抽取失败，请稍后再试");
        }
        $ufeed = array("uniacid" => $_W["uniacid"], "uid" => $mine["id"], "feed_id" => $feed["id"], "feed_uid" => $feed["uid"], "sex" => $sex, "create_time" => time());
        $fee = 0;
        if ($sex == 1) {
            $fee = $_W["module_setting"]["fee_out_1"];
        } else {
            if ($sex == 2) {
                $fee = $_W["module_setting"]["fee_out_2"];
            } else {
                $this->returnError("请选择有效的性别");
            }
        }
        if ($fee > 0) {
            $ufeed["status"] = 0;
        } else {
            $ufeed["status"] = 1;
        }
        pdo_insert("vp_ph_user_feed", $ufeed);
        $ufeed["id"] = pdo_insertid();
        if (empty($ufeed["id"])) {
            $this->returnError("系统繁忙，请重试");
        }
        if ($fee > 0) {
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
            $biz = "feedout" . $sex;
            $biz_name = "从" . ($sex == 1 ? "男生" : "女生") . "盒子里抽1张交友纸条";
            $title = $money / 100 . "元" . $biz_name;
            $redirect_url = $_W["siteroot"] . "app/" . substr($this->createMobileUrl("index", array("cmd" => "ufeed", "id" => $ufeed["id"])), 2);
            pdo_insert("vp_ph_order", array("uniacid" => $_W["uniacid"], "biz" => $biz, "biz_id" => $ufeed["id"], "biz_name" => $biz_name, "title" => $title, "uid" => $mine["id"], "openid" => $mine["openid"], "num" => '', "price" => $money / 100, "cnt" => 1, "amount" => $money / 100, "to_pay" => $money / 100, "agentp" => $mine["agentp"], "agentp_money" => $agentp_money / 100, "agentp1" => $mine["agentp1"], "agentp1_money" => $agentp1_money / 100, "agentp2" => $mine["agentp2"], "agentp2_money" => $agentp2_money / 100, "profit" => $profit / 100, "create_time" => $_W["timestamp"], "remark" => '', "status" => 10, "status_time" => $_W["timestamp"], "pay_way" => 0, "pay_callback" => $redirect_url));
            $order_id = pdo_insertid();
            if (empty($order_id)) {
                $this->returnError("系统繁忙，请重试");
            }
            $params = array("tid" => "FEEDOUT_" . $order_id, "ordersn" => "FEEDOUT_" . $order_id, "title" => $title, "fee" => $money / 100, "user" => $mine["uid"]);
            $params = $this->payReady($params);
            $this->returnSuccess('', array("params" => base64_encode(json_encode($params))));
        } else {
            pdo_query("UPDATE " . tablename("vp_ph_user") . " SET feedout" . $sex . "=feedout" . $sex . "+1 where uniacid=:uniacid AND id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $mine["id"]));
            return $this->returnSuccess($redirect_url);
        }
    } else {
        if ($cmd == "ufeed") {
            $id = intval($_GPC["id"]);
            if (!($id > 0)) {
                $this->returnError("缺少参数");
            }
            $ufeed = pdo_fetch("SELECT * FROM " . tablename("vp_ph_user_feed") . " WHERE uniacid = :uniacid AND uid=:uid AND id=:id AND status=1 ", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"], ":id" => $id));
            if (empty($ufeed)) {
                $this->returnError("该内容不存在");
            }
            $feed = pdo_fetch("SELECT * FROM " . tablename("vp_ph_feed") . " WHERE uniacid = :uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $ufeed["feed_id"]));
            if (empty($feed)) {
                $this->returnError("该纸条不存在");
            }
            $feed["images"] = iunserializer($feed["images"]);
            $j = 0;
            while ($j < count($feed["images"])) {
                $feed["images"][$j] = array("path" => $feed["images"][$j], "url" => VP_IMAGE_URL($feed["images"][$j]));
                $j++;
            }
            include $this->template("ufeed");
        } else {
            if ($cmd == "iins") {
                $submit = $_GPC["submit"];
                if ($submit == "list") {
                    $start = $_GPC["start"];
                    if (!isset($start) || empty($start) || intval($start <= 0)) {
                        $start = 0;
                    } else {
                        $start = intval($start);
                    }
                    $limit = 50;
                    $list = pdo_fetchall("select * from " . tablename("vp_ph_feed") . " where uniacid=:uniacid AND uid=:uid AND status=1 AND is_del=0 ORDER BY create_time DESC limit " . $start . "," . $limit . " ", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"]));
                    $i = 0;
                    while ($i < count($list)) {
                        $list[$i]["images"] = iunserializer($list[$i]["images"]);
                        $j = 0;
                        while ($j < count($list[$i]["images"])) {
                            $list[$i]["images"][$j] = array("path" => $list[$i]["images"][$j], "url" => VP_IMAGE_URL($list[$i]["images"][$j]));
                            $j++;
                        }
                        $i++;
                    }
                    $more = 1;
                    if (empty($users) || count($users) < $limit) {
                        $more = 0;
                    }
                    $start += count($users);
                    return $this->returnSuccess('', array("mine" => $mine, "list" => $list, "start" => $start, "more" => $more));
                } else {
                    if ($submit == "del") {
                        $id = $_GPC["id"];
                        pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET is_del=1 where uniacid=:uniacid AND uid=:uid AND id=:id", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"], ":id" => $id));
                        return $this->returnSuccess("已销毁");
                    } else {
                        if ($submit == "edit") {
                            $id = intval($_GPC["id"]);
                            if (!($id > 0)) {
                                return $this->returnError("缺少参数");
                            }
                            $content = trim($_GPC["content"]);
                            $images = $_GPC["images"];
                            $ctype = $_GPC["ctype"];
                            $contact = trim($_GPC["contact"]);
                            if (empty($content)) {
                                return $this->returnError("请填写您的交友简介");
                            }
                            if (empty($images) || count($images) == 0) {
                                return $this->returnError("请至少上传一张照片");
                            }
                            if (empty($contact)) {
                                return $this->returnError("请填写您的联系方式");
                            }
                            load()->func("file");
                            $down_images = array();
                            $WeiXinAccountService = WeiXinAccount::create($_W["uniacid"]);
                            foreach ($images as $imgid) {
                                if (strpos($imgid, "images/") === 0) {
                                    $down_images[] = $imgid;
                                } else {
                                    $ret = @$WeiXinAccountService->downloadMedia($imgid);
                                    if (is_error($ret)) {
                                        $this->returnError("照片上传失败:" . $ret["message"]);
                                    }
                                    $down_images[] = $ret;
                                }
                            }
                            $images = iserializer($down_images);
                            $feedUp = array("content" => $content, "images" => $images, "ctype" => $ctype, "contact" => $contact, "update_time" => time(), "verify_status" => 5);
                            pdo_update("vp_ph_feed", $feedUp, array("uniacid" => $_W["uniacid"], "uid" => $mine["id"], "id" => $id));
                            return $this->returnSuccess("修改成功");
                        } else {
                            include $this->template("iins");
                        }
                    }
                }
            } else {
                if ($cmd == "iouts") {
                    $submit = $_GPC["submit"];
                    if ($submit == "list") {
                        $start = $_GPC["start"];
                        if (!isset($start) || empty($start) || intval($start <= 0)) {
                            $start = 0;
                        } else {
                            $start = intval($start);
                        }
                        $limit = 50;
                        $list = pdo_fetchall("select f.* from " . tablename("vp_ph_user_feed") . " uf JOIN " . tablename("vp_ph_feed") . " f on(uf.feed_id=f.id) where uf.uniacid=:uniacid AND uf.uid=:uid AND uf.status=1 ORDER BY uf.create_time DESC limit " . $start . "," . $limit . " ", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["id"]));
                        $i = 0;
                        while ($i < count($list)) {
                            $list[$i]["images"] = iunserializer($list[$i]["images"]);
                            $j = 0;
                            while ($j < count($list[$i]["images"])) {
                                $list[$i]["images"][$j] = array("path" => $list[$i]["images"][$j], "url" => VP_IMAGE_URL($list[$i]["images"][$j]));
                                $j++;
                            }
                            $i++;
                        }
                        $more = 1;
                        if (empty($users) || count($users) < $limit) {
                            $more = 0;
                        }
                        $start += count($users);
                        return $this->returnSuccess('', array("mine" => $mine, "list" => $list, "start" => $start, "more" => $more));
                    } else {
                        include $this->template("iouts");
                    }
                } else {
                    $slides = $_W["module_setting"]["slides"];
                    include $this->template("index");
                }
            }
        }
    }
}