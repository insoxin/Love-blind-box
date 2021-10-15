<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--系统管理首页-->
<div class="welcome-container js-system-welcome" ng-controller="WelcomeCtrl" ng-cloak>
	<!--new start-->
	<div ng-if="ads" class="ad-img we7-margin-bottom " style="">
	
		<div id="welcome-ad" class="carousel slide" data-ride="carousel">
			<ol class="carousel-indicators">
				<li data-target="#welcome-ad" data-slide-to="{{key}}" ng-class="key ==0 ? 'active' : ''" ng-repeat="(key, ad) in ads"></li>
			</ol>
			<div class="carousel-inner" role="listbox">
				<!-- Wrapper for slides -->
				<div class="item advertising-box" ng-class="key ==0 ? 'active' : ''" ng-repeat="(key, ad) in ads">
				
				</div>
			</div>
		</div>
	</div>
    
	<div class="panle we7-panel">
		<div class="panel-heading">
			<h4>统计</h4>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#liuliang" aria-controls="liuliang" role="tab" data-toggle="tab">全站统计</a>
				</li>
				<li role="presentation">
					<a href="#fangwen" aria-controls="fangwen" role="tab" data-toggle="tab">后台统计</a>
				</li>
				<li role="presentation">
					<a href="#app_count" aria-controls="app_count" role="tab" data-toggle="tab">前台统计</a>
				</li>
				
			</ul>
		</div>
		<div clss="panel-body">
			<div class="tab-content">
				<div class="tab-pane fade in active" id="liuliang">
					<div class="chart-box">
						<div class="chart-select" ng-init="dayType='week'">
							<button class="btn btn-default daterange daterange-date" we7-date-range-picker ng-model="dateRange">
								<span> {{dateRange.startDate}} </span>至<span> {{dateRange.endDate}} </span> <i class="fa fa-calendar"></i>
							</button>
						</div>
						<div id="chart-line" class="chart-line"></div>
					</div>
				</div>

				<div class="tab-pane fade in active" id="fangwen">
					<div class="chart-box">
						<div class="chart-select">
							<button class="btn btn-default daterange daterange-date" we7-date-range-picker ng-model="dateRange">
								<span> {{dateRange.startDate}} </span>至<span> {{dateRange.endDate}} </span>
								<i class="fa fa-calendar"></i>
							</button>
						</div>
						<div id="chart-line-fangwen" class="chart-line"></div>
					</div>
				</div>

				<div class="tab-pane fade in active" id="app_count">
					<div class="chart-box">
						<div class="chart-select">
							<button class="btn btn-default daterange daterange-date" we7-date-range-picker ng-model="dateRange">
								<span> {{dateRange.startDate}} </span>至<span> {{dateRange.endDate}} </span>
								<i class="fa fa-calendar"></i>
							</button>
						</div>
						<div id="chart-line-app-count" class="chart-line"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- 可升级应用 start -->
	<div class="panel we7-panel" ng-if="upgrade_module_list_show">
		<div class="panel-heading">
			<h4>应用动态</h4>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active" ng-click="searchType('all')">
					<a href="#update-all" aria-controls="update-all" role="tab" data-toggle="tab">全部</a>
				</li>
				<li role="presentation" ng-click="searchType('has_new_version')">
					<a href="#update-version" aria-controls="update-version" role="tab" data-toggle="tab">版本更新 ({{ count_modules_has_new_version }})</a>
				</li>
				<li role="presentation" ng-click="searchType('has_new_branch')">
					<a href="#update-branch" aria-controls="profile" role="tab" data-toggle="tab">分支升级 ({{ count_modules_has_new_branch }})</a>
				</li>
				<li role="presentation" ng-click="searchType('unstall')">
					<a href="#update-branch" aria-controls="profile" role="tab" data-toggle="tab">未安装 ({{ count_modules_uninstall }})</a>
				</li>
			</ul>
			<a href="<?php  echo url('module/manage-system/installed')?>" class="more">查看更多</a>
		</div>
		<div class="panel-body">
			<div class="tab-content ">
				<div class="tab-pane active" id="update-all">
					<div class="update-module-statistics">
						<div ng-if="search_type_name != 'unstall' && (upgrade_modules | we7IsEmpty)" class="empty">恭喜您应用都为最新版本</div>
						<div ng-if="count_modules_uninstall == 0 && search_type_name == 'unstall'" class="empty">没有未安装应用</div>
						<div class="item" ng-repeat="module in upgrade_modules" ng-if="!module.is_ignore">
							<a class="item-box" href="javascript:void(0);" ng-click="moduleLink(module)">
								<div class="icon">
									<img ng-src="{{module.logo}}" alt="{{module.title}}">
								</div>
								<div class="info">
									<div class="title text-over">{{module.title|limitTo:4}}</div>
									<div class="new" ng-if="module.has_new_version == 1">新版本</div>
									<div class="new" ng-if="module.has_new_branch == 1">新分支</div>
									<div class="new" ng-if="module.unstall == 1">未安装</div>
								</div>
							</a>
							<a ng-show="module.unstall != 1" href="javascript:;" class="action-hide" data-toggle="tooltip" data-placement="bottom" title="忽略" ng-click="ignoreUpdate(module.name)"><i class="wi wi-hide"></i></a>
							<a ng-show="module.unstall == 1" href="javascript:;" class="action-hide" data-toggle="tooltip" data-placement="bottom" title="放入回收站" ng-click="removeUnstallModule(module)" ><i class="wi wi-delete"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 可升级应用 end -->

	<!-- 系统更新/数据库备份 start -->
	<div class="row">
		<div class="col-sm-6">
			<div class="panel we7-panel">
				<div class="panel-heading">
					<h4>系统更新</h4>
				</div>
				<div class="panel-body">
					<div class="update-statistics">
						<div class="icon">
							<img src="resource/images/welcome/update-icon.png" alt="">
						</div>
						<div class="content">
							<div ng-class="{1 : 'update-version new', 0 : 'update-version'}[upgrade_show]">
								<?php echo IMS_FAMILY;?><?php echo IMS_VERSION;?>
							</div>
							<div class="now-version" ng-if="upgrade_show">当前版本：<?php echo IMS_FAMILY;?><?php echo IMS_VERSION;?>（<?php echo IMS_RELEASE_DATE;?>）</div>
							<div class="now-version" ng-if="!upgrade_show">当前版本即最新版本，无须升级</div>
							<div class="">
								<a ng-if="upgrade_show" href="<?php  echo url('cloud/upgrade');?>" class="btn btn-primary">升级版本</a>
								<a ng-if="!upgrade_show" href="<?php  echo url('cloud/upgrade');?>" class="btn btn-default">检测新版本</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="panel we7-panel">
				<div class="panel-heading">
					<h4>数据库备份</h4>
				</div>
				<div class="panel-body">
					<div class="mysql-statistics">
						<div class="icon">
							<img src="resource/images/welcome/mysql-backup-icon.png" alt="">
						</div>
						<div class="content">
							<div class="no-backup">
								<span class="day"><?php  echo $backup_days;?></span>
								备份数据库,请养成备份数据的好习惯!
							</div>
							<a href="<?php  echo url('system/database');?>"  class="btn btn-primary">立即备份</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 系统更新/数据库备份 end -->

	<!-- 环境检测 start -->

	<?php  if(!empty($system_check) && $system_check['check_wrong_num'] > 0) { ?>
	<div class="panel we7-panel">
		<div class="panel-heading">
			<h4>环境检测</h4>
		</div>
		<div class="panel-body">
			<div class="environment-statistics">
				<div class="icon">
					<img src="resource/images/welcome/environment-icon.png" alt="">
				</div>
				<div class="error">
					<div class="name">系统环境检测发现异常</div>
					<div class="title text-over">
						<?php  if(is_array($system_check['check_items'])) { foreach($system_check['check_items'] as $check_item => $check_info) { ?>
						<?php  if(!$check_info['check_result']) { ?>
						<span> <i class="wi wi-info"></i><?php  echo $check_info['solution'];?></span>
						<?php  } ?>
						<?php  } } ?>
					</div>
				</div>
				<div class="action">
					<a href="<?php  echo url('system/check')?>" class="btn btn-default">去检测</a>
				</div>
			</div>
		</div>
	</div>
	<?php  } else { ?>
	<div class="panel we7-panel">
		<div class="panel-heading">
			<h4>环境检测</h4>
		</div>
		<div class="panel-body">
			<div class="environment-statistics">
				<div class="icon">
					<img src="resource/images/welcome/environment-icon.png" alt="">
				</div>
				<div class="content">
					<div class="name">环境检测</div>
					<div class="title">在使用过程中，如果出现未知错误，可以自行检测修复，避免花费过多等待时间</div>
				</div>
				<div class="action">
					<a href="<?php  echo url('system/check')?>" class="btn btn-default">去检测</a>
				</div>
			</div>
		</div>
	</div>
	<?php  } ?>

	<!-- 推荐应用 start -->
	<!-- <div class="panel we7-panel" ng-if="is_recommend_close != 1">
		<div class="panel-heading">
			<h4>推荐应用</h4>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" :ng-class="{active: key == 'recommend'}" ng-click="changeTab(key)" ng-repeat="(key, item) in recommend"><a href="{{'#' + key}}" role="tab" aria-controls="{{key}}" data-type="{{key}}" data-toggle="tab" >{{item.name}}</a></li>
			</ul>
			
			<a href="http://s.w7.cc/" target="_blank" class="more">去市场</a>
		</div>
		<div class="panel-body">
			<div class="tab-content">
				<div class="tab-pane " :ng-class="{active: key == 'recommend'}"  id="{{key}}" ng-repeat="(key, item) in recommend">
					<div class="app-statistics">
						<div class="left-box">
							<div id="{{key + '-app-carousel'}}" class="carousel slide recommend-app-carousel" data-ride="carousel">
								<ol class="carousel-indicators">
									<li data-target="{{'#' + key + '-app-carousel'}}" data-slide-to="{{index}}"  ng-class="{active: index == 0}" ng-repeat="(index, ad) in item.ads"></li>
								</ol>
								<div class="carousel-inner" role="listbox">
									<div class="item advertising-box" ng-class="{active: index == 0}" ng-repeat="(index, ad) in item.ads">
										<a ng-href="{{ad.url}}" target="_blank">
											<img ng-src="{{ad.cdn_logo}}" alt="">
										</a>
									</div>
								</div>
							</div>
							<div class="go-store" ng-if="key != 'recommend' && item.ads.length == 0">
								<div class="icon">
									<img ng-src="{{'resource/images/welcome/' + item.icon + '-icon.png'}}" alt="">
								</div>
								<div class="name">
									{{item.name}}
								</div>
								<div class="title">
									网罗市场最新应用，更快了解最新应用
								</div>
								<a href="//s.w7.cc" class="btn btn-primary">去应用市场</a>
							</div>
						</div>
						<div class="right-box">
							<a ng-href="{{'//s.w7.cc/module-'+app.aid+'.html'}}" target="_blank" class="app-item" ng-repeat="(index, app) in item.list" ng-if="index < 9">
								<div class="app-item-box">
									<div class="info">
										<div class="logo">
											<img ng-src="{{app.cdn_logo + '?imageView2/5/w/60/h/60/format/png'}}" alt="">
										</div>
										<div class="text-over">
											<div class="name text-over" ng-bind="app.title"></div>
											<div class="time" ng-bind="'下载次数' + app.down_count" ng-if="key != 'new-app'"></div>
											<div class="time" ng-bind="'更新时间' + app.last_upgrade_time" ng-if="key == 'new-app'"></div>
										</div>
									</div>
								</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>-->
	<!-- 推荐应用 end -->
	<!--new end-->
