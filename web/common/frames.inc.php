<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
global $_W;

$w7_system_menu = array();
$w7_system_menu['welcome'] = array(
	'title' => '首页',
	'icon' => 'wi wi-home',
	'url' => url('home/welcome/system', array('page' => 'home')),
	'section' => array(),
);

$w7_system_menu['account_manage'] = array(
	'title' => '平台管理',
	'icon' => 'wi wi-platform-manage',
	'dimension' => 2,
	'url' => url('account/manage'),
	'section' => array(
		'account_manage' => array(
			'title' => '平台管理',
			'menu' => array(
				'account_manage_display' => array(
					'title' => '平台列表',
					'url' => url('account/manage'),
					'permission_name' => 'account_manage_display',
					'sub_permission' => array(
						array(
							'title' => '帐号停用',
							'permission_name' => 'account_manage_stop',
						),
					),
				),
				'account_manage_recycle' => array(
					'title' => '回收站',
					'url' => url('account/recycle'),
					'permission_name' => 'account_manage_recycle',
					'sub_permission' => array(
						array(
							'title' => '帐号删除',
							'permission_name' => 'account_manage_delete',
						),
						array(
							'title' => '帐号恢复',
							'permission_name' => 'account_manage_recover',
						),
					),
				),
				'account_manage_system_platform' => array(
					'title' => ' 微信开放平台',
					'url' => url('system/platform'),
					'permission_name' => 'account_manage_system_platform',
				),
				'account_manage_expired_message' => array(
					'title' => ' 自定义到期提示',
					'url' => url('account/expired-message'),
					'permission_name' => 'account_manage_expired_message',
				),
			),
		),
	),
);

$w7_system_menu['module_manage'] = array(
	'title' => '应用管理',
	'icon' => 'wi wi-module-manage',
	'dimension' => 2,
	'url' => url('module/manage-system/installed'),
	'section' => array(
		'module_manage' => array(
			'title' => '应用管理',
			'menu' => array(
				'module_manage_installed' => array(
					'title' => '已安装列表',
					'url' => url('module/manage-system/installed'),
					'permission_name' => 'module_manage_installed',
					'sub_permission' => array(),
				),
				'module_manage_stoped' => array(
					'title' => '已停用列表',
					'url' => url('module/manage-system/recycle', array('type' => MODULE_RECYCLE_INSTALL_DISABLED)),
					'permission_name' => 'module_manage_stoped',
					'sub_permission' => array(),
				),
				'module_manage_not_installed' => array(
					'title' => '未安装列表',
					'url' => url('module/manage-system/not_installed'),
					'permission_name' => 'module_manage_not_installed',
					'sub_permission' => array(),
				),
				'module_manage_recycle' => array(
					'title' => '回收站',
					'url' => url('module/manage-system/recycle', array('type' => MODULE_RECYCLE_UNINSTALL_IGNORE)),
					'permission_name' => 'module_manage_recycle',
					'sub_permission' => array(),
				),
				'module_manage_subscribe' => array(
					'title' => '订阅管理',
					'url' => url('module/manage-system/subscribe'),
					'permission_name' => 'module_manage_subscribe',
					'sub_permission' => array(),
				),
				'module_manage_expire' => array(
					'title' => '应用停用提醒',
					'url' => url('module/expire'),
					'permission_name' => 'module_manage_expire',
					'sub_permission' => array(),
				),
			),
		),
	),
);

$w7_system_menu['user_manage'] = array(
	'title' => '用户管理',
	'icon' => 'wi wi-user-group',
	'dimension' => 2,
	'url' => url('user/display'),
	'section' => array(
		'user_manage' => array(
			'title' => '用户管理',
			'menu' => array(
				'user_manage_display' => array(
					'title' => '用户列表',
					'url' => url('user/display'),
					'permission_name' => 'user_manage_display',
					'sub_permission' => array(),
				),
				
				'user_manage_check' => array(
					'title' => '审核用户',
					'url' => url('user/display', array('type' => 'check')),
					'permission_name' => 'user_manage_check',
					'sub_permission' => array(),
				),
				'user_manage_recycle' => array(
					'title' => '用户回收站',
					'url' => url('user/display', array('type' => 'recycle')),
					'permission_name' => 'user_manage_recycle',
					'sub_permission' => array(),
				),
				'user_manage_fields' => array(
					'title' => '用户属性设置',
					'url' => url('user/fields/display'),
					'permission_name' => 'user_manage_fields',
					'sub_permission' => array(),
					'founder' => true,
				),
				'user_manage_expire_setting' => array(
					'title' => '自定义到期提示',
					'url' => url('user/expire/setting'),
					'permission_name' => 'user_manage_expire_setting',
					'sub_permission' => array(),
					'founder' => true,
				),
			),
		),
	),
);

