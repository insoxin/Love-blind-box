<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
defined("IN_IA") or exit("Access Denied");
function vp_register_jssdk($debug = false)
{
    global $_W;
    if (defined("HEADER")) {
        echo '';
        return;
    }
    $sysinfo = array("uniacid" => $_W["uniacid"], "acid" => $_W["acid"], "siteroot" => $_W["siteroot"], "siteurl" => $_W["siteurl"], "attachurl" => $_W["attachurl"], "cookie" => array("pre" => $_W["config"]["cookie"]["pre"]));
    if (!empty($_W["acid"])) {
        $sysinfo["acid"] = $_W["acid"];
    }
    if (!empty($_W["openid"])) {
        $sysinfo["openid"] = $_W["openid"];
    }
    if (defined("MODULE_URL")) {
        $sysinfo["MODULE_URL"] = MODULE_URL;
    }
    $sysinfo = json_encode($sysinfo);
    $jssdkconfig = json_encode($_W["account"]["jssdkconfig"]);
    $debug = $debug ? "true" : "false";
    $script = <<<EOF

<script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script type="text/javascript">
\twindow.sysinfo = window.sysinfo || {$sysinfo} || {};
\t
\t// jssdk config 对象
\tjssdkconfig = {$jssdkconfig} || {};
\t
\t// 是否启用调试
\tjssdkconfig.debug = {$debug};
\t
\tjssdkconfig.jsApiList = [
\t\t'checkJsApi',
\t\t'onMenuShareTimeline',
\t\t'onMenuShareAppMessage',
\t\t'onMenuShareQQ',
\t\t'onMenuShareWeibo',
\t\t'hideMenuItems',
\t\t'showMenuItems',
\t\t'hideAllNonBaseMenuItem',
\t\t'showAllNonBaseMenuItem',
\t\t'translateVoice',
\t\t'startRecord',
\t\t'stopRecord',
\t\t'onRecordEnd',
\t\t'playVoice',
\t\t'pauseVoice',
\t\t'stopVoice',
\t\t'uploadVoice',
\t\t'downloadVoice',
\t\t'chooseImage',
\t\t'previewImage',
\t\t'uploadImage',
\t\t'downloadImage',
\t\t'getNetworkType',
\t\t'openLocation',
\t\t'getLocation',
\t\t'hideOptionMenu',
\t\t'showOptionMenu',
\t\t'closeWindow',
\t\t'scanQRCode',
\t\t'chooseWXPay',
\t\t'openProductSpecificView',
\t\t'addCard',
\t\t'chooseCard',
\t\t'openCard',
\t\t'openAddress',
\t\t'editAddress'
\t];

\tjssdkconfig.openTagList = [
\t\t'wx-open-launch-weapp',
\t\t'wx-open-launch-app'
\t];

\twx.config(jssdkconfig);
\t
</script>
EOF;
    echo $script;
}
function vp_message_delete($acc, $msg_id)
{
    $token = $acc->getAccessToken();
    if (is_error($token)) {
        return $token;
    }
    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token={$token}";
    $data = array("msg_id" => $msg_id);
    $data = urldecode(json_encode($data, JSON_UNESCAPED_UNICODE));
    $response = ihttp_request($url, $data);
    if (is_error($response)) {
        return error(-1, "访问公众平台接口失败, 错误: {$response["message"]}");
    }
    $result = @json_decode($response["content"], true);
    if (empty($result)) {
        return error(-1, "接口调用失败, 元数据: {$response["meta"]}");
    } else {
        if (!empty($result["errcode"])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result["errcode"]}, 错误信息: {$result["errmsg"]},错误详情：{$this->errorCode($result["errcode"])}");
        } else {
        }
    }
    return $result;
}
function vp_ocr($acc, $img_url)
{
    $token = $acc->getAccessToken();
    if (is_error($token)) {
        return $token;
    }
    $url = "https://api.weixin.qq.com/cv/ocr/comm?img_url=" . urlencode($img_url) . "&access_token={$token}";
    $data = array();
    $data = urldecode(json_encode($data, JSON_UNESCAPED_UNICODE));
    $response = ihttp_request($url, $data);
    if (is_error($response)) {
        return error(-1, "访问公众平台接口失败, 错误: {$response["message"]}");
    }
    $result = @json_decode($response["content"], true);
    if (empty($result)) {
        return error(-1, "接口调用失败, 元数据: {$response["meta"]}");
    } else {
        if (!empty($result["errcode"])) {
            return error(-1, "访问微信接口错误, 错误代码: {$result["errcode"]}, 错误信息: {$result["errmsg"]},错误详情：{$this->errorCode($result["errcode"])}");
        } else {
        }
    }
    return $result;
}
function vp_security_msg($content)
{
    global $_W;
    if (empty($content)) {
        return true;
    }
    load()->classs("wxapp.account");
    $WxappAccountAPI = WxappAccount::create($_W["uniacid"]);
    $token = $WxappAccountAPI->getAccessToken();
    if (is_error($token)) {
        return true;
    }
    $data = array();
    $data["content"] = $content;
    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    $post_url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token={$token}";
    $response = ihttp_request($post_url, $data);
    if (is_error($response)) {
        return true;
    }
    $result = @json_decode($response["content"], true);
    if (empty($result)) {
        return true;
    } else {
        if (!empty($result["errcode"])) {
            return error(-1, "内容含有违规敏感内容" . $result["errmsg"]);
        } else {
        }
    }
    return true;
}
function vp_security_img($imgData)
{
    global $_W;
    if (empty($imgData)) {
        return true;
    }
    load()->classs("wxapp.account");
    $WxappAccountAPI = WxappAccount::create($_W["uniacid"]);
    $token = $WxappAccountAPI->getAccessToken();
    if (is_error($token)) {
        return true;
    }
    $data = array();
    $data["media"] = $imgData;
    $post_url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token={$token}";
    $response = ihttp_request($post_url, $data);
    if (is_error($response)) {
        return true;
    }
    $result = @json_decode($response["content"], true);
    if (empty($result)) {
        return true;
    } else {
        if (!empty($result["errcode"])) {
            return error(-1, "图片含有违规敏感内容");
        } else {
        }
    }
    return true;
}