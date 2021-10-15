<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$cmd = $_GPC["cmd"];
if ($cmd == "outcash") {
    $ids = $_GPC["ids"];
    if (empty($ids)) {
        returnError("请选择要发放的记录");
    }
    $remark = $_GPC["remark"];
    if (empty($remark)) {
        returnError("请填写操作备注");
    }
    $outcashs = pdo_fetchall("select * from " . tablename("vp_ph_outcash") . "  where uniacid=:uniacid and id IN (:ids) ", array(":uniacid" => $_W["uniacid"], ":ids" => $ids));
    if (empty($outcashs) || count($outcashs) == 0) {
        returnError("记录不存在");
    }
    $errs = array();
    $i = 0;
    while ($i < count($outcashs)) {
        $outcash = $outcashs[$i];
        if ($outcash["channel"] == 1) {
            pdo_query("UPDATE " . tablename("vp_ph_outcash") . " SET status=1,update_time=:update_time,remark=:remark where id=:id", array(":update_time" => time(), ":id" => $outcash["id"], ":remark" => $remark));
        }
        $i++;
    }
    returnSuccess("操作成功");
    exit;
}
if ($cmd == "refusecash") {
    $id = $_GPC["id"];
    if (empty($id)) {
        returnError("请选择要拒绝的记录");
    }
    $fedback = $_GPC["fedback"];
    if (empty($fedback)) {
        returnError("请填写拒绝原因");
    }
    $outcash = pdo_fetch("select * from " . tablename("vp_ph_outcash") . "  where uniacid=:uniacid and id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $id));
    if (empty($outcash)) {
        returnError("记录不存在");
    }
    pdo_query("UPDATE " . tablename("vp_ph_outcash") . " SET status=2,update_time=:update_time,fedback=:fedback where id=:id", array(":update_time" => time(), ":id" => $outcash["id"], ":fedback" => $fedback));
    returnSuccess("成功拒绝");
    exit;
}
$where = " where uniacid=:uniacid ";
$params = array(":uniacid" => $_W["uniacid"]);
$sort = " order by create_time DESC ";
$s_user = $_GPC["s_user"];
if ($s_user) {
    $uid = 0;
    if (is_numeric($s_user)) {
        $uid = intval($s_user);
    } else {
        $s_user = pdecode($s_user);
        $uid = intval($s_user);
    }
    $where .= " and id=" . $uid;
}
if (!empty($_GPC["s_state"])) {
    $s_state = intval($_GPC["s_state"]);
    if ("1" == $s_state) {
        $where .= " and status=1 ";
    } else {
        $where .= " and status=0 ";
    }
} else {
    $where .= " and status=0 ";
}
$total = pdo_fetchcolumn("select count(id) from " . tablename("vp_ph_outcash") . $where . '', $params);
$pindex = max(1, intval($_GPC["page"]));
$psize = 12;
$pager = pagination($total, $pindex, $psize);
$start = ($pindex - 1) * $psize;
$limit .= " LIMIT {$start},{$psize} ";
$list = pdo_fetchall("select * from " . tablename("vp_ph_outcash") . $where . $sort . $limit, $params);
if ($list && count($list) > 0) {
    $i = 0;
    while ($i < count($list)) {
        $list[$i]["_user"] = pdo_fetch("select id,nickname,avatar from " . tablename("vp_ph_user") . "  where uniacid=:uniacid and id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $list[$i]["uid"]));
        $i++;
    }
}
include $this->template("web/outcash_list");