<?php defined('IN_IA') or exit('Access Denied');?></div>
<div class="clearfix"></div>
<div class="container-fluid footer text-center" role="footer">	
	<div class="friend-link">
		<?php  if(empty($_W['setting']['copyright']['footerright'])) { ?>
			<a href="https://ym.qingshan.site">优选源码库</a>&nbsp;&nbsp;
			<a href="https://yxymk.comquestion?type=new_question">问题反馈</a>&nbsp;&nbsp;
		<?php  } else { ?>
			<?php  echo $_W['setting']['copyright']['footerright'];?>
		<?php  } ?>
	</div>
	<div class="copyright"><?php  if(empty($_W['setting']['copyright']['footerleft'])) { ?>Powered by <a href="https://ym.qingshan.site"><b>微信魔方</b></a> v<?php echo IMS_VERSION;?> &copy; 2014-2020 <a href="https://ym.qingshan.site">www.baidu.com</a><?php  } else { ?><?php  echo $_W['setting']['copyright']['footerleft'];?><?php  } ?></div>
	
	<div>
		<?php  $icps = iunserializer($_W['setting']['copyright']['icps']);?>
		<?php  if(!empty($icps)) { ?>
		<?php  if(is_array($icps)) { foreach($icps as $icp) { ?>
		<?php  if(strpos($_SERVER['HTTP_HOST'], $icp['domain']) !== false) { ?>
			备案号：<a href="http://beian.miit.gov.cn/" target="_blank"><?php  echo $icp['icp'];?></a>
			<?php  if(!empty($icp['policeicp_location']) && !empty($icp['policeicp_code'])) { ?>
			<a target="_blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=<?php  echo $icp['policeicp_code']?>" >
				&nbsp;&nbsp;<img src="./resource/images/icon-police.png" >
				<?php  echo $icp['policeicp_location']?><?php  echo $icp['policeicp_code']?>号
			</a>
			<?php  } ?>
			<?php  if(!empty($icp['electronic_license'])) { ?>
				<div>
					<a href="<?php  echo $icp['electronic_license']?>" target="_blank"><img src="https://zzlz.gsxt.gov.cn/images/lz4.png"  height="15px"> 电子执照</a>
				</div>
			<?php  } ?>
		<?php  } ?>
		<?php  } } ?>
		<?php  } ?>
	</div>
</div>
</div>

</div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer-base', TEMPLATE_INCLUDEPATH)) : (include template('common/footer-base', TEMPLATE_INCLUDEPATH));?>
</body>
</html>