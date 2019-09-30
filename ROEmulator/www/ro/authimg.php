<?
//file:authform.php ÇëÊäÈëÑéÖ¤Âë£º 
/* * Filename:authimg.php */
Header("Content-type:image/PNG");
$auth_num="";
$im=imagecreate(63,20);
srand((double)microtime()*1000000);
$auth_num=$_GET['authnum'];
$black=ImageColorAllocate($im,0,0,0);
$white=ImageColorAllocate($im,255,255,255);
$gray=ImageColorAllocate($im,255,255,255);
ImageFill($im,63,20,$black);
imagestring($im,5,10,3,$auth_num,$gray);
ImagePNG($im);
ImageDestroy($im);
?> 