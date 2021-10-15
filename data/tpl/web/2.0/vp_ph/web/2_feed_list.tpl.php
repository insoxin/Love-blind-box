<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style>
	.sdverify{display:inline-block;height:20px;line-height:20px;padding-left:20px;font-size:12px;background:url("<?php echo MODULE_URL;?>/static/mobile/images/v_0.png") no-repeat left center;background-size:16px 16px;}
	.sdverify.v1{background-image:url("<?php echo MODULE_URL;?>/static/mobile/images/v_1.png");color:#07ce81}
	.sdverify.v2{background-image:url("<?php echo MODULE_URL;?>/static/mobile/images/v_2.png");color:#ff8808}
</style> 


<ul class="nav nav-tabs">
	<li class="active"><a href="javascript:location.reload()">纸条管理</a></li>
</ul>


<form id="the_form" class="form-horizontal form" action="" method="post" enctype="multipart/form-data">
	<div class="panel panel-default">
		<div class="panel-heading">
			纸条筛选
		</div>
		<div class="panel-body">

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label">用户UID</label>
				<div class="col-sm-9 col-xs-12">
					<input type="text" name="s_user" class="form-control" value="<?php  echo $_GPC['s_user'];?>" placeholder="根据UID查找用户"/>
					<div class="help-block"></div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label">性别</label>
				<div class="col-sm-9 col-xs-12">
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" value="" name="s_sex" <?php  if(empty($_GPC['s_sex'])) { ?>checked<?php  } ?>/> 全部
						</label>
						<label class="radio-inline">
							<input type="radio" value="1" name="s_sex" <?php  if($_GPC['s_sex']=='1' ) { ?>checked<?php  } ?>/> 男生
						</label>
						<label class="radio-inline">
							<input type="radio" value="2" name="s_sex" <?php  if($_GPC['s_sex']=='2' ) { ?>checked<?php  } ?>/> 女生
						</label>
					</div>
					<div class="help-block"></div>
				</div>
			</div>

			
			<!--
			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label">审核状态</label>
				<div class="col-sm-9 col-xs-12">
					<div class="col-sm-9 col-xs-12">
						<label class="radio-inline">
							<input type="radio" value="" name="s_op" <?php  if(empty($_GPC['s_op'])) { ?>checked<?php  } ?>/> 全部
						</label>
						<label class="radio-inline">
							<input type="radio" value="1" name="s_op" <?php  if($_GPC['s_op']==1) { ?>checked<?php  } ?>/> 未审核
						</label>
						<label class="radio-inline">
							<input type="radio" value="3" name="s_op" <?php  if($_GPC['s_op']==3) { ?>checked<?php  } ?>/> 已通过
						</label>
						<label class="radio-inline">
							<input type="radio" value="2" name="s_op" <?php  if($_GPC['s_op']==2) { ?>checked<?php  } ?>/> 已封禁
						</label>
					</div>
					<div class="help-block"></div>
				</div>
			</div>
			-->

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
				<div class="col-sm-9 col-xs-12">
					<input id="btn-submit" type="submit" value="筛选" class="btn btn-primary col-lg-1" data-loading-text="正在筛选..."/>
				</div>
			</div>
		</div>
	</div>

</form>

<div class="panel panel-default">
	<nav role="navigation" class="navbar navbar-default navbar-static-top" style="margin-bottom:0;">
		<div class="container-fluid">
			<div class="navbar-header">
				<a href="javascript:;" class="navbar-brand">纸条列表 (<span style="color:#428bca"><?php  echo $total;?></span>条记录)</a> </a>
			</div>
		</div>
	</nav>


	<div class="panel-body table-responsive" style="overflow:visible;">
		<table class="table table-hover">
			<thead class="navbar-inner">
				<tr>
					<th style="width:60px;">UID</th> 
					<th>用户</th>
					<th></th>

					<th style="width:80px;">性别</th> 
					<!--<th style="width:250px;">链接标题</th> -->
					<th>纸条内容</th>
					<th>联系方式</th>
					<th style="width:200px;">发布时间</th>
					<th style="width:100px;">审核状态</th>
					<th>被抽次数</th>
					<!---<th style="width:240px;">操作</th>-->
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($list)) { foreach($list as $item) { ?>
				<tr>
					<td>
						<p><?php  echo pencode($item['_user']['id'])?></p>
						<p><?php  echo $item['_user']['id'];?></p>
					</td>
					<td><img src="<?php  echo VP_AVATAR($item['_user']['avatar'],'s');?>" style="width:50px;"/></td>
					<td><?php  echo $item['_user']['nickname'];?></td>

					<td style="overflow:visible;">
						<?php  if($item['sex']==1) { ?>  
							男生
						<?php  } ?>
						<?php  if($item['sex']==2) { ?>  
							女生
						<?php  } ?>
					</td>
					<td  style="word-break:break-all;">
						<p><?php  echo $item['content'];?><p>
						<div style="height:50rpx;overflow:hidden;">
						<?php  if(is_array($item['images'])) { foreach($item['images'] as $img) { ?>
							<a href="<?php  echo VP_IMAGE_URL($img)?>" target="_blank"><img style="width:50px;" src="<?php  echo VP_IMAGE_URL($img)?>"/></a>
						<?php  } } ?>
						</div>
					</td> 
					<td>
						<?php  if($item['ctype']=='wx') { ?>微信号：<?php  } ?>
						<?php  if($item['ctype']=='qq') { ?>QQ号：<?php  } ?>
						<?php  echo $item['contact'];?>
					</td> 
					
					<td>
						<?php  echo date('Y-m-d H:i:s',$item['create_time']);?>
					</td>
					
					<td style="overflow:visible;">
						<?php  if($item['verify_status']==0 || $item['verify_status']==5) { ?>  
							<label class='label label-default' >待审核</label>
						<?php  } ?>
						<?php  if($item['verify_status']==10) { ?>  
							<label class='label label-danger' >审核拒绝</label>
							<p><?php  echo $item['verify_remark'];?></p>
						<?php  } ?>
						<?php  if($item['verify_status']==20) { ?>  
							<label class='label label-success' >审核通过</label>
						<?php  } ?>
					</td>
					<td>
						<?php  echo $item['views'];?>
					</td>
					
					<!--
					<td style="text-align:left;overflow:visible;">
						<div class="btn-group btn-group">
							<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:;">查看 <span class="caret"></span></a>
							<ul class="dropdown-menu dropdown-menu-right" role="menu">
								<li>
									<a href="<?php  echo $item['_url'];?>" target="_blank"><i class="fa fa-external-link fa-fw"></i> 直接访问</a>
								</li>
								<li role="presentation">
									<a href="javascript:;" onclick="displayUrl('<?php  echo $item['_url'];?>', '<?php  echo $item['_surl'];?>');"><i class="fa fa-link fa-fw"></i> 查看链接</a>
								</li>
								<li role="presentation">
									<a href="javascript:;" onclick="displayQr('<?php  echo $this->createWebUrl('qr', array('raw' => base64_encode($item['_url'])))?>');"><i class="fa fa-qrcode fa-fw"></i> 查看二维码</a>
								</li>
							</ul>
						</div>

						<?php  if($item['op']==0 || $item['op']==1) { ?>  
							<a href="javascript:;" class="btn btn-sm btn-success" onclick="cell_to_op2('<?php  echo $item['id'];?>','<?php  echo $item['title'];?>');">通过</a>
						<?php  } ?>
						<?php  if($item['op']==0 ||  $item['op']==2) { ?>  
							<a href="javascript:;" class="btn btn-sm btn-danger" onclick="cell_to_op1('<?php  echo $item['id'];?>','<?php  echo $item['title'];?>');">封禁</a>
						<?php  } ?>

						<a href="<?php  echo $this->createWebUrl('link', array('cmd'=>'delete','id' => $item['id']))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="删除"><i class="fa fa-times"></i></a>
					</td>
					-->

				</tr>
				<?php  } } ?>
			</tbody>
		</table>
		<?php  echo $pager;?>
	</div>
	</div>
</div>
<script type="text/javascript">
	require(['bootstrap'],function($){
		$('.btn-tooltip').hover(function(){
			$(this).tooltip('show');
		},function(){
			$(this).tooltip('hide');
		});
	});

	function displayEnter(page,accode) {
		require(['jquery', 'util'], function($, u) {
			var content = '<div class="panel panel-default text-center"><img src="' + accode + '" alt="二维码" class="img-rounded" style="width:200px"> <p style="padding:10px;">'+page+'</p></div>';
			var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
			var diaobj = u.dialog('查看二维码', content, footer);
			diaobj.find('.btn-default').click(function() {
				diaobj.modal('hide');
			});
			diaobj.modal('show');
		});
	}


	function displayUrl(lurl, surl) {
		require(['jquery', 'util'], function($, u) {
			var content = '<p class="form-control-static" style="word-break:break-all">菜单使用链接(需要oAuth): <br>' + lurl + '</p>';
			content += '<p class="form-control-static" style="word-break:break-all">自动回复使用链接: <br>' + surl + '</p>';
			var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>' +
					'<button type="button" class="btn btn-primary">复制菜单链接</button>' +
					'<button type="button" class="btn btn-success">复制自动回复链接</button>';
			var diaobj = u.dialog('查看URL', content, footer);
			diaobj.find('.btn-default').click(function() {
				diaobj.modal('hide');
			});
			diaobj.on('shown.bs.modal', function(){
				u.clip(diaobj.find('.btn-primary')[0], lurl);
				u.clip(diaobj.find('.btn-success')[0], surl);
			});
			diaobj.modal('show');
		});
	}
	function displayQr(url) {
		require(['jquery', 'util'], function($, u) {
			var content = '<div class="panel panel-default text-center"><img src="' + url + '" alt="房间二维码" class="img-rounded"></div>';
			var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
			var diaobj = u.dialog('查看URL二维码', content, footer);
			diaobj.find('.btn-default').click(function() {
				diaobj.modal('hide');
			});
			diaobj.modal('show');
		});
	}


	// 封禁
	function cell_to_op1(id,title) {
		require(['jquery', 'util'], function($, u) {
			var content =  '	<div class="form-group">';
				content += '	<h4>封禁原因：</h4>';
                content += '	<textarea name="op_remark" class="op_remark form-control" rows="5"></textarea>';
				content += '	</div>';

			var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
					'<button type="button" class="btn btn-success">确定封禁</button>';
			var diaobj = u.dialog(title, content, footer);
			diaobj.find('.btn-default').click(function() {
				diaobj.modal('hide');
			});
			diaobj.find('.btn-success').click(function() {
				$.post("<?php  echo $this->createWebUrl('link', array('cmd' => 'op','submit' => 'stop'))?>",{
					id:id,
					op_remark:diaobj.find('.op_remark').val()
				},function(resp) {
					if(resp.status==1){
						alert(resp.info);
						location.reload();
					}else{
						alert(resp.info);
					}
				});
			});
			diaobj.modal('show');
		});
	}
	
	// 启用
	function cell_to_op2(id,title) {
		require(['jquery', 'util'], function($, u) {
			var content =  '	<div class="form-group">';
				content += '	<h4>备注：</h4>';
                content += '	<textarea name="op_remark" class="op_remark form-control" rows="5"></textarea>';
				content += '	</div>';

			var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
					'<button type="button" class="btn btn-success">确定通过</button>';
			var diaobj = u.dialog(title, content, footer);
			diaobj.find('.btn-default').click(function() {
				diaobj.modal('hide');
			});
			diaobj.find('.btn-success').click(function() {
				$.post("<?php  echo $this->createWebUrl('link', array('cmd' => 'op','submit' => 'open'))?>",{
					id:id,
					op_remark:diaobj.find('.op_remark').val()
				},function(resp) {
					if(resp.status==1){
						alert(resp.info);
						location.reload();
					}else{
						alert(resp.info);
					}
				});
			});
			diaobj.modal('show');
		});
	}

</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
