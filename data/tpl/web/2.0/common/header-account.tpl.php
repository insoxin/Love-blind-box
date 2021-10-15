<?php defined('IN_IA') or exit('Access Denied');?><!-- <div class="menu-title"><i class="wi wi-white-collar"></i><?php  echo $_W['account']['type_name'];?></div> -->

<div class="account-info">
	<!--logo-->
	<img src="<?php  echo $_W['account']['logo'];?>" class="head-logo account-img">
	<!-- 名称-->
	<div class="account-name text-over"><?php  echo $_W['account']['name'];?></div>
	<!--类型-->
	<div class="account-type">
		<?php  if($_W['account']->typeSign == 'account') { ?>
		<span class="">
			<?php  if($_W['account']['level'] == 1 || $_W['account']['level'] == 3) { ?>
			订阅号
			<?php  } ?>
			<?php  if($_W['account']['level'] == 2 || $_W['account']['level'] == 4) { ?>
			服务号
			<?php  } ?>
		</span>
		<span class="account-level">
			<?php  if($_W['account']['level'] == 1 || $_W['account']['level'] == 3) { ?>
			<?php  if($_W['account']['level'] == 3) { ?>已认证<?php  } ?>
			<?php  } ?>
			<?php  if($_W['account']['level'] == 2 || $_W['account']['level'] == 4) { ?>
			<?php  if($_W['account']['level'] == 4) { ?>已认证<?php  } ?>
			<?php  } ?>
		</span>
		<span class="account-isconnect">
			<?php  if($_W['uniaccount']['isconnect'] == 0) { ?>
			未接入
			<a href="<?php  echo $_W['account']['accessurl']?>" class="text-danger"> 立即接入</a>
			<?php  } else { ?>
			已接入
			<?php  } ?>
		</span>
		<?php  } ?>
	</div>
	<div class="account-operate">
		<?php  if($_W['account']->typeSign == 'account') { ?>
		<a href="<?php  echo url('utility/emulator');?>" target="_blank" class="h">模拟测试</a>
		<?php  } ?>
		<?php  if($_W['role'] != ACCOUNT_MANAGE_NAME_OPERATOR) { ?>
		<a href="<?php  echo $_W['account']['manageurl'] . '&iscontroller=0'?>" class="h">管理设置</a>
		<?php  } ?>
	</div>
</div>
