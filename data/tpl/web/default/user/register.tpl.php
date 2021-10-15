<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
<script>
	$('#form1').submit(function(){
		if ($.trim($(':text[name="username"]').val()) == '') {
			util.message('没有输入用户名.', '', 'error');
			return false;
		}
		if ($('#password').val() == '') {
			util.message('没有输入密码.', '', 'error');
			return false;
		}
		if ($('#password').val() != $('#repassword').val()) {
			util.message('两次输入的密码不一致.', '', 'error');
			return false;
		}
		/* 		<?php  if(is_array($extendfields)) { foreach($extendfields as $item) { ?>
		<?php  if($item['required']) { ?>
		if (!$.trim($('[name="<?php  echo $item['field'];?>"]').val())) {
		util.message('<?php  echo $item['title'];?>为必填项，请返回修改！', '', 'error');
		return false;
		}
		<?php  } ?>
		<?php  } } ?>
		*/
		<?php  if($_W['setting']['register']['code']) { ?>
		if ($.trim($(':text[name="code"]').val()) == '') {
			util.message('没有输入验证码.', '', 'error');
			return false;
		}
		<?php  } ?>
		});
	var h = document.documentElement.clientHeight;
	$(".login").css('min-height',h);
</script>
<div class="user-login">
	<div class="login-logo">
		<div class="container">
			<a href="<?php  echo $_W['siteroot'];?>">
				<img src="<?php  if(!empty($_W['setting']['copyright']['flogo'])) { ?><?php  echo to_global_media($_W['setting']['copyright']['flogo'])?><?php  } else { ?>./resource/images/logo/login-logo.png<?php  } ?>" class="logo">
			</a>
		</div>
	</div>
	<div class="login-header">
		<div class="container">
			<h3>账号注册</h3>
			<div class="go">
				已有账号，<a href="<?php  echo url('user/login');?>" class="color-default">去登录</a>
			</div>
		</div>
	</div>
	<div class="login-content">
		<ul class="login-tab clearfix">
			<?php  if(!empty($_W['setting']['register']['open'])) { ?>
			<li  <?php  if($_GPC['register_type'] == 'system' || empty($_GPC['register_type'])) { ?>class="active"<?php  } ?>>
				<a href="<?php  echo url('user/register', array('register_type' => 'system', 'owner_uid' => $_GPC['owner_uid'], 'type' => $user_type, 'm' => $_GPC['m'], 'redirect' => $_GPC['redirect']))?>">用户名密码</a>
			</li>
			<?php  } ?>
			<?php  if(!empty($_W['setting']['copyright']['mobile_status'])) { ?>
			<li <?php  if($_GPC['register_type'] == 'mobile') { ?>class="active"<?php  } ?>>
				<a href="<?php  echo url('user/register', array('register_type' => 'mobile', 'owner_uid' => $_GPC['owner_uid'], 'type' => $user_type, 'm' => $_GPC['m'], 'redirect' => $_GPC['redirect']))?>" >手机注册</a>
			</li>
			<?php  } ?>
		</ul>
		<div class="clearfix"></div>
		<div class="login-form">
			<?php  if(!empty($_W['setting']['register']['open'])) { ?>
			<?php  if($_GPC['register_type'] == 'system' || empty($_GPC['register_type'])) { ?>
			<form action="" class="we7-form register-mobile" method="post" role="form" id="form1" ng-controller="UserRegisterSystem" ng-cloak>
				<?php  if($user_type == USER_TYPE_CLERK) { ?>
				<input type="hidden" name="type" value="<?php echo USER_TYPE_CLERK;?>"/>
				<?php  } ?>
				<div class="form-group required" ng-class="{true:'has-error has-feedback',false:'has-success has-feedback'}[usernameErr]">
					<label class="control-label col-sm-1">用户名:</label>
					<div class="col-sm-11">
						<input name="username" type="text" class="form-control" placeholder="请输入<?php  if($user_type == USER_TYPE_CLERK) { ?>应用操作员<?php  } ?>用户名" ng-model="username" ng-blur="checkUsername()" required>
						<span ng-class="{true:'fa fa-times form-control-feedback reg-system-valid',false:'fa fa-check form-control-feedback reg-system-valid'}[usernameErr]" aria-hidden="true"></span>
						<span ng-class="{true:'color-red',false:'sr-only'}[usernameErr]" class="help-block" ng-bind="usernameMsg"></span>
					</div>
				</div>

				<div class="form-group required" ng-class="{true:'has-error has-feedback',false:'has-success has-feedback'}[passwordErr]">
					<label class="control-label col-sm-1">密码:</label>
					<div class="col-sm-11">
						<input name="password" type="password" id="password" class="form-control col-sm-10" placeholder="请输入不少于8位的密码" ng-model="password" ng-blur="checkPassword()" required>
						<span ng-class="{true:'fa fa-times form-control-feedback reg-system-valid',false:'fa fa-check form-control-feedback reg-system-valid'}[passwordErr]" aria-hidden="true"></span>
						<span ng-class="{true:'color-red',false:'sr-only'}[passwordErr]" class="help-block" ng-bind="passwordMsg"></span>
					</div>
				</div>

				<div class="form-group required" ng-class="{true:'has-error has-feedback',false:'has-success has-feedback'}[repasswordErr]">
					<label class="control-label col-sm-1">确认密码:</label>
					<div class="col-sm-11">
						<input name="password " type="password" id="repassword" class="form-control col-sm-10" placeholder="请再次输入不少于8位的密码" ng-blur="checkRepassword()" ng-model="repassword" required>
						<span ng-class="{true:'fa fa-times form-control-feedback reg-system-valid',false:'fa fa-check form-control-feedback reg-system-valid'}[repasswordErr]" aria-hidden="true"></span>
						<span ng-class="{true:'color-red',false:'sr-only'}[repasswordErr]" class="help-block" ng-bind="repasswordMsg"></span>
					</div>
				</div>

				<!--用户注册拓展字段 end -->
				<?php  if($extendfields) { ?>
					<?php  if(is_array($extendfields)) { foreach($extendfields as $item) { ?>
						<div class="form-group <?php  if($item['required']) { ?>required<?php  } ?>">
							<label class="control-label col-sm-1 "><?php  echo $item['title'];?>:</label>
							<div class="col-sm-11">
								<?php  echo tpl_fans_form($item['field'])?>
							</div>
						</div>
					<?php  } } ?>
				<?php  } ?>

				<?php  if($_W['setting']['register']['code']) { ?>
				<div class="form-group required">
					<label class="control-label col-sm-1">验证码:</label>
					<div class="col-sm-11">
						<div class="input-group">
							<input name="code" type="text" class="form-control" placeholder="请输入验证码" ng-model="code">
							<a href="javascript:;" class="input-group-btn imgverify" ng-click="changeVerify()"><img ng-src="{{image}}" style="height: 32px;"/></a>
						</div>
						<span ng-class="{true:'color-red',false:'sr-only'}[codeErr]" class="help-block" ng-bind="codeMsg"></span>
					</div>
				</div>
				<?php  } ?>
				<?php  if(!empty($setting['agreement_status']) && !empty($setting['agreement_status'])) { ?>
				<div class="login-service">
					<input type="checkbox" we7-check-all="1" id="server_system" class="" ng-model="agreement">
					<label for="server_system">阅读并接受协议 <a href="<?php  echo url('user/agreement-show')?>" class="color-default" target="_blank" >用户协议</a></label>
				</div>
				<?php  } ?>
				<div class="login-submit text-center">
					<?php  if(!empty($setting['agreement_status']) && !empty($setting['agreement_status'])) { ?>
					<input type="submit" name="submit" value="注册" class="btn btn-block btn-primary" ng-disabled="usernameInvalid || passwordInvalid || repasswordInvalid || !agreement"/>
					<?php  } else { ?>
					<input type="submit" name="submit" value="注册" class="btn btn-block btn-primary" ng-disabled="usernameInvalid || passwordInvalid || repasswordInvalid"/>
					<?php  } ?>
					<!-- <a href="<?php  echo url('user/login');?>" class="btn btn-default">登录</a> -->
					<input name="token" value="<?php  echo $_W['token'];?>" type="hidden"/>
					<input name="owner_uid" value="<?php  echo $_GPC['owner_uid'];?>" type="hidden"/>
					<input name="register_type" value="" type="hidden"/>
					<input name="do" value="register" type="hidden"/>
				</div>
			</form>
			<?php  } ?>
			<?php  } ?>
			<!--div class="form-group">
				<label>邀请码:<span style="color:red">*</span></label>
				<input name="invitation" type="text" class="form-control" placeholder="请输入邀请码">
			</div-->
			<?php  if(!empty($_W['setting']['copyright']['mobile_status'])) { ?>
			<?php  if($_GPC['register_type'] == 'mobile') { ?>
			<form action="javascript:;" class="we7-form">
				<div class="register-mobile" ng-controller="UsersRegisterMobile" ng-cloak>
					<div class="form-group required">
						<label class="control-label col-sm-1">手机号:</label>
						<div class="col-sm-11">
							<div class="input-group">
								<input type="text" class="form-control" placeholder="请输入常用手机号" ng-model="mobile" ng-blur="checkMobile()">
								<span ng-class="{true:'color-red',false:'sr-only'}[mobileErr]" class="help-block" ng-bind="mobileMsg"></span>
								<a href="javascript:;" class="input-group-btn">
									<!--<button class="btn btn-primary">发送验证码</button>-->
									<input type="button" class="btn btn-primary send-code" ng-disabled="isDisable" ng-click="sendMessage()" value="{{text}}">
								</a>
							</div>
						</div>
					</div>

					<button style="display:none;" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#imageCode" >开始演示模态框</button>
					<div class="modal fade" id="imageCode" role="dialog" >
						<div class="we7-modal-dialog modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
									<div class="modal-title">输入图形验证码</div>
								</div>
								<div class="modal-body">
									<div class="form-group">
										<div class="input-group">
											<input ng-model="imagecode" type="text" class="form-control" placeholder="请输入图形验证码">
											<a href="javascript:;" class="input-group-btn imgverify" ng-click="changeVerify()">
												<img ng-src="{{image}}" style="height: 32px;"/>
											</a>
										</div>
										<span ng-class="{true:'color-red',false:'sr-only'}[imagecodeErr]" ng-bind="imagecodeMsg"></span>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary" ng-click="sendMessage()">发送短信验证码</button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-1">输入验证码:</label>
						<div class="col-sm-11">
							<input ng-model='smscode' type="text" class="form-control" placeholder="请输入手机验证码">
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-1">密码:</label>
						<div class="col-sm-11">
							<input ng-model="password" type="password" class="form-control" placeholder="请输入不少于8位的密码" ng-blur="checkPassword()">
							<span ng-class="{true:'fa fa-times form-control-feedback color-red reg-system-valid',false:'fa fa-check form-control-feedback color-green reg-system-valid'}[passwordErr]" aria-hidden="true"></span>
							<span ng-class="{true:'color-red',false:'sr-only'}[passwordErr]" class="help-block" ng-bind="passwordMsg"></span>
						</div>
					</div>
					<div class="form-group required">
						<label class="control-label col-sm-1">确认密码:</label>
						<div class="col-sm-11">
							<input ng-model="repassword" type="password" class="form-control" placeholder="请再次输入密码" ng-blur="checkRepassword()">
							<span ng-class="{true:'fa fa-times form-control-feedback color-red reg-system-valid',false:'fa fa-check form-control-feedback color-green reg-system-valid'}[repasswordErr]" aria-hidden="true"></span>
							<span ng-class="{true:'color-red',false:'sr-only'}[repasswordErr]" class="help-block" ng-bind="repasswordMsg"></span>
						</div>
					</div>
					<?php  if(!empty($setting['agreement_status']) && !empty($setting['agreement_status'])) { ?>

						<div class="login-service">
							<input name="agreement" type="checkbox" we7-check-all="1" id="service_mobile" ng-model="agreement" class="">
							<label for="service_mobile">阅读并接受协议 <a href="<?php  echo url('user/agreement-show')?>" class="color-default" target="_blank" >用户协议</a></label>
						</div>
						<div class="login-submit text-center">
							<input type="submit" ng-click="register()" value="注册" class="btn btn-block btn-primary" ng-disabled="passwordInvalid || repasswordInvalid"/>
						</div>
					<?php  } else { ?>
					<div class="login-submit text-center">
						<input type="submit" ng-click="register()" value="注册" class="btn btn-block btn-primary" ng-disabled="passwordInvalid || repasswordInvalid"/>
					</div>
					<?php  } ?>

				</div>
			</form>
			<?php  } ?>
			<?php  } ?>
		</div>
	</div>
