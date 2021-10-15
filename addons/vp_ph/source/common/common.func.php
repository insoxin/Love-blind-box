<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
defined("IN_IA") or exit("Access Denied");
function vp_getcookie($key)
{
    global $_W, $_COOKIE;
    return $_COOKIE[$_W["config"]["cookie"]["pre"] . $key];
}
function returnError($message, $data = '', $status = 0, $type = '')
{
    global $_W;
    if ($_W["isajax"] || $type == "ajax") {
        header("Content-Type:application/json; charset=utf-8");
        $ret = array("status" => $status, "info" => $message, "data" => $data);
        exit(json_encode($ret));
    } else {
        exit($message);
    }
}
function returnSuccess($message, $data = '', $status = 1, $type = '')
{
    global $_W;
    if ($_W["isajax"] || $type == "ajax") {
        header("Content-Type:application/json; charset=utf-8");
        $ret = array("status" => $status, "info" => $message, "data" => $data);
        exit(json_encode($ret));
    } else {
        exit($message);
    }
}
function time_to_text($s, $m = '')
{
    $t = '';
    if ($s > 86400) {
        $t .= intval($s / 86400) . "天";
        $s = $s % 86400;
        if ($m == "d") {
            return $t;
        }
    }
    if ($s > 3600) {
        $t .= intval($s / 3600) . "小时";
        $s = $s % 3600;
        if ($m == "h") {
            return $t;
        }
    }
    if ($s > 60) {
        $t .= intval($s / 60) . "分钟";
        $s = $s % 60;
        if ($m == "m") {
            return $t;
        }
    }
    if ($s > 0) {
        $t .= intval($s) . "秒";
    }
    return $t;
}
function array_get_by_range($as, $v, $k)
{
    $o = null;
    foreach ($as as $a) {
        if (!($v >= $a[$k])) {
            break;
        }
        $o = $a;
    }
    return $o;
}
function time_to_time($t1, $t2 = "now", $f = "m-d H:i", $w = "前")
{
    $t = '';
    $t2 = $t2 == "now" ? time() : $t2;
    $s = abs($t2 - $t1);
    if ($s > 86400) {
        $t = date($f, $t1);
    } else {
        if ($s > 3600) {
            $t .= intval($s / 3600) . "小时";
            $s = $s % 3600;
        }
        if ($s > 60) {
            $t .= intval($s / 60) . "分钟";
            $s = $s % 60;
        }
        if ($s >= 0) {
            $t .= intval($s) . "秒" . $w;
        }
    }
    return $t;
}
function rand_words($src, $len)
{
    $randStr = str_shuffle($src);
    return substr($randStr, 0, $len);
}
function url_base64_encode($str)
{
    $str = base64_encode($str);
    $code = url_encode($str);
    return $code;
}
function url_encode($code)
{
    $code = str_replace("+", "!", $code);
    $code = str_replace("/", "*", $code);
    $code = str_replace("=", '', $code);
    return $code;
}
function url_base64_decode($code)
{
    $code = url_decode($code);
    $str = base64_decode($code);
    return $str;
}
function url_decode($code)
{
    $code = str_replace("!", "+", $code);
    $code = str_replace("*", "/", $code);
    return $code;
}
function pencode($code, $seed = "gengli9876543210")
{
    $c = url_base64_encode($code);
    $pre = substr(md5($seed . $code), 0, 3);
    return $pre . $c;
}
function pdecode($code, $seed = "gengli9876543210")
{
    if (empty($code) || strlen($code) <= 3) {
        return '';
    }
    $pre = substr($code, 0, 3);
    $c = substr($code, 3);
    $str = url_base64_decode($c);
    $spre = substr(md5($seed . $str), 0, 3);
    if ($spre == $pre) {
        return $str;
    } else {
        return '';
    }
}
function text_len($text)
{
    preg_match_all("/./us", $text, $match);
    return count($match[0]);
}
function VP_IMAGE_SAVE($path, $dir = '')
{
    global $_W;
    $filePath = ATTACHMENT_ROOT . "/" . $path;
    $key = $path;
    $accessKey = $_W["module_setting"]["qn_ak"];
    $secretKey = $_W["module_setting"]["qn_sk"];
    $auth = new Qiniu\Auth($accessKey, $secretKey);
    $bucket = empty($dir) ? $_W["module_setting"]["qn_bucket"] : $dir;
    $token = $auth->uploadToken($bucket);
    $uploadMgr = new Qiniu\Storage\UploadManager();
    list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
    return array("error" => $err, "image" => empty($ret) ? '' : $ret["key"]);
}
function VP_HTTP($url, $curlPost)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function VP_IMAGE_URL($path, $style = '', $dir = '', $driver = '')
{
    global $_W;
    if (empty($_W["setting"]["remote"]["type"])) {
        return tomedia($path, true);
    } else {
        if ($_W["setting"]["remote"]["type"] == 3) {
            return tomedia($path) . ($style ? "-" . $style : '');
        } else {
            return tomedia($path);
        }
    }
}
function VP_VIDEO_URL($path, $style = "m", $dir = '', $driver = '')
{
    global $_W;
    if ("local" == $driver) {
        return $_W["attachurl_local"] . $path;
    } else {
        return "http://" . $_W["module_setting"]["qn_api"] . "/" . $path . "?" . $style;
    }
}
function VP_AVATAR($src, $size = "b")
{
    if (empty($src)) {
        return '';
    }
    $parts = parse_url($src);
    if ($parts["host"] == "thirdwx.qlogo.cn" || $parts["host"] == "wx.qlogo.cn") {
        if ($size == "s") {
            $size = "64";
        } else {
            if ($size == "m") {
                $size = "132";
            } else {
                $size = "0";
            }
        }
        $src = substr($src, 0, strrpos($src, "/")) . "/" . $size;
    } else {
        $src = VP_IMAGE_URL($src, $size);
    }
    return $src;
}
function VP_THUMB($src, $size = 120)
{
    $ppos = strrpos($src, ".");
    return substr($src, 0, $ppos) . "_" . $size . substr($src, $ppos);
}
function VP_STATIC_URL($path, $default)
{
    return empty($path) ? MODULE_URL . $default : tomedia($path);
}
function VP_IMAGE_CREATE_FROM($src, $type)
{
    if ($type == 1) {
        if (function_exists("imagecreatefromgif")) {
            return imagecreatefromgif($src);
        }
    } else {
        if ($type == 2) {
            if (function_exists("imagecreatefromjpeg")) {
                return imagecreatefromjpeg($src);
            }
        } else {
            if ($type == 3) {
                if (function_exists("imagecreatefrompng")) {
                    return imagecreatefrompng($src);
                }
            }
        }
    }
}
function VP_IMAGE_RESIZE($path, $s = 640)
{
    $src = imagecreatefromjpeg($path);
    $size_src = getimagesize($path);
    $w = $size_src["0"];
    $h = $size_src["1"];
    if ($w < 640) {
        return "图片宽度不能低于640像素";
    }
    if ($h < 640) {
        return "图片高度不能低于640像素";
    }
    $w = 640;
    $h = $h * (640 / $size_src["0"]);
    $image = imagecreatetruecolor($w, $h);
    imagecopyresampled($image, $src, 0, 0, 0, 0, $w, $h, $size_src["0"], $size_src["1"]);
    imagejpeg($image, $path);
    return true;
}
function WX_CARD_TYPE($type = null)
{
    $map = array("GROUPON" => "团购券", "DISCOUNT" => "折扣券", "GIFT" => "礼品券", "CASH" => "代金券", "GENERAL_COUPON" => "通用券", "MEMBER_CARD" => "会员卡", "SCENIC_TICKET" => "景点门票", "MOVIE_TICKET" => "电影票", "BOARDING_PASS" => "飞机票", "MEETING_TICKET" => "会议门票", "BUS_TICKET" => "汽车票");
    if ($type == null) {
        return $map;
    } else {
        return $map[$type];
    }
}
function WX_CARD_STATUS($status = null)
{
    $map = array("CARD_STATUS_NOT_VERIFY" => "待审核", "CARD_STATUS_VERIFY_FAIL" => "审核失败", "CARD_STATUS_VERIFY_OK" => "通过审核", "CARD_STATUS_USER_DELETE" => "卡券被商户删除", "CARD_STATUS_DISPATCH" => "在公众平台投放过的卡券");
    if ($status == null) {
        return $map;
    } else {
        return $map[$status];
    }
}
function roll_weight($datas = array())
{
    $roll = rand(1, array_sum($datas));
    $_tmpW = 0;
    $rollnum = 0;
    foreach ($datas as $k => $v) {
        $min = $_tmpW;
        $_tmpW += $v;
        $max = $_tmpW;
        if ($roll > $min && $roll <= $max) {
            $rollnum = $k;
        }
    }
    return $rollnum;
}
function vp_sqr($n)
{
    return $n * $n;
}
function vp_random($bonus_min, $bonus_max)
{
    $sqr = intval(vp_sqr($bonus_max - $bonus_min));
    $rand_num = rand(0, $sqr - 1);
    return intval(sqrt($rand_num));
}
function redpack_plan($bonus_total, $bonus_count, $bonus_max, $bonus_min)
{
    $result = array();
    $average = $bonus_total / $bonus_count;
    $a = $average - $bonus_min;
    $b = $bonus_max - $bonus_min;
    $range1 = vp_sqr($average - $bonus_min);
    $range2 = vp_sqr($bonus_max - $average);
    $i = 0;
    while ($i < $bonus_count) {
        if (!(rand($bonus_min, $bonus_max) > $average)) {
            $temp = $bonus_max - vp_random($average, $bonus_max);
            $result[$i] = $temp;
            $bonus_total -= $temp;
        } else {
            $temp = $bonus_min + vp_random($bonus_min, $average);
            $result[$i] = $temp;
            $bonus_total -= $temp;
        }
        $i++;
    }
    while ($bonus_total > 0) {
        $i = 0;
        while ($i < $bonus_count) {
            if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                $result[$i]++;
                $bonus_total--;
            }
            $i++;
        }
    }
    while ($bonus_total < 0) {
        $i = 0;
        while ($i < $bonus_count) {
            if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                $result[$i]--;
                $bonus_total++;
            }
            $i++;
        }
    }
    return $result;
}
function explode_array($txt)
{
    $result = array();
    $arr = array();
    $txt = str_replace("\r\n", "%e2%80%a1", $txt);
    $txt = str_replace("\n", "%e2%80%a1", $txt);
    $arr = explode("%e2%80%a1", $txt);
    return $arr;
}
function explode_map($txt)
{
    $result = array();
    $arr = array();
    $txt = str_replace("\r\n", "%e2%80%a1", $txt);
    $txt = str_replace("\n", "%e2%80%a1", $txt);
    $arr = explode("%e2%80%a1", $txt);
    foreach ($arr as $kv) {
        if (!empty($kv)) {
            $kv = explode(":", $kv);
            if (!(count($kv) != 2)) {
                $result[$kv[0]] = $kv[1];
            } else {
            }
        } else {
        }
    }
    return $result;
}
function build_order_no($sell_id)
{
    return str_pad($sell_id, 10, "0", STR_PAD_LEFT) . date("Ymd") . substr(implode(NULL, array_map("ord", str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}
function format_order_no($num)
{
    if (strlen($num) > 10) {
        return substr($num, 10);
    } else {
        return $num;
    }
}
function en_sell_code($sell_id, $src_uid, $mine_uid)
{
    return pencode(implode(",", array($sell_id, $src_uid, $mine_uid)));
}
function de_sell_code($code)
{
    if (empty($code)) {
        return null;
    }
    $code = pdecode($code);
    if (empty($code)) {
        return null;
    }
    $code = explode(",", $code);
    if (count($code) != 3) {
        return null;
    }
    return $code;
}
function format_money($m)
{
    return round(floatval($m), 2);
}
function VP_SMS_ALI($mobile, $tpl, $param)
{
    global $_W;
    if (empty($mobile) || empty($tpl)) {
        return;
    }
    if (empty($_W["module_setting"]["sms_ali_appkey"]) || empty($_W["module_setting"]["sms_ali_secretkey"]) || empty($_W["module_setting"]["sms_ali_extend"]) || empty($_W["module_setting"]["sms_ali_sign"])) {
        return;
    }
    require_once MD_ROOT . "/libs/Top/TopSdk.php";
    $c = new TopClient();
    $c->appkey = $_W["module_setting"]["sms_ali_appkey"];
    $c->secretKey = $_W["module_setting"]["sms_ali_secretkey"];
    $req = new AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend($_W["module_setting"]["sms_ali_extend"]);
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($_W["module_setting"]["sms_ali_sign"]);
    $req->setSmsParam(json_encode($param));
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode($tpl);
    $resp = $c->execute($req);
}
function k2v_agent_setting($k)
{
    $vals = array(0 => "不需要帮卖", 1 => "允许朋友帮我卖", 2 => "邀请朋友帮我卖");
    return $vals[$k];
}
function VP_QN_TOKEN($filename)
{
    global $_W;
    require_once IA_ROOT . "/framework/library/qiniu/autoload.php";
    $auth = new Qiniu\Auth($_W["setting"]["remote"]["qiniu"]["accesskey"], $_W["setting"]["remote"]["qiniu"]["secretkey"]);
    $mp4 = Qiniu\base64_urlSafeEncode($_W["setting"]["remote"]["qiniu"]["bucket"] . ":" . $filename);
    $pfopOps = "avthumb/mp4/vcodec/libx264/" . $mp4;
    $fsize = 5 * 1024 * 1024;
    $putpolicy = array("persistentOps" => $pfopOps, "persistentPipeline" => $_W["setting"]["remote"]["qiniu"]["queue"], "mimeLimit" => "video/*", "fsizeLimit" => $fsize);
    $uploadtoken = $auth->uploadToken($_W["setting"]["remote"]["qiniu"]["bucket"], null, 3600, $putpolicy);
    return $uploadtoken;
}
function explode_rules($txt)
{
    $result = array();
    $arr = array();
    $txt = str_replace("\r\n", "%e2%80%a1", $txt);
    $txt = str_replace("\n", "%e2%80%a1", $txt);
    $arr = explode("%e2%80%a1", $txt);
    foreach ($arr as $x => $y) {
        $arr[$x] = explode(":", $y);
    }
    return $arr;
}
function get_value($arr, $score)
{
    $res = '';
    $i = 0;
    while ($i < count($arr)) {
        if (!($i == 0)) {
            if (!($i < count($arr) - 1 && $i != 0)) {
                if ($arr[$i - 1][0] > $score && $score >= $arr[$i][0]) {
                    $res = $arr[$i][1];
                }
            } else {
                if ($arr[$i - 1][0] > $score && $score >= $arr[$i][0]) {
                    $res = $arr[$i][1];
                }
            }
        } else {
            if ($score >= $arr[$i][0]) {
                $res = $arr[$i][1];
            }
        }
        $i++;
    }
    return $res;
}
function VP_IS_URL($url)
{
    $r = "/http[s]?:\\/\\/[\\w.]+[\\w\\/]*[\\w.]*\\??[\\w=&\\+\\%]*/is";
    if (preg_match($r, $url)) {
        return true;
    } else {
        return false;
    }
}
function VP_LINKS_EXPLODE($txt)
{
    $result = array();
    $arr = array();
    $txt = str_replace("\r\n", "%e2%80%a1", $txt);
    $txt = str_replace("\n", "%e2%80%a1", $txt);
    $arr = explode("%e2%80%a1", $txt);
    $links = array();
    if (count($arr) >= 2) {
        $i = 0;
        while ($i < count($arr)) {
            $links[] = array("label" => $arr[$i], "value" => $arr[$i + 1]);
            $i += 2;
        }
    }
    return $links;
}
function VP_SHOP_CATS($kv = false)
{
    global $_W;
    $cats = explode(",", $_W["module_setting"]["shop_cats"]);
    if ($kv) {
        $catkvs = array();
        $i = 0;
        while ($i < count($cats)) {
            $catkvs[] = array("label" => $cats[$i], "value" => $cats[$i]);
            $i++;
        }
        return $catkvs;
    } else {
        return $cats;
    }
}