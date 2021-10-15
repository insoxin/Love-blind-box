<?php defined('IN_IA') or exit('Access Denied');?><?php  if($_W['iscontroller']) { ?>
<ol class="breadcrumb we7-breadcrumb">
	<a href="<?php  echo url('account/manage')?>"><i class="wi wi-back-circle"></i> </a>
	<li><a href="<?php  echo url('account/manage')?>">平台管理</a></li>
	<li><?php echo ACCOUNT_TYPE_NAME;?>设置</li>
</ol>
<?php  } ?>
<div class="we7-head-info">
	<img src="<?php  echo $account['logo'];?>" class="account-img logo" alt="">
	<div class="info">
		<div class="title">
			<?php  echo $account['name'];?>
		</div>
		<div class="type">
			<i class="wi wi-<?php  echo $account['type_sign']?>"></i>
			<?php echo ACCOUNT_TYPE_NAME;?>
		</div>
	</div>
	<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_OWNER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) { ?>
		<a href="<?php  echo url('account/display/switch', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-primary" >进入<?php echo ACCOUNT_TYPE_NAME;?></a>&nbsp;&nbsp;&nbsp;
		<a href="<?php  echo url('account/manage/delete', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-primary" onclick="return confirm('确认放入回收站吗？')">停 用</a>
	<?php  } ?>
</div>
<div class="clearfix"></div>
<div class="btn-group we7-btn-group ">
	<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_OWNER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) { ?>
	<a href="<?php  echo url('account/post/base', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-default <?php  if($do == 'base') { ?> active<?php  } ?>">基础信息</a>
	<?php  } ?>
	<a href="<?php  echo url('account/post-user/edit', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-default <?php  if($action == 'post-user' && $do == 'edit') { ?> active<?php  } ?>">使用者管理</a>
	<?php  if($account->supportVersion) { ?>
	<a href="<?php  echo url('miniapp/manage/display', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-default <?php  if($action == 'manage' && $do == 'display') { ?> active<?php  } ?>">版本管理</a>
	<?php  } ?>
	<a href="<?php  echo url('account/post/modules_tpl', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-default <?php  if($action == 'post' && $do == 'modules_tpl') { ?> active<?php  } ?>">可用应用模板/模块</a>
	<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_OWNER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) { ?>
	<a href="<?php  echo url('account/post/operators', array('uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE))?>" class="btn btn-default <?php  if($do == 'operators') { ?>active<?php  } ?>">应用操作员管理</a>
	<?php  } ?>
</div>