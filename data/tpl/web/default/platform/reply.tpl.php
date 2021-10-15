<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php  if(in_array($m, $sysmods)) { ?>
	<ul class="we7-page-tab">
		<?php  if(is_array($active_sub_permission)) { foreach($active_sub_permission as $active_menu) { ?>
			<?php  if(permission_check_account_user($active_menu['permission_name'], false) && (empty($active_menu['is_display']) || is_array($active_menu['is_display']) && in_array($_W['account']['type'], $active_menu['is_display']))) { ?>
				<li <?php  if($m == $active_menu['active']) { ?>class="active"<?php  } ?>><a href="<?php  echo $active_menu['url'];?>"><?php  echo $active_menu['title'];?></a></li>
			<?php  } ?>
		<?php  } } ?>
	</ul>
<?php  } else { ?>
	<ul class="we7-page-tab">
		<?php  if(!empty($_W['current_module']['isrulefields']) &&!empty($_W['account']) && in_array($_W['account']['type'], array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH))) { ?><li class="active"><a href="<?php  echo url('platform/reply', array('module_name' => $m, 'version_id' => intval($_GPC['version_id'])));?>">关键字链接入口 </a></li><?php  } ?>
		<?php  if(!empty($frames['section']['platform_module_common']['menu']['platform_module_cover'])) { ?>
		<li><a href="<?php  echo url('platform/cover', array('module_name' => $m, 'version_id' => intval($_GPC['version_id'])));?>">封面链接入口</a></li>
		<?php  } ?>
	</ul>	
