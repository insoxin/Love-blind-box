<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->classs('weixin.platform');

class WxappPlatform extends WeixinPlatform {
	const JSCODEURL = 'https://api.weixin.qq.com/sns/component/jscode2session?appid=%s&js_code=%s&grant_type=authorization_code&component_appid=%s&component_access_token=%s';

	const FAST_REGISTER_WEAPP_CREATE = 'https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp?action=create&component_access_token=%s';
	const FAST_REGISTER_WEAPP_SEARCH = 'https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp?action=search&component_access_token=%s';

		public $appid;
	protected $appsecret;
	public $encodingaeskey;
	public $token;
	protected $refreshtoken;
	protected $tablename = 'account_wxapp';
	protected $menuFrame = 'wxapp';
	protected $type = ACCOUNT_TYPE_APP_AUTH;
	protected $typeName = '微信小程序';
	protected $typeSign = WXAPP_TYPE_SIGN;
	protected $supportVersion = STATUS_ON;

	public function __construct($uniaccount = array()) {
		$setting = setting_load('platform');
		$this->appid = $setting['platform']['appid'];
		$this->appsecret = $setting['platform']['appsecret'];
		$this->token = $setting['platform']['token'];
		$this->encodingaeskey = $setting['platform']['encodingaeskey'];
		parent::__construct($uniaccount);
	}

	protected function getAccountInfo($uniacid) {
		if ('wxd101a85aa106f53e' == $this->account['key']) {
			$this->account['key'] = $this->appid;
			$this->openPlatformTestCase();
		}
		$account = table('account_wxapp')->getAccount($uniacid);
		$account['encrypt_key'] = $this->appid;

		return $account;
	}

	public function getAuthLoginUrl() {
		$preauthcode = $this->getPreauthCode();
		if (is_error($preauthcode)) {
			$authurl = "javascript:alert('{$preauthcode['message']}');";
		} else {
			$authurl = sprintf(ACCOUNT_PLATFORM_API_LOGIN, $this->appid, $preauthcode, urlencode($GLOBALS['_W']['siteroot'] . 'index.php?c=wxapp&a=auth&do=forward'), ACCOUNT_PLATFORM_API_LOGIN_WXAPP);
		}

		return $authurl;
	}

	public function getOauthInfo($code = '') {
		$component_accesstoken = $this->getComponentAccesstoken();
		if (is_error($component_accesstoken)) {
			return $component_accesstoken;
		}
		$apiurl = sprintf(self::JSCODEURL, $this->account['key'], $code, $this->appid, $component_accesstoken);

		$response = $this->request($apiurl);
		if (is_error($response)) {
			return $response;
		}
		cache_write('account_oauth_refreshtoken' . $this->account['key'], $response['refresh_token']);

		return $response;
	}

	protected function setAuthRefreshToken($token) {
		$tablename = 'account_wxapp';
		pdo_update($tablename, array('auth_refresh_token' => $token), array('uniacid' => $this->account['uniacid']));
		cache_write(cache_system_key('account_auth_refreshtoken', array('uniacid' => $this->account['uniacid'])), $token);
	}

	
	public function pkcs7Encode($encrypt_data, $iv) {
		$key = base64_decode($_SESSION['session_key']);
		$result = aes_pkcs7_decode($encrypt_data, $key, $iv);
		if (is_error($result)) {
			return error(1, '解密失败');
		}
		$result = json_decode($result, true);
		if (empty($result)) {
			return error(1, '解密失败');
		}
		if ($result['watermark']['appid'] != $this->account['key']) {
			return error(1, '解密失败');
		}
		unset($result['watermark']);

		return $result;
	}

	public function result($errno, $message = '', $data = '') {
		exit(json_encode(array(
			'errno' => $errno,
			'message' => $message,
			'data' => $data,
		)));
	}

	public function getDailyVisitTrend($date) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token={$token}";

		$response = $this->requestApi($url, json_encode(array('begin_date' => $date, 'end_date' => $date)));
		if (is_error($response)) {
			return $response;
		}

