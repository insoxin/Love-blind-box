<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$cmd = $_GPC["cmd"];
if ($cmd == "op") {
    if ("stop" == $_GPC["submit"]) {
        $id = $_GPC["id"];
        if (empty($id)) {
            returnError("请选择要操作的链接");
        }
        $op_remark = $_GPC["op_remark"];
        if (empty($op_remark)) {
            returnError("请填写封禁原因");
        }
        $ret = pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET op=1,op_remark=:op_remark,op_time=:op_time where uniacid=:uniacid and id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":op_remark" => $op_remark, ":op_time" => time()));
        if (false === $ret) {
            return $this->returnMessage("失败啦，重新试试呢~");
        }
        returnSuccess("操作成功");
    } else {
        if ("open" == $_GPC["submit"]) {
            $id = $_GPC["id"];
            if (empty($id)) {
                returnError("请选择要操作的链接");
            }
            $ret = pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET op=2,op_remark=:op_remark,op_time=:op_time where uniacid=:uniacid and id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":op_remark" => $op_remark, ":op_time" => time()));
            if (false === $ret) {
                return $this->returnMessage("失败啦，重新试试呢~");
            }
            returnSuccess("操作成功");
        } else {
        }
    }
    exit;
}
if ($cmd == "delete") {
    $id = $_GPC["id"];
    if (empty($id)) {
        returnError("请选择要删除的链接");
    }
    $link = pdo_fetch("select * from " . tablename("vp_ph_feed") . " where uniacid=:uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $id));
    if (empty($link)) {
        returnError("该链接不存在或已删除");
    }
    if ($link["is_direct"] == 1) {
        $account_api = WeAccount::createByUniacid();
        $ret = vp_message_delete($account_api, $link["direct_msgid"]);
        if (is_error($ret)) {
            returnError("图文删除失败" . $ret["message"]);
        }
    }
    pdo_query("UPDATE " . tablename("vp_ph_feed") . " SET is_del=1,del_time=:del_time where uniacid=:uniacid and id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":del_time" => time()));
    returnSuccess("删除成功!", $this->createWebUrl("link"));
} else {
    $where = " where uniacid=:uniacid AND status=1 AND is_del=0 ";
    $params = array(":uniacid" => $_W["uniacid"]);
    $order = " order by create_time DESC ";
    $s_user = $_GPC["s_user"];
    if ($s_user) {
        $uid = 0;
        if (is_numeric($s_user)) {
            $uid = intval($s_user);
        } else {
            $s_user = pdecode($s_user);
            $uid = intval($s_user);
        }
        $where .= " and uid=" . $uid;
    }
    $s_sex = $_GPC["s_sex"];
    if (!empty($s_sex)) {
        $where .= " and sex=:s_sex ";
        $params[":s_sex"] = intval($s_sex);
    }
    $total = pdo_fetchcolumn("select count(id) from " . tablename("vp_ph_feed") . $where . '', $params);
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 12;
    $pager = pagination($total, $pindex, $psize);
    $start = ($pindex - 1) * $psize;
    $limit .= " LIMIT {$start},{$psize} ";
    $list = pdo_fetchall("select * from " . tablename("vp_ph_feed") . $where . $order . $limit, $params);
    $i = 0;
    while ($i < count($list)) {
        $list[$i]["_id"] = pencode($list[$i]["id"]);
        $list[$i]["images"] = iunserializer($list[$i]["images"]);
        $list[$i]["_user"] = pdo_fetch("select id,uid,openid,id,nickname,avatar,money_in from " . tablename("vp_ph_user") . " where uniacid=:uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $list[$i]["uid"]));
        $i++;
    }
    include $this->template("web/feed_list");
}