<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div id="js-user-edit-base" ng-controller="UserEditOperatoers" ng-cloak>

	<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('account/account-header', TEMPLATE_INCLUDEPATH)) : (include template('account/account-header', TEMPLATE_INCLUDEPATH));?>

	<div class="search-box clearfix we7-margin-bottom">
		<form action="" method="get" class="search-form">
			<input type="hidden" name="c" value="<?php  echo $controller;?>">
			<input type="hidden" name="a" value="<?php  echo $action;?>">
			<input type="hidden" name="do" value="<?php  echo $do;?>">
			<input type="hidden" name="uid" value="<?php  echo $uid;?>">
			<input type="hidden" name="uniacid" value="<?php  echo $uniacid;?>">
			<input type="hidden" name="page" value="1">
			<div class="input-group">
				<input class="form-control" name="username" value="<?php  echo $username;?>" type="text" placeholder="请输入要搜索的用户名">
				<span class="input-group-btn"><button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button></span>
			</div>
		</form>
	</div>
	<table class="table we7-table">
		<tr>
			<th>用户名</th>
			<th>操作应用</th>
			<th>权限信息</th>
			<th>操作</th>
		</tr>
		<tr ng-repeat="clerk in list" ng-if="list">
			<td><span ng-bind="clerk.username"></span></td>
			<td><span ng-bind="clerk.module_name"></span></td>
			<td class="color-default" ><span ng-bind="clerk.permission"></span></td>
			<td class="color-default">
				<a target="_blank" ng-href="{{clerk.permission_setting_url}}">权限设置</a>
				<a href="javascript:;" ng-click="deleteClerk(clerk.uid, clerk.type)" ng-if="clerk.can_delete">删除</a>
			</td>
		</tr>
		<tr ng-if="!list">
			<td colspan="10" class="text-center">暂无操作员...</td>
		</tr>
	</table>
	<div class="text-right">
		<?php  echo $pager;?>
	</div>
</div>
<script>
	angular.module('userProfile').value('config', {
		list: <?php echo !empty($list) ? json_encode($list) : 'null'?>,
		links: {
			deleteClerk: "<?php  echo url('module/permission/delete', array('uniacid' => $uniacid))?>",
		},
	});
	angular.bootstrap($('#js-user-edit-base'), ['userProfile']);
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>

