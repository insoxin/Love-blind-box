<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
define("IN_IA", true);
require "source/common/common.func.php";
if (empty($_SERVER["HTTP_REFERER"])) {
    returnError("请从视频号扩展链接入口访问");
}
$query = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_QUERY);
if (empty($query)) {
    returnError("该地址有误");
}
$queryParts = explode("&", $query);
$params = array();
foreach ($queryParts as $param) {
    $item = explode("=", $param);
    $params[$item[0]] = $item[1];
}
$uin = $params["uin"];
if (empty($uin)) {
    returnError("缺少链接参数");
}
$uin = pdecode($uin);
if (empty($uin)) {
    returnError("链接参数错误");
}
$ps = explode("_", $uin);
if (count($ps) != 2) {
    returnError("链接参数有误");
}
$uniacid = intval($ps[0]);
$fuid = intval($ps[1]);
$auid = intval($ps[1]);
$http_type = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https" ? "https://" : "http://";
$redirect_url = $http_type . $_SERVER["HTTP_HOST"] . "/app/index.php?i=" . $uniacid . "&c=entry&m=vp_pp&do=index&fuid=" . pencode($fuid) . "&auid=" . pencode($auid);
header("Location:" . $redirect_url);
exit;