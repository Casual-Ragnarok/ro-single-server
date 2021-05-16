<?php
include_once DISCUZ_ROOT.'./data/dzapp_haodai_config.php';
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_setting.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_xindai_ad_'.HD_CITY.'.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.HD_CITY.'.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.HD_CITY.'.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_jyfx_'.HD_CITY.'.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_xiaofei.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_goufang.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_gouche.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_qiye.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_hot_recommend.php');
@unlink(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_city.php');
@unlink(DISCUZ_ROOT.'./data/dzapp_haodai_config.php');
$finish = TRUE;
?>