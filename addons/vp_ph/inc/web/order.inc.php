<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$cmd = $_GPC["cmd"];
if ($cmd == "test") {
    exit;
}
$where = " where A.uniacid=:uniacid AND A.status=20  ";
$params = array(":uniacid" => $_W["uniacid"]);
$sort = " order by A.create_time DESC ";
$s_user = $_GPC["s_user"];
if ($s_user) {
    $uid = 0;
    if (is_numeric($s_user)) {
        $uid = intval($s_user);
    } else {
        $s_user = pdecode($s_user);
        $uid = intval($s_user);
    }
    $where .= " and A.uid=" . $uid;
}
$total = pdo_fetchcolumn("select count(id) from " . tablename("vp_ph_order") . " A   " . $where . '', $params);
$pindex = max(1, intval($_GPC["page"]));
$psize = 12;
$pager = pagination($total, $pindex, $psize);
$start = ($pindex - 1) * $psize;
$limit .= " LIMIT {$start},{$psize}";
$list = pdo_fetchall("select A.* from " . tablename("vp_ph_order") . " A " . $where . " " . $sort . " " . $limit, $params);
$i = 0;
while ($i < count($list)) {
    $list[$i]["_user"] = pdo_fetch("select id,uid,openid,id,nickname,avatar,money_in from " . tablename("vp_ph_user") . " where uniacid=:uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $list[$i]["uid"]));
    $i++;
}
include $this->template("web/order_list");