$w7_system_menu['permission'] = array(
	'title' => '权限组',
	'icon' => 'wi wi-userjurisdiction',
	'dimension' => 2,
	'url' => url('module/group'),
	'section' => array(
		'permission' => array(
			'title' => '权限组',
			'menu' => array(
				'permission_module_group' => array(
					'title' => '应用权限组',
					'url' => url('module/group'),
					'permission_name' => 'permission_module_group',
					'sub_permission' => array(),
				),
				'permission_create_account_group' => array(
					'title' => '账号权限组',
					'url' => url('user/create-group'),
					'permission_name' => 'permission_create_account_group',
					'sub_permission' => array(),
				),
				'permission_user_group' => array(
					'title' => '用户权限组合',
					'url' => url('user/group'),
					'permission_name' => 'permission_user_group',
					'sub_permission' => array(),
				),
				
			),
		),
	),
);

$w7_system_menu['system'] = array(
	'title' => '系统功能',
	'icon' => 'wi wi-setting',
	'dimension' => 3,
	'url' => url('article/notice'),
	'section' => array(
		'article' => array(
			'title' => '站内公告',
			'menu' => array(
				'system_article' => array(
					'title' => '站内公告',
					'url' => url('article/notice'),
					'icon' => 'wi wi-article',
					'permission_name' => 'system_article',
					'sub_permission' => array(
						array(
							'title' => '公告列表',
							'permission_name' => 'system_article_notice_list',
						),
						array(
							'title' => '公告分类',
							'permission_name' => 'system_article_notice_category',
						),
					),
				),
			),
			'founder' => true,
		),
		'sms' => array(
			'title' => '系统短信',
			'menu' => array(
				'system_cloud_sms_sign' => array(
					'title' => '签名管理',
					'url' => url('cloud/sms-sign'),
					'icon' => 'wi wi-sms',
					'permission_name' => 'system_cloud_sms_sign',
				),
				'system_cloud_sms_package' => array(
					'title' => '购买短信包',
					'url' => url('cloud/sms-package'),
					'icon' => 'wi wi-sms',
					'permission_name' => 'system_cloud_sms_package',
				),
				'system_cloud_sms_statistics' => array(
					'title' => '发送统计',
					'url' => url('cloud/sms-statistics'),
					'icon' => 'wi wi-sms',
					'permission_name' => 'system_cloud_sms_statistics',
				),
				'system_cloud_sms_template' => array(
					'title' => '短信模板',
					'url' => url('cloud/sms-template'),
					'icon' => 'wi wi-sms',
					'permission_name' => 'system_cloud_sms_template',
				),
				'system_cloud_sms_share' => array(
					'title' => '分配短信',
					'url' => url('cloud/sms-share'),
					'icon' => 'wi wi-sms',
					'permission_name' => 'system_cloud_sms_share',
				),
				'system_sms_mass' => array(
					'title' => '短信群发',
					'url' => url('cloud/sms-mass'),
					'icon' => 'wi wi-sms-sign',
					'permission_name' => 'system_cloud_sms_mass',
					'is_display' => 0,
				),
			),
			'founder' => true,
		),
		

		
	),
);

