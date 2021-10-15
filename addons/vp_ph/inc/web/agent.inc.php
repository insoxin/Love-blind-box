<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
global $_W, $_GPC;
$cmd = $_GPC["cmd"];
if ($cmd == "verify") {
    $id = $_GPC["id"];
    if (empty($id)) {
        returnError("请选择要操作的用户");
    }
    if ("yes" == $_GPC["submit"]) {
        $ret = pdo_query("UPDATE " . tablename("vp_ph_user") . " SET verify_status=20,verify_time=:verify_time where uniacid=:uniacid and id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":verify_time" => $_W["timestamp"]));
        if (false === $ret) {
            return $this->returnMessage("失败啦，重新试试呢~");
        }
        returnSuccess("操作成功");
    } else {
        if ("no" == $_GPC["submit"]) {
            $remark = $_GPC["remark"];
            if (empty($remark)) {
                returnError("请填写原因");
            }
            $ret = pdo_query("UPDATE " . tablename("vp_ph_user") . " SET verify_status=10,verify_remark=:verify_remark,verify_time=:verify_time where uniacid=:uniacid and id=:id", array(":uniacid" => $_W["uniacid"], ":id" => $id, ":verify_remark" => $remark, ":verify_time" => $_W["timestamp"]));
            if (false === $ret) {
                return $this->returnMessage("失败啦，重新试试呢~");
            }
            returnSuccess("操作成功");
        } else {
        }
    }
    exit;
}
$where = " uniacid=:uniacid AND agent>0 ";
$params = array(":uniacid" => $_W["uniacid"]);
$order = " order by id desc ";
$total = pdo_fetchcolumn("select count(id) from " . tablename("vp_ph_user") . "  where  " . $where . '', $params);
$pindex = max(1, intval($_GPC["page"]));
$psize = 12;
$pager = pagination($total, $pindex, $psize);
$start = ($pindex - 1) * $psize;
$limit .= " LIMIT {$start},{$psize}";
$list = pdo_fetchall("select * from " . tablename("vp_ph_user") . "  where   " . $where . $order . $limit, $params);
include $this->template("web/agent_list");