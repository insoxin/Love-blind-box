<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
<?php  if(user_is_vice_founder($_W['uid']) && $_W['iscontroller']) { ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-vice', TEMPLATE_INCLUDEPATH)) : (include template('common/header-vice', TEMPLATE_INCLUDEPATH));?>
<?php  } else { ?>
<div class="skin-2 <?php  if(!$_W['iscontroller']) { ?>skin-2--full<?php  } ?>" data-skin="2">
	<?php  if($_GPC['c'] == 'home' && in_array($_GPC['do'], array('system_home', 'system'))) { ?>
	<div class="skin-2__left js-home-menu">
	<?php  } else { ?>
	<div class="skin-2__left <?php  if(!$_GPC['jsMenuLock']) { ?>skin-2__left--small<?php  } ?>">
	<?php  } ?>
		<!-- logo -->
		<a class="skin-2__logo" href="<?php  if($_W['iscontroller']) { ?><?php  echo url('home/welcome/system', array('page' => 'home'))?><?php  } else { ?><?php  echo $_W['siteroot'] . 'web/home.php'?><?php  } ?>">
			<img src="<?php  if(!empty($_W['setting']['copyright']['blogo'])) { ?><?php  echo to_global_media($_W['setting']['copyright']['blogo'])?><?php  } else { ?>./resource/images/logo/logo.png<?php  } ?>" class="logo" style="max-height: 40px;max-width: 100px;">
		</a>
		<!-- end logo-->
		<!-- 一级菜单 -->
		<ul class="main-nav">
			<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-topnav', TEMPLATE_INCLUDEPATH)) : (include template('common/header-topnav', TEMPLATE_INCLUDEPATH));?>
		</ul>
		<!-- end一级菜单 -->
		<a href="javascript:;" class="js-lock-menu skin-2__lock <?php  if($_GPC['jsMenuLock']) { ?>lock<?php  } ?>">
			<div class="unlock" data-toggle="tooltip" data-placement="right" data-container="body" title="菜单锁定">
				<i class="wi wi-appjurisdiction"　></i> 
			</div>
			<div class="locked" data-toggle="tooltip" data-placement="top" data-container="body" title="点击解锁">
				菜单已锁定
			</div>
		</a>
	</div>
	<div class="skin-2__right">
		<div class="skin-2__header">
			<div class="shortcut pull-left dropdown ">
				<div class="shortcut-header" data-target="#" data-toggle="dropdown" ><i class="wi wi-menu-setting"></i>菜单</div>
				<ul class="shortcut-list ">
					<li class="shortcut-item">
						<?php  if(is_array($top_nav_shortcut)) { foreach($top_nav_shortcut as $nav) { ?>
						<div class="parent">
							<a href="<?php  if(empty($nav['url'])) { ?><?php  echo url('home/welcome/' . $nav['name']);?><?php  } else { ?><?php  echo $nav['url'];?><?php  } ?>" <?php  if(!empty($nav['blank'])) { ?>target="_blank"<?php  } ?>>
								<i class="<?php  echo $nav['icon'];?> icon"></i><?php  echo $nav['title'];?>
							</a>
						</div>
						<?php  } } ?>
					</li>
					<?php  $shortcut_menu = system_shortcut_menu()?>
					<?php  if(is_array($shortcut_menu)) { foreach($shortcut_menu as $menu) { ?>
					<?php  if(!empty($menu['section'])) { ?>
					<li class="shortcut-item">
						<div class="parent">
							<a href="<?php  echo $menu['url'];?>">
								<i class="<?php  echo $menu['icon'];?> icon"></i><?php  echo $menu['title'];?>
							</a>
						</div>
						<div class="child">
							<?php  if(is_array($menu['section'])) { foreach($menu['section'] as $section) { ?>
								<?php  if(!isset($section['is_display']) || !empty($section['is_display'])) { ?>
									<?php  if(is_array($section['menu'])) { foreach($section['menu'] as $nav) { ?>
										<?php  if(!empty($nav['is_display'])) { ?>
										<div class="item text-over">
											<a href="<?php  echo $nav['url'];?>">
												<i class="<?php  echo $nav['icon'];?> icon"></i><?php  echo $nav['title'];?>
											</a>
										</div>
										<?php  } ?>
									<?php  } } ?>
								<?php  } ?>
							<?php  } } ?>
						</div>
					</li>
					<?php  } ?>
					<?php  } } ?>
				</ul>
				<div class=""></div>
			</div>
			<?php  if(!$_W['iscontroller']) { ?>
			<div class="header-info-common pull-left">
				<a class="header-info-common__logo" href="<?php  echo $_W['siteroot'] . 'web/home.php'?>">
					<img src="<?php  if(!empty($_W['setting']['copyright']['blogo'])) { ?><?php  echo to_global_media($_W['setting']['copyright']['blogo'])?><?php  } else { ?>./resource/images/logo/logo.png<?php  } ?>" class="logo" style="max-height: 40px;max-width: 100px;">
				</a>
				<?php  if($_W['breadcrumb']) { ?>
				<div class="header-info-common__breadcrumb">
					<a href="<?php  echo $_W['siteroot'];?>web/home.php" class="home">
						<i class="wi wi-home"></i>
					</a>
					<span class="separator"> <i class="wi wi-angle-right"></i> </span>
					<div class="item"><?php  echo $_W['breadcrumb'];?></div>
				</div>
				<?php  } ?>
			</div>
			<?php  } ?>
			<?php  if(!empty($_W['uid'])) { ?>
			<ul class="user-info">
				<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-user', TEMPLATE_INCLUDEPATH)) : (include template('common/header-user', TEMPLATE_INCLUDEPATH));?>
			</ul>
			<?php  } else { ?>
			<ul class="user-info">
				<li class="dropdown"><a href="<?php  echo url('user/register');?>">注册</a></li>
				<li class="dropdown"><a href="<?php  echo url('user/login');?>">登录</a></li>
			</ul>
			<?php  } ?>
		</div>
		<div class="skin-2__content main">
			<?php  if(!defined('IN_MESSAGE')) { ?>
				<?php  if($frames['dimension'] == 3 && in_array(FRAME, array('account', 'system', 'advertisement', 'wxapp', 'site', 'webapp', 'phoneapp')) && !in_array($_GPC['a'], array('news-show', 'notice-show', 'notice-news'))) { ?>
				<div class="skin-2__sub">
					<div class="sub-top">
						<!-- 模块信息 -->
						<?php  if(!empty($_GPC['module_name']) && !in_array($_GPC['module_name'], array('keyword', 'special', 'welcome', 'default', 'userapi', 'service', 'apply')) || defined('IN_MODULE') && IN_MODULE != '') { ?>
						<div class="apply-fixed-top">
							<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-module', TEMPLATE_INCLUDEPATH)) : (include template('common/header-module', TEMPLATE_INCLUDEPATH));?>
						</div>
						<!-- 模块信息 -->
						<?php  } else { ?>
						<div class="left-menu-top-panel">
							<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-' . FRAME, TEMPLATE_INCLUDEPATH)) : (include template('common/header-' . FRAME, TEMPLATE_INCLUDEPATH));?>
						</div>
						<?php  } ?>
					</div>
					
					<!-- 二级菜单-->
					<div class="js-menu" id="js-menu-<?php echo FRAME;?><?php  echo $_W['account']['uniacid'];?>">
					<?php  if(is_array($frames['section'])) { foreach($frames['section'] as $frame_section_id => $frame_section) { ?>
						
						<?php  if(!isset($frame_section['is_display']) || !empty($frame_section['is_display'])) { ?>
						<div class="panel panel-menu">
							<?php  if($frame_section['title']) { ?>
							<div class="panel-heading">
								<span class="<?php  if($_GPC['menu_fold_tag:'.$frame_section_id] == 1) { ?>collapsed<?php  } ?>" data-toggle="collapse" data-target="#frame-<?php  echo $frame_section_id;?>" onclick="util.cookie.set('menu_fold_tag:<?php  echo $frame_section_id;?>', util.cookie.get('menu_fold_tag:<?php  echo $frame_section_id;?>') == 1 ? 0 : 1)">
									<span class="nav-title"><?php  echo $frame_section['title'];?><i class="wi wi-angle-down pull-right setting"></i></span>
								</span>
							</div>
							<?php  } ?>
							<ul class="list-group collapse <?php  if($_GPC['menu_fold_tag:'.$frame_section_id] == 0) { ?>in<?php  } ?>" id="frame-<?php  echo $frame_section_id;?>" >
								<?php  if(is_array($frame_section['menu'])) { foreach($frame_section['menu'] as $menu_id => $menu) { ?>
									<?php  if(!empty($menu['is_display'])) { ?>
										<?php  if($menu_id == 'platform_module_plugin_more' || $menu_id == 'platform_module_more') { ?>
										<li class="list-group-item">
											<a href="<?php  echo $menu['url']?>" class="text-over">
												<span class="nav-icon" ><span class="icon-more"><i class="wi wi-plus"></i></span></span>
												<span class="nav-title"><?php  echo $menu['title'];?></span>
											</a>
										</li>
										<?php  } else { ?>
										<?php  if($menu['active']) { ?><?php  $active_sub_permission = $menu['sub_permission'];?><?php  } ?>
										<li class="list-group-item list-group-item-plugin <?php  if($menu['multilevel']) { ?>list-group-item-more<?php  } ?> <?php  if($menu['active']) { ?>active<?php  } ?>">
											<?php  if($frame_section_id == 'platform_module_plugin' && !empty($_GPC['m']) && $_GPC['m'] != $menu['main_module']) { ?>
											<a class="back" href="<?php  echo url('module/welcome', array('m' => $menu['main_module'], 'uniacid' => $_W['uniacid']))?>" >
												<i class="wi wi-angle-left"></i>
											</a>
											<?php  } ?>
											<a href="<?php  if($menu['multilevel']) { ?>javascript:;<?php  } else { ?><?php  echo $menu['url'];?><?php  } ?>" <?php  if($menu['multilevel']) { ?>data-toggle="collapse" data-target="#frame-child-<?php  echo $frame_section_id;?>-<?php  echo $menu_id;?>"<?php  } ?> class="text-over" <?php  if($frame_section_id == 'platform_module') { ?>target="_blank"<?php  } ?>>
												<span class="nav-icon" >
													<?php  if($frame_section_id == 'platform_module' || $frame_section_id == 'platform_module_plugin') { ?>
													<img src="<?php  echo $menu['icon'];?>"/>
													<?php  } else { ?>
													<i class="<?php  echo $menu['icon'];?>"></i>
													<?php  } ?>
												</span>
												<span class="nav-title"><?php  echo $menu['title'];?><?php  if($menu['multilevel']) { ?><i class="wi wi-angle-down pull-right setting"></i><?php  } ?></span>
											</a>
											<?php  if($menu['multilevel'] && !empty($menu['childs'])) { ?>
											<ul class="list-child collapse in" id="frame-child-<?php  echo $frame_section_id;?>-<?php  echo $menu_id;?>">
												<?php  if(is_array($menu['childs'])) { foreach($menu['childs'] as $module_menu_child) { ?>
												<li class="list-child-item <?php  if($module_menu_child['active']) { ?>active<?php  } ?>">
													<a href="<?php  echo $module_menu_child['url'];?>" class="text-over">
														<span class="nav-title"><?php  echo $module_menu_child['title'];?></span>
													</a>
												</li>
												<?php  } } ?>
											</ul>
											<?php  } ?>
										</li>
										<?php  } ?>
									<?php  } ?>
								<?php  } } ?>
							</ul>
						</div>
						<?php  } ?>
					<?php  } } ?>
					</div>
					<!-- end二级菜单-->
				</div>
				<?php  } ?>
				<div class="skin-2__container container">
						<div class="content">
						<?php  if(!empty($_W['page']['title']) && $frames['title'] != '首页' && !empty($frames['title']) && !defined('IN_MODULE') && $_W['iscontroller']) { ?>
							<div class="we7-page-title"><?php  echo $_W['page']['title'];?></div>
						<?php  } ?>
						<?php  if($frames['dimension'] == 2) { ?>
							<!-- start消息管理菜单特殊,走自己的we7-page-tab,故加此if判断;平台/应用/我的账户无we7-page-table -->
							<?php  if(!in_array(FRAME, array('message', 'platform', 'module', 'myself', 'workorder'))) { ?>
							<ul class="we7-page-tab">
								<?php  $have_right_content_menu = 0;?>
								<?php  if(is_array($frames['section'][FRAME]['menu'])) { foreach($frames['section'][FRAME]['menu'] as $menu_id => $menu) { ?>
									<?php  if(in_array(FRAME, array('account_manage', 'permission'))) { ?>
									<?php  if(permission_check_account_user('see_' . $menu['permission_name'])) { ?>
									<li class="<?php  if($menu['active']) { ?>active<?php  } ?>"><a href="<?php  echo $menu['url'];?>"><?php  echo $menu['title'];?></a></li>
									<?php  } ?>
									<?php  } else { ?>
									<li class="<?php  if($menu['active']) { ?>active<?php  } ?>">
										<a href="<?php  echo $menu['url'];?>">
											<?php  echo $menu['title'];?>
											<!-- start应用管理中未安装应用数量 -->
											<?php  if(FRAME == 'module_manage' && $menu_id == 'module_manage_not_installed') { ?><span class="color-red"> (<?php  echo $module_uninstall_total;?>) </span><?php  } ?>
											<!-- end应用管理中未安装应用数量 -->
										</a>
									</li>
									<?php  } ?>
									<?php  if($menu['active']) { ?><?php  $have_right_content_menu = 1;?><?php  } ?>
								<?php  } } ?>
							</ul>
							<script>
								$(function(){
									<?php  if(empty($have_right_content_menu)) { ?>
										$('.we7-page-tab, .we7-page-title').addClass('hidden');
									<?php  } ?>
								});
							</script>
							<?php  } ?>
							<!-- end用户管理菜单和消息管理菜单特殊,走自己的we7-page-tab;平台/应用/我的账户无we7-page-table -->
						<?php  } ?>
			<?php  } ?>
<?php  } ?>