$w7_system_menu['site'] = array(
	'title' => '站点设置',
	'icon' => 'wi wi-system-site',
	'dimension' => 3,
	'url' => url('cloud/upgrade'),
	'section' => array(
		'cloud' => array(
			'title' => '云服务',
			'menu' => array(
				'system_profile' => array(
					'title' => '系统升级',
					'url' => url('cloud/upgrade'),
					'icon' => 'wi wi-cache',
					'permission_name' => 'system_cloud_upgrade',
				),
				/* 'system_cloud_register' => array(
					'title' => '注册站点',
					'url' => url('cloud/profile'),
					'icon' => 'wi wi-registersite',
					'permission_name' => 'system_cloud_register',
				), */
				'system_cloud_diagnose' => array(
					'title' => '云服务诊断',
					'url' => url('cloud/diagnose'),
					'icon' => 'wi wi-diagnose',
					'permission_name' => 'system_cloud_diagnose',
				),
			),
		),
		'setting' => array(
			'title' => '设置',
			'menu' => array(
				'system_setting_site' => array(
					'title' => '站点设置',
					'url' => url('system/site'),
					'icon' => 'wi wi-site-setting',
					'permission_name' => 'system_setting_site',
				),
				'system_setting_menu' => array(
					'title' => '菜单设置',
					'url' => url('system/menu'),
					'icon' => 'wi wi-menu-setting',
					'permission_name' => 'system_setting_menu',
				),
				'system_setting_attachment' => array(
					'title' => '附件设置',
					'url' => url('system/attachment'),
					'icon' => 'wi wi-attachment',
					'permission_name' => 'system_setting_attachment',
				),
				'system_setting_systeminfo' => array(
					'title' => '系统信息',
					'url' => url('system/systeminfo'),
					'icon' => 'wi wi-system-info',
					'permission_name' => 'system_setting_systeminfo',
				),
				'system_setting_logs' => array(
					'title' => '查看日志',
					'url' => url('system/logs'),
					'icon' => 'wi wi-log',
					'permission_name' => 'system_setting_logs',
				),
				'system_setting_ipwhitelist' => array(
					'title' => 'IP白名单',
					'url' => url('system/ipwhitelist'),
					'icon' => 'wi wi-ip',
					'permission_name' => 'system_setting_ipwhitelist',
				),
				'system_setting_sensitiveword' => array(
					'title' => '过滤敏感词',
					'url' => url('system/sensitiveword'),
					'icon' => 'wi wi-sensitive',
					'permission_name' => 'system_setting_sensitiveword',
				),
				'system_setting_thirdlogin' => array(
					'title' => '用户登录/注册设置',
					'url' => url('user/registerset'),
					'icon' => 'wi wi-user',
					'permission_name' => 'system_setting_thirdlogin',
				),
				'system_setting_oauth' => array(
					'title' => '全局借用权限',
					'url' => url('system/oauth'),
					'icon' => 'wi wi-oauth',
					'permission_name' => 'system_setting_oauth',
				),
			),
		),
		'utility' => array(
			'title' => '常用工具',
			'menu' => array(
				'system_utility_filecheck' => array(
					'title' => '系统文件校验',
					'url' => url('system/filecheck'),
					'icon' => 'wi wi-file',
					'permission_name' => 'system_utility_filecheck',
				),
				'system_utility_optimize' => array(
					'title' => '性能优化',
					'url' => url('system/optimize'),
					'icon' => 'wi wi-optimize',
					'permission_name' => 'system_utility_optimize',
				),
				'system_utility_database' => array(
					'title' => '数据库',
					'url' => url('system/database'),
					'icon' => 'wi wi-sql',
					'permission_name' => 'system_utility_database',
				),
				'system_utility_scan' => array(
					'title' => '木马查杀',
					'url' => url('system/scan'),
					'icon' => 'wi wi-safety',
					'permission_name' => 'system_utility_scan',
				),
				'system_utility_bom' => array(
					'title' => '检测文件BOM',
					'url' => url('system/bom'),
					'icon' => 'wi wi-bom',
					'permission_name' => 'system_utility_bom',
				),
				'system_utility_check' => array(
					'title' => '系统常规检测',
					'url' => url('system/check'),
					'icon' => 'wi wi-bom',
					'permission_name' => 'system_utility_check',
				),
			),
		),
		'backjob' => array(
			'title' => '后台任务',
			'menu' => array(
				'system_job' => array(
					'title' => '后台任务',
					'url' => url('system/job/display'),
					'icon' => 'wi wi-job',
					'permission_name' => 'system_job',
				),
			),
		),
	),
	'founder' => true,
);

$w7_system_menu['myself'] = array(
	'title' => '我的账户',
	'icon' => 'wi wi-bell',
	'dimension' => 2,
	'url' => url('user/profile'),
	'section' => array(),
);

$w7_system_menu['message'] = array(
	'title' => '消息管理',
	'icon' => 'wi wi-xiaoxi',
	'dimension' => 2,
	'url' => url('message/notice'),
	'section' => array(
		'message' => array(
			'title' => '消息管理',
			'menu' => array(
				'message_notice' => array(
					'title' => '消息提醒',
					'url' => url('message/notice'),
					'permission_name' => 'message_notice',
				),
				'message_setting' => array(
					'title' => '消息设置',
					'url' => url('message/notice/setting'),
					'permission_name' => 'message_setting',
				),
				'message_wechat_setting' => array(
					'title' => '微信提醒设置',
					'url' => url('message/notice/wechat_setting'),
					'permission_name' => 'message_wechat_setting',
					'founder' => true,
				),
			),
		),
	),
);

