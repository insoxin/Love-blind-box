<?php defined('IN_IA') or exit('Access Denied');?><?php  global $top_nav?>
<?php  $top_nav_shortcut = array()?>
<?php  $nav_top_fold=array()?>
<?php  $platform_url=url('account/display')?>
<?php  $nav_top_fold[] = array('name' => 'all', 'title'=>'全部类型', 'type' => 'all', 'url' => $platform_url)?>
<?php  if(is_array($top_nav)) { foreach($top_nav as $nav) { ?>
    <?php  if(in_array($nav['name'], array(ACCOUNT_TYPE_SIGN, WXAPP_TYPE_SIGN, WEBAPP_TYPE_SIGN, PHONEAPP_TYPE_SIGN, ALIAPP_TYPE_SIGN, BAIDUAPP_TYPE_SIGN, TOUTIAOAPP_TYPE_SIGN))) { ?>
        <?php  $nav_top_fold[]=$nav?>
    <?php  } else if(in_array($nav['name'], array('store', 'help', 'workorder', 'custom_help')) || !$nav['is_system']) { ?>
        <?php  $nav_top_tiled_other[] = $nav?>
        <?php  if(in_array($nav['name'], array('workorder'))) { ?>
            <?php  $top_nav_shortcut[] = $nav?>
        <?php  } ?>
    <?php  } else if($nav['name'] =='message') { ?>
        <?php  $nav_top_message = $nav?>
        <?php  $top_nav_shortcut[] = $nav?>
    <?php  } else { ?>
        <?php  $nav_top_tiled_system[] = $nav?>
        <?php  if(!in_array($nav['name'], array('system', 'site', 'appmarket'))) { ?>
            <?php  $top_nav_shortcut[] = $nav?>
        <?php  } ?>
    <?php  } ?>
    <?php  if('store' == $nav['name'] && $_W['isadmin']) { ?><?php  $nav_top_tiled_system[] = $nav?><?php  } ?>
<?php  } } ?>
<?php  if($_W['iscontroller'] && $_W['isadmin']) { ?>
<?php  if(is_array($nav_top_tiled_system)) { foreach($nav_top_tiled_system as $key => $nav) { ?>
<!-- start应用入口和平台入口特殊处理active-->
<?php  if((FRAME == 'account' && $nav['name'] == 'platform') && !defined('IN_MODULE')) { ?><?php  $nav['name'] = FRAME?><?php  } ?>
<?php  if(defined('IN_MODULE') && $nav['name'] == 'module') { ?><?php  $nav['name'] = 'account'?><?php  } ?>
<!-- end应用入口和平台入口特殊处理active-->
<li class="js-w7-menu-<?php  echo $nav_top_tiled_system[$key]['name'];?> <?php  if(FRAME == $nav['name']) { ?>active<?php  } ?>">
    <a href="<?php  if(empty($nav['url'])) { ?><?php  echo url('home/welcome/' . $nav['name']);?><?php  } else { ?><?php  echo $nav['url'];?><?php  if($nav['title'] != '市场') { ?>&iscontroller=1<?php  } ?><?php  } ?>" <?php  if(!empty($nav['blank'])) { ?>target="_blank"<?php  } ?>><i class="<?php  echo $nav['icon'];?>"></i><?php  echo $nav['title'];?></a>
</li>
<?php  } } ?>
<?php  } ?>