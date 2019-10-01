<?php
if (!defined('FLUX_ROOT')) exit;

$title = Flux::message('ReloadMobSkillsTitle');

$mobDB1 = Flux::config('MobSkillDb1');
$mobDB2 = Flux::config('MobSkillDb2');
$mobDB  = Flux::config('MobSkillDb');

// Check permissions.
$readable1 = is_readable($mobDB1);
$readable2 = is_readable($mobDB2);
$writable  = !file_exists($mobDB) || is_writable($mobDB);

if (!$readable1) {
	$errorMessage = sprintf(Flux::message('ReloadMobSkillsError1'), $mobDB1);
}
else if (!$readable2) {
	$errorMessage = sprintf(Flux::message('ReloadMobSkillsError2'), $mobDB2);
}
else if (!$writable) {
	$errorMessage = sprintf(Flux::message('ReloadMobSkillsError3'), $mobDB);
}
else {
	$fdb1 = fopen($mobDB1, 'r');
	$fdb2 = fopen($mobDB2, 'r');
	$fdb = fopen($mobDB, 'w');
	
	if (!$fdb) {
		$errorMessage = sprintf(Flux::message('ReloadMobSkillsError3'), $mobDB);
		break;
	}

	$write1 = array();
	while($text1 = fgets($fdb1)) {
		if (substr($text1, 0, 2) != '//' && !empty($text1)) {
			$text1 = explode("//", $text1, 2);
			$read1 = trim($text1[0]);
			if (!empty($read1))
				$write1[] = $read1."\r\n";
		}
	}
	fclose($fdb1);
	
	$write2 = array();
	while($text2 = fgets($fdb2)) {
		if (substr($text2, 0, 2) != '//' && !empty($text2)) {
			$text2 = explode("//", $text2, 2);
			$read2 = trim($text2[0]);
			if (!empty($read2))
				$write2[] = $read2."\r\n";
		}
	}
	fclose($fdb2);
	
	natsort($write1);
	foreach ($write1 as $line1)
		fwrite($fdb, preg_replace('/@+/','@',$line1));
	
	natsort($write2);
	foreach ($write2 as $line2)
		fwrite($fdb, preg_replace('/@+/','@',$line2));
	
	fclose($fdb);
}
?>