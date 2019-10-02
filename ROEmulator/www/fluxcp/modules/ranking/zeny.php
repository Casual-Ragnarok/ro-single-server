<?php
if (!defined('FLUX_ROOT')) exit;

$title    = 'Zeny Ranking';
$classes  = Flux::config('JobClasses')->toArray();
$jobClass = $params->get('jobclass');
$bind     = array((int)Flux::config('RankingHideLevel'));

if (trim($jobClass) === '') {
	$jobClass = null;
}

if (!is_null($jobClass) && !array_key_exists($jobClass, $classes)) {
	$this->deny();
}

$charPrefsTable = Flux::config('FluxTables.CharacterPrefsTable');

$col  = "ch.char_id, ch.name AS char_name, ch.zeny, ch.class AS char_class, ch.base_level, ch.base_exp, ch.job_level, ch.job_exp, ";
$col .= "ch.guild_id, guild.name AS guild_name, guild.emblem_len AS guild_emblem_len";

$sql  = "SELECT $col FROM {$server->charMapDatabase}.`char` AS ch ";
$sql .= "LEFT JOIN {$server->charMapDatabase}.$charPrefsTable AS hide_from_zr ON ";
$sql .= "(hide_from_zr.name = 'HideFromZenyRanking' AND hide_from_zr.char_id = ch.char_id) ";
$sql .= "LEFT JOIN {$server->charMapDatabase}.guild ON guild.guild_id = ch.guild_id ";
$sql .= "LEFT JOIN {$server->loginDatabase}.login ON login.account_id = ch.account_id ";
$sql .= "WHERE 1=1 ";

if (Flux::config('HidePermBannedZenyRank')) {
	$sql .= "AND login.state != 5 ";
}
if (Flux::config('HideTempBannedZenyRank')) {
	$sql .= "AND (login.unban_time IS NULL OR login.unban_time = 0) ";
}

$sql .= "AND login.level < ? ";

if ($days=Flux::config('ZenyRankingThreshold')) {
	$sql    .= 'AND TIMESTAMPDIFF(DAY, login.lastlogin, NOW()) <= ? ';
	$bind[]  = $days * 24 * 60 * 60;
}

// Whether or not the character is allowed to hide themselves from the Zeny Ranking.
$sql .= "AND (((hide_from_zr.value IS NULL OR hide_from_zr.value = 0) AND login.level >= ?) OR login.level < ?) ";
$bind[] = $auth->getLevelToHideFromZenyRank;
$bind[] = $auth->getLevelToHideFromZenyRank;

if (!is_null($jobClass)) {
	$sql .= "AND ch.class = ? ";
	$bind[] = $jobClass;
}

$sql .= "ORDER BY ch.zeny DESC, ch.base_level DESC, ch.base_exp DESC, ch.job_level DESC, ch.job_exp DESC, ch.char_id ASC ";
$sql .= "LIMIT ".(int)Flux::config('ZenyRankingLimit');
$sth  = $server->connection->getStatement($sql);

$sth->execute($bind);

$chars = $sth->fetchAll();
?>