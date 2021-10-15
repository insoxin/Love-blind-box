<?php defined('IN_IA') or exit('Access Denied');?>
	<?php  if(!empty($_W['setting']['copyright']['statcode'])) { ?><?php  echo $_W['setting']['copyright']['statcode'];?><?php  } ?>
	<?php  if(!empty($_GPC['m']) && !in_array($_GPC['m'], array('keyword', 'special', 'welcome', 'default', 'userapi')) || defined('IN_MODULE')) { ?>
	<script>
		if(typeof $.fn.tooltip != 'function' || typeof $.fn.tab != 'function' || typeof $.fn.modal != 'function' || typeof $.fn.dropdown != 'function') {
			require(['bootstrap']);
		}
		$('[data-toggle="tooltip"]').tooltip()
	</script>
	<?php  } ?>
	<?php  if(!defined('IN_MODULE')) { ?>
	<script>
		$(document).ready(function() {
			if($('select').niceSelect) {
				$('select').niceSelect();
			}
		});
	</script>
	<?php  } ?>