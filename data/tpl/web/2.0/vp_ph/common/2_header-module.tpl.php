<?php defined('IN_IA') or exit('Access Denied');?><!-- <span class="menu-title">
	<i class="wi wi-apply"></i>应用
</span> -->
<div class="sub-module-info">
	<div class="module-info-name">
		<?php  if(empty($_W['current_module']['logo'])) { ?>
			<img src="./resource/images/init_module_logo.png" class="head-app-logo module-img" onclick="initModuleLogo()">
		<?php  } else { ?>
			<img src="<?php  echo tomedia($_W['current_module']['logo'])?>" class="head-app-logo module-img">
		<?php  } ?>
		<?php  if($_W['current_module']['main_module']) { ?>
			<div class="name text-over"><?php  echo $_W['current_module']['main_module_title'];?></div>
		<?php  } else { ?>
			<div class="name text-over"><?php  echo $_W['current_module']['title'];?></div>
		<?php  } ?>
	</div>
	<?php  if($_W['current_module']['main_module']) { ?>
	<div class="module-plugin-info">
		<a href="<?php  echo url('home/welcome/ext', array('m' => $_W['current_module']['main_module']))?>"><i class="wi wi-angle-left"></i></a>
		<div class="plugin-info-box">
			<?php  if(file_exists(IA_ROOT. "/addons/". $_W['current_module']['name']. "/icon-custom.jpg")) { ?>
				<img src="<?php  echo tomedia("addons/".$_W['current_module']['name']."/icon-custom.jpg")?>" class="plugin-info-logo" onerror="this.src='./resource/images/gw-wx.gif'">
			<?php  } else { ?>
				<img src="<?php  echo tomedia("addons/".$_W['current_module']['name']."/icon.jpg")?>" class="plugin-info-logo" onerror="this.src='./resource/images/gw-wx.gif'">
			<?php  } ?>
		</div>
		<div class="plugin-info-name text-over"><?php  echo $_W['current_module']['title'];?></div>
	</div>
	<?php  } ?>
</div>

<script>
	function initModuleLogo() {
		$.get('./index.php?c=module&a=manage-system&do=init_modules_logo', function(data){
			window.location.reload();
		});
	}
</script>

<!-- 兼容历史性问题：模块内获取不到模块信息$module的问题-start -->
<?php  if(defined('CRUMBS_NAV') && CRUMBS_NAV == 1) { ?>
<?php  global $module;?>
<?php  } ?>
<!-- end -->