<?php defined('IN_IA') or exit('Access Denied');?><?php  if(empty($tips)) { ?>
	<?php  define('IN_MESSAGE', true)?>
	<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
		<div class="container message-noexist text-center">
			<span class="error-icon"><i class="wi text-<?php  echo $label;?> wi-<?php  if($label=='success') { ?>right-sign<?php  } ?><?php  if($label=='danger') { ?>warning-sign<?php  } ?><?php  if($label=='info') { ?>info-sign<?php  } ?><?php  if($label=='warning') { ?>error-sign<?php  } ?>"></i></span>
			<?php  if(is_array($msg)) { ?>
				<h2>MYSQL 错误：</h2>
				<div class="tips"><?php  echo cutstr($msg['sql'], 300, 1);?></div>
				<div class="state"><b><?php  echo $msg['error']['0'];?> <?php  echo $msg['error']['1'];?>：</b><?php  echo $msg['error']['2'];?></div>
			<?php  } else { ?>
				<div class="tips"><?php  echo $caption;?></div>
				<div class="state"><?php  echo $msg;?></div>
			<?php  } ?>
			<?php  if($redirect && $type != 'expired') { ?>
			<div class="btn-group">
				<a class="btn btn-link" href="<?php  echo $redirect;?>">如果你的浏览器没有自动跳转，请点击此链接</a>
				<script type="text/javascript">
					setTimeout(function () {
						location.href = "<?php  echo $redirect;?>";
					}, 3000);
				</script>
			</div>
			<?php  } else { ?>
				<p>
					<?php  if($type != 'expired') { ?>
					<a href="javascript:history.go(-1);" class="btn btn-primary">点击这里返回上一页</a>
					<a href="<?php  echo url('home/welcome/system', array('page' => 'home'))?>" class="btn btn-default">首页</a>
					<?php  } ?>
					<?php  if(is_array($extend)) { foreach($extend as $button) { ?>
						<a href="<?php  echo $button['url'];?>" class="<?php  echo $button['class'];?>" target="<?php  if(!empty($button['target'])) { ?><?php  echo $button['target'];?><?php  } else { ?>_self<?php  } ?>"><?php  echo $button['title'];?></a>
					<?php  } } ?>
				</p>
			<?php  } ?>
		</div>

		<?php  if($redirect == url('home/welcome/ext', array('m' => 'store')) && $type == 'expired') { ?>
			<script type="text/javascript">
				setTimeout(function () {
					location.href = "<?php  echo $redirect;?>";
				}, 5000);
			</script>
		<?php  } ?>
	<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<?php  } else { ?>
	<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
	<script type="text/javascript">
		function setCookie(cname,cvalue,exdays){
			var d = new Date();
			d.setTime(d.getTime()+(exdays*24*60*60*1000));
			var expires = "expires="+d.toGMTString();
			document.cookie = cname+"="+cvalue+"; "+expires;
		}
		//设置cookie
		var modal = new Object();
			<?php  if(is_array($msg)) { ?>
				modal.title = 'MYSQL 错误';
				modal.msg = '<?php  echo cutstr($msg['sql'], 300, 1);?>';
			<?php  } else { ?>
				modal.title = '<?php  echo $caption;?>';
				modal.msg = '<i class="wi text-<?php  echo $label;?> wi-<?php  if($label=='success') { ?>right-sign<?php  } ?><?php  if($label=='danger') { ?>warning-sign<?php  } ?><?php  if($label=='info') { ?>info-sign<?php  } ?><?php  if($label=='warning') { ?>error-sign<?php  } ?>"></i><?php  echo $msg;?>';
			<?php  } ?>
			<?php  if($redirect) { ?>
				modal.redirect = "<?php  echo $redirect;?>";
				
			<?php  } else { ?>
				modal.redirect = "";
			<?php  } ?>
		setCookie("modal",JSON.stringify(modal),30000);
		
		//跳转
		<?php  if($redirect) { ?>
			setTimeout(function(){
				window.location.href= "<?php  echo $redirect;?>"; 
			},0)	
		<?php  } else { ?>
			setTimeout(function(){
				window.history.back(-1);
			},0)
		<?php  } ?>
		
	</script>
	<div class="hidden">
		<div>
		<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer-base', TEMPLATE_INCLUDEPATH)) : (include template('common/footer-base', TEMPLATE_INCLUDEPATH));?>
	</div>
<?php  } ?>