<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style>
	.sdverify{display:inline-block;height:20px;line-height:20px;padding-left:20px;font-size:12px;background:url("<?php echo MODULE_URL;?>/static/mobile/images/v_0.png") no-repeat left center;background-size:16px 16px;}
	.sdverify.v1{background-image:url("<?php echo MODULE_URL;?>/static/mobile/images/v_1.png");color:#07ce81}
	.sdverify.v2{background-image:url("<?php echo MODULE_URL;?>/static/mobile/images/v_2.png");color:#ff8808}
</style> 


<ul class="nav nav-tabs">
	<li class="active"><a href="<?php  echo $this->createWebUrl('order')?>">消费记录</a></li>
</ul>


<form id="the_form" class="form-horizontal form" action="" method="post" enctype="multipart/form-data">
	<div class="panel panel-default">
		<div class="panel-heading">
			记录筛选
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
				<a href="javascript:;" class="navbar-brand">消费订单</a>
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
					<th>商品</th> 
					<th>价格</th>
					<th>实际支付</th>
					<th>支付方式</th>
					<th>分销商收益</th>
					<th>上级收益</th>
					<th>上上级收益</th>
					<th>平台利润</th>
					<th>消费时间</th>
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

					<td><?php  echo $item['title'];?></td>
					<td><?php  echo $item['amount'];?>元</td>
					<td><?php  echo $item['pay'];?>元</td>
					<td>
						<?php  if($item['pay_way']==0) { ?><label class='label label-success' >在线支付</label><?php  } ?>
						<?php  if($item['pay_way']==10) { ?><label class='label label-default' >后台手动</label><?php  } ?>
					</td>
					<td><?php  echo $item['agentp_money'];?></td>
					<td><?php  echo $item['agentp1_money'];?></td>
					<td><?php  echo $item['agentp2_money'];?></td>
					<td><?php  echo $item['profit'];?></td>
					<td>
						<!---<?php  if($item['status']==1) { ?><label class='label label-success' >正常</label><?php  } else { ?><label class='label label-warning' >删除</label><?php  } ?><br/>-->
						<?php  echo date('Y-m-d h:i', $item['create_time']);?>
					</td>
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
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
