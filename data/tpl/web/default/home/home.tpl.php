<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<script>
    window.we7Info = {'url': "<?php  echo $_W['siteroot'];?>"};
    if(window.sysinfo.highest_role === 'clerk' && !window.localStorage.getItem('we7StarRoute')) {
        window.localStorage.setItem('we7StarRoute', '/modules')
    }
</script>
<div>
    <link href="./resource/home/css/star.css" rel="stylesheet">
    <div id=app></div>
    <script src="./resource/home/js/chunk-vendors.star.js?v=1"> </script>
    <!-- <script src="./resource/home/js/chunk-common.star.js"> </script> -->
    <script src="./resource/home/js/star.star.js?v=1"> </script>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>