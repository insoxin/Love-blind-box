<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php  if($do == 'display') { ?>
<div class="clearfix">
	<div class="we7-padding-bottom clearfix">
		<?php  if(empty($module['main_module'])) { ?>
		<div class="pull-right">
			<a href="<?php  echo url('module/permission/post', array('module_name' => $module_name))?>" class="btn btn-primary we7-padding-horizontal">添加操作员</a>
			<a href="<?php  echo url('user/register', array('type' => USER_TYPE_CLERK, 'm' => $module_name, 'uniacid' => $_W['uniacid']))?>"  target="_blank" class="btn btn-primary we7-padding-horizontal">注册操作员</a>
		</div>
		<?php  } ?>
	</div>
	<table id="js-module-permission" class="table we7-table table-hover" ng-controller="clerkPermission" ng-cloak>
		<thead class="navbar-inner">
		<tr>
			<th class="text-center" style="width:100px;">操作员名称</th>
			<th class="text-center" style="width:150px">权限信息</th>
			<th class="text-center" style="width:150px">状态</th>
			<th class="text-right" style="width:100px;">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php  if(!empty($user_permissions)) { ?>
		<?php  if(is_array($user_permissions)) { foreach($user_permissions as $item) { ?>
			<tr>
				<td class="text-center" style="width:50px;"><?php  echo $item['user_info']['username'];?></td>
				<td class="text-center">
					<?php  if(!empty($item['permission']) && !array_key_exists('all', $item['permission'])) { ?>
						<?php  if(is_array($item['permission'])) { foreach($item['permission'] as $permission) { ?>
						<span class="label label-primary"><?php  echo $permission;?></span>
						<?php  } } ?>
					<?php  } else { ?>
						<span class="label label-primary">所有</span>
					<?php  } ?>
				</td>
				<td class="text-center">
					<?php  if($item['user_info']['status'] == 1) { ?><span class="color-red">待审核</span>
					<?php  } else if($item['user_info']['status'] == 3) { ?><span class="color-gray">已禁用</span>
					<?php  } else { ?><span class="color-default">正常</span>
					<?php  } ?>
				</td>
				<td style="width:100px;">
					<div class="link-group" >
						<?php  if(empty($module['main_module'])) { ?>
							<a href="<?php  echo url('module/permission/post', array('uid' => $item['uid'], 'module_name' => $module_name));?>">编辑</a>
							<a href="javascript:void(0);" ng-click="deleteClerk(<?php  echo $item['uid'];?>, '<?php  echo $module_name;?>')" class="del">删除</a>
						<?php  } else { ?>
							<span>---</span>
						<?php  } ?>
					</div>
				</td>
			</tr>
		<?php  } } ?>
		<?php  } else { ?>
		<tr>
			<td colspan="3" class="text-center">暂无数据</td>
		</tr>
		<?php  } ?>
		</tbody>
	</table>
	<script>
		angular.module('moduleApp').value('config', {
			links: {
				deleteClerk: "<?php  echo url('module/permission/delete');?>"
			}
		});
		angular.bootstrap($('#js-module-permission'),['moduleApp']);
	</script>
</div>
<?php  } ?>
<?php  if($do == 'post') { ?>
<div class="clearfix">
	<form action="" method="post" class="form-horizontal form ajaxfrom we7-form" role="form" id="form-user">
		<div class="form-group">
			<label class="col-sm-2 ontrol-label">用户名</label>
			<div class="col-sm-6 input-group">
				<input id="" name="username" type="text" class="form-control" value="<?php  echo $user['username'];?>" <?php  if(!empty($_GPC['uid'])) { ?>readonly<?php  } ?>/>
				<span class="input-group-btn">
					<a href="<?php  echo url('user/register', array('type' => USER_TYPE_CLERK, 'm' => $module_name, 'uniacid' => $_W['uniacid']))?>"  target="_blank" class="btn btn-default">注册操作员</a>
				</span>
			</div>
			<div class="col-sm-offset-2">
				<span class="help-block">请输入用户名，用户名为 3 到 15 个字符组成，包括汉字，大小写字母（不区分大小写）</span>
			</div>
		</div>
		<div class="form-group module-permission">
			<label class="col-sm-2 control-label">权限设置</label>
			<div class="col-sm-10">
				<?php  if(is_array($all_permission)) { foreach($all_permission as $key => $module_val) { ?>
					<div class="col-sm-10 col-lg-10 col-xs-12 plugin-name"><?php  echo $module_val['info']['title'];?></div>
					<?php  if(is_array($module_val['permission'])) { foreach($module_val['permission'] as $sub_key => $permission) { ?>
					<div class="col-sm-4 dropdown">
						<span class="checkbox">
							<input id="check-child-<?php  echo $key;?>-<?php  echo $sub_key;?>" type="checkbox" value="<?php  echo $permission['permission'];?>" name="module_permission[<?php  echo $module_val['info']['name'];?>][]" <?php  if(!empty($permission['checked'])) { ?>checked<?php  } ?>>
							<label for="check-child-<?php  echo $key;?>-<?php  echo $sub_key;?>" data-toggle="tooltip"><?php  echo $permission['title'];?><?php  if(!empty($menu['sub_permission'])) { ?><span class="caret"></span><?php  } ?></label>
						</span>
						<?php  if($permission['sub_permission']) { ?>
						<ul class="dropdown-menu">
							<?php  if(is_array($permission['sub_permission'])) { foreach($permission['sub_permission'] as $sub_permission) { ?>
							<li class="text-left">
								<input id="check-child-<?php  echo $sub_permission['permission'];?>"  we7-check-all="1" type="checkbox" value="<?php  echo $sub_permission['permission'];?>" <?php  if(!empty($sub_permission['checked'])) { ?>checked<?php  } ?> name="module_permission[<?php  echo $module_val['info']['name'];?>][]">
								<label for="check-child-<?php  echo $sub_permission['permission'];?>">
									<?php  echo $sub_permission['title'];?>
								</label>
							</li>
							<?php  } } ?>
						</ul>
						<?php  } ?>
					</div>
					<?php  } } ?>
				<?php  } } ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 ">
				<input type="submit" class="btn btn-primary span3" name="submit" value="提交" />
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
			</div>
		</div>
	</form>
</div>
<script>
    $('.module-permission .dropdown span').hover(function(){
        $(this).parent().addClass('open').find('.dropdown-menu').show();
        $(this).parent().find('.dropdown-menu').hover(
            function(){$(this).show();$(this).parent().addClass('open')},
            function(){$(this).hide();$(this).parent().removeClass('open');}
        );
    },function(){
        $(this).parent().removeClass('open').find('.dropdown-menu').hide();
    });
var haveChecked = false;
$('input[type="checkbox"]').each(function(i, n) {
	if ($(n).prop('checked')) {
		haveChecked = true;
	}
});
if (!haveChecked) {
	$('input[type="checkbox"]').each(function(i, n) {
		$(n).prop('checked', 'checked')
	});	
}
</script>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>