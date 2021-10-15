<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
defined('IN_IA') or exit('Access Denied');
define('MD_NAME', 'vp_ph');
define('MD_ROOT', IA_ROOT . '/addons/' . MD_NAME);
require MD_ROOT . '/source/common/common.func.php';
require MD_ROOT . '/source/common/we7ex.func.php';
class Vp_phModuleSite extends WeModuleSite
{
    public function __construct()
    {
        global $_GPC, $_W;
        load()->model('module');
        $module = module_fetch(MD_NAME);
        if (empty($module['config'])) {
            return returnError('应用尚未配置');
        }
        $module['config']['set_app_logo'] = VP_IMAGE_URL($module['config']['set_app_logo']);
        $module['config']['set_acc_logo'] = VP_IMAGE_URL($module['config']['set_acc_logo']);
        $module['config']['set_acc_qrcode'] = VP_IMAGE_URL($module['config']['set_acc_qrcode']);
        $module['config']['server_qrcode'] = VP_IMAGE_URL($module['config']['server_qrcode']);
        $_W['module_setting'] = $module['config'];
    }
    public $_user;
    public $_is_user_infoed = 0;
    protected function _doMobileAuth()
    {
        global $_GPC, $_W;
        if ($_W['container'] != 'wechat') {
            return $this->returnError('应用目前仅支持在微信中访问', '', 'error');
        }
        if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])) {
            if (intval($_W['account']['level']) != 4) {
                if (empty($_W['oauth_account'])) {
                    return message('该公众号无微信授权能力，请联系公众号管理员', '', 'error');
                }
                if ($_W['oauth_account']['level'] != 4) {
                    return message('微信授权能力获取失败，请联系公众号管理员', '', 'error');
                }
            }
            if (empty($_SESSION['oauth_openid'])) {
                return message('微信授权失败，请重试', '', 'error');
            }
            $getUserInfo = false;
            $accObj = WeiXinAccount::create($_W['uniacid']);
            $userinfo = $accObj->fansQueryInfo($_SESSION['oauth_openid']);
            if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['subscribe'])) {
                if (empty($userinfo['nickname'])) {
                    return message('获取个人信息失败，请重试', '', 'error');
                }
                $getUserInfo = true;
                $userinfo['nickname'] = stripcslashes($userinfo['nickname']);
                $userinfo['avatar'] = $userinfo['headimgurl'];
                unset($userinfo['headimgurl']);
                $_SESSION['userinfo'] = base64_encode(iserializer($userinfo));
            }
            $default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' . tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(":uniacid" => $_W['uniacid']));
            $data = array("uniacid" => $_W['uniacid'], "email" => md5($_SESSION['oauth_openid']) . '@we7.cc', "salt" => random(8), "groupid" => $default_groupid, "createtime" => TIMESTAMP, "password" => md5($message['from'] . $data['salt'] . $_W['config']['setting']['authkey']));
            if (true === $getUserInfo) {
                $data['nickname'] = stripslashes($userinfo['nickname']);
                $data['avatar'] = rtrim($userinfo['avatar'], '0') . 132;
                $data['gender'] = $userinfo['sex'];
                $data['nationality'] = $userinfo['country'];
                $data['resideprovince'] = $userinfo['province'] . '省';
                $data['residecity'] = $userinfo['city'] . '市';
            }
            $uid = pdo_fetchcolumn('SELECT uid FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND email = :email ', array(":uniacid" => $_W['uniacid'], ":email" => $data['email']));
            if (!$uid || empty($uid) || $uid <= 0) {
                pdo_insert('mc_members', $data);
                $uid = pdo_insertid();
            }
            $_SESSION['uid'] = $uid;
            $fan = mc_fansinfo($_SESSION['oauth_openid']);
            if (empty($fan)) {
                $fan = array("openid" => $_SESSION['oauth_openid'], "uid" => $uid, "acid" => $_W['acid'], "uniacid" => $_W['uniacid'], "salt" => random(8), "updatetime" => TIMESTAMP, "follow" => 0, "followtime" => 0, "unfollowtime" => 0);
                if (true === $getUserInfo) {
                    $fan['nickname'] = $data['nickname'];
                    $fan['follow'] = $userinfo['subscribe'];
                    $fan['followtime'] = $userinfo['subscribe_time'];
                    $fan['tag'] = base64_encode(iserializer($userinfo));
                }
                pdo_insert('mc_mapping_fans', $fan);
            } else {
                $fan['uid'] = $uid;
                $fan['updatetime'] = TIMESTAMP;
                unset($fan['tag']);
                if (true === $getUserInfo) {
                    $fan['nickname'] = $data['nickname'];
                    $fan['follow'] = $userinfo['subscribe'];
                    $fan['followtime'] = $userinfo['subscribe_time'];
                    $fan['tag'] = base64_encode(iserializer($userinfo));
                }
                unset($fan['sex']);
                unset($fan['gender']);
                unset($fan['headimgurl']);
                unset($fan['avatar']);
                pdo_update('mc_mapping_fans', $fan, array("openid" => $_SESSION['oauth_openid'], "acid" => $_W['acid'], "uniacid" => $_W['uniacid']));
            }
            $_W['fans'] = $fan;
            $_W['fans']['from_user'] = $_SESSION['oauth_openid'];
            if (intval($_W['account']['level']) != 4) {
                $mc_oauth_fan = _mc_oauth_fans($_SESSION['oauth_openid'], $_W['acid']);
                if (empty($mc_oauth_fan)) {
                    $data = array("acid" => $_W['acid'], "oauth_openid" => $_SESSION['oauth_openid'], "uid" => $uid, "openid" => $_SESSION['openid']);
                    pdo_insert('mc_oauth_fans', $data);
                } else {
                    if (!empty($mc_oauth_fan['uid'])) {
                        $_SESSION['uid'] = intval($mc_oauth_fan['uid']);
                    }
                    if (empty($_SESSION['openid']) && !empty($mc_oauth_fan['openid'])) {
                        $_SESSION['openid'] = strval($mc_oauth_fan['openid']);
                    }
                }
            } else {
                $_SESSION['openid'] = $_SESSION['oauth_openid'];
            }
            header('Location: ' . $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING']);
            exit;
        }
        load()->model('mc');
        $this->_user = mc_fetch($_SESSION['uid'], array("email", "mobile", "nickname", "gender", "avatar", "credit1", "credit2", "credit3", "credit4", "credit5"));
        if (empty($this->_user)) {
            if (intval($_W['account']['level']) != 4) {
                pdo_delete('mc_oauth_fans', array("acid" => $_W['acid'], "uid" => $_SESSION['uid']));
            }
            unset($_SESSION['uid']);
            header('Location: ' . $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING']);
            exit;
        }
        if (!empty($this->_user['nickname']) || !empty($this->_user['avatar'])) {
            $this->_is_user_infoed = 1;
        }
    }
    public $_cmd;
    public $_mine;
    protected function _doMobileInitialize()
    {
        global $_GPC, $_W, $do;
        $this->_cmd = $_GPC['cmd'];
        $this->_mine = $this->vp_user($this->_user['uid'], true);
        if ($_W['module_setting']['set_app_status'] == 4) {
            if (!$this->_mine || $this->_mine['agent'] != 9) {
                $this->returnError($_W['module_setting']['set_app_hint'] ? $_W['module_setting']['set_app_hint'] : '抱歉，应用目前暂停访问');
            }
        }
        if (!$_W['isajax']) {
            if (empty($this->_mine)) {
                if ($_W['module_setting']['set_app_status'] == 3) {
                    $this->returnError($_W['module_setting']['set_app_hint'] ? $_W['module_setting']['set_app_hint'] : '抱歉，目前暂停新用户加入');
                }
                $fuid = $_GPC['fuid'];
                if (!empty($fuid)) {
                    $fuid = pdecode($fuid);
                    if (!empty($fuid)) {
                        $fuid = intval($fuid);
                    }
                }
                $fuid = $fuid > 0 ? $fuid : 0;
                $auid = $_GPC['auid'];
                $agentp = null;
                if (!empty($auid)) {
                    $auid = pdecode($auid);
                    if (!empty($auid)) {
                        $auid = intval($auid);
                        if ($auid > 0) {
                            $agentp = pdo_fetch('select * from ' . tablename('vp_ph_user') . ' where uniacid=:uniacid AND id=:id AND agent>0 ', array(":uniacid" => $_W['uniacid'], ":id" => $auid));
                        }
                    }
                }
                load()->model('mc');
                $fan = mc_fansinfo($this->_user['uid'], $_W['acid'], $_W['uniacid']);
                $mine = array();
                $mine['uniacid'] = $_W['uniacid'];
                $mine['uid'] = $this->_user['uid'];
                $mine['openid'] = $_SESSION['openid'];
                if (!empty($this->_user['nickname'])) {
                    $mine['nickname'] = $this->_user['nickname'];
                }
                if (!empty($this->_user['avatar'])) {
                    $mine['avatar'] = $this->_user['avatar'];
                }
                $mine['fuid'] = $fuid;
                if ($agentp) {
                    if ($agentp['agent'] > 0) {
                        $mine['agentp'] = $agentp['id'];
                        pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET 	users=users+1 where uniacid=:uniacid AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $agentp['id']));
                    }
                    if ($agentp['agentp'] > 0) {
                        $mine['agentp1'] = $agentp['agentp'];
                    }
                    if ($agentp['agentp1'] > 0) {
                        $mine['agentp2'] = $agentp['agentp1'];
                    }
                }
                $mine['create_time'] = time();
                pdo_insert('vp_ph_user', $mine);
                $mine_id = pdo_insertid();
                if ($mine_id > 0) {
                    $this->_mine = $this->vp_user($mine_id);
                }
            }
        }
        if (empty($this->_mine)) {
            $this->returnError('请从正常入口访问');
        }
        if ($this->_mine['black'] == 1) {
            $this->returnError('您暂时无法访问，原因：' . $this->_mine['black_why']);
        }
        if (empty($this->_mine['openid']) || empty($this->_mine['nickname']) && !empty($this->_user['nickname']) || empty($this->_mine['avatar']) && !empty($this->_user['avatar'])) {
            $this->_mine['openid'] = $_SESSION['openid'];
            $this->_mine['nickname'] = $this->_user['nickname'];
            $this->_mine['avatar'] = $this->_user['avatar'];
            pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET openid=:openid,nickname=:nickname,avatar=:avatar where uniacid=:uniacid and id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $this->_mine['id'], ":openid" => $this->_mine['openid'], ":nickname" => $this->_user['nickname'], ":avatar" => $this->_user['avatar']));
        }
    }
    public function doMobileLogin()
    {
        global $_GPC, $_W;
        if (empty($_SESSION['login_referer'])) {
            $_SESSION['login_referer'] = $_SERVER['HTTP_REFERER'];
        }
        if ($_W['container'] == 'wechat') {
            $userinfo = mc_oauth_userinfo();
            if (is_error($userinfo)) {
                unset($_SESSION['login_referer']);
                return message($userinfo['message'], '', 'error');
            }
            if (empty($userinfo) || !is_array($userinfo)) {
                unset($_SESSION['login_referer']);
                return message('微信自动登录失败，请重试', '', 'error');
            }
            $login_referer = $_SESSION['login_referer'];
            unset($_SESSION['login_referer']);
            header('Location: ' . $login_referer);
            exit;
        } else {
            unset($_SESSION['login_referer']);
            return message('该应用仅支持在微信中运行', '', 'error');
        }
        unset($_SESSION['login_referer']);
        return message('该应用目前仅支持在微信中访问', '', 'error');
    }
    protected function vp_user($id, $is_uid = false, $fields = "*")
    {
        global $_W;
        if ($is_uid) {
            $_user = pdo_fetch('select ' . $fields . ' from ' . tablename('vp_ph_user') . ' where uniacid=:uniacid and uid=:uid ', array(":uniacid" => $_W['uniacid'], ":uid" => $id));
        } else {
            $_user = pdo_fetch('select ' . $fields . ' from ' . tablename('vp_ph_user') . ' where uniacid=:uniacid and id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $id));
        }
        if ($_user) {
            $_user['_id'] = pencode($_user['id']);
            if ($_user['cover']) {
                $_user['_cover'] = VP_IMAGE_URL($_user['cover']);
            }
            if ($_user['images']) {
                $_user['images'] = iunserializer($_user['images']);
            }
        }
        return $_user;
    }
    protected function vp_users($uids, $fields)
    {
        global $_W;
        if (empty($uids)) {
            return null;
        }
        if (is_array($uids)) {
            if (count($uids) == 0) {
                return array();
            }
            return pdo_fetchall('select ' . $fields . ' from ' . tablename('vp_ph_user') . ' where uniacid=:uniacid  AND uid IN(' . implode(',', $uids) . ') ', array(":uniacid" => $_W['uniacid']), 'uid');
        } else {
            return pdo_fetch('select ' . $fields . ' from ' . tablename('vp_ph_user') . ' where uniacid=:uniacid  AND uid=:uid ', array(":uniacid" => $_W['uniacid'], ":uid" => $uids));
        }
    }
    public function doMobileReset()
    {
        global $_GPC, $_W;
        session_unset();
        message('已清空');
    }
    public function doMobileQr()
    {
        global $_GPC;
        $raw = @base64_decode($_GPC['raw']);
        if (!empty($raw)) {
            include MD_ROOT . '/source/common/phpqrcode.php';
            QRcode::png($raw, false, QR_ECLEVEL_Q, 4);
        }
    }
    public function doWebQr()
    {
        global $_GPC;
        $raw = @base64_decode($_GPC['raw']);
        if (!empty($raw)) {
            include MD_ROOT . '/source/common/phpqrcode.php';
            QRcode::png($raw, false, QR_ECLEVEL_Q, 4);
        }
    }
    protected function returnMessage($msg, $redirect = "", $type = "")
    {
        global $_W, $_GPC;
        if ($redirect == 'refresh') {
            $redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
        }
        if ($redirect == 'referer') {
            $redirect = referer();
        }
        if ($redirect == '') {
            $type = in_array($type, array("success", "error", "info", "warn")) ? $type : 'info';
        } else {
            $type = in_array($type, array("success", "error", "info", "warn")) ? $type : 'success';
        }
        if (empty($msg) && !empty($redirect)) {
            header('location: ' . $redirect);
            exit;
        }
        $label = $type;
        if ($type == 'error') {
            $label = 'warn';
        }
        include $this->template('inc/message');
        exit;
    }
    protected function returnError($message, $data = "", $status = 0, $type = "")
    {
        global $_W;
        if ($_W['isajax'] || $type == 'ajax') {
            header('Content-Type:application/json; charset=utf-8');
            $ret = array("status" => $status, "info" => $message, "data" => $data);
            exit(json_encode($ret));
        } else {
            return $this->returnMessage($message, $data, 'error');
        }
    }
    protected function returnSuccess($message, $data = "", $status = 1, $type = "")
    {
        global $_W;
        if ($_W['isajax'] || $type == 'ajax') {
            header('Content-Type:application/json; charset=utf-8');
            $ret = array("status" => $status, "info" => $message, "data" => $data);
            exit(json_encode($ret));
        } else {
            return $this->returnMessage($message, $data, 'success');
        }
    }
    protected function vp_nav_2_url($model, $link)
    {
        if ($model == 'url') {
            return $link;
        } else {
            if ($model == 'page') {
                return $this->createMobileUrl('index', array("cmd" => $link));
            } else {
                if ($model == 'cat') {
                    return $this->createMobileUrl('index', array("cmd" => "cat", "code" => $link));
                }
            }
        }
    }
    protected function payReady($params = array(), $mine = array())
    {
        global $_W;
        $params['module'] = $this->module['name'];
        $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        $pars[':module'] = $params['module'];
        $pars[':tid'] = $params['tid'];
        $log = pdo_fetch($sql, $pars);
        if (empty($log)) {
            $log = array("uniacid" => $_W['uniacid'], "acid" => $_W['acid'], "openid" => $_W['member']['uid'], "module" => $this->module['name'], "tid" => $params['tid'], "fee" => $params['fee'], "card_fee" => $params['fee'], "status" => "0", "is_usecard" => "0");
            pdo_insert('core_paylog', $log);
        }
        if ($log['status'] == '1') {
            message('这个订单已经支付成功, 不需要重复支付.');
        }
        return $params;
    }
    public function payResult($params)
    {
        global $_W;
        $tinfo = $params['tid'];
        if (empty($tinfo)) {
            $this->returnError('交易失败，缺少订单号');
        }
        $tinfo = explode('_', $tinfo);
        if (count($tinfo) != 2) {
            $this->returnError('交易失败，订单号错误');
        }
        $params['tid'] = $tinfo[1];
        if ($tinfo[0] == 'FEEDIN') {
            $this->payResultFEEDIN($params);
        } else {
            if ($tinfo[0] == 'FEEDOUT') {
                $this->payResultFEEDOUT($params);
            } else {
                if ($tinfo[0] == 'AGENT') {
                    $this->payResultAGENT($params);
                } else {
                    $this->returnError('交易失败，订单业务不存在');
                }
            }
        }
    }
    protected function payResultFEEDIN($params)
    {
        global $_W;
        $order = pdo_fetch('select * from ' . tablename('vp_ph_order') . ' where id=:id ', array(":id" => $params['tid']));
        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            if (empty($order)) {
                exit;
            }
            $payed = round(floatval($params['fee']) * 100);
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != $payed) {
                exit;
            }
            pdo_query('UPDATE ' . tablename('vp_ph_order') . ' SET status=20,pay=:pay,pay_time=:pay_time where id=:id', array(":id" => $order['id'], ":pay" => $order['to_pay'], ":pay_time" => $_W['timestamp']));
            pdo_query('UPDATE ' . tablename('vp_ph_feed') . ' SET status=1 where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $order['biz_id']));
            pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET ' . $order['biz'] . '=' . $order['biz'] . '+1 where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $order['uid']));
            $agentp_money = $order['agentp_money'];
            if ($agentp_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp'], ":agent_money" => $agentp_money, ":agent_money_in" => $agentp_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp'], "uid" => $order['agentp'], "money" => $agentp_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '我的用户' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp1_money = $order['agentp1_money'];
            if ($agentp1_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp1'], ":agent_money" => $agentp1_money, ":agent_money_in" => $agentp1_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp1'], "uid" => $order['agentp1'], "money" => $agentp1_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp2_money = $order['agentp2_money'];
            if ($agentp2_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp2'], ":agent_money" => $agentp2_money, ":agent_money_in" => $agentp2_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp2'], "uid" => $order['agentp2'], "money" => $agentp2_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
        }
        if ($params['from'] == 'return') {
            if (empty($order)) {
                $this->returnError('支付失败，订单错误');
            }
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != round(floatval($params['fee']) * 100)) {
                $this->returnError('支付金额不符【' . $order['to_pay'] * 100 . '】【' . $params['fee'] . '】！');
            }
            if ($params['result'] == 'success') {
                $msg = '纸条已放入，等待缘分降临吧~';
                $url = $order['pay_callback'];
                $this->returnSuccess($msg, $url);
            } else {
                $this->returnError('支付失败！');
            }
        }
    }
    protected function payResultFEEDOUT($params)
    {
        global $_W;
        $order = pdo_fetch('select * from ' . tablename('vp_ph_order') . ' where id=:id ', array(":id" => $params['tid']));
        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            if (empty($order)) {
                exit;
            }
            $payed = round(floatval($params['fee']) * 100);
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != $payed) {
                exit;
            }
            pdo_query('UPDATE ' . tablename('vp_ph_order') . ' SET status=20,pay=:pay,pay_time=:pay_time where id=:id', array(":id" => $order['id'], ":pay" => $order['to_pay'], ":pay_time" => $_W['timestamp']));
            pdo_query('UPDATE ' . tablename('vp_ph_user_feed') . ' SET status=1 where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $order['biz_id']));
            pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET ' . $order['biz'] . '=' . $order['biz'] . '+1 where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $order['uid']));
            $ufeed = pdo_fetch('select * from ' . tablename('vp_ph_user_feed') . ' where uniacid=:uniacid AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $order['biz_id']));
            if ($ufeed) {
                pdo_query('UPDATE ' . tablename('vp_ph_feed') . ' SET views=views+1 where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $ufeed['feed_id']));
            }
            $agentp_money = $order['agentp_money'];
            if ($agentp_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp'], ":agent_money" => $agentp_money, ":agent_money_in" => $agentp_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp'], "uid" => $order['agentp'], "money" => $agentp_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '我的用户' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp1_money = $order['agentp1_money'];
            if ($agentp1_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp1'], ":agent_money" => $agentp1_money, ":agent_money_in" => $agentp1_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp1'], "uid" => $order['agentp1'], "money" => $agentp1_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp2_money = $order['agentp2_money'];
            if ($agentp2_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp2'], ":agent_money" => $agentp2_money, ":agent_money_in" => $agentp2_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp2'], "uid" => $order['agentp2'], "money" => $agentp2_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
        }
        if ($params['from'] == 'return') {
            if (empty($order)) {
                $this->returnError('支付失败，订单错误');
            }
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != round(floatval($params['fee']) * 100)) {
                $this->returnError('支付金额不符【' . $order['to_pay'] * 100 . '】【' . $params['fee'] . '】！');
            }
            if ($params['result'] == 'success') {
                header('Location: ' . $order['pay_callback']);
                exit;
            } else {
                $this->returnError('支付失败！');
            }
        }
    }
    protected function payResultAGENT($params)
    {
        global $_W;
        $order = pdo_fetch('select * from ' . tablename('vp_ph_order') . ' where id=:id ', array(":id" => $params['tid']));
        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            if (empty($order)) {
                exit;
            }
            $payed = round(floatval($params['fee']) * 100);
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != $payed) {
                exit;
            }
            pdo_query('UPDATE ' . tablename('vp_ph_order') . ' SET status=20,pay=:pay,pay_time=:pay_time where id=:id', array(":id" => $order['id'], ":pay" => $order['to_pay'], ":pay_time" => $_W['timestamp']));
            pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent=1,agent_time=:agent_time where uniacid=:uniacid AND id=:id', array(":uniacid" => $_W['uniacid'], ":id" => $order['biz_id'], ":agent_time" => $_W['timestamp']));
            if ($order['agentp'] > 0) {
                pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agentbs=agentbs+1 where uniacid=:uniacid AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $order['agentp']));
            }
            if ($order['agentp1'] > 0) {
                pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agentb1s=agentb1s+1 where uniacid=:uniacid AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $order['agentp1']));
            }
            $agentp_money = $order['agentp_money'];
            if ($agentp_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp'], ":agent_money" => $agentp_money, ":agent_money_in" => $agentp_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp'], "uid" => $order['agentp'], "money" => $agentp_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '我的用户' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp1_money = $order['agentp1_money'];
            if ($agentp1_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp1'], ":agent_money" => $agentp1_money, ":agent_money_in" => $agentp1_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp1'], "uid" => $order['agentp1'], "money" => $agentp1_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
            $agentp2_money = $order['agentp2_money'];
            if ($agentp2_money > 0) {
                $ret = pdo_query('UPDATE ' . tablename('vp_ph_user') . ' SET agent_money=agent_money+:agent_money,agent_money_in=agent_money_in+:agent_money_in where uniacid=:uniacid AND id=:id', array(":uniacid" => $order['uniacid'], ":id" => $order['agentp2'], ":agent_money" => $agentp2_money, ":agent_money_in" => $agentp2_money));
                if ($ret > 0) {
                    pdo_insert('vp_ph_money', array("uniacid" => $order['uniacid'], "who" => "agent", "who_id" => $order['agentp2'], "uid" => $order['agentp2'], "money" => $agentp2_money, "biz" => $order['biz'], "biz_id" => $order['id'], "biz_name" => '下下级推广' . $order['biz_name'], "create_time" => $_W['timestamp']));
                }
            }
        }
        if ($params['from'] == 'return') {
            if (empty($order)) {
                $this->returnError('支付失败，订单错误');
            }
            if (intval($order['to_pay'] * 100) < 1 || round($order['to_pay'] * 100) != round(floatval($params['fee']) * 100)) {
                $this->returnError('支付金额不符【' . $order['to_pay'] * 100 . '】【' . $params['fee'] . '】！');
            }
            if ($params['result'] == 'success') {
                $msg = '购买成功!';
                $url = $order['pay_callback'];
                $this->returnSuccess($msg, $url);
            } else {
                $this->returnError('支付失败！');
            }
        }
    }
    public function doMobileTest()
    {
        global $_W;
        echo $_W['fans']['follow'];
    }
    protected function transferByPay($transfer)
    {
        global $_W;
        $account = $_W['account'];
        if (empty($account)) {
            return error(1, '公众号缺少配置信息');
        }
        $pay_setting = $account['setting']['payment'];
        $refund_setting = $account['setting']['payment']['wechat_refund'];
        if ($refund_setting['switch'] != 1) {
            return error(1, '未开启微信退款功能！');
        }
        if (empty($refund_setting['key']) || empty($refund_setting['cert'])) {
            return error(1, '缺少微信证书！');
        }
        $api = array("mchid" => $pay_setting['wechat']['mchid'], "appid" => $account['key'], "key" => $pay_setting['wechat']['apikey']);
        $ret = array();
        $amount = $transfer['amount'];
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $pars = array();
        $pars['mch_appid'] = $api['appid'];
        $pars['mchid'] = $api['mchid'];
        $pars['nonce_str'] = random(32);
        $pars['partner_trade_no'] = $api['mchid'] . date('Ymd') . $transfer['id'];
        $pars['openid'] = empty($transfer['openid']) ? $_W['openid'] : $transfer['openid'];
        $pars['check_name'] = 'NO_CHECK';
        $pars['amount'] = $amount;
        $pars['desc'] = $transfer['desc'];
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key={$api['key']}";
        $pars['sign'] = strtoupper(md5($string1));
        $xml = array2xml($pars);
        $extras = array();
        $cert_key_file = ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_cert_key.pem';
        $cert_cert_file = ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_cert_cert.pem';
        if (!file_exists($cert_key_file)) {
            file_put_contents($cert_key_file, authcode($refund_setting['key'], 'DECODE'));
        }
        if (!file_exists($cert_cert_file)) {
            file_put_contents($cert_cert_file, authcode($refund_setting['cert'], 'DECODE'));
        }
        $extras['CURLOPT_SSLCERT'] = $cert_cert_file;
        $extras['CURLOPT_SSLKEY'] = $cert_key_file;
        load()->func('communication');
        $procResult = null;
        $resp = ihttp_request($url, $xml, $extras);
        if (is_error($resp)) {
            return error(-1, $resp['message']);
        } else {
            $xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
            $dom = new DOMDocument();
            if ($dom->loadXML($xml)) {
                $xpath = new DOMXPath($dom);
                $code = $xpath->evaluate('string(//xml/return_code)');
                $result = $xpath->evaluate('string(//xml/result_code)');
                if (strtolower($code) == 'success' && strtolower($result) == 'success') {
                    $partner_trade_no = $xpath->evaluate('string(//xml/partner_trade_no)');
                    $payment_no = $xpath->evaluate('string(//xml/payment_no)');
                    $payment_time = $xpath->evaluate('string(//xml/payment_time)');
                    return array("partner_trade_no" => $partner_trade_no, "payment_no" => $payment_no, "payment_time" => $payment_time);
                } else {
                    $error = $xpath->evaluate('string(//xml/err_code_des)');
                    return error(-2, $error);
                }
            } else {
                return error(-3, 'error response');
            }
        }
    }
    public function doMobileTaskDayReset()
    {
        global $_W, $_GPC;
        if (empty($_GPC['_key_']) || $_GPC['_key_'] != $_W['module_setting']['task_key']) {
            echo 'no auth';
            exit;
        }
        $ret = pdo_query('UPDATE ' . tablename('vp_ph_task') . ' SET 	out_today=0 where uniacid = :uniacid ', array(":uniacid" => $_W['uniacid']));
        echo '已重置' . $ret . '个任务';
    }
    public function doMobileTaskWork()
    {
        global $_W, $_GPC;
        if (empty($_GPC['_key_']) || $_GPC['_key_'] != $_W['module_setting']['task_key']) {
            echo 'no auth';
            exit;
        }
        $exp_works = pdo_fetchall('select * from ' . tablename('vp_ph_task_user') . ' where uniacid = :uniacid AND status=10 AND status_time < :status_time', array(":uniacid" => $_W['uniacid'], ":status_time" => $_W['timestamp'] - 3600 * intval($_W['module_setting']['work_exptime'])));
        $exps = 0;
        $i = 0;
        while ($i < count($exp_works)) {
            $_work = $exp_works[$i];
            $ret = pdo_query('UPDATE ' . tablename('vp_ph_task_user') . ' SET 	status=38,status_time=:status_time where uniacid = :uniacid  AND id=:id AND status=10 ', array(":uniacid" => $_W['uniacid'], ":id" => $_work['id'], ":status_time" => $_W['timestamp']));
            if ($ret > 0) {
                pdo_query('UPDATE ' . tablename('vp_ph_task') . ' SET stock_all=stock_all+1,out_today=out_today-1 where uniacid = :uniacid  AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $_work['task_id']));
                $exps++;
            }
            $i++;
        }
        $ok_works = pdo_fetchall('select * from ' . tablename('vp_ph_task_user') . ' where uniacid = :uniacid AND status=20 AND work_time < :work_time', array(":uniacid" => $_W['uniacid'], ":work_time" => $_W['timestamp'] - 3600 * intval($_W['module_setting']['work_vertime'])));
        $oks = 0;
        $i = 0;
        while ($i < count($ok_works)) {
            $_work = $ok_works[$i];
            $ret = $this->biz_work_verify_yes($_work, 46, 0);
            if ($ret === true) {
                $oks++;
            }
            $i++;
        }
        $re_works = pdo_fetchall('select * from ' . tablename('vp_ph_task_user') . ' where uniacid = :uniacid AND status=25 AND status_time < :status_time', array(":uniacid" => $_W['uniacid'], ":status_time" => $_W['timestamp'] - 3600 * intval($_W['module_setting']['work_retime'])));
        $res = 0;
        $i = 0;
        while ($i < count($re_works)) {
            $_work = $re_works[$i];
            $ret = pdo_query('UPDATE ' . tablename('vp_ph_task_user') . ' SET 	status=36,status_time=:status_time where uniacid = :uniacid  AND id=:id AND status=25 ', array(":uniacid" => $_W['uniacid'], ":id" => $_work['id'], ":status_time" => $_W['timestamp']));
            if ($ret > 0) {
                pdo_query('UPDATE ' . tablename('vp_ph_task') . ' SET stock_all=stock_all+1,out_today=out_today-1 where uniacid = :uniacid  AND id=:id ', array(":uniacid" => $_W['uniacid'], ":id" => $_work['task_id']));
                $res++;
                $this->nt_worker_workno(array("openid" => $_work['openid'], "first" => "抱歉，您的订单信息无效，未能获得补贴", "k1" => $_work['verify_remark'], "k2" => date('Y-m-d H:i', $_W['timestamp']), "k3" => "如果对审核结果有异议，可联系客服", "remark" => "点击查看详情", "url" => $_W['siteroot'] . 'app/' . substr($this->createMobileUrl('task', array("cid" => pencode($_work['city_id']), "id" => pencode($_work['task_id']), "tuid" => pencode($_work['id']))), 2)));
            }
            $i++;
        }
        echo '共取消' . $exps . '个超时补贴，发放' . $oks . '个未审补贴，复审' . $res . '个无效补贴';
    }
    public function doMobileNtTest()
    {
        global $_W, $_GPC;
        $this->_doMobileAuth();
        $user = $this->_user;
        $is_user_infoed = $this->_is_user_infoed;
        $this->_doMobileInitialize();
        $cmd = $this->_cmd;
        $mine = $this->_mine;
    }
    protected function nt_user_pair($ps)
    {
        global $_W;
        load()->classs('weixin.account');
        if (!empty($_W['module_setting']['nt_pair'])) {
            $accObj = WeiXinAccount::create($_W['uniacid']);
            $pdata = array("first" => array("value" => $ps['first'], "color" => "#f73959"), "keyword1" => array("value" => $ps['k1']), "keyword2" => array("value" => $ps['k2']), "keyword3" => array("value" => $ps['k3']), "remark" => array("value" => $ps['remark']));
            $ret = $accObj->sendTplNotice($ps['openid'], $_W['module_setting']['nt_pair'], $pdata, $ps['url'], '#f73959');
            return $ret;
        }
        return false;
    }
}