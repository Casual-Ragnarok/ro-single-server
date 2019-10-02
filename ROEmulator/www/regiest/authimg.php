<?
//file:authform.php ÇëÊäÈëÑéÖ¤Âë£º 
/* * Filename:authimg.php */
Header("Content-type:image/PNG");
$auth_num=$_GET['authnum'];
$im=imagecreate(60,20);
srand((double)microtime()*1000000);
$black=ImageColorAllocate($im,255,252,243);
$white=ImageColorAllocate($im,0,0,0);
$gray=ImageColorAllocate($im,255,0,0);
ImageFill($im,60,20,$black);
imagestring($im,5,10,5,$auth_num,$gray);
ImagePNG($im);
ImageDestroy($im);
?> 