$w7_system_menu['account'] = array(
	'title' => '公众号',
	'icon' => 'wi wi-white-collar',
	'dimension' => 3,
	'url' => url('home/welcome/platform'),
	'section' => array(
		'platform' => array(
			'title' => '增强功能',
			'menu' => array(
				'platform_reply' => array(
					'title' => '自动回复',
					'url' => url('platform/reply'),
					'icon' => 'wi wi-reply',
					'permission_name' => 'platform_reply',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_reply_keyword' => array(
							'title' => '关键字自动回复',
							'url' => url('platform/reply', array('module_name' => 'keyword')),
							'permission_name' => 'platform_reply_keyword',
							'active' => 'keyword',
						),
						'platform_reply_special' => array(
							'title' => '非关键字自动回复',
							'url' => url('platform/reply', array('module_name' => 'special')),
							'permission_name' => 'platform_reply_special',
							'active' => 'special',
						),
						'platform_reply_welcome' => array(
							'title' => '首次访问自动回复',
							'url' => url('platform/reply', array('module_name' => 'welcome')),
							'permission_name' => 'platform_reply_welcome',
							'active' => 'welcome',
						),
						'platform_reply_default' => array(
							'title' => '默认回复',
							'url' => url('platform/reply', array('module_name' => 'default')),
							'permission_name' => 'platform_reply_default',
							'active' => 'default',
						),
						'platform_reply_service' => array(
							'title' => '常用服务',
							'url' => url('platform/reply', array('module_name' => 'service')),
							'permission_name' => 'platform_reply_service',
							'active' => 'service',
						),
						'platform_reply_userapi' => array(
							'title' => '自定义接口回复',
							'url' => url('platform/reply', array('module_name' => 'userapi')),
							'permission_name' => 'platform_reply_userapi',
							'active' => 'userapi',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'platform_reply_setting' => array(
							'title' => '回复设置',
							'url' => url('profile/reply-setting'),
							'permission_name' => 'platform_reply_setting',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
					),
				),
				'platform_menu' => array(
					'title' => '自定义菜单',
					'url' => url('platform/menu/post'),
					'icon' => 'wi wi-custommenu',
					'permission_name' => 'platform_menu',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_menu_default' => array(
							'title' => '默认菜单',
							'url' => url('platform/menu/post'),
							'permission_name' => 'platform_menu_default',
							'active' => 'post',
						),
						'platform_menu_conditional' => array(
							'title' => '个性化菜单',
							'url' => url('platform/menu/display', array('type' => MENU_CONDITIONAL)),
							'permission_name' => 'platform_menu_conditional',
							'active' => 'display',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
					),
				),
				'platform_qr' => array(
					'title' => '二维码',
					'url' => url('platform/qr'),
					'icon' => 'wi wi-qrcode',
					'permission_name' => 'platform_qr',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_qr_qr' => array(
							'title' => '二维码',
							'url' => url('platform/qr/list'),
							'permission_name' => 'platform_qr_qr',
							'active' => 'list',
						),
						'platform_qr_statistics' => array(
							'title' => '二维码扫描统计',
							'url' => url('platform/qr/display'),
							'permission_name' => 'platform_qr_statistics',
							'active' => 'display',
						),
					),
				),
				'platform_masstask' => array(
					'title' => '群发',
					'url' => url('platform/mass'),
					'icon' => 'wi wi-crontab',
					'permission_name' => 'platform_masstask',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_masstask_post' => array(
							'title' => '群发',
							'url' => url('platform/mass/post'),
							'permission_name' => 'platform_masstask_post',
							'active' => 'post',
						),
						'platform_masstask_send' => array(
							'title' => '群发记录',
							'url' => url('platform/mass/send'),
							'permission_name' => 'platform_masstask_send',
							'active' => 'send',
						),
					),
				),
				'platform_material' => array(
					'title' => '素材/编辑器',
					'url' => url('platform/material'),
					'icon' => 'wi wi-redact',
					'permission_name' => 'platform_material',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_material_news' => array(
							'title' => '图文',
							'url' => url('platform/material', array('type' => 'news')),
							'permission_name' => 'platform_material_news',
							'active' => 'news',
						),
						'platform_material_image' => array(
							'title' => '图片',
							'url' => url('platform/material', array('type' => 'image')),
							'permission_name' => 'platform_material_image',
							'active' => 'image',
						),
						'platform_material_voice' => array(
							'title' => '语音',
							'url' => url('platform/material', array('type' => 'voice')),
							'permission_name' => 'platform_material_voice',
							'active' => 'voice',
						),
						'platform_material_video' => array(
							'title' => '视频',
							'url' => url('platform/material', array('type' => 'video')),
							'permission_name' => 'platform_material_video',
							'active' => 'video',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'platform_material_delete' => array(
							'title' => '删除',
							'permission_name' => 'platform_material_delete',
							'is_display' => 0,
						),
					),
				),
				'platform_site' => array(
					'title' => '微官网-文章',
					'url' => url('site/multi'),
					'icon' => 'wi wi-home',
					'permission_name' => 'platform_site',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'platform_site_multi' => array(
							'title' => '微官网',
							'url' => url('site/multi/display'),
							'permission_name' => 'platform_site_multi',
							'active' => 'multi',
						),
						'platform_site_style' => array(
							'title' => '微官网模板',
							'url' => url('site/style/template'),
							'permission_name' => 'platform_site_style',
							'active' => 'style',
						),
						'platform_site_article' => array(
							'title' => '文章管理',
							'url' => url('site/article/display'),
							'permission_name' => 'platform_site_article',
							'active' => 'article',
						),
						'platform_site_category' => array(
							'title' => '文章分类管理',
							'url' => url('site/category/display'),
							'permission_name' => 'platform_site_category',
							'active' => 'category',
						),
					),
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_OFFCIAL_NORMAL,
				ACCOUNT_TYPE_OFFCIAL_AUTH,
			),
		),
		'platform_module' => array(
			'title' => '应用模块',
			'menu' => array(),
			'is_display' => 1,
		),
		'mc' => array(
			'title' => '粉丝',
			'menu' => array(
				'mc_fans' => array(
					'title' => '粉丝管理',
					'url' => url('mc/fans'),
					'icon' => 'wi wi-fansmanage',
					'permission_name' => 'mc_fans',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
					'sub_permission' => array(
						'mc_fans_display' => array(
							'title' => '全部粉丝',
							'url' => url('mc/fans/display'),
							'permission_name' => 'mc_fans_display',
							'active' => 'display',
						),
						'mc_fans_fans_sync_set' => array(
							'title' => '粉丝同步设置',
							'url' => url('mc/fans/fans_sync_set'),
							'permission_name' => 'mc_fans_fans_sync_set',
							'active' => 'fans_sync_set',
						),
					),
				),
				'mc_member' => array(
					'title' => '会员管理',
					'url' => url('mc/member'),
					'icon' => 'wi wi-fans',
					'permission_name' => 'mc_member',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
						ACCOUNT_TYPE_WEBAPP_NORMAL,
					),
					'sub_permission' => array(
						'mc_member_diaplsy' => array(
							'title' => '会员管理',
							'url' => url('mc/member/display'),
							'permission_name' => 'mc_member_diaplsy',
							'active' => 'display',
						),
						'mc_member_group' => array(
							'title' => '会员组',
							'url' => url('mc/group/display'),
							'permission_name' => 'mc_member_group',
							'active' => 'display',
						),
						'mc_member_uc' => array(
							'title' => '会员中心',
							'url' => url('site/editor/uc'),
							'permission_name' => 'mc_member_uc',
							'active' => 'uc',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'mc_member_quickmenu' => array(
							'title' => '快捷菜单',
							'url' => url('site/editor/quickmenu'),
							'permission_name' => 'mc_member_quickmenu',
							'active' => 'quickmenu',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'mc_member_register_seting' => array(
							'title' => '注册设置',
							'url' => url('mc/member/register_setting'),
							'permission_name' => 'mc_member_register_seting',
							'active' => 'register_setting',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'mc_member_credit_setting' => array(
							'title' => '积分设置',
							'url' => url('mc/member/credit_setting'),
							'permission_name' => 'mc_member_credit_setting',
							'active' => 'credit_setting',
						),
						'mc_member_fields' => array(
							'title' => '会员字段管理',
							'url' => url('mc/fields/list'),
							'permission_name' => 'mc_member_fields',
							'active' => 'list',
						),
					),
				),
				'mc_message' => array(
					'title' => '留言管理',
					'url' => url('mc/message'),
					'icon' => 'wi wi-message',
					'permission_name' => 'mc_message',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_OFFCIAL_NORMAL,
				ACCOUNT_TYPE_OFFCIAL_AUTH,
				ACCOUNT_TYPE_WEBAPP_NORMAL,
			),
		),
		'profile' => array(
			'title' => '配置',
			'menu' => array(
				'profile_setting' => array(
					'title' => '参数配置',
					'url' => url('profile/remote'),
					'icon' => 'wi wi-parameter-setting',
					'permission_name' => 'profile_setting',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
						ACCOUNT_TYPE_WEBAPP_NORMAL,
					),
					'sub_permission' => array(
						'profile_setting_remote' => array(
							'title' => '远程附件',
							'url' => url('profile/remote/display'),
							'permission_name' => 'profile_setting_remote',
							'active' => 'display',
						),
						'profile_setting_passport' => array(
							'title' => '借用权限',
							'url' => url('profile/passport/oauth'),
							'permission_name' => 'profile_setting_passport',
							'active' => 'oauth',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'profile_setting_tplnotice' => array(
							'title' => '微信通知设置',
							'url' => url('profile/tplnotice/list'),
							'permission_name' => 'profile_setting_tplnotice',
							'active' => 'list',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'profile_setting_notify' => array(
							'title' => '邮件通知参数',
							'url' => url('profile/notify/mail'),
							'permission_name' => 'profile_setting_notify',
							'active' => 'mail',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
						'profile_setting_upload_file' => array(
							'title' => '上传JS接口文件',
							'url' => url('profile/common/upload_file'),
							'permission_name' => 'profile_setting_upload_file',
							'active' => 'upload_file',
							'is_display' => array(
								ACCOUNT_TYPE_OFFCIAL_NORMAL,
								ACCOUNT_TYPE_OFFCIAL_AUTH,
							),
						),
					),
				),
				'profile_payment' => array(
					'title' => '支付参数',
					'url' => url('profile/payment'),
					'icon' => 'wi wi-pay-setting',
					'permission_name' => 'profile_payment',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
						ACCOUNT_TYPE_WEBAPP_NORMAL,
					),
					'sub_permission' => array(
						'profile_payment_pay' => array(
							'title' => '支付配置',
							'url' => url('profile/payment'),
							'permission_name' => 'profile_payment_pay',
							'active' => 'payment',
						),
						'profile_payment_refund' => array(
							'title' => '退款配置',
							'url' => url('profile/refund/display'),
							'permission_name' => 'profile_payment_refund',
							'active' => 'refund',
						),
					),
				),
				'profile_app_module_link' => array(
					'title' => '数据同步',
					'url' => url('profile/module-link-uniacid'),
					'is_display' => 1,
					'icon' => 'wi wi-data-synchro',
					'permission_name' => 'profile_app_module_link_uniacid',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
				),
				
				'webapp_module_link' => array(
					'title' => '数据同步',
					'url' => url('profile/module-link-uniacid'),
					'is_display' => 1,
					'icon' => 'wi wi-data-synchro',
					'permission_name' => 'webapp_module_link',
					'is_display' => array(
						ACCOUNT_TYPE_WEBAPP_NORMAL,
					),
				),
				'webapp_rewrite' => array(
					'title' => '伪静态',
					'url' => url('webapp/rewrite'),
					'icon' => 'wi wi-rewrite',
					'permission_name' => 'webapp_rewrite',
					'is_display' => array(
						ACCOUNT_TYPE_WEBAPP_NORMAL,
					),
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_OFFCIAL_NORMAL,
				ACCOUNT_TYPE_OFFCIAL_AUTH,
				ACCOUNT_TYPE_WEBAPP_NORMAL,
			),
		),
		'publish' => array(
			'title' => '发布',
			'menu' => array(
				'publish_setting' => array(
					'title' => '发布设置',
					'url' => url('profile/publish'),
					'icon' => 'wi wi-send',
					'permission_name' => 'publish_setting',
					'is_display' => array(
						ACCOUNT_TYPE_OFFCIAL_NORMAL,
						ACCOUNT_TYPE_OFFCIAL_AUTH,
					),
				),
				
			),
			'permission_display' => array(
				ACCOUNT_TYPE_OFFCIAL_NORMAL,
				ACCOUNT_TYPE_OFFCIAL_AUTH,
			)
		),
		
	),
);

$w7_system_menu['wxapp'] = array(
	'title' => '微信小程序',
	'icon' => 'wi wi-small-routine',
	'dimension' => 3,
	'url' => url('wxapp/display/home'),
	'section' => array(
		'wxapp_entrance' => array(
			'title' => '小程序入口',
			'menu' => array(
				'module_entrance_link' => array(
					'title' => '入口页面',
					'url' => url('wxapp/entrance-link'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
					),
					'icon' => 'wi wi-data-synchro',
					'permission_name' => 'wxapp_entrance_link',
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_APP_NORMAL,
				ACCOUNT_TYPE_APP_AUTH,
				ACCOUNT_TYPE_WXAPP_WORK,
			),
		),
		'platform_module' => array(
			'title' => '应用',
			'menu' => array(),
			'is_display' => 1,
		),
		'mc' => array(
			'title' => '粉丝',
			'menu' => array(
				'mc_member' => array(
					'title' => '会员',
					'url' => url('mc/member'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
					),
					'icon' => 'wi wi-fans',
					'permission_name' => 'mc_wxapp_member',
					'sub_permission' => array(
						'mc_member_diaplsy' => array(
							'title' => '会员管理',
							'url' => url('mc/member/display'),
							'permission_name' => 'mc_member_diaplsy',
							'active' => 'display',
						),
						'mc_member_group' => array(
							'title' => '会员组',
							'url' => url('mc/group/display'),
							'permission_name' => 'mc_member_group',
							'active' => 'display',
						),
						'mc_member_credit_setting' => array(
							'title' => '积分设置',
							'url' => url('mc/member/credit_setting'),
							'permission_name' => 'mc_member_credit_setting',
							'active' => 'credit_setting',
						),
						'mc_member_fields' => array(
							'title' => '会员字段管理',
							'url' => url('mc/fields/list'),
							'permission_name' => 'mc_member_fields',
							'active' => 'list',
						),
					),
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_APP_NORMAL,
				ACCOUNT_TYPE_APP_AUTH,
				ACCOUNT_TYPE_WXAPP_WORK,
			),
		),
		'wxapp_profile' => array(
			'title' => '配置',
			'menu' => array(
				'wxapp_profile_module_link_uniacid' => array(
					'title' => '数据同步',
					'url' => url('wxapp/module-link-uniacid'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
						ACCOUNT_TYPE_PHONEAPP_NORMAL,
						ACCOUNT_TYPE_ALIAPP_NORMAL,
						ACCOUNT_TYPE_BAIDUAPP_NORMAL,
						ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
					),
					'icon' => 'wi wi-data-synchro',
					'permission_name' => 'wxapp_profile_module_link_uniacid',
				),
				'wxapp_profile_payment' => array(
					'title' => '支付参数',
					'url' => url('wxapp/payment'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
					),
					'icon' => 'wi wi-appsetting',
					'permission_name' => 'wxapp_profile_payment',
					'sub_permission' => array(
						'wxapp_payment_pay' => array(
							'title' => '支付参数',
							'url' => url('wxapp/payment/display'),
							'permission_name' => 'wxapp_payment_pay',
							'active' => 'payment',
						),
						'wxapp_payment_refund' => array(
							'title' => '退款配置',
							'url' => url('wxapp/refund/display'),
							'permission_name' => 'wxapp_payment_refund',
							'active' => 'refund',
						),
					),
				),
				'wxapp_profile_domainset' => array(
					'title' => '域名设置',
					'url' => url('wxapp/domainset'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
					),
					'icon' => 'wi wi-examine',
					'permission_name' => 'wxapp_profile_domainset',
				),
				'profile_setting_remote' => array(
					'title' => '参数配置',
					'url' => url('profile/remote'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
						ACCOUNT_TYPE_PHONEAPP_NORMAL,
						ACCOUNT_TYPE_ALIAPP_NORMAL,
						ACCOUNT_TYPE_BAIDUAPP_NORMAL,
						ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
					),
					'icon' => 'wi wi-parameter-setting',
					'permission_name' => 'profile_setting_remote',
				),
				'wxapp_profile_platform_material' => array(
					'title' => '素材管理',
					'is_display' => 0,
					'permission_name' => 'wxapp_profile_platform_material',
					'sub_permission' => array(
						array(
							'title' => '删除',
							'permission_name' => 'wxapp_profile_platform_material_delete',
						),
					),
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_APP_NORMAL,
				ACCOUNT_TYPE_APP_AUTH,
				ACCOUNT_TYPE_WXAPP_WORK,
				ACCOUNT_TYPE_PHONEAPP_NORMAL,
				ACCOUNT_TYPE_ALIAPP_NORMAL,
				ACCOUNT_TYPE_BAIDUAPP_NORMAL,
				ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
			),
		),
		'publish' => array(
			'title' => '发布',
			'menu' => array(
				'publish_front_download' => array(
					'title' => '发布设置',
					'url' => $_W['account']['type_sign'] == 'phoneapp' ? url('phoneapp/front-download') : url('wxapp/front-download'),
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
						ACCOUNT_TYPE_PHONEAPP_NORMAL,
						ACCOUNT_TYPE_ALIAPP_NORMAL,
						ACCOUNT_TYPE_BAIDUAPP_NORMAL,
						ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
					),
					'icon' => 'wi wi-examine',
					'permission_name' => 'publish_front_download',
				),
			),
			'permission_display' => array(
				ACCOUNT_TYPE_APP_NORMAL,
				ACCOUNT_TYPE_APP_AUTH,
				ACCOUNT_TYPE_WXAPP_WORK,
				ACCOUNT_TYPE_PHONEAPP_NORMAL,
				ACCOUNT_TYPE_ALIAPP_NORMAL,
				ACCOUNT_TYPE_BAIDUAPP_NORMAL,
				ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
			)
		),
		'statistics' => array(
			'title' => '统计',
			'menu' => array(
				
				'statistics_fans' => array(
					'title' => '用户统计',
					'url' => url('wxapp/statistics'),
					'icon' => 'wi wi-statistical',
					'permission_name' => 'statistics_fans_wxapp',
					'is_display' => array(
						ACCOUNT_TYPE_APP_NORMAL,
						ACCOUNT_TYPE_APP_AUTH,
						ACCOUNT_TYPE_WXAPP_WORK,
					),
				),

			),
			'permission_display' => array(
				ACCOUNT_TYPE_APP_NORMAL,
				ACCOUNT_TYPE_APP_AUTH,
				ACCOUNT_TYPE_WXAPP_WORK,
				ACCOUNT_TYPE_PHONEAPP_NORMAL,
				ACCOUNT_TYPE_ALIAPP_NORMAL,
				ACCOUNT_TYPE_BAIDUAPP_NORMAL,
				ACCOUNT_TYPE_TOUTIAOAPP_NORMAL,
			),
		),
	),
);

$w7_system_menu['webapp'] = array(
	'title' => 'PC',
	'icon' => 'wi wi-pc',
	'url' => url('webapp/home/display'),
	'section' => array(),
);

$w7_system_menu['phoneapp'] = array(
	'title' => 'APP',
	'icon' => 'wi wi-white-collar',
	'url' => url('phoneapp/display/home'),
	'section' => array(
		'platform_module' => array(
			'title' => '应用',
			'menu' => array(),
			'is_display' => 1,
		),
		'phoneapp_profile' => array(
			'title' => '配置',
			'menu' => array(
				'profile_phoneapp_module_link' => array(
					'title' => '数据同步',
					'url' => url('wxapp/module-link-uniacid'),
					'is_display' => array(
						ACCOUNT_TYPE_PHONEAPP_NORMAL,
					),
					'icon' => 'wi wi-data-synchro',
					'permission_name' => 'profile_phoneapp_module_link',
				),
				'front_download' => array(
					'title' => '下载APP',
					'url' => url('phoneapp/front-download'),
					'is_display' => 1,
					'icon' => 'wi wi-examine',
					'permission_name' => 'phoneapp_front_download',
				),
			),
			'is_display' => 1,
			'permission_display' => array(
				ACCOUNT_TYPE_PHONEAPP_NORMAL,
			),
		),
	),
);

$w7_system_menu['aliapp'] = array(
	'title' => '支付宝小程序',
	'icon' => 'wi wi-aliapp',
	'url' => url('miniapp/display/home'),
	'section' => array(
		'platform_module' => array(
			'title' => '应用',
			'menu' => array(),
			'is_display' => 1,
		),
	),
);

$w7_system_menu['baiduapp'] = array(
	'title' => '百度小程序',
	'icon' => 'wi wi-baiduapp',
	'url' => url('miniapp/display/home'),
	'section' => array(
		'platform_module' => array(
			'title' => '应用',
			'menu' => array(),
			'is_display' => 1,
		),
	),
);

$w7_system_menu['toutiaoapp'] = array(
	'title' => '字节跳动小程序',
	'icon' => 'wi wi-toutiaoapp',
	'url' => url('miniapp/display/home'),
	'section' => array(
		'platform_module' => array(
			'title' => '应用',
			'menu' => array(),
			'is_display' => 1,
		),
	),
);



/* $w7_system_menu['appmarket'] = array(
	'title' => '市场',
	'icon' => 'wi wi-shichang',
	'url' => 'http://s.w7.cc',
	'section' => array(),
	'blank' => true,
	'founder' => true,
);

$w7_system_menu['workorder'] = array(
	'title' => '工单',
	'icon' => 'wi wi-gongdan',
	'url' => 'http://wo.w7.cc/#/work?site_id='.$_W['setting']['site']['key'],
	'section' => array(),
	'founder' => true,
);
 */
return $w7_system_menu;