</div> 
<!--end 系统管理首页-->

<script type="text/javascript">
	$(function(){
		$('.we7-store-search .js-show-search').click(function() {
			$(this).addClass('active')
		})
		$('body').on('click', function(event) {
			var target = $(event.target);
			if (!target.hasClass('js-show-search')
					&& !target.parents('.js-show-search').length) {
						$('.we7-store-search .js-show-search').removeClass('active');
			}
		});
		angular.module('systemApp').value('config', {
			'frame': "<?php echo FRAME;?>",
			'notices': "<?php echo !empty($notices) ? json_encode($notices) : 'null'?>",
			'systemUpgradeUrl' : "<?php  echo url('home/welcome/get_system_upgrade')?>",
			'upgradeModulesUrl': "<?php  echo url('home/welcome/get_upgrade_modules')?>",
			'moduleStatisticsUrl': "<?php  echo url('home/welcome/get_module_statistics')?>",
			'ignoreUpdateUrl' : "<?php  echo url('home/welcome/ignore_update_module')?>",
			'getWorkerOrderUrl' : "<?php  echo url('home/welcome/get_workerorder')?>",
			'apiLink': "<?php echo CLOUD_API_DOMAIN;?>",
			'moduleNotInstalledUrl' : "<?php  echo url('module/manage-system/not_installed')?>",
			'links': {
				'accountApi': "<?php  echo url('statistics/account/get_account_api')?>",
				'removeUnstallModuleUrl' : "<?php  echo url('module/manage-system/recycle_post')?>",
			},
		});
		angular.bootstrap($('.js-system-welcome'), ['systemApp']);
	});
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>