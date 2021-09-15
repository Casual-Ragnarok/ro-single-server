<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); if(!$ref && $_GET['action'] == 'get') { if($allowdiy) { ?>
if(!$('diypage') && $('wp')) {
var dom = document.createElement('div');
dom.id = 'diypage';
dom.className = 'area';
$('wp').appendChild(dom);
}
$('diypage').innerHTML = '<div class="hm" style="border: 2px dashed #CDCDCD; padding:200px 0;"><p class="mbn"><button type="button" class="pn pnc" onclick="saveUserdata(\'diy_advance_mode\', 1);openDiy();"><strong>开始 DIY</strong></button></p>\n\<p>自己动手，让自己拥有个性的页面。请点击上面的按钮开始 DIY，或者\n\
<a href="javascript:saveUserdata(\'diy_advance_mode\', \'1\');saveUserdata(\'openfn\',\'drag.openFrameImport(1)\');openDiy();" class="xi2">导入程序现有模板</a></p></div>';

<?php } else { ?>

if(!$('diypage') && $('wp')) {
var dom = document.createElement('div');
dom.id = 'diypage';
dom.className = 'area';
$('wp').appendChild(dom);
}
$('diypage').innerHTML = '<div class="bm hm xs2 xg1" style="padding:200px 0;">正在建设中，请稍候……</div>';

<?php } } ?>