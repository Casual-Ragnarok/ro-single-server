<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
?>
<br />
<center>
	<?php echo adshow('footerbanner//1').adshow('footerbanner//2').adshow('footerbanner//3'); ?>
	<div id="footer">
		Powered by <strong><a target="_blank" href="http://www.discuz.net">Discuz! <?php echo $_G['setting']['version']; ?> Archiver</a></strong> &nbsp; &copy 2001-2013 <a target="_blank" href="http://www.comsenz.com">Comsenz Inc.</a>
		<br />
		<br />
	</div>
</center>
</body>
</html>
<?php output(); ?>