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
if ($cmd == "html") {
    $type = $_GPC["type"];
    $id = $_GPC["id"];
    $html = '';
    if ($type == "app_qa") {
        $html = $_W["module_setting"]["app_qa"];
    } else {
        if ($type == "slide") {
            $html = pdo_fetchcolumn("select stype_content from " . tablename("vp_wm_slide") . " where uniacid=:uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $id));
        } else {
            if ($type == "nav") {
                $html = pdo_fetchcolumn("select stype_content from " . tablename("vp_wm_nav") . " where uniacid=:uniacid AND id=:id ", array(":uniacid" => $_W["uniacid"], ":id" => $id));
            }
        }
    }
    include $this->template("html");
    exit;
}
if ($cmd == "citys") {
    include $this->template("citys");
    exit;
}
if ($cmd == "door") {
    if (!($_GPC["submit"] == "enter")) {
        exit;
    }
    $code = $_GPC["code"];
    if (empty($code)) {
        return $this->returnError("请输入邀请码");
    }
    $code = pdecode($code);
    if (empty($code)) {
        return $this->returnError("邀请码无效");
    }
    $code = intval($code);
    if (!($code > 0)) {
        return $this->returnError("邀请码无效");
    }
    pdo_query("UPDATE " . tablename("vp_sphz_user") . " SET fuid=:fuid where uniacid=:uniacid and uid=:uid", array(":uniacid" => $_W["uniacid"], ":uid" => $mine["uid"], ":fuid" => $code));
    return $this->returnSuccess("OK");
}
if ($cmd == "auth") {
    if ($_GPC["submit"] == "auth") {
        $codes = $_GPC["codes"];
        if (empty($codes)) {
            return $this->returnError("请输入内容");
        }
        $codes = explode_array($codes);
        $codes = implode(",", $codes);
        $codes = pencode($codes);
        return $this->returnSuccess('', $codes);
    }
    include $this->template("public_auth");
    exit;
}
if ($cmd == "user") {
    include $this->template("public_user");
    exit;
}