		return $response['list'][0];
	}

	public function fastRegisterWxapp($data) {
		$component_accesstoken = $this->getComponentAccesstoken();
		if (is_error($component_accesstoken)) {
			return $component_accesstoken;
		}
		$apiurl = sprintf(self::FAST_REGISTER_WEAPP_CREATE, $component_accesstoken);
		$post = array(
			'name' => $data['name'],   			'code' => $data['code'], 			'code_type' => $data['code_type'], 			'legal_persona_wechat' => $data['legal_persona_wechat'], 			'legal_persona_name' => $data['legal_persona_name'], 			'component_phone' => $data['component_phone'], 		);

		return $this->request($apiurl, $post);
	}

	public function fastRegisterWxappSearch($data) {
		$component_accesstoken = $this->getComponentAccesstoken();
		if (is_error($component_accesstoken)) {
			return $component_accesstoken;
		}
		$apiurl = sprintf(self::FAST_REGISTER_WEAPP_SEARCH, $component_accesstoken);
		$post = array(
			'name' => $data['name'],   			'legal_persona_wechat' => $data['legal_persona_wechat'], 			'legal_persona_name' => $data['legal_persona_name'], 		);

		return $this->request($apiurl, $post);
	}

		public function bindTester($wechatid) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('wechatid' => $wechatid);
		$url = "https://api.weixin.qq.com/wxa/bind_tester?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getDomain() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'action' => 'get',
		);
		$url = "https://api.weixin.qq.com/wxa/modify_domain?access_token={$token}";

		return $this->request($url, $data);
	}

		public function setWebViewDomain($data) {
		$webviewdomain = array();
		if ('get' == $data['action']) {
			$cachekey = cache_system_key('account_web_view_domain', array('uniacid' => $this->account['uniacid']));
			$webviewdomain = cache_load($cachekey);
		}
		if (empty($webviewdomain)) {
			$token = $this->getAccessToken();
			if (is_error($token)) {
				return $token;
			}
			$url = "https://api.weixin.qq.com/wxa/setwebviewdomain?access_token={$token}";
			$webviewdomain = $this->request($url, $data);
			if (is_error($webviewdomain)) {
				return error($webviewdomain['errno'], $this->errorCode($webviewdomain['errno']));
			}
			if ('get' == $data['action']) {
				$webviewdomain = $webviewdomain['webviewdomain'];
				cache_write($cachekey, $webviewdomain, CACHE_EXPIRE_LONG);
			}
		}
		return $webviewdomain;
	}

		public function modifyDomain($domains) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'action' => 'set',
			'requestdomain' => $domains['requestdomain'],
			'wsrequestdomain' => $domains['wsrequestdomain'],
			'uploaddomain' => $domains['uploaddomain'],
			'downloaddomain' => $domains['downloaddomain'],
		);
		$url = "https://api.weixin.qq.com/wxa/modify_domain?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getAccountBasicInfo() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/account/getaccountbasicinfo?access_token={$token}";

		return $this->request($url);
	}

		public function setNickname($name) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('nick_name' => $name);
		$url = "https://api.weixin.qq.com/wxa/setnickname?access_token={$token}";

		return $this->request($url, $data);
	}

		public function queryNickname($audit_id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('audit_id' => $audit_id);
		$url = "https://api.weixin.qq.com/wxa/api_wxa_querynickname?access_token={$token}";

		return $this->request($url, $data);
	}

		public function checkwxVerifyNickname($nick_name) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('nick_name' => $nick_name);
		$url = "https://api.weixin.qq.com/cgi-bin/wxverify/checkwxverifynickname?access_token={$token}";

		return $this->request($url, $data);
	}

		public function modifyHeadImage($path) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$upload_to_temporary = $this->uploadMedia($path);
		if (is_error($upload_to_temporary)) {
			return $upload_to_temporary;
		}
		$media_id = $upload_to_temporary['media_id'];
		$data = array(
			'head_img_media_id' => $media_id,
			'x1' => 0,
			'y1' => 0,
			'x2' => 1,
			'y2' => 1,
		);
		$url = "https://api.weixin.qq.com/cgi-bin/account/modifyheadimage?access_token={$token}";

		return $this->request($url, $data);
	}

		public function modifySignature($signature) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('signature' => $signature);
		$url = "https://api.weixin.qq.com/cgi-bin/account/modifysignature?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getAllCategories() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/getallcategories?access_token={$token}";

		return $this->request($url);
	}

		public function addCategory($category) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		if (!empty($category['certicates']) && !empty($category['certicates']['value'])) {
			$upload_to_temporary = $this->uploadMedia($category['certicates']['value']);
			if (is_error($upload_to_temporary)) {
				return $upload_to_temporary;
			}
			$category['certicates']['value'] = $upload_to_temporary['media_id'];
		}
		$data = array('categories' => $category);
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/addcategory?access_token={$token}";

		return $this->request($url, $data);
	}

		public function deleteCategory($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/deletecategory?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getCategory() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/getcategory?access_token={$token}";

		return $this->request($url);
	}

		public function modifyCategory($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/modifycategory?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getTemplateDraftList() {
		$token = $this->getComponentAccesstoken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/gettemplatedraftlist?access_token={$token}";

		return $this->request($url);
	}

		public function getTemplatelist() {
		$token = $this->getComponentAccesstoken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/gettemplatelist?access_token={$token}";

		return $this->request($url);
	}

		public function addToTemplate($draft_id) {
		$token = $this->getComponentAccesstoken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('draft_id' => $draft_id);
		$url = "https://api.weixin.qq.com/wxa/addtotemplate?access_token={$token}";

		return $this->request($url, $data);
	}

		public function deleteTemplate($template_id) {
		$token = $this->getComponentAccesstoken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('template_id' => $template_id);
		$url = "https://api.weixin.qq.com/wxa/deletetemplate?access_token={$token}";

		return $this->request($url, $data);
	}

		public function commit($template_id, $ext_json, $version, $desc = '') {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'template_id' => $template_id,
			'ext_json' => !empty($ext_json) ? json_encode($ext_json) : '',
			'user_version' => $version,
			'user_desc' => $desc,
		);
		$url = "https://api.weixin.qq.com/wxa/commit?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getQrcode($path = '') {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token={$token}";
		if (!empty($path)) {
			$url .= '&path=' . urlencode($path);
		}

		return ihttp_request($url);
	}

		public function getWxappCategory() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/get_category?access_token={$token}";

		return $this->request($url);
	}

		public function getWxappPage() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/get_page?access_token={$token}";

		return $this->request($url);
	}

		public function submitAudit($item_list = array()) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('item_list' => $item_list);
		$url = "https://api.weixin.qq.com/wxa/submit_audit?access_token={$token}";

		return $this->request($url, $data);
	}

		public function getLatestAuditStatus() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token={$token}";

		return $this->request($url);
	}
	
		public function getAuditStatus($auditid) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array('auditid' => $auditid);
		$url = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token={$token}";
		
		return $this->request($url, $data);
	}
	
		public function release() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/release?access_token={$token}";
		$response = ihttp_request($url, '{}');
		$response = json_decode($response['content'], true);
		if (empty($response) || !empty($response['errcode'])) {
			return error($response['errcode'], $this->errorCode($response['errcode'], $response['errmsg']));
		}

		return $response;
	}

		public function undoCodeAudit() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token={$token}";

		return $this->request($url);
	}
		public function revertCodeRelease() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/wxa/revertcoderelease?access_token={$token}";
		return $this->request($url);
	}
}