<?php  } ?>
<?php  if($m == 'keyword' || $m == 'userapi' || !in_array($m, $sysmods)) { ?>
	<div id="js-keyword-display" ng-controller="KeywordDisplay" ng-cloak>
		<div class="search-box we7-margin-bottom">
			<?php  if(in_array($m, $sysmods) && $m != 'userapi') { ?>
			<select class="we7-margin-right" ng-model="keywordType" ng-change="changeType()">
				<option value="">全部</option>
				<option value="news" >回复图文</option>
				<option value="apply">回复模块</option>
				<option value="voice">回复语音</option>
				<option value="basic">回复文字</option>
				<option value="music">回复音乐</option>
				<option value="images" >回复图片</option>
				<option value="video" >回复视频</option>
			</select>
			<?php  } ?>

			<form action="./index.php" method="get" class="form-horizontal search-form" role="form">
			<div class="search-form">
				<div class="input-group  search-form">
					<input type="hidden" name="c" value="platform">
					<input type="hidden" name="a" value="reply">
					<input type="hidden" name="module_name" value="<?php  echo $_GPC['module_name'];?>" />
					<input type="hidden" name="status" value="<?php  echo $status;?>" />

					<input name="type" type="hidden" value="<?php  echo $_GPC['type'];?>">
					<span class="input-group-btn">
						<select class="we7-select" name="search_type">
							<option value="keyword" <?php  if($_GPC['search_type'] == 'keyword') { ?>selected<?php  } ?>>关键字</option>
							<option value="rule" <?php  if($_GPC['search_type'] == 'rule') { ?>selected<?php  } ?>>规则名</option>
						</select>
					</span>
					<input class="form-control" name="keyword" type="text" value="<?php  echo $_GPC['keyword'];?>" autocomplete="false" placeholder="输入规则名称或关键字名称" style="width: 330px;">
					<span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span>
				</div>
			</div>
			</form>
			<a href="<?php  echo url('platform/reply/post', array('module_name' => $m));?>" class="btn btn-primary">添加<?php  if($m == 'userapi') { ?>自定义接口<?php  } else { ?>关键字<?php  } ?>回复</a>
			<?php  if(in_array($m, $sysmods) && $m != 'userapi') { ?><a href="<?php  echo url('platform/reply/post', array('module_name' => 'apply'));?>" class="btn btn-default  we7-margin-left">添加应用关键字</a><?php  } ?>
		</div>
		
		<div class="clearfix"></div>
		<div class=" <?php  if(!in_array($m, $sysmods)) { ?> we7-padding-bottom <?php  } ?>">
			<form action="<?php  echo url('platform/reply/delete');?>" method="post" role="form" class="form we7-form" id="form1">
				<input type="hidden" name="m" value="<?php  echo $m;?>">
				<?php  if(!empty($replies)) { ?>
					<table class="table we7-table table-hover">
						<col width="200px"/>
						<col>
						<col width=""/>
						<col width="120px"/>
						<col width="230px"/>
						<tr>
							<th>规则名称</th>
							<th>关键字</th>
							<th>回复内容</th>
							<th>开关</th>
							
							<th class="text-right">操作</th>
						</tr>
						<tr>
							<?php  if(is_array($replies)) { foreach($replies as $row) { ?>
							<td >
								<input id='rid-<?php  echo $row['id'];?>' type="checkbox" name='rid[]' we7-check-all="we7-check-all" value="<?php  echo $row['id'];?>"/>
								<label class="text-over reply-item-name" for="rid-<?php  echo $row['id'];?>">&nbsp;<label class="text-over reply-item-name"><?php  if(!empty($row['name'])) { ?><?php  echo $row['name'];?><?php  } ?></label></label>
							</td>
							<td>
								<?php  if(is_array($row['keywords'])) { foreach($row['keywords'] as $kw) { ?>
								<span class="keyword-tag" data-toggle="tooltip" data-placement="bottom" title="<?php  if($kw['type']==1) { ?>此关键字为精准触发<?php  } else if($kw['type']==2) { ?>此关键字为包含触发<?php  } else if($kw['type']==3) { ?>此关键字为正则匹配触发<?php  } ?>"><?php  echo $kw['content'];?></span>
								<?php  if($kw['type'] == 4) { ?><span class="form-control-static keyword-tag" data-toggle="tooltip" data-placement="bottom" title="托管">优先级在<?php  echo $row['displayorder'];?>之下直接生效</span><?php  } ?>
								<?php  } } ?>
							</td>
							<td>
								<span class="">
								<?php  if($m == 'userapi') { ?>
									自定义
								<?php  } else if(in_array($row['module'], $sysmods)) { ?>
									共<?php  echo $row['allreply']['sum'];?>条（<?php  if($row['allreply']['basic'] > 0) { ?><?php  echo $row['allreply']['basic'];?>条文字 <?php  } ?><?php  if($row['allreply']['images'] > 0) { ?><?php  echo $row['allreply']['images'];?>条图片 <?php  } ?><?php  if($row['allreply']['news'] > 0) { ?><?php  echo $row['allreply']['news'];?>条图文 <?php  } ?><?php  if($row['allreply']['music'] > 0) { ?><?php  echo $row['allreply']['music'];?>条音乐 <?php  } ?><?php  if($row['allreply']['voice'] > 0) { ?><?php  echo $row['allreply']['voice'];?>条语音 <?php  } ?><?php  if($row['allreply']['video'] > 0) { ?><?php  echo $row['allreply']['video'];?>条视频 <?php  } ?><?php  if($row['allreply']['wxcard'] > 0) { ?><?php  echo $row['allreply']['wxcard'];?>条卡券<?php  } ?><?php  if($row['allreply']['wxapp'] > 0) { ?><?php  echo $row['allreply']['wxapp'];?>条小程序<?php  } ?>）
								<?php  } else { ?>
									<?php  echo cutstr($row['module_info']['title'], 10);?>应用
								<?php  } ?>
								</span>
							</td>
							<td>
								<label>
									<div class="switch <?php  if($row['status']) { ?> switchOn<?php  } ?>" id="key-<?php  echo $row['id'];?>" ng-click="changeStatus(<?php  echo $row['id'];?>)"></div>
								</label>
							</td>
							<td>
								<div class="link-group">
									<?php  if(in_array($row['module'], $sysmods)) { ?>
									<a href="<?php  echo url('platform/reply/post', array('module_name' => $m, 'rid' => $row['id']))?>">编辑</a>
									<?php  } else { ?>
									<a href="<?php  echo url('platform/reply/post', array('module_name' => $row['module'], 'rid' => $row['id']))?>">编辑</a>
									<?php  } ?>
									<a href="javascript:void(0);" class="del" onclick="deleteReply('<?php  echo url("platform/reply/delete", array("m" => $row['module'], "rid" => $row["id"]))?>')">删除</a>
									<?php  if(is_array($entries['rule'])) { foreach($entries['rule'] as $ext_menu) { ?>
									<a href="<?php  echo $ext_menu['url'];?>&id=<?php  echo $row['id'];?>&rid=<?php  echo $row['id'];?>"><?php  echo $ext_menu['title'];?></a>
									<?php  } } ?>
								</div>
							</td>
						</tr>
						<?php  } } ?>
					</table>
					<div class="checkboxall">
						<input type="checkbox" name="rid[]" we7-check-all="we7-check-all" id="select_all"  class="we7-margin-left" value="1" />
						<label for="select_all">&nbsp;</label>
						<input type="submit" class="btn btn-danger" value="删除" onclick="if(!confirm('确定删除选中的规则吗？')) return false;"/>
						<input type="hidden" name="token" value="<?php  echo $_W['token'];?>"/>
						<div class="text-right">
							<?php  echo $pager;?>
						</div>
					</div>
				<?php  } else { ?>
					<p class="text-center we7-margin-top">暂无数据</p>
				<?php  } ?>
			</form>
		</div>
	</div>
	<script>

		function deleteReply(url) {
			util.confirm(function () {
				window.location.href = url;
			}, function () {
				return false;
			}, '确认删除吗?');
		}

		$(function () {
			$('[data-toggle="tooltip"]').tooltip();
			$('#select_all').click(function(){
				$('#form1 :checkbox').prop('checked', $(this).prop('checked'));
			});
			$('#form1 :checkbox').click(function(){
				if(!$(this).prop('checked')) {
					$('#select_all').prop('checked', false);
				} else {
					var flag = 0;
					$('#form1 :checkbox[name="rid[]"]').each(function(){
						if(!$(this).prop('checked') && !flag) {
							flag = 1;
						}
					});
					if(flag) {
						$('#select_all').prop('checked', false);
					} else {
						$('#select_all').prop('checked', true);
					}
				}
			});
			angular.module('replyFormApp').value('config', {
				'type': '<?php  echo $_GPC['type'];?>',
				'replyUrl': '<?php  echo url('platform/reply', array('module_name' => 'keyword'));?>',
			});
			angular.bootstrap($('#js-keyword-display'), ['replyFormApp']);
		});
	</script>
<?php  } else if($m == 'special') { ?>
	<div class="NoKeyword-list" id="js-special-display" ng-controller="SpecialDisplay" ng-cloak>
		<div class="table we7-tables">
			<table class="table we7-table table-hover vertical-middle">
				<col width="160px"/>
				<col />
				<col width="120px"/>
				<col width="180px"/>
				<tr>
					<th class="text-left">类型</th>
					<th class="text-left">关键字/模块</th>
					<th>状态</th>
					<th class="text-left">操作</th>
				</tr>
				<?php  if(is_array($mtypes)) { foreach($mtypes as $name => $title) { ?>
				<tr>
					<td class="text-left">
						<?php  echo $title;?>
					</td>
					<td class="text-left">
						<?php  if(!empty($setting[$name]['type'])) { ?>
							<?php  if($setting[$name]['type'] == 'keyword') { ?>
								<?php  echo $setting[$name]['keyword'];?>
							<?php  } else { ?>
								<?php  if(is_array($setting[$name]['module'])) { foreach($setting[$name]['module'] as $module_name) { ?>
									<?php  echo $module[$module_name]['title'];?>
								<?php  } } ?>
							<?php  } ?>
						<?php  } else { ?>
							<?php  if(!empty($setting[$name]['keyword'])) { ?>
								<?php  echo $setting[$name]['keyword'];?>
							<?php  } else { ?>
								<?php  if(is_array($setting[$name]['module'])) { foreach($setting[$name]['module'] as $module_name) { ?>
									<?php  echo $module[$module_name]['title'];?>
								<?php  } } ?>
							<?php  } ?>
						<?php  } ?>
					</td>
					<td>
						<label>
							<div ng-class="switch_class['<?php  echo $name;?>']" ng-click="changestatus('<?php  echo $name;?>')"></div>
						</label>
					</td>
					<td>
						<div class="link-group text-left">
							<a href="<?php  echo url('platform/reply/post', array('module_name' => 'special', 'type' => $name))?>" class="keyword-link">编辑</a>
						</div>
					</td>
				</tr>
				<?php  } } ?>
			</table>
		</div>
	</div>
	<script>
		$(function() {
			angular.module('replyFormApp').value('config', {
				<?php  if(is_array($mtypes)) { foreach($mtypes as $name => $title) { ?>
					'<?php  echo $name;?>' : '<?php  echo $setting[$name]['type'];?>',
				<?php  } } ?>
				'url' : '<?php  echo url('platform/reply/change_status')?>'
			});
			angular.bootstrap($('#js-special-display'), ['replyFormApp']);
		});
	</script>
<?php  } else if($m == 'welcome') { ?>
	<div class="alert we7-page-alert">
		<i class="wi wi-info-sign"></i>用户关注公众号时，发送的欢迎信息。
	</div>
	<div class="new-keyword" id="welcome" ng-cloak>
		<div class="we7-form" ng-controller="WelcomeDisplay">
			<form id="reply-form" class="form-horizontal form" action="<?php  echo url('platform/reply/post', array('module_name' => $m, 'rid' => $rule_keyword_id))?>" method="post" enctype="multipart/form-data">
				<div>
					<?php  echo module_build_form('core', $rule_keyword_id, array('keyword' => false))?>
				</div>
				<input type="submit" name="submit"  value="保存" class="btn btn-primary" style="padding: 6px 50px;"/>
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				<input type="hidden" name="m" value="<?php  echo $m;?>">
				<input type="hidden" name="type" value="<?php  echo $type;?>">
			</form>
		</div>
	</div>
	<script>
		require(['underscore'], function() {
			angular.bootstrap($('#welcome'), ['replyFormApp']);
		});
	</script>
<?php  } else if($m == 'default') { ?>
	<div class="alert we7-page-alert">
		<i class="wi wi-info-sign"></i>当系统不知道该如何回复粉丝的消息时，默认发送的内容。
	</div>
	<div class="new-keyword" id="default">
		<div id="a" class="we7-form" ng-controller="DefaultDisplay">
			<form id="reply-form" class="form-horizontal form" action="<?php  echo url('platform/reply/post', array('module_name' => $m, 'rid' => $rule_keyword_id))?>" method="post" enctype="multipart/form-data">
				<div>
					<?php  echo module_build_form('core', $rule_keyword_id, array('keyword' => false))?>
				</div>
				<input type="submit" name="submit"  value="保存" class="btn btn-primary" style="padding: 6px 50px;"/>
				<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				<input type="hidden" name="m" value="<?php  echo $m;?>">
				<input type="hidden" name="type" value="<?php  echo $type;?>">
			</form>
		</div>
	</div>
	<script>
		require(['underscore'], function() {
			angular.bootstrap($('#default'), ['replyFormApp']);
		});
	</script>
<?php  } else if($m == 'service') { ?>
	<div class="NoKeyword-list" id="js-service-display" ng-controller="serviceDisplay" ng-cloak>
		<div class="table we7-tables">
			<table class="table we7-table table-hover">
				<col width="160px"/>
				<col />
				<col width="120px"/>
				<tr>
					<th class="text-left">服务名称</th>
					<th class="text-left">功能说明</th>
					<th>状态</th>
				</tr>
				<tr ng-repeat="(id, api) in service track by id" ng-if="service">
					<td class="text-left">
						{{ api.name }}
					</td>
					<td class="text-left" ng-bind-html="api.description">
					</td>
					<td class="vertical-middle">
						<label>
							<div ng-class="api.switch == 'checked' ? 'switch switchOn' : 'switch'" ng-click="changeStatus(id)"></div>
						</label>
					</td>
				</tr>
				<tr ng-if="!service">
					<td colspan="3" class="text-center">
						暂无数据
					</td>
				</tr>
			</table>
		</div>
	</div>
	<script>
		$(function() {
			angular.module('replyFormApp').value('config', {
				'url' : "<?php  echo url('platform/reply/change_status')?>",
				'service' : <?php echo !empty($service_list) ? json_encode($service_list) : 'null'?>
			});
			angular.bootstrap($('#js-service-display'), ['replyFormApp']);
		});
	</script>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>