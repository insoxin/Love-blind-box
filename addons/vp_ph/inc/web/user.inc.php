<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$cmd = $_GPC["cmd"];
if ($cmd == "agent") {
    $uid = intval($_GPC["uid"]);
    $agent = intval($_GPC["agent"]);
    $uid = $_GPC["uid"];
    if (empty($uid)) {
        returnError("请选择要操作的用户");
    }
    pdo_query("UPDATE " . tablename("vp_ph_user") . " SET agent=:agent where uniacid=:uniacid and uid=:uid", array(":uniacid" => $_W["uniacid"], ":uid" => $uid, ":agent" => $agent));
    returnSuccess("操作成功");
}
if ($cmd == "admin") {
    $uid = intval($_GPC["uid"]);
    $admin = intval($_GPC["admin"]);
    $uid = $_GPC["uid"];
    if (empty($uid)) {
        returnError("请选择要操作的用户");
    }
    pdo_query("UPDATE " . tablename("vp_ph_user") . " SET admin=:admin where uniacid=:uniacid and uid=:uid", array(":uniacid" => $_W["uniacid"], ":uid" => $uid, ":admin" => $admin));
    returnSuccess("操作成功");
    exit;
}
$where = " where uniacid=:uniacid  ";
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
    $where .= " and id=" . $uid;
}
$total = pdo_fetchcolumn("select COUNT(uid) from " . tablename("vp_ph_user") . "  " . $where . '', $params);
$pindex = max(1, intval($_GPC["page"]));
$psize = 12;
$pager = pagination($total, $pindex, $psize);
$start = ($pindex - 1) * $psize;
$limit = " LIMIT {$start},{$psize} ";
$list = pdo_fetchall("select * from " . tablename("vp_ph_user") . "  " . $where . $order . $limit, $params);
include $this->template("web/user_list");