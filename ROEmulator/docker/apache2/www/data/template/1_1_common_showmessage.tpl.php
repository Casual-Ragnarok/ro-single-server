<?php if(!defined('IN_DISCUZ')) exit('Access Denied'); hookscriptoutput('showmessage');?>
<?php if(!$_G['inajax']) { include template('common/header'); ?><div id="ct" class="wp cl w">
<?php if(!$param['login']) { ?>
<div class="nfl">
<?php } else { ?>
<div class="nfl" id="main_succeed" style="display: none">
<div class="f_c altw">
<div class="alert_right">
<p id="succeedmessage"></p>
<p id="succeedlocation" class="alert_btnleft"></p>
<p class="alert_btnleft"><a id="succeedmessage_href">如果您的浏览器没有自动跳转，请点击此链接</a></p>
</div>
</div>
</div>
<div class="nfl" id="main_message">
<?php } } else { include template('common/header_ajax'); } if($param['msgtype'] == 1 || $param['msgtype'] == 2 && !$_G['inajax']) { ?>
<div class="f_c altw">
<div id="messagetext" class="<?php echo $alerttype;?>">
<p><?php echo $show_message;?></p>
<?php if($url_forward) { if(!$param['redirectmsg']) { ?>
<p class="alert_btnleft"><a href="<?php echo $url_forward;?>">如果您的浏览器没有自动跳转，请点击此链接</a></p>
<?php } else { ?>
<p class="alert_btnleft"><a href="<?php echo $url_forward;?>">如果 <?php echo $refreshsecond;?> 秒后下载仍未开始，请点击此链接</a></p>
<?php } } elseif($allowreturn) { ?>
<script type="text/javascript">
if(history.length > (BROWSER.ie ? 0 : 1)) {
document.write('<p class="alert_btnleft"><a href="javascript:history.back()">[ 点击这里返回上一页 ]</a></p>');
} else {
document.write('<p class="alert_btnleft"><a href="./">[ <?php echo $_G['setting']['bbname'];?> 首页 ]</a></p>');
}
</script>
<?php } ?>
</div>
<?php if($param['login']) { ?>
<div id="messagelogin"></div>
<script type="text/javascript">ajaxget('member.php?mod=logging&action=login&infloat=yes&frommessage', 'messagelogin');</script>
<?php } ?>
</div>
<?php } elseif($param['msgtype'] == 2) { ?>
<h3 class="flb"><em>提示信息</em><?php if($_G['inajax']) { ?><span><a href="javascript:;" class="flbc" onclick="hideWindow('<?php echo $_GET['handlekey'];?>');" title="关闭">关闭</a></span><?php } ?></h3>
<div class="c altw">
<div class="<?php echo $alerttype;?>"><?php echo $show_message;?></div>
</div>
<p class="o pns">
<?php if($param['closetime']) { ?>
<span class="z xg1"><?php echo $param['closetime'];?> 秒后窗口关闭</span>
<?php } elseif($param['locationtime']) { ?>
<span class="z xg1"><?php echo $param['locationtime'];?> 秒后页面跳转</span>
<?php } if($param['login']) { ?>
<button type="button" class="pn pnc" onclick="hideWindow('<?php echo $_GET['handlekey'];?>');showWindow('login', 'member.php?mod=logging&action=login');"><strong>登录</strong></button>
<?php if(!$_G['setting']['bbclosed']) { ?>
<button type="button" class="pn pnc" onclick="hideWindow('<?php echo $_GET['handlekey'];?>');window.open('member.php?mod=<?php echo $_G['setting']['regname'];?>');"><em><?php echo $_G['setting']['reglinkname'];?></em></button>
<?php } ?>
<button type="button" class="pn" onclick="hideWindow('<?php echo $_GET['handlekey'];?>');"><em>取消</em></button>
<?php } elseif(!$param['closetime'] && !$param['locationtime']) { ?>
<button type="button" class="pn pnc" id="closebtn" onclick="hideWindow('<?php echo $_GET['handlekey'];?>');"><strong>确定</strong></button>
<script type="text/javascript" reload="1">if($('closebtn')) {$('closebtn').focus();}</script>
<?php } ?>
</p>
<?php } else { ?><?php echo $show_message;?><?php } if(!$_G['inajax']) { ?>
</div>
</div><?php include template('common/footer'); } else { include template('common/footer_ajax'); } ?>