</div>


<script type="text/javascript">
	angular.module('userManageApp').value('config', {
		'owner_uid': "<?php echo !empty($owner_uid) ? $owner_uid : 0?>",
		'register_type': "<?php echo !empty($register_type) ? $register_type : 0?>",
		'register_sign': "<?php echo !empty($register_sign) ? $register_sign : 'null'?>",
		'image': "<?php  echo url('utility/code')?>",
		'password_safe': "<?php  echo $setting['safe'];?>",
		'links': {
			'valid_mobile_link': "<?php  echo url('user/register/valid_mobile')?>",
			'send_code_link': "<?php  echo url('utility/verifycode/send_code')?>",
			'check_smscode_link': "<?php  echo url('utility/verifycode/check_smscode')?>",
			'img_verify_link': "<?php  echo url('utility/code')?>",
			'register_link': "<?php  echo url('user/register/register', array('type' => $user_type, 'm' => $_GPC['m'], 'redirect' => $_GPC['redirect']))?>",
			'check_username_link': "<?php  echo url('user/register/check_username')?>",
			'check_code_link': "<?php  echo url('user/register/check_code')?>",
			'check_password_link': "<?php  echo url('user/register/check_password_safe')?>",
		},
	});
	angular.bootstrap($('.register-mobile'), ['userManageApp']);
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer-base', TEMPLATE_INCLUDEPATH)) : (include template('common/footer-base', TEMPLATE_INCLUDEPATH));?>