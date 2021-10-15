<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('account/account-header', TEMPLATE_INCLUDEPATH)) : (include template('account/account-header', TEMPLATE_INCLUDEPATH));?>
<div id="js-account-manage-modules-tpl" ng-controller="AccountMangeModulesTpl" ng-cloak>
	<?php  if($owner['is_admin']) { ?>
		<div class="panel we7-panel user-permission">
			<div class="panel-heading">
				主管理员的会员权限
			</div>
			<div class="panel-body we7-padding">
				全部
			</div>
		</div>
	<?php  } ?>
	<?php  if(!$owner['is_admin']) { ?>
		<!--主管理员会员权限-->
		<div class="panel we7-panel user-permission">
			<div class="panel-heading">
				<span>主管理员: {{owner.group.name}}</span>
				
				<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER && $owner['uid'] != $_W['uid']) { ?>
				<a href="<?php  if($owner['founder_groupid'] == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER) { ?><?php  echo url('founder/edit/edit_modules_tpl', array('uid' => $owner['uid']))?><?php  } else { ?><?php  echo url('user/edit/edit_modules_tpl',array('uid'=>$owner['uid']))?><?php  } ?>" class="pull-right color-default">修改</a>
				<?php  } ?>
				
				
			</div>
			<div class="we7-group-show we7-padding"  ng-repeat="module_tpl in modules_tpl">
				<div class="name">
					{{module_tpl.name}}
				</div>
				<div class="group-app-list" >
					<div class="group-app-item" ng-repeat="module in module_tpl[account_type_sign]">
						<img src="{{module.logo}}" class="module-img" alt="">
						<div class="info">
							<div class="title text-over">
								{{module.title}}
							</div>
							<div class="type-list">
								<i ng-class="we7TypeDefault[account_type_sign]['icon']"></i>
							</div>
						</div>
					</div>
					<div class="group-app-item" ng-repeat="module in module_tpl.templates" ng-if="account_type_sign == 'account'">
						<img src="{{module.logo}}" class="template-img" alt="">
						<div class="info">
							<div class="title">
								{{module.title}}
							</div>
							<div class="type-list">
								<i class="wi wi-template"></i>
							</div>
						</div>
					</div>
				</div>
				<a class="action" ></a>
			</div>
			<div class="we7-group-show we7-padding">
				<div class="name">
					附加应用
				</div>
				<div class="group-app-list" >
					<div class="group-app-item" ng-repeat="module in user_extend_modules">
						<img src="{{module.logo}}" class="module-img" alt="">
						<div class="info">
							<div class="title text-over">
								{{module.title}}
							</div>
							<div class="type-list">
								<i ng-class="we7TypeDefault[account_type_sign]['icon']"></i>
							</div>
						</div>
					</div>
					<div class="group-app-item" ng-repeat="module in module_tpl.templates" ng-if="account_type_sign == 'account'">
						<img src="{{module.logo}}" class="template-img" alt="">
						<div class="info">
							<div class="title">
								{{module.title}}
							</div>
							<div class="type-list">
								<i class="wi wi-template"></i>
							</div>
						</div>
					</div>
				</div>
				<a class="action" ></a>
			</div>
		</div>
		
		<!--附加会员权限-->
		<div class="panel we7-panel user-permission">
			<div class="panel-heading">
				<span>附加应用权限组</span>
				
				<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER && $owner['uid'] != $_W['uid']) { ?>
				<we7-modal-app title="'修改附加应用权限组'" module-list="groupList" multiple="true" on-confirm="changeGroup()"><a href="javascript:;" class="pull-right color-default">修改</a></we7-modal-app>
				<?php  } ?>
				
				
				
			</div>
			<div class="we7-group-show we7-padding"  ng-repeat="uni_group in extend.groups">
				<div class="name">
					{{uni_group.name}}
				</div>
				<div class="group-app-list" >
					<div class="group-app-item" ng-repeat="module in uni_group[account_type_sign]" ng-if="module[account_type_sign + '_support'] == 2">
						<img src="{{module.logo}}" class="module-img" alt="">
						<div class="info">
							<div class="title text-over">
								{{module.title}}
							</div>
							<div class="type-list">
								<i ng-class="we7TypeDefault[account_type_sign]['icon']"></i>
							</div>
						</div>
					</div>
					<div class="group-app-item" ng-repeat="module in uni_group.templates" ng-if="account_type_sign == 'account'">
						<img src="{{module.logo}}" class="template-img" alt="">
						<div class="info">
							<div class="title">
								{{module.title}}
							</div>
							<div class="type-list">
								<i class="wi wi-template"></i>
							</div>
						</div>
					</div>
				</div>
				<a class="action" ></a>
			</div>
			<div class="we7-empty-block" ng-if="extend.groups | we7IsEmpty">
				暂无
			</div>
		</div>
		<!-- 附加应用 -->
		<div class="panel we7-panel user-permission">
			<div class="panel-heading">
				<span>附加应用</span>
				
				<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER && $owner['uid'] != $_W['uid']) { ?>
				<we7-modal-app module-list="moduleList" multiple="true" on-confirm="addExtend()"><a href="javascript:;" class="pull-right color-default">修改</a></we7-modal-app>
				<?php  } ?>
				
				
			</div>
			<div class="we7-group-show we7-padding">
				<div class="name">
					附加应用
				</div>
				<div class="group-app-list" >
					<div class="group-app-item" ng-repeat="module in extend.modules" ng-if="module[account_type_sign + '_support'] == 2">
						<img src="{{module.logo}}" class="module-img" alt="">
						<div class="info">
							<div class="title text-over">
								{{module.title}}
							</div>
							<div class="type-list">
								<i ng-class="we7TypeDefault[account_type_sign]['icon']"></i>
							</div>
						</div>
					</div>
					<div class="group-app-item" ng-repeat="module in extend.templates" ng-if="account_type_sign == 'account'">
						<img src="{{module.logo}}" class="template-img" alt="">
						<div class="info">
							<div class="title">
								{{module.title}}
							</div>
							<div class="type-list">
								<i class="wi wi-template"></i>
							</div>
						</div>
					</div>
				</div>
				<a class="action" ></a>
			</div>
		</div>
	<?php  } ?>
	<?php  if(!empty($account_buy_package) || !empty($account_buy_modules)) { ?>
	<div class="panel we7-panel user-permission">
		<div class="panel-heading ">
			商城购买权限
		</div>
		<div class="panel-body">
			
			<?php  if(!empty($account_buy_package)) { ?>
			<table class="table we7-table" >
				<tr>
					<th colspan="2" class="text-left">
						<span class="we7-padding-right">商城购买权限组</span>
						<span></span>
					</th>
					<th colspan="4">
						到期时间
					</th>
				</tr>
				<tbody ng-repeat="package in packagelist">
				<tr>
					<td colspan="3" class="text-left we7-padding-right" ng-init="module_tpl.show = false">
						<span>{{ package['name'] }}</span>
						<span class="text text-danger" ng-if="package.near_expire">即将到期</span>
					</td>
					<td>
						{{ package.expire_time }}
						<?php  if(permission_check_account_user('see_account_post_modules_tpl_edit_store_endtime')) { ?><a href="javascript:;" class="color-default" data-toggle="modal" ng-click="editEndTime(package.expire_time, package.order_id)">修改</a><?php  } ?>
					</td>
					<td>
						<a class="color-default" href="<?php  echo url('site/entry/goodsbuyer', array('direct' => 1, 'operate' => 'goods_info', 'm' => 'store'))?>&goods={{ package.goods_id }}">续费</a>
					</td>
					<td>
						<div class="link-group">
							<a href="javascript:;" class="color-default" ng-show="module_tpl.show" ng-click="module_tpl.show = false">收起</a>
							<a href="javascript:;" class="color-default" ng-show="!module_tpl.show" ng-click="module_tpl.show = true">展开</a>

						</div>
					</td>
				</tr>
				<tr ng-show="module_tpl.show">
					<td colspan="5">
						<div class="col-sm-1 color-gray text-left we7-padding-none"><?php  echo $account->typeName?>应用</div>
						<div class="col-sm-11">
							<div class="col-sm-3 text-left we7-margin-bottom"
								 <?php  if($account->typeSign == ACCOUNT_TYPE_SIGN) { ?> ng-repeat="module in package.account"
								<?php  } else if($account->typeSign == WXAPP_TYPE_SIGN) { ?> ng-repeat="module in package.wxapp"
								<?php  } else if($account->typeSign == WEBAPP_TYPE_SIGN) { ?> ng-repeat="module in package.webapp"
								<?php  } else if($account->typeSign == PHONEAPP_TYPE_SIGN) { ?> ng-repeat="module in package.phoneapp"
								<?php  } else if($account->typeSign == ALIAPP_TYPE_SIGN) { ?> ng-repeat="module in package.aliapp"
								<?php  } ?> >
								<div ng-if="module.name != 'all'" class="text-over text-left">
									<img ng-src="{{ module.logo }}" alt="" style="width:50px;height:50px;">
									{{ module.title }}
								</div>
								<label class="label label-info" ng-if="module.name == 'all'">所有模块</label>
							</div>
						</div>
					</td>
					<td class="we7-padding-right color-default"></td>
				</tr>
				<?php  if($account->typeSign == ACCOUNT_TYPE_SIGN) { ?>
				<tr ng-show="module_tpl.show">
					<td colspan="5">
						<div class="col-sm-1 color-gray text-left we7-padding-none">模板</div>
						<div class="col-sm-11">
							<div class="col-sm-3 text-left we7-margin-bottom" ng-repeat="tpl in package.templates">
								<a href="javascript:;" class="label label-info" ng-bind="tpl.title"></a>
							</div>

						</div>
					</td>
					<td class="we7-padding-right color-default"></td>
				</tr>
				<?php  } ?>
				</tbody>
			</table>
			<?php  } ?>
			<?php  if(!empty($account_buy_modules)) { ?>
			<table class="table we7-table" >
				<tr>
					<th colspan="7" class="text-left">
						<span class="we7-padding-right">商城购买模块</span>
						<span></span>
					</th>
					<th class="text-left">
						<span class="we7-padding-right">到期时间</span>
						<span></span>
					</th>
					<th class="text-left">
						<span class="we7-padding-right">操作</span>
						<span></span>
					</th>
				</tr>
				<tbody>
				<tr>
					<td colspan="9">
						<?php  echo $account->typeName?>应用
					</td>
				</tr>
				<?php  if(is_array($account_buy_modules)) { foreach($account_buy_modules as $module) { ?>
				<?php  if($module['goods_id']) { ?>
				<tr>
					<td>
						<img src="<?php  echo $module['logo'];?>" class="img-responsive icon" alt="" style="width:50px;height:50px;">
					</td>
					<td>
						<?php  echo $module['title'];?>
					</td>
					<td>
						<?php  $expire_week = strtotime('-1 week', $module['expire_time']);?>
						<?php  if($module['expire_time'] > TIMESTAMP && $expire_week < TIMESTAMP) { ?>
						<span class="text text-error">即将到期</span>
						<?php  } ?>
					</td>
					<td colspan="5">
						<?php  if($module['expire_time'] < TIMESTAMP) { ?>
						已到期
						<?php  } else { ?>
						<?php  echo date('Y-m-d', $module['expire_time'])?>
						<?php  } ?>
						<?php  if(permission_check_account_user('see_account_post_modules_tpl_edit_store_endtime')) { ?>
						<a href="javascript:;" class="color-default" data-toggle="modal" ng-click="editEndTime('<?php  echo date('Y-m-d', $module['expire_time'])?>', <?php  echo $module['order_id'];?>)">修改</a>
						<?php  } ?>
					</td>
					<td class="we7-padding-right color-default">
						<?php  if(permission_check_account_user('see_modules_recharge')) { ?>
						<a href="<?php  echo url('site/entry/goodsbuyer', array('direct' => 1, 'operate' => 'goods_info', 'm' => 'store', 'goods' => $module['goods_id']))?>">续费</a>
						<?php  } ?>
						<?php  if(permission_check_account_user('see_modules_deactivate')) { ?>
						<a href="<?php  echo url('site/entry/deactivateOrder', array('direct' => 1, 'm' => 'store', 'order_id' => $module['order_id'], 'goods_id' => $module['goods_id'], 'uniacid' => $uniacid, 'type' => $_GPC['account_type']))?>">删除</a>
						<?php  } ?>
					</td>
				</tr>
				<?php  } ?>
				<?php  } } ?>
				</tbody>
			</table>
			<?php  } ?>
			<div class="modal fade" id="endtime" role="dialog">
				<div class="we7-modal-dialog modal-dialog we7-form">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<div class="modal-title">设置到期时间</div>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<?php  echo tpl_form_field_date('endtime', '');?>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" data-dismiss="modal" ng-click="httpChange()">确定</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
						</div>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	<?php  } ?>


</div>
<script>
	angular.module('accountApp').value('config', {
		owner: <?php echo !empty($owner) ? json_encode($owner) : 'null'?>,
		
		packagelist: <?php echo !empty($account_buy_package) ? json_encode($account_buy_package) : 'null'?>,
		
		modules_tpl: <?php echo !empty($modules_tpl) ? json_encode($modules_tpl) : 'null'?>,
		user_extend_modules: <?php echo !empty($user_extend_modules) ? json_encode($user_extend_modules) : 'null'?>,
		extend: <?php echo !empty($extend) ? json_encode($extend) : 'null'?>,
		uni_groups: <?php echo !empty($uni_groups) ? json_encode($uni_groups) : 'null'?>,
		links: {
			postModulesTpl: "<?php  echo url('account/post/edit_modules_tpl', array('uniacid' => $uniacid))?>",
		},
		modules: <?php echo !empty($modules) ? json_encode($modules) : 'null'?>,
		account_type_sign: '<?php  echo $account->typeSign?>',
	});
	angular.bootstrap($('#js-account-manage-modules-tpl'), ['accountApp']);
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>