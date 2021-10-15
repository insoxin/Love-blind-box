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
if ($mine["admin"] == 0) {
    return $this->returnError("该功能仅限管理员使用");
}
if ($cmd == "test") {
    exit;
}
if ($cmd == "verify") {
    $submit = $_GPC["submit"];
    if ($submit == "list") {
        $start = $_GPC["start"];
        if (!isset($start) || empty($start) || intval($start <= 0)) {
            $start = 0;
        } else {
            $start = intval($start);
        }
        $filt = intval($_GPC["filt"]);
        $limit = 50;
        $list = pdo_fetchall("select * from " . tablename("vp_ph_feed") . " where  uniacid=:uniacid AND status=1 AND verify_status<10 AND is_del=0 order by update_time ASC limit " . $start . "," . $limit . " ", array(":uniacid" => $_W["uniacid"]));
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
        if (empty($list) || count($list) < $limit) {
            $more = 0;
        }
        $start += count($list);
        return $this->returnSuccess('', array("list" => $list, "start" => $start, "more" => $more));
    } else {
        if ($submit == "verify") {
            $verify = $_GPC["verify"];
            $id = $_GPC["id"];
            if ($verify == "yes") {
                pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET verify_status=20,verify_time=:verify_time where uniacid=:uniacid AND id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":verify_time" => TIMESTAMP));
                return $this->returnSuccess("已通过");
            } else {
                if (!($verify == "no")) {
                } else {
                    $verify_remark = $_GPC["verify_remark"];
                    pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET verify_status=10,verify_remark=:verify_remark,verify_time=:verify_time where uniacid=:uniacid AND id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":verify_time" => TIMESTAMP, ":verify_remark" => $verify_remark));
                    return $this->returnSuccess("已拒绝");
                }
            }
        } else {
            include $this->template("admin_verify");
        }
    }
} else {
}